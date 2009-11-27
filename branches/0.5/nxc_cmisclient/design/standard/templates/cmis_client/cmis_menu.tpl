<div class="box-header">
    <div class="box-tc">
        <div class="box-ml">
            <div class="box-mr">
                <div class="box-tl">
                    <div class="box-tr">
                        {def $vendor = fetch( 'cmis_client', 'vendor_name' )}

                        <h4>{if $vendor}{$vendor}{else}CMIS{/if}</h4>

                        {undef $vendor}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="box-bc">
    <div class="box-ml">
        <div class="box-mr">
            <div class="box-bl">
                <div class="box-br">
                    <div class="box-content">

                        <ul>
                            <li><div><a href={'/cmis_client/browser'|ezurl}>{'Repository'|i18n( 'cmis' )}</a></div></li>
                            <li><div><a href={'/cmis_client/search'|ezurl}>{'Search'|i18n( 'cmis' )}</a></div></li>
                            <li><div><a href={'/cmis_client/info'|ezurl}>{'CMIS Information'|i18n( 'cmis' )}</a></div></li>
                            {def $logged_name = fetch( 'cmis_client', 'logged_username' )}
                            {if ne( $logged_name, '' )}
                                <li><div><a href={'/cmis_client/logout'|ezurl}>{'Logout'|i18n( 'cmis' )} ({$logged_name|wash})</a></div></li>
                            {else}
                                <li><div><a href={'/cmis_client/login'|ezurl}>{'Login'|i18n( 'cmis' )}</a></div></li>
                            {/if}
                        </ul>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
