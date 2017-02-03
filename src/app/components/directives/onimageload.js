(function () {
  'use strict';

  /*
   * Password Recovery Modal
   */
  angular
    .module('zoomtivity')
    .directive('onimageload', onImageLoad);

  /** @ngInject */
  function onImageLoad(API_URL, S3_URL) {
    function getRandomInt(min, max)
    {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }
    return {
      restrict: 'A',
      scope: {
        item: '='
      },
      link: function(scope, element, attrs) {
            element.bind('error', function(){
                var type = scope.item.item.type;
                if (type == 'food' || type== 'shelter') {
                    var max = (type === 'food')?32:84;
                    var imgnum = getRandomInt(0, max);
                    scope.item.image = S3_URL + '/assets/img/placeholders/' + type + '/' + imgnum + '.jpg';
                } else {
                    scope.item.image = API_URL + "/uploads/missings/covers/original/missing.png";
                }
            });
        }
    };
  }

})();