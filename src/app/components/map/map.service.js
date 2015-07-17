(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('MapService', function ($rootScope, $timeout, snapRemote) {
      var map = null;
      var tilesUrl = 'http://otile3.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.jpeg';
      var radiusSelectionLimit = 500000; //in meters
      var drawLayer = L.featureGroup();
      var eventsLayer = new L.MarkerClusterGroup();
      var pitstopsLayer = new L.MarkerClusterGroup();
      var recreationsLayer = new L.MarkerClusterGroup();
      var otherLayer = new L.MarkerClusterGroup();
      var currentLayer = "";

      // Path variables
      var pathRouter = L.Routing.osrm({geometryOnly: true});
      var pathSelectionStarted = false;

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
            snapRemote.enable();

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

          });
        }

      });
      L.Control.Path = function (options) {
        return new L.Control.path(options);
      };
      // Save selection
      L.Control.saveSelection = L.Control.extend({
        options: {
          position: 'bottomleft',
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
          position: 'bottomleft',
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
        }).setView({lat: 49.9, lng: 36.25}, 8);
        L.tileLayer(tilesUrl, {
          maxZoom: 15,
          minZoom: 3
        }).addTo(map);
        ChangeState("big");

        //add controls
        AddControls();
        map.addLayer(drawLayer);

        return map;
      }

      function GetMap() {
        return map;
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

      //Layers
      function ChangeState(state) {
        switch (state.toLowerCase()) {
          case "big":
            showEventsLayer(true);
            $rootScope.mapState = "big";
            break;
          case "small":
            showOtherLayers();
            $rootScope.mapState = "small";
            break;
          case "hidden":
            $rootScope.mapState = "hidden";
            removeAllLayers();
            break;
        }
        $rootScope.$apply();

        //Wait until digest cycle ends and then invalidateSize of the map.
        $timeout(function () {
          map.invalidateSize();
        });
      }

      function showEventsLayer(clearLayers) {
        if (clearLayers) { eventsLayer.clearLayers(); }
        if (currentLayer !== "events") {
          map.addLayer(eventsLayer);
        }
        map.removeLayer(recreationsLayer);
        map.removeLayer(pitstopsLayer);
        map.removeLayer(otherLayer);
        currentLayer = "events";
      }

      function showPitstopsLayer(clearLayers) {
        if (clearLayers) { pitstopsLayer.clearLayers(); }
        if (currentLayer !== "pitstops") {
          map.addLayer(pitstopsLayer);
        }
        map.removeLayer(recreationsLayer);
        map.removeLayer(eventsLayer);
        map.removeLayer(otherLayer);
        currentLayer = "pitstops";
      }

      function showRecreationsLayer(clearLayers) {
        if (clearLayers) { recreationsLayer.clearLayers(); }
        if (currentLayer !== "recreations") {
          map.addLayer(recreationsLayer);
        }
        map.removeLayer(eventsLayer);
        map.removeLayer(pitstopsLayer);
        map.removeLayer(otherLayer);
        currentLayer = "recreations";
      }

      function showOtherLayers() {
        otherLayer.clearLayers();
        if (currentLayer !== "other") {
          map.addLayer(otherLayer);
        }
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
            callback(getConcaveHull(points), b_box);
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
          rect;
        var lineOptions = {};
        lineOptions.styles = [{type: 'polygon', color: 'blue', opacity: 0.2, weight: 1}, {
          color: 'red',
          opacity: 1,
          weight: 3
        }];
        ClearSelectionListeners();

        pathSelectionStarted = true;
        map.on('click', onMapClick);

        function onMapClick(e) {
          var marker = L.marker(e.latlng, {draggable: true}).addTo(drawLayer);
          markers.push(marker);

          marker.on('dragend', RecalculateRoute);
          RecalculateRoute();
        }

        function RecalculateRoute() {
          if (markers.length >= 2) {
            var waypoints = _.map(markers, function (m) {
              return {latLng: m.getLatLng()};
            });
            pathRouter.route(waypoints, function (err, routes) {
              if (line) {
                drawLayer.removeLayer(rect);
                drawLayer.removeLayer(line);
                line.off('linetouched');
              }

              if (err) {
                console.log(err);
              } else {
                line = L.Routing.line(routes[0], lineOptions).addTo(drawLayer);
                line.on('linetouched', onLineTouched);
              }
            }, {geometryOnly: true});
          }
        }
      }

      function onLineTouched(e) {
        console.log(e);
      }

      function CancelPathSelection() {
        pathSelectionStarted = false;
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
        if (currentLayer === "none") { return false; }
        var marker = L.marker(latlng, options);
        GetCurrentLayer().addLayer(marker);

        return marker;
      }

      function RemoveMarker(Marker) {
        if (currentLayer === "none") { return; }
        GetCurrentLayer().removeLayer(Marker);
      }

      function DragMarker(Marker, Callback) {
        Marker.on('dragend', function (e) {
          callback(e);
        });
      }


      //Processing functions
      //Return concave hull from points array
      function getConcaveHull(latLngs) {
        return new ConcaveHull(latLngs).getLatLngs();
      }

      //Determine if point inside polygon or not
      function pointInPolygon(point, polyPoints) {
        if (point.lat && point.lng) {
          var p = map.latLngToLayerPoint(point);
          point = [p.x, p.y];
        }
        var x = point[0], y = point[1];

        var inside = false;
        for (var i = 0, j = polyPoints.length - 1; i < polyPoints.length; j = i++) {
          if (polyPoints[i].lat && polyPoints[i].lng) {
            var polyPoint = map.latLngToLayerPoint(polyPoints[i]);
            polyPoints[i] = [polyPoint.x, polyPoint.y];
          }
          if (polyPoints[j].lat && polyPoints[j].lng) {
            var polyPointSecond = map.latLngToLayerPoint(polyPoints[j]);
            polyPoints[j] = [polyPointSecond.x, polyPointSecond.y];
          }

          var xi = polyPoints[i][0], yi = polyPoints[i][1];
          var xj = polyPoints[j][0], yj = polyPoints[j][1];

          var intersect = ((yi > y) !== (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
          if (intersect) {
            inside = !inside;
            break;
          }
        }

        return inside;
      }

      //Scale bounding box
      function scaleBoundingBox(b_box, offset) {
        var _southWest = b_box.getSouthWest();
        var _northEast = b_box.getNorthEast();
        var zoomLevel = map.getZoom();
        console.log(offset / 1000 * zoomLevel / 100);

        _southWest.lat = _southWest.lat - offset / 1000 * zoomLevel / 100;
        _southWest.lng = _southWest.lng - offset / 1000 * zoomLevel / 100;

        _northEast.lat = _northEast.lat + offset / 1000 * zoomLevel / 100;
        _northEast.lng = _northEast.lng + offset / 1000 * zoomLevel / 100;

        var newBoundingBox = L.latLngBounds(_southWest, _northEast);

        return newBoundingBox;
      }

      return {
        Init: InitMap,
        GetMap: GetMap,
        GetControlGroup: GetControlGroup,
        GetCurrentLayer: GetCurrentLayer,
        //Layers
        ChangeState: ChangeState,
        showEvents: showEventsLayer,
        showPitstops: showPitstopsLayer,
        showRecreations: showRecreationsLayer,
        showOtherLayers: showEventsLayer,
        //Selections
        clearSelections: ClearSelections,
        //Controls
        AddControls: AddControls,
        RemoveControls: RemoveControls,
        //Makers
        CreateMarker: CreateMarker,
        RemoveMarker: RemoveMarker,
        //Math
        pointInPolygon: pointInPolygon
      };
    });

})();
