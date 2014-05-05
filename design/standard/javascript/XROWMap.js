XROWMap = function() {
}
XROWMap.prototype.start = function(element) {
    this.init(element);
}
XROWMap.prototype.init = function(element) {
    var ini, map, options={}, controlOptions, layersettings={}, tmp, url, featureLayers=[], GPXLayers=[], x=0, y=0, that;
    this.map, this.layer, this.styledPoint, this.lonLat, this.markers, this.params={}, this.layerOptions={};
    this.options = $.data(element);
    this.config = $('.'+this.options.config);
    this.mapOptions=this.config.data('mapoptions');
    this.projection = $(this.config).find('.baseLayer').data().projection;
    this.layerzoom = $(this.config).find('.baseLayer').data().layerzoom;
    
    Proj4js.defs["EPSG:25832"] = "+proj=utm +zone=32 +ellps=GRS80 +units=m +no_defs";
    OpenLayers.Layer.GML = OpenLayers.Class(OpenLayers.Layer.GML, {requestFailure:false});
    OpenLayers.ImgPath = "/extension/xrowgis/design/standard/javascript/OpenLayers/img/";
    OpenLayers.Request.DEFAULT_CONFIG.url = location.host;// change the url
                                                            // from
                                                            // window.location.href
                                                            // to location .host
    if(typeof(this.layerzoom) == 'undefined')
    {
        this.zoom = this.mapOptions.mapview.zoom;
    }
    else
    {
        this.zoom = this.layerzoom;
    }
    // fix for elements which are not visibly at first, for e.g. maps which are hidden in tabs
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
    // set additional MapOptions.mapoptins
    if(typeof(this.mapOptions.mapoptions)!='undefined')
    {
        for(var i in this.mapOptions.mapoptions)
        {
            options[i] = eval(this.mapOptions.mapoptions[i]);
        }
        this.map.setOptions(options);
    }

    //create Layers
    map = this.map;
    
    $(this.config).find('li').each(function(index, value)
    {
        if($(this).data().service == 'Vector')
        {
            //perhaps we need to handle different kinds of vector layers
            switch($(this).data().vectortype)
            {
                case 'gpx':
                    // Add the Layer with the GPX Track
                    this.layer = new OpenLayers.Layer.Vector( $(this).data().layername , {
                        isBaseLayer: $(this).data().layeroptions.isBaseLayer,
                        visibility: $(this).data().layeroptions.visibility,
                        strategies: [new OpenLayers.Strategy.Fixed()],
                        protocol: new OpenLayers.Protocol.HTTP({
                            url: $(this).data().routeparams.url,
                            format: new OpenLayers.Format.GPX()
                        }),
                        style: $(this).data().routeparams.style,
                        projection: new OpenLayers.Projection("EPSG:4326")
                    });
                    //make different config informations accessable for further progress
                    GPXLayers[this.layer.id] = 
                    {
                       'layer' : this.layer,
                       'start' : $(this).data().routeparams.start,
                       'end' : $(this).data().routeparams.end,
                       'url' : $(this).data().routeparams.featureURL,
                       'show' : $(this).data().routeparams.show
                    };
                    break;
            }
        }
        else
        {
            if($(this).data().url != undefined)
            {
                url = $(this).data().url;
            }
            eval("this.layer = new OpenLayers.Layer." + $(this).data().service + "('" + $(this).data().layername + "', '" + url + "', " + stringify($(this).data().layerparams) + ", " + stringify($(this).data().layeroptions) + ");");
        }
        //some layers need a special treatment - place it here if needed
        switch($(this).data().service)
        {
            case 'GML':
                this.layer.setVisibility($(this).data().layeroptions.visibility);//check why we have to set the visibility here
        }
        
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
                    'layerAssets' : $(this).data().layerassets,
                    'layer' : this.layer
            }
            ++x;
        }
        //are there eventlistener we have to add to the Layer?!
        if(typeof($(this).data().layerlisteners) != 'undefined')
        {
            tmp = $(this).data().layerlisteners.eventListeners;
            for( property in $(this).data().layerlisteners.eventListeners ) { 
                this.layer.events.register(property, this.layer, function(event){
                    eval(tmp[property]+'();');
                });
            }
        }
        map.addLayer(this.layer);
    });
    this.map.featureLayers = featureLayers;
    this.map.GPXLayers = GPXLayers;
    this.map = map;

    //ie 8 hack -> Bug #3182
    this.map.Z_INDEX_BASE.Control=980;
    this.map.eventsDiv.style.zIndex = this.map.Z_INDEX_BASE.Control - 1;
    // defining Icon stuff for gml Layer and marker Layer
    this.size = new OpenLayers.Size(this.mapOptions.assets.icon.width, this.mapOptions.assets.icon.height);
    this.xoffset = (Number(this.mapOptions.assets.icon.xoffset));
    this.yoffset = (Number(this.mapOptions.assets.icon.yoffset));
    this.offset = new OpenLayers.Pixel(this.xoffset, this.yoffset);
    this.icon = new OpenLayers.Icon(this.mapOptions.assets.icon.src, this.size, this.offset);
    
    // add simple Marker and reproject the coords
    this.markers = new OpenLayers.Layer.Markers("Marker Layer");
    this.lonLat = new Proj4js.Point(this.options.lon, this.options.lat);
    Proj4js.transform(new Proj4js.Proj(this.projection.projection), new Proj4js.Proj(this.projection.displayProjection), this.lonLat);
    this.lonLat = new OpenLayers.LonLat(this.lonLat.x, this.lonLat.y);
    this.map.addLayer(this.markers);
    this.markers.addMarker(new OpenLayers.Marker(this.lonLat, this.icon));

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
        this.map.addControl(new OpenLayers.Control.Geolocate());
        this.map.addControl(new OpenLayers.Control.Button());
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
                poimap=map;
                break;
            default:
                map = new XROWMap();
                $.data($(this)[0], 'render', 'true');// render the default Map
            }
        map.start($(this)[0]);
    });// ende each
    
    $("input.map-search").click(function()
    {
        mapSearch();
    });
    $('#map-search-form').submit(function()
    {
        mapSearch();
    });
    
    $("input.current-position").click(function()
    {
        if (navigator.geolocation) {
            initiate_geolocation();
            }else {
                error('not supported');
            }
    });
    $(".olControlGeolocate").click(function()
    {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position)
            {
                if(position.coords.longitude != 0 && position.coords.latitude != 0)
                {
                    var lonLat = new Proj4js.Point(position.coords.longitude, position.coords.latitude);
                    Proj4js.transform(new Proj4js.Proj(poimap.projection.projection), new Proj4js.Proj(poimap.projection.displayProjection), lonLat);
                    currentPos = new OpenLayers.Layer.Markers("Current Position", {rendererOptions : {zIndexing : true}});
                    poimap.map.addLayer(currentPos);
                    currentPos.setZIndex( 1001 );
                    lonLat = new OpenLayers.LonLat(lonLat.x, lonLat.y);
                    currentPos.addMarker(new OpenLayers.Marker(lonLat, new OpenLayers.Icon(poimap.mapOptions.assets.curPos.src, new OpenLayers.Size(poimap.mapOptions.assets.curPos.width, poimap.mapOptions.assets.curPos.height))));
                    poimap.map.setCenter(lonLat, poimap.zoom);
                }
            });
         }else {
             error('not supported');
         }
    });
   
    $(".click-list li :checkbox").click(function()
    {
        if($(this)[0].parentNode.layer.visibility===true && ($(this)[0].parentNode.layer.isBaseLayer===false || $(this)[0].parentNode.layer.isBaseLayer=='false'))
        {
            if(typeof(window.map.map.layerLinkage[$(this)[0].parentNode.layer.id]) != 'undefined')
            {
                $(window.map.map.layerLinkage[$(this)[0].parentNode.layer.id]).each(function(index, value)
                {
                     window.map.map.getLayersByName(value)[0].setVisibility(false);
                });
            }
            $(this)[0].parentNode.layer.setVisibility(false);
        }else
        {
            if(typeof(window.map.map.layerLinkage[$(this)[0].parentNode.layer.id]) != 'undefined')
            {
                $(window.map.map.layerLinkage[$(this)[0].parentNode.layer.id]).each(function(index, value)
                {
                    window.map.map.getLayersByName(value)[0].setVisibility(true);
                });
            }
            $(this)[0].parentNode.layer.setVisibility(true);
         }
    });
    $(".click-list input[type=checkbox]").each(
        function() {
                if($(this)[0].checked === true)
                {
                    if(typeof(window.map.map.layerLinkage[$(this)[0].parentElement.layer.id]) != 'undefined')
                    {
                        $(window.map.map.layerLinkage[$(this)[0].parentElement.layer.id]).each(function(index, value)
                                {
                                    window.map.map.getLayersByName(value)[0].setVisibility(true);
                                });
                    }
                    $(this)[0].parentElement.layer.setVisibility(true);
                }
            }
      );
});
