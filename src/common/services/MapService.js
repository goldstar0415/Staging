angular
    .module('zoomtivity')
    .factory('MapService', function ($rootScope, $timeout, $http) {
        var map = null;
        var tilesUrl = 'http://otile3.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.jpeg';
        var radiusSelectionLimit = 1500;
        var controlGroup = L.layerGroup();
        var drawLayer = L.featureGroup();
        var eventsLayer = new L.MarkerClusterGroup();
        var pitstopsLayer = new L.MarkerClusterGroup();
        var recreationsLayer = new L.MarkerClusterGroup();
        var otherLayer = new L.MarkerClusterGroup();
        var currentLayer = "";

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

                    touch = e.touches[0]
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
            }).setView({lat:49.9, lng:36.25}, 8);
            L.tileLayer(tilesUrl, {
                maxZoom: 15,
                minZoom: 3
            }).addTo(map);
            map.addLayer(controlGroup);
            map.addLayer(drawLayer);

            ChangeState("big");
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
            $timeout(function(){
                map.invalidateSize();
            });
        }

        function showEventsLayer(clearLayers) {
            if (clearLayers) eventsLayer.clearLayers();
            if (currentLayer != "events") {
                map.addLayer(eventsLayer);
            }
            map.removeLayer(recreationsLayer);
            map.removeLayer(pitstopsLayer);
            map.removeLayer(otherLayer);
            currentLayer = "events";
        }

        function showPitstopsLayer(clearLayers) {
            if (clearLayers) pitstopsLayer.clearLayers();
            if (currentLayer != "pitstops") {
                map.addLayer(pitstopsLayer);
            }
            map.removeLayer(recreationsLayer);
            map.removeLayer(eventsLayer);
            map.removeLayer(otherLayer);
            currentLayer = "pitstops";
        }

        function showRecreationsLayer(clearLayers) {
            if (clearLayers) recreationsLayer.clearLayers();
            if (currentLayer != "recreations") {
                map.addLayer(recreationsLayer);
            }
            map.removeLayer(eventsLayer);
            map.removeLayer(pitstopsLayer);
            map.removeLayer(otherLayer);
            currentLayer = "recreations";
        }

        function showOtherLayers() {
            otherLayer.clearLayers();
            if (currentLayer != "other") {
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

        //Note: Selections tools are private and can be used only inside MapService
        function LassoSelection(callback) {
            map.clearAllEventListeners();
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
                map.clearAllEventListeners();
                if (started) {
                    started = false;
                    points.push(e.latlng);
                    points.push(points[0]);
                    drawLayer.removeLayer(polyline);
                    callback(getConcaveHull(points));
                }
            }
        }
        function RadiusSelection(callback) {
            map.clearAllEventListeners();
            var started = false,
                startPoint = null,
                radius = 0,
                circle = null;

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
                circle = L.polyline(startPoint, radius, {color: 'red', weight: 3}).addTo(drawLayer);
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
                map.clearAllEventListeners();
                if (started) {
                    started = false;
                    var circleGeoJson = circle.toGeoJSON();
                    drawLayer.removeLayer(circle);
                    callback(startPoint, radius, circleGeoJson);
                }
            }
        }
        function PathSelection(callback) {
            map.clearAllEventListeners();
        }

        function ClearSelections(){
            drawLayer.clearLayers();
            eventsLayer.clearLayers();
            pitstopsLayer.clearLayers();
            recreationsLayer.clearLayers();
            otherLayer.clearLayers();
        }

        //Controls
        function RemoveControls() {
            map.removeLayer(controlGroup);
        }
        function AddControls() {
            map.addLayer(controlGroup);
        }

        //Makers
        function CreateMarker(latlng, options) {
            if (currentLayer == "none") return false;
            var marker = L.marker(latlng, options);
            GetCurrentLayer().addLayer(marker);

            return marker;
        }
        function RemoveMarker(Marker) {
            if (currentLayer == "none") return;
            GetCurrentLayer().removeLayer(marker);
        }

        //Processing functions
        //Return concave hull from points array
        function getConcaveHull(latLngs) {
            latLngs.push(latLngs[0]);
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
                    var p = map.latLngToLayerPoint(polyPoints[i]);
                    polyPoints[i] = [p.x, p.y];
                }
                if (polyPoints[j].lat && polyPoints[j].lng) {
                    var p = map.latLngToLayerPoint(polyPoints[j]);
                    polyPoints[j] = [p.x, p.y];
                }

                var xi = polyPoints[i][0], yi = polyPoints[i][1];
                var xj = polyPoints[j][0], yj = polyPoints[j][1];

                var intersect = ((yi > y) != (yj > y))
                    && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
                if (intersect) {
                    inside = !inside;
                    break;
                }
            }

            return inside;
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
        }
    });



