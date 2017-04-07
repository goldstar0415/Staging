
See https://docs.docker.com/engine/getstarted/step_six/ for instructions

- cd `image/`
- `docker login`, enter your username/password
- build an image `docker build -t wirnex/postgis:1.2 --rm --no-cache .`
- `docker push wirnex/postgis:1.2` - push the image you have created 

Checkout the image tags page: https://hub.docker.com/r/wirnex/postgis/tags/
