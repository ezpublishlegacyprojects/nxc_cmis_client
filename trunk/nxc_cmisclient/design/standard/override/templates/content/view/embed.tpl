{def $cmis_object = fetch( 'cmis', 'object', hash( 'uri', $object.current.data_map.uri.content ) )}
{if $cmis_object}
    {if eq( $cmis_object.doc_type, 'Space' )}
        {'folder'|class_icon( normal, $current_object.summary|wash )}
    {else}
        {$cmis_object.doc_type|mimetype_icon( normal, $cmis_object.summary|wash )}
    {/if}

    {if eq( $cmis_object.base_type, 'document' )}
        <a href={concat( 'cmis/download/', $cmis_object.self_uri )|ezurl}>
    {else}
        <a href={concat( 'cmis/browser/', $cmis_object.self_uri )|ezurl}>
    {/if}
    {$cmis_object.title|wash}
    </a>
{else}
    <h1>{$object.name|wash()}</h1>
{/if}
