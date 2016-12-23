# Install the necessary components
Run 

`npm install bower -g`

`npm install gulp -g`

`npm install`

`bower install`

#Build development version and start a server

Run `gulp serve`
ENV: `PORT` (8081 by default)

#Build production version

Run `gulp deploy` and see `dist/` folder
Run `gulp ensure` in order to run the production version
ENV: `ENSURE_PORT`  (8082 by default)

# Develop through Continuous Integration

Server [ci.zoomtivity.com](http://ci.zoomtivity.com)

* branch from **master** with name like ZOOM-FRONT-\<trello_card_number\>
* create new feature, fix bugs, make changes
* write frontend tests
* push branch
* create pull request to **master**
* watch Jenkins build status in the Bitbucket

![Bibbucket](https://i.gyazo.com/d1fffe67ba922dd288acb9a839854263.png "Заголовок изображения")

* do manual testing on your own testing website
* notify a reviewer do final a testing and accept the pull request
