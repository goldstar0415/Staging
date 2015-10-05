(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PhotomapController', PhotomapController);

  /** @ngInject */
  function PhotomapController(albums, $stateParams, $state, dialogs, toastr, MapService) {
    var vm = this;
    vm.currentId = $stateParams.user_id;
    vm.albums = albums;

    initMap();

    //create marker on map
    function createMarker(iconUrl, title, location, data) {
      var icon = MapService.CreateCustomIcon(iconUrl, 'custom-map-icons');
      var options = {};

      if (icon) options.icon = icon;
      if (title) options.title = title;

      var marker = MapService.CreateMarker(location, options);
      marker.on('click', function () {
        $state.go('photos.album', {album_id: data.id, user_id: data.user_id});
      });
    }

    //show all markers on map
    function initMap() {
      var count = 0;
      for (var k in albums) {
        if (albums[k].location) {
          count++;
          createMarker(albums[k].cover.medium, albums[k].title, albums[k].location, albums[k]);
        }
      }
      if (count > 0) {
        MapService.FitBoundsOfCurrentLayer();
      }
    }

    /*
     * Delete album
     * @param item {Album} album id
     * @param idx {number} album index
     */
    vm.deleteAlbum = function (item, idx) {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete album?').result.then(function () {
        item.$delete(function () {
          toastr.info('Album successfully deleted');
          vm.albums.splice(idx, 1);
        });
      });
    }
  }
})();
