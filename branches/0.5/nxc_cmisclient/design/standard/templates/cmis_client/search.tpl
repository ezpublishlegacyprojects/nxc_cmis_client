{if $error_list}
    <div class="message-warning">
        <h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span> {"CMIS error"|i18n( "cmis" )}</h2>	
        <ul>
            {foreach $error_list as $error}
                <li>{$error|wash}</li>
            {/foreach}
        </ul>
    </div>
{else}

<form action={'/cmis_client/search/'|ezurl} method="get">

    <div class="context-block">
        {* DESIGN: Header START *}
        <div class="box-header">
            <div class="box-tc">
                <div class="box-ml">
                    <div class="box-mr">
                        <div class="box-tl">
                            <div class="box-tr">
                                <h1 class="context-title">{'Search the repository'|i18n( 'cmis' )}</h1>
                                {* DESIGN: Mainline *}<div class="header-mainline"></div>
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

                                <div class="context-attributes">

                                    <div class="block">
                                        <input class="halfbox" type="text" name="SearchText" id="Search" value="{$search_text|wash}" />
                                        <input class="button"  name="SearchButton" type="submit" value="{'Search'|i18n( 'design/admin/content/search' )}" />
                                    </div>

                                    {* No matches. *}
                                    {if and( ne( $search_text, '' ), $search_count|not )}
                                        <h2>{'No results were found while searching for <%1>'|i18n( 'design/admin/content/search',, array( $search_text ) )|wash}</h2>
                                        <p>{'Search tips'|i18n( 'design/admin/content/search' )}</p>
                                        <ul>
                                            <li>{'Check spelling of keywords.'|i18n( 'design/admin/content/search' )}</li>
                                            <li>{'Try changing some keywords e.g. &quot;car&quot; instead of &quot;cars&quot;.'|i18n( 'design/admin/content/search' )}</li>
                                            <li>{'Try more general keywords.'|i18n( 'design/admin/content/search' )}</li>
                                            <li>{'Fewer keywords result in more matches. Try reducing keywords until you get a result.'|i18n( 'design/admin/content/search' )}</li>
                                       </ul>
                                    {/if}

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {* DESIGN: Content END *}

    </div>

    {* Search result *}
    {if $search_count}
        <div class="context-block">
            {* DESIGN: Header START *}
            <div class="box-header">
                <div class="box-tc">
                    <div class="box-ml">
                        <div class="box-mr">
                            <div class="box-tl">
                                <div class="box-tr">
                                    <h2 class="context-title">{'Search for <%1> returned %2 matches'|i18n( 'design/admin/content/search',, array( $search_text, $search_count ) )|wash}</h2>
                                    {* DESIGN: Mainline *}<div class="header-subline"></div>
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

                                    {if $search_result}
                                        <table class="list" cellspacing="0">
                                        <tr>
                                            <th>{'Name'|i18n( 'design/admin/content/search' )}</th>
                                            <th>{'Summary'|i18n( 'design/admin/content/search' )}</th>
                                        </tr>

                                        {foreach $search_result as $object sequence array( bglight, bgdark ) as $sequence}
                                        <tr class="{$sequence}">
                                            <td>
                                                {if is_set( $object.icon )}<img src="{$object.icon|wash}"> &nbsp;{/if}
                                                <a href={concat( 'cmis_client/browser/', $object.self_uri)|ezurl}>{$object.title|wash}</a>
                                            </td>
                                            <td>
                                                {$object.summary|wash}
                                            </td>
                                        </tr>
                                        {/foreach}
                                        </table>
                                    {/if}

                                    <div class="context-toolbar">
                                        {include name=Navigator
                                                 uri='design:navigator/google.tpl'
                                                 page_uri='/cmis_client/search'
                                                 page_uri_suffix=concat( '?SearchText=', $search_text|urlencode )
                                                 item_count=$search_count
                                                 view_parameters=$view_parameters
                                                 item_limit=$page_limit}
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {* DESIGN: Content END *}	
        </div>
    {/if}

</form>

{/if}
