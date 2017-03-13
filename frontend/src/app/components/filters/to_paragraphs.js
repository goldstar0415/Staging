(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .filter('toParagraphs', function () {
      return function (input) {
        var html;

        if (input) {
          html = '<p>';
          html += input.split('\n').join('</p><p>');
          html += '</p>';
          //html = html.replace(/(\<p\>\<\/p\>)/g, '\n');
        }

        return html;
      }
    })

})();


