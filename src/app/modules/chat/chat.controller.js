(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ChatController', ChatController);

  /** @ngInject */
  function ChatController(dialogs) {
    var vm = this;
    vm.dialogs = dialogs;

  }
})();
