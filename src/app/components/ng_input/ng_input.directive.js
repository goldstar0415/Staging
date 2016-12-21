(function () {
  'use strict';

  /*
   * Directive to send message with attachments
   */
  angular
    .module('zoomtivity')
    .directive('ngInput', ngInput);

  /** @ngInject */
  function ngInput() {
    return {
      restrict: 'E',
      scope: {
        message: '=',
        attachments: '=',
        onSubmit: '&',
        onFocus: '&',
        maxlength: '=',
        mobile: '='
      },
      templateUrl: '/app/components/ng_input/ng_input.html',
      controller: NgInputController,
      controllerAs: 'NgInput',
      bindToController: true
    };

    /** @ngInject */
    function NgInputController($modal, $scope, $rootScope, toastr, $http, API_URL, $ocLazyLoad) {
      var vm = this;
      var LINKS_PATERN = /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/=]*)/gi;
      var blackListLinks = [];
      vm.attachments = {
        photos: [],
        spots: [],
        areas: [],
        links: []
      };
      //parse link from message and attach the preview
      $scope.$watch('NgInput.message', function (value) {
        if (value) {
          var links = value.match(LINKS_PATERN);
          if (links && links.length > 0) {
            var uniqLinks = _.reject(links, function (link) {
              return _.findWhere(blackListLinks, {url: link});
            });
            if (uniqLinks.length > 0) {
              $http({
                url: API_URL + '/url-parse',
                method: 'GET',
                params: {'links[]': uniqLinks}
              }).success(function (resp) {
                var parsedLinks = resp.data;
                if (resp.result && parsedLinks.length) {
                  _.each(parsedLinks, function (parsedLink) {
                    if (parsedLink.title && !parsedLink.error) {
                      parsedLink.image = parsedLink.images.length ? parsedLink.images[0] : null;
                      delete parsedLink.images;
                      vm.attachments.links.push(parsedLink);
                    }
                  });
                  blackListLinks = _.union(blackListLinks, parsedLinks);
                }
              });
            }
          }
        }
      });

      /*
       * Submit form
       * @param form {ngForm} angular form object
       */
      vm.submit = function (form) {
        if (form.$valid) {
          if (vm.attachments.photos.length > 10) {
            toastr.error('The maximum number of attached photo is 10');
            return;
          }

          if (blackListLinks.length > 0 && blackListLinks[0].url == vm.message) {
            vm.message = '';
          }

          vm.onSubmit();
          blackListLinks = [];
          form.$submitted = false;
        }
      };

      /*
       * Delete photo from attachments
       * @param idx {number}  photo index
       */
      vm.deletePhoto = function (idx) {
        vm.attachments.photos.splice(idx, 1);
      };

      /*
       * Delete spot from attachments
       * @param idx {number}  spot index
       */
      vm.deleteSpot = function (idx) {
        vm.attachments.spots.splice(idx, 1);
      };

      /*
       * Delete area from attachments
       * @param idx {number}  area index
       */
      vm.deleteArea = function (idx) {
        vm.attachments.areas.splice(idx, 1);
      };

      /*
       * Delete link from attachments
       * @param idx {number}  link index
       */
      vm.deleteLink = function (idx) {
        var deletedLink = vm.attachments.links.splice(idx, 1);
        blackListLinks.push(deletedLink);
      };

      vm.getAttachmentsCount = function () {
        return vm.attachments.links.length + vm.attachments.spots.length + vm.attachments.areas.length;
      };

      vm.clearAttachments = function () {
        vm.attachments.photos = [];
        vm.attachments.spots = [];
        vm.attachments.areas = [];
        vm.attachments.links = [];
      };

      //Open modal with user photos
      vm.openPhotosModal = function () {
        $modal.open({
          templateUrl: '/app/components/ng_input/photos_modal.html',
          controller: 'PhotosModalController',
          controllerAs: 'modal',
          modalContentClass: 'clearfix',
          resolve: {
            albums: function (Album) {
              return Album.query({user_id: $rootScope.currentUser.id}).$promise;
            },
            attachments: function () {
              return vm.attachments;
            },
            uploader: ['$ocLazyLoad', function($ocLazyLoad) {
              return $ocLazyLoad.load([
                'cropper',
                'uploader',
              ]);
            }],
          }
        });
      };

      //Open modal with user spots and areas
      vm.openActivityModal = function () {
        $modal.open({
          templateUrl: '/app/components/ng_input/activity_modal.html',
          controller: 'ActivityModalController',
          controllerAs: 'modal',
          //modalContentClass: 'clearfix',
          resolve: {
            spots: function (Spot) {
              return Spot.query({
                user_id: $rootScope.currentUser.id
              }).$promise;
            },
            favorites: function (Spot) {
              return Spot.favorites({
                user_id: $rootScope.currentUser.id
              }).$promise;
            },
            areas: function (Area) {
              return Area.query().$promise;
            },
            attachments: function () {
              return vm.attachments;
            }
          }
        });
      };
    }
  }

})();
