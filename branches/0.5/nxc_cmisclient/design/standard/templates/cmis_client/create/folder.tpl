{default $name = ''
         $desc = ''}

<form action={'cmis_client/create'|ezurl} method="post" name="ObjectCreate">

{literal}
<script type="text/javascript">

function checkButtonState()
{
    if (document.getElementById( "Name" ).value.length == 0 )
    {
        document.getElementById( "ConfirmButton" ).disabled = true;
    }
    else
    {
         document.getElementById( "ConfirmButton" ).disabled = false;
    }
}
</script>
{/literal}

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

                            <h1 class="context-title">{'folder'|class_icon( normal, 'Create space'|i18n( 'cmis' ) )}&nbsp;{'Create space'|i18n( 'cmis' )}</h1>

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

                <div class="context-attributes">
                    <div class="block">
                        <label>{'Name'|i18n( 'cmis' )} <span class="required">({'required'|i18n( 'design/admin/content/edit_attribute' )})</span>:</label>
                        <input id="Name" class="box" type="text" size="70" name="AttributeName" value="{$name}" onkeyup="checkButtonState();" />
                    </div>

                    <div class="block">
                        <label>{'Description'|i18n( 'cmis_client/content' )}:</label>
                        <input id="Description" class="box" type="text" size="70" name="AttributeDescription" value="{$desc}" />
                    </div>
                </div>

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
                                    <input id="ConfirmButton" class="button" type="submit" name="ConfirmButton" value="{'OK'|i18n( 'design/admin/node/removeobject' )}" {if not( $error_list )}disabled="disabled"{/if}/>
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
</div>

</form>
{/default}
