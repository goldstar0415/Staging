(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('CreateAlbumController', CreateAlbumController);

  /** @ngInject */
  function CreateAlbumController(UploaderService, toastr, API_URL, $state) {
    var vm = this;
    vm.images = UploaderService.images;
    vm.title = "";
    vm.address= "";
    vm.location= null;
    vm.isPrivate = 0;
    vm.edit = $state.current.edit;
    vm.privacy = [
      {value: 0, label: 'Public'},
      {value: 1, label: 'Private'}
    ];

    vm.deleteImage = function (idx) {
      vm.images.files.splice(idx, 1);
    };
    vm.createAlbum = function (form) {
      if(form.$valid && vm.images.files.length > 0) {
        var request = {
          title: vm.title,
          location: vm.location,
          address: vm.address,
          is_private: vm.isPrivate
        };
        UploaderService
          .upload(API_URL + '/albums', 'POST', request)
          .then(function (resp) {
            $state.go('album', {album_id: resp.data.album_id});
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
