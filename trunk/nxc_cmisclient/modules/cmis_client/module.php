<?php
/**
 * Definition of module CMIS
 *
 * Created on: <18-Apr-2009 11:00:54 vd>
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

$Module = array( 'name' => 'CMIS Client',
                 'variable_params' => true );

$ViewList = array();
$ViewList['browser']   = array( 'script' => 'browser.php',
                                'default_navigation_part' => 'nxccmispart',
                                'unordered_params' => array( 'offset' => 'Offset' ) );
$ViewList['search']    = array( 'script' => 'search.php',
                                'default_navigation_part' => 'nxccmispart' );
$ViewList['download']  = array( 'script' => 'download.php',
                                'default_navigation_part' => 'nxccmispart' );
$ViewList['info']      = array( 'script' => 'info.php',
                                'default_navigation_part' => 'nxccmispart' );
$ViewList['action']    = array( 'script' => 'action.php',
                                'default_navigation_part' => 'nxccmispart' );
$ViewList['remove']    = array( 'script' => 'remove.php',
                                'default_navigation_part' => 'nxccmispart' );
$ViewList['edit']      = array( 'script' => 'edit.php',
                                'default_navigation_part' => 'nxccmispart' );
$ViewList['content']   = array( 'script' => 'content.php',
                                'default_navigation_part' => 'nxccmispart' );
$ViewList['expand']    = array( 'script' => 'ezoe/expand.php',
                                'default_navigation_part' => 'nxccmispart' );
$ViewList['relations'] = array( 'script' => 'ezoe/relations.php',
                                'default_navigation_part' => 'nxccmispart',
                                'params' => array( 'ObjectID', 'ObjectVersion', 'ContentType', 'EmbedID', 'EmbedInline', 'EmbedSize' ) );
$ViewList['upload']    = array( 'script' => 'ezoe/upload.php',
                                'default_navigation_part' => 'nxccmispart',
                                'params' => array( 'ObjectID', 'ObjectVersion', 'ContentType', 'ForcedUpload' ) );
$ViewList['login']     = array( 'script' => 'login.php',
                                'default_navigation_part' => 'nxccmispart' );
$ViewList['logout']    = array( 'script' => 'logout.php',
                                'default_navigation_part' => 'nxccmispart' );

?>
