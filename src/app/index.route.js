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
        templateUrl: 'app/components/navigation/profile_menu/profile_menu.html',
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
            return Album.query({user_id: $stateParams.user_id}).$promise;
          }
        },
        mapState: 'small',
        parent: 'profile_menu',
        locate: 'none'
      })
      .state('createAlbum', {
        url: '/albums/create',
        templateUrl: 'app/modules/photomap/create_album/album_create.html',
        controller: 'CreateAlbumController',
        controllerAs: 'CreateAlbum',
        mapState: 'small',
        parent: 'profile_menu',
        edit: false,
        require_auth: true
      })
      .state('album', {
        url: '/albums/:album_id',
        templateUrl: 'app/modules/photomap/album/album.html',
        controller: 'AlbumController',
        controllerAs: 'Album',
        resolve: {
          album: function (Album, $stateParams) {
            return Album.get({id: $stateParams.album_id}).$promise;
          }
        },
        mapState: 'small',
        parent: 'profile_menu',
        locate: 'none'
      })
      .state('editAlbum', {
        url: '/albums/:album_id/edit',
        templateUrl: 'app/modules/photomap/create_album/album_create.html',
        controller: 'CreateAlbumController',
        controllerAs: 'CreateAlbum',
        resolve: {
          album: function (Album, $stateParams) {
            return Album.get({id: $stateParams.album_id}).$promise;
          }
        },
        edit: true,
        mapState: 'small',
        parent: 'profile_menu',
        require_auth: true,
        locate: 'none'
      })
      .state('friendsmap', {
        url: '/friendsmap',
        templateUrl: 'app/modules/friendsmap/friendsmap.html',
        controller: 'FriendsmapController',
        controllerAs: 'Friendsmap',
        resolve: {
          friends: function(Friends) {
            return Friends.query().$promise;
          }
        },
        mapState: 'small',
        parent: 'profile_menu',
        require_auth: true,
        locate: 'none'
      })
      .state('friendsmap_create', {
        url: '/friendsmap/create',
        templateUrl: 'app/modules/friendsmap/create/friendsmap.create.html',
        controller: 'CreateFriendController',
        controllerAs: 'CreateFriend',
        resolve: {
          friend: function(Friends) {
            return new Friends
          }
        },
        mapState: 'small',
        parent: 'profile_menu',
        edit: false,
        require_auth: true
      })
      .state('friendsmap_edit', {
        url: '/friendsmap/:id/edit',
        templateUrl: 'app/modules/friendsmap/create/friendsmap.create.html',
        controller: 'CreateFriendController',
        controllerAs: 'CreateFriend',
        resolve: {
          friend: function(Friends, $stateParams) {
            return Friends.getFriend({id: $stateParams.id}).$promise;
          }
        },
        mapState: 'small',
        edit: true,
        parent: 'profile_menu',
        require_auth: true,
        locate: 'none'
      })
      .state('settings', {
        url: '/settings',
        templateUrl: 'app/modules/settings/settings.html',
        controller: 'SettingsController',
        controllerAs: 'Settings',
        resolve: {
          currentUser: function (User) {
            return User.currentUser().$promise;
          }
        },
        mapState: 'hidden',
        require_auth: true,
        locate: 'none'
      });

    $urlRouterProvider.otherwise('/');
  }

})();
