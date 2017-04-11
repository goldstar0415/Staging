#!/bin/bash

set -e
set -x

file_env() {
	local var="$1"
	local fileVar="${var}_FILE"
	local def="${2:-}"
	if [ "${!var:-}" ] && [ "${!fileVar:-}" ]; then
		echo >&2 "error: both $var and $fileVar are set (but are exclusive)"
		exit 1
	fi
	local val="$def"
	if [ "${!var:-}" ]; then
		val="${!var}"
	elif [ "${!fileVar:-}" ]; then
		val="$(< "${!fileVar}")"
	fi
	export "$var"="$val"
	unset "$fileVar"
}

init_pglogical() {
    echo
    echo "[pglogical] init()"
    echo "[pglogical] configuring pg_hba.conf..."
    cat /pglogical-configs/pg_hba.conf >> /var/lib/postgresql/data/pg_hba.conf
    echo "[pglogical] configuring postgresql.conf..."
    cat /pglogical-configs/postgresql.conf >> /var/lib/postgresql/data/postgresql.conf

    REPLICATION_USER="logical_replication"
    REPLICATION_HOST="database"
    REPLICATION_PORT="5432"
    REPLICATION_PROVIDER="provider"

    createuser -U postgres --superuser --replication ${REPLICATION_USER} || echo "[pglogical] skip: create user"
    psql -U postgres -d ${POSTGRES_DB} -c "CREATE EXTENSION pglogical;" || echo "[pglogical] skip: create extension"
    psql -U postgres -d ${POSTGRES_DB} -c "SELECT pglogical.create_node(node_name := '${REPLICATION_PROVIDER}', dsn := 'host=${REPLICATION_HOST} port=${REPLICATION_PORT} dbname=${POSTGRES_DB} user=${REPLICATION_USER}');" || echo "skip: init provider"
    psql -U postgres -d ${POSTGRES_DB} -c "SELECT pglogical.replication_set_add_all_tables('default', '{public}'::text[]);" || echo "[pglogical] skip: replication_set_add_all_tables()"
    psql -d ${POSTGRES_DB} -c "SELECT pglogical.replication_set_remove_table('default', 'spatial_ref_sys');" || echo "[pglogical] skip: replication_set_remove_table()"

    echo "[pglogical] initialization completed"
}

if [ "${1:0:1}" = '-' ]; then
	set -- postgres "$@"
fi

# allow the container to be started with `--user`
if [ "$1" = 'postgres' ] && [ "$(id -u)" = '0' ]; then
	mkdir -p "$PGDATA"
	chown -R postgres "$PGDATA"
	chmod 700 "$PGDATA"

	mkdir -p /var/run/postgresql
	chown -R postgres /var/run/postgresql
	chmod g+s /var/run/postgresql

	# Create the transaction log directory before initdb is run (below) so the directory is owned by the correct user
	if [ "$POSTGRES_INITDB_XLOGDIR" ]; then
		mkdir -p "$POSTGRES_INITDB_XLOGDIR"
		chown -R postgres "$POSTGRES_INITDB_XLOGDIR"
		chmod 700 "$POSTGRES_INITDB_XLOGDIR"
	fi

	exec gosu postgres "$BASH_SOURCE" "$@"
fi

if [ "$1" = 'postgres' ]; then
	mkdir -p "$PGDATA"
	chown -R "$(id -u)" "$PGDATA" 2>/dev/null || :
	chmod 700 "$PGDATA" 2>/dev/null || :

	# look specifically for PG_VERSION, as it is expected in the DB dir
	if [ ! -s "$PGDATA/PG_VERSION" ]; then
		file_env 'POSTGRES_INITDB_ARGS'
		if [ "$POSTGRES_INITDB_XLOGDIR" ]; then
			export POSTGRES_INITDB_ARGS="$POSTGRES_INITDB_ARGS --xlogdir $POSTGRES_INITDB_XLOGDIR"
		fi
		eval "initdb --username=postgres $POSTGRES_INITDB_ARGS"

		# check password first so we can output the warning before postgres
		# messes it up
		file_env 'POSTGRES_PASSWORD'
		if [ "$POSTGRES_PASSWORD" ]; then
			pass="PASSWORD '$POSTGRES_PASSWORD'"
			authMethod=md5
		else
			# The - option suppresses leading tabs but *not* spaces. :)
			cat >&2 <<-'EOWARN'
				****************************************************
				WARNING: No password has been set for the database.
				         This will allow anyone with access to the
				         Postgres port to access your database. In
				         Docker's default configuration, this is
				         effectively any other container on the same
				         system.
				         Use "-e POSTGRES_PASSWORD=password" to set
				         it in "docker run".
				****************************************************
			EOWARN

			pass=
			authMethod=trust
		fi

		{
			echo
			echo "host all all all $authMethod"
		} >> "$PGDATA/pg_hba.conf"

		# internal start of server in order to allow set-up using psql-client
		# does not listen on external TCP/IP and waits until start finishes
		PGUSER="${PGUSER:-postgres}" \
		pg_ctl -D "$PGDATA" \
			-o "-c listen_addresses='localhost'" \
			-w start

		file_env 'POSTGRES_USER' 'postgres'
		file_env 'POSTGRES_DB' "$POSTGRES_USER"

		psql=( psql -v ON_ERROR_STOP=1 )

		if [ "$POSTGRES_DB" != 'postgres' ]; then
			"${psql[@]}" --username postgres <<-EOSQL
				CREATE DATABASE "$POSTGRES_DB" ;
			EOSQL
			echo
		fi

		if [ "$POSTGRES_USER" = 'postgres' ]; then
			op='ALTER'
		else
			op='CREATE'
		fi
		"${psql[@]}" --username postgres <<-EOSQL
			$op USER "$POSTGRES_USER" WITH SUPERUSER $pass ;
		EOSQL
		echo

		psql+=( --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" )

		echo
		for f in /docker-entrypoint-initdb.d/*; do
			case "$f" in
				*.sh)     echo "$0: running $f"; . "$f" ;;
				*.sql)    echo "$0: running $f"; "${psql[@]}" -f "$f"; echo ;;
				*.sql.gz) echo "$0: running $f"; gunzip -c "$f" | "${psql[@]}"; echo ;;
				*)        echo "$0: ignoring $f" ;;
			esac
			echo
		done

        # init pglogical for the newly created db
        if [ ! -f ${PGDATA}/.docker-pglogical.lock ]; then
            init_pglogical
            echo 1 > ${PGDATA}/.docker-pglogical.lock
        else
            echo "[pglogical] Already initialized, do nothing"
        fi

		PGUSER="${PGUSER:-postgres}" \
		pg_ctl -D "$PGDATA" -m fast -w stop

		echo
		echo 'PostgreSQL init process complete; ready for start up.'
		echo
	else
	    # try init pglogical for existing db
        if [ ! -f ${PGDATA}/.docker-pglogical.lock ]; then
            # start a temporary server
            PGUSER="${PGUSER:-postgres}" \
            pg_ctl -D "$PGDATA" -o "-c listen_addresses='localhost'" -w start
            init_pglogical
            # stop the server
            PGUSER="${PGUSER:-postgres}" \
            pg_ctl -D "$PGDATA" -m fast -w stop
            echo 1 > ${PGDATA}/.docker-pglogical.lock
        else
            echo "[pglogical] Already initialized, do nothing"
        fi
	fi
fi

exec "$@"
