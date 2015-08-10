(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('spotPopup', spotPopup);

  /** @ngInject */
  function spotPopup() {
    return {
      restrict: 'E',
      templateUrl: 'app/components/spot_popup/spot_popup.html',
      //template: '<p>test</p>',
      controller: SpotPopupController,
      controllerAs: 'SpotPopup',
      scope: {
        spot: '='
      }
    };
  }

  function SpotPopupController($scope) {

  }
})();
