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

                        {def $cmis_object = fetch( 'cmis', 'object', hash( 'uri', $object.data_map.uri.content ) )}
                        {if $cmis_object}
                            {include uri='design:cmis_client/view.tpl'
                                     current_object=$cmis_object}

                            {if eq( $cmis_object.base_type, 'document' )}
                                {$cmis_object.doc_type|mimetype_icon( small, $cmis_object.doc_type )} <a href={concat( 'cmis_client/download/', $cmis_object.self_uri )|ezurl}>{'Download'|i18n( 'cmis' )}</a>
                            {/if}
                        {else}
                            <div class="attribute-header">
                                <h1>{$object.name|wash()}</h1>
                            </div>

                            <div class="attribute-uri">
                                {$object.data_map.uri.content}
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
