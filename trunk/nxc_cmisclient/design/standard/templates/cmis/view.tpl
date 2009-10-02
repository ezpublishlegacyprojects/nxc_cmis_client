{switch match=$current_object.class_identifier}
{case match='folder'}
    {include uri='design:cmis/view/full/folder.tpl'
             current_object=$current_object}
{/case}

{case match='image'}
    {include uri='design:cmis/view/full/image.tpl'
             current_object=$current_object
             width="200"}
{/case}

{case match='text'}
    {include uri='design:cmis/view/full/text.tpl'
             current_object=$current_object}
{/case}

{case}
    {include uri='design:cmis/view/full/file.tpl'
             current_object=$current_object}
{/case}

{/switch}
