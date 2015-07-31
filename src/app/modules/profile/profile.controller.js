(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('ProfileController', ProfileController);

  /** @ngInject */
  function ProfileController($rootScope, user) {
    var vm = this;

    $rootScope.profileUser = user;
  }
})();
