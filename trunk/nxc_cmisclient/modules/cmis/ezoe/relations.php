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
 * Handler of embed objects in ezoe.
 * Tries to fetch object from eZ Publish content tree,
 * if it does not exist create new one.
 * Result object will be returned to 'relations' module of ezoe.
 *
 * @file relations.php
 */

include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/ezcmisobject.php' );

$Module         = $Params['Module'];
$objectID       = isset( $Params['ObjectID'] )      ? (int) $Params['ObjectID']      : 0;
$objectVersion  = isset( $Params['ObjectVersion'] ) ? (int) $Params['ObjectVersion'] : 0;
$embedInline    = isset( $Params['EmbedInline'] )   ? $Params['EmbedInline']         : 'false';
$embedSize      = isset( $Params['EmbedSize'] )     ? $Params['EmbedSize']           : '';
$userParameters = $Params['UserParameters'];

$uri = isset( $userParameters['uri'] ) ? $userParameters['uri'] : false;

if ( !$uri )
{
    header( 'HTTP/1.0 500 Internal Server Error' );
    echo ezi18n( 'design/standard/ezoe', 'Invalid or missing parameter: %parameter', null, array( '%parameter' => 'uri' ) );
    eZExecution::cleanExit();
}

try
{
    $object = eZCMISObject::getContentObject( $uri );

    if ( !$object )
    {
        header( 'HTTP/1.0 500 Internal Server Error' );
        echo ezi18n( 'cmis', 'Could not fetch eZContentObject by cmis object id: %key', null, array( '%key' => $uri ) );
        eZExecution::cleanExit();
    }

    return $Module->redirectTo( 'ezoe/relations/' . $objectID . '/' . $objectVersion . '/auto/eZObject_' . $object->attribute( 'id' ) . '/' . $embedInline . '/' . $embedSize );
}
catch ( Exception $error )
{
    // If access is denied
    if ( $error->getCode() == 403 )
    {
        return $Module->redirectTo( 'cmis/login' );
    }

    header( 'HTTP/1.0 500 Internal Server Error' );
    echo $error->getMessage();
    eZExecution::cleanExit();
}
?>
