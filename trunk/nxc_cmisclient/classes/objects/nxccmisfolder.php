<?php
/**
 * Definition of nxcCMISFolder class
 *
 * Created on: <25-Apr-2009 21:00:15 vd>
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
 * Definition of CMIS folder class
 *
 * @file nxccmisfolder.php
 */

//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/objects/nxccmisbaseobject.php' );
//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisutils.php' );

class nxcCMISFolder extends nxcCMISBaseObject
{
    /**
     * Uri to fetch children
     *
     * @var string
     */
    protected $ChildrenUri = null;

    /**
     * Uri to fetch all children (children of children etc)
     *
     * @var string
     */
    protected $DescendantsUri = null;

    /**
     * Parent id
     *
     * @var string
     */
    protected $ParentId = null;

    /**
     * @reimp
     */
    public function __construct( SimpleXMLElement $entry = null )
    {
        parent::__construct( $entry );

        $this->DocType = 'Folder';
        $this->BaseType = 'folder';
    }

    /**
     * @reimp
     */
    public function setFields( $entry )
    {
        if ( !$entry )
        {
            return;
        }

        parent::setFields( $entry );

        $this->ChildrenUri = nxcCMISUtils::getEncodedUri( nxcCMISUtils::getHostlessUri( nxcCMISUtils::getLinkUri( $entry, nxcCMISUtils::getVersionSpecificValue( 'down' ), 'application\/atom\+xml;\s*type=feed' ) ) );
        $this->DescendantsUri = nxcCMISUtils::getEncodedUri( nxcCMISUtils::getHostlessUri( nxcCMISUtils::getLinkUri( $entry, nxcCMISUtils::getVersionSpecificValue( 'down', array( 'descendants' => true ) ), 'application\/cmistree\+xml' ) ) );
        $this->ParentId = (string) nxcCMISUtils::getXMLValue( $entry, nxcCMISUtils::getVersionSpecificProperty( 'parent_id' ) );
    }

    /**
     * @reimp
     */
    public static function definition()
    {
        $parentDef = parent::definition();
        $currentDef = array( 'function_attributes' => array_merge( $parentDef['function_attributes'], array( 'children_uri' => 'getChildrenUri',
                                                                                                             'descendants_uri' => 'getDescendantsUri',
                                                                                                             'parent_id' => 'getParentId',
                                                                                                             ) ) );

        return $currentDef;
    }

    /**
     * @return Parent id
     */
    public function getParentId()
    {
        return $this->ParentId;
    }

    /**
     * Provides list of children
     *
     * @return array of SimpleXMLElement
     */
    public function getChildren( $offset = 0, $limit = 0 )
    {
        $name = __METHOD__ . '_' . $this->Id . '_' . $offset . '_' . $limit;
        if ( isset( $GLOBALS[$name] ) )
        {
            return $GLOBALS[$name];
        }

        $entries = array();

        $childrenUri = nxcCMISUtils::getDecodedUri( $this->getChildrenUri() );
        if ( !$childrenUri or empty( $childrenUri ) )
        {
            return $entries;
        }

        $questionMark = strpos( $childrenUri, '?' ) === false ? '?' : '&';
        $uri = $childrenUri . $questionMark . ( $offset ? 'skipCount=' . $offset : '' ) . ( $limit ? '&maxItems=' . $limit : '' );

        $response = nxcCMISUtils::invokeService( $uri );
        $entry = nxcCMISUtils::fetchEntries( $response );

        if ( isset( $entry[0] ) )
        {
            $id = nxcCMISUtils::getValue( $entry[0], 'id' );
            /**
             * @HACK: Fixing wrong child list in knowledgeTree when using paging.
             *        If returned object is the same with current need to try add '/' to uri.
             */
            if ( $id == $this->Id )
            {
                $uri = $childrenUri . '/' . $questionMark . 'skipCount=' . $offset . '&maxItems=' . $limit;
                $response = nxcCMISUtils::invokeService( $uri );
                $entries = nxcCMISUtils::fetchEntries( $response );
            }
            else
            {
                $entries = $entry;
            }
        }

        if ( count( $entries ) )
        {
            $GLOBALS[$name] = $entries;
        }

        return $entries;
    }

    /**
     * @return Document children
     */
    public function getChildrenUri()
    {
        return $this->ChildrenUri;
    }

    /**
     * Sets children uri
     */
    public function setChildrenUri( $uri )
    {
        $this->ChildrenUri = nxcCMISUtils::getEncodedUri( $uri );
    }

    /**
     * @return Document descendants uri
     */
    public function getDescendantsUri()
    {
        return $this->DescendantsUri;
    }

    /**
     * @reimp
     * @TODO Is it better to put it to ini?
     */
    public function getClassIdentifier()
    {
        return 'folder';
    }

    /**
     * @reimp
     */
    public function store( $parentChildrenUri = false )
    {
        $uri = $parentChildrenUri;
        $method = 'POST';

        // If $this->SelfUri is not set it means new folder should be created in repository
        if ( $this->SelfUri )
        {
            $uri = nxcCMISUtils::getDecodedUri( $this->SelfUri );
            $method = 'PUT';
        }

        if ( !$uri )
        {
            eZDebug::writeError( 'Parent\'s children uri is not set.', __METHOD__ );

            return false;
        }

        $doc = nxcCMISUtils::createDocument();
        $root = nxcCMISUtils::createRootNode( $doc, 'entry' );
        $doc->appendChild( $root );
        $title = $doc->createElement( 'title', nxcCMISUtils::escapeXMLEntries( $this->Title ) );
        $root->appendChild( $title );
        $summary = $doc->createElement( 'summary', nxcCMISUtils::escapeXMLEntries( $this->Summary ) );
        $root->appendChild( $summary );

        $object = $doc->createElement( nxcCMISUtils::getVersionSpecificValue( 'cmisra:object' ) );
        $root->appendChild( $object );
        $properties = $doc->createElement( 'cmis:properties' );
        $object->appendChild( $properties );
        $objectTypeId = $doc->createElement( 'cmis:propertyId' );
        $objectTypeId->setAttribute( nxcCMISUtils::getVersionSpecificValue( 'propertyDefinitionId' ), nxcCMISUtils::getVersionSpecificValue( 'cmis:objectTypeId' ) );
        $properties->appendChild( $objectTypeId );
        $value = $doc->createElement( 'cmis:value', nxcCMISUtils::getVersionSpecificValue( 'cmis:' ) . 'folder' ); // @TODO: Hardcoded value!!!
        $objectTypeId->appendChild( $value );

        $xml = $doc->saveXML();

        $response = nxcCMISUtils::invokeService( $uri, $method, nxcCMISUtils::createHeaders( strlen( $xml ) ), $xml );

        if ( is_bool( $response ) )
        {
            return $response;
        }

        $entry = nxcCMISUtils::fetchEntry( $response );
        // Update current object by returned values
        $this->setFields( $entry );
        if ( !$this->SelfUri )
        {
            eZDebug::writeError( '"self" uri does not exist in response', __METHOD__ );

            return false;
        }

        return true;
    }

    /**
     * @reimp
     */
    public function isContainer()
    {
        return true;
    }

    /**
     * @reimp
     */
    public function remove()
    {
        $response = nxcCMISUtils::invokeService( nxcCMISUtils::getDecodedUri( $this->DescendantsUri ), 'DELETE' );

        return ( is_bool( $response ) and $response );
    }
}
?>
