{def $alfresco_object = fetch( 'alfresco', 'object', hash( 'id', $object.current.data_map.id.content ) )}
{if $alfresco_object}
	{*include uri='design:alfresco/view.tpl' current_object=$alfresco_object*}
	{if eq( $alfresco_object.type, 'document' )}
		{$alfresco_object.doc_type|mimetype_icon( small, $alfresco_object.doc_type )} 
		<a href={concat( 'alfresco/download/(id)/', $alfresco_object.selfUri|urlencode|urlencode )|ezurl}>{$object.current.name|wash|i18n( 'alfresco' )}</a>
	{/if}
{/if}
