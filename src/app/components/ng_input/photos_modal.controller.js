(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PhotosModalController', PhotosModalController);

  /** @ngInject */
  function PhotosModalController(Album, albums, attachments, $modalInstance) {
    var vm = this;
    vm.albums = albums;
    vm.attachments = attachments;

    vm.selectAlbum = function (id) {
      vm.selectedAlbum =  Album.get({id: id}, function (album) {
        album.photos = _.filter(album.photos, function (photo) {
          return !_.findWhere(vm.attachments.photos, {id: photo.id});
        });
        return album;
      });
    };

    vm.addPhoto = function (idx) {
      var photo = vm.selectedAlbum.photos.splice(idx, 1);
      vm.attachments.photos.push(photo[0]);
      console.log(photo, vm.attachments.photos);
    };
  }
})();
