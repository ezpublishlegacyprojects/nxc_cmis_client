<?php
/**
 * Created on: <01-Jul-2009 11:00:54 vd>
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
 * Definitions of tpl fetch functions
 *
 * @file function_definition.php
 */

//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/modules/cmis_client/nxccmisfunctioncollection.php' );

$FunctionList = array();
$FunctionList['logged_username'] = array( 'name' => 'logged_username',
                                          'call_method' => array( 'class' => 'nxcCMISFunctionCollection',
                                                                  'method' => 'fetchLoggedUserName' ),
                                          'parameter_type' => 'standard',
                                          'parameters' => array() );

$FunctionList['object']          = array( 'name' => 'object',
                                          'call_method' => array( 'class' => 'nxcCMISFunctionCollection',
                                                                  'method' => 'fetchObject' ),
                                          'parameter_type' => 'standard',
                                          'parameters' => array( array( 'name' => 'uri',
                                                                        'type' => 'string',
                                                                        'required' => false,
                                                                        'default' => false ) ) );
$FunctionList['vendor_name']     = array( 'name' => 'vendor_name',
                                          'call_method' => array( 'class' => 'nxcCMISFunctionCollection',
                                                                  'method' => 'fetchVendorName' ),
                                          'parameter_type' => 'standard',
                                          'parameters' => array( ) );


?>
