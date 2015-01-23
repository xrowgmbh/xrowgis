{*You can also use $node to get the attribute you want to show in the Popup*}
<span style="display:none;" class="coID" data-id="{$node.contentobject_id}"></span>
<h2>{$node.name|wash}</h2>
{if is_set( $collection_attributes.GeoRSS.image )}
    <img class="{$collection_attributes.GeoRSS.image.class}" alt="{$collection_attributes.image.GeoRSS.alt}" src={$collection_attributes.GeoRSS.image.src} />
{/if}
{if is_set( $collection_attributes.GeoRSS.address )}
    <p>
        {$collection_attributes.GeoRSS.address.0}<br>
        {$collection_attributes.GeoRSS.address.1} {$collection_attributes.GeoRSS.address.2}
    </p>
{/if}
{if is_set( $collection_attributes.GeoRSS.description )}
    <p>{$collection_attributes.GeoRSS.description}</p>
{/if}
<a href={$collection_attributes.GeoRSS.link|ezurl('no', 'full')}>{'more'|i18n('extension/xrowgis')}...</a>
{undef}