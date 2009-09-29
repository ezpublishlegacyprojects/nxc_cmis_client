{switch match=$current_object.class_identifier}
{case match='folder'}
    {include uri='design:alfresco/view/full/space.tpl'
             current_object=$current_object}
{/case}

{case match='image'}
    {include uri='design:alfresco/view/full/image.tpl'
             current_object=$current_object
             width="200"}
{/case}

{case match='text'}
    {include uri='design:alfresco/view/full/text.tpl'
             current_object=$current_object}
{/case}

{case}
    {include uri='design:alfresco/view/full/file.tpl'
             current_object=$current_object}
{/case}

{/switch}
