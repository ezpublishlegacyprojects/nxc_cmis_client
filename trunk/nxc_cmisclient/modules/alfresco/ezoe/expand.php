<?php
/**
 * Created on: <08-Jun-2009 11:00:00 vd>
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

/*
 * Expand the children of a node with offset and limit as a json response for use in javascript
 */

//include_once( 'extension/nxc_alfresco/classes/nxcalfresco.php' );
//include_once( 'extension/nxc_alfresco/classes/nxcalfrescoobjecthandler.php' );
//include_once( 'extension/nxc_alfresco/classes/nxcalfrescooeajaxcontent.php' );

$userParameters = $Params['UserParameters'];
$limit          = isset( $userParameters['limit'] ) ? $userParameters['Limit'] : 10;
$offset         = (int) $userParameters['offset'];
$path           = implode( '/', $Module->ViewParameters );

if ( $path )
{
    $path = '/' . $path;
}

// @TODO: Do not use root folder but user home folder instead
//$objectKey = isset( $userParameters['id'] ) ? urldecode( $userParameters['id'] ) : ( $path ? nxcAlfrescoUtils::urlEncode( $path ) : nxcAlfresco::getRootFolder() );

 $objectKey = isset( $userParameters['id'] ) ? urldecode( $userParameters['id'] ) : ( $path ? nxcAlfrescoUtils::urlEncode( $path ) : false );
 $objectKey = str_replace( '#slash#', '/', $objectKey );
$http = eZHTTPTool::instance();
try
{

    if ( $objectKey )
    {
        $object = nxcAlfrescoObjectHandler::instance( $objectKey );

        if ( !$object->hasObject() )
        {
            header("HTTP/1.0 500 Internal Server Error");
            echo ezi18n( 'alfresco', 'Could not fetch alfresco object by key %key', null, array( '%key' => $objectKey ) );
            eZExecution::cleanExit();
        }
    }
    else
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


        if ( !$object->hasObject() )
        {

            header("HTTP/1.0 500 Internal Server Error");
            echo ezi18n( 'design/standard/ezoe', 'Invalid or missing parameter: %parameter', null, array( '%parameter' => 'objectKey' ) );
            eZExecution::cleanExit();
        }
    }

    $params = array( 'Limit'  => $limit,
                     'Offset' => $offset );

    $childList = array();
    $children = $object->getChildren( $offset, $limit );
    foreach ( $children as $child  )
    {
        //$childList[] = nxcAlfrescoObjectHandler::instance( $child->getId() );
        $childList[] = nxcAlfrescoObjectHandler::instance( $child->getSelfUri() );
    }

    // Fetch nodes and total node count
    $count = count( $object->getChildren() );
    // Generate json response from node list
    $list = $childList ? nxcAlfrescoOEAjaxContent::encode( $childList, array( 'fetchChildrenCount' => true, 'loadImages' => true ) ) : '[]';

    $result = '{list:' . $list .
         ",\r\ncount:" . count( $childList ) .
         ",\r\ntotal_count:" . $count .
         ",\r\nnode:" . nxcAlfrescoOEAjaxContent::encode( $object, array( 'fetchPath' => true ) ) .
         ",\r\noffset:" . $offset .
         ",\r\nlimit:" . $limit .
         "\r\n};";

    // Output debug info as js comment
    echo "/*\r\n";
    eZDebug::printReport( false, false );
    echo "*/\r\n" . $result;
}
catch ( Exception $error )
{
    $result = '{error:"' . $error->getMessage() . '"';

    // If access is denied
    if ( $error->getCode() == 403 )
    {
        $url = 'alfresco/login';
        eZURI::transformURI( $url );
        $result .= ', login_url: "' . $url . '"';
    }

    $result .= '};';

    echo $result;
}

eZDB::checkTransactionCounter();
eZExecution::cleanExit();

?>
