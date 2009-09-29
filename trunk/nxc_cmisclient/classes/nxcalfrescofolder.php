<?php
/**
 * Definition of nxcAlfrescoFolder class
 *
 * Created on: <25-Apr-2009 21:00:15 vd>
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
 * Definition of alfresco folder class
 *
 * @file nxcalfrescofolder.php
 */

//include_once( 'extension/nxc_alfresco/classes/nxcalfrescobaseobject.php' );

class nxcAlfrescoFolder extends nxcAlfrescoBaseObject
{
    /**
     * @reimp
     */
    protected function setFields( $object )
    {
        parent::setFields( $object );

        $this->DocType = 'Space';
    }

    /**
     * @reimp
     */
    public function getClassIdentifier()
    {
        return 'folder';
    }

    /**
     * @reimp
     */
    public function store( $parentFolderId = false )
    {

        // Just update current folder
        if ( $this->Id )
        {
            $properties = array( 'title' => $this->Title );
            $id = nxcAlfresco::updateProperties( $this->Id, $properties );
            if ( $id === false )
            {
                eZDebug::writeError( 'Could not update folder(' . $this->Id . ') using properties: ' . print_r( $properties, true ), 'nxcAlfrescoFolder::store()' );
                return false;
            }
        }
        else // If id is not set it means new folder should be created in repository
        {
            // It is impossible to create new folder without parent folder id
            if ( !$parentFolderId )
            {
                eZDebug::writeError( 'Parent folder id is not set.', 'nxcAlfrescoFolder::store()' );
                return false;
            }

            $properties = array( 'title' => $this->Title,
                                 'summary' => $this->Summary );
            $newId = nxcAlfresco::createFolder( $properties, $parentFolderId );
            if ( $newId === false )
            {
                eZDebug::writeError( 'Could not create folder using properties: ' . print_r( $properties, true ) . 'and parent folder id: ' . $parentFolderId, 'nxcAlfrescoFolder::store()' );
                return false;
            }

            $this->Id = $newId;
        }

        return true;
    }

    /**
     * @reimp
     */
    public function remove()
    {
        return nxcAlfresco::deteleTree( $this->DescendantsUri );
    }

}
?>
