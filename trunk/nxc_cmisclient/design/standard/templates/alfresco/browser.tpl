{default $browser_view = 'alfresco/browser'}

{if $error_list}
    <div class="message-warning">
        {if ezhttp('knowledgeTreeDMS', 'session')}
        	<h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span> {"KnowledgeTree error"|i18n( "alfresco" )}</h2>
        {else}
        	<h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span> {"Alfresco error"|i18n( "alfresco" )}</h2>
        {/if}
        <ul>
            {foreach $error_list as $error}
                <li>{$error|wash}</li>
            {/foreach}
        </ul>
    </div>
{else}
<div class="content-navigation">

    {* Content window. *}
    <div class="context-block">

        {* DESIGN: Header START *}
        <div class="box-header">
            <div class="box-tc">
                <div class="box-ml">
                    <div class="box-mr">
                        <div class="box-tl">
                            <div class="box-tr">
                                <h1 class="context-title">
                                    {if eq( $current_object.doc_type, 'Space' )}
                                        {'folder'|class_icon( normal, $current_object.summary|wash )}
                                    {else}
                                        {$current_object.doc_type|mimetype_icon( normal, $current_object.summary|wash )}
                                    {/if}
                                    &nbsp;{$current_object.title|wash}&nbsp;[{$current_object.doc_type|wash}]
                                </h1>

                                {* DESIGN: Mainline *}<div class="header-mainline"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {* DESIGN: Header END *}

        <form method="post" action={'alfresco/action'|ezurl}>
        {*<input type="hidden" name="CurrentObjectID" value="{$current_object.id}" />*}
        <input type="hidden" name="CurrentObjectID" value="{$current_object.selfUri}" />
        <input type="hidden" name="CurrentObjectURI" value="{concat( $browser_view, '/(id)/',$current_object.selfUri|urlencode)}" />
        {*if $current_object.parentUri}
            <input type="hidden" name="ParentObjectURI" value="{concat( $browser_view, '/(id)/', $current_object.parentUri|urlencode )}" />
        {/if*}
        {if $parent_self}
            <input type="hidden" name="ParentObjectURI" value="{concat( $browser_view, '/(id)/',$parent_self|urlencode)}" />
        {/if}

        <div class="box-ml">
            <div class="box-mr">

                <div class="context-information">
                    <p class="modified">{'Last modified'|i18n( 'design/admin/node/view/full' )}:&nbsp;{$current_object.updated|wash}</p>
                    <div class="break"></div>
                </div>

                {* Content preview in content window. *}

                <div class="mainobject-window" title="{$current_object.title|wash}: {$current_object.summary|wash}">
                    <div class="fixedsize">{* Fix for overflow bug in Opera *}
                        <div class="holdinplace">{* Fix for some width bugs in IE *}

                            {include uri='design:alfresco/view.tpl'
                                     current_object=$current_object}

                        </div>
                    </div>
                    <div class="break"></div>{* Terminate overflow bug fix *}
                </div>

            </div>
        </div>

        {* Buttonbar for content window. *}
        <div class="controlbar">

        {* DESIGN: Control bar START *}
        <div class="box-bc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tc">
                        <div class="box-bl">
                            <div class="box-br">

                                <div class="block">

                                    <div class="left">
                                        {if not(ezhttp('knowledgeTreeDMS', 'session'))}
                                        	{* Edit button. *}
                                        	<input class="button" type="submit" name="ActionEdit" value="{'Edit'|i18n( 'design/admin/node/view/full' )}" title="{'Edit the contents of this item.'|i18n( 'design/admin/node/view/full' )}" />
                                        {/if}

                                        {* Move button. *}
                                        {*<input class="button" type="submit" name="MoveNodeButton" value="{'Move'|i18n( 'design/admin/node/view/full' )}" title="{'Move this item to another location.'|i18n( 'design/admin/node/view/full' )}" />*}

                                        {* Remove button. *}
                                        {if $current_object.parentUri}
                                            <input class="button" type="submit" name="ActionRemove" value="{'Remove'|i18n( 'design/admin/node/view/full' )}" title="{'Remove this item.'|i18n( 'design/admin/node/view/full' )}" />
                                        {else}
                                            <input class="button-disabled" type="submit" name="ActionRemove" value="{'Remove'|i18n( 'design/admin/node/view/full' )}" title="{'You do not have permission to remove any of the items from the list above.'|i18n( 'design/admin/node/view/full' )}" disabled="disabled" />
                                        {/if}
                                    </div>

                                    {if eq( $current_object.type, 'document' )}
                                        <div class="right">
                                            {$current_object.doc_type|mimetype_icon( small, $current_object.doc_type )} <a href={concat( 'alfresco/download/(id)/', $current_object.selfUri|urlencode|urlencode )|ezurl}>{'Download'|i18n( 'alfresco' )}</a>
                                        </div>
                                    {/if}
                                    <div class="break"></div>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {* DESIGN: Control bar END *}
        </form>
    </div>
    {* Content window. END *}

</div>

{* Details window. *}
<div class="context-block">

    {* DESIGN: Header START *}
    <div class="box-header">
        <div class="box-tc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tl">
                        <div class="box-tr">
                            <h2 class="context-title">{'Details'|i18n( 'design/admin/node/view/full' )}</h2>

                            <div class="header-subline"></div>
                            {* DESIGN: Header END *}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {* DESIGN: Content START *}
    <div class="box-bc">
        <div class="box-ml">
            <div class="box-mr">
                <div class="box-bl">
                    <div class="box-br">
                        <div class="box-content">

                            <table class="list" cellspacing="0">
                            <tr>
                                <th>Id</th>
                                {if is_set( $current_object.size )}
                                    <th>{'Size'|i18n( 'alfresco' )}</th>
                                {/if}
                                <th>{'Author'|i18n( 'design/admin/node/view/full' )}</th>
                            </tr>
                            <tr class="bglight">
                                <td>{$current_object.id|wash}</td>
                                {if is_set( $current_object.size )}
                                    <td>{$current_object.size|wash}</th>
                                {/if}

                                <td>{$current_object.author|wash}</td>
                            </tr>
                            </table>

                            {* DESIGN: Content END *}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{if eq( $current_object.type, 'folder' )}
{let select_to_remove = ''}
{if ezhttp('knowledgeTreeDMS', 'session')}
	{set select_to_remove = '(select)/true/'}
{/if}
<form name="children" method="post" action={concat('alfresco/action/', $select_to_remove)|ezurl}>
{/let}

<input type="hidden" name="ParentObjectURI" value="{concat( $browser_view, '/(id)/', $current_object.selfUri|urlencode)}" />

<div class="context-block">
    {* DESIGN: Header START *}
    <div class="box-header">
        <div class="box-tc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tl">
                        <div class="box-tr">
                            <h2 class="context-title">
                            {if $current_object.parentUri}<a href={concat( $browser_view, '/(id)/', $current_object.parentUri|urlencode|urlencode )|ezurl} title="{'Up one level.'|i18n( 'design/admin/node/view/full'  )}"><img src={'back-button-16x16.gif'|ezimage} alt="{'Up one level.'|i18n( 'design/admin/node/view/full' )}" title="{'Up one level.'|i18n( 'design/admin/node/view/full' )}" /></a>&nbsp;{/if}
                            {'Sub items [%children_count]'|i18n( 'design/admin/node/view/full',, hash( '%children_count', $children_count ) )}</h2>

                            {* DESIGN: Subline *}<div class="header-subline"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {* DESIGN: Header END *}

    {* DESIGN: Content START *}
    <div class="box-ml">
        <div class="box-mr">
            <div class="box-content">
                <div class="content-navigation-childlist">

                    {if $children_count}

                        {* Items per page and view mode selector. *}
                        <div class="context-toolbar">
                            <div class="block">
                                <div class="left">
                                    <p>
                                        {switch match=$limit}
                                        {case match=25}
                                            <a href={'/user/preferences/set/alfresco_browse_children_limit/1'|ezurl} title="{'Show 10 items per page.'|i18n( 'design/admin/node/view/full' )}">10</a>
                                            <span class="current">25</span>
                                            <a href={'/user/preferences/set/alfresco_browse_children_limit/3'|ezurl} title="{'Show 50 items per page.'|i18n( 'design/admin/node/view/full' )}">50</a>
                                        {/case}

                                        {case match=50}
                                            <a href={'/user/preferences/set/alfresco_browse_children_limit/1'|ezurl} title="{'Show 10 items per page.'|i18n( 'design/admin/node/view/full' )}">10</a>
                                            <a href={'/user/preferences/set/alfresco_browse_children_limit/2'|ezurl} title="{'Show 25 items per page.'|i18n( 'design/admin/node/view/full' )}">25</a>
                                            <span class="current">50</span>
                                        {/case}

                                        {case}
                                            <span class="current">10</span>
                                            <a href={'/user/preferences/set/alfresco_browse_children_limit/2'|ezurl} title="{'Show 25 items per page.'|i18n( 'design/admin/node/view/full' )}">25</a>
                                            <a href={'/user/preferences/set/alfresco_browse_children_limit/3'|ezurl} title="{'Show 50 items per page.'|i18n( 'design/admin/node/view/full' )}">50</a>
                                        {/case}
                                        {/switch}
                                    </p>
                                </div>
                                <div class="break"></div>
                            </div>
                        </div>

                        <table class="list" cellspacing="0">
                        <tr>
                            {* Remove column *}
                            <th class="remove"><img src={'toggle-button-16x16.gif'|ezimage} alt="{'Invert selection.'|i18n( 'design/admin/node/view/full' )}" title="{'Invert selection.'|i18n( 'design/admin/node/view/full' )}" onclick="ezjs_toggleCheckboxes( document.children, 'DeleteIDArrayAlfresco[]' ); return false;" /></th>

                            {* Name column *}
                            <th class="name">{'Name'|i18n( 'design/admin/node/view/full' )}</th>

                            {* Size column *}
                            <th>{'Size'|i18n( 'alfresco' )}</th>

                            {* Type column *}
                            <th>{'Type'|i18n( 'alfresco' )}</th>

                            {* Author column *}
                            <th>{'Author'|i18n( 'alfresco' )}</th>

                            {* Modified column *}
                            <th>{'Modified'|i18n( 'alfresco' )}</th>
                        </tr>

                        {foreach $children as $node sequence array( bglight, bgdark ) as $sequence}
                        <tr class="{$sequence}">

                            {* Remove checkbox *}
                            <td>
                                <input type="checkbox" name="DeleteIDArrayAlfresco[]" value="{$node.selfUri}" title="{'Use these checkboxes to select items for removal. Click the "Remove selected" button to  remove the selected items.'|i18n( 'design/admin/node/view/full' )|wash()}" />
                            </td>

                            {* Name *}
                            <td>
                                {if eq( $node.doc_type, 'Space' )}
                                    {'folder'|class_icon( small, $node.summary|wash )}
                                {else}
                                    {$node.doc_type|mimetype_icon( small, $node.summary|wash )}
                                {/if}
                                <a href={concat( $browser_view, '/(id)/', $node.selfUri|urlencode|urlencode, '/(parent_id)/', $current_object.selfUri|urlencode|urlencode, '/(parent_name)/', $current_object.title|wash)|ezurl} title="{$node.summary}">{$node.title|wash}</a>
                            </td>

                            {* Size column *}
                            <td class="class">{if is_set( $node.size )}{$node.size}{/if}</td>

                            {* Type column *}
                            <td class="class">{$node.doc_type}</td>

                            {* Author column *}
                            <td>{$node.author}</td>

                            {* Modified column *}
                            <td class="modified">{$node.updated}</td>
                        </tr>
                        {/foreach}
                        </table>
                    {else}
                        <div class="block">
                            <p>{'The current item does not contain any sub items.'|i18n( 'design/admin/node/view/full' )}</p>
                        </div>
                    {/if}
                </div>

                <div class="context-toolbar">
                    {include name=navigator
                             uri='design:navigator/google.tpl'
                             page_uri=concat( $browser_view, '/(id)/', $current_object.selfUri|urlencode|urlencode )
                             item_count=$children_count
                             view_parameters=$view_parameters
                             item_limit=$limit}
                </div>

            </div>
        </div>
    </div>

    {* DESIGN: Content END *}

    {* Buttons *}
    {* DESIGN: Control bar START *}

    <div class="controlbar">
        <div class="box-bc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tc">
                        <div class="box-bl">
                            <div class="box-br">
                                {* Remove button *}
                                <div class="block">
                                    <div class="left">
                                        <input {if $children_count}class="button"{else}class="button-disabled" disabled="disabled"{/if} type="submit" name="RemoveButton" value="{'Remove selected'|i18n( 'design/admin/node/view/full' )}" title="{'Remove the selected items from the list above.'|i18n( 'design/admin/node/view/full' )}" />
                                    </div>
                                    <div class="break"></div>
                                </div>

                                {* The "Create new here" thing: *}

                                <div class="block">
                                    <div class="left">

                                        <select id="ClassID" name="ClassID" title="{'Use this menu to select the type of item you want to create then click the "Create here" button. The item will be created in the current location.'|i18n( 'design/admin/node/view/full' )|wash()}">
                                        {foreach $current_object.can_create_classes as $class}

                                            <option value="{$class|wash()}">{$class|wash()}</option>

                                        {/foreach}
                                        </select>

                                        <input class="button" type="submit" name="NewButton" value="{'Create here'|i18n( 'design/admin/node/view/full' )}" title="{'Create a new item in the current location. Use the menu on the left to select the type of  item.'|i18n( 'design/admin/node/view/full' )}" />
                                        <input type="hidden" name="ParentObjectID" value="{$current_object.childrenUri}" />
                                    </div>
                                </div>

                            &nbsp;
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {* DESIGN: Control bar END *}
</div>
</form>

{else}

<div class="context-block">

    {* DESIGN: Header START *}
    <div class="box-header">
        <div class="box-tc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tl">
                        <div class="box-tr">
                            <h2 class="context-title">
                            {if $current_object.parentUri}<a href={concat( $browser_view, '/(id)/', $current_object.parentUri|urlencode|urlencode)|ezurl} title="{'Up one level.'|i18n( 'design/admin/node/view/full'  )}"><img src={'back-button-16x16.gif'|ezimage} alt="{'Up one level.'|i18n( 'design/admin/node/view/full' )}" title="{'Up one level.'|i18n( 'design/admin/node/view/full' )}" /></a>&nbsp;{/if}
                            {'Sub items [%children_count]'|i18n( 'design/admin/node/view/full',, hash( '%children_count', $children_count ) )}</h2>

                            {* DESIGN: Subline *}<div class="header-subline"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {* DESIGN: Header END *}

    {* DESIGN: Content START *}
    <div class="box-bc">
        <div class="box-ml">
            <div class="box-mr">
                <div class="box-bl">
                    <div class="box-br">
                        <div class="box-content">

                            <div class="block">
                                <p>{'This class is not configured to contain any sub items.'|i18n( 'alfresco' )|wash}</p>
                            </div>

                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>
     {* DESIGN: Content END *}

</div>
{/if} {* eq( $current_object.type, 'folder' ) *}
{/if}
{/default}
