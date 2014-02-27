<!--If you need to add eventListeners to your layers -> data-layerlisteners='{"eventListeners" : {"loadend" : "layerLoaded", "visibilitychanged" : "layerLoaded"}}' -->
{def $url_array = $url|explode('://')
     $serviceURL = ezini("DataServices","ServiceURL","xrowgis.ini")
     $displayClass = ezini("Two-Col-Map","DisplayClass","xrowgis.ini")
     $attributionText = ezini("Two-Col-Map","AttributionText","xrowgis.ini")
     $theme = ezini("Assets","Theme","xrowgis.ini")
     $default = ezini("Assets","DefaultIcon","xrowgis.ini")
     $special = ezini("Assets","SpecialIcon","xrowgis.ini")
     $scale_osm = 16
     $scale_hde = 4}
{if $url_array.0|eq('eznode')}
    {set $url = concat('xrowgis/georss/', $url_array.1)|ezurl('no', 'full')}
{/if}
{if $url}
    {def $maptype = "POIMap"}
{/if}

{switch match=$scale}
    {case match='1'} 
        {set $scale_osm = 17
             $scale_hde = 5}
    {/case}
    {case match='2'} 
        {set $scale_osm = 16
             $scale_hde = 5}
    {/case}
    {case match='3'} 
        {set $scale_osm = 15
             $scale_hde = 4}
    {/case}
    {case match='4'} 
        {set $scale_osm = 14
             $scale_hde = 4}
    {/case}
    {case match='5'} 
        {set $scale_osm = 13
             $scale_hde = 3}
    {/case}
    {case match='6'} 
        {set $scale_osm = 12
             $scale_hde = 3}
    {/case}
    {case match='7'} 
        {set $scale_osm = 11
             $scale_hde = 2}
    {/case}
    {case match='8'} 
        {set $scale_osm = 10
             $scale_hde = 2}
    {/case}
    {case match='9'} 
        {set $scale_osm = 9
             $scale_hde = 1}
    {/case}
    {case match='10'} 
        {set $scale_osm = 8
             $scale_hde = 1}
    {/case}
    {case match='11'} 
        {set $scale_osm = 7
             $scale_hde = 0}
    {/case}
    {case match='12'} 
        {set $scale_osm = 6
             $scale_hde = 0}
    {/case}
{/switch}

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
                        data-mapoptions='{"generals" : {"units" : "m", "projection" : "EPSG:25832"}, "mapview" : {"controls" : ["Navigation", "PanPanel", "ZoomPanel", "Attribution"], "controlOptions" : {"Attribution" : {"displayClass":"{/literal}{$displayClass}{literal}"}}, "zoom":"{/literal}{$scale_osm}{literal}"}, "theme" : "{/literal}{$theme}{literal}", "assets" : {"icon" : {/literal}{$default}{literal}, "curPos" : {/literal}{$special.curPos}{literal}}}'>
                       {/literal}
                       {switch match=$layer}
                           {case match='OSM'}
                           {literal}
                               <li class="baseLayer" 
                                    data-service="OSM"
                                    data-url="{/literal}{$serviceURL.OSM}{literal}"
                                    data-projection='{"displayProjection" : "EPSG:900913", "projection" : "EPSG:4326"}'
                                    data-layerparams='{}'
                                    data-layeroptions='{"isBaseLayer" : true}'
                                    data-layerzoom="{/literal}{$scale_osm}{literal}"
                                    data-default="active" 
                                    data-layername="OSM" >OSM</li>
                            {/literal}
                            {/case}
                            {case match='Hannover'}
                                {literal}
                                <li class="baseLayer"
                                    data-service="WMS"
                                    data-url="{/literal}{$serviceURL.WMS}{literal}"
                                    data-layersettings='{"maxExtent" : "new OpenLayers.Bounds(516000, 5774000, 590000, 5838000)", "scales" : "[4000, 6000, 8000,12000,15000,17000]"}'
                                    data-projection='{"displayProjection" : "EPSG:25832", "projection" : "EPSG:4326"}'
                                    data-layerparams='{"layers" : "Hannover", "format" : "image/png", "tiled": true}'
                                    data-layeroptions='{"isBaseLayer" : true, "attribution" : "&copy; {/literal}{concat(currentdate()|datetime( 'custom', '%Y' ), ', ' , $attributionText)}{literal}"}'
                                    data-layerzoom="{/literal}{$scale_hde}{literal}"
                                    data-default="active" 
                                    data-layername="WMS" >WMS</li>
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
                            data-layeroptions='{"isBaseLayer" : false, "visibility" : true}'
                            data-features='{"featureType" : "GeoRSS"}'
                            data-default="active" 
                            data-layername="GeoRSS" >GeoRSS</li>
                    </ul>
                    {/literal}
                {/if}
<!-- map content: END -->