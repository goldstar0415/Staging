(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('CreateAlbumController', CreateAlbumController);

  /** @ngInject */
  function CreateAlbumController(UploaderService, toastr, API_URL) {
    var vm = this;
    vm.images = UploaderService.images;
    vm.title = "";
    vm.address= "";
    vm.location= null;
    vm.isPrivate = 0;

    vm.deleteImage = function (idx) {
      vm.images.files.splice(idx, 1);
    };

    vm.createAlbum = function (form) {
      if(form.$valid && vm.images.files.length > 0) {
        var request = {
          title: vm.title,
          location: vm.location,
          address: vm.address,
          isPrivate: vm.isPrivate
        };
        UploaderService
          .upload(API_URL + '/albums', 'POST', request)
          .then(function (resp) {
            console.log(resp);
          })
          .catch(function (resp) {
            toastr.error('Upload failed');
          });
      } else if(vm.images.files.length < 1 ) {
        toastr.error("You can't save album without images");
      }
    };
  }
})();
