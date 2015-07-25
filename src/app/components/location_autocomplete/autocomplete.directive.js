(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('location', location);

  /** @ngInject */
  function location(MapService, $http) {
    return {
      restrict: 'E',
      templateUrl: 'app/components/location_autocomplete/location.html',
      scope: {
        location: '=',
        address: '=',
        bindMarker: '=',
        limit: '='
      },
      link: function autocompleteLink(s, e, a) {
        var limit = s.limit || 10;
        var searchUrl = 'http://open.mapquestapi.com/nominatim/v1/search.php?format=json&addressdetails=1&limit='+ limit +'&q=';
        var bindMarker = s.bindMarker || true;
        var marker = null;

        function moveOrCreateMarker(latlng) {
          if(bindMarker) {
            if(marker) {
              marker.setLatLng(latlng);
            } else {
              marker = MapService.CreateMarker(latlng, {draggable: true});
              MapService.BindMarkerToInput(marker, function(data) {
                s.location = data.latlng;
                s.address = data.address;
                s.viewAddress = data.address;
              })
            }
            MapService.GetMap().setView(marker.getLatLng(), 12);
          }
        }
        function removeMarker() {
          if(marker) {
            MapService.RemoveMarker(marker);
            marker = null;
          }
        }

        s.onChange = function() {
          if(!s.viewAddress){
            removeMarker();
          }
        };
        s.SetCurrentLocation = function() {
          MapService.GetCurrentLocation(function(e) {
            MapService.GetAddressByLatlng(e.latlng, function(data) {
              s.location = e.latlng;
              s.address = data.display_name;
              s.viewAddress = data.display_name;
              moveOrCreateMarker(e.latlng);
            })
          });
        };
        s.onAutocompleteSelect = function($item, $model, $label) {
          s.location = {lat: $model.lat, lng: $model.lon};
          s.address = $model.display_name;
          moveOrCreateMarker({lat: $model.lat, lng: $model.lon});
        };
        s.getData = function(val) {
          var url = searchUrl + val;
          return $http.get(url, {withCredentials: false}).then(function(response){
            return response.data
          });
        };
      }
    };

  }

})();
