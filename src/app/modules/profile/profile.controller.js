(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ProfileController', ProfileController);

  /** @ngInject */
  function ProfileController($rootScope, user, wall) {
    var vm = this;
    vm.wall = wall;

    $rootScope.profileUser = user;
  }
})();
