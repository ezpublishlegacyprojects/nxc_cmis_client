<?php
/**
 * Created on: <19-Apr-2009 15:00:00 vd>
 *
 * COPYRIGHT NOTICE: Copyright (C) 2001-2009 NXC AS
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
//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisobjecthandler.php' );
//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/ezcmisobject.php' );

$Module = $Params['Module'];
$userParameters = $Params['UserParameters'];
$http = eZHTTPTool::instance();

$errorList = array();
$parentURI = $http->hasSessionVariable( 'CMISParentSelfURI' ) ? $http->sessionVariable( 'CMISParentSelfURI' ) : '/';
$classID = $http->hasSessionVariable( 'CMISClassID' ) ? $http->sessionVariable( 'CMISClassID' ) : false;
$parentChildrenURI = $http->hasSessionVariable( 'CMISParentChildrenURI' ) ? $http->sessionVariable( 'CMISParentChildrenURI' ) : false;

$objectKey = nxcCMISUtils::getDecodedUri( implode( '/', $Module->ViewParameters ) );

$redirectURI = $http->hasPostVariable( 'RedirectURI' ) ? $http->postVariable( 'RedirectURI' ) : $parentURI;

$tpl = templateInit();

// Cleanup and redirect back when cancel is clicked
if ( $http->hasPostVariable( 'CancelButton' ) )
{
    $http->removeSessionVariable( 'CMISParentSelfURI' );
    $http->removeSessionVariable( 'CMISClassID' );
    $http->removeSessionVariable( 'CMISParentChildrenURI' );

    return $Module->redirectTo( $redirectURI );
}

if ( $http->hasPostVariable( 'ConfirmButton' ) )
{
    $name = $http->hasPostVariable( 'AttributeName' ) ? $http->postVariable( 'AttributeName' ) : false;
    $desc = $http->hasPostVariable( 'AttributeDescription' ) ? $http->postVariable( 'AttributeDescription' ) : '';
    $selfUri = $http->hasPostVariable( 'SelfUri' ) ? nxcCMISUtils::getDecodedUri( $http->postVariable( 'SelfUri' ) ) : false;

    $tpl->setVariable( 'name', $name );
    $tpl->setVariable( 'desc', $desc );
    $editObject = false;

    if ( $classID == 'content' )
    {
        $contentType = $http->hasPostVariable( 'AttributeContentType' ) ? $http->postVariable( 'AttributeContentType' ) : 'text/plain';
        $content = $http->hasPostVariable( 'AttributeContent' ) ? $http->postVariable( 'AttributeContent' ) : '';

        $tpl->setVariable( 'content_type', $contentType );
        $tpl->setVariable( 'content', $content );

        $editObject = $objectKey ? nxcCMISObjectHandler::instance( $objectKey )->getObject() : nxcCMISObjectHandler::createObjectByBaseType( 'document' );
        if ( $editObject )
        {
            $editObject->setDocType( $contentType );
            $editObject->setContent( $content );
        }
    }
    elseif ( $classID == 'folder' )
    {
        $editObject = $objectKey ? nxcCMISObjectHandler::instance( $objectKey )->getObject() : nxcCMISObjectHandler::createObjectByBaseType( 'folder' );
    }
    elseif ( $classID == 'file' )
    {
        $attrName = 'AttributeFile';
        $canFetch = eZHTTPFile::canFetch( $attrName );
        if ( !$selfUri and !$canFetch )
        {
            $error = ezi18n( 'cmis', 'Could not fetch file by name: %name', false, array( '%name' => $name ) );
            $errorList[] = $error;

            eZDebug::writeError( $error, $Module->functionURI( 'create' ) );
        }

        $editObject = $objectKey ? nxcCMISObjectHandler::instance( $objectKey )->getObject() : nxcCMISObjectHandler::createObjectByBaseType( 'document' );

        if ( $editObject and $canFetch and !count( $errorList ) )
        {
            $binaryFile = eZHTTPFile::fetch( $attrName );
            $fileName = $binaryFile->attribute( 'filename' );

            $editObject->setDocType( $binaryFile->attribute( 'mime_type' ) );
            $editObject->setContent( file_get_contents( $fileName ) );
        }
    }

    // Store or create object
    if ( $editObject )
    {
        $editObject->setTitle( $name );
        $editObject->setSummary( $desc );

        if ( $selfUri )
        {
            $editObject->setSelfUri( $selfUri );
        }

        try
        {
            if ( !$editObject->store( $parentChildrenURI ) )
            {
                $errorList[] = ezi18n( 'cmis', 'Could not store %name object', false, array( '%name' => $classID ) );
            }
            else
            {
                // Update existing ezp cmis object
                eZCMISObject::update( $editObject );
            }
        }
        catch ( Exception $error )
        {
            // @TODO: If access denied processed, all entered data will be lost after redirection
            // If access is denied
            if ( $error->getCode() == 403 )
            {
                return $Module->redirectTo( $Module->functionURI( 'login' ) );
            }

            $errorList[] = $error->getMessage();
        }
    }
    else
    {
        $errorList[] = ezi18n( 'cmis', 'No object created' );
    }

    if ( !count( $errorList ) )
    {
        $http->removeSessionVariable( 'CMISParentSelfURI' );
        $http->removeSessionVariable( 'CMISClassID' );
        $http->removeSessionVariable( 'CMISParentChildrenURI' );

        return $Module->redirectTo( $redirectURI );
    }

    $objectKey = $selfUri;
}

$object = false;

if ( !$objectKey )
{
    $supportedClasses = nxcCMISObjectHandler::getCreateClasses();
    if ( !isset( $supportedClasses[$classID] ) )
    {
        eZDebug::writeError( "Class ID ($classID) is not supported", $Module->functionURI( 'edit' ) );

        return $Module->redirectTo( $redirectURI );
    }
}
else // If an object should be edited
{
    try
    {
        $object = nxcCMISObjectHandler::instance( $objectKey );
        if ( !$object->hasObject() )
        {
            eZDebug::writeError( "Could not fecth object by url: $objectKey", $Module->functionURI( 'edit' ) );

            return $Module->redirectTo( $redirectURI );
        }

        $classID = $object->getBaseClass();
        $http->setSessionVariable( 'CMISClassID', $classID );
    }
    catch ( Exception $error )
    {
        // If access is denied
        if ( $error->getCode() == 403 )
        {
            return $Module->redirectTo( $Module->functionURI( 'login' ) );
        }

        $errorList[] = $error->getMessage();
    }

    $redirectURI = $Module->functionURI( 'browser' ) . '/' . nxcCMISUtils::getEncodedUri( $objectKey );
}

if ( !$classID )
{
    eZDebug::writeError( 'Class id is not defined', $Module->functionURI( 'edit' ) );

    return $Module->redirectTo( $redirectURI );
}

$tpl->setVariable( 'error_list', $errorList );
$tpl->setVariable( 'object', $object );
$tpl->setVariable( 'redirect_uri', $redirectURI );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:cmis_client/edit/' . strtolower( $classID ) . '.tpl' );
$Result['left_menu'] = 'design:cmis_client/cmis_menu.tpl';
$Result['path'] = array( array( 'url' => false,
                                'text' => $objectKey ? ezi18n( 'kernel/content', 'Edit object' ) : ezi18n( 'kernel/content', 'Create object' ) ) );

?>
