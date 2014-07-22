{if is_set($height)|not()}
    {def $height = "100%"}
{/if}
{if is_set($width)|not()}
    {def $width = "100%"}
{/if}
{if is_set($zoom)|not()}
    {def $zoom = "16"}
{/if}
{if is_set($class)|not()}
    {def $class = "global-map-config click-list"}
{/if}
{if is_set($mapname)|not()}
    {def $mapname = "POIMap"}
{/if}
{if is_set($mapsearch)|not()}
    {def $mapsearch = true()} 
{/if}
{if is_set($geolocate)|not()}
    {def $geolocate = true()} 
{/if}
class="{$class}" data-mapsearch="true" data-mapname="{$mapname}" data-mapoptions='{literal}{ "generals" :{ "units" : "m", "projection" : "EPSG:25832" }, "mapview" : { {/literal}"height" : "{$height}",{if is_set($width)}"width" : "{$width}",{/if}"controls" : [{if $geolocate|eq(true())}"Geolocate", {/if}"Navigation", "PanPanel", "ZoomPanel", "Attribution", "Button"], "controlOptions" : {literal}{ "Attribution" : { "displayClass":"{/literal}{ezini("Two-Col-Map","DisplayClass","xrowgis.ini")}{literal}" } },{/literal} "zoom": {$zoom} }, "theme" : "{ezini("Assets","Theme","xrowgis.ini")}{literal}", "assets" : { "icon" : {/literal}{ezini("Assets","DefaultIcon","xrowgis.ini")}{literal}, "curPos" : {/literal}{ezini("Assets","SpecialIcon","xrowgis.ini").curPos}{literal} }}{/literal}'
{undef}