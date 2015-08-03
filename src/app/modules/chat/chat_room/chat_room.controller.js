(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ChatRoomController', ChatRoomController);

  /** @ngInject */
  function ChatRoomController($rootScope, user, messages, Message, toastr, ChatService) {
    var vm = this;

    vm.user = user;
    vm.message = '';
    vm.messages = ChatService.groupByDate(messages);
    vm.sendMessage = sendMessage;
    vm.markAsRead = markAsRead;
    vm.deleteMessage = deleteMessage;


    function sendMessage(form) {
      console.log(form);
      if (form.$valid) {
        Message.save({
            user_id: user.id,
            message: vm.message
          },
          function success(message) {
            vm.messages.data.push(message);
            vm.message = '';
          },
          function error(resp) {
            console.log(resp);
            toastr.error('Send message failed');
          });

      }
    }

    function markAsRead() {
      var countNewMessages = _.where(vm.messages.data, {is_read: false});
      if (countNewMessages.length > 0) {
        Message.markAsRead({user_id: user.id}, function () {
          angular.forEach(vm.messages.data, function (message) {
            if (message.pivot.receiver_id == $rootScope.currentUser.id) {
              message.is_read = true;
            }
          })
        });
      }
    }

    function deleteMessage(idx) {
      Message.delete({id: vm.messages.data[idx].id}, function () {
        vm.messages.data.splice(idx, 1);
      });
    }

  }
})();
