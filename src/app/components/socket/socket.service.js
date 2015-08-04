(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('socket', function ($rootScope, SOCKET_URL, ChatService) {
      var socket;
      return {
        connect: function (socket_id) {
          socket = io.connect(SOCKET_URL);

          socket.on('connect', function () {
            console.log('Socket connected');
          });

          socket.on('user.' + socket_id + ':App\\Events\\OnMessage', function (data) {
            console.log('new message ', data);
            ChatService.onNewMessage(data);
          });
          socket.on('user.' + socket_id + ':App\\Events\\OnReadMessage', function (data) {
            console.log('read message ', data);
            ChatService.onReadMessage(data);
          });
        },
        disconnect: function () {
          socket.disconnect();
          console.log('Socket disconnected');
        },
        on: function (eventName, callback) {
          socket.on(eventName, function () {
            var args = arguments;
            $rootScope.$apply(function () {
              callback.apply(socket, args);
            });
          });
        },
        emit: function (eventName, data, callback) {
          socket.emit(eventName, data, function () {
            var args = arguments;
            $rootScope.$apply(function () {
              if (callback) {
                callback.apply(socket, args);
              }
            });
          })
        }
      };
    });
})();
