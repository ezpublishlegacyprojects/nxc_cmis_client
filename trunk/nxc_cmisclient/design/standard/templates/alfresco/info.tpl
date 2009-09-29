{* Details window. *}
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

<div class="context-block">

    {* DESIGN: Header START *}
    <div class="box-header">
        <div class="box-tc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tl">
                        <div class="box-tr">
                            <h1 class="context-title">{'CMIS Information'|i18n( 'alfresco' )}</h1>
                            {* DESIGN: Mainline *}
                            <div class="header-mainline"></div>
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
                                {* Name *}
                                <th>{'Name'|i18n( 'design/admin/node/view/full' )}</th>

                                {* Properties *}
                                <th class="name">{'Properties'|i18n( 'alfresco' )}</th>

                            </tr>

                            {foreach $repository_info as $name => $value sequence array( bglight, bgdark ) as $sequence}
                            <tr class="{$sequence}">

                                {* Name *}
                                <td>{$name|wash}</td>

                                {* Properties *}
                                <td>{$value|wash}</td>
                            </tr>
                            {/foreach}
                            </table>

                            {* DESIGN: Content END *}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{/if}
