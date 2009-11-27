<?php
/**
 * Created on: <08-Jun-2009 11:00:00 vd>
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

/*
 * Expand the children of a node with offset and limit as a json response for use in javascript
 */

//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisobjecthandler.php' );
//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisoeajaxcontent.php' );

$Module         = $Params['Module'];
$userParameters = $Params['UserParameters'];
$limit          = isset( $userParameters['limit'] ) ? $userParameters['Limit'] : 10;
$offset         = (int) $userParameters['offset'];
$uri            = isset( $userParameters['uri'] ) ? $userParameters['uri'] : false;

try
{
    $object = nxcCMISObjectHandler::instance( nxcCMISUtils::getDecodedUri( $uri ) );

    if ( !$object->hasObject() )
    {
        header( 'HTTP/1.0 500 Internal Server Error' );
        echo ezi18n( 'cmis', 'Could not fetch cmis object by uri %uri%', null, array( '%uri%' => $uri ) );
        eZExecution::cleanExit();
    }

    $params = array( 'Limit'  => $limit,
                     'Offset' => $offset );

    $childList = $object->getChildren( $offset, $limit );

    // Fetch nodes and total node count
    $count = count( $object->getChildren() );
    // Generate json response from node list
    $list = $childList ? nxcCMISOEAjaxContent::encode( $childList, array( 'fetchChildrenCount' => true, 'loadImages' => true ) ) : '[]';

    $result = '{list:' . $list .
         ",\r\ncount:" . count( $childList ) .
         ",\r\ntotal_count:" . $count .
         ",\r\nnode:" . nxcCMISOEAjaxContent::encode( $object, array( 'fetchPath' => true ) ) .
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
        $url = $Module->functionURI( 'login' );
        eZURI::transformURI( $url );
        $result .= ', login_url: "' . $url . '"';
    }

    $result .= '};';

    echo $result;
}

eZDB::checkTransactionCounter();
eZExecution::cleanExit();

?>
