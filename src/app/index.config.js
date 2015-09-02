(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .config(config)
    .animation('.mapResize', mapResizeAnimation);

  /** @ngInject */
  function config($logProvider, toastr, $httpProvider, cfpLoadingBarProvider, snapRemoteProvider, $locationProvider, DEBUG, JS_CONSOLE_KEY, $translateProvider) {
    // Enable log
    $logProvider.debugEnabled(DEBUG);
    $locationProvider.html5Mode(true);

    $httpProvider.defaults.withCredentials = true;

    // toastr
    toastr.options.timeOut = 1500;
    toastr.options.positionClass = 'toast-top-right';
    toastr.options.preventDuplicates = true;
    toastr.options.progressBar = true;
    toastr.options.onShown = function () {
      //TODO: make smart margin of top
    };


    // snap
    var disable = "";
    var touchToDrag = false;
    var windowWidth = angular.element(window).width();
    if (windowWidth < 992) {
      touchToDrag: true;
      disable = "right";
    } else {
      touchToDrag: false;
      disable = "left";
    }

    snapRemoteProvider.globalOptions = {
      disable: disable,
      hyperextensible: false,
      maxPosition: 230,
      minPosition: -230,
      minDragDistance: 40,
      touchToDrag: touchToDrag,
      tapToClose: true
    };

    // loading bar
    cfpLoadingBarProvider.includeBar = true;
    cfpLoadingBarProvider.includeSpinner = true;
    cfpLoadingBarProvider.latencyThreshold = 100;

    $translateProvider.preferredLanguage('en');
    $translateProvider.translations('en', {
      DIALOGS_YES: 'Yes',
      DIALOGS_NO: 'No'
    });

    //enable js console
    if (DEBUG && windowWidth < 992) {
      var script = angular.element('<script>')
        .attr('src', 'http://jsconsole.com/remote.js?' + JS_CONSOLE_KEY);
      angular.element('head').append(script);
    }
  }

  /** @ngInject */
  function mapResizeAnimation() {
    return {
      setClass: function (element, addedClass, removedClass, doneFn) {
        if (window.map) {
          window.map.invalidateSize();
        }
        doneFn();
      }
    }

  }

})();
