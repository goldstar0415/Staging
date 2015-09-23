(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ChatController', ChatController);

  /** @ngInject */
  function ChatController(messages, dialogs, Message, ChatService) {
    var vm = this;
    vm.dialogs = messages;
    vm.deleteDialog = deleteDialog;
    ChatService.dialogs = messages;

    function deleteDialog(user_id, idx) {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete dialog?').result.then(function () {
        Message.deleteDialog({user_id: user_id}, function () {
          vm.dialogs.splice(idx, 1);
        });
      });
    }
  }
})();
