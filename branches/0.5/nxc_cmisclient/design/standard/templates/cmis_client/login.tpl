{* Warnings *}
{if $error_list}
    <div class="message-warning">
        <h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span> {'The system could not log you in.'|i18n( 'design/admin/user/login' )}</h2>
        <ul>
        {if is_set( $error_list['bad_login'] )}
            <li>{'Make sure that the username and password is correct.'|i18n( 'design/admin/user/login' )}</li>
            <li>{'All letters must be entered in the correct case.'|i18n( 'design/admin/user/login' )}</li>
            <li>{'Please try again or contact the site administrator.'|i18n( 'design/admin/user/login' )}</li>
        {else}
            {foreach $error_list as $error}
                <li>{$error|wash}</li>
            {/foreach}	
        {/if}
        </ul>
    </div>
{/if}

<form name="loginform" method="post" action={'/cmis_client/login/'|ezurl}>

{* Login window *}
<div class="context-block">

    {* DESIGN: Header START *}
    <div class="box-header">
        <div class="box-tc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tl">
                        <div class="box-tr">
                            {def $vendor = fetch( 'cmis_client', 'vendor_name' )
                                 $vendor_name = $vendor|choose( 'CMIS repository', $vendor )}
                            <h1 class="context-title">{'Log in to %vendor%'|i18n( 'cmis',, hash( '%vendor%', $vendor_name ) )}</h1>

                            {undef $vendor $vendor_name}

                            {* DESIGN: Mainline *}
                            <div class="header-mainline"></div>

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
                        <p>{'Please enter a valid username/password combination then click "Log in".'|i18n( 'design/admin/user/login' )}</p>
                    </div>

                    <div class="block">
                         <label for="id1">{'Username'|i18n( 'design/admin/user/login' )}:</label>
                        <input class="halfbox" type="text" size="10" name="Login" id="id1" value="{$login|wash}" tabindex="1" title="{'Enter a valid username in this field.'|i18n( 'design/admin/user/login' )}" />
                    </div>

                    <div class="block">
                        <label for="id2">{'Password'|i18n( 'design/admin/user/login' )}:</label>
                        <input class="halfbox" type="password" size="10" name="Password" id="id2" value="" tabindex="1" title="{'Enter a valid password in this field.'|i18n( 'design/admin/user/login' )}" />
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
                                    <input class="button" type="submit" name="LoginButton" value="{'Log in'|i18n( 'design/admin/user/login', 'Login button' )}" tabindex="1" title="{'Click here to log in using the username/password combination entered in the fields above.'|i18n( 'design/admin/user/login' )}" />
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

<input type="hidden" name="RedirectURI" value="{$redirect_uri|wash}" />

</form>

{literal}
<script language="JavaScript" type="text/javascript">
<!--
    window.onload=function()
    {
        document.getElementById('id1').focus();
    }
-->
</script>
{/literal}
