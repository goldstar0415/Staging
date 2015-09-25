(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SaveSelectionController', SaveSelectionController);

  /** @ngInject */
  function SaveSelectionController($modalInstance, $scope) {
    $scope.save = function () {
      if ($scope.data.title) {
        $modalInstance.close($scope.data);
      } else {
        //can't save without server;
        toastr.error('Title is required!');
      }
    };

    $scope.cancel = function () {
      $modalInstance.dismiss('cancel');
    };
  }
})();
