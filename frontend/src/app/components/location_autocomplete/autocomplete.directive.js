(function () {
  'use strict';

  /*
   * Autocomplete locations
   */
  angular
    .module('zoomtivity')
    .directive('location', location);

  /** @ngInject */
  function location(MapService, $http, toastr, $rootScope, API_URL) {
    return {
      restrict: 'E',
      templateUrl: '/app/components/location_autocomplete/location.html',
      scope: {
        location: '=',
        address: '=',
        bindMarker: '=',
        limit: '=',
        showUserLocation: '=',
        provider: '=',
        onEmpty: '&',
        marker: '=',
        inputPlaceholder: '@',
        addClassOnchange: '=',
        inputClass: '@',
        customClasses: '@',
        pls: '='
      },
      link: function autocompleteLink(s, e, a) {
        var className = s.inputClass || 'location-changed';
        var limit = s.limit || 10;
        var searchUrl = API_URL + '/geocoder/search?addressdetails=1&limit=' + limit + '&q=';
        var bindMarker = s.bindMarker;
        var provider = s.provider || 'google';

        s.placeHolder = s.inputPlaceholder || s.pls || "Type location or click on map...";
        s.provider = provider;
        s.className = '';
        s.viewAddress = '';
        s.validateField = validateField;
        s.onChange = onChange;
        s.SetCurrentLocation = SetCurrentLocation;
        s.onAutocompleteSelect = onAutocompleteSelect;
        s.getData = getData;

        //if change location move or create marker
        s.$watch('location', watchLocation);
        e.on('focusin', onFocusin);

        if (s.address && s.location) {
          s.viewAddress = s.address;
          moveOrCreateMarker(s.location);
          MapService.GetMap().setView(s.location, 12);
        }


        function watchLocation() {
          if (s.location) {
            moveOrCreateMarker(s.location);
          }
          s.viewAddress = s.address;
        }

        function onFocusin() {
          if (!s.location) {
            MapService.GetMap().on('click', onMapClick);
          }
        }

        function onMapClick(event) {
          if (!s.location) {
            s.location = event.latlng;

            moveOrCreateMarker(event.latlng);

            MapService.GetAddressByLatlng(event.latlng, function (data) {
              s.address = data.display_name;
              s.viewAddress = data.display_name;
            });
          }

          MapService.GetMap().off('click');
        }


        /*
         * Move or marker on map by latitude and longitude
         * @param {object} latitude and longitude
         */
        function moveOrCreateMarker(latlng) {
          if (bindMarker) {
            if (s.marker) {
              s.marker.setLatLng(latlng);
            } else {
              createMarker(latlng);
            }
            MapService.GetMap().setView(s.marker.getLatLng());
          }
        }

        /*
         * Create marker on map by latitude and longitude
         * @param {object} latitude and longitude
         */
        function createMarker(latlng) {
          s.marker = MapService.CreateMarker(latlng, {draggable: true});
          MapService.BindMarkerToInput(s.marker, function (data) {
            s.location = data.latlng;
            s.address = data.address;
            s.viewAddress = data.address;
          });
        }

        /*
         * Remove marker
         */
        function removeMarker() {
          if (s.marker) {
            MapService.RemoveMarker(s.marker);
            s.marker = null;
          }
        }

        /*
         * On change address in input
         */
        function onChange() {
          if (!s.viewAddress) {
            removeMarker();
            s.location = null;
            s.address = '';
            s.onEmpty();
          } else {
            s.address = s.viewAddress;
            if (s.addClassOnchange) {
              s.className = className;
            }
          }
        }

        /*
         * Set user default location
         */
        function SetCurrentLocation() {
          if (!$rootScope.currentLocation) {
            toastr.error('Geolocation error!');
          } else {
            MapService.GetAddressByLatlng($rootScope.currentLocation, function (data) {
              s.location = e.latlng;
              s.address = data.display_name;
              s.viewAddress = data.display_name;
              moveOrCreateMarker(e.latlng);
            })
          }
        }

        function onAutocompleteSelect($item, $model, $label) {
          if (provider == 'google') {
            s.location = {lat: $model.geometry.location.lat, lng: $model.geometry.location.lng};
            s.address = $model.formatted_address;
            s.viewAddress = $model.formatted_address;
          } else {
            s.location = {lat: $model.lat, lng: $model.lon};
            s.address = $model.display_name;
            s.viewAddress = $model.display_name;
          }
        }

        /*
         * Get addresses from google api
         */
        function getData(val) {
          if (provider == 'google') {
            return $http.get('//maps.googleapis.com/maps/api/geocode/json', {
              withCredentials: false,
              params: {
                address: val,
                sensor: false
              }
            }).then(function (response) {
              return response.data.results.map(function (item) {
                return item;
              });
            });
          } else {
            var url = searchUrl + val;
            return $http.get(url, {withCredentials: false}).then(function (response) {
              return response.data
            });
          }
        }

        function validateField() {
          if (!s.location) {
            s.address = '';
            s.viewAddress = '';
          }
        }
      }
    };

  }

})();
