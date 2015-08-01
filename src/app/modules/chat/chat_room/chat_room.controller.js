(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ChatRoomController', ChatRoomController);

  /** @ngInject */
  function ChatRoomController($rootScope, user, ChatService) {
    var vm = this;

    vm.user = user;
    vm.message = '';
    vm.messages = ChatService.messages;
    vm.sendMessage = sendMessage;
    vm.markAsRead = markAsRead;
    vm.deleteMessage = ChatService.deleteMessage;

    ChatService.listenMessages();

    function sendMessage(form) {
      console.log(form);
      if (form.$valid) {
        ChatService.sendMessage({
          user_id: $rootScope.currentUser.id,
          message: vm.message
        });

        vm.message = '';
      }
    }

    function markAsRead() {
      var countNew = _.where(vm.messages.data, {is_read: false});
      if (countNew.length > 0) {
        ChatService.markAsRead();
      }
    }

  }
})();
