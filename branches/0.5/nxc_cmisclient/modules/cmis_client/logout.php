<?php
/**
 * Created on: <18-Apr-2009 19:21:00 vd>
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
 * Logout module
 *
 * @file logout.php
 */

//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisutils.php' );

$Module = $Params['Module'];
$http = eZHTTPTool::instance();
$redirectionURI = $http->hasSessionVariable( 'LastAccessesURI' ) ? $http->sessionVariable( 'LastAccessesURI' ) : $Module->functionURI( 'browser' );

nxcCMISUtils::logout();

return $Module->redirectTo( $redirectionURI );

?>
