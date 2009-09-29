<div class="box-header">
    <div class="box-tc">
        <div class="box-ml">
            <div class="box-mr">
                <div class="box-tl">
                    <div class="box-tr">
						{if ezhttp('knowledgeTreeDMS', 'session')}
							<h4>KnowledgeTree</h4>
						{else}
							<h4>Alfresco</h4>
						{/if}
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
                            <li><div><a href={'/alfresco/browser'|ezurl}>{'Repository'|i18n( 'alfresco' )}</a></div></li>
                            <li><div><a href={'/alfresco/opensearch'|ezurl}>{'Search'|i18n( 'alfresco' )}</a></div></li>
                            <li><div><a href={'/alfresco/info'|ezurl}>{'CMIS Information'|i18n( 'alfresco' )}</a></div></li>
                            {def $logged_name = fetch( 'alfresco', 'logged_username' )}
                            {if ne( $logged_name, '' )}
                                <li><div><a href={'/alfresco/logout'|ezurl}>{'Logout'|i18n( 'alfresco' )} ({$logged_name|wash})</a></div></li>
                            {else}
                                <li><div><a href={'/alfresco/login'|ezurl}>{'Login'|i18n( 'alfresco' )}</a></div></li>
                            {/if}
                        </ul>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
