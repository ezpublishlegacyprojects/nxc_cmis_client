<?php
/**
 * Created on: <18-Apr-2009 10:00:00 vd>
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
 * Object repository browser
 */

include_once( 'kernel/common/template.php' );
include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisobjecthandler.php' );
include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisutils.php' );

$Module = $Params['Module'];
$offset = $Params['Offset'];
$viewParameters = array( 'offset' => $offset );
$browserView = $Module->functionURI( $Module->currentView() );

$objectKey = nxcCMISUtils::getDecodedUri( implode( '/', $Module->ViewParameters ) );

$pathList = array();
$errorList = array();
$object = null;
$limit = 10;
$children = array();
$childrenCount = 0;

if ( eZPreferences::value( 'cmis_browse_children_limit' ) )
{
    switch( eZPreferences::value( 'cmis_browse_children_limit' ) )
    {
        case '2': { $limit = 25; } break;
        case '3': { $limit = 50; } break;
        default:  { $limit = 10; } break;
    }
}

try
{
    $object = nxcCMISObjectHandler::instance( $objectKey );

    if ( $object->hasObject() )
    {
        // Organize path list from root folder to current object
        $pathList = $object->getBreadCrumbs();
        $children = $object->getChildren( $offset, $limit );
        // Total chidren count
        $childrenCount = count( $object->getChildren() );
    }
    else
    {
        $error = ezi18n( 'cmis', "Unable to get object from repository by uri: '%id%'", false, array( '%id%' => $objectKey ) );
        $errorList[] = $error;
        eZDebug::writeError( $error );
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
    eZDebug::writeError( $error->getMessage() );
}

$tpl = templateInit();

$tpl->setVariable( 'current_object', $object );
$tpl->setVariable( 'error_list', $errorList );
$tpl->setVariable( 'limit', $limit );
$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'children_count', $childrenCount );
$tpl->setVariable( 'children', $children );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:cmis_client/browser.tpl' );
$Result['left_menu'] = 'design:cmis_client/cmis_menu.tpl';
$Result['path'] = $pathList;

?>
