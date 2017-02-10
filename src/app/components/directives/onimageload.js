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
    return {
      restrict: 'A',
      scope: {
        item: '='
      },
      link: function(scope, element, attrs) {
            element.bind('error', function(){
                var type = (scope.item.item.type).toLowerCase();
                var id = (scope.item.item.spot_id) ? scope.item.item.spot_id : scope.item.item.id;
                if (type == 'food' || type== 'shelter') {
                    var max = (type === 'food')?32:84;
                    scope.item.image = S3_URL + '/assets/img/placeholders/' + type + '/' + (id % max) + '.jpg';
                } else {
                    scope.item.image = API_URL + "/uploads/missings/covers/original/missing.png";
                }
            });
        }
    };
  }

})();