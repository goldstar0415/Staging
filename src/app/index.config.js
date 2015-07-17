(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .config(config);

  /** @ngInject */
  function config($logProvider, toastr, cfpLoadingBarProvider, $locationProvider, DEBUG) {
    // Enable log
    $logProvider.debugEnabled(DEBUG);
    if (!DEBUG) {
      $locationProvider.html5Mode({enabled: true, requireBase: false});
    }


    // Set options third-party lib
    toastr.options.timeOut = 3000;
    toastr.options.positionClass = 'toast-top-right';
    toastr.options.preventDuplicates = true;
    toastr.options.progressBar = true;

    // config loading bar
    cfpLoadingBarProvider.includeBar = true;
    cfpLoadingBarProvider.includeSpinner = true;
    cfpLoadingBarProvider.latencyThreshold = 100;
  }

})();
