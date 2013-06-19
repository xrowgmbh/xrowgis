{def $searchclasses = array()
     $page_limit = 5
     $searchtext = $variables.SearchText}
{foreach $variables.classes as $class}
    {set $searchclasses = $searchclasses|append($class)}
{/foreach}
{def $search = fetch( ezfind, search, hash( 'text', $searchtext,
                                            'class_id', $searchclasses ),
                                            'limit', $page_limit)
      $search_count = $search.SearchCount
      $search_result = $search[SearchResult]}
{foreach $search_result as $item}
    <p>{$item.name}</p>
{/foreach}