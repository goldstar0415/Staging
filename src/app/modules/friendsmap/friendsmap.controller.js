(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('FriendsmapController', FriendsmapController);

  /** @ngInject */
  function FriendsmapController(friends, MapService) {
    var vm = this;
    vm.friends = friends;
    initMap();

    function createMarker(iconUrl, title, location) {
      var icon = MapService.CreateCustomIcon(iconUrl, 'custom-map-icons');
      var options = {};

      if(icon) options.icon = icon;
      if(title) options.title = title;

      MapService.CreateMarker(location, options);
    }
    function initMap() {
      for(var k in friends) {
        var obj = friends[k];
        var title = obj.first_name + " " + obj.last_name;
        if(obj.location) {
          createMarker(obj.avatar_url.thumb, title, obj.location);
        }
      }

      if(friends.length > 1) {
        MapService.FitBoundsOfCurrentLayer();
      } else if(friends.length == 1) {
        if(friends[0].location) {
          MapService.GetMap().setView(friends[0].location, 10);
        }
      }
    }
  }
})();
