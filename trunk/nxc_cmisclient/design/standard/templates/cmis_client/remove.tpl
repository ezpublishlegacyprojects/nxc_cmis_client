<form action={'cmis_client/remove'|ezurl} method="post" name="ObjectRemove">

{if $error_list}
    <div class="message-warning">
        <h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span> {"CMIS error"|i18n( "cmis" )}</h2>	
        <ul>
            {foreach $error_list as $error}
                <li>{$error|wash}</li>
            {/foreach}
        </ul>
    </div>
{/if}

<div class="context-block">

    {* DESIGN: Header START *}
    <div class="box-header">
        <div class="box-tc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tl">
                        <div class="box-tr">

                            <h2 class="context-title">{'Confirm location removal'|i18n( 'design/admin/node/removeobject' )}</h2>

                            {* DESIGN: Mainline *}<div class="header-mainline"></div>

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

                <div class="block">
                    <p>{'Removing the items will also result in the removal of their sub items.'|i18n( 'design/admin/node/removeobject' )}</p>
                    <p>{'Are you sure you want to remove the items along with their contents?'|i18n( 'design/admin/node/removeobject' )}</p>
                </div>

                <table class="list" cellspacing="0">
                <tr>
                    <th colspan="2">{'Item'|i18n( 'design/admin/node/removeobject' )}</th>
                    <th>{'Type'|i18n( 'design/admin/node/removeobject' )}</th>
                </tr>

                {foreach $remove_list as $remove_item sequence array( bglight, bgdark ) as $sequence}
                <tr class="{$sequence}">
                    {* Object icon. *}
                    <td class="tight">
                        {if eq( $remove_item.base_type, 'folder' )}
                            {'folder'|class_icon( small, $remove_item.summary|wash )}
                        {else}
                            {$remove_item.doc_type|mimetype_icon( small, $remove_item.summary|wash )}
                        {/if}
                    </td>

                    {* Location. *}
                    <td>
                        {$remove_item.title|wash}
                    </td>

                    {* Type. *}
                    <td>
                        {$remove_item.doc_type|wash}
                    </td>
                </tr>
                {/foreach}
                </table>

            </div>
        </div>
    </div>
    {* DESIGN: Content END *}

    <div class="controlbar">

    {* DESIGN: Control bar START *}
    <div class="box-bc">
        <div class="box-ml">
            <div class="box-mr">
                <div class="box-tc">
                    <div class="box-bl">
                        <div class="box-br">

                            <div class="block">
                                <input class="button" type="submit" name="ConfirmButton" value="{'OK'|i18n( 'design/admin/node/removeobject' )}" />
                                <input type="submit" class="button" name="CancelButton" value="{'Cancel'|i18n( 'design/admin/node/removeobject' )}" title="{'Cancel the removal of locations.'|i18n( 'design/admin/node/removeobject' )}" />
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
