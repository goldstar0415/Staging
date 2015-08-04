(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ChatRoomController', ChatRoomController);

  /** @ngInject */
  function ChatRoomController($rootScope, user, messages, Message, toastr, $location, $anchorScroll, ChatService) {
    var vm = this;

    vm.user = user;
    vm.message = '';
    vm.messages = messages;
    vm.messages.data = ChatService.groupByDate(messages.data);
    vm.sendMessage = sendMessage;
    vm.markAsRead = markAsRead;
    vm.deleteMessage = deleteMessage;


    function sendMessage(form) {
      if (form.$valid) {
        Message.save({
            user_id: user.id,
            message: vm.message
          },
          function success(message) {
            ChatService.pushToToday(message);
            vm.message = '';
          },
          function error(resp) {
            console.log(resp);
            toastr.error('Send message failed');
          });

      }
    }

    function markAsRead() {
      ChatService.markAsRead(user.id);
    }

    function deleteMessage(idx) {
      Message.delete({id: vm.messages.data[idx].id}, function () {
        vm.messages.data.splice(idx, 1);
      });
    }

  }
})();
