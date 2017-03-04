(function () {
  'use strict';

  /*
   * Pictures dummies on load
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
                if (['food', 'shelter', 'event'].indexOf(type) > -1) {
                    var maxImgNums = {
                        food: 32,
                        shelter: 84,
                        event: 100
                    };
                    scope.item.image = S3_URL + '/assets/img/placeholders/' + type + '/' + (id % maxImgNums[type]) + '.jpg';
                } else {
                    scope.item.image = API_URL + "/uploads/missings/covers/original/missing.png";
                }
            });
        }
    };
  }

})();