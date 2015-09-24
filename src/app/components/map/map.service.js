(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('MapService', function ($rootScope, $timeout, $http, API_URL, snapRemote, $compile, moment, $modal, toastr, GEOCODING_KEY, Area, SignUpService) {
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
      var pitstopsLayer = new L.MarkerClusterGroup(clusterOptions);
      var recreationsLayer = new L.MarkerClusterGroup(clusterOptions);
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
          case "recreations":
            layer = recreationsLayer;
            break;
          case "pitstops":
            layer = pitstopsLayer;
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
      function ChangeState(state) {

        switch (state.toLowerCase()) {
          case "big":
            showEventsLayer(true);
            $rootScope.mapState = "full-size";
            map.scrollWheelZoom.enable();
            break;
          case "small":
            showOtherLayers();
            $rootScope.mapState = "small-size";
            map.scrollWheelZoom.disable();
            break;
          case "hidden":
            $rootScope.mapState = "hidden";
            removeAllLayers();
            break;
        }
        map.closePopup();
        markersLayer.clearLayers();
        draggableMarkerLayer.clearLayers();
        drawLayer.clearLayers();

        $timeout(function () {
          map.invalidateSize();
        })
      }

      //show events layer on map.
      function showEventsLayer(clearLayers) {
        ClearSelectionListeners();
        if (clearLayers) {
          eventsLayer.clearLayers();
        }
        map.addLayer(eventsLayer);
        map.removeLayer(recreationsLayer);
        map.removeLayer(pitstopsLayer);
        map.removeLayer(otherLayer);
        currentLayer = "events";
      }

      //show pitstops layer on map
      function showPitstopsLayer(clearLayers) {
        ClearSelectionListeners();
        if (clearLayers) {
          pitstopsLayer.clearLayers();
        }
        map.addLayer(pitstopsLayer);
        map.removeLayer(recreationsLayer);
        map.removeLayer(eventsLayer);
        map.removeLayer(otherLayer);
        currentLayer = "pitstops";
      }

      //show recreations layer
      function showRecreationsLayer(clearLayers) {
        ClearSelectionListeners();
        if (clearLayers) {
          recreationsLayer.clearLayers();
        }
        map.addLayer(recreationsLayer);
        map.removeLayer(eventsLayer);
        map.removeLayer(pitstopsLayer);
        map.removeLayer(otherLayer);
        currentLayer = "recreations";
      }

      //show other layers
      function showOtherLayers() {
        ClearSelectionListeners();
        otherLayer.clearLayers();
        map.addLayer(otherLayer);
        map.removeLayer(recreationsLayer);
        map.removeLayer(pitstopsLayer);
        map.removeLayer(eventsLayer);
        currentLayer = "other";
      }

      //remove all layers from map
      function removeAllLayers() {
        currentLayer = "none";
        map.removeLayer(otherLayer);
        map.removeLayer(recreationsLayer);
        map.removeLayer(pitstopsLayer);
        map.removeLayer(eventsLayer);
      }

      //clear all layers
      function clearLayers() {
        eventsLayer.clearLayers();
        recreationsLayer.clearLayers();
        pitstopsLayer.clearLayers();
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
                callback();
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

        var modalInstance;
        if (wp.length > 0 || geoJson && geoJson.features.length > 0) {
          modalInstance = $modal.open({
            animation: true,
            templateUrl: '/app/components/map_partials/saveSelection/saveSelection.html',
            controller: SaveSelectionController,
            controllerAs: 'Crop',
            modalClass: 'save-selection-modal',
            modalContentClass: 'clearfix'
          });

          modalInstance.result.then(function (data) {
            //success
            SaveSelections(data.title, data.description, share);
          });
        } else {
          toastr.error('You can\'t save empty selection.');
        }


        function SaveSelectionController($modalInstance, $scope) {
          $scope.save = function () {
            if ($scope.data.title) {
              $modalInstance.close($scope.data);
            } else {
              //can't save without server;
              toastr.error('Title is required!');
            }
          };

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          };
        }
      }

      //save selection
      function SaveSelections(title, description, share) {
        ClearSelectionListeners();
        if (pathSelectionStarted) {
          CancelPathSelection();
        }
        var wp = GetDrawLayerPathWaypoints();
        var geoJson = drawLayerGeoJSON;


        var req = {
          title: title,
          description: description,
          waypoints: wp,
          zoom: map.getZoom(),
          data: geoJson
        };

        if (share) {
          //TODO: отдельный route под selection шаринг. В начале сейв селекшена, а потом его шаринг.

        } else {
          //if ($rootScope.currentParams.area_id) {
          //  req.area_id = $rootScope.currentParams.area_id;
          //  Area.update(req, function (data) {
          //    toastr.success('Selection saved!');
          //  }, function (data) {
          //    toastr.error('Error!')
          //  })
          //} else {
          Area.save(req, function (data) {
            toastr.success('Selection saved!');
          }, function (data) {
            toastr.error('Error!')
          });
          //}
        }
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
        GetDataByBBox(bboxes);
      }

      function ClearSelections() {
        markersLayer.clearLayers();
        draggableMarkerLayer.clearLayers();
        drawLayer.clearLayers();
        eventsLayer.clearLayers();
        pitstopsLayer.clearLayers();
        recreationsLayer.clearLayers();
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
        map.removeLayer(saveSelectionControl);
        map.removeLayer(clearSelectionControl);
      }

      function AddControls() {
        clearSelectionControl.addTo(map);
        saveSelectionControl.addTo(map);
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

        if ($rootScope.isMobile) {
          marker.on('click', function () {
            $modal.open({
              templateUrl: 'SpotMapModal.html',
              controller: 'SpotMapModalController',
              controllerAs: 'SpotPopup',
              modalClass: 'spot-mobile-modal',
              resolve: {
                spot: function () {
                  return spot;
                },
                marker: function () {
                  return marker;
                }
              }
            });
          });
        } else {
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
        }

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
        zoom = zoom || 8;

        var userBlockLocation = localStorage && localStorage.getItem('blockLocation');
        if (userBlockLocation) {
          console.log(moment.unix(userBlockLocation).format('MMM DD, YYYY H:mm:s A'));
        }

        if (!userBlockLocation) {
          map.locate({setView: true})
            .on('locationfound', function (e) {
              var location = {lat: e.latitude, lng: e.longitude};
              $rootScope.currentLocation = location;
              FocusMapToGivenLocation(location, zoom)
            })
            .on('locationerror', function () {
              if (!userBlockLocation) {
                localStorage.setItem('blockLocation', moment().unix());
              }
            });
        }
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

      function GetDataByBBox(bbox_array) {
        var spots = [];
        if (bbox_array.length > 0) {
          $http.post(API_URL + '/map/search', {b_boxes: bbox_array})
            .success(function (data) {
              _.each(data, function (item) {
                if (PointInPolygon(item.location)) {
                  spots.push(item);
                }
              });
              spots = FilterUniqueObjects(spots);

              $rootScope.$emit('update-map-data', spots);
            })
        } else {
          clearLayers();
          $rootScope.$emit('update-map-data', []);
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
              item.address = point.address;

              item.location = point.location;

              var marker = L.marker(item.location, {icon: icon});
              console.log(item);

              BindSpotPopup(marker, item);

              spotMarkers.push(marker);
              markers.push(marker);
            });

            item.markers = spotMarkers;
          }
        });
        switch (type) {
          case 'pitstop':
            pitstopsLayer.addLayers(markers);
            break;
          case 'recreation':
            recreationsLayer.addLayers(markers);
            break;
          case 'event':
            eventsLayer.addLayers(markers);
            break;
          case 'other':
            otherLayer.addLayers(markers);
            break;
        }

        //currentLayer = type;
        FitBoundsOfCurrentLayer();

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
        showEvents: showEventsLayer,
        showPitstops: showPitstopsLayer,
        showRecreations: showRecreationsLayer,
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
        drawSpotMarkers: drawSpotMarkers,
        drawBlogMarkers: drawBlogMarkers,
        WeatherSelection: WeatherSelection
      };
    });

})();
