<script type="text/javascript" src={"javascript/ezoe/cmis.js"|ezdesign}></script>
<script type="text/javascript">

{literal}
eZOEPopupUtils.cmisBrowse = function( uri, offset )
{
    // browse for a specific node id and a offset on the child elements
    eZOEPopupUtils.ajax.load( "{/literal}{"cmis/expand/"|ezurl( no )}{literal}" + '/(uri)/' + ( uri || '' ) + '/(offset)/' + (offset || 0), '', eZOEPopupUtils.cmisBrowseCallBack );
    ez.$('browse_progress' ).show();
};

eZOEPopupUtils.selectByCMISEmbedURI = function( uri )
{
    var s = tinyMCEPopup.editor.settings;
    window.location ={/literal}'{'cmis/relations/'|ezurl( no )}{literal}/' + s.ez_contentobject_id + '/' + s.ez_contentobject_version + '/(uri)/' + uri;
};

{/literal}

</script>
{default embed_mode         = true()
         class_filter_array = array()
         root_nodes         = array(
            fetch('content', 'node', hash( 'node_id', ezini( 'NodeSettings', 'RootNode', 'content.ini' ) )),
            fetch('content', 'node', hash( 'node_id', ezini( 'NodeSettings', 'MediaRootNode', 'content.ini' ) )),
            fetch('content', 'node', hash( 'node_id', ezini( 'NodeSettings', 'UserRootNode', 'content.ini' ) )) )
         has_access         = fetch( 'user', 'has_access_to', hash( 'module', 'ezoe',
                                                                    'function', 'browse' ) )}
    <div class="panel" style="display: none; position: relative;">
        <div style="background-color: #eee; text-align: center">
        {if $embed_mode}
            <a id="embed_browse_go_back_link" title="Go back" href="JavaScript:void(0);" style="float: right;"><img width="16" height="16" border="0" src={"tango/emblem-unreadable.png"|ezimage} /></a>
        {/if}

    {if $has_access}

        {foreach $root_nodes as $n}
            <a href="JavaScript:eZOEPopupUtils.browse( {$n.node_id} )" style="font-weight: bold">{$n.name|shorten( 35 )}</a> &nbsp;
        {/foreach}
        {if and( is_set( $#object ), $#object.published )}
            <a href="JavaScript:eZOEPopupUtils.browse( {$#object.main_node_id} )" style="font-weight: bold">{$#object.name|shorten( 35 )} ({'this'|i18n('design/standard/ezoe')})</a>
        {/if}

        {if not( $embed_mode )}
            &nbsp;
            {def $vendor = fetch( 'cmis', 'vendor_name' )}
            <a href="JavaScript:eZOEPopupUtils.cmisBrowse()" style="font-weight: bold">{if $vendor}{$vendor}{else}CMIS{/if}</a>
            {undef $vendor}
        {/if}

        </div>
        <div id="browse_progress" class="progress-indicator" style="display: none;"></div>
        <table class="node_datalist" id="browse_box_prev">
            <thead>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
            </tfoot>
        </table>
    {else}
        </div>
        <p>{"Your current user does not have the proper privileges to access this page."|i18n('design/standard/error/kernel')}</p>
    {/if}
    </div>
{/default}
<script type="text/javascript">
<!--

eZOEPopupUtils.browse( {ezini( 'NodeSettings', 'RootNode', 'content.ini' )} );
// UserRootNode MediaRootNode

//-->
</script>