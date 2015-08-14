(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('MapService', function ($rootScope, $timeout, $http, API_URL, snapRemote, $compile, moment) {
      var map = null;
      var tilesUrl = 'http://otile3.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.jpeg';
      var radiusSelectionLimit = 500000; //in meters
      var markersLayer = L.featureGroup();
      var drawLayer = L.featureGroup();
      var draggableMarkerLayer = L.featureGroup();
      //============================================
      var eventsLayer = new L.MarkerClusterGroup();
      var pitstopsLayer = new L.MarkerClusterGroup();
      var recreationsLayer = new L.MarkerClusterGroup();
      var otherLayer = new L.MarkerClusterGroup();
      //===============================================
      var currentLayer = "";

      // Path variables
      var pathRouter = L.Routing.osrm({geometryOnly: true});
      var pathSelectionStarted = false;

      //GEOCODING
      var GeocodingSearchUrl = 'http://open.mapquestapi.com/nominatim/v1/search.php?format=json&addressdetails=1&limit=3&q=';
      var GeocodingReverseUrl = 'http://open.mapquestapi.com/nominatim/v1/reverse.php?format=json';

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
          snapRemote.disable();
          L.DomEvent.stopPropagation(e);
          L.DomEvent.preventDefault(e);
          LassoSelection(function LassoCallback(points, b_box) {
            var poly = L.polygon(points, {
              weight: 2,
              color: 'green',
              opacity: 0.2,
              fillColor: 'green',
              fillOpacity: 0.1
            }).addTo(drawLayer);

            var popup = RemoveMarkerPopup(
              function() {
                drawLayer.removeLayer(poly);
                var bboxes = GetDrawLayerBBoxes();
                GetDataByBBox(bboxes);
              },
              function() {
                poly.closePopup();
              });

            poly.bindPopup(popup);
            snapRemote.enable();

            var bboxes = GetDrawLayerBBoxes();
            GetDataByBBox(bboxes);
          });
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
          snapRemote.disable();
          L.DomEvent.stopPropagation(e);
          L.DomEvent.preventDefault(e);
          RadiusSelection(function (startPoing, radius, b_box) {
            snapRemote.enable();

            var circleToGeoJSON = L.Circle.prototype.toGeoJSON;
            L.Circle.include({
              toGeoJSON: function() {
                var feature = circleToGeoJSON.call(this);
                feature.properties = {
                  point_type: 'circle',
                  radius: this.getRadius()
                };
                return feature;
              }
            });

            var circle = L.circle(startPoing, radius, {
              weight: 2,
              color: 'green',
              opacity: 0.2,
              fillColor: 'green',
              fillOpacity: 0.1
            });

            var popup = RemoveMarkerPopup(
              function() {
                drawLayer.removeLayer(circle);
                var bboxes = GetDrawLayerBBoxes();
                GetDataByBBox(bboxes);
              },
              function() {
                circle.closePopup();
              });

            circle.bindPopup(popup);



            circle.addTo(drawLayer);

            var bboxes = GetDrawLayerBBoxes();
            GetDataByBBox(bboxes);
          });
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
          L.DomEvent.stopPropagation(e);
          L.DomEvent.preventDefault(e);
          PathSelection(function () {
            var bboxes = GetDrawLayerBBoxes();
            GetDataByBBox(bboxes);
          });
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
          var container = L.DomUtil.create('div', 'map-tools');

          this.link = L.DomUtil.create('div', 'save-selection', container);
          this.link.href = '#';
          this._map = map;

          L.DomEvent.on(this.link, 'click', this._click, this);
          return container;
        },
        _click: function (e) {
          L.DomEvent.stopPropagation(e);
          L.DomEvent.preventDefault(e);
          SaveSelections();
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
          var container = L.DomUtil.create('div', 'map-tools');

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
          touchExtend: true
        });
        L.Map.TouchExtend = L.Handler.extend({
          initialize: function (map) {
            this._map = map;
            this._container = map._container;
            this._pane = map._panes.overlayPane;
          },
          addHooks: function () {
            L.DomEvent.on(this._container, 'touchstart', this._onTouchStart, this);
            L.DomEvent.on(this._container, 'touchend', this._onTouchEnd, this);
            L.DomEvent.on(this._container, 'touchmove', this._onTouchMove, this);
          },
          removeHooks: function () {
            L.DomEvent.off(this._container, 'touchstart', this._onTouchStart);
            L.DomEvent.off(this._container, 'touchend', this._onTouchEnd);
            L.DomEvent.off(this._container, 'touchmove', this._onTouchMove);
          },
          _onTouchEvent: function (e, type) {
            var touch, containerPoint, layerPoint, latlng;
            if (!this._map._loaded) {
              return;
            }

            touch = e.touches[0];
            containerPoint = L.point(touch.clientX, touch.clientY);
            layerPoint = this._map.containerPointToLayerPoint(containerPoint);
            latlng = this._map.layerPointToLatLng(layerPoint);

            this._map.fire(type, {
              latlng: latlng,
              layerPoint: layerPoint,
              containerPoint: containerPoint,
              originalEvent: e
            });
          },
          _onTouchStart: function (e) {
            this._onTouchEvent(e, 'touchstart');
          },
          _onTouchMove: function (e) {
            this._onTouchEvent(e, 'touchmove');
          },
          _onTouchEnd: function (e) {
            if (!this._map._loaded) {
              return;
            }

            this._map.fire('touchend', {
              originalEvent: e
            });
          }
        });
        L.Map.addInitHook('addHandler', 'touchExtend', L.Map.TouchExtend);

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

        window.map = map;
        map.locate({setView: true, maxZoom: 8});
        return map;
      }

      function GetMap() {
        return map;
      }

      function InvalidateMapSize() {
        map.invalidateSize();
      }

      function GetControlGroup() {
        return controlGroup;
      }

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

      function GetDraggableLayer() {
        return draggableMarkerLayer;
      }

      //Layers
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


        console.log('state ', state);
        console.log('draw layer ', map.hasLayer(drawLayer));
        console.log('draggable layer ', map.hasLayer(draggableMarkerLayer));
        console.log('marker layer ', map.hasLayer(markersLayer));
        console.log('events layer ', map.hasLayer(eventsLayer));
        console.log('recreations layer ', map.hasLayer(recreationsLayer));
        console.log('pitstops layer ', map.hasLayer(pitstopsLayer));
        console.log('other layer ', map.hasLayer(otherLayer));
        console.log('==================================================');
        $timeout(function() {
          map.invalidateSize();
        })
      }

      function showEventsLayer(clearLayers) {
        ClearSelectionListeners();
        //if (clearLayers) { eventsLayer.clearLayers(); }
        map.addLayer(eventsLayer);
        map.removeLayer(recreationsLayer);
        map.removeLayer(pitstopsLayer);
        map.removeLayer(otherLayer);
        currentLayer = "events";
      }

      function showPitstopsLayer(clearLayers) {
        ClearSelectionListeners();
        //if (clearLayers) { pitstopsLayer.clearLayers(); }
        map.addLayer(pitstopsLayer);
        map.removeLayer(recreationsLayer);
        map.removeLayer(eventsLayer);
        map.removeLayer(otherLayer);
        currentLayer = "pitstops";
      }

      function showRecreationsLayer(clearLayers) {
        ClearSelectionListeners();
        //if (clearLayers) { recreationsLayer.clearLayers(); }
        map.addLayer(recreationsLayer);
        map.removeLayer(eventsLayer);
        map.removeLayer(pitstopsLayer);
        map.removeLayer(otherLayer);
        currentLayer = "recreations";
      }

      function showOtherLayers() {
        ClearSelectionListeners();
        otherLayer.clearLayers();
        map.addLayer(otherLayer);
        map.removeLayer(recreationsLayer);
        map.removeLayer(pitstopsLayer);
        map.removeLayer(eventsLayer);
        currentLayer = "other";
      }

      function removeAllLayers() {
        currentLayer = "none";
        map.removeLayer(otherLayer);
        map.removeLayer(recreationsLayer);
        map.removeLayer(pitstopsLayer);
        map.removeLayer(eventsLayer);
      }

      function clearLayers() {
        eventsLayer.clearLayers();
        recreationsLayer.clearLayers();
        pitstopsLayer.clearLayers();
        otherLayer.clearLayers();
        draggableMarkerLayer.clearLayers();
      }

      //Selections

      /* Callback output:
       * points - array of latlng points
       * b_box - bounding box of the shape
       */
      function LassoSelection(callback) {
        ClearSelectionListeners();
        map.dragging.disable();
        var started = false;
        var points = [];
        var polyline = null;

        if (L.Browser.touch) {
          map.on('touchstart', start);
          map.on('touchmove', move);
          map.on('touchend', end);
        } else {
          map.on('mousedown', start);
          map.on('mousemove', move);
          map.on('mouseup', end);
        }

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

      /* Callback output:
       * startPoint - latlng of the circles center
       * radius - radius of the circle in meters
       * b_box - bounding box of the circle
       */
      function RadiusSelection(callback) {
        ClearSelectionListeners();
        map.dragging.disable();
        var started = false;
        var startPoint = null;
        var radius = 1000;
        var circle = null;

        if (L.Browser.touch) {
          map.on('touchstart', start);
          map.on('touchmove', move);
          map.on('touchend', end);
        } else {
          map.on('mousedown', start);
          map.on('mousemove', move);
          map.on('mouseup', end);
        }


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

      function PathSelection(callback) {
        var markers = [],
          line,
          cancelPopup;
        var lineOptions = {};
        lineOptions.styles = [{type: 'polygon', color: 'blue', opacity: 0.6, weight: 3, fillOpacity: 0.2}, {
          color: 'red',
          opacity: 1,
          weight: 3
        }];
        ClearSelectionListeners();

        pathSelectionStarted = true;
        map.on('click', onMapClick);

        function onMapClick(e, idx) {
          var marker = L.marker(e.latlng, {draggable: true}).addTo(markersLayer);
          if(!isNaN(idx)) {
            markers.splice(idx + 1, 0, marker);
          } else {
            markers.push(marker);
          }

          var popup = RemoveMarkerPopup(
            function() {
              for(var k in markers) {
                if(markers[k]._leaflet_id == marker._leaflet_id) {
                  markersLayer.removeLayer(marker);
                  markers.splice(k, 1);
                  RecalculateRoute();
                }
              }
            },
            function() {
              marker.closePopup();
            });

          marker.bindPopup(popup);
          marker.on('dragend',  function() {
            if(cancelPopup && pathSelectionStarted) {
              cancelPopup
                .setLatLng(marker.getLatLng())
                .openOn(map);
            }

            angular.element('.cancel-selection').on('click', function() {
              ClearSelectionListeners();
              map.closePopup();
            });
            RecalculateRoute();
          });

          if(markers.length > 1) {
            if(!cancelPopup) {
              cancelPopup = L.popup({
                offset: L.point(0, -15),
                closeButton: false,
                keepInView: true,
                autoPan: true
              })
                .setLatLng(marker.getLatLng())
                .setContent('<button class="btn btn-block btn-success cancel-selection">Cancel selection</button>')
                .openOn(map);


            } else {
              cancelPopup
                .setLatLng(marker.getLatLng())
                .openOn(map);
            }

            angular.element('.cancel-selection').on('click', function() {
              ClearSelectionListeners();
              map.closePopup();
            });
          }
          RecalculateRoute();
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
                console.log(err);
              } else {
                line = L.Routing.line(routes[0], lineOptions).addTo(drawLayer);
                line.on('linetouched', function(e) {
                  function remove() {
                    for(var k in markers) {
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
            if(!pathSelectionStarted) {
              for(var k in markers) {
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

      function CancelPathSelection() {
        pathSelectionStarted = false;
      }

      function WeatherSelection(callback) {
        map.on('click', function(e) {
          $http.get(API_URL + '/weather?lat='+ e.latlng.lat + '&lng=' + e.latlng.lng)
            .success(function(data) {
              callback(data);
            })
            .error(function(data) {
              callback(null);
            })
        });
      }

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

      function SaveSelections() {
        ClearSelectionListeners();
        if (pathSelectionStarted) {
          CancelPathSelection();
        }
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
        if(options.draggable) {
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
        if(Marker.options.draggable) {
          draggableMarkerLayer.removeLayer(Marker);
        } else {
          GetCurrentLayer().removeLayer(Marker);
        }

        if(callback) callback();
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
        Marker.on('dragend', function(e) {
          var latlng = Marker.getLatLng();
          GetAddressByLatlng(latlng, function(response) {
            if(response.display_name) {
              Callback({latlng: latlng, address: response.display_name})
            } else {
              Callback({latlng: latlng, address: 'Unknown place'});
            }
          })
        });
      }

      function BindSpotPopup(marker, spot) {
        var scope = $rootScope.$new();
        scope.item = spot;
        scope.marker = marker;
        var popupContent = $compile('<spot-popup spot="item" marker="marker"></spot-popup>')(scope);
        var popup = L.popup({
          keepInView: true,
          autoPan: true,
          closeButton: false,
          className: 'popup'
        }).setContent(popupContent[0]);

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
        drawLayer.eachLayer(function(layer) {
          if(result.length < 1) {
            if(layer._route) {
              layer.eachLayer(function(l) {
                if(!l._latlngs) {
                  result = leafletPip.pointInLayer([point.lng, point.lat], l);
                }
              });
            } else {
              if(layer.toGeoJSON) {
                var l = layer.toGeoJSON();
                var geoJSONlayer = L.geoJson(l);
                if(l.geometry.type != 'Point') {
                  result = leafletPip.pointInLayer([point.lng, point.lat], geoJSONlayer);
                } else {
                  if(l.properties.point_type == 'circle') {
                    var latlng = L.GeoJSON.coordsToLatLng(l.geometry.coordinates);
                    if(latlng.distanceTo(point) < l.properties.radius) {
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

      function GetAddressByLatlng (latlng, callback) {
        var url = GeocodingReverseUrl + "&lat=" + latlng.lat + "&lon=" + latlng.lng;
        $http.get(url, {withCredentials: false}).
          success(function(data, status, headers) {
            callback(data);
          }).
          error(function(data, status, headers) {
            callback(data);
          });
      }
      function GetLatlngByAddress (address, callback) {
        var url = GeocodingSearchUrl + address
        $http.get(url, {withCredentials: false}).
          success(function(data, status, headers) {
            callback(data);
          }).
          error(function(data, status, headers) {
            callback(data);
          });
      }
      function GetCurrentLocation (callback) {
        map.on('locationfound', function onLocationFound(e){
          map.off('locationfound');
          map.off('locationerror');
          callback(e);
        });
        map.on('locationerror', function onLocationError(e){
          map.off('locationfound');
          map.off('locationerror');
          callback(e);
        });

        map.locate({setView: false});
      }
      function FocusMapToCurrentLocation(zoom) {
        var zoomLevel = zoom || 8;
        map.locate({setView: true, maxZoom: zoomLevel});
      }
      function FocusMapToGivenLocation(location, zoom) {
        map.setView(location, zoom);
      }
      function FitBoundsOfCurrentLayer() {
        var bounds = GetCurrentLayer().getBounds();
        map.fitBounds(bounds);
      }
      function FitBoundsOfDrawLayer() {
        var bounds = drawLayer.getBounds();
        map.fitBounds(bounds);
      }


      //=================================================================
      function GetDrawLayerBBoxes() {
        var bboxes = [];
        drawLayer.eachLayer(function(layer) {
          if(layer._route) {
            layer.eachLayer(function(l) {
              if(!l._latlngs) {
                bboxes.push(l.getBounds());
              }
            });
          } else {
            if(layer.getBounds) {
              bboxes.push(layer.getBounds());
            }
          }
        });

        return bboxes;
      }

      function GetDataByBBox(bbox_array) {
        var spots = [];
        if(bbox_array.length > 0) {
          $http.post(API_URL + '/map/search', {b_boxes: bbox_array})
            .success(function(data) {
              _.each(data, function(item) {
                if(PointInPolygon(item.location)) {
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
        return _.uniq(array, function(item) {
          return item.spot_id
        })
      }

      //return sorted by rating array
      function SortByRating(array) {
        return _.sortBy(array, function(item) {
          return item.spot.rating;
        }).reverse();
      }

      //return sorted by end_date array
      function SortByDate(array) {
        return _.sortBy(array, function(item) {
            return moment(item.endDate, 'YYYY-MM-DD HH:mm:ss').format('x');
        });
      }

      //return sorted by rating only for selected categories
      function SortBySubcategory(array, categories) {
        var resultArray = array;
        if(categories.length > 0) {
          resultArray = _.reject(array, function(item) {
            var result = true;

            for(var k in categories) {
              if(categories[k].id == item.spot.category.id) {
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
        if(clear) {
          GetCurrentLayer().clearLayers();
        }
        var markers = [];

        _.each(spots, function(item) {
          var marker = L.marker(item.location);
          BindSpotPopup(marker, item);

          markers.push(marker);
        });

        switch(type) {
          case 'pitstop':
            pitstopsLayer.addLayers(markers);
            break;
          case 'recreation':
            recreationsLayer.addLayers(markers);
            break;
          case 'event':
            eventsLayer.addLayers(markers);
            break;
        }

      }


      //=================================================================


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
        //Selections
        clearSelections: ClearSelections,
        //Controls
        AddControls: AddControls,
        RemoveControls: RemoveControls,
        //Makers
        CreateMarker: CreateMarker,
        RemoveMarker: RemoveMarker,
        CreateCustomIcon: CreateCustomIcon,
        BindMarkerToInput: BindMarkerToInput,
        //Math
        PointInPolygon: PointInPolygon,
        //Geocoding
        GetAddressByLatlng: GetAddressByLatlng,
        GetLatlngByAddress: GetLatlngByAddress,
        GetCurrentLocation: GetCurrentLocation,
        FocusMapToCurrentLocation: FocusMapToCurrentLocation,
        FocusMapToGivenLocation: FocusMapToGivenLocation,
        FitBoundsOfCurrentLayer: FitBoundsOfCurrentLayer,
        FitBoundsOfDrawLayer:FitBoundsOfDrawLayer,
        //sorting
        SortByRating: SortByRating,
        SortByDate: SortByDate,
        SortBySubcategory: SortBySubcategory,

        GetBBoxes: GetDrawLayerBBoxes,
        GetDataByBBox: GetDataByBBox,
        drawSpotMarkers: drawSpotMarkers,
        WeatherSelection: WeatherSelection
      };
    });

})();

//TODO: move forward events, recreation & pitstop layers
