<?php
/**
 * Created on: <18-Apr-2009 13:00:00 vd>
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
 * Remover of repository objects
 */

include_once( 'kernel/common/template.php' );
//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisobjecthandler.php' );
//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisutils.php' );
//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/ezcmisobject.php' );

$Module = $Params['Module'];

$http = eZHTTPTool::instance();

$deleteURIArray = $http->hasSessionVariable( 'CMISDeleteURIArray' ) ? $http->sessionVariable( 'CMISDeleteURIArray' ) : array();
$parentURI = $http->hasSessionVariable( 'CMISParentSelfURI' ) ? $http->sessionVariable( 'CMISParentSelfURI' ) : $Module->functionURI( 'browser' );
$currentURI = $http->hasSessionVariable( 'CMISCurrentSelfURI' ) ? $http->sessionVariable( 'CMISCurrentSelfURI' ) : $parentURI;
$errorList = array();

if ( count( $deleteURIArray ) <= 0 )
{
    return $Module->redirectTo( $parentURI );
}

// Cleanup and redirect back when cancel is clicked
if ( $http->hasPostVariable( 'CancelButton' ) )
{
    $http->removeSessionVariable( 'CMISParentSelfURI' );
    $http->removeSessionVariable( 'CMISDeleteURIArray' );
    $http->removeSessionVariable( 'CMISCurrentSelfURI' );

    return $Module->redirectTo( $currentURI );
}

if ( $http->hasPostVariable( 'ConfirmButton' ) )
{
    foreach ( $deleteURIArray as $objectURI )
    {
        try
        {
            $object = nxcCMISObjectHandler::fetch( nxcCMISUtils::getDecodedUri( $objectURI ) );
            if ( $object and $object->remove() )
            {
                // Remove object from eZ Publish if it exists
                eZCMISObject::remove( $objectURI );
            }
            else
            {
                $errorList[] = ezpI18n::tr( 'cmis', 'Failed to remove "%name"', false, array( '%name' => $object ? $object->getTitle() : $objectURI ) );
            }
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
    }

    if ( !count( $errorList ) )
    {
        $http->removeSessionVariable( 'CMISParentSelfURI' );
        $http->removeSessionVariable( 'CMISDeleteURIArray' );
        $http->removeSessionVariable( 'CMISCurrentSelfURI' );

        return $Module->redirectTo( $parentURI );
    }
}

$objectList = array();

foreach ( $deleteURIArray as $objectURI )
{
    try
    {
        $objectList[] = nxcCMISObjectHandler::fetch( nxcCMISUtils::getDecodedUri( $objectURI ) );
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
}

$tpl = templateInit();

$tpl->setVariable( 'remove_list', $objectList );
$tpl->setVariable( 'error_list', $errorList );

$Result = array();
$Result['content'] = $tpl->fetch( "design:cmis_client/remove.tpl" );
$Result['left_menu'] = 'design:cmis_client/cmis_menu.tpl';
$Result['path'] = array( array( 'url' => false,
                                'text' => ezpI18n::tr( 'kernel/content', 'Remove object' ) ) );

?>