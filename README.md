Zoomtivity
==========

Running a Docker cluster
------------------------

### Clone the project from git

Checkout the `dockerizing` branch

#### Folder Structure

- `/etc` contains all config files for system
- `/backend` contains the laravel application
- `/frontend` contains the user interface for web
- `/ios_app` contains the code for the... iOS app
- `/postgres` contains an image template which allow us to build a postgis image. In runtime it will contain also a ./data folder as a persistent PostgreSQL storage
- `/redis` in runtime, will contain Redis data
- `/websocket` contains WS server and a Dockerfile to build it


#### Discover the docker-compose configs

- `abstract-cluster.yml` - it's a base extendable config file
- `docker-compose.yml` - the main config which provides a cluster definition
- `docker-compose.override.yml` - the environment-dependent config (doesn't exist in git)
- `docker-compose.override.example.yml` - an example for docker-compose.override.yml

Check out how to access microservices inside the cluster: service name is a domain name inside the defined docker networks (front-tier/back-tier). 
For example, Laravel can connect to DB using `database` hostname

### Install Docker

See https://docs.docker.com/engine/installation/

Check your installation using `docker -v`, `docker-compose -v`


### Configure env vars, port bindings and volumes

#### Environment variables

Create a `docker-compose.override.yml` from `docker-compose.override.example.yml`

We can define any environment variables (like APP_NAME) using the `environment` sections in `docker-compose.override.yml`

```yml
  backend:
    environment:
      - APP_NAME=zoomtivity
      ...
```

#### Port bindings

Each container exposes some ports. 
By default they are not visible, but we can bind them to the host machine via host-to-container bridge.

For example, let's forward a container's nginx port via docker-proxy in the docker-compose.override.yml:

```yml
  backend:
    ports:
      - "19080:80"
```

Now we have an active host-machine port 19080

Using port binding, you can just forward a PostgreSQL port to connect to the DB manually
```yml
  database:
    ports:
      - "54328:5432"
```

#### Volumes

Volumes allow us to mount folders between host machine and containers. So we can change some files and see a mirrored files on the other side

For example, PostgreSQL has a data directory:

```yml
  database:
    extends:
      file: abstract-cluster.yml
      service: base
    image: wirnex/postgis:1.2
    volumes:
      - ./postgres/data:/var/lib/postgresql/data        <-----------
```
 
We've mounted this folder into `./postgres/data` on host machine. Thus every time we restart the container we have a restored data.

Volumes can be used for development to see changes without restarting containers.

#### Configure frontend environments

Edit the src/env.js file, configure services URLs (use domain names or port binding, like hostmachinedomain:19080). 
If you have selected some domain names instead of host:port, configure the nginx reverse-proxy (see the next step)

#### Set up a front reverse-proxy 

Configure your host-machine nginx, use the external ports you have defined in the `docker-compose.override.yml`

See examples: `etc/nginx/front-nginx.dev.conf`, `etc/nginx/front-nginx.prod.conf` 


### Restore a postgres backup 

Run your pg_restore using a forwarded DB port

The fastest way: just get your PostgreSQL files from backup and put into `/postgres/data` volume


### Run the cluster

Choose a prefix for your docker cluster, like 'my-zoom-cluster'

Run `docker-compose -p my-zoom-cluster up -d`
Wait... Docker will build all the images, start containers.

Run `docker-compose -p my-zoom-cluster ps` to see containers that docker has started


### Development Mode

#### Frontend

- Configure your docker-compose.override.yml

```yml
  frontend:
    environment:
      - SERVE_PORT=81       <----------- define a custom internal gulp-serve port
    ports:
      - "19082:80"
      - "19083:81"          <----------- configure gulp-serve port forwarding here, like '19083' 
    volumes:
      - ./frontend:/var/www/zoomtivity-dev
```

- Rebuild and restart your 'frontend' image & container

- Configure your front Nginx, create a dev vhost (see etc/nginx/front-nginx.dev.conf):

```nginx
upstream zoom_front_serve { server localhost:19083; }

server {
        listen 80;
        server_name dev.zoomtivity.loc;     <-------- choose a dev domain name
        #... some configs .....
        location / {
            # .... some configs .....
            proxy_pass http://zoom_front_serve;       <----------- put here the upstream you have configured before
        }
}
```

- Reload nginx: `sudo nginx -t`, `sudo nginx -s reload`

- Configure automatic file uploads to your dev server in the IDE

- Run `gulp serve`:

```bash
docker-compose -p my-zoom-cluster exec frontend npm run serve
```

This command will work in foreground, and the gulp will watch the container's filesystem for changes, recompile scss, ...

- Open `http://dev.zoomtivity.loc` in your browser. In IDE, try to change src/index.html or a scss file, see the gulp's console messages

> gulp-serve will not reload the static server when we change any js files or angular's html templates - it's not required
> because files are served directly "as is" from the `src/` directory. Just reload the page in the browser.


#### Backend

//todo:


### Useful docker commands

`docker-compose -p my-zoom-cluster ps` - show cluster containers
`docker-compose -p my-zoom-cluster up -d` - up all
`docker-compose -p my-zoom-cluster up -d --build backend` - to rebuild and up one service
`docker-compose -p my-zoom-cluster down` - down all
`docker-compose -p my-zoom-cluster restart` - restart all
`docker-compose -p my-zoom-cluster restart backend` - restart one service if needed
`docker-compose -p my-zoom-cluster down --rmi=local` - down and remove all the local images (volumes will not be deleted)
`docker-compose -p my-zoom-cluster exec backend ls -la` - execute commands inside containers, for example, run `ls -la` on backend
`docker-compose -p my-zoom-cluster logs -f` - see cluster logs in realtime
`docker-compose -p my-zoom-cluster logs -f backend` - see backend's logs in realtime
