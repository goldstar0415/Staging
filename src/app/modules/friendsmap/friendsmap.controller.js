(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('FriendsmapController', FriendsmapController);

  /** @ngInject */
  function FriendsmapController(friends, MapService, Friends, CropService) {
    var vm = this;
    var markers = [];
    vm.friends = format(friends);
    initMap();

    function format(friends) {
      return _.each(friends, function (friend) {
        friend.birth_date = moment(friend.birth_date).format('MM.DD.YYYY')
      })
    }
    function createMarker(iconUrl, title, location) {
      var icon = MapService.CreateCustomIcon(iconUrl, 'custom-map-icons');
      var options = {};

      if(icon) options.icon = icon;
      if(title) options.title = title;

      return MapService.CreateMarker(location, options);
    }
    function initMap() {
      for(var k in friends) {
        var obj = friends[k];
        var title = obj.first_name + " " + obj.last_name;
        if(obj.location) {
          var m = createMarker(obj.avatar_url.thumb, title, obj.location);
          markers.push({marker: m, id: obj.id});
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

    vm.setAvatar = function(id, files) {
      if(files.length > 0) {
        CropService.crop(files[0], 512, 512, function(result) {
          if (result) {
            Friends.setAvatar({id: id}, {avatar:result}, function(res) {
              var marker = null;
              var friend = null;

              for(var k in markers) {
                if(markers[k].id == id) {
                  marker = markers[k].marker;
                }
              }

              for(var k in vm.friends) {
                if(vm.friends[k].id == id) {
                  friend = vm.friends[k]
                }
              }

              if(friend) {
                friend.avatar_url.medium = result;
              }

              if(marker) {
                var icon = MapService.CreateCustomIcon(result,'custom-map-icons');
                marker.setIcon(icon);
              }
            });
          }
        });
      }
    };
  }
})();
