(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('AlbumController', AlbumController);

  /** @ngInject */
  function AlbumController(album, photos, $rootScope, MapService, dialogs, toastr, $state, Album, Photo, CropService, User) {
    var vm = this;
    vm.photos = photos;
    vm.album = album;
    var markers = [];


    initMap();

    //create marker on map
    function createMarker(iconUrl, title, location) {
      var icon = MapService.CreateCustomIcon(iconUrl, 'album');
      var options = {};

      if (icon) options.icon = icon;
      if (title) options.title = title;

      return MapService.CreateMarker(location, options);
    }

    //initialize marker on map
    function initMap() {
      var counter = 0;
      for (var k in photos) {
        var obj = photos[k];
        if (obj.location) {
          counter++;
          var m = createMarker(obj.photo_url.thumb, '', obj.location);
          markers.push({marker: m, photo_id: obj.id});
        }
      }

      if (counter > 1) {
        MapService.FitBoundsOfCurrentLayer();
      } else if (photos.length == 1) {
        if (photos[0].location) {
          MapService.GetMap().setView(photos[0].location, 10);
        }
      }
    }

    /*
     * Set photo as avatar
     * @param image {string} image object
     */
    vm.setAsAvatar = function (image) {
      CropService.crop(image, 512, 512, true, function (result) {
        if (result) {
          User.setAvatar({}, {avatar: result},
            function (user) {
              $rootScope.currentUser.avatar_url = user.avatar_url;
              toastr.success('Avatar changed');
            });
        }
      });
    };

    /*
     * Delete photo
     * @param id {number} photo id
     * @param idx {number} photo index
     */
    vm.deletePhoto = function (id, idx) {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete photo?').result.then(function () {
        Photo.delete({id: id}, function () {
          vm.photos.splice(idx, 1);
          for (var k in markers) {
            if (markers[k].photo_id == id) {
              MapService.RemoveMarker(markers[k].marker);
            }
          }
        });
      });
    };

    /*
     * Delete album
     */
    vm.deleteAlbum = function () {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete album?').result.then(function () {
        album.$delete(function () {
          toastr.info('Album successfully deleted');
          $state.go('photos.list', {user_id: $rootScope.currentUser.id});
        });
      });
    }
  }
})();
