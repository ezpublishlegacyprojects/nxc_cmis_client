<div class="border-box">
    <div class="border-tl">
        <div class="border-tr">
            <div class="border-tc">
            </div>
        </div>
    </div>
    <div class="border-ml">
        <div class="border-mr">
            <div class="border-mc float-break">

                <div class="content-view-full">
                    <div class="class-{$object.class_identifier}">

                        {def $alfresco_object = fetch( 'alfresco', 'object', hash( 'id', $object.data_map.id.content ) )}
                        {if $alfresco_object}
                            {include uri='design:alfresco/view.tpl'
                                     current_object=$alfresco_object}

                            {if eq( $alfresco_object.type, 'document' )}
                                {$alfresco_object.doc_type|mimetype_icon( small, $alfresco_object.doc_type )} <a href={concat( 'alfresco/download/(id)/', $alfresco_object.selfUri|urlencode|urlencode )|ezurl}>{'Download'|i18n( 'alfresco' )}</a>
                            {/if}
                        {else}
                            <div class="attribute-header">
                                <h1>{$node.name|wash()}</h1>
                            </div>

                            <div class="attribute-id">
                                {$node.object.data_map.id.content}
                            </div>
                        {/if}

                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="border-bl">
        <div class="border-br">
            <div class="border-bc">
            </div>
        </div>
    </div>
</div>
