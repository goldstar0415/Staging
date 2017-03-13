(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('userHints', userHintsDirective);

  /** @ngInject */
  function userHintsDirective($rootScope, $timeout, dialogs, $ocLazyLoad) {
    return {
      restrict: 'EA',
      link: function () {
		  /** @todo https://trello.com/c/p5ZPBWsV Hints are disabled */
		  return;
        $timeout(function () {
          if (window.localStorage && localStorage.getItem('disable_hints')) {
            window.isHintsDisable = true;
            $rootScope.hideHints = true;
          }

          makeHint('#menu_expand', 'EXPAND THE SIDE BAR');
          makeHint('.close-popup', 'MINIMIZE SIDE BAR');
          makeHint('#events_map_icon', 'SELECT YOUR SEARCH CATEGORY', {width: 200});
          makeHint('#weather_map_icon', 'SELECT WEATHER AND THEN CLICK THE MAP TO FIND OUT THE CURRENT WEATHER AND FORECAST', {width: 300});
          makeHint('.radius-selection', 'SEARCH TOOLS:<br/>Search by<br/>Radius,<br/>Custom Area,<br/>Road Trip', {
            width: 130,
            offset: {
              y: 5
            }
          });
          makeHint('#filters-button-open', 'ADD FILTER', {
            position: {
              y: 'top'
            },
            outside: 'y'
          });
          makeHint('#btn-cancel', 'Remove All Filters', {
            position: {
              y: 'bottom'
            },
            outside: 'y'
          });
          makeHint('.icon-minus', 'Remove Filters', {
            position: {
              y: 'bottom'
            },
            outside: 'y'
          });

          makeHint('.unauthorized-menu li a', 'PLEASE SIGN IN OR SIGN UP TO USE THESE AWESOME FEATURES', {
            width: 300,
            position: {
              y: 'bottom'
            },
            closeButton: false,
            outside: 'y',
            trigger: 'click'
          }, true);
        });
      }
    };

    function makeHint(elem, hint, options, isAlwaysOpen) {
      options = options || {};

      var tooltip,
      $elem = $(elem),
      defaultOptions = {
        theme: 'TooltipBorder',
        width: 150,
        adjustTracker: true,
        closeButton: 'box',
        closeOnMouseleave: true,
        closeOnClick:true,
        closeOnEsc: true,
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
          $('.jBox-closeButton').off('click').on('click', function () {
            dialogs.confirm('Confirmation', 'Do you want disable hints?').result.then(disableHints);
          });

          if (!isAlwaysOpen && window.isHintsDisable) {
            tooltip.close();
            tooltip.disable();
          }

          if(window.tooltipTimeoutId) {
            clearTimeout(window.tooltipTimeoutId);
          }
          window.tooltipTimeoutId = window.setTimeout(function(){tooltip.close();},3000);
        }
      };

      angular.extend(defaultOptions, options);

      if ($ocLazyLoad.isLoaded('jbox')) {
        _tooltip();
      } else {
        $ocLazyLoad.load('jbox').then(_tooltip);
      }

      function _tootip() {
        tooltip = new jBox('Tooltip', defaultOptions);
      }
    }

    function disableHints() {
      window.isHintsDisable = true;

      if (window.localStorage) {
        localStorage.setItem('disable_hints', true);
      }
    }
  }
})();
