(function () {
  'use strict';

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
        maxlength: '='
      },
      templateUrl: '/app/components/ng_input/ng_input.html',
      controller: NgInputController,
      controllerAs: 'NgInput',
      bindToController: true
    };

    /** @ngInject */
    function NgInputController($modal, $scope, $rootScope, $http, API_URL) {
      var vm = this;
      var LINKS_PATERN = /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/=]*)/gi;
      var blackListLinks = [];
      vm.maxlength = vm.maxlength || 5000;
      vm.attachments = {
        photos: [],
        spots: [],
        areas: [],
        links: []
      };

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

      vm.submit = function (form) {
        if (form.$valid) {
          if (blackListLinks.length > 0 && blackListLinks[0].url == vm.message) {
            vm.message = '';
          }

          vm.onSubmit();
          blackListLinks = [];
          form.$submitted = false;
        }
      };

      vm.deletePhoto = function (idx) {
        vm.attachments.photos.splice(idx, 1);
      };

      vm.deleteSpot = function (idx) {
        vm.attachments.spots.splice(idx, 1);
      };

      vm.deleteArea = function (idx) {
        vm.attachments.areas.splice(idx, 1);
      };

      vm.deleteLink = function (idx) {
        var deletedLink = vm.attachments.links.splice(idx, 1);
        blackListLinks.push(deletedLink);
      };

      vm.openPhotosModal = function () {
        $modal.open({
          templateUrl: 'PhotosModal.html',
          controller: 'PhotosModalController',
          controllerAs: 'modal',
          modalContentClass: 'clearfix',
          resolve: {
            albums: function (Album) {
              return Album.query({user_id: $rootScope.currentUser.id}).$promise;
            },
            attachments: function () {
              return vm.attachments;
            }
          }
        });
      };

      vm.openActivityModal = function () {
        $modal.open({
          templateUrl: 'ActivityModal.html',
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
