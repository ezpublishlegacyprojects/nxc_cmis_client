{default $width = ''
         $height = ''}
<div class="content-view-full">
    <div class="class-image">

        <h1>{$current_object.title|wash}</h1>

        <div class="attribute-long">
            <p>{$current_object.summary|wash( xhtml )}</p>
        </div>

        <div class="attribute-image">
            <p><a href={concat( 'alfresco/download/(id)/', $current_object.selfUri|urlencode|urlencode )|ezurl}><img src={concat( 'alfresco/content/(id)/', $current_object.id|urlencode|urlencode )|ezurl} width="{$width}" height="{$height}" alt="{$current_object.title|wash( xhtml )}" title="{$current_object.title|wash( xhtml )}" /></a></p>
        </div>

    </div>
</div>

