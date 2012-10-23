POIMap = function() {

}
POIMap.prototype = new XROWMap();
POIMap.prototype.constructor = POIMap;

POIMap.prototype.start = function(element) {
    this.init(element);//init parent Map
    this.parentMap = this.map;//we want to render the parent Map, if there is no return value from gml
    this.markerLayer;
    this.popup;
    this.layerURL=[];

    if (this.options.url != "false" || typeof(this.map.featureLayers) != 'undefined') {//if we have no url, render the default map
        
        this.markers.destroy();//destroy Parent Marker
        
        for(var i in this.map.featureLayers)
        {
            switch(this.map.featureLayers[i].featureType)
            {
            case 'GeoRSS':
                this.styledPoint = new OpenLayers.StyleMap({
                    "default" : new OpenLayers.Style({
                        graphicWidth : this.size.w,
                        graphicHeight : this.size.h,
                        externalGraphic : this.mapOptions.icon.src,
                        pointRadius : "13",
                        cursor : 'pointer'
                    })
                });
                this.map.featureLayers[i].layer.addOptions({
                    format : OpenLayers.Format.GeoRSS,
                    styleMap : this.styledPoint
                });
                this.popupControl = new OpenLayers.Control.SelectFeature(
                        this.map.featureLayers[i].layer,
                        {
                            onSelect : function(feature) {
                                var description = "";
                                this.pos = feature.geometry;
                                this.featureLonLat = new OpenLayers.LonLat(this.pos.x, this.pos.y);
                                this.map.setCenter(this.featureLonLat, 16);
                                
                                if(feature.attributes.description != 'No Description')
                                {
                                    description = "<p>" + feature.attributes.description + "</p><br />";
                                }
                                
                                if (typeof this.popup != "undefined" && this.popup != null) {
                                    this.map.removePopup(this.popup);
                                }
                                this.popup = new OpenLayers.Popup.FramedCloud("popup",
                                        this.featureLonLat,
                                        new OpenLayers.Size(200, 200), 
                                        "<h2>" + feature.attributes.title + "</h2>" 
                                            + description  +
                                        "<a href='" + feature.attributes.link + "' target='_blank'>mehr...</a>",
                                        null, 
                                        false);
                                this.popup.calculateRelativePosition = function () {
                                    return 'br';
                                }
                                this.map.addPopup(this.popup);
                                this.popup.events.register("click", this, popupDestroy);
                            }
                        });
                this.map.addControl(this.popupControl);
                this.popupControl.activate();
              break;
            case 'Shape':
                    if(typeof(this.layerURL[this.map.featureLayers[i].layer.url])!= 'object')
                    {
                        this.layerURL[this.map.featureLayers[i].layer.url] = new Array();
                    }
                    this.layerURL[this.map.featureLayers[i].layer.url][i] = this.map.featureLayers[i].layerName;
              break;
            }
        }
    }
    //@TODO: process getFeatureInfo only for the clicked Layer 
    for(var x in this.layerURL)
    {
        var tmp, map;
        map = this.map;
        tmp = this.layerURL[x];
        
        map.events.register('click', map, function(e) {
            xy = e.xy;
            params_new =
                {
                    REQUEST : "GetFeatureInfo",
                    EXCEPTIONS : "application/vnd.ogc.se_xml",
                    BBOX : map.getExtent().toBBOX(),
                    SERVICE : "WMS",
                    INFO_FORMAT : 'text/plain',
                    QUERY_LAYERS : tmp.join(', '),
                    FEATURE_COUNT : 100,
                    Layers : tmp.join(', '),
                    WIDTH : map.size.w,
                    HEIGHT : map.size.h,
                    format : 'image/png',
                    srs : map.layers[0].params.SRS
                };
                params_new.version = "1.1.1";
                params_new.x = parseInt(e.xy.x);
                params_new.y = parseInt(e.xy.y);
            OpenLayers.loadURL(
                    ""+x+"",
                    params_new, this, setHTML);
            OpenLayers.Event.stop(e);
        });
    }
    this.map.render(element);
}

//all this stuff underneath here comes to MapUtils.js...later.

function setHTML(response) {
    var cat="", src="", leg="", linkinfo="", lines, vals, popup_info;

    if (response.responseText.indexOf('no features were found') == -1) {
        lines = response.responseText.split('\n');

        for (lcv = 0; lcv < (lines.length); lcv++) {
            vals = lines[lcv].replace(/^\s*/,'').replace(/\s*$/,'').replace(/ = /,"=").replace(/'/g,'').split('=');
            if (vals[1] == "") {
                vals[1] = "";
            }
            if (vals[0].indexOf('Name') != -1 ) {
                cat = vals[1];
            } else if (vals[0].indexOf('NAME') != -1 ) {
                cat = vals[1];
            } else if (vals[0].indexOf('SOURCE') != -1 ) {
                src = vals[1];
            } else if (vals[0].indexOf('INFO') != -1 ) {
                leg = vals[1];
            } else if (vals[0].indexOf('info') != -1 ) {
                 leg = vals[1];
            } else if (vals[0].indexOf('HREF') != -1 ) {
                if(vals[1]!='')
                {
                    linkinfo = "<br /><a href='" + vals[1] + "' target='_blank'>mehr...</a>";
                }
                
            }
        }
        popup_info = "<h2>" + cat +
                     "</h2><p>" + leg + "</p>"
                       + linkinfo;
        
        this.featureLonLat = this.getLonLatFromPixel(window.xy);
        this.setCenter(this.featureLonLat, 16);
        if (typeof this.popup != "undefined" && this.popup != null) {
            this.removePopup(this.popup);
        }
        this.popup = new OpenLayers.Popup.FramedCloud("popup",
                this.featureLonLat,
                new OpenLayers.Size(200, 200), 
                popup_info,
                null, 
                false);
        this.popup.calculateRelativePosition = function () {
            return 'br';
        }
        this.addPopup(this.popup);
        this.popup.events.register("click", this, popupDestroy);
    }
}

function popupDestroy(e) {
    if(this.popup != null)
    {
        this.popup.destroy();
        this.popup = null;
    }
    OpenLayers.Util.safeStopPropagation(e);
}

function initiate_geolocation() {
    navigator.geolocation.getCurrentPosition(handle_geolocation_query);  
}  


//@TODO Make it more generic...avoid hardcoded icon path 
function handle_geolocation_query(position){
    if(typeof(window.currentPos)!= 'undefined')
    {
        window.currentPos.destroy();
    }
    if(position.coords.longitude != 0 && position.coords.latitude != 0)
    {
        var lonLat = new Proj4js.Point(position.coords.longitude, position.coords.latitude);
        Proj4js.transform(new Proj4js.Proj(window.map.projection.projection), new Proj4js.Proj(window.map.projection.displayProjection), lonLat);
        currentPos = new OpenLayers.Layer.Markers("Current Position", {rendererOptions : {zIndexing : true}});
        window.map.map.addLayer(currentPos);
        currentPos.setZIndex( 1001 );
        lonLat = new OpenLayers.LonLat(lonLat.x, lonLat.y);
        currentPos.addMarker(new OpenLayers.Marker(lonLat, new OpenLayers.Icon("/extension/hannover/design/hannover/images/openlayers-custom/curpos.png", new OpenLayers.Size(24, 32))));
        window.map.map.setCenter(lonLat, window.map.zoom);
    }

} 
