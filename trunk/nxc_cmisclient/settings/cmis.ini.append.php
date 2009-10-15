<?php /*

[CMISSettings]
# URL to end point
EndPoint=http://localhost:8080/alfresco/service/api/repository

# User name with default rights
# If CMIS server returns 'access denied' the user will be asked to provide additional login/password
DefaultUser=admin
# Anonymous password
DefaultPassword=admin

[eZPublishSettings]
# It will be instantiated when any user tries to add cmis object to a document via ezoe
ClassIdentifier=cmis_object
# Content node id where instance of ClassIdentifier class will be stored.
# If it is empty media root node will be used
ParentNodeID=

[LocationSettings]
# Uri of root node in CMIS repository where newly uploaded files via ezoe will be stored.
# NOTE: uri must not contain host and port.
# If uri is 'http://localhost:8080/api/node/2' so you should use 'api/node/2' only
# If empty, root folder id will be used.
RootNode=

*/ ?>