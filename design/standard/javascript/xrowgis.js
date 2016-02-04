(function () {
    jQuery.fn.serializeJSON = function () {
        var json = {};
        jQuery.map(jQuery(this).serializeArray(), function(n, i) {
            json[n['name']] = n['value'];
        });
        return json;
    };
})(jQuery);

(function ( $ ) {
    OpenLayers.ImgPath = "/extension/xrowgis/design/standard/javascript/OpenLayers/img/";
    var methods = {
            createMap : function (options) {
                var controls,
                    map = new OpenLayers.Map({
                        div : 'mapContainer_'+options.attributeID,
                        theme : "http://"+location.host+"/extension/xrowgis/design/standard/javascript/OpenLayers/theme/default/style.css",
                        displayProjection : new OpenLayers.Projection("EPSG:4326"),
                        units : "m",
                        maxResolution : 156543.0339,
                        maxExtent : new OpenLayers.Bounds(-20037508, -20037508, 20037508, 20037508.34)});

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
            controls = { drag : new OpenLayers.Control.DragFeature(markers, {
                'onComplete' : this.onCompleteMove,
                'options' : options })}
            map.addControl(controls['drag']);

            if (options.drag == true) {
                controls['drag'].activate();
            }
            var params = { map : map,
                           lonLat : lonLat,
                           layer : markers }
            this.drawFeatures(params);

            map.setCenter(lonLat, options.zoom);
            map.addControl(new OpenLayers.Control.MousePosition());

            if((options.lat == '' || options.lon == '' || options.lat == 0 || options.lon == 0) && $('#xrowGIS-rel_'+options.attributeID).val()=='noRel')
            {
                $.ajaxSetup({async : false});
                $().servemap('setMapCenter', options);
                $('#recomContainer_'+options.attributeID).css('display', 'none');
                $('#xrowGIS-lon_'+options.attributeID).val('');
                $('#xrowGIS-lat_'+options.attributeID).val('');
            }
            },
        updateMap : function(options) {
            if(typeof options == "object"){
                var data = options;
                data.data = this.serializeJSON();
            }
            else{
                var data = this.serializeJSON();
                data['attributeID'] = options;
                data.zoom = 16;
            }
            $.ez('xrowGIS_page::updateMap', data, function(result) {
                $('#mapContainer_'+data.attributeID).remove().fadeOut('slow');
                $('.mapContainer_'+data.attributeID).append('<div id="mapContainer_'+data.attributeID+'" style="width: 400px; height: 400px;"></div>');
                var options = {
                    div : 'mapContainer_'+data.attributeID,
                    attributeID : data.attributeID,
                    name : result.content.name,
                    lat : result.content.lat,
                    lon : result.content.lon,
                    zoom : data.zoom,
                    drag : true};
                $().servemap('createMap', options);

                $('#xrowGIS-lon_'+data.attributeID).val(result.content.lon);
                $('#xrowGIS-lat_'+data.attributeID).val(result.content.lat);

                $.ez('xrowGIS_page::getAlpha2', {'lon':options.lon, 'lat':options.lat},function(result) {
                    $('#xrowGIS-country-input_'+data.attributeID).val(result.content.country);
                });// set the right country anyway based on lonlat
                
                var show = false;
                if(result.content.zip != null || typeof(result.content.zip) != 'undefined')
                {
                    $('#xrowGIS-zip_'+data.attributeID).replaceWith('<td id="xrowGIS-zip_'+data.attributeID+'">'+result.content.zip+'</td>');
                    show = true;
                }
                if(result.content.street != null || typeof(result.content.street) != 'undefined')
                {
                    $('#xrowGIS-street_'+data.attributeID).replaceWith('<td id="xrowGIS-street_'+data.attributeID+'">'+result.content.street+'</td>');
                    show = true;
                }
                if(result.content.district != null || typeof(result.content.district) != 'undefined')
                {
                    $('#xrowGIS-district_'+data.attributeID).replaceWith('<td id="xrowGIS-district_'+data.attributeID+'">'+result.content.district+'</td>');
                    show = true;
                }
                if(result.content.city != null || typeof(result.content.city) != 'undefined')
                {
                    $('#xrowGIS-city_'+data.attributeID).replaceWith('<td id="xrowGIS-city_'+data.attributeID+'">'+result.content.city+'</td>');
                    show = true;
                }
                if(result.content.state != null || typeof(result.content.state) != 'undefined')
                {
                    $('#xrowGIS-state_'+data.attributeID).replaceWith('<td id="xrowGIS-state_'+data.attributeID+'">'+result.content.state+'</td>');
                    show = true;
                }
                if(show == true)
                {
                    $('#recomContainer_'+data.attributeID).css('display', 'block');
                }
                else
                {
                    $('#recomContainer_'+data.attributeID).css('display', 'none');
                }
            });
        },

        takeOverAdress : function (data) {
            $('#recomContainer_'+data.attributeID).css('display', 'none');
            $('#xrowGIS-street-input_'+data.attributeID).val($('#xrowGIS-street_'+data.attributeID).text());
            $('#xrowGIS-zip-input_'+data.attributeID).val($('#xrowGIS-zip_'+data.attributeID).text());
            $('#xrowGIS-district-input_'+data.attributeID).val($('#xrowGIS-district_'+data.attributeID).text());
            $('#xrowGIS-city-input_'+data.attributeID).val($('#xrowGIS-city_'+data.attributeID).text());
            $('#xrowGIS-state-input_'+data.attributeID).val($('#xrowGIS-state_'+data.attributeID).text());
        },

        resetForm : function (data) {
            $.ajaxSetup({async : false});
            $().servemap('setMapCenter', data);
            $('#recomContainer_'+data.attributeID).css('display', 'none');
            $('#xrowGIS-lon_'+data.attributeID).val('');
            $('#xrowGIS-lat_'+data.attributeID).val('');
            $('#xrowGIS-street-input_'+data.attributeID).val('');
            $('#xrowGIS-zip-input_'+data.attributeID).val('');
            $('#xrowGIS-district-input_'+data.attributeID).val('');
            $('#xrowGIS-city-input_'+data.attributeID).val('');
            $('#xrowGIS-state-input_'+data.attributeID).val('');
            $('#xrowGIS-country-input_'+data.attributeID).val('');
        },

        setMapCenter : function (options) {
            $.ez('xrowGIS_page::getMapCenter', {}, function(result) {
                var data = {
                    div : 'mapContainer_'+options.attributeID,
                    attributeID : options.attributeID,
                    name : result.content.name,
                    lat : result.content.lat,
                    lon : result.content.lon,
                    zoom : 12,
                    drag : true,
                    reverse: true
                };
                $().servemap( 'updateMap', data );
            });
        },

        addRelation : function(data) {
            $.ez('xrowGIS_page::addRelation', data, function(result) {
                if (result.content != null) {
                    var options = {
                        div : 'mapContainer_'+data.attributeID,
                        attributeID : data.attributeID,
                        name : result.content.name,
                        lat : result.content.lat,
                        lon : result.content.lon,
                        zoom : 16,
                        drag : false,
                    };
                    $('.ajaxupdate_'+data.attributeID).html(result.content.template);
                    $().servemap('createMap', options);
                }
            });
        },

        releaseRelation : function(data) {
            $.ez('xrowGIS_page::releaseRelation', data, function(result) {
                var options = {
                    div : 'mapContainer_'+result.content.attributeID,
                    attributeID : result.content.attributeID,
                    name : result.content.name,
                    lat : result.content.lat,
                    lon : result.content.lon,
                    zoom : 16,
                    drag : true,
                };
                $('.ajaxupdate_'+data.attributeID).html(result.content.template);
                $().servemap('createMap', options);
                $.ez('xrowGIS_page::getAlpha2', {'lon':result.content.lon, 'lat':result.content.lat},function(result) {
                    $('#xrowGIS-country-input_'+data.attributeID).val(result.content.country);
                });// set the right country anyway based on lonlat
                $('#xrowGIS-lon_'+data.attributeID).val(result.content.lon);
                $('#xrowGIS-lat_'+data.attributeID).val(result.content.lat);
            });
        }
    };
    $.fn.onCompleteMove = function(feature) {
        var newLonLat = new OpenLayers.LonLat(feature.geometry.x, feature.geometry.y).transform(new OpenLayers.Projection( "EPSG:900913"), new OpenLayers.Projection("EPSG:4326")),
                 data = this.handlers.drag.control.options;
        
        $('#xrowGIS-lon_'+data.attributeID).val(newLonLat.lon);
        $('#xrowGIS-lat_'+data.attributeID).val(newLonLat.lat);

        var data = {
                div : data.div,
                attributeID : data.attributeID,
                lat : newLonLat.lat,
                lon : newLonLat.lon,
                zoom : 16,
                reverse : true,
                drag : true,
        }

        $().servemap( 'updateMap', data );
    };

    $.fn.drawFeatures = function(options) {
        var layer = options.layer;
        var map = options.map;
        var lonLat = options.lonLat;

        layer.removeFeatures(layer.features);
        var center = map.getViewPortPxFromLonLat(map.getCenter());

        var features = [];
        features.push(new OpenLayers.Feature.Vector(new OpenLayers.Geometry.Point(lonLat.lon, lonLat.lat)));
        layer.addFeatures(features);
    };

    $.fn.servemap = function(method) {
        // Method calling logic
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call( arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on $.servemap');
        }
    };

})(jQuery);

jQuery(document).ready( (function () {
    jQuery('input.uploadImage').live( 'click', function(e) {
        var idArray = jQuery(this).attr('id').split('_'),
            url = jQuery('input#' + jQuery(this).attr('id') + '_url').val(),
            page_top = e.pageY - 400,
            body_half_width = jQuery('body').width() / 2;
        
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
        jQuery('a#mce_' + idArray[3] + '_close').on( 'click', function(e) {
            jQuery('#mce_' + idArray[3]).remove();
            jQuery('#mceModalBlocker').remove();
        });
    });
}));
