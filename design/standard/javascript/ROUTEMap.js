ROUTEMap = function() {

}
ROUTEMap.prototype = new POIMap();
ROUTEMap.prototype.constructor = ROUTEMap;

ROUTEMap.prototype.start = function(element) {
    POIMap.prototype.start(element);
    var startLonLat, startFeature, endLonLat, endFeature, 
    styledPoint = {
            externalGraphic: this.mapOptions.icon.src,
            graphicWith: this.size.w,
            graphicHeight: this.size.h,
            graphicXOffset: this.xoffset,
            graphicYOffset: this.yoffset,
            cursor : 'pointer',
            pointRadius : 13
        }
    if ((typeof (this.map.GPXLayers) != 'undefined')) {
        
        for(var i in this.map.GPXLayers)
        {
            if ((typeof (this.map.GPXLayers[i].start) != 'undefined' || this.map.GPXLayers[i].start != '')
                    && (typeof (this.map.GPXLayers[i].end) != 'undefined' || this.map.GPXLayers[i].end != '')) {
                
                startLonLat = new Proj4js.Point(this.map.GPXLayers[i].start.lon,
                        this.map.GPXLayers[i].start.lat);
                Proj4js.transform(new Proj4js.Proj(this.projection.projection),
                        new Proj4js.Proj(this.projection.displayProjection),
                        startLonLat);
                startLonLat = new OpenLayers.Geometry.Point(startLonLat.x, startLonLat.y);
                startFeature = new OpenLayers.Feature.Vector(startLonLat);
                startFeature.style = styledPoint;
                
                endLonLat = new Proj4js.Point(this.map.GPXLayers[i].end.lon,
                        this.map.GPXLayers[i].end.lat);
                Proj4js.transform(new Proj4js.Proj(this.projection.projection),
                        new Proj4js.Proj(this.projection.displayProjection),
                        endLonLat);
                endLonLat = new OpenLayers.Geometry.Point(endLonLat.x, endLonLat.y);
                endFeature = new OpenLayers.Feature.Vector(endLonLat);
                endFeature.style = styledPoint;

//                this.map.GPXLayers[i].layer.addFeatures([startFeature, endFeature]);
            }
        }
    }
    this.map.render(element);
}
