function handleGPXLayer(GPXLayer)
{
    var startLonLat,
        endLonLat,
        startFeature,
        GPXMarkers;
    if(GPXLayer.show.marker != false && typeof(GPXLayer.show.marker) != 'undefined')
    {
        if ((typeof (GPXLayer) != 'undefined' || GPXLayer.start != '')
                && (typeof (GPXLayer.end) != 'undefined' || GPXLayer.end != '')) {
            
            GPXMarkers = new OpenLayers.Layer.Markers("GPXMarkers_"+GPXLayer.layer.id);
            
            //add start Marker Layer
            startLonLat = new Proj4js.Point(GPXLayer.start.lon, GPXLayer.start.lat);
            Proj4js.transform(new Proj4js.Proj(this.map.projection.projection), new Proj4js.Proj(this.map.projection.displayProjection), startLonLat);
            startLonLat = new OpenLayers.LonLat(startLonLat.x, startLonLat.y);
            GPXMarkers.addMarker(new OpenLayers.Marker(startLonLat, this.map.icon.clone()));

            //add end Marker
            endLonLat = new Proj4js.Point(GPXLayer.end.lon, GPXLayer.end.lat);
            Proj4js.transform(new Proj4js.Proj(this.map.projection.projection), new Proj4js.Proj(this.map.projection.displayProjection), endLonLat);
            endLonLat = new OpenLayers.LonLat(endLonLat.x, endLonLat.y);
            GPXMarkers.addMarker(new OpenLayers.Marker(endLonLat, this.map.icon.clone()));

            this.map.map.addLayer(GPXMarkers);
            GPXMarkers.setVisibility(GPXLayer.layer.visibility);

            //let's save the linkage between parent layer and start+end Point to hide and show them together if needed
            this.map.map.layerLinkage[GPXLayer.layer.id]=["GPXMarkers_"+GPXLayer.layer.id];
        }
    }
    //try to center the viewport and adjust the zoom using the start point lonlat
    if(GPXLayer.show.zoom != false && typeof(GPXLayer.show.zoom) != 'undefined')
    {
        this.map.map.setCenter(startLonLat, this.map.map.getZoomForExtent(GPXLayer.layer.getExtent(), false));
    }
}

function initPopups()
{
    this.popupControl = new OpenLayers.Control.SelectFeature( this.map.map.selectLayers, {
        onSelect : function(feature) {
            
            if(feature.layer.featureType == 'GPX')
            {
                this.pos = feature.geometry.components[feature.geometry.components.length/2];
                if(typeof(this.pos) == 'undefined')
                {
                    this.pos = feature.layer.featurePoint;
                }
                feature.attributes = feature.layer.featureContent.attributes;
            }else
            {
                this.pos = feature.geometry;
            }
            
            this.featureLonLat = new OpenLayers.LonLat(this.pos.x, this.pos.y);
            //this.map.setCenter(this.featureLonLat, 16);
            
            if (typeof this.popup != "undefined" && this.popup != null) {
                this.map.removePopup(this.popup);
            }
            this.popup = new OpenLayers.Popup.FramedCloud("popup",
                this.featureLonLat,
                new OpenLayers.Size(200, 200), 
                feature.attributes.description,
                null, 
                true);
            this.popup.calculateRelativePosition = function () {
                return 'br';
            }
            this.map.addPopup(this.popup);
            this.popup.events.register("click", this, popupDestroy);
        }
    });
    this.map.map.addControl(this.popupControl);
    this.popupControl.activate();
}

//needed for shape File Layers
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
        popup_info = "<h2>" + cat + "</h2><p>" + leg + "</p>" + linkinfo;
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
            true);
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
        currentPos.addMarker(new OpenLayers.Marker(lonLat, new OpenLayers.Icon(window.map.mapOptions.assets.curPos.src, new OpenLayers.Size(window.map.mapOptions.assets.curPos.width, window.map.mapOptions.assets.curPos.height))));
        window.map.map.setCenter(lonLat, window.map.zoom);
    }
}

function handleGISRequests(initialPoint)
{
    var data = {},
        position = {},
        SearchClass = new Array();
    
    if(initialPoint != undefined)
    {
        $(".location-search-map input.global-map-search").val(initialPoint);
    }
    if( $("input.global-map-search").length )
    {
        jQuery.ez('xrowGIS_page::updateMap',{'input': $("input.global-map-search").val(), 'mapsearch' : true}, function(result) {
            position  = {'coords' : {'longitude' : result.content.lon, 'latitude' : result.content.lat}};
            handle_geolocation_query(position);
            
            $('input[name="SearchClass"]:checked').each(function(){
                SearchClass.push($(this).val());
            });
            data = {
                'SearchClass'  : SearchClass,
                'SearchText'   : $('input[name="SearchText"]').val(),
                'position'     : position.coords
            };
            jQuery.ez( 'xrowGIS_page::ShowCurPosItems', data, function(result) {
                $("#cur_pos_list").html(result.content.template);
            });
        });
    }
}

function mapSearch()
{
    //deprecated
    jQuery.ez('xrowGIS_page::updateMap',{'input': $("input.global-map-search").val(), 'mapsearch' : true}, function(result) {
        position  = {'coords' : {'longitude' : result.content.lon, 'latitude' : result.content.lat}};
        handle_geolocation_query(position);
    });
}

function mapAddressSearch(inputText) {
    jQuery.ez('xrowGIS_page::updateMap',{'input': $(inputText).val(), 'mapsearch' : true}, function(result) {
        position  = {'coords' : {'longitude' : result.content.lon, 'latitude' : result.content.lat}};
        handle_geolocation_query(position);
    });
}

function zoomEnd()
{
    $.each(this.layers, function(index, value) {
        if(value.isBaseLayer == "false" || value.isBaseLayer == false)
        {
            value.redraw();
        }
    });
}

function changeVisibility(layerArray)
{
    var tmp;
    if(typeof(layerArray.layer)!='undefined')
    {
        tmp = [layerArray];
    }else
    {
        tmp = layerArray;
    }
    $.each(tmp, function(index, value)
    {
        switch(value.layer.visibility)
        {
            case false:
                tmp = true;
              break;
            case true:
                tmp = false;
              break;
        }
        value.layer.setVisibility(tmp)
    });
    
}

function stringify(jsonData) {
    var strJsonData = '{', 
        itemCount = 0, 
        temp;

    for (var item in jsonData) {
        if (itemCount > 0) {
            strJsonData += ', ';
        }
    temp = jsonData[item];
    if (typeof(temp) == 'object') {
        s =  stringify(temp);   
    } else {
        s = '"' + temp + '"';
    }   
    strJsonData += '"' + item + '":' + s;
        itemCount++;
    }
    strJsonData += '}';
    return strJsonData;
}