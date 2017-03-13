(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .filter('absoluteLink', function () {
      return function (link) {
        if (link.indexOf('http:') === -1 && link.indexOf('https:') === -1) {
          link = 'http://' + link;
        }
        return link;
      }
    })

})();


