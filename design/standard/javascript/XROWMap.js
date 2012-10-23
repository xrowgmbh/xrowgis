XROWMap = function() {
}
XROWMap.prototype.start = function(element) {
    this.init(element);
}
XROWMap.prototype.init = function(element) {
    var map, options={}, controlOptions, layersettings={}, tmp, featureLayers=[], params_new, x=0;
    this.map, this.layer, this.styledPoint, this.lonLat, this.markers, this.params={}, this.layerOptions={};
    this.options = $.data(element);
    this.config = $('.'+this.options.config);
    this.mapOptions=this.config.data('mapoptions');
    this.projection = $(this.config).find('.baseLayer').data().projection;
    this.layerzoom = $(this.config).find('.baseLayer').data().layerzoom;
    Proj4js.defs["EPSG:25832"] = "+proj=utm +zone=32 +ellps=GRS80 +units=m +no_defs";
    
    if(typeof(this.layerzoom) == 'undefined')
    {
        this.zoom = this.mapOptions.mapview.zoom;
    }
    else
    {
        this.zoom = this.layerzoom;
    }
    
    
    OpenLayers.ImgPath = "/extension/xrowgis/design/standard/javascript/OpenLayers/img/";
    OpenLayers.Request.DEFAULT_CONFIG.url = location.host;// change the url
                                                            // from
                                                            // window.location.href
                                                            // to location .host
    
    // fix for elements which are not visibly at first, for e.g. maps which are
    // hidden in tabs
    if(typeof(this.mapOptions.mapview.height)=='undefined')
    {
        if ($(element).height() == 0) 
        {
            $(element).height($(element).width());
        }
    }
    else
    {
        $(element).height(this.mapOptions.mapview.height);
    }
    
    if(typeof(this.mapOptions.mapview.width)!='undefined')
    {
        $(element).width(this.mapOptions.mapview.width);
    }
    // initalize map Object
    this.map = new OpenLayers.Map(
                {
                    controls : [],
                    theme : this.mapOptions.theme,
                    projection: this.mapOptions.generals.projection,
                    units : this.mapOptions.generals.units,
                    panMethod : OpenLayers.Easing.Quad.easeInOut,
                    panDuration : 75,
                    eventListeners: {
                        "zoomend": zoomEnd
                    }
                });
    // set additional MapOptions
    if(typeof(this.mapOptions.mapoptions)!='undefined')
    {
        for(var i in this.mapOptions.mapoptions)
        {
            options[i] = eval(this.mapOptions.mapoptions[i]);
        }
        this.map.setOptions(options);
    }
    // create Layers
    map = this.map;
    $(this.config).find('li').each(function(index, value)
    {
        eval("this.layer = new OpenLayers.Layer." + $(this).data().service + "('"+ $(this).data().layername +"', '"+ $(this).data().url +"', " + stringify($(this).data().layerparams) + ", "+ stringify($(this).data().layeroptions) +");");

        if(typeof($(this).data().layersettings)!='undefined')
        {
            tmp = $(this).data().layersettings;
            for(var i in tmp)
            {
                layersettings[i] = eval(tmp[i]);
            }
            
            this.layer.addOptions(layersettings);
        }
        // save all special feature Layers to this.map for next steps
        if(typeof($(this).data().features) != 'undefined')
        {   
            featureLayers[x] = 
            {
                    'featureType' : $(this).data().features.featureType,
                    'layerName' : $(this).data().layername,
                    'layer' : this.layer
            }
            ++x;
        }
        map.addLayer(this.layer);
    });
    
    this.map.featureLayers = featureLayers;
    this.map = map;// @TODO: Why do we have to do it this way?!
    
    //ie 8 hack -> Bug #3182
    this.map.Z_INDEX_BASE.Control=980;
    this.map.eventsDiv.style.zIndex = this.map.Z_INDEX_BASE.Control - 1;
    
    // defining Icon stuff for gml Layer and marker Layer
    this.size = new OpenLayers.Size(this.mapOptions.icon.width, this.mapOptions.icon.height);
    this.xoffset = (Number(this.mapOptions.icon.xoffset));
    this.yoffset = (Number(this.mapOptions.icon.yoffset));
    this.offset = new OpenLayers.Pixel(this.xoffset, this.yoffset);
    this.icon = new OpenLayers.Icon(this.mapOptions.icon.src, this.size, this.offset);
//    this.icon = new OpenLayers.Icon("http://www.openstreetmap.org/openlayers/img/marker.png",new OpenLayers.Size(15, 15));

    // add simple Marker and reproject the coords
    this.markers = new OpenLayers.Layer.Markers("Marker Layer");
    this.lonLat = new Proj4js.Point(this.options.lon, this.options.lat);
    Proj4js.transform(new Proj4js.Proj(this.projection.projection), new Proj4js.Proj(this.projection.displayProjection), this.lonLat);
    this.lonLat = new OpenLayers.LonLat(this.lonLat.x, this.lonLat.y);
    this.map.addLayer(this.markers);
    this.markers.addMarker(new OpenLayers.Marker(this.lonLat, this.icon));
    
    /*REFERENZVECTOR
    var vectors = new OpenLayers.Layer.Vector("Vector Layer");
    var point = new OpenLayers.Geometry.Point(this.lonLat.lon, this.lonLat.lat);
    vectors.addFeatures([new OpenLayers.Feature.Vector(point)]);
    this.map.addLayer(vectors);
    */

    // set center
    this.map.setCenter(this.lonLat, this.zoom);
    // add controls
    if(typeof(this.mapOptions.mapview.controls)!='undefined')
    {
        map = this.map;
        controlOptions = this.mapOptions.mapview.controlOptions;
        $.each(this.mapOptions.mapview.controls, function(index, value)
                {
                    if(typeof(controlOptions)=='undefined')
                    {
                        map.addControl(new OpenLayers.Control[value]());
                    }else
                    {
                        if(typeof(controlOptions[value])!='undefined')
                        {
                            map.addControl(new OpenLayers.Control[value](controlOptions[value]));
                        }else
                        {
                            map.addControl(new OpenLayers.Control[value]());
                        }
                    }
                });
        this.map = map;
    }else// default Controls
    {
        this.map.addControl(new OpenLayers.Control.Navigation());
        this.map.addControl(new OpenLayers.Control.PanPanel());
        this.map.addControl(new OpenLayers.Control.ZoomPanel());
    }
    // render the default Map
    if (this.options.render == 'true') {
        this.map.render(element);
    }
}// end XROWMap init

// all this stuff underneath here comes to MapUtils.js...later.
$(document).ready(function() {
    var position = {};
    $('.XROWMap').each(function(index) {
        switch ($(this).data().maptype) {
            case 'POIMap':
                map = new POIMap();
                break;
            default:
                map = new XROWMap();
                $.data($(this)[0], 'render', 'true');// render the default Map
            }
        map.start($(this)[0]);
    });// ende each
    $("input.map-search").click(function()
            {
                jQuery.ez('xrowGIS_page::updateMap',{'input': $("input.global-map-search").val(), 'mapsearch' : true},
                        function(result) {
                            position  = {'coords' : {'longitude' : result.content.lon, 'latitude' : result.content.lat}};
                            handle_geolocation_query(position);
            });
            });
    $("input.current-position").click(function()
            {
                if (navigator.geolocation) {
                    initiate_geolocation();
                }else {
                    error('not supported');
                }
            });
    $(".click-list li :checkbox").click(function()
        {
            if($(this)[0].parentNode.layer.visibility===true && $(this)[0].parentNode.layer.isBaseLayer =='false')
            {
                $(this)[0].parentNode.layer.setVisibility(false);
            }else
            {
                $(this)[0].parentNode.layer.setVisibility(true);
            }
        });
    $(".click-list input[type=checkbox]").each(
            function() {
                if($(this)[0].checked === true)
                {
                    $(this)[0].parentElement.layer.setVisibility(true);
                }
            }
            );
});

function zoomEnd()
{
    $.each(this.layers, function(index, value)
            {
                if(value.isBaseLayer == "false" || value.isBaseLayer == false)
                {
                    value.redraw();
                }
            });
    
}

function stringify(jsonData) {
    var strJsonData = '{', itemCount = 0, temp;

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
