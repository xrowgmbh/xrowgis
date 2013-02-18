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
        jQuery.ez('xrowGIS_page::updateMap',{'input': $("input.global-map-search").val(), 'mapsearch' : true},
                function(result) {
                    position  = {'coords' : {'longitude' : result.content.lon, 'latitude' : result.content.lat}};

    //                jQuery.ajaxSetup({async:false});
                    //sets the red icon
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