(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('ChatService', ChatService);

  /** @ngInject */
  function ChatService(socket) {
    var dialogs = {data: []},
      messages = {data: []};

    return {
      dialogs: dialogs,
      messages: messages,
      listenDialogs: listenDialogs,
      listenMessages: listenMessages,
      markAsRead: markAsRead,
      deleteMessage: deleteMessage,
      sendMessage: sendMessage
    };

    function listenDialogs() {
      dialogs.data = [];
      socket.emit('dialogs');

      socket.on('dialogs', function (data) {
        console.log(data);

        dialogs.data = data
      });
    }

    function listenMessages(userId) {
      messages.data = [];
      socket.emit('chat', userId);

      socket.on('message:list', function (data) {
        console.log(data);

        messages.data = data;
      });

      socket.on('message:new', function (data) {
        console.log(data);

        messages.data.push(data);
      })
    }

    function sendMessage(message) {
      socket.emit('message:send', message);
    }

    function markAsRead() {
      socket.emit('message:read');
    }

    function deleteMessage(id) {
      socket.emit('message:delete', id);
    }

  }

})();
