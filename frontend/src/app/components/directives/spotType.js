(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('spotType', function () {
      return {
        restrict: 'EA',
        replace: true,
        scope: {
          name: '=type',
          class: '='
        },
        template: '<span ng-class="spotTypeClass">{{ spotTypeName }}</span>"',
        link: function (s, e, a) {
          s.spotTypeName = s.name;
          s.spotTypeClass = s.class || 'spot-type';

          switch (s.name) {
            case 'Event':
              s.spotTypeClass += ' event-spot-page';
              break;
            case 'To-Do':
              s.spotTypeClass += ' recreation-spot';
              break;
            case 'Food':
              s.spotTypeClass += ' pitstop-spot';
              break;
            case 'Shelter':
              s.spotTypeClass += ' recreation-spot';
              break;
          }
        }
      }
    }
  )
  ;

})();
