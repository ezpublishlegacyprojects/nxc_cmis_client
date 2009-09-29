<?php
/**
 * Created on: <6-Jul-2009 11:00:54 vd>
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
 * Handler for uploading files to Alfresco within ezoe.
 *
 * @file upload.php
 */

//include_once( 'extension/nxc_alfresco/classes/nxcalfrescoobjecthandler.php' );

$Module        = $Params['Module'];
$http          = eZHTTPTool::instance();
$objectID      = isset( $Params['ObjectID'] )         ? (int) $Params['ObjectID']         : 0;
$object        = eZContentObject::fetch( $objectID );
$objectVersion = isset( $Params['ObjectVersion'] )    ? (int) $Params['ObjectVersion']    : 0;
$contentType   = ( isset( $Params['ContentType'] )
                   && $Params['ContentType'] !== '' ) ? $Params['ContentType']            : 'auto';
$forcedUpload  = isset( $Params['ForcedUpload'] )     ? (int) $Params['ForcedUpload']     : 0;
$location      = $http->hasPostVariable( 'location' ) ? $http->postVariable( 'location' ) : false;
$redirectUrl   = 'ezoe/upload/' . $objectID . '/' . $objectVersion . '/' . $contentType . '/' . $forcedUpload;
$alfresco      = 'alfresco_';

if ( strpos( $location, $alfresco ) === false )
{
    header( 'HTTP/1.0 500 Internal Server Error' );
    echo ezi18n( 'design/standard/ezoe', 'Invalid or missing parameter: %parameter', null, array( '%parameter' => $alfresco ) );
    eZExecution::cleanExit();
}

$exploded = explode( $alfresco, $location );
$parentId = isset( $exploded[1] ) ? $exploded[1] : false;

if ( !$parentId )
{
    header( 'HTTP/1.0 500 Internal Server Error' );
    echo ezi18n( 'design/standard/ezoe', 'Invalid or missing parameter: %parameter', null, array( '%parameter' => 'parentId' ) );
    eZExecution::cleanExit();
}

$user = eZUser::currentUser();
$result = ( $user instanceOf eZUser ) ? $user->hasAccessTo( 'ezoe', 'relations' ) : $result = array( 'accessWord' => 'no' );

if ( $result['accessWord'] == 'no' )
{
   echo ezi18n( 'design/standard/error/kernel', 'Your current user does not have the proper privileges to access this page.' );
   eZExecution::cleanExit();
}

$errorList = array();

// is this a upload?
// forcedUpload is needed since hasPostVariable returns false if post size exceeds
// allowed size set in max_post_size in php.ini
if ( $http->hasPostVariable( 'uploadButton' ) || $forcedUpload )
{
    $objectName = $http->hasPostVariable( 'objectName' )                         ? trim( $http->postVariable( 'objectName' ) )                         : '';
    $desc       = $http->hasPostVariable( 'ContentObjectAttribute_description' ) ? trim( $http->postVariable( 'ContentObjectAttribute_description' ) ) : '';

    $attrName = 'fileName';
    $canFetch = eZHTTPFile::canFetch( $attrName );
    if ( !$canFetch )
    {
        $errorList[] = ezi18n( 'alfresco', 'Could not fetch file by name: %name', false, array( '%name' => $name ) );
    }

    if ( $canFetch and !count( $errorList ) )
    {
        $binaryFile = eZHTTPFile::fetch( $attrName );
        $fileName = $binaryFile->attribute( 'filename' );

        $properties = new stdClass();
        $properties->title = !empty( $objectName ) ? $objectName :$binaryFile->attribute( 'original_filename' );
        $properties->summary = $desc;
        $properties->type = 'document';
        $properties->contentMimeType = $binaryFile->attribute( 'mime_type' );
        $properties->content = file_get_contents( $fileName );

        try
        {
            $editObject = nxcAlfrescoObjectHandler::createObject( $properties );

            if ( $editObject and $editObject->store( $parentId ) )
            {
                echo '<html><head><title>HiddenUploadFrame</title><script type="text/javascript">';
                echo 'window.parent.eZOEPopupUtils.selectByAlfrescoEmbedId( "' . urlencode( urlencode( $editObject->getId() ) ) . '" );';
                echo '</script></head><body></body></html>';
            }
            else
            {
                $errorList[] = ezi18n( 'alfresco', 'Could not store %name', false, array( '%name' => 'file' ) );
            }
        }
        catch ( Exception $error )
        {
            $errorList[] = $error->getMessage();
        }
    }

    if ( count( $errorList ) )
    {
        echo '<html><head><title>HiddenUploadFrame</title><script type="text/javascript">';
        echo 'window.parent.document.getElementById("upload_in_progress").style.display = "none";';
        echo '</script></head><body><div style="position:absolute; top: 0px; left: 0px;background-color: white; width: 100%;">';

        foreach( $errorList as $err )
        {
            echo '<p style="margin: 0; padding: 3px; color: red">' . $err . '</p>';
        }

        echo '</div></body></html>';
    }
}

eZExecution::cleanExit();

?>
