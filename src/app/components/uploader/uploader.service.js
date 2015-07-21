(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('UploaderService', UploaderService);

  /** @ngInject */
  function UploaderService(Upload, $q, $timeout, toastr) {
    var images = [];

    return {
      images: images,
      upload: upload
    };

    function upload(url, vm) {
      images.upload = Upload.upload({
        url: url,
        file: images,
        fields: vm,
        fileFormDataName: 'files[]',
        method: 'POST'
      });

      images.upload.then(function (response) {
        $timeout(function () {
          images.result = response.data;
        });
      }, function (response) {
        if (response.status > 0) {
          toastr.error(response.status + ': ' + response.data);
        }
      });

      images.upload.progress(function (evt) {
        console.log(Math.min(100, parseInt(100.0 * evt.loaded / evt.total)));
        // Math.min is to fix IE which reports 200% sometimes
        images.progress = Math.min(100, parseInt(100.0 * evt.loaded / evt.total));
      });

    }

  }

})();
