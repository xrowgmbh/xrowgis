POIMap = function() {

}
POIMap.prototype = new XROWMap();

POIMap.prototype.constructor = POIMap;

POIMap.prototype.start = function(element) {
    this.init(element);//init parent Map 
    var styleMapOptions;
    this.markerLayer;
    this.popup;
    this.layerURL=[];
    this.map.layerLinkage=[];
    this.map.featureLinkage={};
    this.map.selectLayers=[];

    if (this.options.url != "false" || typeof(this.map.featureLayers) != 'undefined') {//if we have no url, render the default map

        this.markers.removeMarker(this.markers.markers[0]);// destroy Parent Marker

        for(var i in this.map.featureLayers)
        {
            switch(this.map.featureLayers[i].featureType)
            {
            case 'GeoRSS':
                if(this.map.featureLayers[i].layerAssets != undefined && this.map.featureLayers[i].layerAssets.src != undefined)
                {
                    styleMapOptions = {
                            graphicWidth : this.map.featureLayers[i].layerAssets.width,
                            graphicHeight : this.map.featureLayers[i].layerAssets.height,
                            graphicXOffset : this.map.featureLayers[i].layerAssets.xoffset,
                            graphicYOffset : this.map.featureLayers[i].layerAssets.yoffset,
                            externalGraphic : this.map.featureLayers[i].layerAssets.src,
                            pointRadius : "13",
                            cursor : 'pointer'
                        };
                }
                else
                {
                    styleMapOptions = {
                            graphicWidth : this.icon.size.w,
                            graphicHeight : this.icon.size.h,
                            graphicXOffset : this.icon.offset.x,
                            graphicYOffset : this.icon.offset.y,
                            externalGraphic : this.icon.url,
                            pointRadius : "13",
                            cursor : 'pointer'
                        };
                }
                this.styledPoint = new OpenLayers.StyleMap({
                    "default" : new OpenLayers.Style(styleMapOptions)});
                
                this.map.featureLayers[i].layer.addOptions({
                    format : OpenLayers.Format.GeoRSS,
                    styleMap : this.styledPoint
                });
                
                this.map.featureLayers[i].layer.featureType = this.map.featureLayers[i].featureType;
                this.map.selectLayers.push(this.map.featureLayers[i].layer);
                //add Linkage between contentobject an Feature on layer
                this.map.featureLayers[i].layer.events.register('featureadded', this.map.featureLayers[i].layer, function(event){
                this.map.featureLinkage[$($(event.feature.attributes.description)[0]).data().id] = event.feature.id;
                });
              break;
            case 'GPX':
                that = this;
                $.ajaxSetup({
                    async: false
                    });
                $(".XROWMap").addClass("is_loading");
                $.get(""+this.map.GPXLayers[this.map.featureLayers[i].layer.id].url+"",{},function(xml){
                    that.map.featureLayers[i].layer.featureContent = {'attributes' : 
                                                                        {'description' : $(xml).find("item").find("description").text(), 
                                                                         'link': $(xml).find("item").find("link").text(),
                                                                         'title' : $(xml).find("item").find("title").text()
                                                                         }
                                                                      };
                    that.map.featureLayers[i].layer.featureURL = that.map.GPXLayers[that.map.featureLayers[i].layer.id].url;
                    that.map.featureLayers[i].layer.featureType = that.map.featureLayers[i].featureType;
                    startLonLat = new Proj4js.Point(that.map.GPXLayers[that.map.featureLayers[i].layer.id].start.lon, that.map.GPXLayers[that.map.featureLayers[i].layer.id].start.lat);
                    Proj4js.transform(new Proj4js.Proj(that.projection.projection), new Proj4js.Proj(that.projection.displayProjection), startLonLat);
                    that.map.featureLayers[i].layer.featurePoint = {'x' : startLonLat.x, 'y' : startLonLat.y};
                    $(".XROWMap").removeClass("is_loading");
                    });
                this.map.selectLayers.push(this.map.featureLayers[i].layer);
                
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
        for(var i in this.map.GPXLayers)
        {
            handleGPXLayer(this.map.GPXLayers[i]);
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
    initPopups();
    //make them global
    window.featureLayers=this.map.featureLayers;
    
    this.map.render(element);
}
