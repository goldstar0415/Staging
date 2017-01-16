(function() {
  'use strict';
  angular.module('zoomtivity').directive('locationBloodhound', LocationBloodhound);

  /** @ngInject */
  function LocationBloodhound() {

    return {
      restrict: 'A',
      scope: {
        location: '=',
        address: '=',
        bindMarker: '=',
        limit: '=',
        marker: '=',
        provider: '=',
      },
      controller: controller,
      link: link,
    };

    /**
     * Controller
     */
    function controller($scope, toastr, $rootScope, MapService, $interval) {

      var vm = $scope;
      vm.location = {lat: '', lng: ''};

      vm.$on('typeahead:selected', onTypeaheadSelect);
      vm.$watch('location', watchLocation);

      (function() {
        var stop = $interval(function() {
          if (vm.$$input) {
            vm.$$input.$element.on('focusin', onFocusin);
            $interval.cancel(stop);
          }
        }, 100);
      })();

      function onTypeaheadSelect($event, $model) {
        console.log('Ctrl typeahead selected', $event, $model);
        var viewAddress;
        if (provider == 'google') {
          vm.location = {lat: $model.geometry.location.lat, lng: $model.geometry.location.lng};
          vm.address = $model.formatted_address;
          viewAddress = $model.formatted_address;
        }
        else {
          vm.location = {lat: $model.lat, lng: $model.lon};
          vm.address = $model.display_name;
          viewAddress = $model.display_name;
        }

        display(viewAddress);
      }

      function onFocusin() {
        console.log('onFocusin location', vm.location);
        if (!validateLocation(vm.location)) {
          console.log('Click me pls', vm.location);
          MapService.GetMap().on('click', onMapClick);
        }
      }

      function onMapClick(event) {
        if (!validateLocation(vm.location)) {
          vm.location = event.latlng;

          moveOrCreateMarker(event.latlng);

          MapService.GetAddressByLatlng(event.latlng, function (data) {
            vm.address = data.display_name;
            display(data.display_name);
          });
        }

        MapService.GetMap().off('click');
      }

      function watchLocation() {
        if (validateLocation(vm.location)) {
          moveOrCreateMarker(vm.location);
        }
        display(vm.address);
      }

      function setCurrentLocation() { // todo
        if (!$rootScope.currentLocation) {
          toastr.error('Geolocation error!');
        } else {
          MapService.GetAddressByLatlng($rootScope.currentLocation, function (data) {
            vm.location = vm.$$input.$element.latlng;
            vm.address = data.display_name;
            display(data.display_name);
            moveOrCreateMarker(e.latlng);
          })
        }
      }

      function moveOrCreateMarker(latlng) {
        if (vm.bindMarker) {
          if (vm.marker) {
            vm.marker.setLatLng(latlng);
          } else {
            createMarker(latlng);
          }
          MapService.GetMap().setView(vm.marker.getLatLng());
        }
      }

      function createMarker(latLng) {
        vm.marker = MapService.CreateMarker(latLng, {draggable: true});
        MapService.BindMarkerToInput(vm.marker, function (data) {
          vm.location = data.latlng;
          vm.address = data.address;
          display(data.address);
        });
      }

      function removeMarker() { // todo
        if (vm.marker) {
          MapService.RemoveMarker(vm.marker);
          vm.marker = null;
        }
      }

      function display(val) {
        if (vm.$$input) {
          vm.$$input.$setModelValue(val);
        }
      }

      function validateLocation(location) {
        return location && location.lat && (location.lat+'').trim() != '' && location.lng && (location.lng+'').trim() != '';
      }

      // function onChange() { // todo
      //   if (!s.viewAddress) {
      //     removeMarker();
      //     s.location = null;
      //     s.address = '';
      //     s.onEmpty();
      //   } else {
      //     s.address = s.viewAddress;
      //     if (s.addClassOnchange) {
      //       s.className = className;
      //     }
      //   }
      // }

    }

    /**
     * Link
     */
    function link(scope, elem, attrs) {

      var limit = scope.limit || 10;
      var provider = scope.provider || 'google';

      var URL_MAP_QUEST = 'http://open.mapquestapi.com/nominatim/v1/search.php?format=json&addressdetails=1&limit=' + limit + '&q=%QUERY%';
      var URL_GOOGLE_MAPS = 'https://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=%QUERY%';

      var bhSource = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
          url: apiResolver(),
          wildcard: '%QUERY%',
          prepare: prepareQuery,
          transform: transformResponse,
        }
      });

      var suggestionTemplate = "<div><span class='title'>%VALUE%</span></div>";
      var showPreloader = true;

      elem
        .typeahead(null, {
          name: 'bloodhound-typeahead',
          display: 'value',
          source: bhSource,
          limit: limit,
          templates: {
            suggestion: compileSuggestionTemplate,
          }
        })
        .bind('typeahead:selected', function(event, datum) {
          showPreloader = false; // don't display a preloader when we've selected an item
          var t = setInterval(function() {
            showPreloader = true;
            clearInterval(t);
            t = undefined;
          }, 400);
          scope.$emit('typeahead:selected', datum);
        })
        .on('typeahead:asyncrequest', function() {
          if (showPreloader) {
            $('.tt-menu').addClass('is-loading');
          }
        })
        .on('typeahead:asynccancel typeahead:asyncreceive', function() {
          $('.tt-menu').removeClass('is-loading');
        });

      scope.$$input = {
        $element: elem,
        $setModelValue: setModelValue,
      };

      function compileSuggestionTemplate(context) {
        return suggestionTemplate.replace(/%VALUE%/, getSuggestionName(context));
      }

      function prepareQuery(query, settings) {
        if (!scope.location) {
          scope.location = {lat: '', lng: ''};
        }
        settings.url += '&lat='+scope.location.lat+'&lng='+scope.location.lng;
        settings.url = settings.url.replace(/%QUERY%/, query);
        return settings;
      }

      function apiResolver() {
        return provider == 'google' ? URL_GOOGLE_MAPS : URL_MAP_QUEST;
      }

      function transformResponse(res) {
        if (provider == 'google') {
          return res.results;
        } else {
          return res;
        }
      }

      function getSuggestionName(suggestion) {
        if (provider == 'google') {
          return suggestion.formatted_address;
        } else {
          return suggestion.value;
        }
      }

      function setModelValue(val) {
        elem.typeahead('val', val);
      }

    }

  }

})();

