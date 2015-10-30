(function () {
  'use strict';

  /*
   * Modal for send message to user
   */
  angular
    .module('zoomtivity')
    .directive('sendMessage', SendMessage);

  /** @ngInject */
  function SendMessage() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/send_message/send_message.html',
      controller: SendMessageController,
      controllerAs: 'SendMessage',
      bindToController: true
    };

    /** @ngInject */
    function SendMessageController($modal) {
      var vm = this;

      //open modal
      vm.openSendMessageModal = function () {
        $modal.open({
          templateUrl: 'SendMessageModal.html',
          controller: SendMessageModalController,
          modalClass: 'authentication',
          controllerAs: 'modal'
        });
      };
    }

    /** @ngInject */
    function SendMessageModalController(toastr, $rootScope, Message, $modalInstance) {
      var vm = this;

      //submit form
      vm.send = function () {
        Message.save({
            user_id: $rootScope.profileUser.id,
            message: vm.message || '',
            attachments: {
              album_photos: _.pluck(vm.attachments.photos, 'id'),
              spots: _.pluck(vm.attachments.spots, 'id'),
              areas: _.pluck(vm.attachments.areas, 'id'),
              links: vm.attachments.links
            }
          },
          function success(message) {
            toastr.info('Message sent');

            $modalInstance.close();
          }, function error(resp) {
            toastr.error('Send message failed');
          });
      };

      //close modal
      vm.close = function () {
        $modalInstance.close();
      };
    }

  }

})();
