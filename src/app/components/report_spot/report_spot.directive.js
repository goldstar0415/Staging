(function () {
  'use strict';

  /*
   * New message popup from sockets
   */
  angular
    .module('zoomtivity')
    .directive('reportSpot', reportSpot);

  /** @ngInject */
  function reportSpot($modal) {
    return {
      restrict: 'EA',
      scope: {
        spot: '=reportSpot'
      },
      link: reportSpotLink
    };

    function reportSpotLink(s, e, a) {
      e.on('click', function () {
        $modal.open({
          templateUrl: '/app/components/report_spot/report_spot.html',
          controller: ReportSpotController,
          controllerAs: 'modal',
          resolve: {
            spot: function () {
              return s.spot;
            }
          }
        });
      });
    }

    /** @ngInject */
    function ReportSpotController(Spot, spot, $modalInstance, toastr) {
      var vm = this;

      vm.reasons = [
        {value: 0, label: 'Wrong Information'},
        {value: 1, label: 'Inappropriate Content'},
        {value: 2, label: 'Duplicate Data'},
        {value: 3, label: 'Spam'},
        {value: 4, label: 'Other'}
      ];

      vm.save = function (form) {
        if (form.$valid && vm.reason) {
          spot.isReported = true;
          vm.close();
          Spot.report({id: spot.id}, {
            reason: vm.reason,
            description: vm.description
          }, function (resp) {
            toastr.success('Report successfully sent')
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
