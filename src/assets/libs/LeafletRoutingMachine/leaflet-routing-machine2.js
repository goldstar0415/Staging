function asd123123(coords, styles, mouselistener) {
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


}
