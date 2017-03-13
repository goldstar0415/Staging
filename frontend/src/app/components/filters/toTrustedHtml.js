(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .filter('toTrustedHtml', function ($sce) {
      return function (value) {
        return $sce.trustAsHtml(value);
      };
    });

})();


