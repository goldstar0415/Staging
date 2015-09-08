(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PhotoEditController', PhotoEditController);

  /** @ngInject */
  function PhotoEditController(photo, user_id, Photo, $state) {
    var vm = this;
    vm.photo = photo;
    vm.address = photo.address;
    vm.location = photo.location;

    vm.save = function() {
        Photo.update({id: photo.id}, {address: vm.address, location: vm.location}, function () {
          $state.go('photos.album', {album_id: photo.album_id, user_id: user_id});
        });
    }
  }
})();
