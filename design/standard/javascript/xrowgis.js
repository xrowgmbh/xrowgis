(function () {
    jQuery.fn.serializeJSON = function () {
        var json = {};
        jQuery.map(jQuery(this).serializeArray(), function(n, i) {
            json[n['name']] = n['value'];
        });
        return json;
    };
})(jQuery);

(function () {
	OpenLayers.ImgPath = "/extension/xrowgis/design/standard/javascript/OpenLayers/img/";
    var methods = {
    		createMap : function (options) {
                var controls,
                    map = new OpenLayers.Map({
                    div : "mapContainer",
                    displayProjection : new OpenLayers.Projection("EPSG:4326"),
                    units : "m",
                    maxResolution : 156543.0339,
                    maxExtent : new OpenLayers.Bounds(-20037508, -20037508,
                            20037508, 20037508.34)
                });
            var styledPoint = new OpenLayers.StyleMap({
                "default" : new OpenLayers.Style({
                    pointRadius : "13",
                    externalGraphic : "http://openlayers.org/api/img/marker.png",
                    cursor : 'pointer'
                })
            }),
            // create OSM layer
                osm = new OpenLayers.Layer.OSM(),
            // create Vector layer
                markers = new OpenLayers.Layer.Vector("Markers", {
                displayInLayerSwitcher : false,
                styleMap : styledPoint
            });
            map.addLayers([osm, markers]);
            var lonLat = new OpenLayers.LonLat(options.lon, options.lat).transform(
                    new OpenLayers.Projection(map.displayProjection), map
                            .getProjectionObject());
            controls = {
                drag : new OpenLayers.Control.DragFeature(markers, {
                    'onComplete' : this.onCompleteMove
                })
            }
            map.addControl(controls['drag']);

            if (options.drag == true) {
                controls['drag'].activate();
            }
            var params = {
                map : map,
                lonLat : lonLat,
                layer : markers
            }
            this.drawFeatures(params);

            map.setCenter(lonLat, options.zoom);
            map.addControl(new OpenLayers.Control.MousePosition());

            if((options.lat == '' || options.lon == '' || options.lat == 0 || options.lon == 0) && jQuery('#xrowGIS-rel').val()=='noRel')
            {
                jQuery.ajaxSetup({async : false});
                jQuery().servemap('setMapCenter');
                jQuery('#recomContainer').css('display', 'none');
                jQuery('#xrowGIS-lon').val('');
                jQuery('#xrowGIS-lat').val('');
                jQuery('#xrowGIS-country-input').val('');
            }
            },
        updateMap : function(options) {
            if(typeof options == "object"){
                var data = options;
                data.data = this.serializeJSON();
            }
            else{
                var data = this.serializeJSON();
                data['attr_id'] = options;
                data.zoom = 16;
            }
            jQuery
                    .ez(
                            'xrowGIS_page::updateMap',
                            data,
                            function(result) {
                                jQuery('#mapContainer').remove().fadeOut('slow');
                                jQuery('.mapContainer')
                                        .append(
                                                '<div id="mapContainer" style="width: 400px; height: 400px;"></div>');
                                var options = {
                                	div : 'mapContainer',
                                    name : result.content.name,
                                    lat : result.content.lat,
                                    lon : result.content.lon,
                                    zoom : data.zoom,
                                    drag : true
                                };

                                jQuery().servemap('createMap', options);

                                jQuery('#xrowGIS-lon').val(result.content.lon);
                                jQuery('#xrowGIS-lat').val(result.content.lat);

                                jQuery
                                .ez(
                                        'xrowGIS_page::getAlpha2', {'lon':options.lon, 'lat':options.lat},function(result) {
                                            jQuery('#xrowGIS-country-input').val(result.content.country);
                                        });// set the right country anyway based on lonlat
                                
                                var show = false;
                                if(result.content.zip != null || typeof(result.content.zip) != 'undefined')
                                {
                                    jQuery('#xrowGIS-zip').replaceWith('<td id="xrowGIS-zip">'+result.content.zip+'</td>');
                                    show = true;
                                }
                                if(result.content.street != null || typeof(result.content.street) != 'undefined')
                                {
                                    jQuery('#xrowGIS-street').replaceWith('<td id="xrowGIS-street">'+result.content.street+'</td>');
                                    show = true;
                                }
                                if(result.content.district != null || typeof(result.content.district) != 'undefined')
                                {
                                    jQuery('#xrowGIS-district').replaceWith('<td id="xrowGIS-district">'+result.content.district+'</td>');
                                    show = true;
                                }
                                if(result.content.city != null || typeof(result.content.city) != 'undefined')
                                {
                                    jQuery('#xrowGIS-city').replaceWith('<td id="xrowGIS-city">'+result.content.city+'</td>');
                                    show = true;
                                }
                                if(result.content.state != null || typeof(result.content.state) != 'undefined')
                                {
                                    jQuery('#xrowGIS-state').replaceWith('<td id="xrowGIS-state">'+result.content.state+'</td>');
                                    show = true;
                                }
                                if(show == true)
                                {
                                    jQuery('#recomContainer').css('display', 'block');
                                }
                                else
                                {
                                    jQuery('#recomContainer').css('display', 'none');
                                }
                            });
        },

        takeOverAdress : function () {
            jQuery('#recomContainer').css('display', 'none');
            jQuery('#xrowGIS-street-input').val(jQuery('#xrowGIS-street').text());
            jQuery('#xrowGIS-zip-input').val(jQuery('#xrowGIS-zip').text());
            jQuery('#xrowGIS-district-input').val(jQuery('#xrowGIS-district').text());
            jQuery('#xrowGIS-city-input').val(jQuery('#xrowGIS-city').text());
            jQuery('#xrowGIS-state-input').val(jQuery('#xrowGIS-state').text());
        },

        resetForm : function () {
            jQuery.ajaxSetup({async : false});
            jQuery().servemap('setMapCenter');
            
            jQuery('#recomContainer').css('display', 'none');
            jQuery('#xrowGIS-lon').val('');
            jQuery('#xrowGIS-lat').val('');
            jQuery('#xrowGIS-street-input').val('');
            jQuery('#xrowGIS-zip-input').val('');
            jQuery('#xrowGIS-district-input').val('');
            jQuery('#xrowGIS-city-input').val('');
            jQuery('#xrowGIS-state-input').val('');
            jQuery('#xrowGIS-country-input').val('');
        },

        setMapCenter : function () {
            jQuery.ez('xrowGIS_page::getMapCenter', {}, function(result) {
                    var options = {
                    	div : 'mapContainer',
                        name : result.content.name,
                        lat : result.content.lat,
                        lon : result.content.lon,
                        zoom : 12,
                        drag : true,
                        reverse: true
                    };
                    jQuery().servemap( 'updateMap', options );
            });
        },

        addRelation : function(data) {
            jQuery.ez('xrowGIS_page::addRelation', data, function(result) {
                if (result.content != null) {
                    var options = {
                    	div : 'mapContainer',
                        name : result.content.name,
                        lat : result.content.lat,
                        lon : result.content.lon,
                        zoom : 12,
                        drag : false,
                    };
                    jQuery('.ajaxupdate').html(result.content.template);
                    jQuery().servemap('createMap', options);
                }
            });
        },

        releaseRelation : function(data) {
            jQuery.ez('xrowGIS_page::releaseRelation', data, function(result) {
                jQuery('.ajaxupdate').html(result.content.template);
                var options = {
                	div : 'mapContainer',
                    name : result.content.name,
                    lat : result.content.lat,
                    lon : result.content.lon,
                    zoom : 16,
                    drag : true,
                };
                jQuery().servemap('createMap', options);
                jQuery
                .ez(
                        'xrowGIS_page::getAlpha2', {'lon':result.content.lon, 'lat':result.content.lat},function(result) {
                            jQuery('#xrowGIS-country-input').val(result.content.country);
                        });// set the right country anyway based on lonlat
                jQuery('#xrowGIS-lon').val(result.content.lon);
                jQuery('#xrowGIS-lat').val(result.content.lat);
            });
        }
    };
    jQuery.fn.onCompleteMove = function(feature) {
        var newLonLat = new OpenLayers.LonLat(feature.geometry.x,
                feature.geometry.y).transform(new OpenLayers.Projection(
                "EPSG:900913"), new OpenLayers.Projection("EPSG:4326"));

        jQuery('#xrowGIS-lon').val(newLonLat.lon);
        jQuery('#xrowGIS-lat').val(newLonLat.lat);
        
        var data = {
        		div : 'mapContainer',
                lat : newLonLat.lat,
                lon : newLonLat.lon,
                zoom : 16,
                reverse : true,
                drag : true,
        }

        jQuery().servemap( 'updateMap', data );
    };

    jQuery.fn.drawFeatures = function(options) {
        var layer = options.layer;
        var map = options.map;
        var lonLat = options.lonLat;

        layer.removeFeatures(layer.features);
        var center = map.getViewPortPxFromLonLat(map.getCenter());

        var features = [];
        features.push(new OpenLayers.Feature.Vector(
                new OpenLayers.Geometry.Point(lonLat.lon, lonLat.lat)));

        layer.addFeatures(features);
    };

    jQuery.fn.servemap = function(method) {
        // Method calling logic
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(
                    arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            jQuery.error('Method ' + method
                    + ' does not exist on jQuery.servemap');
        }

    };

})(jQuery);

jQuery(document)
        .ready(
                (function () {
                    if (jQuery('input.uploadImage')) {
                        jQuery('input.uploadImage')
                                .live(
                                        'click',
                                        function(e) {
                                            var idArray = jQuery(this).attr(
                                                    'id').split('_'), url = jQuery(
                                                    'input#'
                                                            + jQuery(this)
                                                                    .attr('id')
                                                            + '_url').val(), page_top = e.pageY - 400, body_half_width = jQuery(
                                                    'body').width() / 2;
                                            if (body_half_width > 510)
                                                var page_left = body_half_width - 200;
                                            else
                                                var page_left = body_half_width - 300;
                                            var innerHTML = '<div id="mce_'
                                                    + idArray[3]
                                                    + '" class="clearlooks2" style="width: 510px; height: 509px; top: '
                                                    + page_top
                                                    + 'px; left: '
                                                    + page_left
                                                    + 'px; overflow: auto; z-index: 300020;">'
                                                    + '<div id="mce_'
                                                    + idArray[3]
                                                    + '_top" class="mceTop"><div class="mceLeft"></div><div class="mceCenter"></div><div class="mceRight"></div><span id="mce_'
                                                    + idArray[3]
                                                    + '_title">Add GIS Relation</span></div>'
                                                    + '<div id="mce_'
                                                    + idArray[3]
                                                    + '_middle" class="mceMiddle">'
                                                    + '<div id="mce_'
                                                    + idArray[3]
                                                    + '_left" class="mceLeft"></div>'
                                                    + '<span id="mce_'
                                                    + idArray[3]
                                                    + '_content">'
                                                    + '<iframe src="'
                                                    + url
                                                    + '" class="uploadFrame_xrowGIS" id="uploadFrame_'
                                                    + jQuery(this).attr('id')
                                                    + '" name="uploadFrame_'
                                                    + jQuery(this).attr('id')
                                                    + '" style="border: 0pt none; width: 500px; height: 480px;" />'
                                                    + '</span>'
                                                    + '<div id="mce_'
                                                    + idArray[3]
                                                    + '_right" class="mceRight"></div>'
                                                    + '</div>'
                                                    + '<div id="mce_'
                                                    + idArray[3]
                                                    + '_bottom" class="mceBottom"><div class="mceLeft"></div><div class="mceCenter"></div><div class="mceRight"></div><span id="mce_'
                                                    + idArray[3]
                                                    + '_status">Content</span></div>'
                                                    + '<a class="mceClose" id="mce_'
                                                    + idArray[3]
                                                    + '_close"></a>' + '</div>'
                                                    + '</div>', blocker = '<div id="mceModalBlocker" class="clearlooks2_modalBlocker" style="z-index: 300017; display: block;"></div>';
                                            jQuery('body').append(innerHTML);
                                            jQuery('body').append(blocker);
                                            jQuery(
                                                    'a#mce_' + idArray[3]
                                                            + '_close')
                                                    .live(
                                                            'click',
                                                            function(e) {
                                                                jQuery(
                                                                        '#mce_'
                                                                                + idArray[3])
                                                                        .remove();
                                                                jQuery(
                                                                        '#mceModalBlocker')
                                                                        .remove();
                                                            });
                                        });
                    }
                }));
