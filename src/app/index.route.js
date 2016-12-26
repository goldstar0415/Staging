(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .config(routeConfig);

  /** @ngInject */
  function routeConfig($stateProvider, $urlRouterProvider, $locationProvider, DEBUG, toastr) {
    $stateProvider
      .state('main', {
        abstract: true,
        template: '<ui-view  />',
        resolve: {
          currentUser: function ($q, User, $rootScope, UserService, $state) {
            if ($rootScope.currentUser) {
              return $rootScope.currentUser;
            } else if (!$rootScope.currentUserFailed) {
              return UserService.getCurrentUserPromise();
            }
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad
              .load(versionize([
                '/app/components/navigation/header/bloodhound-search.directive.js',
              ]))
              .then(window.hidePreloader);
          }]
        }
      })
      //Abstract state for profile menu
      .state('profile_menu', {
        abstract: true,
        parent: 'main',
        templateUrl: '/app/components/navigation/profile_menu/profile_menu.html',
        controller: 'ProfileMenuController',
        controllerAs: 'Profile',
        resolve: {
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/components/navigation/profile_menu/profileMenu.controller.js'
            ]));
          }]
        }
      })

      //Main map page
      .state('index', {
        url: '/',
        parent: 'main',
        mapState: 'big',
        params: {
          spotSearch: null,
          spotLocation: null,
          searchText: '',
		      filter: {}
        }
      })
      .state('index.post', {
        url: '/map/post/:slug',
        controller: 'MapPostController',
        resolve: {
          post: function (Post, $stateParams) {
            return Post.get({id: $stateParams.slug}).$promise;
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/map/map_post.controller.js'
            ]));
          }]
        },
        parent: 'main',
        mapState: 'big'
      })
      .state('index.recovery_password', {
        url: '/password/recovery/:token',
        controller: 'ResetPasswordController',
        parent: 'main',
        mapState: 'big',
        resolve: {
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/components/password_reset/password_reset.controller.js'
            ]));
          }]
        }
      })
      .state('index.email_verified', {
        url: '/email-verified',
        controller: function (SignInService) {
          SignInService.openModal();
          toastr.success('Your email successfully verified');
        },
        parent: 'main',
        mapState: 'big'
      })
      .state('index.email_changed', {
        url: '/settings/email-changed',
        controller: function () {
          toastr.success('Your email successfully changed');
        },
        parent: 'main',
        mapState: 'big'
      })
      .state('index.token_expired', {
        url: '/settings/token-expired',
        controller: function () {
          toastr.error('This token is expired');
        },
        parent: 'main',
        mapState: 'big'
      })
      .state('index.unsubscribe', {
        url: '/unsubscribe',
        controller: function (UserService, $stateParams) {
          UserService.unSubscribe(toastr);
        },
        parent: 'main',
        mapState: 'big',
        require_auth: true
      })


      //Intro pages
      .state('intro', {
        url: '/intro',
        templateUrl: '/app/modules/intro/main.html',
        controller: 'IntroController',
        controllerAs: 'Intro',
        parent: 'main',
        mapState: 'hidden',
        resolve: {
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/intro/intro.controller.js',
            ]));
          }]
        }
      })
      .state('intro.events', {
        url: '/events',
        templateUrl: '/app/modules/intro/events.html',
        parent: 'intro',
        mapState: 'hidden'
      })
      .state('intro.road', {
        url: '/roadtrips',
        templateUrl: '/app/modules/intro/road.html',
        parent: 'intro',
        mapState: 'hidden'
      })
      .state('intro.social', {
        url: '/besocial',
        templateUrl: '/app/modules/intro/social.html',
        parent: 'intro',
        mapState: 'hidden'
      })
      .state('intro.grub', {
        url: '/grub',
        templateUrl: '/app/modules/intro/grub.html',
        parent: 'intro',
        mapState: 'hidden'
      })
      .state('intro.photos', {
        url: '/photos',
        templateUrl: '/app/modules/intro/photos.html',
        parent: 'intro',
        mapState: 'hidden'
      })
      .state('intro.todo', {
        url: '/thingstodo',
        templateUrl: '/app/modules/intro/todo.html',
        parent: 'intro',
        mapState: 'hidden'
      })
      .state('intro.room', {
        url: '/rooms',
        templateUrl: '/app/modules/intro/get_room.html',
        parent: 'intro',
        mapState: 'hidden'
      })
      .state('intro.blog', {
        url: '/blog',
        templateUrl: '/app/modules/intro/blog.html',
        parent: 'intro',
        mapState: 'hidden'
      })

      //Blog page
      .state('blog', {
        url: '/blog',
        templateUrl: '/app/modules/blog/blog.html',
        controller: 'BlogController',
        controllerAs: 'Blog',
        parent: 'main',
        mapState: 'hidden',
        resolve: {
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/blog/blog.controller.js'
            ]));
          }]
        }
      })

      //Blog article creation page
      .state('profile_blog.create', {
        url: '/article/create/',
        templateUrl: '/app/modules/blog/article_create/article_create.html',
        controller: 'ArticleCreateController',
        controllerAs: 'Article',
        mapState: 'small',
        parent: 'profile_menu',
        resolve: {
          categories: function (Post) {
            return Post.categories().$promise;
          },
          article: function (Post) {
            return new Post();
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              'summernote',
              'cropper',
              'uploader',
              '/app/modules/blog/article_create/articleCreate.controller.js',
            ]));
          }]
        },
        require_auth: true,
        locate: 'none'
      })
      .state('profile_blog.edit', {
        url: '/article/edit/:slug',
        templateUrl: '/app/modules/blog/article_create/article_create.html',
        controller: 'ArticleCreateController',
        controllerAs: 'Article',
        mapState: 'small',
        parent: 'profile_menu',
        resolve: {
          categories: function (Post) {
            return Post.categories().$promise;
          },
          article: function (Post, $stateParams) {
            return Post.get({id: $stateParams.slug}).$promise;
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              'summernote',
              'cropper',
              'uploader',
              '/app/modules/blog/article_create/articleCreate.controller.js',
            ]));
          }]
        },
        require_auth: true,
        locate: 'none'
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
          categories: function ($http, API_URL) {
            return $http.get(API_URL + '/spots/categories')
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              'cropper',
              'uploader',
              '/app/modules/spot/spot_create/spotCreate.controller.js'
            ]));
          }]
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
          categories: function ($http, API_URL) {
            return $http.get(API_URL + '/spots/categories')
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              'cropper',
              'uploader',
              '/app/modules/spot/spot_create/spotCreate.controller.js'
            ]));
          }]
        },
        require_auth: true,
        mapState: 'small',
        edit: true
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
        mapState: 'small',
        resolve: {
          all_plans: function (Plan) {
            return Plan.query().$promise;
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              'calendar',
              '/app/modules/planner/planner.controller.js'
            ]));
          }]
        }
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
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/planner/plan_create/planCreate.controller.js'
            ]));
          }]
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
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/planner/plan_create/planCreate.controller.js'
            ]));
          }]
        },
        require_auth: true,
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
          messages: function (Message) {
            return Message.dialogs().$promise;
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load([
              '/app/modules/chat/chat.controller.js'
            ]);
          }]
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
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/chat/chat_room/chat_room.controller.js'
            ]));
          }]
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
        mapState: 'small',
        resolve: {
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/feed/feed.controller.js',
              '/app/models/feed.js',
            ]));
          }]
        }
      })
      .state('comments', {
        url: '/comments',
        templateUrl: '/app/modules/comments/comments.html',
        controller: 'CommentsController',
        controllerAs: 'Comment',
        parent: 'profile_menu',
        locate: 'none',
        require_auth: true,
        mapState: 'small',
        resolve: {
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/comments/comments.controller.js',
              '/app/models/feed.js',
            ]));
          }]
        }
      })
      .state('reviews', {
        url: '/reviews',
        templateUrl: '/app/modules/reviews/reviews.html',
        controller: 'ReviewsController',
        controllerAs: 'Review',
        parent: 'profile_menu',
        locate: 'none',
        require_auth: true,
        mapState: 'small',
        resolve: {
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/reviews/reviews.controller.js',
              '/app/models/feed.js',
            ]));
          }]
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
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/areas/areas.controller.js'
            ]));
          }]
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
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/areas/preview/areasPreview.controller.js'
            ]));
          }]
        },
        parent: 'main',
        locate: 'none',
        mapState: 'big'
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
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              'cropper',
              'uploader',
              '/app/modules/photomap/create_album/createAlbum.controller.js'
            ]));
          }]
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
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              'cropper',
              'uploader',
              '/app/modules/photomap/create_album/createAlbum.controller.js'
            ]));
          }]
        },
        mapState: 'small',
        parent: 'profile_menu',
        require_auth: true,
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
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              'cropper',
              'uploader',
              '/app/modules/friendsmap/friendsmap.controller.js',
            ]));
          }]
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
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              'cropper',
              'uploader',
              '/app/modules/friendsmap/create/createFriend.controller.js',
            ]));
          }]
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
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              'cropper',
              'uploader',
              '/app/modules/friendsmap/create/createFriend.controller.js',
            ]));
          }]
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
        mapState: 'hidden',
        resolve: {
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load([
              '/app/modules/contact_us/contact_us.controller.js',
              '/app/models/staticPage.js',
            ]);
          }]
        }
      })
      //Terms page
      .state('terms', {
        url: "/terms",
        templateUrl: '/app/modules/terms/terms.html',
        controller: 'TermsController',
        controllerAs: 'Term',
        parent: 'main',
        mapState: 'hidden',
        resolve: {
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/terms/terms.controller.js',
              '/app/models/staticPage.js',
            ]));
          }]
        }
      })

      //Zoomers page
      .state('zoomers', {
        url: '/zoomers',
        templateUrl: '/app/modules/zoomers/zoomers.html',
        controller: 'ZoomersController',
        controllerAs: 'Zoomers',
        parent: 'main',
        mapState: 'hidden',
        resolve: {
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load([
              '/app/modules/zoomers/zoomers.controller.js',
            ]);
          }]
        }
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
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              'cropper',
              'uploader',
              '/app/modules/settings/settings.controller.js',
            ]));
          }]
        },
        mapState: 'hidden',
        require_auth: true,
        parent: 'main',
        locate: 'none'
      })

      .state('profile', {
        url: '/:user_id',
        template: '<ui-view  />',
        abstract: true,
        resolve: {
          user: function ($rootScope, User, currentUser, $stateParams, UserService) {
            if (currentUser && (currentUser.id == $stateParams.user_id || currentUser.alias == $stateParams.user_id)) {
              return User.currentUser({}, function (user) {
                $rootScope.currentUser = user;
                UserService.setProfileUser(user);
                return user;
              }).$promise;
            } else if ($stateParams.user_id && $stateParams.user_id != 0) {
              return User.get({id: $stateParams.user_id}, function (user) {
                UserService.setProfileUser(user);
                return user;
              }).$promise;
            } else {
              UserService.setProfileUser(currentUser || {});
            }
          }
        },
        parent: 'profile_menu'
      })
      //Bloggers profile page
      .state('profile_blog', {
        url: '/blog',
        templateUrl: '/app/modules/blog/blogger_profile/blogger_profile.html',
        controller: 'BloggerProfileController',
        controllerAs: 'Blog',
        parent: 'profile',
        mapState: 'small',
        resolve: {
          posts: function (Post, $stateParams) {
            return Post.query({user_id: $stateParams.user_id}).$promise;
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/blog/blogger_profile/blogger_profile.controller.js',
            ]));
          }]
        }
      })
      //Show blog location on map and show pop-up
      .state('blog.article', {
        url: '/article/:slug',
        templateUrl: '/app/modules/blog/article/article.html',
        controller: 'ArticleController',
        controllerAs: 'Article',
        mapState: 'small',
        parent: 'profile',
        resolve: {
          article: function (Post, $stateParams) {
            return Post.get({id: $stateParams.slug}).$promise;
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/blog/article/article.controller.js',
              '/app/models/post_comment.js',
            ]));
          }]
        },
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
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/spot/spots/spots.controller.js',
            ]));
          }]
        }
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
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/spot/spot.controller.js',
            ]));
          }]
        },
        locate: 'none',
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
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/planner/plan/plan.controller.js',
              '/app/models/plan_comment.js',
            ]));
          }]
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
        parent: 'profile',
        mapState: 'small',
        resolve: {
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/profile/profile.controller.js',
              '/app/models/wall.js',
            ]));
          }]
        }
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
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/favorites/favorites.controller.js',
            ]));
          }],
        }
      })
      //Photomap view state
      .state('photos', {
        abstract: true,
        template: '<ui-view />',
        parent: 'profile'
      })
      .state('photos.list', {
        url: '/albums',
        templateUrl: '/app/modules/photomap/photomap.html',
        controller: 'PhotomapController',
        controllerAs: 'Photomap',
        parent: 'profile',
        resolve: {
          albums: function (Album, $stateParams) {
            return Album.query({user_id: $stateParams.user_id}).$promise;
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              // '/app/models/album.js',
              '/app/modules/photomap/photomap.controller.js',
            ]));
          }],
        },
        mapState: 'small',
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
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/photomap/edit_photo/edit_photo.controller.js',
            ]));
          }]
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
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              'cropper',
              'uploader',
              '/app/modules/photomap/album/album.controller.js',
            ]));
          }]
        },
        mapState: 'small',
        parent: 'profile',
        locate: 'none'
      })

      .state('followers', {
        url: '/followers',
        templateUrl: '/app/modules/followers/followers.html',
        controller: 'FollowersController',
        controllerAs: 'Follower',
        parent: 'profile',
        locate: 'none',
        mapState: 'small',
        resolve: {
          users: function (User, $stateParams, user) {
            return User.followers({user_id: user.id}).$promise;
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/followers/followers.controller.js',
            ]));
          }]
        }
      })
      .state('followings', {
        url: '/followings',
        templateUrl: '/app/modules/followers/followers.html',
        controller: 'FollowersController',
        controllerAs: 'Follower',
        parent: 'profile',
        locate: 'none',
        mapState: 'small',
        resolve: {
          users: function (User, user) {
            return User.followings({user_id: user.id}).$promise;
          },
          loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            return $ocLazyLoad.load(versionize([
              '/app/modules/followers/followers.controller.js',
            ]));
          }]
        }
      })

    ;

    $urlRouterProvider.otherwise('/');
  }

})();
