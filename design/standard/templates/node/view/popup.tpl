{*You can also use $node to get the attribute you want to show in the Popup*}
<span style="display:none;" class="coID" data-id="{$node.contentobject_id}"></span>
<h2>{$node.name|wash}</h2>
{if is_set( $collection_attributes.GeoRSS.image )}
    <image class="{$collection_attributes.GeoRSS.image.class}" alt="{$collection_attributes.image.GeoRSS.alt}" src="{$collection_attributes.GeoRSS.image.src}">
{/if}
{if is_set( $collection_attributes.GeoRSS.description )}
    <p>{$collection_attributes.GeoRSS.description}</p>
{/if}
<a href={$collection_attributes.GeoRSS.link|ezurl('double', 'full')}>{'more'|i18n('extension/xrowgis')}...</a>
{undef}