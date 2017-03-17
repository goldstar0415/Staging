Zoomtivity
==========

Running a Docker cluster
------------------------

### Clone the project from git

### Install docker

See https://docs.docker.com/engine/installation/

Check your installation using `docker -v`

### Configure env vars and port binding

- create a `docker-compose.override.yml` from example `docker-compose.override.example.yml`, do changes
- create a front reverse-proxy nginx config, use the external ports you have defined in the `docker-compose.override.yml`
- configure frontend variables `frontend/src/env.js`, change backend/websocket URLs according to your reverse-proxy server names
- up the cluster `docker-compose -p my-zoom-cluster up -d`

Folder Structure
----------------

- `/etc` contains all config files for system
- `/backend` contains the laravel application
- `/frontend` contains the user interface for web
- `/ios_app` contains the code for the... iOS app 


