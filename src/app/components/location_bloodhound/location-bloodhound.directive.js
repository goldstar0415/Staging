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
      var provider = vm.provider || 'google';

      (function() {
        var waitForElement = $interval(function() {
          if (vm.$$input) {
            init();
            $interval.cancel(waitForElement);
          }
        }, 100);
      })();

      vm.$on('typeahead:selected', onTypeaheadSelect);
      vm.$on('typeahead:change', onChange);
      vm.$watch('location', watchLocation);

      vm.setCurrentLocation = setCurrentLocation;

      function init() {
        vm.location = vm.location || {lat: '', lng: ''};
        vm.$$input.$element.on('focusin', onFocusin);
        if (vm.address && validateLocation(vm.location)) {
          display(vm.address);
          moveOrCreateMarker(vm.location);
          MapService.GetMap().setView(vm.location, 12);
        }
      }

      function onTypeaheadSelect($event, $model) {
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
        if (!validateLocation(vm.location) || vm.$$input.$getModelValue() == '') {
          MapService.GetMap().on('click', onMapClick);
        }
      }

      function onMapClick(event) {
        if (!validateLocation(vm.location) || vm.$$input.$getModelValue() == '') {
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

      function setCurrentLocation() {
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

      function moveOrCreateMarker(latLng) {
        if (vm.bindMarker) {
          if (vm.marker) {
            vm.marker.setLatLng(latLng);
          } else {
            createMarker(latLng);
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

      function removeMarker() {
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

      function onChange($event, newValue) {
        if (!newValue || (newValue+'').trim() == '') {
          removeMarker();
          vm.location = null;
          vm.address = '';
        }
      }

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
      var widgetName = 'bloodhound-typeahead-' + Math.floor(Math.random()*1e12); // a random name
      var suggestionsElementCache = null;

      elem
        .typeahead(null, {
          name: widgetName,
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
            getSuggestionsElement().addClass('is-loading');
          }
        })
        .on('typeahead:asynccancel typeahead:asyncreceive', function() {
          getSuggestionsElement().removeClass('is-loading');
        })
        .on('typeahead:change', function(event, newValue) {
          scope.$emit('typeahead:change', newValue);
        });

      scope.$$input = {
        $element: elem,
        $setModelValue: setModelValue,
        $getModelValue: getModelValue,
      };

      function getSuggestionsElement() {
        if (!suggestionsElementCache) {
          suggestionsElementCache = $('.tt-menu:has(.tt-dataset-'+widgetName+')');
        }
        return suggestionsElementCache;
      }

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

      function getModelValue() {
        var val = elem.typeahead('val');
        return val ? (val+'').trim() : '';
      }

    }

  }

})();

