<?php
/**
 * Definition of nxcCMISFunctionCollection class
 *
 * Created on: <01-Jul-2009 11:00:54 vd>
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
 * Container of tpl fetch functions
 *
 * @file nxccmisfunctioncollection.php
 */

//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisobjecthandler.php' );
//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisutils.php' );

class nxcCMISFunctionCollection
{
    /**
     * Determines logged username
     */
    function fetchLoggedUserName()
    {
        return array( 'result' => nxcCMISUtils::getLoggedUserName() );
    }

    /**
     * Fetches object handler by encoded \a $uri
     */
    function fetchObject( $uri )
    {
        try
        {
            $object = nxcCMISObjectHandler::instance( nxcCMISUtils::getDecodedUri( $uri ) );
            if ( $object->hasObject() )
            {
                return array( 'result' => $object );
            }
        }
        catch ( Exception $error )
        {
            eZDebug::writeError( $error->getMessage(), __METHOD__ );
        }

        return array( 'error' => array( 'error_type' => 'kernel',
                                        'error_code' => eZError::KERNEL_NOT_AVAILABLE ) );
    }

    /**
     * Fetches CMIS vendor name
     */
    function fetchVendorName()
    {
        try
        {
            $repositoryInfo = nxcCMISUtils::getRepositoryInfo();

            if ( isset( $repositoryInfo->vendorName ) )
            {
                return array( 'result' => $repositoryInfo->vendorName );
            }
        }
        catch ( Exception $error )
        {
            eZDebug::writeError( $error->getMessage(), __METHOD__ );
        }

        return array( 'error' => array( 'error_type' => 'kernel',
                                        'error_code' => eZError::KERNEL_NOT_AVAILABLE ) );
    }

}

?>
