(function () {
  'use strict';

  /*
   * Service to show newMessage directive
   */
  angular
    .module('zoomtivity')
    .factory('NewMessageService', NewMessageService);

  /** @ngInject */
  function NewMessageService($timeout) {
    var message = {},
      VISIBLE_TIMEOUT = 4000;

    return {
      message: message,
      show: show
    };

    function show(data) {
      message.data = data;
      message.visible = true;

      $timeout(function () {
        message.visible = false;
      }, VISIBLE_TIMEOUT);
    }

  }

})();
