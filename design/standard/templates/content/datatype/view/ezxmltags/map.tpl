{def $url_array = $url|explode('://')}
{if $url_array.0|eq('eznode')}
    {set $url = concat('xrowgis/georss/', $url_array.1)|ezurl('no', 'full')}
{/if}
{if $url}
    {def $maptype = "POIMap"}
{/if}
<!-- map content: START -->
    <div class="XROWMap custom_map"
        data-maptype="{if is_set($maptype)}{$maptype}{else}{ezini("GISSettings","DefaultMapType","xrowgis.ini")}{/if}"
        data-lat="{if and(is_set($lat), $lat|eq('0')|not())}{$lat}{else}{ezini("GISSettings","latitude","xrowgis.ini")}{/if}"
        data-lon="{if and(is_set($lon), $lon|eq('0')|not())}{$lon}{else}{ezini("GISSettings","longitude","xrowgis.ini")}{/if}"
        data-config="{concat('custom-map-config-', currentdate())}">
    </div>
        <ul class="{concat('custom-map-config-', currentdate())}"
                        {literal}
                        style="display:none;"
                        data-mapname="POIMap"
                        data-mapoptions='{"generals" : {"units" : "m", "projection" : "EPSG:25832"}, "mapview" : {"controls" : ["Navigation", "PanPanel", "ZoomPanel", "Attribution"], "controlOptions" : {"Attribution" : {"displayClass":"{/literal}{$displayClass}{literal}"}}, "zoom":"16"}, "theme" : "/extension/hannover/design/hannover/stylesheets/openlayers-custom.css" , "icon" : {"src" : "/extension/hannover/design/hannover/images/openlayers-custom/marker.png", "height" : "64", "width" : "24", "xoffset" : -12, "yoffset" : -32}}'>
                       {/literal}
                       {switch match=$layer}
                           {case match='OSM'}
                           {literal}
                               <li class="baseLayer" 
                                    data-service="OSM"
                                    data-url="http://admin.hannover.de/osm-tiles/${z}/${x}/${y}.png"
                                    data-projection='{"displayProjection" : "EPSG:900913", "projection" : "EPSG:4326"}'
                                    data-layerparams='{}'
                                    data-layeroptions='{"isBaseLayer" : true}'
                                    data-layerzoom="16"
                                    data-default="active" 
                                    data-layername="OSM" >OSM</li>
                            {/literal}
                            {/case}
                            {case match='Hannover'}
                                {literal}
                                <li class="baseLayer"
                                    data-service="WMS"
                                    data-url="http://admin.hannover.de/geoserver/Hannover/wms"
                                    data-layersettings='{"maxExtent" : "new OpenLayers.Bounds(516000, 5774000, 590000, 5838000)", "scales" : "[4000, 6000, 8000, 10000, 15000]"}'
                                    data-projection='{"displayProjection" : "EPSG:25832", "projection" : "EPSG:4326"}'
                                    data-layerparams='{"layers" : "Hannover", "format" : "image/png", "tiled": true}'
                                    data-layeroptions='{"isBaseLayer" : true, "attribution" : "&copy; {/literal}{concat(currentdate()|datetime( 'custom', '%Y' ), ', ' , $attributionText)}{literal}"}'
                                    data-layerzoom="4"
                                    data-default="active" 
                                    data-layername="Hannover" >Region Hannover</li>
                                {/literal}
                            {/case}
                        {/switch}
                    {if $url}
                        {literal}
                        <li data-service="GML"
                            data-url="{/literal}{$url}{literal}"
                            data-projection='{}'
                            data-layersettings='{}'
                            data-layerparams='{"tiled" : true}'
                            data-layeroptions='{"isBaseLayer" : false}'
                            data-features='{"featureType" : "GeoRSS"}'
                            data-default="active" 
                            data-layername="GeoRSS" >GeoRSS</li>
                    </ul>
                    {/literal}
                {/if}
<!-- map content: END -->
