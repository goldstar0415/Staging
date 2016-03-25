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
    function upload(url, data, fileFormDataName) {
      fileFormDataName = fileFormDataName || 'files';
      var params = {
        url: url,
        method: 'POST'
      };
      params.data = data;
      if (fileFormDataName == 'cover') {
        params.data[fileFormDataName] = images.files[0];
      } else {
        params.data[fileFormDataName] = images.files;
      }
      console.log(params);
      return Upload.upload(params);
    }

  }

})();
