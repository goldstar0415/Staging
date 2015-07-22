(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('UploaderService', UploaderService);

  /** @ngInject */
  function UploaderService(Upload, $q, $timeout, toastr) {
    var images = {files: []};

    return {
      images: images,
      upload: upload
    };

    //@return $promise
    function upload(url, method, data) {
      return Upload.upload({
        url: url,
        file: images.files,
        fields: data,
        fileFormDataName: 'files[]',
        method: method
      });
    }

  }

})();
