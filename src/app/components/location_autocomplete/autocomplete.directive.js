(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('location', location);

  /** @ngInject */
  function location(MapService, $http, toastr) {
    return {
      restrict: 'E',
      templateUrl: '/app/components/location_autocomplete/location.html',
      scope: {
        location: '=',
        address: '=',
        bindMarker: '=',
        limit: '=',
        provider: '=',
        onEmpty: '&',
        marker: '=',
        inputPlaceholder: '@'
      },
      link: function autocompleteLink(s, e, a) {
        s.placeHolder = s.inputPlaceholder || "Start typing...";
        var limit = s.limit || 10;
        var searchUrl = 'http://open.mapquestapi.com/nominatim/v1/search.php?format=json&addressdetails=1&limit='+ limit +'&q=';
        var bindMarker = s.bindMarker;
        var provider = s.provider || 'google';
        s.provider = provider;



        if(s.address && s.location) {
          s.viewAddress = s.address;
          moveOrCreateMarker(s.location);
          MapService.GetMap().setView(s.location, 12);
        }
        s.$watch('location', function() {
          if(s.location) {
            moveOrCreateMarker(s.location);
          }
          s.viewAddress = s.address;
        });


        function moveOrCreateMarker(latlng) {
          if(bindMarker) {
            if(s.marker) {
              s.marker.setLatLng(latlng);
            } else {
              s.marker = MapService.CreateMarker(latlng, {draggable: true});
              MapService.BindMarkerToInput(s.marker, function(data) {
                console.log(data, s);
                s.location = data.latlng;
                s.address = data.address;
                s.viewAddress = data.address;
              })
            }
            MapService.GetMap().setView(s.marker.getLatLng(), 12);
          }
        }
        function removeMarker() {
          if(s.marker) {
            MapService.RemoveMarker(s.marker);
            s.marker = null;
          }
        }
        s.onChange = function() {
          if(!s.viewAddress){
            removeMarker();
            s.location = null;
            s.address = '';
            s.onEmpty();
          }
        };
        s.SetCurrentLocation = function() {
          MapService.GetCurrentLocation(function(e) {
            if(e.type == 'locationerror') {
              toastr.error('Geolocation error!');
            } else {
              MapService.GetAddressByLatlng(e.latlng, function(data) {
                s.location = e.latlng;
                s.address = data.display_name;
                s.viewAddress = data.display_name;
                moveOrCreateMarker(e.latlng);
              })
            }
          });
        };
        s.onAutocompleteSelect = function($item, $model, $label) {
          if(provider == 'google') {
            s.location = {lat: $model.geometry.location.lat, lng: $model.geometry.location.lng};
            s.address = $model.formatted_address;
            s.viewAddress = $model.formatted_address;
          } else {
            s.location = {lat: $model.lat, lng: $model.lon};
            s.address = $model.display_name;
            s.viewAddress = $model.display_name;
          }
          //moveOrCreateMarker(s.location);
        };
        s.getData = function(val) {
          if(provider == 'google') {
            return $http.get('//maps.googleapis.com/maps/api/geocode/json', {
              withCredentials: false,
              params: {
                address: val,
                sensor: false
              }
            }).then(function(response){
              return response.data.results.map(function(item){
                return item;
              });
            });
          } else {
            var url = searchUrl + val;
            return $http.get(url, {withCredentials: false}).then(function(response){
              return response.data
            });
          }
        };
      }
    };

  }

})();
