(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .filter('htmlToPlaintext', function () {
      return function (input, limit) {
        if (input) {
          input = String(input).replace(/<[^>]+>/gm, '');
          if (limit) {
            input = input.substr(0, limit);
          }
        }
        return input;
      }
    })

})();


