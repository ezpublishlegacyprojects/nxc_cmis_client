<?php
/**
 * Created on: <18-Apr-2009 13:00:00 vd>
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
 * Remover of repository objects
 */

include_once( 'kernel/common/template.php' );
//include_once( 'extension/nxc_alfresco/classes/nxcalfrescoobjecthandler.php' );
//include_once( 'extension/nxc_alfresco/classes/ezalfresco.php' );

$Module = $Params['Module'];

$http = eZHTTPTool::instance();

$deleteIDArray = $http->hasSessionVariable( 'DeleteIDArrayAlfresco' ) ? $http->sessionVariable( 'DeleteIDArrayAlfresco' ) : array();
$parentURI = $http->hasSessionVariable( 'ParentAlfrescoObjectURI' ) ? $http->sessionVariable( 'ParentAlfrescoObjectURI' ) : $Module->functionURI( 'browser' );
$currentURI = $http->hasSessionVariable( 'CurrentAlfrescoObjectURI' ) ? $http->sessionVariable( 'CurrentAlfrescoObjectURI' ) : $parentURI;
$errorList = array();

if ( count( $deleteIDArray ) <= 0 )
{
    return $Module->redirectTo( $parentURI );
}

// Cleanup and redirect back when cancel is clicked
if ( $http->hasPostVariable( "CancelButton" ) )
{
    $http->removeSessionVariable( 'ParentAlfrescoObjectURI' );
    $http->removeSessionVariable( 'DeleteIDArray' );
    $http->removeSessionVariable( 'CurrentAlfrescoObjectURI' );

    return $Module->redirectTo( $currentURI );
}

if ( $http->hasPostVariable( "ConfirmButton" ) )
{
    $properties = new stdClass();
    foreach ( $deleteIDArray as $objectID )
    {
        try
        {
			/**
			 * $object is nxcAlfrescoDocument or nxcAlfrescoFolder
			 */
        	$object = nxcAlfrescoObjectHandler::fetch( $objectID );

            if ( $object->remove() )
            {
                eZAlfresco::remove( $objectID );
            }
            else
            {
                $errorList[] = ezi18n( 'alfresco', 'Failed to remove "%name"', false, array( '%name' => $object->getTitle() ) );
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
        }
    }

    if ( !count( $errorList ) )
    {
    	$repository = nxcAlfresco::getRootFolder();
		if (
			!empty($repository->repositoryName) &&
			($repository->repositoryName == 'KnowledgeTree DMS') &&
			(!$_SESSION['ktdmsSelect'])
		) {
			if (!empty($_SESSION['parent_path_last'])) {
				$parentURI = 'alfresco/browser/(id)/' . $_SESSION['parent_path_last'];
				$sessionTree = new nxcSessionTree();
				$sessionTree->nav_tree_clear_last();
				//$_SESSION['LastAccessesURI'] = '/alfresco/browser';
				return $Module->redirectTo( $parentURI );
			} else {
				$parentURI = 'alfresco/browser/(id)/';
				return $Module->redirectTo( $parentURI );
			}
		}
        
    	return $Module->redirectTo( $parentURI );
    }
}

$objectList = array();
foreach ( $deleteIDArray as $objectID )
{
    try
    {
        $objectList[] = nxcAlfrescoObjectHandler::fetch( $objectID );
    }
    catch ( Exception $error )
    {
        // If access is denied
        if ( $error->getCode() == 403 )
        {
            return $Module->redirectTo( 'alfresco/login' );
        }

        $errorList[] = $error->getMessage();
    }
}

$tpl = templateInit();

$tpl->setVariable( 'remove_list', $objectList );
$tpl->setVariable( 'error_list', $errorList );

$Result = array();
$Result['content'] = $tpl->fetch( "design:alfresco/remove.tpl" );
$Result['left_menu'] = 'design:alfresco/alfresco_menu.tpl';
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'kernel/content', 'Remove object' ) ) );

?>
