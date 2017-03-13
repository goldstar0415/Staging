(function () {
  'use strict';

  /*
   * Directive for spot popup on map
   */
  angular
    .module('zoomtivity')
    .directive('spotPopup', spotPopup);

  /** @ngInject */
  function spotPopup() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/map_popups/spot_popup/spot_popup.html',
      controller: 'SpotPopupController',
      controllerAs: 'SpotPopup',
      scope: {
        data: '=spot',
        marker: '='
      }
    };
  }

})();
