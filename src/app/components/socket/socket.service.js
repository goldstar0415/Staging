(function () {
  'use strict';

  /*
   * Service for socket management
   */
  angular
    .module('zoomtivity')
    .factory('socket', function ($rootScope, SOCKET_URL, ChatService) {
      var socket;
      return {
        connect: connect,
        disconnect: disconnect,
        on: onMessage,
        emit: emitMessage
      };

      /*
       * Connect to socket
       */
      function connect(socket_id) {

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
      }

      //disconnect socket
      function disconnect() {
        socket.disconnect();
      }

      //angular wrapper on socket.on
      function onMessage(eventName, callback) {
        socket.on(eventName, function () {
          var args = arguments;
          $rootScope.$apply(function () {
            callback.apply(socket, args);
          });
        });
      }

      //angular wrapper on socket.emit
      function emitMessage(eventName, data, callback) {
        socket.emit(eventName, data, function () {
          var args = arguments;
          $rootScope.$apply(function () {
            if (callback) {
              callback.apply(socket, args);
            }
          });
        })
      }
    });
})();
