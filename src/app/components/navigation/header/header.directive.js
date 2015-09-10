(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('zmHeader', ZoomtivityHeader);

  /** @ngInject */
  function ZoomtivityHeader() {
    var directive = {
      restrict: 'E',
      templateUrl: '/app/components/navigation/header/header.html',
      scope: {
        options: "="
      },
      controller: HeaderController,
      controllerAs: 'Header',
      bindToController: true
    };

    return directive;

    /** @ngInject */
    function HeaderController($state, $rootScope, API_URL) {
      var vm = this;
      vm.$state = $state;
      vm.API_URL = API_URL;

      if (vm.options.snap.disable == "left") {
        vm.toggle = "right";
      } else {
        vm.toggle = "left";
      }

      vm.isRole = function (name) {
        if ($rootScope.currentUser) {
          var roles = _.pluck($rootScope.currentUser.roles, 'name');
          return roles.length > 0 && roles.indexOf(name) >= 0;
        } else {
          return false;
        }
      }
    }
  }

})();
