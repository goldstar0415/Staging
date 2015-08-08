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
    vm.glued = true;
    vm.messages = messages;
    vm.messages.data = ChatService.groupByDate(messages.data);
    vm.sendMessage = sendMessage;
    vm.markAsRead = markAsRead;
    vm.deleteMessage = deleteMessage;


    function sendMessage() {
      Message.save({
          user_id: user.id,
          message: vm.message,
          attachments: {
            album_photos: _.pluck(vm.attachments.photos, 'id'),
            spots: _.pluck(vm.attachments.spots, 'id'),
            areas: _.pluck(vm.attachments.areas, 'id')
          }
        },
        function success(message) {
          message = _.extend(message, message.pivot);
          ChatService.pushToToday(message);
          vm.message = '';
          vm.attachments.photos = [];
          vm.attachments.spots = [];
          vm.attachments.areas = [];
        },
        function error(resp) {
          console.log(resp);
          toastr.error('Send message failed');
        });
    }

    function markAsRead() {
      var countNewMessages = 0;
      angular.forEach(vm.messages.data, function (groupMessages) {
        angular.forEach(groupMessages.messages, function (message) {
          if (!message.is_read && message.receiver_id == $rootScope.currentUser.id) {
            countNewMessages++;
          }
        });
      });

      if (countNewMessages > 0) {
        Message.markAsRead({
            user_id: user.id
          },
          function () {
            ChatService.markAsRead($rootScope.currentUser.id);
          }
        );
      }
    }

    function deleteMessage(id) {
      Message.delete({id: id}, function () {
        angular.forEach(vm.messages.data, function (groupMessages) {
          groupMessages.messages = _.reject(groupMessages.messages, function (item) {
            return item.id == id
          })
        });
      });
    }

  }
})();
