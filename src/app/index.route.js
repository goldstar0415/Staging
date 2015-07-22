(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .config(routeConfig);

  /** @ngInject */
  function routeConfig($stateProvider, $urlRouterProvider) {
    $stateProvider
      .state('index', {
        url: '/',
        mapState: 'big'
      })
      .state('photomap', {
        url: '/user/:user_id/albums',
        templateUrl: 'app/modules/photomap/photomap.html',
        controller: 'PhotomapController',
        controllerAs: 'Photomap',
        resolve: {
          albums: function (Album) {
            return Album.query();
          }
        },
        mapState: 'small'
      })
      .state('album', {
        url: '/user/:user_id/albums/:album_id',
        templateUrl: 'app/modules/photomap/album/album.html',
        controller: 'AlbumController',
        controllerAs: 'Album',
        resolve: {
          album: function (Album, $stateParams) {
            return Album.get({id: $stateParams.album_id});
          }
        },
        mapState: 'small'
      })
      .state('editAlbum', {
        url: '/albums/:album_id/edit',
        templateUrl: 'app/modules/photomap/create_album/album_create.html',
        controller: 'EditAlbumController',
        controllerAs: 'vm',
        resolve: {
          album: function (Album, $stateParams) {
            return Album.get({id: $stateParams.album_id});
          }
        },
        mapState: 'small'
      })
      .state('createAlbum', {
        url: '/albums/create',
        templateUrl: 'app/modules/photomap/create_album/album_create.html',
        controller: 'CreateAlbumController',
        controllerAs: 'vm',
        resolve: {
          album: function (AlbumModel) {
            return new AlbumModel();
          }
        },
        mapState: 'small'
      })
      .state('settings', {
        url: '/settings',
        templateUrl: 'app/modules/settings/settings.html',
        controller: 'SettingsController',
        controllerAs: 'Settings',
        mapState: 'hidden'
      })
    ;

    $urlRouterProvider.otherwise('/');
  }

})();
