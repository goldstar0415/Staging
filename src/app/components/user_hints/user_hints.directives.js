(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('userHints', function ($rootScope, $timeout, dialogs) {
      return {
        restrict: 'EA',
        link: function (scope, elem, attrs) {
          if (window.localStorage && localStorage.getItem('disable_hints')) {
            $rootScope.hideHints = true;
            return;
          }

          $('.radius-selection').attr('id', 'radius_map_icon'); //add "id" to radius

          makeHint('menu_expand', 'EXPAND THE SIDE BAR');
          makeHint('events_map_icon', 'SELECT YOUR SEARCH CATEGORY', {width: 200});
          makeHint('weather_map_icon', 'SELECT WEATHER AND THEN CLICK THE MAP TO FIND OUT THE CURRENT WEATHER AND FORECAST', {width: 300});
          makeHint('radius_map_icon', 'SEARCH TOOLS:<br/>Search by<br/>Radius,<br/>Custom Area<br/>Road Trip', {
            width: 130,
            offset: {
              y: 5
            }
          });
        }
      };

      function makeHint(id, hint, options) {
        options = options || {};

        var tooltip,
        $elem = $('#' + id), //.on('click', closeAll),
        defaultOptions = {
          id: id + '_hint',
          theme: 'TooltipBorder',
          width: 150,
          adjustTracker: true,
          closeButton: 'box',
          closeOnMouseleave: true,
          animation: 'move',
          attach: $elem,
          zIndex: 8000,
          position: {
            x: 'left',
            y: 'center'
          },
          outside: 'x',
          pointer: 'top:15',
          content: hint,
          onOpen: function () {
            $('.jBox-closeButton').on('click', function () {
              dialogs.confirm('Confirmation', 'Do you want disable hints?').result.then(disableHints);
            });

            if (window.isHintsDisable) {
              $('.jBox-wrapper').remove();
            }

          }
        };

        angular.extend(defaultOptions, options);
        tooltip = new jBox('Tooltip', defaultOptions);
        //tooltip.open();
      }

      function disableHints() {
        window.isHintsDisable = true;

        if (window.localStorage) {
          localStorage.setItem('disable_hints', true);
        }
      }


    });
})();
