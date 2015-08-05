(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ChatController', ChatController);

  /** @ngInject */
  function ChatController(dialogs, ChatService) {
    var vm = this;
    vm.dialogs = dialogs;
    ChatService.dialogs = dialogs;

  }
})();
