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
        onFocus: '&'
      },
      templateUrl: 'app/components/ng_input/ng_input.html',
      controller: NgInputController,
      controllerAs: 'NgInput',
      bindToController: true
    };

    /** @ngInject */
    function NgInputController($modal, $rootScope) {
      var vm = this;
      vm.attachments = {
        photos: [],
        spots: [],
        areas: []
      };
      vm.deletePhoto = deletePhoto;

      vm.submit = function (form) {
        if (form.$valid) {
          vm.onSubmit();
        }
      };

      function deletePhoto(idx) {
        vm.attachments.photos.splice(idx, 1);
      }

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
              return Spot.query().$promise;
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
