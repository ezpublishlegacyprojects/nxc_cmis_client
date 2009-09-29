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
 * Downloader of repository objects
 */

//include_once( 'extension/nxc_alfresco/classes/nxcalfrescoobjecthandler.php' );

$Module = $Params["Module"];
$userParameters = $Params['UserParameters'];
$objectId = isset( $userParameters['id'] ) ? $userParameters['id'] : false;
if ( !$objectId )
{
    eZDebug::writeError( 'Object id is not set.' );
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

$objectId = urldecode( $objectId );

try
{
    $object = nxcAlfrescoObjectHandler::fetch( $objectId );
    if ( !$object or strtolower( $object->getType() ) == 'folder' )
    {
        eZDebug::writeError( 'Could not fetch object by id: ' . $objectId . ' or it is a space', 'download' );
        return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
    }

    $content = $object->getContent();

    if ( ob_get_level() )
    {
        ob_end_clean();
    }

    header( 'Cache-Control: no-cache, must-revalidate' );
    header( 'Content-type: ' . $object->getDocType() );
    header( 'Content-Disposition: attachment; filename="' . $object->getTitle(). '"' );

    print( $content );

    eZExecution::cleanExit();
}
catch ( Exception $error )
{
    // If access is denied
    if ( $error->getCode() == 403 )
    {
        return $Module->redirectTo( 'alfresco/login' );
    }

    eZDebug::writeError( $error->getMessage(), 'download' );
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

?>
