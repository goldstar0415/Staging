/*! leaflet-routing-machine - v2.6.0 - 2015-12-20 */

!function (a) {
  if ("object" == typeof exports && "undefined" != typeof module)module.exports = a(); else if ("function" == typeof define && define.amd)define([], a); else {
    var b;
    b = "undefined" != typeof window ? window : "undefined" != typeof global ? global : "undefined" != typeof self ? self : this, (b.L || (b.L = {})).Routing = a()
  }
}(function () {
  return function a(b, c, d) {
    function e(g, h) {
      if (!c[g]) {
        if (!b[g]) {
          var i = "function" == typeof require && require;
          if (!h && i)return i(g, !0);
          if (f)return f(g, !0);
          var j = new Error("Cannot find module '" + g + "'");
          throw j.code = "MODULE_NOT_FOUND", j
        }
        var k = c[g] = {exports: {}};
        b[g][0].call(k.exports, function (a) {
          var c = b[g][1][a];
          return e(c ? c : a)
        }, k, k.exports, a, b, c, d)
      }
      return c[g].exports
    }

    for (var f = "function" == typeof require && require, g = 0; g < d.length; g++)e(d[g]);
    return e
  }({
    1: [function (a, b, c) {
      function d(a, b, c) {
        function d(a) {
          return a >= 200 && 300 > a || 304 === a
        }

        function e() {
          void 0 === h.status || d(h.status) ? b.call(h, null, h) : b.call(h, h, null)
        }

        var f = !1;
        if ("undefined" == typeof window.XMLHttpRequest)return b(Error("Browser not supported"));
        if ("undefined" == typeof c) {
          var g = a.match(/^\s*https?:\/\/[^\/]*/);
          c = g && g[0] !== location.protocol + "//" + location.domain + (location.port ? ":" + location.port : "")
        }
        var h = new window.XMLHttpRequest;
        if (c && !("withCredentials"in h)) {
          h = new window.XDomainRequest;
          var i = b;
          b = function () {
            if (f)i.apply(this, arguments); else {
              var a = this, b = arguments;
              setTimeout(function () {
                i.apply(a, b)
              }, 0)
            }
          }
        }
        return "onload"in h ? h.onload = e : h.onreadystatechange = function () {
          4 === h.readyState && e()
        }, h.onerror = function (a) {
          b.call(this, a || !0, null), b = function () {
          }
        }, h.onprogress = function () {
        }, h.ontimeout = function (a) {
          b.call(this, a, null), b = function () {
          }
        }, h.onabort = function (a) {
          b.call(this, a, null), b = function () {
          }
        }, h.open("GET", a, !0), h.send(null), f = !0, h
      }

      "undefined" != typeof b && (b.exports = d)
    }, {}], 2: [function (a, b, c) {
      function d(a, b) {
        a = Math.round(a * b), a <<= 1, 0 > a && (a = ~a);
        for (var c = ""; a >= 32;)c += String.fromCharCode((32 | 31 & a) + 63), a >>= 5;
        return c += String.fromCharCode(a + 63)
      }

      var e = {};
      e.decode = function (a, b) {
        for (var c, d, e = 0, f = 0, g = 0, h = [], i = 0, j = 0, k = null, l = Math.pow(10, b || 5); e < a.length;) {
          k = null, i = 0, j = 0;
          do k = a.charCodeAt(e++) - 63, j |= (31 & k) << i, i += 5; while (k >= 32);
          c = 1 & j ? ~(j >> 1) : j >> 1, i = j = 0;
          do k = a.charCodeAt(e++) - 63, j |= (31 & k) << i, i += 5; while (k >= 32);
          d = 1 & j ? ~(j >> 1) : j >> 1, f += c, g += d, h.push([f / l, g / l])
        }
        return h
      }, e.encode = function (a, b) {
        if (!a.length)return "";
        for (var c = Math.pow(10, b || 5), e = d(a[0][0], c) + d(a[0][1], c), f = 1; f < a.length; f++) {
          var g = a[f], h = a[f - 1];
          e += d(g[0] - h[0], c), e += d(g[1] - h[1], c)
        }
        return e
      }, void 0 !== typeof b && (b.exports = e)
    }, {}], 3: [function (a, b, c) {
      !function () {
        "use strict";
        L.Routing = L.Routing || {}, L.Routing.Autocomplete = L.Class.extend({
          options: {
            timeout: 500,
            blurTimeout: 100,
            noResultsMessage: "No results found."
          }, initialize: function (a, b, c, d) {
            L.setOptions(this, d), this._elem = a, this._resultFn = d.resultFn ? L.Util.bind(d.resultFn, d.resultContext) : null, this._autocomplete = d.autocompleteFn ? L.Util.bind(d.autocompleteFn, d.autocompleteContext) : null, this._selectFn = L.Util.bind(b, c), this._container = L.DomUtil.create("div", "leaflet-routing-geocoder-result"), this._resultTable = L.DomUtil.create("table", "", this._container), L.DomEvent.addListener(this._elem, "input", this._keyPressed, this), L.DomEvent.addListener(this._elem, "keypress", this._keyPressed, this), L.DomEvent.addListener(this._elem, "keydown", this._keyDown, this), L.DomEvent.addListener(this._elem, "blur", function () {
              this._isOpen && this.close()
            }, this)
          }, close: function () {
            L.DomUtil.removeClass(this._container, "leaflet-routing-geocoder-result-open"), this._isOpen = !1
          }, _open: function () {
            var a = this._elem.getBoundingClientRect();
            this._container.parentElement || (this._container.style.left = a.left + window.scrollX + "px", this._container.style.top = a.bottom + window.scrollY + "px", this._container.style.width = a.right - a.left + "px", document.body.appendChild(this._container)), L.DomUtil.addClass(this._container, "leaflet-routing-geocoder-result-open"), this._isOpen = !0
          }, _setResults: function (a) {
            var b, c, d, e;
            for (delete this._selection, this._results = a; this._resultTable.firstChild;)this._resultTable.removeChild(this._resultTable.firstChild);
            for (b = 0; b < a.length; b++)c = L.DomUtil.create("tr", "", this._resultTable), c.setAttribute("data-result-index", b), d = L.DomUtil.create("td", "", c), e = document.createTextNode(a[b].name), d.appendChild(e), L.DomEvent.addListener(d, "mousedown", L.DomEvent.preventDefault), L.DomEvent.addListener(d, "click", this._createClickListener(a[b]));
            b || (c = L.DomUtil.create("tr", "", this._resultTable), d = L.DomUtil.create("td", "leaflet-routing-geocoder-no-results", c), d.innerHTML = this.options.noResultsMessage), this._open(), a.length > 0 && this._select(1)
          }, _createClickListener: function (a) {
            var b = this._resultSelected(a);
            return L.bind(function () {
              this._elem.blur(), b()
            }, this)
          }, _resultSelected: function (a) {
            return L.bind(function () {
              this.close(), this._elem.value = a.name, this._lastCompletedText = a.name, this._selectFn(a)
            }, this)
          }, _keyPressed: function (a) {
            var b;
            return this._isOpen && 13 === a.keyCode && this._selection ? (b = parseInt(this._selection.getAttribute("data-result-index"), 10), this._resultSelected(this._results[b])(), void L.DomEvent.preventDefault(a)) : 13 === a.keyCode ? void this._complete(this._resultFn, !0) : this._autocomplete && document.activeElement === this._elem ? (this._timer && clearTimeout(this._timer), void(this._timer = setTimeout(L.Util.bind(function () {
              this._complete(this._autocomplete)
            }, this), this.options.timeout))) : void this._unselect()
          }, _select: function (a) {
            var b = this._selection;
            b && (L.DomUtil.removeClass(b.firstChild, "leaflet-routing-geocoder-selected"), b = b[a > 0 ? "nextSibling" : "previousSibling"]), b || (b = this._resultTable[a > 0 ? "firstChild" : "lastChild"]), b && (L.DomUtil.addClass(b.firstChild, "leaflet-routing-geocoder-selected"), this._selection = b)
          }, _unselect: function () {
            this._selection && L.DomUtil.removeClass(this._selection.firstChild, "leaflet-routing-geocoder-selected"), delete this._selection
          }, _keyDown: function (a) {
            if (this._isOpen)switch (a.keyCode) {
              case 27:
                return this.close(), void L.DomEvent.preventDefault(a);
              case 38:
                return this._select(-1), void L.DomEvent.preventDefault(a);
              case 40:
                return this._select(1), void L.DomEvent.preventDefault(a)
            }
          }, _complete: function (a, b) {
            function c(a) {
              this._lastCompletedText = d, b && 1 === a.length ? this._resultSelected(a[0])() : this._setResults(a)
            }

            var d = this._elem.value;
            d && (d !== this._lastCompletedText ? a(d, c, this) : b && c.call(this, this._results))
          }
        })
      }()
    }, {}], 4: [function (a, b, c) {
      (function (c) {
        !function () {
          "use strict";
          var d = "undefined" != typeof window ? window.L : "undefined" != typeof c ? c.L : null;
          d.Routing = d.Routing || {}, d.extend(d.Routing, a("./L.Routing.Itinerary")), d.extend(d.Routing, a("./L.Routing.Line")), d.extend(d.Routing, a("./L.Routing.Plan")), d.extend(d.Routing, a("./L.Routing.OSRM")), d.extend(d.Routing, a("./L.Routing.ErrorControl")), d.Routing.Control = d.Routing.Itinerary.extend({
            options: {
              fitSelectedRoutes: "smart",
              routeLine: function (a, b) {
                return d.Routing.line(a, b)
              },
              autoRoute: !0,
              routeWhileDragging: !1,
              routeDragInterval: 500,
              waypointMode: "connect",
              useZoomParameter: !1,
              showAlternatives: !1
            }, initialize: function (a) {
              d.Util.setOptions(this, a), this._router = this.options.router || new d.Routing.OSRM(a), this._plan = this.options.plan || d.Routing.plan(this.options.waypoints, a), this._requestCount = 0, d.Routing.Itinerary.prototype.initialize.call(this, a), this.on("routeselected", this._routeSelected, this), this._plan.on("waypointschanged", this._onWaypointsChanged, this), a.routeWhileDragging && this._setupRouteDragging(), this.options.autoRoute && this.route()
            }, onAdd: function (a) {
              var b = d.Routing.Itinerary.prototype.onAdd.call(this, a);
              return this._map = a, this._map.addLayer(this._plan), this.options.useZoomParameter && this._map.on("zoomend", function () {
                this.route({callback: d.bind(this._updateLineCallback, this)})
              }, this), this._plan.options.geocoder && b.insertBefore(this._plan.createGeocoders(), b.firstChild), b
            }, onRemove: function (a) {
              return this._line && a.removeLayer(this._line), a.removeLayer(this._plan), d.Routing.Itinerary.prototype.onRemove.call(this, a)
            }, getWaypoints: function () {
              return this._plan.getWaypoints()
            }, setWaypoints: function (a) {
              return this._plan.setWaypoints(a), this
            }, spliceWaypoints: function () {
              var a = this._plan.spliceWaypoints.apply(this._plan, arguments);
              return a
            }, getPlan: function () {
              return this._plan
            }, getRouter: function () {
              return this._router
            }, _routeSelected: function (a) {
              var b = a.route, c = this.options.showAlternatives && a.alternatives, d = this.options.fitSelectedRoutes, e = "smart" === d && !this._waypointsVisible() || "smart" !== d && d;
              this._updateLines({
                route: b,
                alternatives: c
              }), e && this._map.fitBounds(this._line.getBounds()), "snap" === this.options.waypointMode && (this._plan.off("waypointschanged", this._onWaypointsChanged, this), this.setWaypoints(b.waypoints), this._plan.on("waypointschanged", this._onWaypointsChanged, this))
            }, _waypointsVisible: function () {
              var a, b, c, e, f, g = this.getWaypoints();
              try {
                for (a = this._map.getSize(), e = 0; e < g.length; e++)f = this._map.latLngToLayerPoint(g[e].latLng), b ? b.extend(f) : b = d.bounds([f]);
                return c = b.getSize(), (c.x > a.x / 5 || c.y > a.y / 5) && this._waypointsInViewport()
              } catch (h) {
                return !1
              }
            }, _waypointsInViewport: function () {
              var a, b, c = this.getWaypoints();
              try {
                a = this._map.getBounds()
              } catch (d) {
                return !1
              }
              for (b = 0; b < c.length; b++)if (a.contains(c[b].latLng))return !0;
              return !1
            }, _updateLines: function (a) {
              var b = void 0 !== this.options.addWaypoints ? this.options.addWaypoints : !0;
              this._clearLines(), this._alternatives = [], a.alternatives && a.alternatives.forEach(function (a, b) {
                this._alternatives[b] = this.options.routeLine(a, d.extend({isAlternative: !0}, this.options.altLineOptions || this.options.lineOptions)), this._alternatives[b].addTo(this._map), this._hookAltEvents(this._alternatives[b])
              }, this), this._line = this.options.routeLine(a.route, d.extend({
                addWaypoints: b,
                extendToWaypoints: "connect" === this.options.waypointMode
              }, this.options.lineOptions)), this._line.addTo(this._map), this._hookEvents(this._line)
            }, _hookEvents: function (a) {
              a.on("linetouched", function (a) {
                this._plan.dragNewWaypoint(a)
              }, this)
            }, _hookAltEvents: function (a) {
              a.on("linetouched", function (a) {
                var b = this._routes.slice(), c = b.splice(a.target._route.routesIndex, 1)[0];
                this.fire("routeselected", {route: c, alternatives: b})
              }, this)
            }, _onWaypointsChanged: function (a) {
              this.options.autoRoute && this.route({}), this._plan.isReady() || (this._clearLines(), this._clearAlts()), this.fire("waypointschanged", {waypoints: a.waypoints})
            }, _setupRouteDragging: function () {
              var a, b = 0;
              this._plan.on("waypointdrag", d.bind(function (c) {
                a = c.waypoints, b || (b = setTimeout(d.bind(function () {
                  this.route({
                    waypoints: a,
                    geometryOnly: !0,
                    callback: d.bind(this._updateLineCallback, this)
                  }), b = void 0
                }, this), this.options.routeDragInterval))
              }, this)), this._plan.on("waypointdragend", function () {
                b && (clearTimeout(b), b = void 0), this.route()
              }, this)
            }, _updateLineCallback: function (a, b) {
              a ? this._clearLines() : this._updateLines({route: b[0], alternatives: b.slice(1)})
            }, route: function (a) {
              var b, c = ++this._requestCount;
              a = a || {}, this._plan.isReady() && (this.options.useZoomParameter && (a.z = this._map && this._map.getZoom()), b = a && a.waypoints || this._plan.getWaypoints(), this.fire("routingstart", {waypoints: b}), this._router.route(b, a.callback || function (d, e) {
                  if (c === this._requestCount) {
                    if (this._clearLines(), this._clearAlts(), d)return void this.fire("routingerror", {error: d});
                    if (e.forEach(function (a, b) {
                        a.routesIndex = b
                      }), a.geometryOnly) {
                      var f = e.splice(0, 1)[0];
                      this._routeSelected({route: f, alternatives: e})
                    } else this.fire("routesfound", {waypoints: b, routes: e}), this.setAlternatives(e)
                  }
                }, this, a))
            }, _clearLines: function () {
              if (this._line && (this._map.removeLayer(this._line), delete this._line), this._alternatives && this._alternatives.length) {
                for (var a in this._alternatives)this._map.removeLayer(this._alternatives[a]);
                this._alternatives = []
              }
            }
          }), d.Routing.control = function (a) {
            return new d.Routing.Control(a)
          }, b.exports = d.Routing
        }()
      }).call(this, "undefined" != typeof global ? global : "undefined" != typeof self ? self : "undefined" != typeof window ? window : {})
    }, {
      "./L.Routing.ErrorControl": 5,
      "./L.Routing.Itinerary": 8,
      "./L.Routing.Line": 10,
      "./L.Routing.OSRM": 12,
      "./L.Routing.Plan": 13
    }], 5: [function (a, b, c) {
      !function () {
        "use strict";
        L.Routing = L.Routing || {}, L.Routing.ErrorControl = L.Control.extend({
          options: {
            header: "Routing error",
            formatMessage: function (a) {
              return a.status < 0 ? "Calculating the route caused an error. Technical description follows: <code><pre>" + a.message + "</pre></code" : "The route could not be calculated. " + a.message
            }
          }, initialize: function (a, b) {
            L.Control.prototype.initialize.call(this, b), a.on("routingerror", L.bind(function (a) {
              this._element && (this._element.children[1].innerHTML = this.options.formatMessage(a.error), this._element.style.visibility = "visible")
            }, this)).on("routingstart", L.bind(function () {
              this._element && (this._element.style.visibility = "hidden")
            }, this))
          }, onAdd: function () {
            var a, b;
            return this._element = L.DomUtil.create("div", "leaflet-bar leaflet-routing-error"), this._element.style.visibility = "hidden", a = L.DomUtil.create("h3", null, this._element), b = L.DomUtil.create("span", null, this._element), a.innerHTML = this.options.header, this._element
          }, onRemove: function () {
            delete this._element
          }
        }), L.Routing.errorControl = function (a, b) {
          return new L.Routing.ErrorControl(a, b)
        }
      }()
    }, {}], 6: [function (a, b, c) {
      (function (c) {
        !function () {
          "use strict";
          var d = "undefined" != typeof window ? window.L : "undefined" != typeof c ? c.L : null;
          d.Routing = d.Routing || {}, d.extend(d.Routing, a("./L.Routing.Localization")), d.Routing.Formatter = d.Class.extend({
            options: {
              units: "metric",
              unitNames: {
                meters: "m",
                kilometers: "km",
                yards: "yd",
                miles: "mi",
                hours: "h",
                minutes: "mín",
                seconds: "s"
              },
              language: "en",
              roundingSensitivity: 1,
              distanceTemplate: "{value} {unit}"
            }, initialize: function (a) {
              d.setOptions(this, a)
            }, formatDistance: function (a, b) {
              var c, e, f, g, h = this.options.unitNames, i = 0 >= b, j = i ? function (a) {
                return a
              } : d.bind(this._round, this);
              return "imperial" === this.options.units ? (e = a / .9144, f = e >= 1e3 ? {
                value: j(a / 1609.344, b),
                unit: h.miles
              } : {value: j(e, b), unit: h.yards}) : (c = j(a, b), f = {
                value: c >= 1e3 ? c / 1e3 : c,
                unit: c >= 1e3 ? h.kilometers : h.meters
              }), i && (g = Math.pow(10, -b), f.value = Math.round(f.value * g) / g), d.Util.template(this.options.distanceTemplate, f)
            }, _round: function (a, b) {
              var c = b || this.options.roundingSensitivity, d = Math.pow(10, (Math.floor(a / c) + "").length - 1), e = Math.floor(a / d), f = e > 5 ? d : d / 2;
              return Math.round(a / f) * f
            }, formatTime: function (a) {
              return a > 86400 ? Math.round(a / 3600) + " h" : a > 3600 ? Math.floor(a / 3600) + " h " + Math.round(a % 3600 / 60) + " min" : a > 300 ? Math.round(a / 60) + " min" : a > 60 ? Math.floor(a / 60) + " min" + (a % 60 !== 0 ? " " + a % 60 + " s" : "") : a + " s"
            }, formatInstruction: function (a, b) {
              return void 0 === a.text ? d.Util.template(this._getInstructionTemplate(a, b), d.extend({
                exitStr: a.exit ? d.Routing.Localization[this.options.language].formatOrder(a.exit) : "",
                dir: d.Routing.Localization[this.options.language].directions[a.direction]
              }, a)) : a.text
            }, getIconName: function (a, b) {
              switch (a.type) {
                case"Straight":
                  return 0 === b ? "depart" : "continue";
                case"SlightRight":
                  return "bear-right";
                case"Right":
                  return "turn-right";
                case"SharpRight":
                  return "sharp-right";
                case"TurnAround":
                  return "u-turn";
                case"SharpLeft":
                  return "sharp-left";
                case"Left":
                  return "turn-left";
                case"SlightLeft":
                  return "bear-left";
                case"WaypointReached":
                  return "via";
                case"Roundabout":
                  return "enter-roundabout";
                case"DestinationReached":
                  return "arrive"
              }
            }, _getInstructionTemplate: function (a, b) {
              var c = "Straight" === a.type ? 0 === b ? "Head" : "Continue" : a.type, e = d.Routing.Localization[this.options.language].instructions[c];
              return e[0] + (e.length > 1 && a.road ? e[1] : "")
            }
          }), b.exports = d.Routing
        }()
      }).call(this, "undefined" != typeof global ? global : "undefined" != typeof self ? self : "undefined" != typeof window ? window : {})
    }, {"./L.Routing.Localization": 11}], 7: [function (a, b, c) {
      (function (c) {
        !function () {
          "use strict";
          function d(a) {
            a.setSelectionRange ? a.setSelectionRange(0, 9999) : a.select()
          }

          var e = "undefined" != typeof window ? window.L : "undefined" != typeof c ? c.L : null;
          e.Routing = e.Routing || {}, e.extend(e.Routing, a("./L.Routing.Autocomplete")), e.Routing.GeocoderElement = e.Class.extend({
            includes: e.Mixin.Events,
            options: {
              createGeocoder: function (a, b, c) {
                var d = e.DomUtil.create("div", "leaflet-routing-geocoder"), f = e.DomUtil.create("input", "", d), g = c.addWaypoints ? e.DomUtil.create("span", "leaflet-routing-remove-waypoint", d) : void 0;
                return f.disabled = !c.addWaypoints, {container: d, input: f, closeButton: g}
              }, geocoderPlaceholder: function (a, b, c) {
                var d = e.Routing.Localization[c.options.language].ui;
                return 0 === a ? d.startPlaceholder : b - 1 > a ? e.Util.template(d.viaPlaceholder, {viaNumber: a}) : d.endPlaceholder
              }, geocoderClass: function () {
                return ""
              }, waypointNameFallback: function (a) {
                var b = a.lat < 0 ? "S" : "N", c = a.lng < 0 ? "W" : "E", d = (Math.round(1e4 * Math.abs(a.lat)) / 1e4).toString(), e = (Math.round(1e4 * Math.abs(a.lng)) / 1e4).toString();
                return b + d + ", " + c + e
              }, maxGeocoderTolerance: 200, autocompleteOptions: {}, language: "en"
            },
            initialize: function (a, b, c, f) {
              e.setOptions(this, f);
              var g = this.options.createGeocoder(b, c, this.options), h = g.closeButton, i = g.input;
              i.setAttribute("placeholder", this.options.geocoderPlaceholder(b, c, this)), i.className = this.options.geocoderClass(b, c), this._element = g, this._waypoint = a, this.update(), i.value = a.name, e.DomEvent.addListener(i, "click", function () {
                d(this)
              }, i), h && e.DomEvent.addListener(h, "click", function () {
                this.fire("delete", {waypoint: this._waypoint})
              }, this), new e.Routing.Autocomplete(i, function (b) {
                i.value = b.name, a.name = b.name, a.latLng = b.center, this.fire("geocoded", {waypoint: a, value: b})
              }, this, e.extend({
                resultFn: this.options.geocoder.geocode,
                resultContext: this.options.geocoder,
                autocompleteFn: this.options.geocoder.suggest,
                autocompleteContext: this.options.geocoder
              }, this.options.autocompleteOptions))
            },
            getContainer: function () {
              return this._element.container
            },
            setValue: function (a) {
              this._element.input.value = a
            },
            update: function (a) {
              var b, c = this._waypoint;
              c.name = c.name || "", !c.latLng || !a && c.name || (b = this.options.waypointNameFallback(c.latLng), this.options.geocoder && this.options.geocoder.reverse ? this.options.geocoder.reverse(c.latLng, 67108864, function (a) {
                a.length > 0 && a[0].center.distanceTo(c.latLng) < this.options.maxGeocoderTolerance ? c.name = a[0].name : c.name = b, this._update()
              }, this) : (c.name = b, this._update()))
            },
            focus: function () {
              var a = this._element.input;
              a.focus(), d(a)
            },
            _update: function () {
              var a = this._waypoint, b = a && a.name ? a.name : "";
              this.setValue(b), this.fire("reversegeocoded", {waypoint: a, value: b})
            }
          }), e.Routing.geocoderElement = function (a, b, c, d) {
            return new e.Routing.GeocoderElement(a, b, c, d)
          }, b.exports = e.Routing
        }()
      }).call(this, "undefined" != typeof global ? global : "undefined" != typeof self ? self : "undefined" != typeof window ? window : {})
    }, {"./L.Routing.Autocomplete": 3}], 8: [function (a, b, c) {
      (function (c) {
        !function () {
          "use strict";
          var d = "undefined" != typeof window ? window.L : "undefined" != typeof c ? c.L : null;
          d.Routing = d.Routing || {}, d.extend(d.Routing, a("./L.Routing.Formatter")), d.extend(d.Routing, a("./L.Routing.ItineraryBuilder")), d.Routing.Itinerary = d.Control.extend({
            includes: d.Mixin.Events,
            options: {
              pointMarkerStyle: {radius: 5, color: "#03f", fillColor: "white", opacity: 1, fillOpacity: .7},
              summaryTemplate: "<h2>{name}</h2><h3>{distance}, {time}</h3>",
              timeTemplate: "{time}",
              containerClassName: "",
              alternativeClassName: "",
              minimizedClassName: "",
              itineraryClassName: "",
              totalDistanceRoundingSensitivity: -1,
              show: !0,
              collapsible: void 0,
              collapseBtn: function (a) {
                var b = d.DomUtil.create("span", a.options.collapseBtnClass);
                d.DomEvent.on(b, "click", a._toggle, a), a._container.insertBefore(b, a._container.firstChild)
              },
              collapseBtnClass: "leaflet-routing-collapse-btn"
            },
            initialize: function (a) {
              d.setOptions(this, a), this._formatter = this.options.formatter || new d.Routing.Formatter(this.options), this._itineraryBuilder = this.options.itineraryBuilder || new d.Routing.ItineraryBuilder({containerClassName: this.options.itineraryClassName})
            },
            onAdd: function (a) {
              var b = this.options.collapsible;
              return b = b || void 0 === b && a.getSize().x <= 640, this._container = d.DomUtil.create("div", "leaflet-routing-container leaflet-bar " + (this.options.show ? "" : "leaflet-routing-container-hide ") + (b ? "leaflet-routing-collapsible " : "") + this.options.containerClassName), this._altContainer = this.createAlternativesContainer(), this._container.appendChild(this._altContainer), d.DomEvent.disableClickPropagation(this._container), d.DomEvent.addListener(this._container, "mousewheel", function (a) {
                d.DomEvent.stopPropagation(a)
              }), b && this.options.collapseBtn(this), this._container
            },
            onRemove: function () {
            },
            createAlternativesContainer: function () {
              return d.DomUtil.create("div", "leaflet-routing-alternatives-container")
            },
            setAlternatives: function (a) {
              var b, c, d;
              for (this._clearAlts(), this._routes = a, b = 0; b < this._routes.length; b++)c = this._routes[b], d = this._createAlternative(c, b), this._altContainer.appendChild(d), this._altElements.push(d);
              return this._selectRoute({route: this._routes[0], alternatives: this._routes.slice(1)}), this
            },
            show: function () {
              d.DomUtil.removeClass(this._container, "leaflet-routing-container-hide")
            },
            hide: function () {
              d.DomUtil.addClass(this._container, "leaflet-routing-container-hide")
            },
            _toggle: function () {
              var a = d.DomUtil.hasClass(this._container, "leaflet-routing-container-hide");
              this[a ? "show" : "hide"]()
            },
            _createAlternative: function (a, b) {
              var c = d.DomUtil.create("div", "leaflet-routing-alt " + this.options.alternativeClassName + (b > 0 ? " leaflet-routing-alt-minimized " + this.options.minimizedClassName : "")), e = this.options.summaryTemplate, f = d.extend({
                name: a.name,
                distance: this._formatter.formatDistance(a.summary.totalDistance, this.options.totalDistanceRoundingSensitivity),
                time: this._formatter.formatTime(a.summary.totalTime)
              }, a);
              return c.innerHTML = "function" == typeof e ? e(f) : d.Util.template(e, f), d.DomEvent.addListener(c, "click", this._onAltClicked, this), this.on("routeselected", this._selectAlt, this), c.appendChild(this._createItineraryContainer(a)), c
            },
            _clearAlts: function () {
              for (var a = this._altContainer; a && a.firstChild;)a.removeChild(a.firstChild);
              this._altElements = []
            },
            _createItineraryContainer: function (a) {
              var b, c, d, e, f, g, h = this._itineraryBuilder.createContainer(), i = this._itineraryBuilder.createStepsContainer();
              for (h.appendChild(i), b = 0; b < a.instructions.length; b++)c = a.instructions[b], f = this._formatter.formatInstruction(c, b), e = this._formatter.formatDistance(c.distance), g = this._formatter.getIconName(c, b), d = this._itineraryBuilder.createStep(f, e, g, i), this._addRowListeners(d, a.coordinates[c.index]);
              return h
            },
            _addRowListeners: function (a, b) {
              d.DomEvent.addListener(a, "mouseover", function () {
                this._marker = d.circleMarker(b, this.options.pointMarkerStyle).addTo(this._map)
              }, this), d.DomEvent.addListener(a, "mouseout", function () {
                this._marker && (this._map.removeLayer(this._marker), delete this._marker)
              }, this), d.DomEvent.addListener(a, "click", function (a) {
                this._map.panTo(b), d.DomEvent.stopPropagation(a)
              }, this)
            },
            _onAltClicked: function (a) {
              for (var b = a.target || window.event.srcElement; !d.DomUtil.hasClass(b, "leaflet-routing-alt");)b = b.parentElement;
              var c = this._altElements.indexOf(b), e = this._routes.slice(), f = e.splice(c, 1)[0];
              this.fire("routeselected", {route: f, alternatives: e})
            },
            _selectAlt: function (a) {
              var b, c, e, f;
              if (b = this._altElements[a.route.routesIndex], d.DomUtil.hasClass(b, "leaflet-routing-alt-minimized"))for (c = 0; c < this._altElements.length; c++)e = this._altElements[c], f = c === a.route.routesIndex ? "removeClass" : "addClass", d.DomUtil[f](e, "leaflet-routing-alt-minimized"), this.options.minimizedClassName && d.DomUtil[f](e, this.options.minimizedClassName), c !== a.route.routesIndex && (e.scrollTop = 0);
              d.DomEvent.stop(a)
            },
            _selectRoute: function (a) {
              this._marker && (this._map.removeLayer(this._marker), delete this._marker), this.fire("routeselected", a)
            }
          }), d.Routing.itinerary = function (a) {
            return new d.Routing.Itinerary(a)
          }, b.exports = d.Routing
        }()
      }).call(this, "undefined" != typeof global ? global : "undefined" != typeof self ? self : "undefined" != typeof window ? window : {})
    }, {"./L.Routing.Formatter": 6, "./L.Routing.ItineraryBuilder": 9}], 9: [function (a, b, c) {
      (function (a) {
        !function () {
          "use strict";
          var c = "undefined" != typeof window ? window.L : "undefined" != typeof a ? a.L : null;
          c.Routing = c.Routing || {}, c.Routing.ItineraryBuilder = c.Class.extend({
            options: {containerClassName: ""},
            initialize: function (a) {
              c.setOptions(this, a)
            },
            createContainer: function (a) {
              var b = c.DomUtil.create("table", a || ""), d = c.DomUtil.create("colgroup", "", b);
              return c.DomUtil.create("col", "leaflet-routing-instruction-icon", d), c.DomUtil.create("col", "leaflet-routing-instruction-text", d), c.DomUtil.create("col", "leaflet-routing-instruction-distance", d), b
            },
            createStepsContainer: function () {
              return c.DomUtil.create("tbody", "")
            },
            createStep: function (a, b, d, e) {
              var f, g, h = c.DomUtil.create("tr", "", e);
              return g = c.DomUtil.create("td", "", h), f = c.DomUtil.create("span", "leaflet-routing-icon leaflet-routing-icon-" + d, g), g.appendChild(f), g = c.DomUtil.create("td", "", h), g.appendChild(document.createTextNode(a)), g = c.DomUtil.create("td", "", h), g.appendChild(document.createTextNode(b)), h
            }
          }), b.exports = c.Routing
        }()
      }).call(this, "undefined" != typeof global ? global : "undefined" != typeof self ? self : "undefined" != typeof window ? window : {})
    }, {}], 10: [function (a, b, c) {
      (function (a) {
        !function () {
          "use strict";
          var c = "undefined" != typeof window ? window.L : "undefined" != typeof a ? a.L : null;
          c.Routing = c.Routing || {}, c.Routing.Line = c.LayerGroup.extend({
            includes: c.Mixin.Events,
            options: {
              styles: [{color: "black", opacity: .15, weight: 9}, {
                color: "white",
                opacity: .8,
                weight: 6
              }, {color: "red", opacity: 1, weight: 2}],
              missingRouteStyles: [{color: "black", opacity: .15, weight: 7}, {
                color: "white",
                opacity: .6,
                weight: 4
              }, {color: "gray", opacity: .8, weight: 2, dashArray: "7,12"}],
              addWaypoints: !0,
              extendToWaypoints: !0,
              missingRouteTolerance: 10
            },
            initialize: function (a, b) {
              c.setOptions(this, b), c.LayerGroup.prototype.initialize.call(this, b), this._route = a, this.options.extendToWaypoints && this._extendToWaypoints(), this._addSegment(a.coordinates, this.options.styles, this.options.addWaypoints)
            },
            addTo: function (a) {
              return a.addLayer(this), this
            },
            getBounds: function () {
              return c.latLngBounds(this._route.coordinates)
            },
            _findWaypointIndices: function () {
              var a, b = this._route.inputWaypoints, c = [];
              for (a = 0; a < b.length; a++)c.push(this._findClosestRoutePoint(b[a].latLng));
              return c
            },
            _findClosestRoutePoint: function (a) {
              var b, c, d, e = Number.MAX_VALUE;
              for (c = this._route.coordinates.length - 1; c >= 0; c--)d = a.distanceTo(this._route.coordinates[c]), e > d && (b = c, e = d);
              return b
            },
            _extendToWaypoints: function () {
              var a, b, d, e = this._route.inputWaypoints, f = this._getWaypointIndices();
              for (a = 0; a < e.length; a++)b = e[a].latLng, d = c.latLng(this._route.coordinates[f[a]]), b.distanceTo(d) > this.options.missingRouteTolerance && this._addSegment([b, d], this.options.missingRouteStyles)
            },
            _addSegment: function (coords, styles, mouselistener) {
              var i, pl;
              var that = this;

              for (i = 0; i < styles.length; i++) {
                if (styles[i].type == "polygon") {
                  //create line and simplify it with turf :)
                  var line = L.polyline(coords, styles[i]).toGeoJSON();
                  line = turf.simplify(line, 0.02, false);

                  //Buffer it with turf library :)
                  var geoJSONPoly = turf.buffer(line, 5, 'miles');
                  //return geoJson layer
                  pl = L.geoJson(geoJSONPoly, {
                    onEachFeature: function (f, l) {
                      if (mouselistener && styles[i].type == "polygon") {
                        l.on('click', that._onLineTouched, that);
                      }
                    }
                  });
                } else {
                  pl = L.polyline(coords, styles[i]);
                }
                this.addLayer(pl);
              }
              //var e, f;
              //for (e = 0; e < b.length; e++)f = c.polyline(a, b[e]), this.addLayer(f), d && f.on("mousedown", this._onLineTouched, this)
            },
            _findNearestWpBefore: function (a) {
              for (var b = this._getWaypointIndices(), c = b.length - 1; c >= 0 && b[c] > a;)c--;
              return c
            },
            _onLineTouched: function (a) {
              var b = this._findNearestWpBefore(this._findClosestRoutePoint(a.latlng));
              this.fire("linetouched", {afterIndex: b, latlng: a.latlng})
            },
            _getWaypointIndices: function () {
              return this._wpIndices || (this._wpIndices = this._route.waypointIndices || this._findWaypointIndices()), this._wpIndices
            }
          }), c.Routing.line = function (a, b) {
            return new c.Routing.Line(a, b)
          }, b.exports = c.Routing
        }()
      }).call(this, "undefined" != typeof global ? global : "undefined" != typeof self ? self : "undefined" != typeof window ? window : {})
    }, {}], 11: [function (a, b, c) {
      !function () {
        "use strict";
        L.Routing = L.Routing || {}, L.Routing.Localization = {
          en: {
            directions: {
              N: "north",
              NE: "northeast",
              E: "east",
              SE: "southeast",
              S: "south",
              SW: "southwest",
              W: "west",
              NW: "northwest"
            },
            instructions: {
              Head: ["Head {dir}", " on {road}"],
              Continue: ["Continue {dir}", " on {road}"],
              SlightRight: ["Slight right", " onto {road}"],
              Right: ["Right", " onto {road}"],
              SharpRight: ["Sharp right", " onto {road}"],
              TurnAround: ["Turn around"],
              SharpLeft: ["Sharp left", " onto {road}"],
              Left: ["Left", " onto {road}"],
              SlightLeft: ["Slight left", " onto {road}"],
              WaypointReached: ["Waypoint reached"],
              Roundabout: ["Take the {exitStr} exit in the roundabout", " onto {road}"],
              DestinationReached: ["Destination reached"]
            },
            formatOrder: function (a) {
              var b = a % 10 - 1, c = ["st", "nd", "rd"];
              return c[b] ? a + c[b] : a + "th"
            },
            ui: {startPlaceholder: "Start", viaPlaceholder: "Via {viaNumber}", endPlaceholder: "End"}
          },
          de: {
            directions: {
              N: "Norden",
              NE: "Nordosten",
              E: "Osten",
              SE: "Südosten",
              S: "Süden",
              SW: "Südwesten",
              W: "Westen",
              NW: "Nordwesten"
            },
            instructions: {
              Head: ["Richtung {dir}", " auf {road}"],
              Continue: ["Geradeaus Richtung {dir}", " auf {road}"],
              SlightRight: ["Leicht rechts abbiegen", " auf {road}"],
              Right: ["Rechts abbiegen", " auf {road}"],
              SharpRight: ["Scharf rechts abbiegen", " auf {road}"],
              TurnAround: ["Wenden"],
              SharpLeft: ["Scharf links abbiegen", " auf {road}"],
              Left: ["Links abbiegen", " auf {road}"],
              SlightLeft: ["Leicht links abbiegen", " auf {road}"],
              WaypointReached: ["Zwischenhalt erreicht"],
              Roundabout: ["Nehmen Sie die {exitStr} Ausfahrt im Kreisverkehr", " auf {road}"],
              DestinationReached: ["Sie haben ihr Ziel erreicht"]
            },
            formatOrder: function (a) {
              return a + "."
            },
            ui: {startPlaceholder: "Start", viaPlaceholder: "Via {viaNumber}", endPlaceholder: "Ziel"}
          },
          sv: {
            directions: {
              N: "norr",
              NE: "nordost",
              E: "öst",
              SE: "sydost",
              S: "syd",
              SW: "sydväst",
              W: "väst",
              NW: "nordväst"
            },
            instructions: {
              Head: ["Åk åt {dir}", " på {road}"],
              Continue: ["Fortsätt {dir}", " på {road}"],
              SlightRight: ["Svagt höger", " på {road}"],
              Right: ["Sväng höger", " på {road}"],
              SharpRight: ["Skarpt höger", " på {road}"],
              TurnAround: ["Vänd"],
              SharpLeft: ["Skarpt vänster", " på {road}"],
              Left: ["Sväng vänster", " på {road}"],
              SlightLeft: ["Svagt vänster", " på {road}"],
              WaypointReached: ["Viapunkt nådd"],
              Roundabout: ["Tag {exitStr} avfarten i rondellen", " till {road}"],
              DestinationReached: ["Framme vid resans mål"]
            },
            formatOrder: function (a) {
              return ["första", "andra", "tredje", "fjärde", "femte", "sjätte", "sjunde", "åttonde", "nionde", "tionde"][a - 1]
            },
            ui: {startPlaceholder: "Från", viaPlaceholder: "Via {viaNumber}", endPlaceholder: "Till"}
          },
          sp: {
            directions: {
              N: "norte",
              NE: "noreste",
              E: "este",
              SE: "sureste",
              S: "sur",
              SW: "suroeste",
              W: "oeste",
              NW: "noroeste"
            },
            instructions: {
              Head: ["Derecho {dir}", " sobre {road}"],
              Continue: ["Continuar {dir}", " en {road}"],
              SlightRight: ["Leve giro a la derecha", " sobre {road}"],
              Right: ["Derecha", " sobre {road}"],
              SharpRight: ["Giro pronunciado a la derecha", " sobre {road}"],
              TurnAround: ["Dar vuelta"],
              SharpLeft: ["Giro pronunciado a la izquierda", " sobre {road}"],
              Left: ["Izquierda", " en {road}"],
              SlightLeft: ["Leve giro a la izquierda", " en {road}"],
              WaypointReached: ["Llegó a un punto del camino"],
              Roundabout: ["Tomar {exitStr} salida en la rotonda", " en {road}"],
              DestinationReached: ["Llegada a destino"]
            },
            formatOrder: function (a) {
              return a + "º"
            },
            ui: {startPlaceholder: "Inicio", viaPlaceholder: "Via {viaNumber}", endPlaceholder: "Destino"}
          },
          nl: {
            directions: {
              N: "noordelijke",
              NE: "noordoostelijke",
              E: "oostelijke",
              SE: "zuidoostelijke",
              S: "zuidelijke",
              SW: "zuidewestelijke",
              W: "westelijke",
              NW: "noordwestelijke"
            },
            instructions: {
              Head: ["Vertrek in {dir} richting", " de {road} op"],
              Continue: ["Ga in {dir} richting", " de {road} op"],
              SlightRight: ["Volg de weg naar rechts", " de {road} op"],
              Right: ["Ga rechtsaf", " de {road} op"],
              SharpRight: ["Ga scherpe bocht naar rechts", " de {road} op"],
              TurnAround: ["Keer om"],
              SharpLeft: ["Ga scherpe bocht naar links", " de {road} op"],
              Left: ["Ga linksaf", " de {road} op"],
              SlightLeft: ["Volg de weg naar links", " de {road} op"],
              WaypointReached: ["Aangekomen bij tussenpunt"],
              Roundabout: ["Neem de {exitStr} afslag op de rotonde", " de {road} op"],
              DestinationReached: ["Aangekomen op eindpunt"]
            },
            formatOrder: function (a) {
              return 1 === a || a >= 20 ? a + "ste" : a + "de"
            },
            ui: {startPlaceholder: "Vertrekpunt", viaPlaceholder: "Via {viaNumber}", endPlaceholder: "Bestemming"}
          },
          fr: {
            directions: {
              N: "nord",
              NE: "nord-est",
              E: "est",
              SE: "sud-est",
              S: "sud",
              SW: "sud-ouest",
              W: "ouest",
              NW: "nord-ouest"
            },
            instructions: {
              Head: ["Tout droit au {dir}", " sur {road}"],
              Continue: ["Continuer au {dir}", " sur {road}"],
              SlightRight: ["Légèrement à droite", " sur {road}"],
              Right: ["A droite", " sur {road}"],
              SharpRight: ["Complètement à droite", " sur {road}"],
              TurnAround: ["Faire demi-tour"],
              SharpLeft: ["Complètement à gauche", " sur {road}"],
              Left: ["A gauche", " sur {road}"],
              SlightLeft: ["Légèrement à gauche", " sur {road}"],
              WaypointReached: ["Point d'étape atteint"],
              Roundabout: ["Au rond-point, prenez la {exitStr} sortie", " sur {road}"],
              DestinationReached: ["Destination atteinte"]
            },
            formatOrder: function (a) {
              return a + "º"
            },
            ui: {startPlaceholder: "Départ", viaPlaceholder: "Intermédiaire {viaNumber}", endPlaceholder: "Arrivée"}
          },
          it: {
            directions: {
              N: "nord",
              NE: "nord-est",
              E: "est",
              SE: "sud-est",
              S: "sud",
              SW: "sud-ovest",
              W: "ovest",
              NW: "nord-ovest"
            },
            instructions: {
              Head: ["Dritto verso {dir}", " su {road}"],
              Continue: ["Continuare verso {dir}", " su {road}"],
              SlightRight: ["Mantenere la destra", " su {road}"],
              Right: ["A destra", " su {road}"],
              SharpRight: ["Strettamente a destra", " su {road}"],
              TurnAround: ["Fare inversione di marcia"],
              SharpLeft: ["Strettamente a sinistra", " su {road}"],
              Left: ["A sinistra", " sur {road}"],
              SlightLeft: ["Mantenere la sinistra", " su {road}"],
              WaypointReached: ["Punto di passaggio raggiunto"],
              Roundabout: ["Alla rotonda, prendere la {exitStr} uscita"],
              DestinationReached: ["Destinazione raggiunta"]
            },
            formatOrder: function (a) {
              return a + "º"
            },
            ui: {startPlaceholder: "Partenza", viaPlaceholder: "Intermedia {viaNumber}", endPlaceholder: "Destinazione"}
          },
          pt: {
            directions: {
              N: "norte",
              NE: "nordeste",
              E: "leste",
              SE: "sudeste",
              S: "sul",
              SW: "sudoeste",
              W: "oeste",
              NW: "noroeste"
            },
            instructions: {
              Head: ["Siga {dir}", " na {road}"],
              Continue: ["Continue {dir}", " na {road}"],
              SlightRight: ["Curva ligeira a direita", " na {road}"],
              Right: ["Curva a direita", " na {road}"],
              SharpRight: ["Curva fechada a direita", " na {road}"],
              TurnAround: ["Retorne"],
              SharpLeft: ["Curva fechada a esquerda", " na {road}"],
              Left: ["Curva a esquerda", " na {road}"],
              SlightLeft: ["Curva ligueira a esquerda", " na {road}"],
              WaypointReached: ["Ponto de interesse atingido"],
              Roundabout: ["Pegue a {exitStr} saída na rotatória", " na {road}"],
              DestinationReached: ["Destino atingido"]
            },
            formatOrder: function (a) {
              return a + "º"
            },
            ui: {startPlaceholder: "Origem", viaPlaceholder: "Intermédio {viaNumber}", endPlaceholder: "Destino"}
          }
        }, b.exports = L.Routing
      }()
    }, {}], 12: [function (a, b, c) {
      (function (c) {
        !function () {
          "use strict";
          var d = "undefined" != typeof window ? window.L : "undefined" != typeof c ? c.L : null, e = a("corslite"), f = a("polyline");
          d.Routing = d.Routing || {}, d.extend(d.Routing, a("./L.Routing.Waypoint")), d.Routing.OSRM = d.Class.extend({
            options: {
              serviceUrl: "//router.project-osrm.org/viaroute",
              timeout: 3e4,
              routingOptions: {}
            }, initialize: function (a) {
              d.Util.setOptions(this, a), this._hints = {locations: {}}
            }, route: function (a, b, c, f) {
              var g, h, i, j, k = !1, l = [];
              for (g = this.buildRouteUrl(a, d.extend({}, this.options.routingOptions, f)), h = setTimeout(function () {
                k = !0, b.call(c || b, {status: -1, message: "OSRM request timed out."})
              }, this.options.timeout), j = 0; j < a.length; j++)i = a[j], l.push(new d.Routing.Waypoint(i.latLng, i.name, i.options));
              return e(g, d.bind(function (a, d) {
                var e, f, g;
                if (clearTimeout(h), !k) {
                  if (f = "HTTP request failed: " + a, g = -1, !a)try {
                    e = JSON.parse(d.responseText);
                    try {
                      return this._routeDone(e, l, b, c)
                    } catch (i) {
                      g = -3, f = i.toString()
                    }
                  } catch (i) {
                    g = -2, f = "Error parsing OSRM response: " + i.toString()
                  }
                  b.call(c || b, {status: g, message: f})
                }
              }, this)), this
            }, _routeDone: function (a, b, c, d) {
              var e, f, g, h;
              if (d = d || c, 0 !== a.status && 200 !== a.status)return void c.call(d, {
                status: a.status,
                message: a.status_message
              });
              if (e = this._decodePolyline(a.route_geometry), g = this._toWaypoints(b, a.via_points), f = [{
                  name: this._createName(a.route_name),
                  coordinates: e,
                  instructions: a.route_instructions ? this._convertInstructions(a.route_instructions) : [],
                  summary: a.route_summary ? this._convertSummary(a.route_summary) : [],
                  inputWaypoints: b,
                  waypoints: g,
                  waypointIndices: this._clampIndices(a.via_indices, e)
                }], a.alternative_geometries)for (h = 0; h < a.alternative_geometries.length; h++)e = this._decodePolyline(a.alternative_geometries[h]), f.push({
                name: this._createName(a.alternative_names[h]),
                coordinates: e,
                instructions: a.alternative_instructions[h] ? this._convertInstructions(a.alternative_instructions[h]) : [],
                summary: a.alternative_summaries[h] ? this._convertSummary(a.alternative_summaries[h]) : [],
                inputWaypoints: b,
                waypoints: g,
                waypointIndices: this._clampIndices(1 === a.alternative_geometries.length ? a.alternative_indices : a.alternative_indices[h], e)
              });
              a.hint_data && this._saveHintData(a.hint_data, b), c.call(d, null, f)
            }, _decodePolyline: function (a) {
              var b, c = f.decode(a, 6), e = new Array(c.length);
              for (b = c.length - 1; b >= 0; b--)e[b] = d.latLng(c[b]);
              return e
            }, _toWaypoints: function (a, b) {
              var c, e = [];
              for (c = 0; c < b.length; c++)e.push(d.Routing.waypoint(d.latLng(b[c]), a[c].name, a[c].options));
              return e
            }, _createName: function (a) {
              var b, c = "";
              for (b = 0; b < a.length; b++)a[b] && (c && (c += ", "), c += a[b].charAt(0).toUpperCase() + a[b].slice(1));
              return c
            }, buildRouteUrl: function (a, b) {
              for (var c, d, e, f, g, h = [], i = 0; i < a.length; i++)c = a[i], f = this._locationKey(c.latLng), h.push("loc=" + f), g = this._hints.locations[f], g && h.push("hint=" + g), c.options && c.options.allowUTurn && h.push("u=true");
              return e = d = !(b && b.geometryOnly), this.options.serviceUrl + "?instructions=" + d.toString() + "&alt=" + e.toString() + "&" + (b.z ? "z=" + b.z + "&" : "") + h.join("&") + (void 0 !== this._hints.checksum ? "&checksum=" + this._hints.checksum : "") + (b.fileformat ? "&output=" + b.fileformat : "") + (b.allowUTurns ? "&uturns=" + b.allowUTurns : "")
            }, _locationKey: function (a) {
              return a.lat + "," + a.lng
            }, _saveHintData: function (a, b) {
              var c;
              this._hints = {checksum: a.checksum, locations: {}};
              for (var d = a.locations.length - 1; d >= 0; d--)c = b[d].latLng, this._hints.locations[this._locationKey(c)] = a.locations[d]
            }, _convertSummary: function (a) {
              return {totalDistance: a.total_distance, totalTime: a.total_time}
            }, _convertInstructions: function (a) {
              var b, c, d, e, f = [];
              for (b = 0; b < a.length; b++)c = a[b], d = this._drivingDirectionType(c[0]), e = c[0].split("-"), d && f.push({
                type: d,
                distance: c[2],
                time: c[4],
                road: c[1],
                direction: c[6],
                exit: e.length > 1 ? e[1] : void 0,
                index: c[3]
              });
              return f
            }, _drivingDirectionType: function (a) {
              switch (parseInt(a, 10)) {
                case 1:
                  return "Straight";
                case 2:
                  return "SlightRight";
                case 3:
                  return "Right";
                case 4:
                  return "SharpRight";
                case 5:
                  return "TurnAround";
                case 6:
                  return "SharpLeft";
                case 7:
                  return "Left";
                case 8:
                  return "SlightLeft";
                case 9:
                  return "WaypointReached";
                case 10:
                  return "Straight";
                case 11:
                case 12:
                  return "Roundabout";
                case 15:
                  return "DestinationReached";
                default:
                  return null
              }
            }, _clampIndices: function (a, b) {
              var c, d = b.length - 1;
              for (c = 0; c < a.length; c++)a[c] = Math.min(d, Math.max(a[c], 0));
              return a
            }
          }), d.Routing.osrm = function (a) {
            return new d.Routing.OSRM(a)
          }, b.exports = d.Routing
        }()
      }).call(this, "undefined" != typeof global ? global : "undefined" != typeof self ? self : "undefined" != typeof window ? window : {})
    }, {"./L.Routing.Waypoint": 14, corslite: 1, polyline: 2}], 13: [function (a, b, c) {
      (function (c) {
        !function () {
          "use strict";
          var d = "undefined" != typeof window ? window.L : "undefined" != typeof c ? c.L : null;
          d.Routing = d.Routing || {}, d.extend(d.Routing, a("./L.Routing.GeocoderElement")), d.extend(d.Routing, a("./L.Routing.Waypoint")), d.Routing.Plan = d.Class.extend({
            includes: d.Mixin.Events,
            options: {
              dragStyles: [{color: "black", opacity: .15, weight: 9}, {
                color: "white",
                opacity: .8,
                weight: 6
              }, {color: "red", opacity: 1, weight: 2, dashArray: "7,12"}],
              draggableWaypoints: !0,
              routeWhileDragging: !1,
              addWaypoints: !0,
              reverseWaypoints: !1,
              addButtonClassName: "",
              language: "en",
              createGeocoderElement: d.Routing.geocoderElement,
              createMarker: function (a, b) {
                var c = {draggable: this.draggableWaypoints}, e = d.marker(b.latLng, c);
                return e
              },
              geocodersClassName: ""
            },
            initialize: function (a, b) {
              d.Util.setOptions(this, b), this._waypoints = [], this.setWaypoints(a)
            },
            isReady: function () {
              var a;
              for (a = 0; a < this._waypoints.length; a++)if (!this._waypoints[a].latLng)return !1;
              return !0
            },
            getWaypoints: function () {
              var a, b = [];
              for (a = 0; a < this._waypoints.length; a++)b.push(this._waypoints[a]);
              return b
            },
            setWaypoints: function (a) {
              var b = [0, this._waypoints.length].concat(a);
              return this.spliceWaypoints.apply(this, b), this
            },
            spliceWaypoints: function () {
              var a, b = [arguments[0], arguments[1]];
              for (a = 2; a < arguments.length; a++)b.push(arguments[a] && arguments[a].hasOwnProperty("latLng") ? arguments[a] : d.Routing.waypoint(arguments[a]));
              for ([].splice.apply(this._waypoints, b); this._waypoints.length < 2;)this.spliceWaypoints(this._waypoints.length, 0, null);
              this._updateMarkers(), this._fireChanged.apply(this, b)
            },
            onAdd: function (a) {
              this._map = a, this._updateMarkers()
            },
            onRemove: function () {
              var a;
              if (this._removeMarkers(), this._newWp)for (a = 0; a < this._newWp.lines.length; a++)this._map.removeLayer(this._newWp.lines[a]);
              delete this._map
            },
            createGeocoders: function () {
              var a, b, c = d.DomUtil.create("div", "leaflet-routing-geocoders " + this.options.geocodersClassName), e = this._waypoints;
              return this._geocoderContainer = c, this._geocoderElems = [], this.options.addWaypoints && (a = d.DomUtil.create("button", "leaflet-routing-add-waypoint " + this.options.addButtonClassName, c), a.setAttribute("type", "button"), d.DomEvent.addListener(a, "click", function () {
                this.spliceWaypoints(e.length, 0, null)
              }, this)), this.options.reverseWaypoints && (b = d.DomUtil.create("button", "leaflet-routing-reverse-waypoints", c), b.setAttribute("type", "button"), d.DomEvent.addListener(b, "click", function () {
                this._waypoints.reverse(), this.setWaypoints(this._waypoints)
              }, this)), this._updateGeocoders(), this.on("waypointsspliced", this._updateGeocoders), c
            },
            _createGeocoder: function (a) {
              var b = this.options.createGeocoderElement(this._waypoints[a], a, this._waypoints.length, this.options);
              return b.on("delete", function () {
                a > 0 || this._waypoints.length > 2 ? this.spliceWaypoints(a, 1) : this.spliceWaypoints(a, 1, new d.Routing.Waypoint)
              }, this).on("geocoded", function (b) {
                this._updateMarkers(), this._fireChanged(), this._focusGeocoder(a + 1), this.fire("waypointgeocoded", {
                  waypointIndex: a,
                  waypoint: b.waypoint
                })
              }, this).on("reversegeocoded", function (b) {
                this.fire("waypointgeocoded", {waypointIndex: a, waypoint: b.waypoint})
              }, this), b
            },
            _updateGeocoders: function () {
              var a, b, c = [];
              for (a = 0; a < this._geocoderElems.length; a++)this._geocoderContainer.removeChild(this._geocoderElems[a].getContainer());
              for (a = this._waypoints.length - 1; a >= 0; a--)b = this._createGeocoder(a), this._geocoderContainer.insertBefore(b.getContainer(), this._geocoderContainer.firstChild), c.push(b);
              this._geocoderElems = c.reverse()
            },
            _removeMarkers: function () {
              var a;
              if (this._markers)for (a = 0; a < this._markers.length; a++)this._markers[a] && this._map.removeLayer(this._markers[a]);
              this._markers = []
            },
            _updateMarkers: function () {
              var a, b;
              if (this._map)for (this._removeMarkers(), a = 0; a < this._waypoints.length; a++)this._waypoints[a].latLng ? (b = this.options.createMarker(a, this._waypoints[a], this._waypoints.length), b && (b.addTo(this._map), this.options.draggableWaypoints && this._hookWaypointEvents(b, a))) : b = null, this._markers.push(b)
            },
            _fireChanged: function () {
              this.fire("waypointschanged", {waypoints: this.getWaypoints()}), arguments.length >= 2 && this.fire("waypointsspliced", {
                index: Array.prototype.shift.call(arguments),
                nRemoved: Array.prototype.shift.call(arguments),
                added: arguments
              })
            },
            _hookWaypointEvents: function (a, b, c) {
              var e, f, g = function (a) {
                return c ? a.latlng : a.target.getLatLng()
              }, h = d.bind(function (a) {
                this.fire("waypointdragstart", {index: b, latlng: g(a)})
              }, this), i = d.bind(function (a) {
                this._waypoints[b].latLng = g(a), this.fire("waypointdrag", {index: b, latlng: g(a)})
              }, this), j = d.bind(function (a) {
                this._waypoints[b].latLng = g(a), this._waypoints[b].name = "", this._geocoderElems && this._geocoderElems[b].update(!0), this.fire("waypointdragend", {
                  index: b,
                  latlng: g(a)
                }), this._fireChanged()
              }, this);
              c ? (e = d.bind(function (a) {
                this._markers[b].setLatLng(a.latlng), i(a)
              }, this), f = d.bind(function (a) {
                this._map.dragging.enable(), this._map.off("mouseup", f), this._map.off("mousemove", e), j(a)
              }, this), this._map.dragging.disable(), this._map.on("mousemove", e), this._map.on("mouseup", f), h({latlng: this._waypoints[b].latLng})) : (a.on("dragstart", h), a.on("drag", i), a.on("dragend", j))
            },
            dragNewWaypoint: function (a) {
              var b = a.afterIndex + 1;
              this.options.routeWhileDragging ? (this.spliceWaypoints(b, 0, a.latlng), this._hookWaypointEvents(this._markers[b], b, !0)) : this._dragNewWaypoint(b, a.latlng)
            },
            _dragNewWaypoint: function (a, b) {
              var c, e = new d.Routing.Waypoint(b), f = this._waypoints[a - 1], g = this._waypoints[a], h = this.options.createMarker(a, e, this._waypoints.length + 1), i = [], j = d.bind(function (a) {
                var b;
                for (h && h.setLatLng(a.latlng), b = 0; b < i.length; b++)i[b].spliceLatLngs(1, 1, a.latlng)
              }, this), k = d.bind(function (b) {
                var c;
                for (h && this._map.removeLayer(h), c = 0; c < i.length; c++)this._map.removeLayer(i[c]);
                this._map.off("mousemove", j), this._map.off("mouseup", k), this.spliceWaypoints(a, 0, b.latlng)
              }, this);
              for (h && h.addTo(this._map), c = 0; c < this.options.dragStyles.length; c++)i.push(d.polyline([f.latLng, b, g.latLng], this.options.dragStyles[c]).addTo(this._map));
              this._map.on("mousemove", j), this._map.on("mouseup", k)
            },
            _focusGeocoder: function (a) {
              this._geocoderElems[a] ? this._geocoderElems[a].focus() : document.activeElement.blur()
            }
          }), d.Routing.plan = function (a, b) {
            return new d.Routing.Plan(a, b)
          }, b.exports = d.Routing
        }()
      }).call(this, "undefined" != typeof global ? global : "undefined" != typeof self ? self : "undefined" != typeof window ? window : {})
    }, {"./L.Routing.GeocoderElement": 7, "./L.Routing.Waypoint": 14}], 14: [function (a, b, c) {
      (function (a) {
        !function () {
          "use strict";
          var c = "undefined" != typeof window ? window.L : "undefined" != typeof a ? a.L : null;
          c.Routing = c.Routing || {}, c.Routing.Waypoint = c.Class.extend({
            options: {allowUTurn: !1},
            initialize: function (a, b, d) {
              c.Util.setOptions(this, d), this.latLng = c.latLng(a), this.name = b
            }
          }), c.Routing.waypoint = function (a, b, d) {
            return new c.Routing.Waypoint(a, b, d)
          }, b.exports = c.Routing
        }()
      }).call(this, "undefined" != typeof global ? global : "undefined" != typeof self ? self : "undefined" != typeof window ? window : {})
    }, {}]
  }, {}, [4])(4)
});
