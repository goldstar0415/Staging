(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('IndexController', IndexController);

  function IndexController($stateParams, MapService) {
    console.log('Test Controller', $stateParams);

    console.log(MapService);

    var location = {
      lat: $stateParams.spotLocation.latitude,
      lng: $stateParams.spotLocation.longitude
    };

    MapService.FocusMapToGivenLocation(location, 16);

  }

})();
