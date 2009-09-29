{if ezhttp('knowledgeTreeDMS', 'session')}
	{let name=Path use_urlalias=ezini('URLTranslator','Translation')|eq('enabled')}
	<p class="path">&gt;
		{section loop=$module_result.path}
	
		{if ezhttp('parent_path_last', 'session', 'hasVariable')}
			{def $parent_path_last = concat('/(id)/', ezhttp('parent_path_last', 'session')|urlencode)}
			{def $parent_name_last = ezhttp('parent_name_last', 'session')}
		{else}
			{def $parent_path_last = ''}
			{def $parent_name_last = 'Repository'|i18n( 'alfresco' )}
		{/if}
			{section show=$:item.url}
				{section show=ne($ui_context,'edit')}
					<a class="path" href={concat('alfresco/browser', $parent_path_last, '/(back)/1')|ezurl}>
						{$parent_name_last|shorten( 18 )|wash}
					</a>
				{section-else}
					<span class="disabled">{$:item.text|shorten( 18 )|wash}</span>
				{/section}
			{section-else}
				{$:item.text|wash}
			{/section}
	
			{delimiter}
				<span class="slash">/</span>
			{/delimiter}
		{/section}
		&nbsp;
	</p>
	{/let}
{else}
	{let name=Path
	     use_urlalias=ezini('URLTranslator','Translation')|eq('enabled')}
	    <p class="path">&gt;
	    {section loop=$module_result.path}
	        {section show=$:item.url}
	            {section show=ne($ui_context,'edit')}
	            <a class="path" href={cond( and( $:use_urlalias, is_set( $:item.url_alias ) ), $:item.url_alias,
	                                        $:item.url )|ezurl}>{$:item.text|shorten( 18 )|wash}</a>
	            {section-else}
	            <span class="disabled">{$:item.text|shorten( 18 )|wash}</span>
	            {/section}
	        {section-else}
	            {$:item.text|wash}
	        {/section}
	
	        {delimiter}
	            <span class="slash">/</span>
	        {/delimiter}
	    {/section}
	    &nbsp;</p>
	{/let}
{/if}