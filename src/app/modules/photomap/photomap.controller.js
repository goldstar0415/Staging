(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PhotomapController', PhotomapController);

  /** @ngInject */
  function PhotomapController(albums, $stateParams, $state, MapService) {
    var vm = this;
    vm.currentId = $stateParams.user_id;
    vm.albums = albums;

    initMap();

    function createMarker(iconUrl, title, location, data) {
      var icon = MapService.CreateCustomIcon(iconUrl, 'custom-map-icons');
      var options = {};

      if (icon) options.icon = icon;
      if (title) options.title = title;

      var marker = MapService.CreateMarker(location, options);
      console.log(data);
      marker.on('click', function () {
        $state.go('photos.album', {album_id: data.id, user_id: data.user_id});
      });
    }

    function initMap() {
      var count = 0;
      for (var k in albums) {
        if (albums[k].location) {
          count++;
          createMarker(albums[k].cover.medium, albums[k].title, albums[k].location, albums[k]);
        }
      }
      if(count > 0) {
        MapService.FitBoundsOfCurrentLayer();
      }
    }
  }
})();
