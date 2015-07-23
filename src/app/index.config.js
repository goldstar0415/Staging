(function() {
  'use strict';

  angular
    .module('zoomtivity')
    .config(config);

  /** @ngInject */
  function config($logProvider, toastr, $httpProvider, cfpLoadingBarProvider, snapRemoteProvider, $locationProvider, DEBUG) {
    // Enable log
    $logProvider.debugEnabled(DEBUG);
    if (!DEBUG) {
      $locationProvider.html5Mode({enabled: true, requireBase: false});
    }

    //fix send cookies cross domain
    $httpProvider.defaults.withCredentials = true;

    // toastr
    toastr.options.timeOut = 3000;
    toastr.options.positionClass = 'toast-top-right';
    toastr.options.preventDuplicates = true;
    toastr.options.progressBar = true;


    // snap
    var disable = "";
    if($(window).width() < 768) {
      disable = "right";
    } else {
      disable = "left";
    }

    snapRemoteProvider.globalOptions = {
      disable: disable,
      hyperextensible: false,
      maxPosition: 230,
      minPosition: -230
    };

    // loading bar
    cfpLoadingBarProvider.includeBar = true;
    cfpLoadingBarProvider.includeSpinner = true;
    cfpLoadingBarProvider.latencyThreshold = 100;
  }

})();
