ROUTEMap = function() {

}
ROUTEMap.prototype = new POIMap();
ROUTEMap.prototype.constructor = ROUTEMap;

ROUTEMap.prototype.start = function(element) {
    POIMap.prototype.start(element);
    var startLonLat, endLonLat

    if ((typeof ($(this.config).find('.baseLayer').data().routeparams) != 'undefined')) {
        this.routeparams = $(this.config).find('.baseLayer').data().routeparams;
        if ((typeof (this.routeparams.start) != 'undefined' || this.routeparams.start != '')
                && (typeof (this.routeparams.end) != 'undefined' || this.routeparams.end != '')) {

            startLonLat = new Proj4js.Point(this.routeparams.start.lon,
                    this.routeparams.start.lat);
            Proj4js.transform(new Proj4js.Proj(this.projection.projection),
                    new Proj4js.Proj(this.projection.displayProjection),
                    startLonLat);
            startLonLat = new OpenLayers.LonLat(startLonLat.x, startLonLat.y);
            this.markers.addMarker(new OpenLayers.Marker(startLonLat, this.icon
                    .clone()));

            endLonLat = new Proj4js.Point(this.routeparams.end.lon,
                    this.routeparams.end.lat);
            Proj4js.transform(new Proj4js.Proj(this.projection.projection),
                    new Proj4js.Proj(this.projection.displayProjection),
                    endLonLat);
            endLonLat = new OpenLayers.LonLat(endLonLat.x, endLonLat.y);
            this.markers.addMarker(new OpenLayers.Marker(endLonLat, this.icon
                    .clone()));

        }
    }
    this.map.render(element);
}
