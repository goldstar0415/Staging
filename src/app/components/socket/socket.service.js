(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('socket', function ($rootScope, SOCKET_URL, ChatService) {
      var socket;
      return {
        connect: function (socket_id) {
          socket = io.connect(SOCKET_URL);

          //socket.on('connect', function () {
          //  console.log('Socket connected');
          //});

          socket.on('user.' + socket_id + ':App\\Events\\OnMessage', function (data) {
            ChatService.onNewMessage(data);
          });
          socket.on('user.' + socket_id + ':App\\Events\\OnMessageRead', function (data) {
            ChatService.onReadMessage(data);
          });
        },
        disconnect: function () {
          socket.disconnect();
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
