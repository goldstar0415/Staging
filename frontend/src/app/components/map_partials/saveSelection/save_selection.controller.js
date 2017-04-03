(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SaveSelectionController', SaveSelectionController);

  /** @ngInject */
  function SaveSelectionController($modalInstance, $scope, MapService, $location) {
    $scope.data = {
        title: '',
        description: '',
        link: '',
        saveClicked: false
    };
    
    var setLink = function(hash) {
        $scope.data.link = window.location.origin + '/areas/' + hash;
    };
    
    $scope.save = function () {
      if ($scope.data.title && !$scope.data.saveClicked) {
        $scope.data.saveClicked = true;
        MapService.SaveSelections($scope.data.title, $scope.data.description, setLink);
      } 
      else if (!$scope.data.title && !$scope.data.saveClicked)
      {
        toastr.error('Title is required!');
      }
    };
    
    $scope.clipboardClick = function() {
        if($scope.data.link !== '')
        {
            toastr.success('Copied to clipboard!');
        }
    }

    $scope.cancel = function () {
      $modalInstance.dismiss('cancel');
    };
  }
})();
