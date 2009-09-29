<?php
/**
 * Created on: <19-Apr-2009 15:00:00 vd>
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
 * Creator of repository objects
 */

include_once( 'kernel/common/template.php' );
//include_once( 'extension/nxc_alfresco/classes/nxcalfresco.php' );
//include_once( 'extension/nxc_alfresco/classes/nxcalfrescoobjecthandler.php' );
//include_once( 'extension/nxc_alfresco/classes/ezalfresco.php' );

$Module = $Params['Module'];
$userParameters = $Params['UserParameters'];
$http = eZHTTPTool::instance();

$errorList = array();
$parentURI = $http->hasSessionVariable( 'ParentAlfrescoObjectURI' ) ? $http->sessionVariable( 'ParentAlfrescoObjectURI' ) : '/';
$classID = $http->hasSessionVariable( 'AlfrescoClassID' ) ? $http->sessionVariable( 'AlfrescoClassID' ) : false;
$parentObjectID = $http->hasSessionVariable( 'ParentAlfrescoObjectID' ) ? $http->sessionVariable( 'ParentAlfrescoObjectID' ) : false;
$editObjectID = isset( $userParameters['id'] ) ? urldecode( $userParameters['id'] ) : false;

$redirectURI = $http->hasPostVariable( 'RedirectURI' ) ? $http->postVariable( 'RedirectURI' ) : $parentURI;

$tpl = templateInit();

// Cleanup and redirect back when cancel is clicked
if ( $http->hasPostVariable( 'CancelButton' ) )
{
    $http->removeSessionVariable( 'ParentAlfrescoObjectURI' );
    $http->removeSessionVariable( 'AlfrescoClassID' );
    $http->removeSessionVariable( 'ParentAlfrescoObjectID' );

    return $Module->redirectTo( $redirectURI );
}

if ( $http->hasPostVariable( 'ConfirmButton' ) )
{
    $name = $http->hasPostVariable( 'AttributeName' ) ? $http->postVariable( 'AttributeName' ) : false;
    $desc = $http->hasPostVariable( 'AttributeDescription' ) ? $http->postVariable( 'AttributeDescription' ) : '';
    $objectID = $http->hasPostVariable( 'ObjectID' ) ? $http->postVariable( 'ObjectID' ) : false;

    $tpl->setVariable( 'name', $name );
    $tpl->setVariable( 'desc', $desc );
    $properties = false;

    if ( strtolower( $classID ) == 'content' )
    {
        $contentType = $http->hasPostVariable( 'AttributeContentType' ) ? $http->postVariable( 'AttributeContentType' ) : 'text/plain';
        $content = $http->hasPostVariable( 'AttributeContent' ) ? $http->postVariable( 'AttributeContent' ) : '';

        $tpl->setVariable( 'content_type', $contentType );
        $tpl->setVariable( 'content', $content );

        $properties = new stdClass();
        $properties->title = $name;
        $properties->summary = $desc;
        $properties->type = 'document';
        $properties->contentMimeType = $contentType;
        $properties->content = $content;
    }
    elseif ( strtolower( $classID ) == 'space' )
    {
        $properties = new stdClass();
        $properties->title = $name;
        $properties->summary = $desc;
        $properties->type = 'Folder';
    }
    elseif ( strtolower( $classID ) == 'file' )
    {
        $attrName = 'AttributeFile';
        $canFetch = eZHTTPFile::canFetch( $attrName );
        if ( !$objectID and !$canFetch )
        {
            $error = ezi18n( 'alfresco', 'Could not fetch file by name: %name', false, array( '%name' => $name ) );
            $errorList[] = $error;

            eZDebug::writeError( $error, 'alfresco/create' );
        }

        $properties = new stdClass();
        $properties->title = $name;
        $properties->summary = $desc;
        $properties->type = 'document';

        if ( $canFetch and !count( $errorList ) )
        {
            $binaryFile = eZHTTPFile::fetch( $attrName );
            $fileName = $binaryFile->attribute( 'filename' );

            $properties->contentMimeType = $binaryFile->attribute( 'mime_type' );
            $properties->content = file_get_contents( $fileName );
        }
    }
   // Store or create object

    if ( $properties instanceof stdClass )
    {

        if ( $objectID )
        {
            $properties->id = $objectID;
        }

        try
        {
            $editObject = nxcAlfrescoObjectHandler::createObject( $properties );
            if ( !$editObject or !$editObject->store( $parentObjectID ) )
            {
                $errorList[] = ezi18n( 'alfresco', 'Could not store %name', false, array( '%name' => $classID ) );
            }
            // Update existing ezp alfresco object
            eZAlfresco::update( $editObject );
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
        $http->removeSessionVariable( 'ParentAlfrescoObjectURI' );
        $http->removeSessionVariable( 'AlfrescoClassID' );
        $http->removeSessionVariable( 'ParentAlfrescoObjectID' );

        return $Module->redirectTo( $redirectURI );
    }

    $editObjectID = $objectID;
}

$object = false;

if ( !$editObjectID )
{
    $supportedClasses = nxcAlfrescoObjectHandler::getCreateClasses();
    if ( !in_array( $classID, $supportedClasses ) )
    {
        eZDebug::writeError( "Class ID ($classID) is not supported", 'alfresco/edit' );
        return $Module->redirectTo( $redirectURI );
    }
}
else // If an object should be edited
{
    try
    {
        $object = nxcAlfrescoObjectHandler::instance( $editObjectID );
        if ( !$object->hasObject() )
        {
            eZDebug::writeError( "Could not fecth object by id: $editObjectID", 'alfresco/edit' );
            return $Module->redirectTo( $redirectURI );
        }

        $classID = $object->getBaseClass();
        $http->setSessionVariable( 'AlfrescoClassID', $classID );
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

    $redirectURI = $Module->functionURI( 'browser' ) . '/(id)/' . urlencode( $editObjectID );
}

if ( !$classID )
{
    eZDebug::writeError( 'Class id is not defined', 'alfresco/edit' );
    return $Module->redirectTo( $redirectURI );
}

$tpl->setVariable( 'error_list', $errorList );
$tpl->setVariable( 'object', $object );
$tpl->setVariable( 'redirect_uri', $redirectURI );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:alfresco/edit/' . strtolower( $classID ) . '.tpl' );
$Result['left_menu'] = 'design:alfresco/alfresco_menu.tpl';
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'kernel/content', 'Create object' ) ) );

?>
