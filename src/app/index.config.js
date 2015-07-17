(function() {
  'use strict';

  angular
    .module('zoomtivity')
    .config(config);

  /** @ngInject */
  function config($logProvider, toastr, snapRemoteProvider) {
    // Enable log
    $logProvider.debugEnabled(true);

    // Set options third-party lib
    toastr.options.timeOut = 3000;
    toastr.options.positionClass = 'toast-top-right';
    toastr.options.preventDuplicates = true;
    toastr.options.progressBar = true;

    snapRemoteProvider.globalOptions = {
      disable: 'right',
      hyperextensible: false
    }
  }

})();
