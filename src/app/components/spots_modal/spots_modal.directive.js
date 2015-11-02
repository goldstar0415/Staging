(function () {
  'use strict';

  /*
   * Spots modal
   */
  angular
    .module('zoomtivity')
    .directive('spotsModal', spotsModal);

  /** @ngInject */
  function spotsModal($modal) {
    return {
      restrict: 'E',
      transclude: true,
      templateUrl: '/app/components/spots_modal/spots_modal.html',
      scope: {
        items: '='
      },
      link: SpotsModalLink
    };


    /** @ngInject */
    function SpotsModalLink(scope, element, attrs, ctrl, transclude) {
      transclude(scope, function (clone) {
        element.append(clone);
      });

      //open modal
      element.click(function () {
        $modal.open({
          templateUrl: 'SpotModal.html',
          controller: SpotsModalController,
          controllerAs: 'modal',
          resolve: {
            selectedSpots: function () {
              return scope.items;
            },
            spots: function (Spot, $rootScope) {
              return Spot.query({
                user_id: $rootScope.currentUser.id
              }).$promise;
            }
          }
        });
      });
    }

    /** @ngInject */
    function SpotsModalController(selectedSpots, spots, $modalInstance, Spot) {
      var vm = this;
      vm.spots = spots;
      vm.close = close;
      vm.addSpot = addSpot;

      //close modal
      function close(isSave) {
        if (isSave) {
          _.each(vm.spots, function (spot) {
            if (spot.selected) {
              selectedSpots.push(spot);
            }
          });
        }

        $modalInstance.close();
      }

      //mark as selected spot
      function addSpot(spot) {
        spot.selected = !spot.selected;
      }
    }
  }

})
();
