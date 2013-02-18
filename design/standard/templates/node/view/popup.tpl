<span style="display:none;" class="coID" data-id="{$node.contentobject_id}"></span>

<h2>{$node.name|wash}</h2>

{if is_set( $node.data_map.image )}
    {$node.data_map.text.content|wash}
{/if}
{if is_set( $node.data_map.text )}
    {$node.data_map.text.content|wash}
{/if}

<a href="{$node.url_alias}">{'more'|i18n('extension/xrowgis')}...</a>
