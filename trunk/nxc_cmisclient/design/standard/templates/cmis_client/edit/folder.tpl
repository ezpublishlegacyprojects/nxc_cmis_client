{default $name = ''
         $desc = ''
         $self_uri = ''}
{if $object}
    {set $name = $object.title
         $desc = $object.summary
         $self_uri = $object.self_uri}
{/if}

<form action={concat( 'cmis_client/edit/', $self_uri )|ezurl} method="post" name="ObjectCreate">

{literal}
<script type="text/javascript">

function checkButtonState()
{
    if ( document.getElementById( "Name" ).value.length == 0 )
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

                            <h1 class="context-title">{if eq( $self_uri, '' )}{'folder'|class_icon( normal, 'Create folder'|i18n( 'cmis' ) )}&nbsp;{'Create folder'|i18n( 'cmis' )}{else}{'folder'|class_icon( normal, 'Edit folder'|i18n( 'cmis' ) )}&nbsp;{'Edit folder'|i18n( 'cmis' )} - {$name}{/if}</h1>

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
                        <label>{'Description'|i18n( 'cmis' )}:</label>
                        <input id="Description" class="box" type="text" size="70" name="AttributeDescription" value="{$desc}"/>
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
                                    <input id="ConfirmButton" class="button" type="submit" name="ConfirmButton" value="{'OK'|i18n( 'design/admin/node/removeobject' )}" {if and( not( $object ), not( $error_list ) )}disabled="disabled"{/if}/>
                                    <input type="submit" class="button" name="CancelButton" value="{'Cancel'|i18n( 'design/admin/node/removeobject' )}" title="{'Cancel the removal of locations.'|i18n( 'design/admin/node/removeobject' )}" />
                                    <input type="hidden" name="RedirectURI" value="{$redirect_uri}" />
                                    {if $object}
                                        <input type="hidden" name="SelfUri" value="{$object.self_uri}" />
                                    {/if}
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
