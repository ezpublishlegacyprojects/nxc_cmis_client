<?php
/**
 * Definition of nxcAlfrescoFunctionCollection class
 *
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
 * Container of tpl fetch functions
 *
 * @file nxcalfrescofunctioncollection.php
 */

//include_once( 'extension/nxc_alfresco/classes/nxcalfresco.php' );
//include_once( 'extension/nxc_alfresco/classes/nxcalfrescoutils.php' );
//include_once( 'extension/nxc_alfresco/classes/nxcalfrescoobjecthandler.php' );

class nxcAlfrescoFunctionCollection
{
    /**
     * Determines logged username
     */
    function fetchLoggedUserName()
    {
        return array( 'result' => nxcAlfrescoUtils::getLoggedUserName() );
    }

    /**
     * Fetches object handler by \a $objectKey
     */
    function fetchObject( $objectKey )
    {
        try
        {
            if ( !$objectKey )
            {

                $repository = nxcAlfresco::getRootFolder();

                $cmis_object = new stdClass();

                $cmis_object->id = $repository->rootFolderId;

                $cmis_object->title = $repository->repositoryName;
                $cmis_object->summary = (string) $repository->repositoryDescription;
                $cmis_object->type = 'folder';
                $cmis_object->updated = null;
                $cmis_object->author = 'system';
                $cmis_object->childrenUri = $repository->childrens;
                $cmis_object->parentUri = "";
                $cmis_object->selfUri = "";
                $object = new nxcAlfrescoObjectHandler(null, $cmis_object);
            }
            else
            {
                // If object key is a path like '/Company Home/Guest Home'
                // all spaces should be escaped
                $objectKey = str_replace( ' ', '%20', $objectKey );

                $object = nxcAlfrescoObjectHandler::instance( $objectKey );
                eZDebug::writeDebug( $object, 'lazy: ' );
            }

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
                                        'error_code' => eZError::KERNEL_ACCESS_DENIED ) );
    }
}

?>
