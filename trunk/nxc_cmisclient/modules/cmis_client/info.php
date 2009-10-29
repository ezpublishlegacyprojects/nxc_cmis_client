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
 * Provider of repository info
 */

include_once( 'kernel/common/template.php' );
//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisutils.php' );

$Module = $Params["Module"];
$repositoryInfo = array();
$errorList = array();

try
{
    $repository = nxcCMISUtils::getRepositoryInfo();
    $repositoryInfo = array( 'Name' => $repository->repositoryName,
                             'Description' => $repository->repositoryDescription,
                             'Id' => $repository->repositoryId,
                             'Root Folder Id' => nxcCMISUtils::getHostlessUri( $repository->rootFolderId ),
                             'Vendor' => $repository->vendorName,
                             'Version' =>  $repository->productVersion );
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

$tpl->setVariable( 'repository_info', $repositoryInfo );
$tpl->setVariable( 'error_list', $errorList );

$Result = array();

$Result['content'] = $tpl->fetch( "design:cmis_client/info.tpl" );
$Result['left_menu'] = 'design:cmis_client/cmis_menu.tpl';
$Result['path'] = array ( array( 'url' => false,
                                 'text' => 'CMIS Information' ) );
?>
