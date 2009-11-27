{switch match=$current_object.class_identifier}
{case match='folder'}
    {include uri='design:cmis_client/view/full/folder.tpl'
             current_object=$current_object}
{/case}

{case match='image'}
    {include uri='design:cmis_client/view/full/image.tpl'
             current_object=$current_object
             width="200"}
{/case}

{case match='content'}
    {include uri='design:cmis_client/view/full/text.tpl'
             current_object=$current_object}
{/case}

{case}
    {include uri='design:cmis_client/view/full/file.tpl'
             current_object=$current_object}
{/case}

{/switch}
