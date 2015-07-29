(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('AlbumController', AlbumController);

  /** @ngInject */
  function AlbumController(album, $state, $rootScope, MapService) {
    var vm = this;
    var photos = album.photos;
    vm.data = album;
    initMap();

    function createMarker(iconUrl, title, location) {
      var icon = MapService.CreateCustomIcon(iconUrl, 'custom-map-icons');
      var options = {};

      if(icon) options.icon = icon;
      if(title) options.title = title;

      MapService.CreateMarker(location, options);
    }
    function initMap() {
      var counter = 0;
      for(var k in photos) {
        var obj = photos[k];
        if(obj.location) {
          counter ++;
          createMarker(obj.photo_url.thumb, '', obj.location);
        }
      }

      if(counter > 1) {
        MapService.FitBoundsOfCurrentLayer();
      } else if(photos.length == 1) {
        if(photos[0].location) {
          MapService.GetMap().setView(photos[0].location, 10);
        }
      }
    }
  }
})();
