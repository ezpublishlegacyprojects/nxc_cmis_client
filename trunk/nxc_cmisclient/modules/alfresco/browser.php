<?php
/**
 * Created on: <18-Apr-2009 10:00:00 vd>
 *
 * COPYRIGHT NOTICE: Copyright (C) 2001-2009 Nexus AS
 * SOFTWARE LICENSE: GNU General Public License v2.0
 * NOTICE: >
 *   This program is free software; you can redistribute it and/or
 *   modify it under the terms of version 2.0  of the GNU General
 *   Public License as published by the Free Software Foundation.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of version 2.0 of the GNU General
 *   Public License along with this program; if not, write to the Free
 *   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *   MA 02110-1301, USA.
 */

/**
 * Object repository browser
 */

include_once( 'kernel/common/template.php' );
//include_once( 'extension/nxc_alfresco/classes/nxcalfresco.php' );
//include_once( 'extension/nxc_alfresco/classes/nxcalfrescoobjecthandler.php' );

$ini = eZINI::instance("alfresco.ini");
$endPoint = $ini->variable( 'AlfrescoSettings', 'EndPoint' );
if (preg_match('/servicedocument/', $endPoint)) {
	$_SESSION['knowledgeTreeDMS'] = true;
}

$Module = $Params['Module'];
$offset = $Params['Offset'];
$viewParameters = array( 'offset' => $offset );
$userParameters = $Params['UserParameters'];
$path = implode( '/', $Module->ViewParameters );
$browserView = $Module->functionURI( $Module->currentView() );

if ( $path )
{
    $path = '/' . $path;
}

$pathList = array();
$errorList = array();
$object = null;
$objectKey = false;
$limit = 10;
$children = array();
$children_count = 0;
if (  (!isset($offset)) || ($offset == '')  )
{
    $offset = 0;
}

if ( eZPreferences::value( 'alfresco_browse_children_limit' ) )
{
    switch( eZPreferences::value( 'alfresco_browse_children_limit' ) )
    {
        case '2': { $limit = 25; } break;
        case '3': { $limit = 50; } break;
        default:  { $limit = 10; } break;
    }
}

try
{
	$repository = nxcAlfresco::getRootFolder();
	if (
		!empty($repository->repositoryName) &&
		($repository->repositoryName == 'KnowledgeTree DMS')
	) {
		$_SESSION['knowledgeTreeDMS'] = true;
		if (preg_match('/alfresco\/browser/', $_SESSION['LastAccessesURI'])) {
			$sessionTree = new nxcSessionTree();
			$userParameters['id'] = $sessionTree->nav_tree_modify($userParameters);
		}
	} else {
		$_SESSION['knowledgeTreeDMS'] = false;
	}

	// @TODO: Do not use root folder but user home folder instead
    $objectKey = isset( $userParameters['id'] ) ? urldecode( $userParameters['id'] ) : ( $path ? nxcAlfrescoUtils::urlEncode( $path ) : false );
    $parentSelfUri = "";
    if ( $objectKey ) {
        $object = nxcAlfrescoObjectHandler::instance( $objectKey );

        if ( $object->hasObject() )
        {
            // Organize path list from root folder to current object
            $pathList = $object->getBreadCrumbs();
            $children = $object->getChildren( $offset, $limit );
            $parentSelfUri = $object->getParentSelfUri();

            // Total chidren count
            $children_count = count($object->getChildren());
        }
        else
        {
            $error = ezi18n( 'alfresco', 'Unable to get object from repository by key: %id%', false, array( '%id%' => $objectKey ) );
            $errorList[] = $error;
            eZDebug::writeError( $error );
        }
    } else
    {
        $repository = nxcAlfresco::getRootFolder();

        $cmis_object = new stdClass();

        $cmis_object->id = $repository->rootFolderId;

        $cmis_object->title = $repository->repositoryName;
        $cmis_object->summary = (string) $repository->repositoryDescription;
        $cmis_object->type = 'folder';
        $cmis_object->updated = null;
        $cmis_object->author = 'system';
        $cmis_object->childrenUri = $repository->childrens;
        $cmis_object->parentUri = "";
        $cmis_object->selfUri = "";
        $object = new nxcAlfrescoObjectHandler(null, $cmis_object);

        if ( $object->hasObject() )
        {
            // Organize path list from root folder to current object
            $pathList = $object->getBreadCrumbs();
            $children = $object->getChildren( $offset, $limit );
            $children_count = count( $object->getChildren( ) );
        }
        else
        {
            $error = ezi18n( 'alfresco', 'Unable to get object from repository by key: %id%', false, array( '%id%' => $objectKey ) );
            $errorList[] = $error;
            eZDebug::writeError( $error );
        }

    }

}
catch ( Exception $error )
{
    // If access is denied
    if ( $error->getCode() == 403 )
    {
        return $Module->redirectTo( 'alfresco/login' );
    }

    $errorList[] = $error->getMessage();
    eZDebug::writeError( $error->getMessage() );
}


$tpl = templateInit();

$object->url_alias = $parentSelfUri;

$tpl->setVariable( 'current_object', $object );
$tpl->setVariable( 'parent_self', $parentSelfUri );
$tpl->setVariable( 'object_id', $objectKey );
$tpl->setVariable( 'error_list', $errorList );
$tpl->setVariable( 'limit', $limit );
$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'children_count', $children_count );
$tpl->setVariable( 'children', $children );

$Result = array();

$Result['content'] = $tpl->fetch( 'design:alfresco/browser.tpl' );
$Result['left_menu'] = 'design:alfresco/alfresco_menu.tpl';
$Result['path'] = $pathList;

?>
