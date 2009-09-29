<?php /*

[AlfrescoSettings]
# URL to Alfresco
##EndPoint=http://localhost:8080/nuxeo/site/cmis/repository
##EndPoint=http://192.168.1.145:8080/nuxeo/site/cmis/repository
##EndPoint=http://cmis.day.com/cmis/service/repository
##EndPoint=http://cmis.varg.lan:8540/eng/cmis/api/repository


EndPoint=http://localhost:8080/alfresco/service/api/repository

##EndPoint=http://val.lan:8080/alfresco/service/api/cmis

# User name with default rights
# If Alfresco returns 'access denied' the user will be asked to provide additional login/password
DefaultUser=admin
DefaultPassword=admin

#DefaultUser=guest
# Anonymous password
#DefaultPassword=

[eZPublishSettings]
# It will be instantiated when any user tries to add alfresco object to a document via ezoe
ClassIdentifier=alfresco_object
# Node id where instance of ClassIdentifier class will be stored.
# If it is empty media root node will be used
ParentNodeID=

[LocationSettings]
# Which root node to fetch locations from
# Either use a alfresco object ID or a path
RootNode=
#RootNode=http://cmis.varg.lan:8540/eng/cmis/api/node?repositoryId=f3e90596361e31d496d4026eb624c983&objectId=f3e90596361e31d496d4026eb624c983

*/ ?>