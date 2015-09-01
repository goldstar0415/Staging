(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .filter('htmlLinky', ['$sanitize', function ($sanitize) {
      var LINKY_URL_REGEXP =
          /((ftp|https?):\/\/|(www\.)|(mailto:)?[A-Za-z0-9._%+-]+@)\S*[^\s.;,(){}<>"\u201d\u2019]/i,
        MAILTO_REGEXP = /^mailto:/i;

      return function (text, target) {
        if (!text) return text;
        var match;
        var raw = text;
        var html = [];
        var url;
        var i;
        while ((match = raw.match(LINKY_URL_REGEXP))) {
          // We can not end in these as they are sometimes found at the end of the sentence
          url = match[0];
          // if we did not match ftp/http/www/mailto then assume mailto
          if (!match[2] && !match[4]) {
            url = (match[3] ? 'http://' : 'mailto:') + url;
          }
          i = match.index;
          addText(raw.substr(0, i));
          addLink(url, match[0].replace(MAILTO_REGEXP, ''));
          raw = raw.substring(i + match[0].length);
        }
        addText(raw);
        return html.join('');

        function addText(text) {
          if (!text) {
            return;
          }
          html.push(text);
        }

        function addLink(url, text) {
          html.push('<a ');
          if (angular.isDefined(target)) {
            html.push('target="',
              target,
              '" ');
          }
          html.push('href="',
            url.replace(/"/g, '&quot;'),
            '">');
          addText(text);
          html.push('</a>');
        }
      };
    }]);

})();


