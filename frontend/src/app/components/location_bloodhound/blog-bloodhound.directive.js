(function() {
  'use strict';
  angular.module('zoomtivity').directive('locationBloodhound', ['API_URL', LocationBloodhound]);

  /** @ngInject */
  function LocationBloodhound(API_URL) {

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
    function controller($scope, toastr, $rootScope, MapService, $interval, $http) {

      var vm = $scope;
      var provider = vm.provider || 'spots';
      var loaded = false;

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
        if(!loaded)
        {
            loaded = true;
            switch (vm.provider || provider) {
              case 'spots': {
                $http.get( API_URL + '/xapi/geocoder/place?placeid=' + $model.place_id, {
                  withCredentials: false
                }).then(function (response) {
                  if(response.data.status == "OK")
                  {
                    var data = response.data.result;
                    vm.location = {lat: data.geometry.location.lat, lng: data.geometry.location.lng}
                  }
                  vm.address = $model.description;
                  var viewAddress = $model.description;
                  display(viewAddress);
                });
                break;
              }
              case 'cities':
              default: {
                vm.location = {lat: $model.lat, lng: $model.lon};
                vm.address = $model.display_name;
                var viewAddress = $model.display_name;
                display(viewAddress);
              }
            }
            var t = setInterval(function() {
                loaded = false;
                clearInterval(t);
                t = undefined;
            }, 400);
        }
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
      
      var URL_CITIES = API_URL + '/xapi/geocoder/search?addressdetails=1&limit=' + limit + '&q=%QUERY%';
      var URL_SPOTS = API_URL + '/xapi/geocoder/autocomplete?q=%QUERY%';

      var bhSource;
      var suggestionTemplate;
      var showPreloader;
      var widgetName; // a random name
      var suggestionsElementCache;

      bindTypeahead();
      scope.$watch('provider', function(value){
          rebindTypeahead();
      });

      function bindTypeahead()
      {
          bhSource = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
              url: apiResolver(),
              wildcard: '%QUERY%',
              prepare: prepareQuery,
            }
          });
          suggestionTemplate = "<div><span class='title'>%VALUE%</span></div>";
          showPreloader = true;
          widgetName = 'bloodhound-typeahead-' + Math.floor(Math.random()*1e12);
          suggestionsElementCache = null;
          elem
            .typeahead({minLength: 3}, {
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
      }
      
      function rebindTypeahead()
      {
          elem.typeahead('destroy');
          bindTypeahead();
      }

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
        switch (getProvider()) {
          case 'spots': {
            return URL_SPOTS;
          }
          case 'cities':
          default: {
            return URL_CITIES;
          }
        }
      }

      function getSuggestionName(suggestion) {
        switch(getProvider()) {
          case 'spots': {
            return suggestion.description;
          }
          case 'cities':
          default: {
            return suggestion.display_name;
          }
        }
      }

      function setModelValue(val) {
        elem.typeahead('val', val);
      }

      function getModelValue() {
        var val = elem.typeahead('val');
        return val ? (val+'').trim() : '';
      }
      
      function getProvider() {
          return scope.provider || 'spots';
      }

    }

  }

})();

