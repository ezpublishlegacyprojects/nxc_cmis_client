<div class="content-view-full">
    <div class="class-article">

        <h1>{$current_object.title|wash}</h1>

        <div class="attribute-long">
            <p>{$current_object.summary|wash( xhtml )}</p>
        </div>

        <div class="attribute-long">
            <p>{$current_object.content|wash( xhtml )|nl2br}</p>
       </div>

    </div>
</div>

