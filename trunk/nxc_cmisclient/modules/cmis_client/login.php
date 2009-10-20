<?php
/**
 * Created on: <18-Apr-2009 19:21:00 vd>
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
 * Login module
 *
 * @file login.php
 */

include_once( 'kernel/common/template.php' );
include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisutils.php' );

$Module = $Params['Module'];
$http = eZHTTPTool::instance();
$errorList = array();
$redirectionURI = $http->hasPostVariable( 'RedirectURI' ) ? $http->postVariable( 'RedirectURI' ) : ( $http->hasSessionVariable( 'LastAccessesURI' ) ? $http->sessionVariable( 'LastAccessesURI' ) : $Module->functionURI( 'browser' ) );

if ( $redirectionURI == $Module->functionURI( 'login' ) )
{
    $redirectionURI = $Module->functionURI( 'browser' );
}

$login = '';

if ( $http->hasPostVariable( 'LoginButton' ) )
{
    $login = $http->hasPostVariable( 'Login' ) ? $http->postVariable( 'Login' ) : $login;
    $password = $http->hasPostVariable( 'Password' ) ? $http->postVariable( 'Password' ) : '';

    try
    {
        nxcCMISUtils::login( $login, $password );

        // Check if login settings are correct
        if ( nxcCMISUtils::invokeService( nxcCMISUtils::getEndPoint() ) )
        {
            return $Module->redirectTo( $redirectionURI );
        }
    }
    catch ( Exception $error )
    {
        if ( $error->getCode() == 403 )
        {
            $errorList['bad_login'] = true;
        }
        else
        {
            $errorList[] = $error->getMessage();
        }

        eZDebug::writeError( $error->getMessage(), $Module->functionURI( 'login' ) );
    }
}

// If errors exist need to log out
// otherwise wrong logged in user name will be displayed
if ( count( $errorList ) )
{
    nxcCMISUtils::logout();
}

$tpl = templateInit();

$tpl->setVariable( 'login', $login );
$tpl->setVariable( 'error_list', $errorList );
$tpl->setVariable( 'redirect_uri', $redirectionURI );

$Result = array();

$Result['content'] = $tpl->fetch( "design:cmis_client/login.tpl" );
$Result['left_menu'] = 'design:cmis_client/cmis_menu.tpl';
$Result['path'] = array ( array( 'url' => false,
                                 'text' => 'Login' ) );
?>
