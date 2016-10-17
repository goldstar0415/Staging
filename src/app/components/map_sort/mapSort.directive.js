(function () {
  'use strict';

  /*
   * Directive for spot control panel
   */
  angular.module('zoomtivity')
    .directive('mapSort', function () {
      return {
        restrict: 'E',
        templateUrl: '/app/components/map_sort/map_sort.html',
        controller: mapSort,
        controllerAs: 'MapSort',
        bindToController: true,
        scope: {}
      }
    });

  function mapSort($rootScope, $q, MapService, $http, $timeout, LocationService, Spot, SpotService, API_URL, DATE_FORMAT, $stateParams) {

	var vm = this;
    var SEARCH_URL = API_URL + '/map/spots';
    var SPOT_LIST_URL = API_URL + '/map/spots/list';
    var SPOTS_PER_PAGE = 12;
    var restrictions = {
      tags: 7,
      locations: 20
    };
    var isSelectedAll = false;
    var geocoder = new google.maps.Geocoder();

    vm.vertical = true;
    vm.weatherForecast = [];
    vm.saveToCalendar = SpotService.saveToCalendar;
    vm.removeFromCalendar = SpotService.removeFromCalendar;
    vm.addToFavorite = SpotService.addToFavorite;
    vm.removeFromFavorite = SpotService.removeFromFavorite;
    vm.search = search;
    vm.selectAllCategories = selectAllCategories;
    vm.invalidTag = invalidTag;
    vm.onTagsAdd = onTagsAdd;
    vm.addLocation = addLocation;
    vm.removeLocation = removeLocation;
    vm.removeFilter = removeFilter;
    vm.removeFilterCategory = removeFilterCategory;
    vm.clearFilters = clearFilters;
    vm.isEmptyFilters = isEmptyFilters;
    vm.loadNextSpots = loadNextSpots;
    vm.typeaheadSearch = typeaheadSearch;
    vm.typeaheadSelectLocation = typeaheadSelectLocation;
    vm.openedItem = null;
    vm.setOpenedItem = setOpenedItem;
    vm.location = "Location";

    vm.searchParams = {
      typeahead: {
        list: []
      },
      locations: [],
      tags: []
    };

    $rootScope.doSearchMap = search;
    $rootScope.sortLayer = $rootScope.sortLayer || 'event';
    $rootScope.isDrawArea = false;
    $rootScope.mapSortFilters = $rootScope.mapSortFilters || {};
    $rootScope.toggleLayer = toggleLayer;
    $rootScope.showMessage = showMessage;

    $rootScope.$on('update-map-data', onUpdateMapData);
    $rootScope.$on('clear-map-selection', onRemoveSelection);
    $rootScope.$on('impossible-route', onImpossibleRoute);

    run();

    function setOpenedItem(item) {
        vm.openedItem = item;
    }

    /**
     * Initialization
     */
    function run() {
		loadCategories();
		vm.searchParams.search_text	= ($stateParams.searchText || '');
		vm.searchParams.searchType	= _.isObject($stateParams.spotSearch) ? $stateParams.spotSearch.activeSpotType || 'event' : 'event';
		vm.searchParams.rating		= _.isObject($stateParams.filter) ? $stateParams.filter.rating || null : null;

		if (_.isObject($stateParams.filter)) {
			if ($stateParams.filter.start_date) {
				vm.searchParams.start_date = moment($stateParams.filter.start_date, DATE_FORMAT.datepicker.date).format(DATE_FORMAT.backend_date);
			}
			if ($stateParams.filter.end_date) {
				vm.searchParams.end_date = moment($stateParams.filter.end_date, DATE_FORMAT.datepicker.date).format(DATE_FORMAT.backend_date);
			}
			if (Array.isArray($stateParams.filter.category_ids) && $stateParams.filter.category_ids.length) {
				_.each(vm.spotCategories[vm.searchParams.searchType], function(item) {
					item.selected = $stateParams.filter.category_ids.indexOf(item.id) >= 0 ? true : false;
				});
			}
        }

		if (_.isObject($stateParams.spotLocation) && $stateParams.spotLocation.lat !== undefined && $stateParams.spotLocation.lat) { // from 'intro'
			vm.vertical = false;
			toggleLayer(vm.searchParams.searchType, false);

			MapService.FocusMapToGivenLocation($stateParams.spotLocation, 13);
			MapService.GetBoundsByCircle($stateParams.spotLocation, function() {
				search();
			});
			MapService.FitBoundsOfCurrentLayer();
		} else {
			// just search
			if (vm.searchParams.search_text.length > 0) {
				vm.vertical = false;
				MapService.FocusMapToCurrentLocation();
				toggleLayer(vm.searchParams.searchType);
				search();
			} else {
				// activate a search tool and desired layer (from intro) if not from profile
				if (vm.searchParams.searchType && !($rootScope.$state && $rootScope.$state.current && ['profile', 'profile_menu'].indexOf($rootScope.$state.current.parent) >= 0)) {
					// toggle a layer, but don't start a search
					toggleLayer(vm.searchParams.searchType, false);
				}
			}
		}

	}

    function showMessage(type, text) {
        toastr[type](text);
    }

    /**
     * Search locations when typing - ok
     */
    function typeaheadSearch() {
      if ( !_.isEmpty(vm.searchParams.search_text) ) {
		if (vm.searchParams.typeahead === undefined) {
			vm.searchParams.typeahead = {};
		}
        vm.searchParams.typeahead.list = [];
        geocoder.geocode({address: vm.searchParams.search_text}, function (results) {
          vm.searchParams.typeahead.list = results;
          console.log('Geocoded address', results);
        });
      }
    }

    /**
     * Select a location from typeahead list
     * @param location
     */
    function typeaheadSelectLocation(location) {
      console.log('Selected location', location);
      var point = { lat: location.geometry.location.lat(), lng: location.geometry.location.lng(), isFirst: true };
      vm.searchParams.address = location.formatted_address;
      vm.searchParams.location = point;

      addLocation();
    }

    function getCircleBounds(bounds) {
      console.log('Circle Bounds', bounds);
      search();
    }

    /**
     * Render map data
     * @param event
     * @param {array} mapSpots
     * @param {string} layer
     * @param {boolean} isDrawArea
     * @param {boolean} ignoreEmptyList
     */
    function onUpdateMapData(event, mapSpots, layer, isDrawArea, ignoreEmptyList) {
      console.log('update map');

      ignoreEmptyList = ignoreEmptyList === undefined || ignoreEmptyList === true;

      layer = layer || $rootScope.sortLayer;
      $rootScope.sortLayer = layer;
      if (angular.isDefined(isDrawArea)) {
        $rootScope.isDrawArea = isDrawArea;
      }

      $rootScope.mapSortSpots = {
        markers: [],
        data: [],
        page: 0,
        cancellerHttp: $rootScope.mapSortSpots.cancellerHttp
      };

	  if (!MapService.hasLayer(layer)) {
		  toggleLayer(layer, false);
	  }
      if ($rootScope.isDrawArea) {
        _.each(mapSpots, function (item) {
          if (MapService.PointInPolygon(item.location)) {
            $rootScope.mapSortSpots.markers.push(item);
          }
        });
      } else {
        $rootScope.mapSortSpots.markers = mapSpots;
      }

      $timeout(function () {
        if ($rootScope.mapSortSpots.markers.length > 0) {
          $rootScope.changeMapState('small', null, false);
          MapService.drawSearchSpotMarkers($rootScope.mapSortSpots.markers, layer, true);
          if (!$rootScope.isDrawArea) {
            MapService.FitBoundsByLayer($rootScope.sortLayer);
          }
        } else {
          $rootScope.changeMapState('big');
          if ( !ignoreEmptyList ) {
            toastr.info('0 spots found');
          }
          MapService.clearLayers();
        }
      });

      $rootScope.mapSortSpots.sourceSpots = _filterUniqueSpots($rootScope.mapSortSpots.markers);
      loadNextSpots();
    }

    function _filterUniqueSpots(array) {
      return _.uniq(array, function (item) {
        return item.spot_id
      });
    }

    /**
     * Infinite scroll - ok
     */
    function loadNextSpots() {
      console.log('loadNextSpots');
      if ($rootScope.mapSortSpots.sourceSpots && $rootScope.mapSortSpots.sourceSpots.length > 0) {
        var startIdx = $rootScope.mapSortSpots.page * SPOTS_PER_PAGE,
        endIdx = startIdx + SPOTS_PER_PAGE,
        spots = $rootScope.mapSortSpots.sourceSpots.slice(startIdx, endIdx),
        ids = _.pluck(spots, 'spot_id');

        if (ids.length > 0) {
          $rootScope.mapSortSpots.isLoading = true;
          $http.get(SPOT_LIST_URL + '?' + jQuery.param({ids: ids}))
            .success(function success(data) {
              if ($rootScope.sortLayer == 'event') {
                data = SpotService.formatSpot(data);
              }

              $rootScope.mapSortSpots.data = _.union($rootScope.mapSortSpots.data, data);
              $rootScope.mapSortSpots.isLoading = false;
            })
            .catch(function (resp) {
              $rootScope.mapSortSpots.isLoading = false;
            });

          $rootScope.mapSortSpots.page++;
        }
      }
    }

    /**
     * Switch a layer and apply UI changes
     * @param {string} layer
     * @param {boolean} startSearch
     */
    function toggleLayer(layer, startSearch) {
		$rootScope.sortLayer = layer;
        vm.currentWeather = null;

		if (layer == 'weather') {
			MapService.showOtherLayers();

			// show weather radar data for US users
			if ($rootScope.currentCountryCode === 'us') {
				MapService.toggleWeatherLayer(true);
			} else {
				console.log('Current country: ', $rootScope.currentCountryCode);
			}

			MapService.WeatherSelection(weather, geocodeCallback);

			if (!vm.currentWeather) {
				toastr.info('Click on map to check weather in this area');
			}
		} else {
			MapService.toggleWeatherLayer(false);
			if (layer != 'event') {
				$rootScope.mapSortFilters.filter = $rootScope.mapSortFilters.filter || {};
				$rootScope.mapSortFilters.filter.start_date = $rootScope.mapSortFilters.filter.end_date = '';
				vm.searchParams.start_date = vm.searchParams.end_date = '';
			}

			if (startSearch !== false) {
				search();
				MapService.showLayer(layer);
			} else {
				// show a layer, but keep existing event listeners, for ex. if path selection has started
				MapService.showLayer(layer, true);
			}
			var wp = MapService.GetPathWaypoints();
			var geoJson = MapService.GetGeoJSON();

			if ($rootScope.isDrawArea && wp.length < 1 && geoJson && geoJson.features.length < 1) {
				toastr.info('Draw the search area');
			}
		}
    }

    function onTagsAdd(q, w, e) {
      console.log('add tags');
      if (vm.searchParams.tags.length < restrictions.tags) {
        return true;
      } else {
        toastr.error('You can\'t add more than ' + restrictions.tags + ' tags');
        return false;
      }
    }

    function invalidTag(tag) {
      if (tag.text.length > 64) {
        toastr.error('Your tag is too long. Max 64 symbols.');
      } else {
        toastr.error('Invalid input.');
      }
    }

    /**
     * API-request or get from $rootScope
     */
    function loadCategories() {
      if (!$rootScope.spotCategories) {
        $http.get(API_URL + '/spots/categories')
          .success(function (data) {
            $rootScope.spotCategories = data;
            _loadCategories(data)
          });
      } else {
        _loadCategories($rootScope.spotCategories);
      }
    }

    function _loadCategories(data) {
		vm.spotCategories = {};
		_.each(data, function (item) {
			vm.spotCategories[item.name] = item.categories;
		});
    }


    /**
     * Search - use existing points or do search
     */
    function search() {
      var l = null, ll = [];

      // get entered location(s)

      if ( !_.isEmpty(vm.searchParams.location) ) {

        l = vm.searchParams.location;

      } else {

        if ( !_.isEmpty(vm.searchParams.locations) ) {
          if ( vm.searchParams.locations.length == 1 ) {
            l = vm.searchParams.locations[0].location;
          } else {
            ll = vm.searchParams.locations;
          }
        }

      }

      // search
      if ( !$rootScope.isDrawArea ) {
        if (l) {
          console.log('Search by ONE location', l);
          MapService.clearSelections(true);
          // focus & draw a circle
          MapService.FocusMapToGivenLocation(l, 13);
          MapService.GetBoundsByCircle(l, function () {
            doSearch();
            MapService.FitBoundsOfCurrentLayer();
          });
        } else {

          if (!_.isEmpty(ll)) {
            // draw a path
            drawPathSelection(doSearch);
          } else {
            // search by filter
            doSearch();
          }

        }
      } else {
        // don't draw new selections
        doSearch();
      }
    }

    /**
     * Search spots when adding locations using the filter
     */
    function intermediateSearch() {
      if ( !_.isEmpty(vm.searchParams.locations) && vm.searchParams.locations.length > 1 ) {
        drawPathSelection(function(){ doSearch(true); }, true);
      }
    }

    /**
     * API-request - apply a custom filter and update the map
     */
	function doSearch(isIntermediateSearch) {
		var data = {
			search_text: vm.searchParams.search_text,
			filter: {}
		};

		if (vm.searchParams.rating) {
			data.filter.rating = vm.searchParams.rating;
		}

		if (vm.searchParams.tags) {
			data.filter.tags = _.pluck(vm.searchParams.tags, 'text');
		}

		if (vm.searchParams.start_date) {
			if (vm.searchParams.start_date.match(/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/)) {
				data.filter.start_date = moment(vm.searchParams.start_date, DATE_FORMAT.datepicker.date).format(DATE_FORMAT.backend_date);
			} else if (vm.searchParams.start_date.match(/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/)) {
				data.filter.start_date = vm.searchParams.start_date;
			}
		}

		if (vm.searchParams.end_date) {
			if (vm.searchParams.end_date.match(/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/)) {
				data.filter.end_date = moment(vm.searchParams.end_date, DATE_FORMAT.datepicker.date).format(DATE_FORMAT.backend_date);
			} else if (vm.searchParams.end_date.match(/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/)) {
				data.filter.end_date = vm.searchParams.end_date;
			}
		}

		var categories = _.where(vm.spotCategories[$rootScope.sortLayer], {selected: true});
		if (categories.length > 0) {
			data.filter.category_ids = _.pluck(categories, 'id');
		}

		$rootScope.mapSortFilters = angular.copy(data);

		var bbox_array = MapService.GetBBoxes();
		if (bbox_array.length > 0) {
			bbox_array = MapService.BBoxToParams(bbox_array);
			data.filter.b_boxes = bbox_array;
			data.search_text = '';
		}

		if (bbox_array.length == 0 && !vm.searchParams.search_text) {
		    // toastr.error('Enter location or draw the area');
            var pn = document.querySelector('.pick-notification');
            pn.style.visibility = 'visible';
            pn.style.opacity = '1';
            pn.style.zIndex = '9999';
            pn.onclick = function() {
                var el = document.querySelector('.pick-notification');
                pn.style = '';
            }
            var container = document.querySelector('.leaflet-bottom.leaflet-left');

			$rootScope.mapSortFilters = {};
			return;
		}
		data.type = $rootScope.sortLayer;

		MapService.cancelHttpRequest();
		$rootScope.mapSortSpots.cancellerHttp = $q.defer();

		isIntermediateSearch = isIntermediateSearch === true;

		if ($rootScope.routeInterpolated) {
			data.filter.path = _.map($rootScope.routeInterpolated, function(e) { return {lat: e.latLng.lat, lng: e.latLng.lng}; });
		}

		$http.post(SEARCH_URL, data, {timeout: $rootScope.mapSortSpots.cancellerHttp.promise})
			.success(function (spots) {
				if (spots.length > 0) {
				onUpdateMapData(null, spots, $rootScope.sortLayer, bbox_array.length > 0, false);
				} else {
					onUpdateMapData(null, [], null, bbox_array.length > 0, false);
				}
				vm.categoryToggle = false;
				vm.isShowFilter = isIntermediateSearch;
				if ( isIntermediateSearch ) {
					$rootScope.isDrawArea = false;
				}
			}).catch(function (resp) {
				if (resp.status > 0) {
					toastr.error(resp.data ? resp.data.message : 'Something went wrong')
				}
			});
    }

    /**
     * Draw points on the map as a path
     * @param callback
     * @param isIntermediateSearch
     */
    function drawPathSelection(callback, isIntermediateSearch) {
      console.log('Draw Path');

      console.log('old locations:', vm.searchParams.locations);

      var points = [];
      if (_.isObject(vm.searchParams.location) && !_.isEmpty(vm.searchParams.location)) {
        points.push(vm.searchParams.location);
      }
      if (vm.searchParams.locations.length > 0) {
        toastr.success('Routing...');
        points = _.union(points, _.pluck(vm.searchParams.locations, 'location'));
      }

      if (_.isArray(points) && isIntermediateSearch ) {
        var buf = [];
        $.each(points, function(j, p){
          // destroy links to objects
          var cp = angular.copy(p);
          cp.ignoreMarkerEvents = true;
          buf.push(cp);
        });
        points = buf;
      }

      console.log('new points');
      console.log(points);

      if (!isIntermediateSearch) {
        MapService.clearSelections();
      } else {
        MapService.clearSelections(true);
      }

      var next = function() {
        // display the entire path
        MapService.FitBoundsOfDrawLayer();
        if (callback) callback();
      };
      MapService.PathSelection(points, next);
    }

    /**
     * Filter: add a location
     */
    function addLocation() {
      console.log('adding location');

      if ( !_.isEmpty(vm.searchParams.address) && !_.isEmpty(vm.searchParams.location) ) {
        var loc = {
          address: vm.searchParams.address,
          location: vm.searchParams.location
        };
        console.log(loc);

        var idxForUpdate = -1;
        var idxDuplicate = -1;

        angular.forEach(vm.searchParams.locations, function (l, idx) {

          if (loc.location.isFirst === true && l.location.isFirst === true) {
            idxForUpdate = idx;
          }

          if ( loc.location.isFirst !== true && Math.round( loc.location.lat*10e7 ) == Math.round( l.location.lat*10e7 ) && Math.round( loc.location.lng*10e7 ) ==  Math.round( l.location.lng*10e7 ) ) {
            idxDuplicate = idx;
          }
        });

        if ( idxDuplicate != -1 ) {
          toastr.warning('The point yoy have entered duplicates the point: "' + vm.searchParams.locations[idxDuplicate].address + '"');
          console.log(vm.searchParams.locations, loc);
          return;
        }

        console.log('idxForUpdate: ', idxForUpdate);

        // update the first location instead of adding
        if ( idxForUpdate != -1 ) {
          console.log('Updating a location');
          vm.searchParams.locations[ idxForUpdate ] = loc;
        } else {
          if (loc.location.isFirst === true) {
            vm.searchParams.locations.push(loc);
          } else {
            vm.searchParams.locations.unshift(loc);
          }
        }

        angular.element('#new_location input').attr('placeholder', 'Add next destination'); // fixme: fix location placeholder
      } else {
        toastr.error('Wrong location');
      }

      vm.searchParams.address = '';
      vm.searchParams.location = {};

      console.log('After location add, locations are: ', vm.searchParams.locations);

      // search spots when adding locations one by one
      intermediateSearch();
    }

    /**
     * Filter: remove the location
     * @param idx
     */
    function removeLocation(idx) {
      var removingTheFirst = vm.searchParams.locations[idx].location.isFirst === true;
      vm.searchParams.locations.splice(idx, 1);
      if (vm.searchParams.locations.length == 0) {
        angular.element('#new_location input').attr('placeholder', 'Add first destination');  // fixme: fix location placeholder
        // remove all the selections on the map
        MapService.clearSelections();
      } else {
        if ( vm.searchParams.locations.length == 1 ) {
          if ( removingTheFirst ) {
            // we've removed the first route point, set a regular point as the first one
            vm.searchParams.location = angular.copy(vm.searchParams.locations[0].location);
            vm.searchParams.search_text = vm.searchParams.locations[0].address;
          }
          // we have only one point, focus
          MapService.clearSelections(true);
          MapService.FocusMapToGivenLocation(vm.searchParams.locations[0].location, 13);
        }
        if (vm.searchParams.locations.length > 1) {
          intermediateSearch();
        }
      }
    }

    function onRemoveSelection() {
      console.log('onRemoveSelection');
      vm.searchParams.search_text = '';
      vm.searchParams.location = {};
      vm.searchParams.address = '';
      vm.searchParams.locations = [];
    }

    function onImpossibleRoute() {
      toastr.warning("Couldn't build a route between points you have selected.");
      MapService.clearSelections();
    }

    function clearFilters() {
      $rootScope.mapSortFilters = {};
      vm.searchParams = {
        locations: [],
        tags: []
      };

      //clear categories
      isSelectedAll = true;
      selectAllCategories();

      if ($rootScope.isDrawArea) {
        search();
      } else {
        MapService.clearLayers();
        MapService.cancelHttpRequest();
      }
    }

    /**
     * Remove a filter
     * @param type
     */
    function removeFilter(type) {
      switch (type) {
        case 'date':
          if ($rootScope.mapSortFilters.filter) {
            $rootScope.mapSortFilters.filter.start_date = '';
            $rootScope.mapSortFilters.filter.end_date = '';
            vm.searchParams.start_date = '';
            vm.searchParams.end_date = '';
          }
          break;
        case 'tags':
          $rootScope.mapSortFilters.filter.tags = [];
          vm.searchParams.tags = [];
          break;
        case 'rating':
          $rootScope.mapSortFilters.filter.rating = null;
          vm.searchParams.rating = null;
          break;
      }

      search();
    }

    function removeFilterCategory(item) {
      item.selected = false;
      if ($rootScope.mapSortFilters && $rootScope.mapSortFilters.filter && $rootScope.mapSortFilters.filter.category_ids) {
        $rootScope.mapSortFilters.filter.category_ids = _.without($rootScope.mapSortFilters.filter.category_ids, item.id);
      }

      search();
    }

    function selectAllCategories() {
      isSelectedAll = !isSelectedAll;
      _.each(vm.spotCategories[$rootScope.sortLayer], function (item) {
        item.selected = isSelectedAll;
      });
    }

    function isEmptyFilters() {
      var isEmpty = true;

      $rootScope.mapSortFilters.filter = $rootScope.mapSortFilters.filter || {};

      if ($rootScope.mapSortFilters.search_text ||
        $rootScope.mapSortFilters.filter.start_date || $rootScope.mapSortFilters.filter.end_date ||
        $rootScope.mapSortFilters.filter.rating ||
        ($rootScope.mapSortFilters.filter.tags && $rootScope.mapSortFilters.filter.tags.length > 0) ||
        ($rootScope.mapSortFilters.filter.category_ids && $rootScope.mapSortFilters.filter.category_ids.length > 0)) {
        isEmpty = false;
      }
      return isEmpty;
    }


    //============================ weather section =========================
    function weather(resp) {
      vm.vertical = false;
      vm.weatherForecast = [];
      var daily = resp.daily.data;

      for (var k in daily) {
        daily[k].formattedDate = moment(daily[k].time * 1000).format('ddd');
        if (k != 0) {
          vm.weatherForecast.push(daily[k]);
        }
      }
      vm.currentWeather = daily[0];
      vm.currentWeather.sunrise = moment(daily[0].sunriseTime * 1000).format(DATE_FORMAT.time);
      vm.currentWeather.sunset = moment(daily[0].sunsetTime * 1000).format(DATE_FORMAT.time);
      vm.currentWeather.temperature = Math.round((daily[0].temperatureMax + daily[0].temperatureMin) / 2);

      $rootScope.toggleSidebar(true);
    }

	/**
	 * Detect City in the Weather Panel
	 */
	function geocodeCallback(data) {
		if (data.address) {
			vm.currentWeatherLocation = {placeName: data.address.city || data.address.county || data.address.state || data.address.country};
		} else {
			vm.currentWeatherLocation = {placeName: 'N/A'};
		}
	}
  }
})();
