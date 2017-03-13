(function () {
  'use strict';

  /*
   * Claim listing button
   */
  angular
    .module('zoomtivity')
    .directive('claimSpot', claimSpot);

  /** @ngInject */
  function claimSpot($modal, $rootScope, SignUpService) {
    return {
      restrict: 'EA',
      scope: {
        spot: '=claimSpot'
      },
      link: claimSpotLink
    };

    function claimSpotLink(s, e, a) {
      e.on('click', function () {
        if (!$rootScope.currentUser) {
          SignUpService.openModal('SignUpModal.html');
          return;
        }

        $modal.open({
          templateUrl: '/app/components/claim_spot/claim_spot.html',
          controller: ClaimSpotController,
          controllerAs: 'modal',
          modalClass: 'authentication',
          resolve: {
            spot: function () {
              return s.spot;
            }
          }
        });
      });
    }

    /** @ngInject */
    function ClaimSpotController(Spot, spot, $modalInstance, toastr) {
      var vm = this;

      vm.save = function (form) {
        console.log(form);
        if (form.$valid) {
          spot.isClaimed = true;

          Spot.claim({id: spot.id}, vm, function (resp) {
            toastr.success('Claim successfully sent');
            vm.close();
          }, function (resp) {
            var message = _.map(resp.data, function (arr) {
              return arr[0];
            });
            toastr.error(message.join('<br/>'));
          });
        }
      };

      //close modal
      vm.close = function () {
        $modalInstance.close();
      };
    }
  }

})();
