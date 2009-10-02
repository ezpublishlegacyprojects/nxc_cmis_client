<?php
/**
 * Created on: <19-Apr-2009 11:00:00 vd>
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
 * Fetches content of specified cmis object
 */

include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisobjecthandler.php' );

$Module = $Params['Module'];
$objectKey = nxcCMISUtils::getDecodedUri( implode( '/', $Module->ViewParameters ) );
if ( !$objectKey )
{
    eZDebug::writeError( 'Object key is not set' );
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

try
{
    $object = nxcCMISObjectHandler::fetch( $objectKey );

    if ( !$object or !method_exists( $object, 'getContent' ) )
    {
        return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
    }

    echo $object->getContent();

    eZExecution::cleanExit();
}
catch ( Exception $error )
{
    // If access is denied
    if ( $error->getCode() == 403 )
    {
        return $Module->redirectTo( 'cmis/login' );
    }

    eZDebug::writeError( $error->getMessage() );
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}
?>
