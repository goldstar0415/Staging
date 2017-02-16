(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('MapService', function ($rootScope, $timeout, $location, $http, API_URL, snapRemote, $compile, moment, $state, $modal, toastr, MOBILE_APP, GEOCODING_KEY, MAPBOX_API_KEY, Area, SignUpService, Spot, SpotComment, SpotService, LocationService, $ocLazyLoad, OPENWEATHERMAP_API_KEY, SKOBBLER_API_KEY) {

      console.log('MapService');

      var map = null;
      var DEFAULT_MAP_LOCATION = [37.405075073242188, -96.416015625000000];
      var tilesUrl = 'https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png';
      var tilesWeatherUrl = '//mesonet.agron.iastate.edu/cache/tile.py/1.0.0/nexrad-n0q-{timestamp}/{z}/{x}/{y}.png';
      var timestamps = ['900913-m50m', '900913-m45m', '900913-m40m', '900913-m35m', '900913-m30m', '900913-m25m', '900913-m20m', '900913-m15m', '900913-m10m', '900913-m05m', '900913'];

      var radiusSelectionLimit = 500000; // in meters
      var markersLayer = L.featureGroup();
      var drawLayer = L.featureGroup();
      var bgLayer = L.featureGroup();
      var draggableMarkerLayer = L.featureGroup();
      var drawLayerGeoJSON;
      var controlGroup = L.featureGroup();
      var clusterOptions = {
        //disableClusteringAtZoom: 8,
		    //chunkedLoading: true,
        spiderfyDistanceMultiplier: 2,
        maxClusterRadius: 8,
        // disableClusteringAtZoom: 12,
        //spiderfyOnMaxZoom: true,
      };
      //============================================
      var eventsLayer = new L.MarkerClusterGroup(clusterOptions);
      var foodLayer = new L.MarkerClusterGroup(clusterOptions);
      var shelterLayer = new L.MarkerClusterGroup(clusterOptions);
      var todoLayer = new L.MarkerClusterGroup(clusterOptions);
      var otherLayer = new L.MarkerClusterGroup(clusterOptions);
      //===============================================
      var currentLayer = "";

      // Path variables
      var pathRouter = L.Routing.osrmv1({geometryOnly: true});
      var pathRouter2 = L.Routing.mapbox(MAPBOX_API_KEY);
      var pathRouterFail = 0;

      var highlightMarker;
      var mobileMarker;
    
      var PickNotification = $('.pick-notification');
      
      function pickNotificationFadeOut() {
          PickNotification.css('opacity', 0);
          $timeout(function() {
              PickNotification.hide();
              PickNotification.css('opacity', 1);
          }, 600);
      }
        
      if (_.isEmpty(fx.rates)) {
          $http.get(API_URL + '/rates')
              .then(function(resp) {
                  if (resp.status === 200) {
                    fx.base = 'USD';
                    fx.rates = resp.data;
                    fx.rates.USD = 1;
                  }
              });
      }

		function getPathRouter() {
			switch(pathRouterFail) {
				case 0:
					return pathRouter;
				case 1:
					return pathRouter2;
				default:
					return pathRouter;
			}
		}

		function lazyRouter(waypoints, cb) {
      if ($ocLazyLoad.isLoaded('turf')) {
        exec();
      } else {
        $ocLazyLoad.load('turf').then(exec);
      }

      function exec() {
        getPathRouter().route(waypoints, cb);
      }
    }

		function pathRouterFailed() {
			pathRouterFail++;
		}

      var pathSelectionStarted = false;

      //GEOCODING
      var GeocodingSearchUrl = '//open.mapquestapi.com/nominatim/v1/search.php?format=json&key=' + GEOCODING_KEY + '&addressdetails=1&limit=3&q=';
      var GeocodingReverseUrl = '//open.mapquestapi.com/nominatim/v1/reverse.php?format=json&key=' + GEOCODING_KEY;

      function closeAll() {
        //   L.DomEvent.stopPropagation(e);
        //   L.DomEvent.preventDefault(e);
          ClearSelections();
          map.closePopup();
          cancelHttpRequest();
          $rootScope.isDrawArea = false;
          angular.element('.leaflet-control-container .map-tools > div').removeClass('active');
          $rootScope.toggleSidebar(false);
          $rootScope.setOpenedSpot(null);
      }

      function setMarkerIcon(spot, isHighlighted) {
          var image = '';
          if (spot) {
              if (spot.category_name === 'Event' || (spot.type && spot.type === 'Event')) {
                  image = '../../../assets/img/markers/marker-event' + (isHighlighted ? '-highlighted.png' : '.png');
              } else if (spot.category_name === 'Food' || (spot.type && spot.type === 'Food')) {
                  image = '../../../assets/img/markers/marker-food' + (isHighlighted ? '-highlighted.png' : '.png');
              } else if (spot.category_name === 'To-Do' || (spot.type && spot.type === 'To-Do')) {
                  image = '../../../assets/img/markers/marker-todo' + (isHighlighted ? '-highlighted.png' : '.png');
              } else if (spot.category_name === 'Shelter' || (spot.type && spot.type === 'Shelter')) {
                  image = '../../../assets/img/markers/marker-shelter' + (isHighlighted ? '-highlighted.png' : '.png');
              }
          } else {
             image = '../../../assets/img/markers/marker-empty' + (isHighlighted ? '-highlighted.png' : '.png');
          }
          return image;
      }

      function plygonFromCircle(lat, lng, radius) {
          var d2r = Math.PI / 180; // degrees to radians
          var r2d = 180 / Math.PI; // radians to degrees
          var earthsradius = 60;
          var points = 32;
          // find the radius in lat/lon
          var rlat = (radius / earthsradius) * r2d;
          var rlng = rlat / Math.cos(lat * d2r);
          var extp = [];
          for (var i = 0; i < points + 1; i++) // one extra here makes sure we connect the
          {
              var theta = Math.PI * (i / (points / 2));
              var ex = lng + (rlng * Math.cos(theta)); // center a + radius x * cos(theta)
              var ey = lat + (rlat * Math.sin(theta)); // center b + radius y * sin(theta)
              extp.push(new L.LatLng(ey, ex));
          }
          return extp;
      }

      function toggleFullScreen() {
          var doc = window.document;
          var docEl = doc.documentElement;

          var requestFullScreen = docEl.requestFullscreen || docEl.mozRequestFullScreen || docEl.webkitRequestFullScreen || docEl.msRequestFullscreen;
          var cancelFullScreen = doc.exitFullscreen || doc.mozCancelFullScreen || doc.webkitExitFullscreen || doc.msExitFullscreen;
          if (!doc.fullscreenElement && !doc.mozFullScreenElement && !doc.webkitFullscreenElement && !doc.msFullscreenElement) {
              requestFullScreen.call(docEl);
          } else {
              cancelFullScreen.call(doc);
          }
      }

      function removeHighlighting() {
          if (highlightMarker) {
              map.removeLayer(highlightMarker);
          }
      }

      function highlightSpot() {
          var selectedId, marker, spot;
          selectedId = $rootScope.visibleSpotsIds[$rootScope.spotsCarousel.index];
          if ($rootScope.mapSortSpots) {
              var features = [];
              features = map._getFeatures();
              marker = $.grep(features, function(s) {
                  return s.spot_id === selectedId;
              })[0];
              spot = $.grep($rootScope.mapSortSpots.data, function(s) {
                  return s.id === selectedId;
              })[0];
              if (spot && !marker) {
                  var el = document.querySelectorAll('.marker-cluster').forEach(function(mc) {
                      mc.classList.remove('active');
                  });
                  for (var i = 0; i < features.length; i++) {
                      if ('_childClusters' in features[i]) {
                          var mrkrs = features[i].getAllChildMarkers();
                          marker = $.grep(mrkrs, function(m) {
                              return m.spot_id === spot.id;
                          })[0];
                          if (marker) {
                              features[i]._icon.classList.add('active');
                              break;
                          }
                      }
                  }
              }
              if (highlightMarker && spot) {
                  var oldIcon = CreateCustomIcon('', '', spot);
                  highlightMarker.setIcon(oldIcon);
              }
          }
          if (marker && spot) {
              var image = setMarkerIcon(spot);
              var icon = L.icon({
                  iconSize: [50, 50],
                  iconUrl: image,
                  className: 'spot-icon'
              });
              marker.setIcon(icon);
              highlightMarker = marker;
              marker.openPopup();
          }
      }

      function clearSpotHighlighting(spot) {
          var el = document.querySelectorAll('.marker-cluster').forEach(function(mc) {
              mc.classList.remove('active');
          });
          if (highlightMarker) {
              var oldIcon = CreateCustomIcon('', '', spot);
              highlightMarker.setIcon(oldIcon);
              highlightMarker = null;
          }
          $rootScope.highlightedSpotId = null;
          spot.marker.closePopup();
      }

      function highlightSpotByHover(spot) {
          var el = document.querySelectorAll('.marker-cluster').forEach(function(mc) {
              mc.classList.remove('active');
          });
          var features = [];
          features = map._getFeatures();
          var marker = $.grep(features, function(m) {
              return m.spot_id === spot.id;
          })[0];
          if (!marker) {
              for (var i = 0; i < features.length; i++) {
                  if ('_childClusters' in features[i]) {
                      var mrkrs = features[i].getAllChildMarkers();
                      marker = $.grep(mrkrs, function(m) {
                          return m.spot_id === spot.id;
                      })[0];
                      if (marker) {
                          features[i]._icon.classList.add('active');
                          break;
                      }
                  }
              }
          }
          var image = setMarkerIcon(spot);
            var icon = L.icon({
                iconSize: [50, 50],
                iconUrl: image,
                className: 'spot-icon'
            });
        //   var icon = new L.HtmlIcon({
        //       html: "<div class='map-marker-plate'><img src='" + image + "' /></div>",
        //   });
          marker.setIcon(icon);
          highlightMarker = marker;
          if ($rootScope.isMapState()) {
              $rootScope.highlightedSpotId = spot.id || spot.spot.id || 0;
          }
          spot.marker.openPopup();
      }

      function detectHover(marker, spot) {
          marker.on('mouseover', function() {
              if ($rootScope.isMapState()) {
                  $rootScope.highlightedSpotId = spot.id || spot.spot.id || 0;
                  $rootScope.$apply();
                  highlightSpotByHover(spot, marker);
              }
          });
          marker.on('mouseout', function() {
              $rootScope.highlightedSpotId = null;
              $rootScope.$apply();
              clearSpotHighlighting(spot, marker);
          });
      }

      function spotsOnScreen() {
          if ($rootScope.sortLayer === 'weather') {
              showWeatherMarkers();
          }
          if (!$rootScope.$$phase) {
              $rootScope.searchLimit = 20;
              var _borderMarkerLayer = undefined;
              var layerGroup = null;
              if (typeof _borderMarkerLayer === 'undefined') {
                  _borderMarkerLayer = new L.LayerGroup();
              }
              _borderMarkerLayer.clearLayers();

              var features = [];
              if (layerGroup != null) {
                  features = layerGroup.getLayers();
              } else {
                  features = map._getFeatures();
              }

              var mapPixelBounds = map.getSize();
              $rootScope.visibleSpotsIds = [];
              for (var i = 0; i < features.length; i++) {

                  var currentMarkerPosition = map.latLngToContainerPoint(
                      features[i].getLatLng());

                  if (!(currentMarkerPosition.y < 0 ||
                          currentMarkerPosition.y > mapPixelBounds.y ||
                          currentMarkerPosition.x > mapPixelBounds.x ||
                          currentMarkerPosition.x < 0)) {
                      if ('_childClusters' in features[i]) {
                          var mrkrs = features[i].getAllChildMarkers();
                          for (var j = 0; j < mrkrs.length; j++) {
                              $rootScope.visibleSpotsIds.push(mrkrs[j].spot_id);
                              continue;
                          }
                      }
                      if (!('_ctx' in features[i])) {
                          $rootScope.visibleSpotsIds.push(features[i].spot_id);
                      }
                  }
              }
              $rootScope.spotsCarousel.index = 0;
              $rootScope.$apply();
          }
      }

      L.HtmlIcon = L.Icon.extend({
          options: {
              /*
              html: (String) (required)
              iconAnchor: (Point)
              popupAnchor: (Point)
              */
          },

          initialize: function(options) {
              L.Util.setOptions(this, options);
          },

          createIcon: function() {
              var div = document.createElement('div');
              div.innerHTML = this.options.html;
              return div;
          },

          createShadow: function() {
              return null;
          }
      });

      //MAP CONTROLS
      // Lasso controls
      L.Control.lasso = L.Control.extend({
        options: {
          position: 'bottomleft'
        },
        onAdd: function (map) {
            var scope = $rootScope.$new();
            var template = '    <div tooltip="Lasso Selection" tooltip-placement="right" class="map-tools map-tools-selection">\
                                    <div class="lasso-selection">\
                                        <img src="../../assets/img/svg/Lasso.svg"/>\
                                    </div>\
                                </div>';
            var btn = $compile(template)(scope);
            this._map = map;
            L.DomEvent.on(btn[0], 'click', this._click, this);
            return btn[0];
        },

        _click: function (e) {
          toastr.info('Draw Search Area');
          pickNotificationFadeOut();
          ClearSelections();
          $rootScope.hideHints = true;
          $timeout(function () {
            $rootScope.$apply();
          });
          snapRemote.disable();
          L.DomEvent.stopPropagation(e);
          L.DomEvent.preventDefault(e);
          LassoSelection(function LassoCallback(points, b_box) {
              L.polygon([points,[[90, -180],[90, 180],[-90, 180],[-90, -180]]], {
                  weight: 3,
                  color: '#00CFFF',
                  opacity: 0.9,
                  fillColor: '#0C2638',
                  fillOpacity: 0.4,
                }).addTo(bgLayer);

              L.polygon(points, {
                opacity: 0.0,
                fill: false
              }).addTo(drawLayer);

            snapRemote.enable();

            var bboxes = GetDrawLayerBBoxes();
            GetDataByBBox(bboxes);
            _activateControl(false);
          });

          _activateControl('.lasso-selection');
        }

      });
      L.Control.Lasso = function (options) {
        return new L.Control.lasso(options);
      };
      // Radius controls
      L.Control.radius = L.Control.extend({
        options: {
          position: 'bottomleft'
        },
        onAdd: function (map) {
            var scope = $rootScope.$new();
            var template = '    <div tooltip="Radius Selection" tooltip-placement="right" class="map-tools map-tools-selection">\
                                    <div class="radius-selection">\
                                        <img src="../../assets/img/svg/Radius.svg"/>\
                                    </div>\
                                </div>';
            var btn = $compile(template)(scope);
            this._map = map;
            L.DomEvent.on(btn[0], 'click', this._click, this);
            return btn[0];
        },
        _click: function (e) {
          toastr.info('Drag Radius');
          pickNotificationFadeOut();
          ClearSelections();
          $rootScope.hideHints = true;
          $timeout(function () {
            $rootScope.$apply();
          });
          snapRemote.disable();
          L.DomEvent.stopPropagation(e);
          L.DomEvent.preventDefault(e);
          RadiusSelection(function (startPoing, radius, b_box) {
            snapRemote.enable();

            var circle = L.circle(startPoing, radius, {
              opacity: 0.0,
              fill: false,
            });

            circle.addTo(drawLayer);

            var bboxes = GetDrawLayerBBoxes();

            var rds = (bboxes[0].getNorthWest().lat - bboxes[0].getSouthWest().lat) / 2;
            var polyCrcl = plygonFromCircle(startPoing.lat, startPoing.lng, rds)

            L.polygon([polyCrcl, [[90, -180],[90, 180],[-90, 180],[-90, -180]]], {
              weight: 3,
              color: '#00CFFF',
              opacity: 0.9,
              fillColor: '#0C2638',
              fillOpacity: 0.4
          }).addTo(bgLayer);

            GetDataByBBox(bboxes);
            _activateControl(false);
          });

          _activateControl('.radius-selection');
        }

      });
      L.Control.Radius = function (options) {
        return new L.Control.radius(options);
      };
      // Path controls
      L.Control.path = L.Control.extend({
        options: {
          position: 'bottomleft'
        },
        onAdd: function (map) {
            var scope = $rootScope.$new();
            var template = '    <div tooltip="Path Selection" tooltip-placement="right" class="map-tools map-tools-selection">\
                                    <div class="path-selection">\
                                        <img src="../../assets/img/svg/Road_icon.svg"/>\
                                    </div>\
                                </div>';
            var btn = $compile(template)(scope);
            this._map = map;
            L.DomEvent.on(btn[0], 'click', this._click, this);
            return btn[0];
        },
        _click: function (e) {
          toastr.info('Place Pin at Start');
          pickNotificationFadeOut();
          ClearSelections();
          $rootScope.hideHints = true;
          $timeout(function () {
            $rootScope.$apply();
          });
          L.DomEvent.stopPropagation(e);
          L.DomEvent.preventDefault(e);
          PathSelection(null, function () {
            var bboxes = GetDrawLayerBBoxes();
            GetDataByBBox(bboxes);
          });

          _activateControl('.path-selection');
        }

      });
      L.Control.Path = function (options) {
        return new L.Control.path(options);
      };

      // Save selection
      L.Control.saveSelection = L.Control.extend({
        options: {
          position: 'topleft'
        },
        onAdd: function (map) {
          var scope = $rootScope.$new();
          var template = '    <div tooltip="Save Selection" tooltip-placement="bottom" class="map-tools-top hidden">\
                                  <div class="save-selection">\
                                      <img src="../../assets/img/svg/floppy-disk-save-file.svg"/>\
                                  </div>\
                              </div>';
          var btn = $compile(template)(scope);
          this._map = map;
          L.DomEvent.on(btn[0], 'click', this._click, this);
          return btn[0];
        },
        _click: function (e) {
          if ($rootScope.currentUser) {
            L.DomEvent.stopPropagation(e);
            L.DomEvent.preventDefault(e);
            OpenSaveSelectionsPopup();
          } else {
            SignUpService.openModal('SignUpModal.html');
          }

        }

      });
      L.Control.SaveSelection = function (options) {
        return new L.Control.saveSelection(options);
      };

      // Clean selection
      L.Control.clearSelection = L.Control.extend({
        options: {
          position: 'topleft'
        },
        onAdd: function (map) {
            var scope = $rootScope.$new();
            var template = '    <div tooltip="Clean Selection" tooltip-placement="bottom" ng-show="$root.sortLayer != \'weather\'" class="map-tools-top hidden">\
                                    <div class="clear-selection">\
                                        <img src="../../assets/img/svg/cancel-button.svg"/>\
                                    </div>\
                                </div>';
            var btn = $compile(template)(scope);
            this._map = map;
            L.DomEvent.on(btn[0], 'click', this._click, this);
            return btn[0];
        },
        _click: function (e) {
            $rootScope.sidebarMessage = "Loading...";
            if ($rootScope.$state.current.name === 'areas.preview') {
                $location.path('/areas');
                $rootScope.mapState = "full-size";
            } else {
                L.DomEvent.stopPropagation(e);
                L.DomEvent.preventDefault(e);
                ClearSelections();
                map.closePopup();

                cancelHttpRequest();
                $rootScope.isDrawArea = false;
                $rootScope.$apply();

                angular.element('.leaflet-control-container .map-tools > div').removeClass('active');

                $rootScope.toggleSidebar(false);
            }
        }

      });
      L.Control.ClearSelection = function (options) {
        return new L.Control.clearSelection(options);
      };

	/**
	 * Focus Geolocation
	 */
	L.Control.focusGeolocation = L.Control.extend({
		options: {
			position: 'bottomleft'
		},
		onAdd: function (map) {
            var scope = $rootScope.$new();
            var template = '    <div tooltip="Focus Geolocation" tooltip-placement="right" class="focus-geolocation-container">\
                                    <div class="focus-geolocation map-tools">\
                                        <img src="../../assets/img/center.png"/>\
                                    </div>\
                                </div>';
            var btn = $compile(template)(scope);
            this._map = map;
            L.DomEvent.on(btn[0], 'click', this._click, this);
            return btn[0];
		},
		_click: function (e) {
			LocationService.getUserLocation(true)
                .then(function(location) {
                    $rootScope.currentLocation = {lat: location.latitude, lng: location.longitude};
                    FocusMapToGivenLocation($rootScope.currentLocation, 14);
                })
                .catch(function(err){
                    console.warn('Focus Geolocation error: ', err);
                });
		}
	});
	L.Control.SaveSelection = function (options) {
		return new L.Control.saveSelection(options);
	};

//weather
    L.Control.radar = L.Control.extend({
      options: {
        position: 'bottomright'
      },
      onAdd: function (map) {
          var scope = $rootScope.$new();
          var template = '  <div tooltip="Toggle Radar" tooltip-placement="left" ng-show="$root.sortLayer == \'weather\' && $root.isMapState()" class="toggle-button" ng-class="{\'active\': $root.isRadarShown}">\
                                <div>off</div>\
                                <div class="show-weather">\
                                    <img src="../../assets/img/svg/radar.svg"/>\
                                </div>\
                                <div>on</div>\
                            </div>';
          var btn = $compile(template)(scope);
          this._map = map;
          L.DomEvent.on(btn[0], 'click', this._click, this);
          return btn[0];
      },
      _click: function (e) {
          e.stopPropagation();
          $rootScope.isRadarShown = !$rootScope.isRadarShown;
          toggleWeatherLayer($rootScope.isRadarShown);
      }
    });
    L.Control.Weather = function (options) {
      return new L.Control.radar(options);
    };

//temperature
    L.Control.temperature = L.Control.extend({
      options: {
        position: 'bottomright'
      },
      onAdd: function (map) {
          var scope = $rootScope.$new();
          var template = '  <div tooltip="Toggle Units" tooltip-placement="left" ng-show="$root.sortLayer == \'weather\' && $root.isMapState()" class="toggle-button" ng-class="{\'active\': $root.weatherUnits === \'si\'}">\
                                <div>°F</div>\
                                <div class="show-weather">\
                                    <img src="../../assets/img/svg/temperature.svg"/>\
                                </div>\
                                <div>°C</div>\
                            </div>';
          var btn = $compile(template)(scope);
          this._map = map;
          L.DomEvent.on(btn[0], 'click', this._click, this);
          return btn[0];
      },
      _click: function (e) {
          e.stopPropagation();
          if ($rootScope.weatherUnits === 'si') {
              $rootScope.weatherUnits = 'us';
          } else {
              $rootScope.weatherUnits = 'si';
          }
          showWeatherMarkers();
      }
    });
    L.Control.Temperature = function (options) {
      return new L.Control.temperature(options);
    };

//fullScreen
    L.Control.fullScreen = L.Control.extend({
      options: {
        position: 'bottomleft'
      },
      onAdd: function (map) {
          var scope = $rootScope.$new();
          var template = '    <div tooltip="Toggle Fullscreen" tooltip-placement="right" class="fullscreen-container">\
                                  <div class="fullscreen">\
                                      <img ng-src="../../assets/img/svg/fullscreen{{!$root.isFullScreen ? \'\' : \'2\' }}.svg"/>\
                                  </div>\
                              </div>';
          var btn = $compile(template)(scope);
          this._map = map;
          L.DomEvent.on(btn[0], 'click', this._click, this);
          return btn[0];
      },
      _click: function(e) {
          e.stopPropagation();
          toggleFullScreen();
      }
    });
    L.Control.FullScreen = function (options) {
      return new L.Control.fullScreen(options);
    };

//filter
    L.Control.filter = L.Control.extend({
        options: {
          position: 'topleft'
        },
      onAdd: function (map) {
        var scope = $rootScope.$new();
        var template = '    <div tooltip="Show Filter" tooltip-placement="bottom" class="map-tools-top hidden">\
                                <div class="filter-selection">\
                                    <img src="../../assets/img/svg/filter.svg"/>\
                                </div>\
                            </div>';
        var btn = $compile(template)(scope);
        this._map = map;
        L.DomEvent.on(btn[0], 'click', this._click, this);
        return btn[0];
      },
      _click: function (e) {
          e.stopPropagation();
          if ($rootScope.isSidebarOpened) { //&& $rootScope.mapSortSpots.sourceSpots.length
            $rootScope.isFilterOpened = true;
            $rootScope.$apply();
          }
      }
    });
    L.Control.Filter = function (options) {
      return new L.Control.filter(options);
    };

//back
    L.Control.back = L.Control.extend({
        options: {
          position: 'topleft'
        },
      onAdd: function (map) {
          var scope = $rootScope.$new();
          var template = '    <div tooltip="Back to Map" tooltip-placement="bottom" class="map-tools-back">\
                                  <div class="save-selection">\
                                      <img src="../../assets/img/svg/back.svg"/>\
                                  </div>\
                              </div>';
          var btn = $compile(template)(scope);
          this._map = map;
          L.DomEvent.on(btn[0], 'click', this._click, this);
          return btn[0];
      },
      _click: function (e) {
          L.DomEvent.stopPropagation(e);
          L.DomEvent.preventDefault(e);
          ClearSelections();
          map.closePopup();

          cancelHttpRequest();
          $rootScope.isDrawArea = false;
          $rootScope.$apply();

          angular.element('.leaflet-control-container .map-tools > div').removeClass('active');

          $rootScope.toggleSidebar(false);

        $location.path('/');
      }
    });
    L.Control.Back = function (options) {
      return new L.Control.back(options);
    };

      //controls
      var lassoControl = L.Control.Lasso();
      var radiusControl = L.Control.Radius();
      var pathControl = L.Control.Path();
      var clearSelectionControl = L.Control.ClearSelection();
      var saveSelectionControl = L.Control.SaveSelection();
      var filter = new L.Control.filter();
	  var focusGeolocation = new L.Control.focusGeolocation();
      var fullScreen = new L.Control.fullScreen();
      var back = new L.Control.back();
      var radar = new L.Control.radar();
      var temperature = new L.Control.temperature();
      //var shareSelectionControl = L.Control.ShareSelection();

	  function clearPathFilter() {
		  $rootScope.routeInterpolated = null;
	  }

      //initialization
      function InitMap(mapDOMElement) {
        //Leaflet touch hook
        L.Map.mergeOptions({
          tap: true,
          tapTolerance: 15,
		  worldCopyJump: true
        });
        L.Map.Tap = L.Handler.extend({
          addHooks: function () {
            L.DomEvent.on(this._map._container, 'touchstart', this._onDown, this);
            L.DomEvent.on(this._map._container, 'touchend', this._onUp, this);
            L.DomEvent.on(this._map._container, 'touchmove', this._onMove, this);
          },
          removeHooks: function () {
            L.DomEvent.off(this._map._container, 'touchstart', this._onDown, this);
            L.DomEvent.off(this._map._container, 'touchend', this._onUp, this);
            L.DomEvent.off(this._map._container, 'touchmove', this._onMove, this);
          },
          _onDown: function (e) {
            if (!e.touches) {
              return;
            }
            L.DomEvent.preventDefault(e);


            if (e.touches.length > 1) {
              return;
            }
            var first = e.touches[0],
            el = first.target;
            this._simulateEvent('mousedown', first);
          },
          _onUp: function (e) {
            if (e && e.changedTouches) {

              var first = e.changedTouches[0],
              el = first.target;

              this._simulateEvent('mouseup', first);
            }
          },
          _onMove: function (e) {
            var first = e.changedTouches[0];
            this._newPos = new L.Point(first.clientX, first.clientY);
            this._simulateEvent('mousemove', first);
          },
          _simulateEvent: function (type, e) {

            var simulatedEvent = document.createEvent('MouseEvents');

            simulatedEvent._simulated = true;
            e.target._simulatedClick = true;

            simulatedEvent.initMouseEvent(
              type, true, true, window, 1,
              e.screenX, e.screenY,
              e.clientX, e.clientY,
              false, false, false, false, 0, null);

            e.target.dispatchEvent(simulatedEvent);
          }
        });
        if (L.Browser.touch) {
          L.Map.addInitHook('addHandler', 'tap', L.Map.Tap);
        }

        //Circle geoJson hook
        var circleToGeoJSON = L.Circle.prototype.toGeoJSON;
        L.Circle.include({
          toGeoJSON: function () {
            var feature = circleToGeoJSON.call(this);
            feature.properties = {
              point_type: 'circle',
              radius: this.getRadius()
            };
            return feature;
          }
        });

        //map init
          
        // the Skobbler map
          
        map = L.skobbler.map(mapDOMElement,{
              apiKey: SKOBBLER_API_KEY,
              mapStyle: 'night',
              bicycleLanes: false,
              onewayArrows: true,
              pois: '2',
              primaryLanguage: 'en',
              fallbackLanguage: 'de',
              mapLabels: 'localNaming',
              retinaDisplay: 'yes',
              zoomControl: true,
              zoomControlPosition: 'top-left',
              center: [37.405075073242188, -96.416015625000000],
              zoom: 5
          });
          
        // the Leaflet map (old)
          
        // map = L.map(mapDOMElement, {
        //   attributionControl: false,
        //   zoomControl: true,
		 //  worldCopyJump: true
        // });
        // L.tileLayer(tilesUrl, {
        //   maxZoom: 17,
        //   minZoom: 3
        // }).addTo(map);

        L.extend(map, {
            _getFeatures: function() {
                var out = [];
                for (var l in this._layers) {
                    if (typeof this._layers[l].getLatLng !== 'undefined') {
                        out.push(this._layers[l]);
                    }
                }
                return out;
            }
        });

        map.on('moveend', spotsOnScreen, this);

        //add controls
        AddControls();
        map.addLayer(bgLayer);
        map.addLayer(draggableMarkerLayer);
        map.addLayer(drawLayer);
        map.addLayer(markersLayer);
        ChangeState('big');

        map.setView(DEFAULT_MAP_LOCATION, 5);
        FocusMapToCurrentLocation(12);

        window.map = map;
        return map;
      }

      //return current map instance
      function GetMap() {
        return map;
      }

      //adjust map canvas size after block resize
      function InvalidateMapSize() {
        map.invalidateSize();
      }

      //Control group: return group with all map contols
      //aka wrapper for: lasso, radius, path, clear-selection, save-selection
      function GetControlGroup() {
        return controlGroup;
      }

      //return current active layer
      function GetCurrentLayer() {
        var layer = null;
        switch (currentLayer) {
          case "event":
            layer = eventsLayer;
            break;
          case "todo":
            layer = todoLayer;
            break;
          case "food":
            layer = foodLayer;
            break;
          case "shelter":
            layer = shelterLayer;
            break;
          case "other":
            layer = otherLayer;
            break;
          default:
            layer = null;
            break;
        }
        layer.name = currentLayer;

        return layer;
      }

      //get layer with draggable markers. (used for spot create markers, path markers etc.)
      function GetDraggableLayer() {
        return draggableMarkerLayer;
      }

      //Layers

      //switch map states;
      function ChangeState(state, clear) {
        switch (state.toLowerCase()) {
          case "big":
            if (clear) {
              showEventsLayer(true);
            }

            $rootScope.mapState = "full-size";
            map.scrollWheelZoom.enable();

            break;
          case "small":
            if (clear) {
              showOtherLayers();
            }

            $rootScope.mapState = "small-size";
            // map.scrollWheelZoom.disable();
            break;
          case "hidden":
            $rootScope.mapState = "hidden";
            removeAllLayers();
            break;
        }

        if (clear) {
          map.closePopup();
          markersLayer.clearLayers();
          draggableMarkerLayer.clearLayers();
          drawLayer.clearLayers();
          bgLayer.clearLayers();
        }

        $timeout(function () {
          map.invalidateSize();
        })
      }

      function showLayer(layer, keepListeners) {
        keepListeners = keepListeners === true;
        switch (layer) {
          case 'event':
            showEventsLayer(false, keepListeners);
            break;
          case 'todo':
            showTodoLayer(false, keepListeners);
            break;
          case 'food':
            showFoodLayer(false, keepListeners);
            break;
          case 'shelter':
            showShelterLayer(false, keepListeners);
            break;
          case 'other':
            showOtherLayers();
            break;
        }
      }

      //show events layer on map.
      function showEventsLayer(clearLayers, keepListeners) {
        if (keepListeners !== true) {
          ClearSelectionListeners();
        }
        if (clearLayers) {
          eventsLayer.clearLayers();
        }
        map.addLayer(eventsLayer);
        map.removeLayer(todoLayer);
        map.removeLayer(foodLayer);
        map.removeLayer(shelterLayer);
        map.removeLayer(otherLayer);
        currentLayer = "event";
      }

      //show food layer on map
      function showFoodLayer(clearLayers, keepListeners) {
        if (keepListeners !== true) {
          ClearSelectionListeners();
        }
        if (clearLayers) {
          foodLayer.clearLayers();
        }
        map.addLayer(foodLayer);
        map.removeLayer(shelterLayer);
        map.removeLayer(todoLayer);
        map.removeLayer(eventsLayer);
        map.removeLayer(otherLayer);
        currentLayer = "food";
      }

		/**
		 * If Map has the layer
		 * @param {string} layer
		 * @returns {boolean}
		 */
		function hasLayer(layer) {
			switch (layer) {
				case 'event':
				return map.hasLayer(eventsLayer);
				break;
			case 'todo':
	            return map.hasLayer(todoLayer);
		        break;
			case 'food':
	            return map.hasLayer(foodLayer);
		        break;
			case 'shelter':
				return map.hasLayer(shelterLayer);
				break;
			case 'other':
				return map.hasLayer(otherLayer);
				break;
			default:
				return false;
			}
		}

      //show shelter layer on map
      function showShelterLayer(clearLayers, keepListeners) {
        if (keepListeners !== true) {
          ClearSelectionListeners();
        }
        if (clearLayers) {
          shelterLayer.clearLayers();
        }
        map.addLayer(shelterLayer);
        map.removeLayer(todoLayer);
        map.removeLayer(foodLayer);
        map.removeLayer(eventsLayer);
        map.removeLayer(otherLayer);
        currentLayer = "shelter";
      }

      //show todo layer
      function showTodoLayer(clearLayers, keepListeners) {
        if (keepListeners !== true) {
          ClearSelectionListeners();
        }
        if (clearLayers) {
          todoLayer.clearLayers();
        }
        map.addLayer(todoLayer);
        map.removeLayer(eventsLayer);
        map.removeLayer(foodLayer);
        map.removeLayer(shelterLayer);
        map.removeLayer(otherLayer);
        currentLayer = "todo";
      }

      //show other layers
      function showOtherLayers() {
        ClearSelectionListeners();
        otherLayer.clearLayers();
        map.addLayer(otherLayer);
        map.removeLayer(todoLayer);
        map.removeLayer(foodLayer);
        map.removeLayer(shelterLayer);
        map.removeLayer(eventsLayer);
        currentLayer = "other";
      }

      //remove all layers from map
      function removeAllLayers() {
        currentLayer = "none";
        map.removeLayer(otherLayer);
        map.removeLayer(todoLayer);
        map.removeLayer(foodLayer);
        map.removeLayer(shelterLayer);
        map.removeLayer(eventsLayer);
      }

      //clear all layers
      function clearLayers() {
        eventsLayer.clearLayers();
        todoLayer.clearLayers();
        foodLayer.clearLayers();
        shelterLayer.clearLayers();
        otherLayer.clearLayers();
        draggableMarkerLayer.clearLayers();
      }

      //lasso selection.
      function LassoSelection(callback) {
        ClearSelectionListeners();
        map.dragging.disable();
        var started = false;
        var points = [];
        var polyline = null;

        map.on('mousedown', start);
        map.on('mousemove', move);
        map.on('mouseup', end);

        function start(e) {
			e.originalEvent.preventDefault();

          points = [];
          started = true;
          polyline = L.polyline([], {color: 'red'}).addTo(drawLayer);
          points.push(e.latlng);
          polyline.setLatLngs(points);
        }

		function move(e) {
			e.originalEvent.preventDefault();

          if (started) {
            points.push(e.latlng);
            polyline.setLatLngs(points);
          }
        }

        function end(e) {
			e.originalEvent.preventDefault();

          if (started) {
            ClearSelectionListeners();
            map.dragging.enable();
            started = false;
            points.push(points[0]);
            var b_box = polyline.getBounds();
            drawLayer.removeLayer(polyline);
            bgLayer.removeLayer(polyline);
            callback(GetConcaveHull(points), b_box);
          }
        }
      }

      // Radius selection
		function RadiusSelection(callback) {
			ClearSelectionListeners();
			map.dragging.disable();
			var started = false;
			var startPoint = null;
			var radius = 1000;
			var circle = null;

			map.on('mousedown', start);
			map.on('mousemove', move);
			map.on('mouseup', end);

			function start(e) {
				if ( started ) {
					return;
				}
				e.originalEvent.preventDefault();

				started = true;
				startPoint = L.latLng(e.latlng.lat, e.latlng.lng);
				circle = L.circle(e.latlng, radius, {color: 'red', weight: 3}).addTo(drawLayer);
			}

        function move(e) {
			e.originalEvent.preventDefault();

          if (started) {
            var endPoint = L.latLng(e.latlng.lat, e.latlng.lng);
            var distance = startPoint.distanceTo(endPoint);
            if (distance <= radiusSelectionLimit) {
              radius = distance;
              circle.setRadius(distance);
            }
          }
        }

        function end(e) {
			e.originalEvent.preventDefault();

          if (started) {
            ClearSelectionListeners();
            map.dragging.enable();
            started = false;
            var b_box = circle.getBounds();
            drawLayer.removeLayer(circle);
            bgLayer.removeLayer(circle);
            callback(startPoint, radius, b_box);
          }
        }
      }

      //Path selection
      function PathSelection(wpArray, callback) {
        var markers = [], line;
        var lineOptions = {};
        // lineOptions.styles = [{type: 'polygon', color: 'red', opacity: 0.6, weight: 10, fillOpacity: 0.2}, {
        //   color: 'red',
        //   opacity: 1,
        //   weight: 3
        // }];
        lineOptions.styles = [{type: 'polygon', weight: 3, color: '#00CFFF', opacity: 0.9, fillColor: '#0C2638', fillOpacity: 0.4}, {color: 'red', opacity: 1, weight: 3}];
        ClearSelectionListeners();
        var showNotification = true;
        pathSelectionStarted = true;
        if (wpArray) {
          for (var k in wpArray) {
            onMapClick({
              latlng: wpArray[k],
              originalEvent: new Event('click') // emulate a 'click' event
            }, null, true);
          }
          RecalculateRoute();
        } else {
          map.on('click', onMapClick);
        }

        function onMapClick(e, idx, dontBuildPath) {
          if (showNotification) {
            showNotification = false;
            toastr.info('Place Pin at Destination(s)');
          }
          // ignore empty originalEvent
          if (e && e.originalEvent !== undefined) {
            e.originalEvent.preventDefault();
          }
          var marker = L.marker(e.latlng, {draggable: !e.latlng.ignoreMarkerEvents}).addTo(markersLayer);
          if (!isNaN(idx)) {
            markers.splice(idx + 1, 0, marker);
          } else {
            markers.push(marker);
          }

          //var popup = RemoveMarkerPopup(
          //  function () {
          //    for (var k in markers) {
          //      if (markers[k]._leaflet_id == marker._leaflet_id) {
          //        markersLayer.removeLayer(marker);
          //        markers.splice(k, 1);
          //        RecalculateRoute();
          //      }
          //    }
          //  },
          //  function () {
          //    marker.closePopup();
          //  });
          //
          //marker.bindPopup(popup);

          if ( !e.latlng.ignoreMarkerEvents  ) {
            marker.on('dragend', function () {
              // ignore empty originalEvent
              if (e && e.originalEvent !== undefined) {
                e.originalEvent.preventDefault();
              }
              angular.element('.cancel-selection').on('click', function () {
                ClearSelectionListeners();
                map.closePopup();
              });
              RecalculateRoute();
            });
          }

          if (!dontBuildPath) {
            RecalculateRoute();
          }
        }
		/** Recalculate Route */
        function RecalculateRoute() {
			if (markers.length >= 2) {
				var waypoints = _.map(markers, function (m) {
					return {latLng: m.getLatLng()};
				});

				lazyRouter(waypoints, function (err, routes) {

					if (line) {
						drawLayer.removeLayer(line);
                        bgLayer.removeLayer(line);
						line.off('linetouched');
					}
					if (err) {
						console.log('route failed', pathRouterFail);
						if (pathRouterFail === 0) {
							pathRouterFailed();
							RecalculateRoute();
						} else {
							console.warn(err);
							$rootScope.$broadcast('impossible-route');
						}
					} else {
						$rootScope.routeInterpolated = [];
						var simplified = turf.simplify(L.polyline(routes[0].coordinates).toGeoJSON(), 0.02, false);
            // console.debug('simplify', simplified);
						simplified.geometry.coordinates.forEach(function(e) {
							$rootScope.routeInterpolated.push({latLng: {lat: e[1], lng: e[0]}});
						});

						line = L.Routing.line(routes[0], lineOptions);
                        var lastKey = line._layers[Object.keys(line._layers)[Object.keys(line._layers).length - 1]];
                        var tmp = line._layers[Object.keys(line._layers)[Object.keys(line._layers).length - 2]];
                        var almostLastKey = tmp._layers[Object.keys(tmp._layers)[Object.keys(tmp._layers).length - 1]];
                        var lineLatlngs = lastKey._latlngs;
                        var polyLatlngs = almostLastKey._latlngs;
                        drawLayer.clearLayers();
                        bgLayer.clearLayers();
                        L.polygon([[[90, 180],[90, -180],[-90, -180],[-90, 180]], polyLatlngs], {
                            weight: 3,
                            color: '#00CFFF',
                            opacity: 0.9,
                            fillColor: '#0C2638',
                            fillOpacity: 0.4,
                        }).addTo(bgLayer);

                        L.polyline(lineLatlngs, {color: 'red', smoothFactor: 1}).addTo(bgLayer);

                        L.polygon(polyLatlngs, {
                          opacity: 0.0,
                          fill: false
                        }).addTo(drawLayer);

                        // line.addTo(drawLayer);

						line.on('linetouched', function (e) {
							function remove() {
								for (var k in markers) {
									markersLayer.removeLayer(markers[k]);
									var bboxes = GetDrawLayerBBoxes();
									GetDataByBBox(bboxes);
								}
								drawLayer.removeLayer(line);
                                bgLayer.removeLayer(line);
								map.closePopup();
								ClearSelectionListeners();
							}

							function cancel() {
								map.closePopup();
							}

							function addmarker() {
								onMapClick(e, e.afterIndex);
								map.closePopup();
							}

							//var popup = RemoveMarkerPopup(remove, cancel, addmarker, e.latlng);
							//
							//popup.openOn(map);
						});

						if (callback) {
							// a small timeout in order to wait for a route been recalculated & rendered
							$timeout(callback, 1000);
						}
					}
				}, {geometryOnly: true});
			} else {
				if (!pathSelectionStarted) {
					for (var k in markers) {
						markersLayer.removeLayer(markers[k]);
					}
				}
				if (line) {
					drawLayer.removeLayer(line);
                    bgLayer.removeLayer(line);
					line.off('linetouched');
				}
			}
        }
      }

      function GetBoundsByCircle(latlng, callback) {
        var circle = null;
        circle = L.circle(latlng, 5000, {color: 'red', weight: 3}).addTo(drawLayer);
        var b_box = circle.getBounds();
        callback(b_box);
      }

      //cancel path selection
      function CancelPathSelection() {
        pathSelectionStarted = false;
      }

      //weather selection
	function WeatherSelection(callback, geocodeCallback) {
		// map.on('click', function (e) {
		// 	var lng = e.latlng.lng;
		// 	if (Math.abs(lng) > 180) {
		// 		lng = lng > 0 ? lng -= 360 : lng += 360;
		// 	}
		// 	$http.get(API_URL + '/weather?lat=' + e.latlng.lat + '&lng=' + lng)
        //     .success(function (data) {
		// 		callback(data);
        //     })
        //     .error(function (data) {
		// 		callback(null);
        //     });
		// 	$http.jsonp('https://nominatim.openstreetmap.org/reverse', {params: {lat: e.latlng.lat, lon: lng, "accept-language": 'en', format: 'json', json_callback: 'JSON_CALLBACK'}})
		// 		.then(function(resp) {
		// 			if (resp.status === 200 && geocodeCallback && typeof geocodeCallback === 'function') {
		// 				geocodeCallback(resp.data);
		// 			}
		// 		});
        // });

      }

      function getWeatherLatLng(setWeatherLatLng) {
          map.on('click', function(e) {
              var lng = e.latlng.lng;
              if (Math.abs(lng) > 180) {
                  lng = lng > 0 ? lng -= 360 : lng += 360;
              }
              setWeatherLatLng(e.latlng.lat, lng);
          });
      }

      //remove all selection listeners
      function ClearSelectionListeners() {
        map.off('mousedown');
        map.off('mousemove');
        map.off('mouseup');
        map.off('touchstart');
        map.off('touchmove');
        map.off('touchend');
        map.off('click');
        CancelPathSelection();
      }

      function OpenSaveSelectionsPopup(share) {
        var wp = GetDrawLayerPathWaypoints();
        var geoJson = GetDrawLayerGeoJSON();

        if (wp.length > 0 || geoJson && geoJson.features.length > 0) {
         // FitBoundsOfDrawLayer();
        //  map.zoomOut();

          var modalInstance = $modal.open({
            animation: true,
            templateUrl: '/app/components/map_partials/saveSelection/saveSelection.html',
            controller: 'SaveSelectionController',
            modalClass: 'save-selection-modal',
            modalContentClass: 'clearfix'
          });

          modalInstance.result.then(function (data) {
            SaveSelections(data.title, data.description, share);
          });
        } else {
          toastr.error('You can\'t save empty selection.');
        }
      }

      //save selection
      function SaveSelections(title, description, share) {
        ClearSelectionListeners();
        if (pathSelectionStarted) {
          CancelPathSelection();
        }

        getScreenshot(function (image) {
          var wp = GetDrawLayerPathWaypoints();
          var req = {
            title: title,
            description: description,
            waypoints: wp,
            zoom: map.getZoom(),
            data: drawLayerGeoJSON,
            cover: image
          };

          req.data.searchLayer = $rootScope.sortLayer;

          Area.save(req, function (data) {
            toastr.success('Selection saved!');
          }, function (data) {
            toastr.error('Error!')
          });
        });
      }

      function getScreenshot(callback) {
        if ($ocLazyLoad.isLoaded('html2canvas')) {
          exec();
        } else {
          $ocLazyLoad.load('html2canvas').then(exec);
        }
        function exec() {
          lazyGetScreenshot(callback);
        }
      }

      function lazyGetScreenshot(callback) {
        var controls = $('.leaflet-control-container, .sidebar-menu-wrap');
        var mapPane = $(".leaflet-map-pane")[0];

        var mapTf = getTransformation(mapPane.style.transform);

        mapPane.style.transform = "";
        mapPane.style.left = mapTf.x + "px";
        mapPane.style.top = mapTf.y + "px";

        var myTiles = $("img.leaflet-tile");
        var tilesLeft = [];
        var tilesTop = [];
        var tileMethod = [];
        for (var i = 0; i < myTiles.length; i++) {
          var tile = myTiles[i];
          var css = tile.style;
          if (css.left != "") {
            tilesLeft.push(parseFloat(css.left));
            tilesTop.push(parseFloat(css.top));
            tileMethod[i] = "left";
          } else if (css.transform != "") {
            var tileTransform = css.transform.split(",");
            tilesLeft[i] = parseFloat(tileTransform[0].split("(")[1]);
            tilesTop[i] = parseFloat(tileTransform[1]);
            css.transform = "";
            tileMethod[i] = "transform";
          } else {
            tilesLeft[i] = 0;
            tilesTop[i] = 0;
            tileMethod[i] = "neither";
          }
          css.left = (tilesLeft[i]) + "px";
          css.top = (tilesTop[i]) + "px";
        }

        var myDivicons = $(".leaflet-marker-icon");
        var dx = [];
        var dy = [];
        for (var i = 0; i < myDivicons.length; i++) {
          var iconCss = myDivicons[i].style;
          var curTf = getTransformation(iconCss.transform);
          dx.push(curTf.x);
          dy.push(curTf.y);
          iconCss.transform = "";
          iconCss.left = dx[i] + "px";
          iconCss.top = dy[i] + "px";
        }

        var mapWidth = parseFloat($("#map").css("width"));
        var mapHeight = parseFloat($("#map").css("height"));

        var linesLayer = $("canvas.leaflet-zoom-animated")[0];

        var oldViewbox = linesLayer.getAttribute("viewBox");
        linesLayer.setAttribute("viewBox", "0 0 " + mapWidth + " " + mapHeight);

        var linesTf = getTransformation(linesLayer.style.transform);
        linesLayer.style.transform = "";
        linesLayer.style.left = linesTf.x + "px";
        linesLayer.style.top = linesTf.y + "px";

        controls.hide();

        html2canvas(document.getElementById("map"), {
          useCORS: true,
          logging: true,
          allowTaint: false,
          taintTest: false,
          onrendered: function onrendered(canvas) {
            for (var i = 0; i < myTiles.length; i++) {
              var css = myTiles[i].style;
              if (tileMethod[i] === "left") {
                css.left = (tilesLeft[i]) + "px";
                css.top = (tilesTop[i]) + "px";
              } else if (tileMethod[i] == "transform") {
                css.left = "";
                css.top = "";
                css.transform = "translate(" + tilesLeft[i] + "px, " + tilesTop[i] + "px)";
              } else {
                css.left = "0px";
                css.top = "0px";
                css.transform = "translate(0px, 0px)";
              }
            }

            for (var i = 0; i < myDivicons.length; i++) {
              var iconCss = myDivicons[i].style;
              iconCss.transform = "translate(" + dx[i] + "px, " + dy[i] + "px)";
              iconCss.left = "0px";
              iconCss.top = "0px";
            }

            linesLayer.style.transform = "translate(" + linesTf.x + "px," + linesTf.y + "px)";
            linesLayer.setAttribute("viewBox", oldViewbox);
            linesLayer.style.left = "0px";
            linesLayer.style.top = "0px";

            mapPane.style.transform = "translate(" + mapTf.x + "px," + mapTf.y + "px)";
            mapPane.style.left = "";
            mapPane.style.top = "";

            var image = canvas.toDataURL("image/jpeg");
            callback(image);

          }
        });

        controls.show();

      }

      function getTransformation(transform){
        var tf = transform.split(",");
        return {
          x:parseFloat(tf[0].split("(")[1]),
          y:parseFloat(tf[1])
        };
      }

      /**
       * Load selection from server
       */
      function LoadSelections(selection) {

        if (selection.waypoints && selection.waypoints.length > 0) {
          _.each(selection.waypoints, function (array) {
            PathSelection(array, function () {
              var bboxes = GetDrawLayerBBoxes();
              GetDataByBBox(bboxes);
            });
          });
        }

        if (selection.data) {
          if (selection.data.searchLayer) {
            $rootScope.toggleLayer(selection.data.searchLayer, true);
          }
          L.geoJson(selection.data, {
            onEachFeature: function (feature) {
              if (feature.geometry.type = 'Point' && feature.properties.radius) {
                var startPoint = L.GeoJSON.coordsToLatLng(feature.geometry.coordinates);
                var radius = feature.properties.radius;

                // var circle = L.circle(startPoint, radius, {
                //   weight: 3,
                //   color: '#00CFFF',
                //   opacity: 0.9,
                //   fillColor: '#0C2638',
                //   fillOpacity: 0.4
                // });

                var circle = L.circle(startPoint, radius, {
                  opacity: 0.0,
                  fill: false,
                });

                circle.addTo(drawLayer);

                var bboxes = GetDrawLayerBBoxes();

                var rds = (bboxes[0].getNorthWest().lat - bboxes[0].getSouthWest().lat) / 2;
                var polyCrcl = plygonFromCircle(startPoint.lat, startPoint.lng, rds)

                L.polygon([polyCrcl, [[90, -180],[90, 180],[-90, 180],[-90, -180]]], {
                  weight: 3,
                  color: '#00CFFF',
                  opacity: 0.9,
                  fillColor: '#0C2638',
                  fillOpacity: 0.4
              }).addTo(bgLayer);

                //var popup = RemoveMarkerPopup(
                //  function () {
                //    drawLayer.removeLayer(circle);
                //    var bboxes = GetDrawLayerBBoxes();
                //    GetDataByBBox(bboxes);
                //  },
                //  function () {
                //    circle.closePopup();
                //  });
                //
                //circle.bindPopup(popup);

                circle.addTo(drawLayer);
              } else {
                _.each(feature.geometry.coordinates, function (coords) {
                  var points = L.GeoJSON.coordsToLatLngs(coords);
                  //[[[90, 180],[90, -180],[-90, -180],[-90, 180]], polyLatlngs]
                  var poly = L.polygon([[[90, -180],[90, 180],[-90, -180],[-90, 180]], points], {
                    weight: 3,
                    color: '#00CFFF',
                    opacity: 0.9,
                    fillColor: '#0C2638',
                    fillOpacity: 0.4
                }).addTo(bgLayer);

                  var poly = L.polygon(points, {
                    opacity: 0.0,
                    fill: false
                  }).addTo(drawLayer);

                  //var popup = RemoveMarkerPopup(
                  //  function () {
                  //    drawLayer.removeLayer(poly);
                  //    var bboxes = GetDrawLayerBBoxes();
                  //    GetDataByBBox(bboxes);
                  //  },
                  //  function () {
                  //    poly.closePopup();
                  //  });
                  //
                  //poly.bindPopup(popup);
                });
              }
            }
          });
        }
        var bboxes = GetDrawLayerBBoxes();
        $timeout(function() {
          GetDataByBBox(bboxes, true);
          // $timeout(function(){ // enable selection-based zoom if needed
          //   if (selection.zoom) {
          //     map.setZoom(selection.zoom);
          //   }
          // }, 10);
        }, 100);
      }

      function ClearSelections(mapOnly, ignoreBBoxes) {
		    clearPathFilter();
        markersLayer.clearLayers();
        draggableMarkerLayer.clearLayers();
        drawLayer.clearLayers();
        bgLayer.clearLayers();
        eventsLayer.clearLayers();
        foodLayer.clearLayers();
        shelterLayer.clearLayers();
        todoLayer.clearLayers();
        otherLayer.clearLayers();

        ClearSelectionListeners();
        if (pathSelectionStarted) {
          CancelPathSelection();
        }

        if (!ignoreBBoxes) {
          GetDrawLayerBBoxes();
          GetDataByBBox([]);
        } {
          _activateControl(false);
        }

        if (!mapOnly) {
          $rootScope.$broadcast('clear-map-selection');
        }
      }

      //Controls
      function RemoveControls() {
        map.removeLayer(temperature);
        map.removeLayer(radar);
        map.removeLayer(radiusControl);
        map.removeLayer(lassoControl);
        map.removeLayer(pathControl);
        map.removeLayer(back);
        //map.removeLayer(shareSelectionControl);
        map.removeLayer(saveSelectionControl);
        map.removeLayer(clearSelectionControl);
		    map.removeLayer(focusGeolocation);
        map.removeLayer(fullScreen);
        map.removeLayer(filter);
      }

      function ToggleSelectionControls(show) {
        if (show) {
          $('.map-tools.map-tools-selection').removeClass('hidden');
        } else {
          $('.map-tools.map-tools-selection').addClass('hidden');
        }
      }

      function AddControls() {
        temperature.addTo(map);
        radar.addTo(map);
        focusGeolocation.addTo(map);
        saveSelectionControl.addTo(map);
        filter.addTo(map);
        clearSelectionControl.addTo(map);
        //shareSelectionControl.addTo(map);
        pathControl.addTo(map);
        lassoControl.addTo(map);
        radiusControl.addTo(map);
        fullScreen.addTo(map);
        back.addTo(map);
      }

      //Makers
      function CreateMarker(latlng, options) {
        var options = options || {};
        //IMPORTANT:
        //I used this construction because L.MarkerCluster is not supporting draggable markers
        //But we still need to cluster markers on some pages. So all draggable marker will be in draggable marker layer
        options.riseOnHover = true;
        var marker = L.marker(latlng, options);
        if (options.draggable) {
          draggableMarkerLayer.addLayer(marker);
        } else {
          GetCurrentLayer().addLayer(marker);
        }

        return marker;
      }

      function RemoveMarker(Marker, callback) {
        //IMPORTANT:
        //I used this construction because L.MarkerCluster is not supporting draggable markers
        //But we still need to cluster markers on some pages. So all draggable marker will be in draggable marker layer
        if (Marker.options.draggable) {
          draggableMarkerLayer.removeLayer(Marker);
        } else {
          GetCurrentLayer().removeLayer(Marker);
        }

        if (callback) callback();
      }

      function CreateCustomIcon(iconUrl, type, item) {
          if (item && type != 'weather') {
              var spot = item.spot ? item.spot : item;
              var image = setMarkerIcon(spot);
              return L.icon({
                  iconSize: [50, 50],
                  iconUrl: image,
                  className: 'spot-icon'
              });
          } else if (type == 'album' || type == 'photomap') {
              return new L.HtmlIcon({
                  html: "<div class='map-marker-icon map-marker-icon-photo'><img src='" + iconUrl + "' /></div>",
              });
          } else if (type == 'friends') {
              return new L.HtmlIcon({
                  html: "<div class='map-marker-icon map-marker-icon-friend'><img src='" + iconUrl + "' /></div>",
              });
          } else if(type == 'weather') {
              var temp = +(item.main.temp).toFixed(0);
              var color;
              if (temp > 30)
                  color = 'hot';
              else if (temp > 10)
                  color = 'warm';
              else if (temp < -30)
                  color = 'cold'
              else if (temp < -10)
                  color = 'cool';
              else
                color = 'normal';
              return new L.HtmlIcon({
                  html: "<div class='map-marker-icon map-marker-icon-weather'><span class='" + color + "'>" + temp + "°</span></div>",
              });
          } else {
              return L.icon({
                  iconSize: [50, 50],
                  iconUrl: iconUrl,
                  className: 'custom-map-icons'
              });
          }
      }

      function BindMarkerToInput(Marker, Callback) {
        Marker.off('dragend');
        Marker.on('dragend', function (e) {
          var latlng = Marker.getLatLng();
          GetAddressByLatlng(latlng, function (response) {
            if (response.display_name) {
              Callback({latlng: latlng, address: response.display_name})
            } else {
              Callback({latlng: latlng, address: 'Unknown place'});
            }
          })
        });
      }

      function BindSpotPopup(marker, spot) {
        var spot_id = spot.id ? spot.id : spot.spot.id;
        var spot = spot.spot ? spot.spot : spot;
        var scope = $rootScope.$new();
        if (spot.minrate) {
            if (!_.isEmpty(fx.rates)) {
                spot.price = '$' + Math.round(fx(spot.minrate).from(spot.currencycode).to("USD"));
            } else {
                spot.price = Math.round(spot.minrate) + ' ' + spot.currencycode;
            }
        }
        scope.item = spot;
        scope.marker = marker;
        var options = {
          keepInView: false,
          autoPan: true,
          closeButton: false,
          className: 'map-marker-plate'
        };

        scope.image = setMarkerIcon(spot, true);
        var template = '<div>\
                            <p class="plate-name">{{item.title}}</p>\
                            <p class="plate-stars"><stars item="item"></stars></p>\
                            <p class="plate-info price" ng-if="item.minrate">{{item.price}}</p>\
                            <p class="plate-info start-date" ng-if="item.start_date">{{item.start_date | date:\'MMM d\'}}</p>\
                            <p class="plate-info" ng-if="!item.minrate">{{item.category.name}}</p>\
                            <img width="50" height="50" src="{{image}}" />\
                        </div>';
        var popupContent = $compile(template)(scope);
        var popup = L.popup(options).setContent(popupContent[0]);
        marker.bindPopup(popup);
        marker.on('click', function () {
            if ($(window).width() > 767) {
                if ($rootScope.isMapState()) {
                    $rootScope.setOpenedSpot(null);
                    $http.get(API_URL + '/spots/' + spot.spot_id)
                        .success(function success(data) {
                            $rootScope.setOpenedSpot(data);
                        });
                } else {
                    var user_id = spot.user_id || spot.spot.user_id || 0;
                    $location.path(user_id + '/spot/' + spot_id);
                }
            } else {
                if(marker.isHighlighted) {
                    marker.isHighlighted = false;
                    $rootScope.setOpenedSpot(null);
                    $http.get(API_URL + '/spots/' + spot.spot_id)
                        .success(function success(data) {
                            $rootScope.setOpenedSpot(data);
                        });
                } else {
                    if (mobileMarker) {
                        mobileMarker.isHighlighted = false;
                    }
                    marker.openPopup();
                    marker.isHighlighted = true;
                    mobileMarker = marker;
                }
            }
        });
      }

      function _loadSpotComments(scope, spot) {
        scope.item.spot = spot;

        var params = {
          page: 1,
          limit: 10,
          spot_id: spot.id
        };
        SpotComment.query(params, function (comments) {
          scope.item.spot.comments = comments.data;
          SpotService.initMarker(scope.item.spot);

          scope.item.$loading = false;
        }, function (resp) {
          console.warn(resp);
        });
      }

      function BindBlogPopup(marker, post) {
        var scope = $rootScope.$new();
        scope.item = post;
        scope.marker = marker;
        var options = {
          keepInView: false,
          autoPan: true,
          closeButton: false,
          className: 'popup post-popup'
        };

        if (!$rootScope.isMobile) {
          var offset = 75;
          options.autoPanPaddingTopLeft = L.point(offset, offset);
          options.autoPanPaddingBottomRight = L.point(offset, offset)
        }
        var popupContent = $compile('<post-popup post="item" marker="marker"></post-popup>')(scope);
        var popup = L.popup(options).setContent(popupContent[0]);
        marker.bindPopup(popup);
      }

      //function RemoveMarkerPopup(remove, cancel, addmarker, location) {
      //  var scope = $rootScope.$new();
      //  scope.remove = remove;
      //  scope.cancel = cancel;
      //  scope.addmarker = addmarker;
      //
      //  scope.popup = L.popup({
      //    keepInView: false,
      //    autoPan: true,
      //    offset: L.point(-40, 0),
      //    closeButton: false,
      //    className: 'remove-popup clearfix'
      //  }).setLatLng(location);
      //  var popupContent = $compile('<confirm-popup></confirm-popup>')(scope);
      //  scope.popup.setContent(popupContent[0]);
      //
      //
      //  return scope.popup;
      //}

      //Processing functions
      //Return concave hull from points array
      function GetConcaveHull(latLngs) {
        return new ConcaveHull(latLngs).getLatLngs();
      }

      //Determine if point inside polygon or not
      function PointInPolygon(point) {
        var result = [];
        drawLayer.eachLayer(function (layer) {
          if (result.length < 1) {
            if (layer._route) {
              layer.eachLayer(function (l) {
                if (!l._latlngs) {
                  result = leafletPip.pointInLayer([point.lng, point.lat], l);
                }
              });
            } else {
              if (layer.toGeoJSON) {
                var l = layer.toGeoJSON();
                var geoJSONlayer = L.geoJson(l);
                if (l.geometry.type != 'Point') {
                  result = leafletPip.pointInLayer([point.lng, point.lat], geoJSONlayer);
                } else {
                  if (l.properties.point_type == 'circle') {
                    var latlng = L.GeoJSON.coordsToLatLng(l.geometry.coordinates);
                    if (latlng.distanceTo(point) < l.properties.radius) {
                      result.push({PointInCircle: true});
                    }
                  }
                }
              }
            }
          }
        });
        return result.length > 0;
      }

      function GetAddressByLatlng(latlng, callback) {
        var url = GeocodingReverseUrl + "&lat=" + latlng.lat + "&lon=" + latlng.lng;
        $http.get(url, {withCredentials: false}).
          success(function (data, status, headers) {
            callback(data);
          }).
          error(function (data, status, headers) {
            callback(data);
          });
      }

      function GetLatlngByAddress(address, callback) {
        var url = GeocodingSearchUrl + address
        $http.get(url, {withCredentials: false}).
          success(function (data, status, headers) {
            callback(data);
          }).
          error(function (data, status, headers) {
            callback(data);
          });
      }

      function GetCurrentLocation(callback) {
        map.locate({setView: true})
          .on('locationfound', function onLocationFound(e) {
            map.off('locationfound');
            map.off('locationerror');
            callback(e);
          })
          .on('locationerror', function onLocationError(e) {
            map.off('locationfound');
            map.off('locationerror');
            callback(e);
          });

        //map.locate({setView: false});
      }

		function FocusMapToCurrentLocation(zoom) {
			if ($state.current.name != 'index')
				return;
			zoom = zoom || 8;

			LocationService.getUserLocation().then(function(location) {
				$rootScope.currentLocation = {lat: location.latitude, lng: location.longitude};
				$rootScope.currentCountryCode = location.countryCode ? location.countryCode : 'N/A';
				FocusMapToGivenLocation($rootScope.currentLocation, 14, true);
			});
		}

		function FocusMapToGivenLocation(location, zoom, pause) {
			if (location.lat && location.lng) {
				setTimeout(function() {
					map.panTo(new L.LatLng(location.lat, location.lng));
				}, !pause ? 1 : 500);
				if (zoom) {
					map.setZoom(zoom);
				}
			}
		}

      /**
       * fixme!
       * @param padding
       * @constructor
	     */
      function PanToVisibleCenter(padding) {
        if ( !_.isObject(padding) ) return;
        var c = map.getCenter();
        var sz = map.getSize();
        var bs = map.getBounds();

        console.log('Size', sz);
        console.log('Bounds', bs, 'x1', bs.getWest(), 'x2', bs.getEast());

        var oldX = sz.x / 2;
        var oldY = sz.y / 2

        console.log('oldX: ', oldX, 'oldY', oldY);

        var newX = (sz.x - (padding.right || 0) + (padding.left || 0)) / 2;
        var newY = (sz.y - (padding.bottom || 0) + (padding.top || 0)) / 2;

        console.log('newX', newX, 'newY', newY); // новые координаты центра в px

        console.log('lng diff: ', bs.getEast() - bs.getWest());

        var dgrPerPixel = Math.abs(bs.getEast() - bs.getWest()) / sz.x;

        var newLat = c.lat*1 + ( oldY - newY ) * dgrPerPixel;
        var newLng = c.lng*1 + ( oldX - newX ) * dgrPerPixel;

        console.log('dgrPerPixel', dgrPerPixel);

        if ( newLat > 180 ) newLat -= 180;
        if ( newLat < -180 ) newLat += 180;
        if ( newLng > 180 ) newLng -= 180;
        if ( newLng < -180 ) newLng += 180;

        console.log('newLat', newLat, 'newLng', newLng);

        var newCenter = new L.LatLng(newLat , newLng);
        map.panTo( newCenter );
      }

      function FitBoundsByLayer(layer) {
        //currentLayer = layer;
        var bounds = GetCurrentLayer().getBounds();
        map.fitBounds(bounds);
      }

      function FitBoundsByCoordinates(coordinates) {
          map.fitBounds(coordinates);
      }

      function FitBoundsOfCurrentLayer() {
        var layer = GetCurrentLayer();
        if (layer) {
          var bounds = layer.getBounds();
          if (!_.isEmpty(bounds)) {
            map.fitBounds(bounds);
          }
        }
      }

      function FitBoundsOfDrawLayer() {
        var bounds = drawLayer.getBounds();
        if (!_.isEmpty(bounds)) {
          map.fitBounds(bounds);
        }
      }

      function GetDrawLayerBBoxes() {
        var bboxes = [];
        drawLayer.eachLayer(function (layer) {
          if (layer._route) {
            layer.eachLayer(function (l) {
              if (!l._latlngs) {
                bboxes.push(l.getBounds());
              }
            });
          } else {
            if (layer.getBounds) {
              bboxes.push(layer.getBounds());
            }
          }
        });

        var wp = GetDrawLayerPathWaypoints();
        drawLayerGeoJSON = GetDrawLayerGeoJSON();

        if ((wp.length > 0 || drawLayerGeoJSON.features.length > 0)) {
            $rootScope.toggleSidebar(true);
        } else {
            $rootScope.toggleSidebar(false);
        }
        return bboxes;
      }

      function GetDrawLayerGeoJSON() {
        var json = drawLayer.toGeoJSON();

        json.features = _.reject(json.features, function (item) {
          return item.geometry.type == "FeatureCollection";
        });

        return json;
      }

      function GetDrawLayerPathWaypoints() {
        var waypoints = [];
        drawLayer.eachLayer(function (layer) {
          if (layer._route) {
            var points = _.map(layer._route.waypoints, function (item) {
              return item.latLng;
            });
            waypoints.push(points);
          }
        });

        return waypoints;
      }

      //fix $.param bug with object keys
      function BBoxToParams(bbox_array) {
        function toSimpleObject(obj) {
          return _.object(_.map(obj, function (item, key) {
            return [key, item]
          }));
        }

        return _.map(bbox_array, function (item, key) {
          var obj = toSimpleObject(item);

          for (var i in obj) {
            obj[i] = toSimpleObject(obj[i]);
          }

          return obj;
        });
      }

      function GetDataByBBox(bbox_array, isFocus) {
        isFocus = isFocus || false;
        if (bbox_array.length > 0) {
          $rootScope.doSearchMap();

          if (isFocus) {
            FitBoundsOfDrawLayer();
          }
        } else {
          clearLayers();
          $rootScope.$emit('update-map-data', [], null, false);
        }
      }


      //return sorted by rating array
      function SortByRating(array) {
        return _.sortBy(array, function (item) {
          return item.spot.rating;
        }).reverse();
      }

      //return sorted by end_date array
      function SortByDate(array) {
        return _.sortBy(array, function (item) {
          return moment(item.endDate, 'YYYY-MM-DD HH:mm:ss').format('x');
        });
      }

      function ClampByDate(array, startDate, endDate) {
        if (startDate && endDate) {
          var start = moment(startDate, 'MM.DD.YYYY');
          var end = moment(endDate, 'MM.DD.YYYY');

          var newArray = _.reject(array, function (item) {
            var itemStart = moment(item.spot.start_date, 'YYYY-MM-DD HH:mm:ss');
            var itemEnd = moment(item.spot.end_date, 'YYYY-MM-DD HH:mm:ss');
            var byStart = itemStart.isAfter(start, 'seconds') && itemStart.isBefore(end, 'seconds');
            var byEnd = itemEnd.isAfter(start, 'seconds') && itemEnd.isBefore(end, 'seconds');

            return !(byStart || byEnd);
          });
          return SortByDate(newArray);
        }

        if (startDate && !endDate) {
          var start = moment(startDate, 'MM.DD.YYYY');

          var newArray = _.reject(array, function (item) {
            var itemStart = moment(item.spot.start_date, 'YYYY-MM-DD HH:mm:ss');
            var itemEnd = moment(item.spot.end_date, 'YYYY-MM-DD HH:mm:ss');


            return !(itemStart.isAfter(start, 'seconds') || itemEnd.isAfter(start, 'seconds'))
          });
          return SortByDate(newArray);
        }

        if (!startDate && endDate) {
          var end = moment(endDate, 'MM.DD.YYYY');


          var newArray = _.reject(array, function (item) {
            var itemStart = moment(item.spot.start_date, 'YYYY-MM-DD HH:mm:ss');
            var itemEnd = moment(item.spot.end_date, 'YYYY-MM-DD HH:mm:ss');


            return !(itemStart.isBefore(end, 'seconds') || itemEnd.isBefore(end, 'seconds'))
          });
          return SortByDate(newArray);
        }
      }

      //return sorted by rating only for selected categories
      function SortBySubcategory(array, categories) {
        var resultArray = array;
        if (categories.length > 0) {
          resultArray = _.reject(array, function (item) {
            var result = true;

            for (var k in categories) {
              if (categories[k].id == item.spot.category.id) {
                result = false;
                break;
              }
            }

            return result;
          });
        }
        return SortByRating(resultArray);
      }

      function drawSpotMarkers(spots, type, clear) {
        if (clear) {
          GetCurrentLayer().clearLayers();
        }
        var markers = [];
        console.log(type);
        _.each(spots, function (item) {
          var icon = CreateCustomIcon(item.spot.category.icon_url, type, item);
          if (item.location) {
            var marker = L.marker(item.location, {icon: icon});
            item.marker = marker;
            BindSpotPopup(marker, item);

            markers.push(marker);
          } else if (item.locations) {
            var spotMarkers = [];
            _.each(item.locations, function (point) {
              var spot = angular.copy(item);
              spot.address = point.address;

              spot.location = point.location;

              var marker = L.marker(spot.location, {icon: icon});

              BindSpotPopup(marker, spot);

              spotMarkers.push(marker);
              markers.push(marker);
            });

            item.markers = spotMarkers;
          }
        });

        switch (type) {
          case 'food':
            foodLayer.addLayers(markers);
            break;
          case 'shelter':
            shelterLayer.addLayers(markers);
            break;
          case 'todo':
            todoLayer.addLayers(markers);
            break;
          case 'event':
            eventsLayer.addLayers(markers);
            break;
          case 'other':
            otherLayer.addLayers(markers);
            break;
        }

        $rootScope.syncMapSpots = spots;
      }

      function drawSearchSpotMarkers(spots, type, clear) {
        if (clear) {
          GetCurrentLayer().clearLayers();
        }
        var markers = [];
        _.each(spots, function (item) {
          var icon = CreateCustomIcon(item.category_icon_url, type, item);
          if (item.location) {
            var marker = L.marker(item.location, {icon: icon});
            L.extend(marker, {
                spot_id: item.id
            });
            item.marker = marker;
            BindSpotPopup(marker, item);
            detectHover(marker, item);
            markers.push(marker);
          }
        });
        switch (type) {
          case 'food':
            foodLayer.addLayers(markers);
            break;
          case 'shelter':
            shelterLayer.addLayers(markers);
            break;
          case 'todo':
            todoLayer.addLayers(markers);
            break;
          case 'event':
            eventsLayer.addLayers(markers);
            break;
          case 'other':
            otherLayer.addLayers(markers);
            break;
        }

      }

      function drawBlogMarkers(posts, clear) {
        //currentLayer = 'other';
        if (clear) {
          GetCurrentLayer().clearLayers();
        }

        var markers = [];
        _.each(posts, function (item) {
          //var icon = CreateCustomIcon(item.cover_url.thumb, 'custom-map-icons', [50, 50]);
          if (item.location) {

            var marker = L.marker(item.location);

            var scope = $rootScope.$new();
            var offset = 75;
            var options = {
              keepInView: false,
              autoPan: true,
              closeButton: false,
              className: 'popup blogpopup',
              autoPanPaddingTopLeft: L.point(offset, offset),
              autoPanPaddingBottomRight: L.point(offset, offset)
            };

            var post = {};
            post.img = item.cover_url.medium;
            post.title = item.title;
            post.address = item.address;
            post.date = item.created_at;
            post.category = item.category.display_name;
            post.author = item.user.first_name + ' ' + item.user.last_name;
            post.slug = item.slug;

            scope.item = post;
            scope.marker = marker;

            marker.on('click', function () {
              if (this.getPopup()) {
                  this.unbindPopup();
              }
              var popupContent = $compile('<blogpopup post="item"></blogpopup>')(scope);
              var popup = L.popup(options).setContent(popupContent[0]);
              this.bindPopup(popup).openPopup();

              scope.item.$loading = true;

            //   var syncSpot;
            //   if ($rootScope.syncSpots && $rootScope.syncSpots.data && (syncSpot = _.findWhere($rootScope.syncSpots.data, {id: spot_id}))) {
            //     _loadSpotComments(scope, syncSpot);
            //   } else {
            //     Spot.get({id: spot_id}, function (fullSpot) {
            //       //merge photos
            //       fullSpot.photos = _.union(fullSpot.photos, fullSpot.comments_photos);
            //       _loadSpotComments(scope, fullSpot);
            //     });
            //   }
            });












            // var popupContent = $compile('<blogpopup></blogpopup>')(scope);
            // marker.bindPopup(popupContent);
            // marker.openPopup();
            // marker.on('click', function () {
            //     console.log(marker);
            //     // this.link = L.DomUtil.create('div', 'clear-selection', container);
            //     // this.link.href = '#';
            //     // this._map = map;
            //     // console.log(item);
            // //   $state.go('blog.article', {slug: item.slug});
            // });
            item.marker = marker;

            markers.push(marker);
          }
        });

        otherLayer.addLayers(markers);
        FitBoundsOfCurrentLayer();
      }

      function _activateControl(activeSelector) {
        angular.element('.leaflet-control-container .map-tools > div').removeClass('active');
        if (activeSelector) {
          angular.element(activeSelector).addClass('active');
        }
      }

      function cancelHttpRequest() {
        if ($rootScope.mapSortSpots && $rootScope.mapSortSpots.cancellerHttp) {
          $rootScope.mapSortSpots.cancellerHttp.resolve();
        }
      }

      function loadWeatherTiles(index) {
          map['weatherLayer' + index] = new L.TileLayer.WMS("http://mesonet.agron.iastate.edu/cgi-bin/wms/nexrad/n0r.cgi", {
              layers: 'nexrad-n0r-' + timestamps[index],
              format: 'image/png',
              transparent: true,
              attribution: "Weather data &copy; 2011 IEM Nexrad",
              opacity: 0
          });
          map['weatherLayer' + index].on('load', function() {
              if (index == 10) {
                  weatherAnimation(true);
              }
          });
          map.addLayer(map['weatherLayer' + index]);
          map['weatherLayer' + index].setOpacity(0);
      }

      var interval;
      var frame = 0;

      function weatherAnimation(start) {
          toastr.clear();
          if (start) {
              clearInterval(interval);
              interval = setInterval(function() {
                  var idx = Math.floor(++frame % 11);
                  map['weatherLayer' + idx].setOpacity(1);
                  if (idx == 0)
                      idx = 11;
                  map['weatherLayer' + (idx - 1)].setOpacity(0);
              }, 1000);
          } else {
              clearInterval(interval);
              if (map.hasLayer(map.weatherLayer0)) {
                  for (var i = 0; i < timestamps.length; i++) {
                      map.removeLayer(map['weatherLayer' + i]);
                  }
              }
          }
      }

      function toggleWeatherLayer(show) {
          if (show) {
              toastr.clear();
              toastr.success('Loading...', '', {
                  timeOut: 50000
              });
              for (var i = 0; i < timestamps.length; i++) {
                  loadWeatherTiles(i);
              }
          } else {
              weatherAnimation(false);
          }
      }

      function showWeatherMarkers() {
          var bounds = map.getBounds();
          var center = map.getCenter();
          var mapBox = [];
          mapBox.push(bounds._southWest.lng);
          mapBox.push(bounds._southWest.lat);
          mapBox.push(bounds._northEast.lng);
          mapBox.push(bounds._northEast.lat);
          mapBox.push(map.getZoom());
          var params = {
              //lat: center.lat,
              //lon: center.lng,
              bbox: mapBox.toString(),
              cluster: 'yes',
              APPID: OPENWEATHERMAP_API_KEY,
              units: $rootScope.weatherUnits == 'us' ? 'imperial' : 'metric',
              cnt: 10
          };
          var q = $.param(params);
          q = 'http://api.openweathermap.org/data/2.5/box/city?' + q;
          $http.get(API_URL + '/weather?q=' + encodeURIComponent(q))
              .success(function(data) {
                  drawWeatherMarkers(data);
              })
      }

      function setSkycon(icon) {
          var i = '';
          if (icon === '01d') {
              i = 'clear-day';
          } else if (icon === '01n') {
              i = 'clear-night';
          } else if (icon === '02d') {
              i = 'partly-cloudy-day';
          } else if (icon === '02n') {
              i = 'partly-cloudy-night';
          } else if (icon === '03d' || icon === '03n' || icon === '04d' || icon === '04n') {
              i = 'cloudy';
          } else if (icon === '09d' || icon === '10d' || icon === '11d' || icon === '09n' || icon === '10n' || icon === '11n') {
              i = 'rain';
          } else if (icon === '13d' || icon === '13n') {
              i = 'snow';
          } else if (icon === '50d' || icon === '50n') {
              i = 'fog';
          }
          return i;
      }

      function drawWeatherMarkers(data) {
          otherLayer.clearLayers();
          var markers = [];
          _.each(data.list, function(item) {
              var icon = CreateCustomIcon('', 'weather', item);
              var marker = L.marker([item.coord.lat, item.coord.lon], {
                  icon: icon
              });
              item.marker = marker;
              var options = {
                keepInView: false,
                autoPan: true,
                closeButton: false,
                className: 'map-marker-plate map-marker-weather-plate'
              };
              item.weather[0].icon = setSkycon(item.weather[0].icon);
              var scope = $rootScope.$new();
              scope.item = item;
              scope.image = setMarkerIcon(null, true);
              var popupContent = $compile('<div>\
                                             <p class="plate-name">{{item.name}}</p>\
                                             <p class="plate-desc">{{item.weather[0].main}}</p>\
                                             <skycon icon="item.weather[0].icon" size="50" color="\'#FFFFFF\'"></skycon>\
                                             <span>{{item.main.temp | number:0}}°</span>\
                                             <img width="50" height="50" src="{{image}}" />\
                                           </div>')(scope);
              var popup = L.popup(options).setContent(popupContent[0]);
              marker.bindPopup(popup);
              marker.on('click', function () {
                  if ($(window).width() > 767) {
                      $rootScope.weatherLocation.lat = item.coord.lat;
                      $rootScope.weatherLocation.lng = item.coord.lon;
                      $rootScope.toggleSidebar(true);
                  } else {
                      if(marker.isHighlighted) {
                          marker.closePopup();
                          marker.isHighlighted = false;
                          $rootScope.weatherLocation.lat = item.coord.lat;
                          $rootScope.weatherLocation.lng = item.coord.lon;
                          $rootScope.toggleSidebar(true);
                      } else {
                          marker.openPopup();
                          marker.isHighlighted = true;
                      }
                  }
              });
              marker.on('mouseover', function () {
                  marker.openPopup();
                  marker.isHighlighted = false;
              });
              marker.on('mouseout', function () {
                  marker.closePopup();
                  marker.isHighlighted = true;
              });
              markers.push(marker);
          });
          otherLayer.addLayers(markers);
      }

      return {
        Init: InitMap,
        GetMap: GetMap,
        InvalidateMapSize: InvalidateMapSize,
        GetControlGroup: GetControlGroup,
        GetCurrentLayer: GetCurrentLayer,
        GetDraggableLayer: GetDraggableLayer,
        //Layers
        ChangeState: ChangeState,
        showLayer: showLayer,
        showEvents: showEventsLayer,
        showFood: showFoodLayer,
        showShelter: showShelterLayer,
        showTodo: showTodoLayer,
        showOtherLayers: showOtherLayers,
        clearLayers: clearLayers,
        toggleWeatherLayer: toggleWeatherLayer,
        //Selections
        clearSelections: ClearSelections,
        LassoSelection: LassoSelection,
        PathSelection: PathSelection,
        RadiusSelection: RadiusSelection,
        SaveSelections: SaveSelections,
        LoadSelections: LoadSelections,
        spotsOnScreen: spotsOnScreen,
        //Controls
        AddControls: AddControls,
        RemoveControls: RemoveControls,
        ToggleSelectionControls: ToggleSelectionControls,
        //Makers
        CreateMarker: CreateMarker,
        RemoveMarker: RemoveMarker,
        CreateCustomIcon: CreateCustomIcon,
        BindSpotPopup: BindSpotPopup,
        BindMarkerToInput: BindMarkerToInput,
        //Math
        PointInPolygon: PointInPolygon,
        //Geocoding
        GetAddressByLatlng: GetAddressByLatlng,
        GetLatlngByAddress: GetLatlngByAddress,
        GetCurrentLocation: GetCurrentLocation,
        FocusMapToCurrentLocation: FocusMapToCurrentLocation,
        FocusMapToGivenLocation: FocusMapToGivenLocation,
        FitBoundsByLayer: FitBoundsByLayer,
        FitBoundsOfCurrentLayer: FitBoundsOfCurrentLayer,
        FitBoundsOfDrawLayer: FitBoundsOfDrawLayer,
        FitBoundsByCoordinates: FitBoundsByCoordinates,
        //get bounds based on a point
        GetBoundsByCircle: GetBoundsByCircle,
        //sorting
        SortByRating: SortByRating,
        SortByDate: SortByDate,
        ClampByDate: ClampByDate,
        SortBySubcategory: SortBySubcategory,

        GetBBoxes: GetDrawLayerBBoxes,
        GetPathWaypoints: GetDrawLayerPathWaypoints,
        GetGeoJSON: GetDrawLayerGeoJSON,
        GetDataByBBox: GetDataByBBox,
        BBoxToParams: BBoxToParams,
        drawSpotMarkers: drawSpotMarkers,
        drawSearchSpotMarkers: drawSearchSpotMarkers,
        drawBlogMarkers: drawBlogMarkers,
        WeatherSelection: WeatherSelection,

        cancelHttpRequest: cancelHttpRequest,
		    hasLayer: hasLayer,
        OpenSaveSelectionsPopup: OpenSaveSelectionsPopup,

        highlightSpot: highlightSpot,
        removeHighlighting: removeHighlighting,
        highlightSpotByHover: highlightSpotByHover,
        clearSpotHighlighting: clearSpotHighlighting,

        getWeatherLatLng: getWeatherLatLng,
        showWeatherMarkers: showWeatherMarkers,

        closeAll: closeAll
      };
    });

})();
