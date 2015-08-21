(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('CreateAlbumController', CreateAlbumController);

  /** @ngInject */
  function CreateAlbumController(UploaderService, album, toastr, API_URL, $state) {
    var vm = this;
    vm.images = UploaderService.images;
    vm.album = album;
    vm.privacy = [
      {value: 0, label: 'Public'},
      {value: 1, label: 'Private'}
    ];

    vm.deleteImage = function (idx) {
      vm.images.files.splice(idx, 1);
    };
    vm.createAlbum = function (form) {
      if (form.$valid && vm.images.files.length > 0) {
        var request = {
            title: vm.album.title,
            location: vm.album.location,
            address: vm.album.address,
            is_private: +vm.album.is_private
          },
          url = API_URL + '/albums';

        if (album.id) {
          url = API_URL + '/albums/' + album.id;
          request._method = 'PUT';
        }

        UploaderService
          .upload(url, request)
          .then(function (resp) {
            $state.go('photos.album', {album_id: resp.data.id, user_id: resp.data.user_id});
          })
          .catch(function (resp) {
            toastr.error('Upload failed');
          });
      } else if (vm.images.files.length < 1) {
        toastr.error("You can't save album without images");
      }
    };
  }
})();
