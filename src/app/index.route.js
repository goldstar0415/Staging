(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .config(routeConfig);

  /** @ngInject */
  function routeConfig($stateProvider, $urlRouterProvider, $locationProvider, DEBUG) {
    $stateProvider
      .state('main', {
        abstract: true,
        template: '<ui-view  />',
        resolve: {
          currentUser: function ($q, User, $rootScope, UserService) {
            if ($rootScope.currentUser) {
              return $rootScope.currentUser;
            } else if (!$rootScope.currentUserFailed) {
             return UserService.getCurrentUserPromise();
            }
          }
        }
      })
      //Abstract state for profile menu
      .state('profile_menu', {
        abstract: true,
        parent: 'main',
        templateUrl: '/app/components/navigation/profile_menu/profile_menu.html',
        controller: 'ProfileMenuController',
        controllerAs: 'Profile'
      })
      .state('profile', {
        url: '/user/:user_id',
        template: '<ui-view  />',
        abstract: true,
        resolve: {
          user: function ($rootScope, User, currentUser, $stateParams,  UserService) {
            if (currentUser && currentUser.id == $stateParams.user_id) {
              return User.currentUser({}, function (user) {
                $rootScope.currentUser = user;
                UserService.setProfileUser(user);
                return user;
              }).$promise;
            } else {
              return User.get({id: $stateParams.user_id}, function (user) {
                UserService.setProfileUser(user);
                return user;
              }).$promise;
            }
          }
        },
        parent: 'profile_menu'
      })

      //Main map page
      .state('index', {
        url: '/',
        parent: 'main',
        mapState: 'big'
      })

      //Blog page
      .state('blog', {
        url: '/bloggers_profile',
        templateUrl: '/app/modules/blog/blog.html',
        controller: 'BlogController',
        controllerAs: 'Blog',
        parent: 'main',
        mapState: 'none'
      })
      //Bloggers profile page
      .state('blogger_profile', {
        url: '/bloggers_profile',
        templateUrl: '/app/modules/blog/bloggers_profile/bloggers_profile.html',
        controller: 'BloggerProfileController',
        controllerAs: 'Blogger',
        parent: 'main',
        mapState: 'none'
      })
      //Show blog location on map and show pop-up
      .state('blog_article', {
        url: '/article/:id',
        controller: 'ArticleController',
        controllerAs: 'Article',
        mapState: 'big',
        parent: 'main',
        resolve: {
          article: function () {
            //TODO: Pass article data to controller (to show this data on pop-up)
            return 'article'
          }
        }
      })
      //Blog article creation page
      .state('blog_article_create', {
        url: '/article/create',
        templateUrl: '/app/modules/blog/article_create/article_create.html',
        controller: 'ArticleCreateController',
        controllerAs: 'ArticleCreate',
        mapState: 'small',
        parent: 'main',
        require_auth: true,
        locate: 'none'
      })

      .state('spots', {
        url: '/spots',
        templateUrl: '/app/modules/spot/spots/spots.html',
        controller: 'SpotsController',
        controllerAs: 'Spots',
        parent: 'profile',
        locate: 'none',
        mapState: 'small',
        resolve: {
          allSpots: function(Spot, $stateParams) {
            return Spot.query({user_id: $stateParams.user_id}).$promise;
          }
        }
      })
      .state('spot_create', {
        url: '/spot/create',
        templateUrl: '/app/modules/spot/spot_create/spot_create.html',
        controller: 'SpotCreateController',
        controllerAs: 'SpotCreate',
        parent: 'profile_menu',
        locate: 'current',
        resolve: {
          spot: function (Spot) {
            return new Spot();
          },
          categories: function($http, API_URL) {
            return $http.get(API_URL + '/spots/categories')
          }
        },
        require_auth: true,
        mapState: 'small',
        edit: false
      })
      .state('spot_edit', {
        url: '/spot/:spot_id/edit',
        templateUrl: '/app/modules/spot/spot_create/spot_create.html',
        controller: 'SpotCreateController',
        controllerAs: 'SpotCreate',
        parent: 'profile_menu',
        locate: 'none',
        resolve: {
          spot: function (Spot, $stateParams) {
            return Spot.get({id: $stateParams.spot_id}).$promise;
          },
          categories: function($http, API_URL) {
            return $http.get(API_URL + '/spots/categories')
          }
        },
        require_auth: true,
        mapState: 'small',
        edit: true
      })
      .state('spot', {
        url: '/spot/:spot_id',
        templateUrl: '/app/modules/spot/spot.html',
        controller: 'SpotController',
        controllerAs: 'Spot',
        parent: 'profile',
        resolve: {
          spot: function (Spot, $stateParams) {
            return Spot.get({id: $stateParams.spot_id}).$promise;
          }
        },
        locate: 'none',
        mapState: 'small'
      })

      //Planner (calendar + list of all plans)
      .state('planner', {
        abstract: true,
        template: '<ui-view />',
        parent: 'profile_menu'
      })
      .state('planner.list', {
        url: '/planner',
        templateUrl: '/app/modules/planner/planner.html',
        controller: 'PlannerController',
        controllerAs: 'Planner',
        parent: 'planner',
        locate: 'none',
        require_auth: true,
        mapState: 'small'
      })
      .state('planner.create', {
        url: '/plan/create',
        templateUrl: '/app/modules/planner/plan_create/plan_create.html',
        controller: 'PlanCreateController',
        controllerAs: 'Plan',
        resolve: {
          plan: function (Plan) {
            return new Plan();
          },
          categories: function (Plan) {
            return Plan.activityCategories().$promise;
          }
        },
        parent: 'planner',
        locate: 'none',
        require_auth: true,
        mapState: 'small'
      })
      .state('planner.edit', {
        url: '/plan/:plan_id/edit',
        templateUrl: '/app/modules/planner/plan_create/plan_create.html',
        controller: 'PlanCreateController',
        controllerAs: 'Plan',
        parent: 'planner',
        locate: 'none',
        resolve: {
          plan: function (Plan, $stateParams) {
            return Plan.get({id: $stateParams.plan_id}).$promise;
          },
          categories: function (Plan) {
            return Plan.activityCategories().$promise;
          }
        },
        require_auth: true,
        mapState: 'small'
      })
      .state('planner.view', {
        url: '/plan/:plan_id',
        templateUrl: '/app/modules/planner/plan/plan.html',
        controller: 'PlanController',
        controllerAs: 'Plan',
        parent: 'profile',
        resolve: {
          plan: function (Plan, $stateParams) {
            return Plan.get({id: $stateParams.plan_id}).$promise;
          }
        },
        require_auth: true,
        locate: 'none',
        mapState: 'small'
      })

      .state('profile.main', {
        url: '',
        templateUrl: '/app/modules/profile/profile.html',
        controller: 'ProfileController',
        controllerAs: 'Profile',
        resolve: {
          spots: function (user, Spot, $stateParams, PermissionService) {
            if (PermissionService.checkPermission(user.privacy_events, user)) {
              return Spot.query({
                user_id: $stateParams.user_id
              }).$promise;
            }
          }
        },
        parent: 'profile',
        locate: 'none',
        mapState: 'small'
      })

      //chat
      .state('chat', {
        url: '/chat',
        templateUrl: '/app/modules/chat/chat.html',
        controller: 'ChatController',
        controllerAs: 'Chat',
        parent: 'profile_menu',
        locate: 'none',
        resolve: {
          dialogs: function (Message) {
            return Message.dialogs().$promise;
          }
        },
        require_auth: true,
        mapState: 'small'
      })
      .state('chatRoom', {
        url: '/chat/:user_id',
        templateUrl: '/app/modules/chat/chat_room/chat_room.html',
        controller: 'ChatRoomController',
        controllerAs: 'ChatRoom',
        resolve: {
          user: function (User, $stateParams) {
            return User.get({id: $stateParams.user_id}).$promise;
          },
          messages: function (Message, $stateParams) {
            return Message.query({
              user_id: $stateParams.user_id
            }).$promise;
          }
        },
        parent: 'profile_menu',
        locate: 'none',
        require_auth: true,
        mapState: 'small'
      })
      .state('feeds', {
        url: '/feeds',
        templateUrl: '/app/modules/feed/feed.html',
        controller: 'FeedsController',
        controllerAs: 'Feed',
        parent: 'profile_menu',
        locate: 'none',
        require_auth: true,
        mapState: 'small'
      })
      .state('reviews', {
        url: '/reviews',
        templateUrl: '/app/modules/reviews/reviews.html',
        controller: 'ReviewsController',
        controllerAs: 'Review',
        parent: 'profile_menu',
        locate: 'none',
        require_auth: true,
        mapState: 'small'
      })

      .state('favorites', {
        url: '/favorites',
        templateUrl: '/app/modules/favorites/favorites.html',
        controller: 'FavoritesController',
        controllerAs: 'Favorite',
        parent: 'profile',
        locate: 'none',
        mapState: 'small',
        resolve: {
          allSpots: function(Spot, $stateParams) {
            return Spot.favorites({user_id: $stateParams.user_id}).$promise;
          }
        }
      })
      .state('areas', {
        url: '/areas',
        templateUrl: '/app/modules/areas/areas.html',
        controller: 'AreasController',
        controllerAs: 'Area',
        resolve: {
          areas: function (Area) {
            return Area.query().$promise;
          }
        },
        parent: 'profile_menu',
        locate: 'none',
        require_auth: true,
        mapState: 'small'
      })
      .state('areas.preview', {
        url: '/areas/:area_id',
        template: '',
        controller: 'AreasPreviewController',
        controllerAs: 'AreasPreview',
        resolve: {
          selection: function (Area, $stateParams) {
            return Area.get({
              area_id: $stateParams.area_id
            }).$promise;
          }
        },
        parent: 'main',
        locate: 'none',
        mapState: 'big'
      })


      //Photomap view state
      .state('photos', {
        url: '/albums',
        templateUrl: '/app/modules/photomap/photomap.html',
        controller: 'PhotomapController',
        controllerAs: 'Photomap',
        resolve: {
          albums: function (Album, $stateParams) {
            return Album.query({user_id: $stateParams.user_id}).$promise;
          }
        },
        mapState: 'small',
        parent: 'profile',
        locate: 'none'
      })
      //Create album state
      .state('photos.createAlbum', {
        url: '/albums/create',
        templateUrl: '/app/modules/photomap/create_album/album_create.html',
        controller: 'CreateAlbumController',
        controllerAs: 'CreateAlbum',
        mapState: 'small',
        parent: 'profile_menu',
        resolve: {
          album: function () {
            return {
              address: "",
              location: null,
              is_private: 0
            };
          }
        },
        require_auth: true
      })
      //Edit album state
      .state('photos.editAlbum', {
        url: '/albums/:album_id/edit',
        templateUrl: '/app/modules/photomap/create_album/album_create.html',
        controller: 'CreateAlbumController',
        controllerAs: 'CreateAlbum',
        resolve: {
          album: function (Album, $stateParams) {
            return Album.get({id: $stateParams.album_id}).$promise;
          }
        },
        mapState: 'small',
        parent: 'profile_menu',
        require_auth: true,
        locate: 'none'
      })
      //Albums page state
      .state('photos.edit_photo', {
        url: '/photos/:photo_id',
        templateUrl: '/app/modules/photomap/edit_photo/edit_photo.html',
        controller: 'PhotoEditController',
        controllerAs: 'Photo',
        resolve: {
          photo: function (Photo, $stateParams) {
            return Photo.get({id: $stateParams.photo_id}).$promise;
          },
          user_id: function ($stateParams) {
            return $stateParams.user_id;
          }
        },
        mapState: 'small',
        parent: 'profile',
        require_auth: true,
        locate: 'none'
      })
      .state('photos.album', {
        url: '/albums/:album_id',
        templateUrl: '/app/modules/photomap/album/album.html',
        controller: 'AlbumController',
        controllerAs: 'Album',
        resolve: {
          album: function (Album, $stateParams) {
            return Album.get({id: $stateParams.album_id}).$promise;
          },
          photos: function (Album, $stateParams) {
            return Album.photos({album_id: $stateParams.album_id}).$promise;
          }
        },
        mapState: 'small',
        parent: 'profile',
        locate: 'none'
      })


      //Friends map state
      .state('friendsmap', {
        url: '/friendsmap',
        templateUrl: '/app/modules/friendsmap/friendsmap.html',
        controller: 'FriendsmapController',
        controllerAs: 'Friendsmap',
        resolve: {
          friends: function (Friends) {
            return Friends.query().$promise;
          }
        },
        mapState: 'small',
        parent: 'profile_menu',
        require_auth: true,
        locate: 'none'
      })
      //Friends creation state
      .state('friendsmap_create', {
        url: '/friendsmap/create',
        templateUrl: '/app/modules/friendsmap/create/friendsmap.create.html',
        controller: 'CreateFriendController',
        controllerAs: 'CreateFriend',
        resolve: {
          friend: function (Friends) {
            return new Friends();
          }
        },
        mapState: 'small',
        parent: 'profile_menu',
        edit: false,
        require_auth: true
      })
      //Friends edit state
      .state('friendsmap_edit', {
        url: '/friendsmap/:id/edit',
        templateUrl: '/app/modules/friendsmap/create/friendsmap.create.html',
        controller: 'CreateFriendController',
        controllerAs: 'CreateFriend',
        resolve: {
          friend: function (Friends, $stateParams) {
            return Friends.getFriend({id: $stateParams.id}).$promise;
          }
        },
        mapState: 'small',
        edit: true,
        parent: 'profile_menu',
        require_auth: true,
        locate: 'none'
      })

      //About us page
      .state('about_us', {
        url: "/about-us",
        templateUrl: '/app/modules/about_us/about_us.html',
        parent: 'main',
        mapState: 'hidden'
      })
      //Contact us page
      .state('contact_us', {
        url: "/contact-us",
        templateUrl: '/app/modules/contact_us/contact_us.html',
        controller: 'ContactUsController',
        controllerAs: 'ContactUs',
        parent: 'main',
        mapState: 'hidden'
      })

      //Zoomers page
      .state('zoomers', {
        url: '/zoomers',
        templateUrl: '/app/modules/zoomers/zoomers.html',
        controller: 'ZoomersController',
        controllerAs: 'Zoomers',
        resolve: {
          users: function (User) {
            return User.query().$promise;
          }
        },
        parent: 'main',
        mapState: 'hidden'
      })

      //Settings state
      .state('settings', {
        url: '/settings',
        templateUrl: '/app/modules/settings/settings.html',
        controller: 'SettingsController',
        controllerAs: 'Settings',
        resolve: {
          currentUser: function (User) {
            return User.currentUser().$promise;
          }
        },
        mapState: 'hidden',
        require_auth: true,
        parent: 'main',
        locate: 'none'
      });

    $urlRouterProvider.otherwise('/');
  }

})();
