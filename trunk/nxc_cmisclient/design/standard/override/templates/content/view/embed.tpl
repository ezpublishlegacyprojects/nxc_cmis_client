{def $cmis_object = fetch( 'cmis_client', 'object', hash( 'uri', $object.current.data_map.uri.content ) )}
{if $cmis_object}
    {if eq( $cmis_object.class_identifier, 'image' )}
        <img src={concat( 'cmis_client/content/', $cmis_object.self_uri )|ezurl} alt="{$cmis_object.title|wash}">
    {else}
        {if eq( $cmis_object.doc_type, 'Folder' )}
            {'folder'|class_icon( normal, $current_object.summary|wash )}
        {else}
            {$cmis_object.doc_type|mimetype_icon( normal, $cmis_object.summary|wash )}
        {/if}

        {if eq( $cmis_object.base_type, 'document' )}
            <a href={concat( 'cmis_client/download/', $cmis_object.self_uri )|ezurl}>
        {else}
            <a href={concat( 'cmis_client/browser/', $cmis_object.self_uri )|ezurl}>
        {/if}
        {$cmis_object.title|wash}
       </a>
    {/if}
{else}
    <h1>{$object.name|wash()}</h1>
{/if}
