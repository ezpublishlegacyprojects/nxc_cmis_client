<?php
/**
 * Definition of nxcCMISBasObject class
 *
 * Created on: <25-Apr-2009 20:59:01 vd>
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
 * Definition of bass class for CMIS objects
 *
 * @file nxccmisbaseobject.php
 */

include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisutils.php' );

class nxcCMISBaseObject
{
    /**
     * CMIS identifier
     *
     * @var string
     */
    protected $Id = null;

    /**
     * Name of CMIS object
     *
     * @var string
     */
    protected $Title = null;

    /**
     * Summary
     *
     * @var string
     */
    protected $Summary = null;

    /**
     * Type of CMIS object like 'document' or 'folder'
     *
     * @var string
     */
    protected $BaseType = null;

    /**
     * Modified date
     *
     * @var string
     */
    protected $Updated = null;

    /**
     * Creator of the object
     *
     * @var string
     */
    protected $Author = null;

    /**
     * Type of the object like 'Folder' or 'text/plain'
     *
     * @var string
     */
    protected $DocType = null;

    /**
     * Object own uri
     *
     * @var string
     */
    protected $SelfUri = null;

    /**
     * Uri to edit the object
     *
     * @var string
     */
    protected $EditUri = null;

    /**
     * Uri to fetch actions that can be executed on the object
     *
     * @var string
     */
    protected $AllowableActionsUri = null;

    /**
     * Uri to fetch relations
     *
     * @var string
     */
    protected $RelationshipsUri = null;

    /**
     * Uri to fetch definition of the object' type
     *
     * @var string
     */
    protected $TypeUri = null;

    /**
     * Uri to fetch repository info where the object is located
     *
     * @var string
     */
    protected $RepositoryUri = null;

    /**
     * Uri to fetch parents
     *
     * @var string
     */
    protected $ParentsUri = null;


    /**
     * Constructor.
     *
     * @param SimpleXMLElement
     */
    public function __construct( SimpleXMLElement $entry = null )
    {
        if ( $entry )
        {
            $this->setFields( $entry );
        }
    }

    /**
     * Sets fields based on \a $object
     *
     * @param stdClass
     */
    public function setFields( $entry )
    {
        if ( !$entry )
        {
            return;
        }

        $this->Id = (string) nxcCMISUtils::getValue( $entry, 'id' );
        $this->Title = (string) nxcCMISUtils::getValue( $entry, 'title' );
        $this->Summary = (string) nxcCMISUtils::getValue( $entry, 'summary' );
        //$this->BaseType = (string) nxcCMISUtils::getXMLValue( $entry, 'cmis:object/cmis:properties/cmis:*[@cmis:name="BaseType"]/cmis:value' );
        $this->Author = (string) nxcCMISUtils::getValue( nxcCMISUtils::getValue( $entry, 'author' ), 'name' );
        $this->Updated = date_format( date_create( (string) nxcCMISUtils::getValue( $entry, 'updated' ) ), 'n/j/Y g:i A' );
        $this->SelfUri = nxcCMISUtils::getEncodedUri( nxcCMISUtils::getHostlessUri( nxcCMISUtils::getLinkUri( $entry, 'self' ) ) );
        $this->EditUri = nxcCMISUtils::getEncodedUri( nxcCMISUtils::getHostlessUri( nxcCMISUtils::getLinkUri( $entry, 'edit' ) ) );
        $this->AllowableActionsUri = nxcCMISUtils::getEncodedUri( nxcCMISUtils::getHostlessUri( nxcCMISUtils::getLinkUri( $entry, 'allowableactions' ) ) );
        $this->RelationshipsUri = nxcCMISUtils::getEncodedUri( nxcCMISUtils::getHostlessUri( nxcCMISUtils::getLinkUri( $entry, 'relationships' ) ) );
        $this->TypeUri = nxcCMISUtils::getEncodedUri( nxcCMISUtils::getHostlessUri( nxcCMISUtils::getLinkUri( $entry, 'type' ) ) );
        $this->RepositoryUri = nxcCMISUtils::getEncodedUri( nxcCMISUtils::getHostlessUri( nxcCMISUtils::getLinkUri( $entry, 'repository' ) ) );
        $this->ParentsUri = nxcCMISUtils::getEncodedUri( nxcCMISUtils::getHostlessUri( nxcCMISUtils::getLinkUri( $entry, 'parents' ) ) );
    }

    /**
     * Definition of the function attributes
     *
     * @note Is used in templates
     */
    public static function definition()
    {
        return array( 'function_attributes' => array( 'id' => 'getId',
                                                      'title' => 'getTitle',
                                                      'summary' => 'getSummary',
                                                      'base_type' => 'getBaseType',
                                                      'updated' => 'getUpdated',
                                                      'author' => 'getAuthor',
                                                      'class_identifier' => 'getClassIdentifier',
                                                      'doc_type' => 'getDocType',
                                                      'parent_self_uri' => 'getParentSelfUri',
                                                      'parent_children_uri' => 'getParentChildrenUri',
                                                      'self_uri' => 'getSelfUri',
                                                      'edit_uri' => 'getEditUri',
                                                      'allowable_actions_uri' => 'getAllowableActionsUri',
                                                      'relationships' => 'getRelationshipsUri',
                                                      'type_uri' => 'getTypeUri',
                                                      'repository_uri' => 'getRepositoryUri'
                                                      ) );
    }

    /**
     * @return Self uri
     */
    public function getSelfUri()
    {
        return $this->SelfUri;
    }

    /**
     * Sets self uri
     */
    public function setSelfUri( $uri )
    {
        // @TODO Is it needed to check for existance of selfUri? If exists don't need to set
        $this->SelfUri = nxcCMISUtils::getEncodedUri( $uri );
    }

    /**
     * @return Edit uri
     */
    public function getEditUri()
    {
        return $this->EditUri;
    }

    /**
     * @return Actions uri
     */
    public function getAllowableActionsUri()
    {
        return $this->AllowableActionsUri;
    }

    /**
     * @return Relations uri
     */
    public function getRelationshipsUri()
    {
        return $this->RelationshipsUri;
    }

    /**
     * @return Type uri
     */
    public function getTypeUri()
    {
        return $this->TypeUri;
    }

    /**
     * @return Repository uri
     */
    public function getRepositoryUri()
    {
        return $this->RepositoryUri;
    }

    /**
     * @return Parent self uri
     */
    public function getParentSelfUri()
    {
        $uri = nxcCMISUtils::fetchLinkValue( nxcCMISUtils::getDecodedUri( $this->ParentsUri ), 'self' );
        // @HACK to prevent situation when the server returns the same object with current, but parent must be returned
        return $uri != nxcCMISUtils::getDecodedUri( $this->SelfUri ) ? nxcCMISUtils::getEncodedUri( $uri ) : '';
    }

    /**
     * @return Parent children uri
     */
    public function getParentChildrenUri()
    {
        $uri = nxcCMISUtils::fetchLinkValue( nxcCMISUtils::getDecodedUri( $this->ParentsUri ), 'children' );

        return  $uri != nxcCMISUtils::getDecodedUri( $this->SelfUri ) ? nxcCMISUtils::getEncodedUri( $uri ) : '';
    }

    /**
     * @return Object id
     */
    public function getId()
    {
        return $this->Id;
    }

    /**
     *?
     */
    public function setId( $id )
    {
        $this->Id = $id;
    }

    /**
     * @return Object name
     */
    public function getTitle()
    {
        return $this->Title;
    }

    /**
     * Sets title
     */
    public function setTitle( $title )
    {
        $this->Title = $title;
    }

    /**
     * @return Object summary
     */
    public function getSummary()
    {
        return $this->Summary;
    }

    /**
     * Sets summary
     */
    public function setSummary( $summary )
    {
        $this->Summary = $summary;
    }

    /**
     * @return Base object type
     */
    public function getBaseType()
    {
        return $this->BaseType;
    }

    /**
     * @return Object modified date
     */
    public function getUpdated()
    {
        return $this->Updated;
    }

    /**
     * @return Object creator
     */
    public function getAuthor()
    {
        return $this->Author;
    }

    /**
     * @return Object class identifier
     *
     * @note Is needed when we need to define which type is this object like 'folder' or 'image' (eZP classes)
     *       Defines which tpl should be parsed
     */
    public function getClassIdentifier()
    {
        return null;
    }

    /**
     * @return Document mime type
     */
    public function getDocType()
    {
        return $this->DocType;
    }

    /**
     * Sets doc type
     */
    public function setDocType( $docType )
    {
        // Do nothing
    }

    /**
     * @return Parents uri
     */
    public function getParentsUri()
    {
        return $this->ParentsUri;
    }

    /**
     * Fetches object parent list from CMIS repository
     *
     * @param If true full parent list or just parent object otherwise
     * @return array of SimpleXMLElement objects
     */
    public function getParentList( $fromRoot = true )
    {
        $name = __METHOD__ . '_' . $this->Id . $fromRoot;
        if ( isset( $GLOBALS[$name] ) )
        {
            return $GLOBALS[$name];
        }

        $parentList = array();
        $parentsUri = nxcCMISUtils::getDecodedUri( $this->ParentsUri );

        while ( $parentsUri )
        {
            $entry = nxcCMISUtils::fetchEntry( nxcCMISUtils::invokeService( $parentsUri ) );
            if ( !$entry )
            {
                break;
            }

            // Prevent infinite loop
            $tmpParentsUri = nxcCMISUtils::getLinkUri( $entry, 'parents' );
            if ( nxcCMISUtils::getHostlessUri( $tmpParentsUri ) == $parentsUri )
            {
                break;
            }

            $parentList[] = $entry;

            if ( !$fromRoot )
            {
                break;
            }

            $parentsUri = $tmpParentsUri;
        }

        $GLOBALS[$name] = $parentList;

        return $GLOBALS[$name];
    }

    /**
     * Stores current object in repository
     *
     * @param should be decoded value. See also nxcCMISUtils::getEncodedUri()
     * @return true if ok
     */
    public function store( $parentChildrenUri = false )
    {
        return true;
    }

    /**
     * Removes current object from repository
     *
     * @return bool
     */
    public function remove()
    {
        return true;
    }

    /**
     * Fetches content of fields from repository and update current
     *
     * @return true if ok
     */
    public function update()
    {
        if ( !$this->SelfUri )
        {
            return false;
        }

        $response = nxcCMISUtils::invokeService( nxcCMISUtils::getDecodedUri( $this->SelfUri ) );
        $entry = nxcCMISUtils::fetchEntry( $response );
        $this->setFields( $entry );

        return true;
    }

    /**
     * @return true if the attribute \a $attr is part of the definition fields or function attributes
     */
    public function hasAttribute( $attr )
    {
        $def = $this->definition();

        return isset( $def['function_attributes'][$attr] );
    }

    /**
     * @return the attribute data for \a $attr, this is a member function depending on function attributes matched
     */
    public function attribute( $attr )
    {
        $retVal = null;
        $def = $this->definition();
        $attrFunctions = isset( $def["function_attributes"] ) ? $def["function_attributes"] : null;
        if ( isset( $attrFunctions[$attr] ) )
        {
            $functionName = $attrFunctions[$attr];

            if ( method_exists( $this, $functionName ) )
            {
                $retVal = $this->$functionName();
            }
            else
            {
                eZDebug::writeError( 'Could not find function : "' . get_class( $this ) . '::' . $functionName . '()".',
                                     'nxcCMISBaseObject::attribute()' );
            }

            return $retVal;
        }

        eZDebug::writeError( "Attribute '$attr' does not exist", 'nxcCMISBaseObject::attribute' );

        return $retVal;
    }

    /**
     * Checks if current object is container
     *
     * @return bool
     * @note Container object must have getChildren() method to fetch children list
     */
    public function isContainer()
    {
        return false;
    }
}
?>
