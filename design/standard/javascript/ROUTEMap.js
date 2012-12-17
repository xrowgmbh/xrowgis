ROUTEMap = function() {

}
ROUTEMap.prototype = new POIMap();
ROUTEMap.prototype.constructor = ROUTEMap;

ROUTEMap.prototype.start = function(element) {
    this.init(element);// init parent Map
    var startLonLat, endLonLat

    this.routeparams = $(this.config).find('.baseLayer').data().routeparams;

    if ((typeof (this.routeparams.start) != 'undefined' || this.routeparams.start != '') && (typeof (this.routeparams.end) != 'undefined' || this.routeparams.end != '')) {
        this.markers.removeMarker(this.markers.markers[0]);// destroy Parent Marker

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
                new Proj4js.Proj(this.projection.displayProjection), endLonLat);
        endLonLat = new OpenLayers.LonLat(endLonLat.x, endLonLat.y);
        this.markers.addMarker(new OpenLayers.Marker(endLonLat, this.icon
                .clone()));

    }
    for ( var i in this.map.GPXLayers) {
        this.map.GPXLayers[i].layer.addOptions(
            {
                strategies: [new OpenLayers.Strategy.Fixed()],
                  protocol: new OpenLayers.Protocol.HTTP({
                       url: OpenLayers.Request.DEFAULT_CONFIG.url+this.map.GPXLayers[i].url,
//                    url: 'var/TestRoute.gpx',
                    format: new OpenLayers.Format.GPX()
                }),
                     style: this.map.GPXLayers[i].style
//                projection: new OpenLayers.Projection(this.projection.projection)
            });
        console.log(this.map.GPXLayers[i].layer);
    }
    
    this.map.render(element);
}
