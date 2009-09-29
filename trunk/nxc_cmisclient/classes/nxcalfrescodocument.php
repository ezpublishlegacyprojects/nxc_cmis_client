<?php
/**
 * Definition of nxcAlfrescoDocument class
 *
 * Created on: <25-Apr-2009 21:00:11 vd>
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
 * Definition of alfresco document class
 *
 * @file nxcalfrescodocument.php
 */

//include_once( 'extension/nxc_alfresco/classes/nxcalfrescobaseobject.php' );
//include_once( 'extension/nxc_alfresco/classes/nxcalfresco.php' );

class nxcAlfrescoDocument extends nxcAlfrescoBaseObject
{

    /**
     * Size of the content
     *
     * @var string
     */
    protected $Size = null;

    /**
     * Content
     *
     * @var byte[]
     */
    protected $Content = null;

    /**
     * Alfresco icon URL of current object
     *
     * @var string
     */
    protected $AlfrescoIcon = null;

    /**
     * @reimp
     */
    protected function setFields( $object )
    {
        parent::setFields( $object );

        $this->Size = isset( $object->size ) ? number_format( $object->size / 1000, 2, '.', ',' ) . ' K' : null;
        $this->DocType = isset( $object->contentMimeType ) ? (string) $object->contentMimeType : null;
        $this->Content = isset( $object->content ) ? $object->content : null;
        $this->AlfrescoIcon = isset( $object->icon ) ? $object->icon : null;
    }

    /**
     * @reimp
     */
    public static function definition()
    {
        $parentDef = parent::definition();
        $currentDef = array( 'function_attributes' => array_merge( $parentDef['function_attributes'], array( 'size' => 'getSize',
                                                                                                             'content' => 'getContent',
                                                                                                             'alfresco_icon' => 'getAlfrescoIcon' ) ) );

        return $currentDef;
    }

    /**
     * @return byte[] Object content
     */
    public function getContent()
    {
        
        if ( !$this->Content )
        {
            $this->Content = nxcAlfresco::getContentStream( $this->StreamUri );
        }

        return $this->Content;
    }

    /**
     * @return string Size
     */
    public function getSize()
    {
        return $this->Size;
    }

    /**
     * @reimp
     */
    public function getClassIdentifier()
    {
        return strstr( $this->DocType, 'image' ) !== false ? 'image' : ( strstr( $this->DocType, 'text' ) !== false ? 'text' : 'file' );
    }

    /**
     * @return url to alfresco icon
     */
    public function getAlfrescoIcon()
    {
        return $this->AlfrescoIcon;
    }

    /**
     * @reimp
     */
    public function update()
    {
        if ( !parent::update() )
        {
            return false;
        }

        // Clear content to update it later in getContent()
        $this->Conetnt = null;
    }

    /**
     * @reimp
     *
     * @note Updating content of not text files is not supported by CMIS
     */
    public function store( $parentFolderId = false )
    {
        $content = $this->Content ? $this->Content : false;
        // Just update current folder
        if ( $this->Id )
        {
            $properties = array( 'title' => $this->Title,
                                 'content-type' => $this->DocType );

            // Binary content cannot be stored, only text content should be passed
            $contentStr = strstr( $this->DocType, 'text' ) !== false ? $content : false;
            $id = nxcAlfresco::updateProperties( $this->Id, $properties, $content );
            if ( $id === false )
            {
                eZDebug::writeError( 'Could not update document(' . $this->Id . ') using properties: ' . print_r( $properties, true ), 'nxcAlfrescoDocument::store()' );
                return false;
            }

        }
        else // Create new one
        {
            if ( !$parentFolderId )
            {
                eZDebug::writeError( 'Parent folder id is not set.', 'nxcAlfrescoDocument::store()' );
                return false;
            }

            $properties = array( 'title' => $this->Title,
                                 'summary' => $this->Summary,
                                 'content-type' => $this->DocType );

            $newId = nxcAlfresco::createDocument( $properties, $parentFolderId, $content );
            if ( $newId === false )
            {
                eZDebug::writeError( 'Could not create document using properties: ' . print_r( $properties, true ) . 'and parent folder id: ' . $parentFolderId, 'nxcAlfrescoDocument::store()' );
                return false;
            }

            $this->Id = $newId;
        }

        return true;
    }

    /**
     * Removes current object from repository
     */
    public function remove()
    {
        if ($_SESSION['knowledgeTreeDMS']) {
        	return nxcAlfresco::deleteObject( $this->StreamUri );
        } else {
        	return nxcAlfresco::deleteObject( $this->SelfUri );
        }
    }

}
?>
