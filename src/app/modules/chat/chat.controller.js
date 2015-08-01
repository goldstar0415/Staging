(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ChatController', ChatController);

  /** @ngInject */
  function ChatController($rootScope, ChatService) {
    var vm = this;
    vm.dialogs = ChatService.dialogs;
    ChatService.listenDialogs();

  }
})();
