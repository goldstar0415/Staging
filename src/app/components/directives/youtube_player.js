(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('youtubePlayer', function ($sce) {
      return {
        restrict: 'EA',
        //replace: true,
        scope: {
          src: '='
        },
        template: '<iframe width="100%" height="100%" ng-src="{{url}}" ng-if="url" style="overflow:hidden;height:100%;width:100%" frameborder="0" allowfullscreen></iframe>',
        link: function (scope, elem, attrs, ctrl) {
          scope.$watch('src', function (val) {
            if (val) {
              var code = youtube_parser(val);
              console.log(code);
              if (code) {
                scope.url = $sce.trustAsResourceUrl("http://www.youtube.com/embed/" + code);
              } else {
                elem.parents('li').hide();
              }
            }
          });
        }

      };
    }
  )
  ;

  function youtube_parser(url) {
    var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
    var match = url.match(regExp);
    if (match && match[7].length == 11) {
      return match[7];
    }

    return null;
  }

})();
