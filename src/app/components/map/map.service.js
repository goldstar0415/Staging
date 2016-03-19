(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('MapService', function ($rootScope, $timeout, $http, API_URL, snapRemote, $compile, moment, $state, $modal, toastr, MOBILE_APP, GEOCODING_KEY, Area, SignUpService, Spot, SpotComment, SpotService) {
      var map = null;
      var DEFAULT_MAP_LOCATION = [60.1708, 24.9375]; //Helsinki
      var tilesUrl = 'http://otile3.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.jpeg';
      var radiusSelectionLimit = 500000; //in meters
      var markersLayer = L.featureGroup();
      var drawLayer = L.featureGroup();
      var draggableMarkerLayer = L.featureGroup();
      var drawLayerGeoJSON;
      var controlGroup = L.featureGroup();
      var clusterOptions = {
        disableClusteringAtZoom: 8
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
      var pathRouter = L.Routing.osrm({geometryOnly: true});
      var pathSelectionStarted = false;

      //GEOCODING
      var GeocodingSearchUrl = 'http://open.mapquestapi.com/nominatim/v1/search.php?format=json&key=' + GEOCODING_KEY + '&addressdetails=1&limit=3&q=';
      var GeocodingReverseUrl = 'http://open.mapquestapi.com/nominatim/v1/reverse.php?format=json&key=' + GEOCODING_KEY;

      //MAP CONTROLS
      // Lasso controls
      L.Control.lasso = L.Control.extend({
        options: {
          position: 'bottomleft',
          title: {
            'false': 'Lasso selection',
            'true': 'Lasso selection'
          }
        },
        onAdd: function (map) {
          var container = L.DomUtil.create('div', 'map-tools');

          this.link = L.DomUtil.create('div', 'lasso-selection', container);
          this.link.href = '#';
          this._map = map;

          L.DomEvent.on(this.link, 'click', this._click, this);
          return container;
        },
        _click: function (e) {
          $rootScope.hideHints = true;
          $timeout(function () {
            $rootScope.$apply();
          });
          snapRemote.disable();
          L.DomEvent.stopPropagation(e);
          L.DomEvent.preventDefault(e);
          LassoSelection(function LassoCallback(points, b_box) {
            var poly = L.polygon(points, {
              weight: 3,
              color: '#00CFFF',
              opacity: 0.9,
              fillColor: '#0C2638',
              fillOpacity: 0.4
            }).addTo(drawLayer);

            var popup = RemoveMarkerPopup(
              function () {
                drawLayer.removeLayer(poly);
                var bboxes = GetDrawLayerBBoxes();
                GetDataByBBox(bboxes);
              },
              function () {
                poly.closePopup();
              });

            poly.bindPopup(popup);
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
          position: 'bottomleft',
          title: {
            'false': 'Radius selection',
            'true': 'Radius selection'
          }
        },
        onAdd: function (map) {
          var container = L.DomUtil.create('div', 'map-tools');

          this.link = L.DomUtil.create('div', 'radius-selection', container);
          this.link.href = '#';
          this._map = map;

          L.DomEvent.on(this.link, 'click', this._click, this);
          return container;
        },
        _click: function (e) {
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
              weight: 3,
              color: '#00CFFF',
              opacity: 0.9,
              fillColor: '#0C2638',
              fillOpacity: 0.4
            });

            var popup = RemoveMarkerPopup(
              function () {
                drawLayer.removeLayer(circle);
                var bboxes = GetDrawLayerBBoxes();
                GetDataByBBox(bboxes);
              },
              function () {
                circle.closePopup();
              });

            circle.bindPopup(popup);


            circle.addTo(drawLayer);

            var bboxes = GetDrawLayerBBoxes();
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
          position: 'bottomleft',
          title: {
            'false': 'Path selection',
            'true': 'Path selection'
          }
        },
        onAdd: function (map) {
          var container = L.DomUtil.create('div', 'map-tools');

          this.link = L.DomUtil.create('div', 'path-selection', container);
          this.link.href = '#';
          this._map = map;

          L.DomEvent.on(this.link, 'click', this._click, this);
          return container;
        },
        _click: function (e) {
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

      // Share selection
      L.Control.shareSelection = L.Control.extend({
        options: {
          position: 'topright',
          title: {
            'false': 'Share selection',
            'true': 'Share selection'
          }
        },
        onAdd: function (map) {
          var container = L.DomUtil.create('div', 'map-tools map-tools-top hide-tools');

          this.link = L.DomUtil.create('div', 'share-selection', container);
          this.link.href = '#';
          this._map = map;

          L.DomEvent.on(this.link, 'click', this._click, this);
          return container;
        },
        _click: function (e) {
          if ($rootScope.currentUser) {
            L.DomEvent.stopPropagation(e);
            L.DomEvent.preventDefault(e);
            OpenSaveSelectionsPopup(true);
          } else {
            SignUpService.openModal('SignUpModal.html');
          }
        }
      });
      L.Control.ShareSelection = function (options) {
        return new L.Control.shareSelection(options);
      };

      // Save selection
      L.Control.saveSelection = L.Control.extend({
        options: {
          position: 'topright',
          title: {
            'false': 'Save selection',
            'true': 'Save selection'
          }
        },
        onAdd: function (map) {
          var container = L.DomUtil.create('div', 'map-tools map-tools-top hide-tools');

          this.link = L.DomUtil.create('div', 'save-selection', container);
          this.link.href = '#';
          this._map = map;

          L.DomEvent.on(this.link, 'click', this._click, this);
          return container;
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
          position: 'topright',
          title: {
            'false': 'Clear selection',
            'true': 'Clear selection'
          }
        },
        onAdd: function (map) {
          var container = L.DomUtil.create('div', 'map-tools map-tools-top hide-tools');

          this.link = L.DomUtil.create('div', 'clear-selection', container);
          this.link.href = '#';
          this._map = map;

          L.DomEvent.on(this.link, 'click', this._click, this);
          return container;
        },
        _click: function (e) {
          L.DomEvent.stopPropagation(e);
          L.DomEvent.preventDefault(e);
          ClearSelections();
          map.closePopup();

          angular.element('.leaflet-control-container .map-tools > div').removeClass('active');
        }

      });
      L.Control.ClearSelection = function (options) {
        return new L.Control.clearSelection(options);
      };

      //controls
      var lassoControl = L.Control.Lasso();
      var radiusControl = L.Control.Radius();
      var pathControl = L.Control.Path();
      var clearSelectionControl = L.Control.ClearSelection();
      var saveSelectionControl = L.Control.SaveSelection();
      var shareSelectionControl = L.Control.ShareSelection();

      //initialization
      function InitMap(mapDOMElement) {
        //Leaflet touch hook
        L.Map.mergeOptions({
          tap: true,
          tapTolerance: 15
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
        map = L.map(mapDOMElement, {
          attributionControl: false,
          zoomControl: true
        });
        L.tileLayer(tilesUrl, {
          maxZoom: 17,
          minZoom: 3
        }).addTo(map);

        //add controls
        AddControls();
        map.addLayer(draggableMarkerLayer);
        map.addLayer(drawLayer);
        map.addLayer(markersLayer);
        ChangeState('big');

        map.setView(DEFAULT_MAP_LOCATION, 3);
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
          case "events":
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
            map.scrollWheelZoom.disable();
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
        }

        $timeout(function () {
          map.invalidateSize();
        })
      }

      function showLayer(layer) {
        switch (layer) {
          case 'event':
            showEventsLayer();
            break;
          case 'todo':
            showTodoLayer();
            break;
          case 'food':
            showFoodLayer();
            break;
          case 'shelter':
            showShelterLayer();
            break;
          case 'other':
            showOtherLayers();
            break;
        }
      }

      //show events layer on map.
      function showEventsLayer(clearLayers) {
        ClearSelectionListeners();
        if (clearLayers) {
          eventsLayer.clearLayers();
        }
        map.addLayer(eventsLayer);
        map.removeLayer(todoLayer);
        map.removeLayer(foodLayer);
        map.removeLayer(shelterLayer);
        map.removeLayer(otherLayer);
        currentLayer = "events";
      }

      //show food layer on map
      function showFoodLayer(clearLayers) {
        ClearSelectionListeners();
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

      //show shelter layer on map
      function showShelterLayer(clearLayers) {
        ClearSelectionListeners();
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
      function showTodoLayer(clearLayers) {
        ClearSelectionListeners();
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
          points = [];
          started = true;
          polyline = L.polyline([], {color: 'red'}).addTo(drawLayer);
          points.push(e.latlng);
          polyline.setLatLngs(points);
        }

        function move(e) {
          if (started) {
            points.push(e.latlng);
            polyline.setLatLngs(points);
          }
        }

        function end(e) {
          if (started) {
            ClearSelectionListeners();
            map.dragging.enable();
            started = false;
            points.push(points[0]);
            var b_box = polyline.getBounds();
            drawLayer.removeLayer(polyline);
            callback(GetConcaveHull(points), b_box);
          }
        }
      }

      //Radius selection
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
          started = true;
          startPoint = L.latLng(e.latlng.lat, e.latlng.lng);
          circle = L.circle(e.latlng, radius, {color: 'red', weight: 3}).addTo(drawLayer);
        }

        function move(e) {
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
          if (started) {
            ClearSelectionListeners();
            map.dragging.enable();
            started = false;
            var b_box = circle.getBounds();
            drawLayer.removeLayer(circle);
            callback(startPoint, radius, b_box);
          }
        }
      }

      //Path selection
      function PathSelection(wpArray, callback) {
        var markers = [],
        line,
        cancelPopup;
        var showCancelPopup = true;
        var lineOptions = {};
        lineOptions.styles = [{type: 'polygon', color: 'blue', opacity: 0.6, weight: 3, fillOpacity: 0.2}, {
          color: 'red',
          opacity: 1,
          weight: 3
        }];
        ClearSelectionListeners();

        pathSelectionStarted = true;
        if (wpArray) {
          showCancelPopup = false;
          for (var k in wpArray) {
            onMapClick({latlng: wpArray[k]}, null, true);
          }
          RecalculateRoute();
        } else {
          map.on('click', onMapClick);
        }

        function onMapClick(e, idx, dontBuildPath) {
          var marker = L.marker(e.latlng, {draggable: true}).addTo(markersLayer);
          if (!isNaN(idx)) {
            markers.splice(idx + 1, 0, marker);
          } else {
            markers.push(marker);
          }

          var popup = RemoveMarkerPopup(
            function () {
              for (var k in markers) {
                if (markers[k]._leaflet_id == marker._leaflet_id) {
                  markersLayer.removeLayer(marker);
                  markers.splice(k, 1);
                  RecalculateRoute();
                }
              }
            },
            function () {
              marker.closePopup();
            });

          marker.bindPopup(popup);
          marker.on('dragend', function () {
            if (cancelPopup && pathSelectionStarted) {
              cancelPopup
                .setLatLng(marker.getLatLng())
                .openOn(map);
            }

            angular.element('.cancel-selection').on('click', function () {
              ClearSelectionListeners();
              map.closePopup();
            });
            RecalculateRoute();
          });

          if (!dontBuildPath) {
            if (markers.length > 1 && showCancelPopup) {
              if (!cancelPopup) {
                cancelPopup = L.popup({
                  offset: L.point(0, -15),
                  closeButton: false,
                  keepInView: false
                })
                  .setLatLng(marker.getLatLng())
                  .setContent('<button class="btn btn-block btn-success cancel-selection">Finish selection</button>')
                  .openOn(map);
              } else {
                cancelPopup
                  .setLatLng(marker.getLatLng())
                  .openOn(map);
              }

              angular.element('.cancel-selection').on('click', function () {
                ClearSelectionListeners();
                map.closePopup();
                _activateControl(false);
              });
            }
            RecalculateRoute();
          }
        }

        function RecalculateRoute() {
          if (markers.length >= 2) {
            var waypoints = _.map(markers, function (m) {
              return {latLng: m.getLatLng()};
            });

            pathRouter.route(waypoints, function (err, routes) {

              if (line) {
                drawLayer.removeLayer(line);
                line.off('linetouched');
              }
              if (err) {
                console.warn(err);
              } else {
                line = L.Routing.line(routes[0], lineOptions).addTo(drawLayer);
                line.on('linetouched', function (e) {
                  function remove() {
                    for (var k in markers) {
                      markersLayer.removeLayer(markers[k]);
                      var bboxes = GetDrawLayerBBoxes();
                      GetDataByBBox(bboxes);
                    }
                    drawLayer.removeLayer(line);
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

                  var popup = RemoveMarkerPopup(remove, cancel, addmarker, e.latlng);

                  popup.openOn(map);
                });

                if (callback) {
                  callback();
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
              line.off('linetouched');
            }
          }
        }
      }

      //cancel path selection
      function CancelPathSelection() {
        pathSelectionStarted = false;
      }

      //weather selection
      function WeatherSelection(callback) {
        map.on('click', function (e) {
          $http.get(API_URL + '/weather?lat=' + e.latlng.lat + '&lng=' + e.latlng.lng)
            .success(function (data) {
              callback(data);
            })
            .error(function (data) {
              callback(null);
            })
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
          FitBoundsOfDrawLayer();

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

          Area.save(req, function (data) {
            toastr.success('Selection saved!');
          }, function (data) {
            toastr.error('Error!')
          });
        });
      }

      function getScreenshot(callback) {
        var controls = $('.leaflet-control-container, .sidebar-menu-wrap');
        var mapPane = $(".leaflet-map-pane")[0];
        var mapTransform = mapPane.style.transform.split(",");
        var mapX = parseFloat(mapTransform[0].split("(")[1].replace("px", ""));
        var mapY = parseFloat(mapTransform[1].replace("px", ""));
        mapPane.style.transform = "";
        mapPane.style.left = mapX + "px";
        mapPane.style.top = mapY + "px";

        var myTiles = $("img.leaflet-tile");
        var tilesLeft = [];
        var tilesTop = [];
        var tileMethod = [];
        for (var i = 0; i < myTiles.length; i++) {
          if (myTiles[i].style.left != "") {
            tilesLeft.push(parseFloat(myTiles[i].style.left.replace("px", "")));
            tilesTop.push(parseFloat(myTiles[i].style.top.replace("px", "")));
            tileMethod[i] = "left";
          } else if (myTiles[i].style.transform != "") {
            var tileTransform = myTiles[i].style.transform.split(",");
            tilesLeft[i] = parseFloat(tileTransform[0].split("(")[1].replace("px", ""));
            tilesTop[i] = parseFloat(tileTransform[1].replace("px", ""));
            myTiles[i].style.transform = "";
            tileMethod[i] = "transform";
          } else {
            tilesLeft[i] = 0;
            tilesRight[i] = 0;
            tileMethod[i] = "neither";
          }
          myTiles[i].style.left = (tilesLeft[i]) + "px";
          myTiles[i].style.top = (tilesTop[i]) + "px";
        }

        var myDivicons = $(".leaflet-marker-icon");
        var dx = [];
        var dy = [];
        for (var i = 0; i < myDivicons.length; i++) {
          var curTransform = myDivicons[i].style.transform;
          var splitTransform = curTransform.split(",");
          dx.push(parseFloat(splitTransform[0].split("(")[1].replace("px", "")));
          dy.push(parseFloat(splitTransform[1].replace("px", "")));
          myDivicons[i].style.transform = "";
          myDivicons[i].style.left = dx[i] + "px";
          myDivicons[i].style.top = dy[i] + "px";
        }

        //var mapWidth = parseFloat($("#map").css("width").replace("px", ""));
        //var mapHeight = parseFloat($("#map").css("height").replace("px", ""));

        var linesLayer = $("canvas.leaflet-zoom-animated")[0];
        //var oldLinesWidth = linesLayer.getAttribute("width");
        //var oldLinesHeight = linesLayer.getAttribute("height");
        //var oldViewbox = linesLayer.getAttribute("viewBox");
        //linesLayer.setAttribute("width", mapWidth);
        //linesLayer.setAttribute("height", mapHeight);
        //linesLayer.setAttribute("viewBox", "0 0 " + mapWidth + " " + mapHeight);
        //var linesTransform = linesLayer.style.transform.split(",");
        //var linesX = parseFloat(linesTransform[0].split("(")[1].replace("px", ""));
        //var linesY = parseFloat(linesTransform[1].replace("px", ""));
        //linesLayer.style.transform = "";
        //linesLayer.style.left = "";
        //linesLayer.style.top = "";

        controls.hide();

        html2canvas(document.getElementById("map"), {
          useCORS: true,
          logging: true,
          allowTaint: false,
          //background: "#E8F0F6",
          onrendered: function onrendered(canvas) {
            for (var i = 0; i < myTiles.length; i++) {
              if (tileMethod[i] == "left") {
                myTiles[i].style.left = (tilesLeft[i]) + "px";
                myTiles[i].style.top = (tilesTop[i]) + "px";
              } else if (tileMethod[i] == "transform") {
                myTiles[i].style.left = "";
                myTiles[i].style.top = "";
                myTiles[i].style.transform = "translate(" + tilesLeft[i] + "px, " + tilesTop[i] + "px)";
              } else {
                myTiles[i].style.left = "0px";
                myTiles[i].style.top = "0px";
                myTiles[i].style.transform = "translate(0px, 0px)";
              }
            }
            for (var i = 0; i < myDivicons.length; i++) {
              myDivicons[i].style.transform = "translate(" + dx[i] + "px, " + dy[i] + "px)";
              myDivicons[i].style.left = "0px";
              myDivicons[i].style.top = "0px";
            }
            //linesLayer.style.transform = "translate(" + (linesX) + "px," + (linesY) + "px)";
            //linesLayer.setAttribute("viewBox", oldViewbox);
            //linesLayer.setAttribute("width", oldLinesWidth);
            //linesLayer.setAttribute("height", oldLinesHeight);
            mapPane.style.transform = "translate(" + (mapX) + "px," + (mapY) + "px)";
            mapPane.style.left = "";
            mapPane.style.top = "";

            var image = canvas.toDataURL("image/jpeg");
            callback(image);
            //window.open(image);
          }
        });

        controls.show();

      }

      //load selection from server
      function LoadSelections(selection) {
        if (selection.zoom) {
          map.setZoom(selection.zoom);
        }

        if (selection.waypoints && selection.waypoints.length > 0) {
          _.each(selection.waypoints, function (array) {
            PathSelection(array, function () {
              var bboxes = GetDrawLayerBBoxes();
              GetDataByBBox(bboxes);
            });
          });
        }

        if (selection.data) {
          L.geoJson(selection.data, {
            onEachFeature: function (feature) {
              if (feature.geometry.type = 'Point' && feature.properties.radius) {
                var startPoint = L.GeoJSON.coordsToLatLng(feature.geometry.coordinates);
                var radius = feature.properties.radius;

                var circle = L.circle(startPoint, radius, {
                  weight: 3,
                  color: '#00CFFF',
                  opacity: 0.9,
                  fillColor: '#0C2638',
                  fillOpacity: 0.4
                });

                var popup = RemoveMarkerPopup(
                  function () {
                    drawLayer.removeLayer(circle);
                    var bboxes = GetDrawLayerBBoxes();
                    GetDataByBBox(bboxes);
                  },
                  function () {
                    circle.closePopup();
                  });

                circle.bindPopup(popup);

                circle.addTo(drawLayer);
              } else {
                _.each(feature.geometry.coordinates, function (coords) {
                  var points = L.GeoJSON.coordsToLatLngs(coords);

                  var poly = L.polygon(points, {
                    weight: 3,
                    color: '#00CFFF',
                    opacity: 0.9,
                    fillColor: '#0C2638',
                    fillOpacity: 0.4
                  }).addTo(drawLayer);

                  var popup = RemoveMarkerPopup(
                    function () {
                      drawLayer.removeLayer(poly);
                      var bboxes = GetDrawLayerBBoxes();
                      GetDataByBBox(bboxes);
                    },
                    function () {
                      poly.closePopup();
                    });

                  poly.bindPopup(popup);
                });
              }
            }
          });
        }
        var bboxes = GetDrawLayerBBoxes();
        GetDataByBBox(bboxes, true);
      }

      function ClearSelections() {
        markersLayer.clearLayers();
        draggableMarkerLayer.clearLayers();
        drawLayer.clearLayers();
        eventsLayer.clearLayers();
        foodLayer.clearLayers();
        shelterLayer.clearLayers();
        todoLayer.clearLayers();
        otherLayer.clearLayers();

        ClearSelectionListeners();
        if (pathSelectionStarted) {
          CancelPathSelection();
        }

        GetDrawLayerBBoxes();
        GetDataByBBox([]);
      }

      //Controls
      function RemoveControls() {
        map.removeLayer(radiusControl);
        map.removeLayer(lassoControl);
        map.removeLayer(pathControl);
        map.removeLayer(shareSelectionControl);
        map.removeLayer(saveSelectionControl);
        map.removeLayer(clearSelectionControl);
      }

      function AddControls() {
        clearSelectionControl.addTo(map);
        saveSelectionControl.addTo(map);
        shareSelectionControl.addTo(map);
        pathControl.addTo(map);
        lassoControl.addTo(map);
        radiusControl.addTo(map);
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

      function CreateCustomIcon(iconUrl, className, iconSize) {
        var iconSize = iconSize || [50, 50];
        return L.icon({
          iconSize: iconSize,
          iconUrl: iconUrl,
          className: className
        });
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
        //if ($rootScope.isMobile) {
        //  marker.on('click', function () {
        //    $modal.open({
        //      templateUrl: 'SpotMapModal.html',
        //      controller: 'SpotMapModalController',
        //      controllerAs: 'SpotPopup',
        //      modalClass: 'spot-mobile-modal',
        //      resolve: {
        //        spot: function () {
        //          return spot;
        //        },
        //        marker: function () {
        //          return marker;
        //        }
        //      }
        //    });
        //  });
        //} else {
          var scope = $rootScope.$new();
          var offset = 75;
          var options = {
            keepInView: false,
            autoPan: true,
            closeButton: false,
            className: 'popup',
            autoPanPaddingTopLeft: L.point(offset, offset),
            autoPanPaddingBottomRight: L.point(offset, offset)
          };


          scope.item = spot;
          scope.marker = marker;
          var popupContent = $compile('<spot-popup spot="item" marker="marker"></spot-popup>')(scope);
          var popup = L.popup(options).setContent(popupContent[0]);
          marker.bindPopup(popup);

          marker.on('click', function () {
            //if (!scope.item.spot.photos) {
            Spot.get({id: scope.item.spot.id}, function (fullSpot) {
              //merge photos
              fullSpot.photos = _.union(fullSpot.comments_photos, fullSpot.photos);
              scope.item.spot = fullSpot;

              var params = {
                page: 1,
                limit: 10,
                spot_id: fullSpot.id
              };
              SpotComment.query(params, function (comments) {
                scope.item.spot.comments = comments.data;

                SpotService.initMarker(scope.item.spot);
              });
            });
            //}
          });
        //}
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

      function RemoveMarkerPopup(remove, cancel, addmarker, location) {
        var scope = $rootScope.$new();
        scope.remove = remove;
        scope.cancel = cancel;
        scope.addmarker = addmarker;

        scope.popup = L.popup({
          keepInView: true,
          autoPan: true,
          offset: L.point(-40, 0),
          closeButton: false,
          className: 'remove-popup clearfix'
        }).setLatLng(location);
        var popupContent = $compile('<confirm-popup></confirm-popup>')(scope);
        scope.popup.setContent(popupContent[0]);


        return scope.popup;
      }

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
        if ($state.current.name != 'index') return;
        zoom = zoom || 8;

        map
          .locate({setView: true})
          .on('locationfound', function (e) {
            var location = {lat: e.latitude, lng: e.longitude};
            $rootScope.currentLocation = location;
            FocusMapToGivenLocation(location, zoom)
          })
          .on('locationerror', function () {
            //detect country by ip
            var latLng = localStorage.getItem('country_location');
            if (latLng) {
              focusonLocation(latLng);
            } else {
              $.get("http://ipinfo.io", function (response) {
                localStorage.setItem('country_location', response.loc);
                focusonLocation(response.loc);
              }, "jsonp");
            }

            function focusonLocation(latlng) {
              var location = latlng.split(',');
              FocusMapToGivenLocation({lat: location[0], lng: location[1]}, 6);
            }
          });
      }

      function FocusMapToGivenLocation(location, zoom) {
        if (location.lat && location.lng) {
          map.panTo(new L.LatLng(location.lat, location.lng));

          if (zoom) {
            map.setZoom(zoom);
          }
        }
      }

      function FitBoundsByLayer(layer) {
        //currentLayer = layer;
        var bounds = GetCurrentLayer().getBounds();
        map.fitBounds(bounds);
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
          angular.element('.map-tools-top').removeClass('hide-tools');
        } else {
          angular.element('.map-tools-top').addClass('hide-tools');
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
        var spots = [];
        if (bbox_array.length > 0) {
          var params = BBoxToParams(bbox_array);
          params = $.param({b_boxes: params});

          $http.get(API_URL + '/map/search?' + params)
            .success(function (data) {
              _.each(data, function (item) {
                if (PointInPolygon(item.location)) {
                  spots.push(item);
                }
              });
              spots = FilterUniqueObjects(spots);
              $rootScope.$emit('update-map-data', spots, null, true);

              if (isFocus) {
                FitBoundsOfDrawLayer();
              }
            })
            .catch(function (resp) {
              toastr.error(resp.data ? resp.data.message : 'Something went wrong')
            });
        } else {
          clearLayers();
          $rootScope.$emit('update-map-data', [], null, false);
        }
      }

      function FilterUniqueObjects(array) {
        return _.uniq(array, function (item) {
          return item.spot_id
        })
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
        _.each(spots, function (item) {
          var icon = CreateCustomIcon(item.spot.category.icon_url, 'custom-map-icons', [50, 50]);
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

        //currentLayer = type;

        $rootScope.syncMapSpots = spots;
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
            item.marker = marker;
            BindBlogPopup(marker, item);

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
        //Selections
        clearSelections: ClearSelections,
        LassoSelection: LassoSelection,
        PathSelection: PathSelection,
        RadiusSelection: RadiusSelection,
        SaveSelections: SaveSelections,
        LoadSelections: LoadSelections,
        //Controls
        AddControls: AddControls,
        RemoveControls: RemoveControls,
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
        drawBlogMarkers: drawBlogMarkers,
        WeatherSelection: WeatherSelection
      };
    });

})();
