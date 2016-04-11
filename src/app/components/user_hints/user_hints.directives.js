(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('userHints', userHintsDirective);

  /** @ngInject */
  function userHintsDirective($rootScope, $timeout, dialogs) {
    return {
      restrict: 'EA',
      link: function (scope, elem, attrs) {
        $timeout(function () {
          if (window.localStorage && localStorage.getItem('disable_hints')) {
            $rootScope.hideHints = true;
            return;
          }

          makeHint('#menu_expand', 'EXPAND THE SIDE BAR');
          makeHint('#events_map_icon', 'SELECT YOUR SEARCH CATEGORY', {width: 200});
          makeHint('#weather_map_icon', 'SELECT WEATHER AND THEN CLICK THE MAP TO FIND OUT THE CURRENT WEATHER AND FORECAST', {width: 300});
          makeHint('.radius-selection', 'SEARCH TOOLS:<br/>Search by<br/>Radius,<br/>Custom Area<br/>Road Trip', {
            width: 130,
            offset: {
              y: 5
            }
          });
          makeHint('.unauthorized-menu li a', 'PLEASE SIGN IN OR SIGN UP TO USE THESE AWESOME FEATURES', {
            width: 300,
            position: {
              y: 'bottom'
            },
            outside: 'y',
            trigger: 'click'
          });
        });
      }
    };

    function makeHint(elem, hint, options) {
      options = options || {};

      var tooltip,
      $elem = $(elem), //.on('click', closeAll),
      defaultOptions = {
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


  }
})();
