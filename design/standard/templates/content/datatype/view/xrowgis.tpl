{if is_set($attribute.data_int)}
    {def $relatedObject = fetch('content', 'object', hash(
                                                          'object_id', $attribute.data_int))}
{/if}
<div>
{if $attribute.has_content}
    <div style="float: left;">
<br />
    <table>
{if is_set($relatedObject)}
        <tr>
            <td><label>{'Related Object'|i18n( 'extension/xrowgis' )}:</label></td>
            <td><a target="_blank" href="{$relatedObject.main_node.url_alias|ezurl( 'no', 'full')}">{$relatedObject.name}</a></td>
        </tr>
{/if}
        <tr>
            <td><label>{'Longitude'|i18n( 'extension/xrowgis' )}:</label></td>
            <td>{$attribute.content.longitude}</td>
        </tr>
        <tr>
            <td><label>{'Latitude'|i18n( 'extension/xrowgis' )}:</label></td>
            <td>{$attribute.content.latitude}</td>
        </tr>
        <tr>
            <td><label>{'Street'|i18n( 'extension/xrowgis' )}:</label></td>
            <td>{$attribute.content.street}</td>
        </tr>
        <tr>
            <td><label>{'ZIP'|i18n( 'extension/xrowgis' )}:</label></td>
            <td>{$attribute.content.zip}</td>
        </tr>
        <tr>
            <td><label>{'District'|i18n( 'extension/xrowgis' )}:</label></td>
            <td>{$attribute.content.district}</td>
        </tr>
        <tr>
            <td><label>{'City'|i18n( 'extension/xrowgis' )}:</label></td>
            <td>{$attribute.content.city}</td>
        </tr>
        <tr>
            <td><label>{'State'|i18n( 'extension/xrowgis' )}:</label></td>
            <td>{$attribute.content.state}</td>
        </tr>
        <tr>
            <td><label>{'Country'|i18n( 'extension/xrowgis' )}:</label></td>
            <td>{$attribute.content.country}</td>
        </tr>
    </table>
    </div>
    <div style="float: right;">
        <div id="mapContainer" style="width: 200px; height: 200px;"></div>
    </div>
{* map attribute values or define default values for lat and long *}
{if and(not($attribute.content.latitude),not($attribute.content.longitude))}
    {def $latitude = ezini("GISSettings","latitude","xrowgis.ini")}
    {def $longitude = ezini("GISSettings","longitude","xrowgis.ini")}
{else}
    {def $latitude = $attribute.content.latitude}
    {def $longitude = $attribute.content.longitude}
{/if}

<script>
{literal}
    var options = {
        div:'mapContainer',
        name:'{/literal}{ezini("GISSettings","Interface","xrowgis.ini")}{literal}',
        lat:'{/literal}{$latitude}{literal}',
        lon:'{/literal}{$longitude}{literal}',
        zoom:'{/literal}{ezini(ezini("GISSettings","Interface","xrowgis.ini"),"DefaultZoom","xrowgis.ini")}{literal}',
        css : '{/literal}{"extension/xrowgis/design/standard/stylesheets/openlayers-custom.css"|ezroot(no, full)}{literal}',
        drag :false
        };
    
    jQuery(document).ready(jQuery().servemap( 'createMap', options ));
{/literal}
</script>
{else}
    No geo information avialable.
{/if}
</div>