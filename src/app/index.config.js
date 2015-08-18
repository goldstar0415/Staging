(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .config(config)
    .animation('.mapResize', mapResizeAnimation);

  /** @ngInject */
  function config($logProvider, toastr, $httpProvider, cfpLoadingBarProvider, snapRemoteProvider, $locationProvider, DEBUG, $translateProvider) {
    // Enable log
    $logProvider.debugEnabled(DEBUG);
    if (!DEBUG) {
      $locationProvider.html5Mode({enabled: true, requireBase: false});
    }

    $httpProvider.defaults.withCredentials = true;

    // toastr
    toastr.options.timeOut = 3000;
    toastr.options.positionClass = 'toast-top-right';
    toastr.options.preventDuplicates = true;
    toastr.options.progressBar = true;


    // snap
    var disable = "";
    var touchToDrag = false;
    if ($(window).width() < 992) {
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
