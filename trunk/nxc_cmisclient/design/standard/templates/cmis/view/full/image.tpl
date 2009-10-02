{default $width = ''
         $height = ''}
<div class="content-view-full">
    <div class="class-image">

        <h1>{$current_object.title|wash}</h1>

        <div class="attribute-long">
            <p>{$current_object.summary|wash( xhtml )}</p>
        </div>

        <div class="attribute-image">
            <p><a href={concat( 'cmis/download/', $current_object.self_uri )|ezurl}><img src={concat( 'cmis/content/', $current_object.self_uri )|ezurl} width="{$width}" height="{$height}" alt="{$current_object.title|wash( xhtml )}" title="{$current_object.title|wash( xhtml )}" /></a></p>
        </div>

    </div>
</div>

