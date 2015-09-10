(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('spotsModal', spotsModal);

  /** @ngInject */
  function spotsModal($modal) {
    return {
      restrict: 'A',
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

      function addSpot(spot) {
        //if (_.findWhere(selectedSpots, {id: spot.id})) {
        //  var idx = _getIndexById(selectedSpots, spot.id);
        //  selectedSpots.splice(idx, 1);
        //} else {
        //  selectedSpots.push(spot);
        //}
        spot.selected = !spot.selected;
      }

      function _markAsSelected(spots) {
        _.each(selectedSpots, function (spot) {
          var item = _.findWhere(spots, {id: spot.id});
          if (item) {
            item.selected = true;
          }
        });

        return spots;
      }

      function _getIndexById(items, id) {
        for (var i = 0; i < items.length; i++) {
          if (items[i].id == id) {
            return i;
          }
        }
        return null;
      }
    }

  }

})
();
