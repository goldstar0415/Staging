(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('share', share);

  /** @ngInject */
  function share() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/share/share.html',
      scope: {
        item: '=',
        type: '@'
      },
      controller: ShareController,
      controllerAs: 'share',
      bindToController: true
    };

    function ShareController($state) {
      var vm = this;
      console.log(vm);
      switch (vm.type) {
        case 'spot':
              vm.text = vm.item.title;
              vm.facebook_url = 'http://api.zoomtivity.com/spots/208/preview';
              vm.url = $state.href('spot', {spot_id: vm.item.id, user_id: vm.item.user_id}, {absolute: true});
              break;
      }
    }

  }
})();
