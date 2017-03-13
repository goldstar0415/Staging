(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('GoogleContactsController', GoogleContactsController);

  /** @ngInject */
  function GoogleContactsController(contacts, friends, $modalInstance, API_URL, Friends, $rootScope) {
    var vm = this;
    vm.API_URL = API_URL;
    vm.users = contacts;

    vm.save = function () {
      vm.close();
      // open another modal with following and invitation options, pass selected users
      $rootScope.$broadcast('contacts.import.after', _.filter(vm.users, function(u) { return u.selected; }));
    };

    vm.isAnySelected = function () {
      return _.findWhere(vm.users, {selected: true});
    };

    //close modal
    vm.close = function () {
      $modalInstance.close();
    };

    vm.isDisabled = function(who) {
      who = who || "unknownElementCameIn";
      switch(who) {
        case 'selectAll':
          if (vm.inRequest) {
            return true;
          }
          // if some is not checked selectAll is enabled
          for (var id in vm.users) {
            if (!vm.users[id].selected) {
              // enable button
              return false;
            }
          }
          // all are enabled - cant press selectAll
          return true;
        case 'add':
        case 'selectNone':
          if (vm.inRequest) {
            return true;
          }
          // if someone is checked selectNone is enabled
          for (var id in vm.users) {
            if (vm.users[id].selected) {
              // enable button
              return false;
            }
          }
          // all are enabled - cant press selectNone
          return true;
        default:
          // disable by default
          return true;
      }
    };

    vm.selectNone = function() {
      selectMultiple(false);
    };

    vm.selectAll = function() {
      selectMultiple(true);
    };

    function selectMultiple (boolS) {
      for (var id in vm.users) {
        vm.users[id].selected = boolS;
      }
    }
  }
})();
