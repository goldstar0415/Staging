(function () {
  'use strict';

  /*
   * New message popup from sockets
   */
  angular
    .module('zoomtivity')
    .directive('newMessage', newMessage);

  /** @ngInject */
  function newMessage() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/new_message/new_message.html',
      controller: NewMessageController,
      controllerAs: 'Message',
      bindToController: true
    };

    /** @ngInject */
    function NewMessageController(NewMessageService, $state) {
      var vm = this;

      vm.message = NewMessageService.message;
      vm.redirectToMessage = redirectToMessage;

      //redirect user to chat room
      function redirectToMessage() {
        vm.message.visible = false;
        $state.go('chatRoom', {user_id: vm.message.data.user.id});
      }
    }
  }

})();
