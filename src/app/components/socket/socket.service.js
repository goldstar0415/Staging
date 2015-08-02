(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('socket', function ($rootScope, SOCKET_URL) {
      var socket;
      return {
        connect: function (userId) {
          socket = io.connect(SOCKET_URL);

          socket.on('connect', function () {
            console.log('connect', userId);
          });
          socket.emit('initUser', userId);
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
