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
        onSubmit: '&',
        onFocus: '&'
      },
      templateUrl: 'app/components/ng_input/ng_input.html',
      controller: NgInputController,
      controllerAs: 'NgInput',
      bindToController: true
    };

    /** @ngInject */
    function NgInputController($modal) {
      var vm = this;
      vm.message.attachments = {};

      vm.submit = function (form) {
        if (form.$valid) {
          vm.onSubmit();
        }
      };

      vm.openPhotosModal = function () {
        $modal.open({
          templateUrl: 'PhotosModal.html',
          controller: PhotosModalController,
          controllerAs: 'modal',
          modalContentClass: 'clearfix'
        });
      };

      vm.openActivityModal = function () {

      }
    }

    function PhotosModalController(toastr, $rootScope, Message, $modalInstance) {
      var vm = this;

      vm.save = function (form) {
        if (form.$valid) {
          Message.save({
              user_id: $rootScope.profileUser.id,
              message: vm.message
            },
            function success(message) {
              toastr.info('Message sent');

              $modalInstance.close();
            }, function error(resp) {
              console.log(resp);
              toastr.error('Send message failed');
            });
        }
      };

      vm.close = function () {
        $modalInstance.close();
      };
    }

  }

})();
