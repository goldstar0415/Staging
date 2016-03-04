(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('spotType', function () {
      return {
        restrict: 'EA',
        replace: true,
        scope: {
          name: '=type'
        },
        template: '<span class="spot-type {{ spotTypeClass }}">{{ spotTypeName }}</span>"',
        link: function (s, e, a) {
          s.spotTypeName = s.name;

          switch (s.name) {
            case 'Event':
              s.spotTypeClass = 'event-spot-page';
              break;
            case 'To-Do':
              s.spotTypeClass = 'recreation-spot';
              break;
            case 'Food':
              s.spotTypeClass = 'pitstop-spot';
              break;
            case 'Shelter':
              s.spotTypeClass = 'recreation-spot';
              break;
          }
        }
      }
    }
  )
  ;

})();
