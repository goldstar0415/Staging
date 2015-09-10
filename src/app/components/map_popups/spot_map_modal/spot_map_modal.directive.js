(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('spotMapModal', spotsModal);

  /** @ngInject */
  function spotsModal($modal) {
    return {
      restrict: 'E',
      scope: {
        data: '=spot',
        marker: '='
      },
      link: SpotsModalLink
    };


    /** @ngInject */
    function SpotsModalLink(scope, element, attrs, ctrl) {
      element.click(function () {
        $modal.open({
          templateUrl: 'SpotMapModal.html',
          controller: 'SpotPopupController',
          controllerAs: 'SpotPopup',
          resolve: {
            data: function () {
              return scope.data;
            },
            marker: function () {
              return scope.marker;
            }
          }
        });
      });
    }
  }

})
();
