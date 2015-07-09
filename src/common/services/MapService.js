(function(){
    'use strict';
    angular.module('zoomtivity')
        .factory('MapService', function ($rootScope) {
            var map = null;
            var radiusSelectionLimit = 1500;
            var eventsLayer = new L.MarkerClusterGroup();
            var pitstopsLayer = new L.MarkerClusterGroup();;
            var recreationsLayer = new L.MarkerClusterGroup();
            var otherLayer = new L.MarkerClusterGroup();
            var currentLayer = "";

            //initialization
            function InitMap (mapDOMElement, mapOptions, mapTilesUrl) {
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

                map = L.map(mapDOMElement);
                L.tileLayer(mapTilesUrl, mapOptions).addTo(map);
                L.control.fullscreen({
                    position: 'bottomright'
                }).addTo(map);
                return map;
            }
            function GetMap() {
                return map;
            }

            //Layers
            function ChangeState(state) {
                switch (state) {
                    case "big":
                        showEventsLayer(true);
                        break;
                    case "small":
                        showOtherLayers();
                        break;
                    case "hidden":
                        removeAllLayers();
                        break;
                }
            }
            function showEventsLayer (clearLayers) {
                if(clearLayers) eventsLayer.clearLayers();
                if(currentLayer != "events"){
                    map.addLayer(eventsLayer);
                }
                map.removeLayer(recreationsLayer);
                map.removeLayer(pitstopsLayer);
                map.removeLayer(otherLayer);
                currentLayer = "events";
            }
            function showPitstopsLayer (clearLayers) {
                if(clearLayers) pitstopsLayer.clearLayers();
                if(currentLayer  != "pitstops") {
                    map.addLayer(pitstopsLayer);
                }
                map.removeLayer(recreationsLayer);
                map.removeLayer(eventsLayer);
                map.removeLayer(otherLayer);
                currentLayer = "pitstops";
            }
            function showRecreationsLayer(clearLayers) {
                if(clearLayers) recreationsLayer.clearLayers();
                if(currentLayer != "recreations") {
                    map.addLayer(recreationsLayer);
                }
                map.removeLayer(eventsLayer);
                map.removeLayer(pitstopsLayer);
                map.removeLayer(otherLayer);
                currentLayer = "recreations";
            }
            function showOtherLayers () {
                otherLayer.clearLayers();
                if(currentLayer != "other") {
                    map.addLayer(otherLayer);
                }
                map.removeLayer(recreationsLayer);
                map.removeLayer(pitstopsLayer);
                map.removeLayer(eventsLayer);
                currentLayer = "other";
            }
            function removeAllLayers () {
                currentLayer = "empty";
                map.removeLayer(otherLayer);
                map.removeLayer(recreationsLayer);
                map.removeLayer(pitstopsLayer);
                map.removeLayer(eventsLayer);
            }

            //Selections
            function LassoSelection(callback) {

            }
            function PathSelection(callback) {

            }
            function PolygonSelection(callback) {

            }
            function RadiusSelection(callback) {

            }

            //Controls
            function RemoveControls(controls) {
                for(var k in controls) {
                    map.removeLayer(controls[k]);
                }
            }
            function AddControls(controls) {
                for(var k in controls) {
                    map.addLayer(controls[k]);
                }
            }

            //Makers
            function CreateMaker(latlng, options) {
                return L.marker(latlng, options).addTo(map);
            }

            //Processing functions
            //Return concave hull from points array
            function getConcaveHull(latLngs) {
                latLngs.push(latLngs[0]);
                return new ConcaveHull(latLngs).getLatLngs();
            }
            //Simplify polygon
            function simplifyPolygon(points) {
                return simplify(points, 0.01, true);
            }
            //Determine if point inside polygon or not
            function pointInPolygon(point, polyPoints) {
                if(point.lat && point.lng){
                    var p = map.latLngToLayerPoint(point);
                    point = [p.x, p.y];
                }
                var x = point[0], y = point[1];

                var inside = false;
                for (var i = 0, j = polyPoints.length - 1; i < polyPoints.length; j = i++) {
                    if(polyPoints[i].lat && polyPoints[i].lng){
                        var p = map.latLngToLayerPoint(polyPoints[i]);
                        polyPoints[i] = [p.x, p.y];
                    }
                    if(polyPoints[j].lat && polyPoints[j].lng) {
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
                //Layers
                ChangeState: ChangeState,
                showEvents: showEventsLayer,
                showPitstops: showPitstopsLayer,
                showRecreations: showRecreationsLayer,
                showOtherLayers: showEventsLayer,
                //Selections
                Lasso: LassoSelection,
                Path: PathSelection,
                Polygon: PolygonSelection,
                Radius: RadiusSelection,
                //Controls
                AddControls: AddControls,
                RemoveControls: RemoveControls,
                //Makers
                CreateMarker: CreateMaker,
                //Math
                pointInPolygon: pointInPolygon
            }
        });
})(angular);



