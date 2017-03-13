(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .filter('shortLink', function () {
      return function (input) {
        var link = document.createElement('a');
        link.href = input;
        return link.hostname;
      }
    })

})();


