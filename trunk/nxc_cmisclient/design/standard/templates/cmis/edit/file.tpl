{default $name = ''
         $desc = ''
         $self_uri = ''}
{if $object}
    {set $name = $object.title
         $desc = $object.summary
         $self_uri = $object.self_uri}
{/if}

<form action={concat( 'cmis/edit/', $self_uri )|ezurl} method="post" name="ObjectCreate" enctype="multipart/form-data">

{literal}
<script type="text/javascript">

function checkButtonState()
{
{/literal}
{if $object}
    {literal}
        if ( document.getElementById( "Name" ).value.length == 0 )
    {/literal}
{else}
    {literal}
    if ( ( document.getElementById( "Name" ).value.length == 0 ) || ( document.getElementById( "File" ).value.length == 0 ) )
    {/literal}
{/if}
{literal}
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

                            <h1 class="context-title">{'file'|class_icon( normal, 'Upload content'|i18n( 'cmis' ) )}&nbsp;{'Upload content'|i18n( 'cmis' )}</h1>

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
                    {* Name *}
                    <div class="block">
                        <label>{'Name'|i18n( 'cmis' )} <span class="required">({'required'|i18n( 'design/admin/content/edit_attribute' )})</span>:</label>
                        <input id="Name" class="box" type="text" size="70" name="AttributeName" value="{$name}" onkeyup="checkButtonState();" />
                    </div>

                    {* Description *}
                    <div class="block">
                        <label>{'Description'|i18n( 'cmis' )}:</label>
                        <input id="Description" class="box" type="text" size="70" name="AttributeDescription" value="{$desc}"/>
                    </div>

                    {* Current file. *}
                    <div class="block">
                        <label>{'Current file'|i18n( 'design/standard/content/datatype' )}:</label>
                        {if $object}
                            {$object.doc_type|mimetype_icon( small, $object.doc_type )} <a href={concat( 'cmis/download/', $object.self_uri )|ezurl}>{'Download'|i18n( 'cmis' )}</a>
                        {else}
                            <p>{'There is no file.'|i18n( 'design/standard/content/datatype' )}</p>
                        {/if}
                    </div>

                    {* New file *}
                    <div class="block">
                        <label>{'New file for upload'|i18n( 'design/standard/content/datatype' )}{if not( $object )} <span class="required">({'required'|i18n( 'design/admin/content/edit_attribute' )})</span>{/if}:</label>
                        <input id="File" class="box" name="AttributeFile" type="file" onchange="checkButtonState();"/>
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

</form>
{/default}
