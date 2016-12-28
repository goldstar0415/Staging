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

Run `gulp build` and see `dist/` folder
Run `gulp ensure` in order to build & run the production version
ENV: `ENSURE_PORT` (8082 by default)

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

#Development through lazy-load

> See https://oclazyload.readme.io/docs

- we have to add all new scripts/styles (bower, assets, app) manually (without gulp wiredep)
- add your "lazy" scripts/styles into `src/index.route.js`, or load them on-the-fly using `$ocLazyLoad` service in your angular modules. You can define an ocLazyLoad module in the `src/index.module.js` in order to group files.
- get a "lazy" module using `$injector` if needed, for example:
```
var app = angular.module("myApp", ["oc.lazyLoad"]);

app.controller('MyCtrl', function($scope, $ocLazyLoad, $injector) {
    $ocLazyLoad.load('ngDialog.js').then(function() {
        var ngDialog = $injector.get('ngDialog');
        ngDialog.open({
            template: 'dialogTemplate'
        });
    });
});
```
- if you want to load js/css globally, just add it into `src/index.html` (for debug) and `gulp/*.stream` (for production) 
