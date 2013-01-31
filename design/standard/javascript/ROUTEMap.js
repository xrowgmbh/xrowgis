ROUTEMap = function() {

}
ROUTEMap.prototype = new POIMap();
ROUTEMap.prototype.constructor = ROUTEMap;

ROUTEMap.prototype.start = function(element) {
    POIMap.prototype.start(element);
    var startLonLat, startFeature;
    this.map.layerLinkage=[];

    for(var i in this.map.GPXLayers)
        {
            if(this.map.GPXLayers[i].show.marker != false && typeof(this.map.GPXLayers[i].show.marker) != 'undefined')
            {
                if ((typeof (this.map.GPXLayers[i].start) != 'undefined' || this.map.GPXLayers[i].start != '')
                        && (typeof (this.map.GPXLayers[i].end) != 'undefined' || this.map.GPXLayers[i].end != '')) {
                    
                    this.GPXMarkers = new OpenLayers.Layer.Markers("GPXMarkers_"+this.map.GPXLayers[i].layer.id);
                    
                    //add start Marker Layer
                    startLonLat = new Proj4js.Point(this.map.GPXLayers[i].start.lon, this.map.GPXLayers[i].start.lat);
                    Proj4js.transform(new Proj4js.Proj(this.projection.projection), new Proj4js.Proj(this.projection.displayProjection), startLonLat);
                    startLonLat = new OpenLayers.LonLat(startLonLat.x, startLonLat.y);
                    this.GPXMarkers.addMarker(new OpenLayers.Marker(startLonLat, this.icon.clone()));

                    //add end Marker
                    endLonLat = new Proj4js.Point(this.map.GPXLayers[i].end.lon, this.map.GPXLayers[i].end.lat);
                    Proj4js.transform(new Proj4js.Proj(this.projection.projection), new Proj4js.Proj(this.projection.displayProjection), endLonLat);
                    endLonLat = new OpenLayers.LonLat(endLonLat.x, endLonLat.y);
                    this.GPXMarkers.addMarker(new OpenLayers.Marker(endLonLat, this.icon.clone()));

                    this.map.addLayer(this.GPXMarkers);
                    this.GPXMarkers.setVisibility(this.map.GPXLayers[i].layer.visibility);

                    //let's save the linkage between parent layer and start+end Point to hide and show them together if needed
                    this.map.layerLinkage[this.map.GPXLayers[i].layer.id]=["GPXMarkers_"+this.map.GPXLayers[i].layer.id];
                }
            }
            //try to center the viewport and adjust the zoom using the start point lonlat
            if(this.map.GPXLayers[i].show.zoom != false && typeof(this.map.GPXLayers[i].show.zoom) != 'undefined')
            {
                this.map.setCenter(startLonLat, this.map.getZoomForExtent(this.map.GPXLayers[i].layer.getExtent(), false));
            }
        }
    this.map.render(element);
    
}

