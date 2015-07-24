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
      .state('profile_menu', {
        abstract: true,
        templateUrl: 'app/components/profile_menu/profile_menu.html',
        controller: 'ProfileMenuController',
        controllerAs: 'Profile'
      })
      .state('photomap', {
        url: '/users/:user_id/albums',
        templateUrl: 'app/modules/photomap/photomap.html',
        controller: 'PhotomapController',
        controllerAs: 'Photomap',
        resolve: {
          albums: function (Album, $stateParams, MapService) {
            return Album.query({user_id: $stateParams.user_id});
          }
        },
        mapState: 'small',
        parent: 'profile_menu'
      })
      .state('createAlbum', {
        url: '/albums/create',
        templateUrl: 'app/modules/photomap/create_album/album_create.html',
        controller: 'CreateAlbumController',
        controllerAs: 'CreateAlbum',
        resolve: {
          album: function (Album) {
            return new Album();
          }
        },
        mapState: 'small',
        parent: 'profile_menu'
      })
      .state('album', {
        url: '/albums/:album_id',
        templateUrl: 'app/modules/photomap/album/album.html',
        controller: 'AlbumController',
        controllerAs: 'Album',
        resolve: {
          album: function (Album, $stateParams) {
            return Album.get({id: $stateParams.album_id});
          }
        },
        mapState: 'small',
        parent: 'profile_menu'
      })
      .state('editAlbum', {
        url: '/albums/:album_id/edit',
        templateUrl: 'app/modules/photomap/create_album/album_create.html',
        controller: 'EditAlbumController',
        controllerAs: 'EditAlbum',
        resolve: {
          album: function (Album, $stateParams) {
            return Album.get({id: $stateParams.album_id});
          }
        },
        mapState: 'small',
        parent: 'profile_menu'
      })
      .state('friendsmap', {})
      .state('friendsmap_create', {})
      .state('friendsmap_edit', {})
      .state('settings', {
        url: '/settings',
        templateUrl: 'app/modules/settings/settings.html',
        controller: 'SettingsController',
        controllerAs: 'Settings',
        resolve: {
          settings: function(Settings) {
            return Settings.get();
          }
        },
        mapState: 'hidden'
      });

    $urlRouterProvider.otherwise('/');
  }

})();
