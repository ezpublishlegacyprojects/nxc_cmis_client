<?php
/**
 * Definition of nxcAlfrescoBasObject class
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
 * Definition of bass class for alfresco objects
 *
 * @file nxcalfrescobaseobject.php
 */

//include_once( 'extension/nxc_alfresco/classes/nxcalfresco.php' );
//include_once( 'extension/nxc_alfresco/classes/nxcalfrescoutils.php' );

class nxcAlfrescoBaseObject
{
    /**
     * Alfresco identifier
     *
     * @var string
     */
    protected $Id = null;

    /**
     * Name of alfresco object
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
     * Type of alfresco object like 'document' or 'folder'
     *
     * @var string
     */
    protected $Type = null;

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
     * Type of the object like 'Space' or 'text/plain'
     *
     * @var string
     */
    protected $DocType = null;

     /**
     * Children for object
     *
     * @var string
     */
    protected $ChildrenUri = null;

     /**
     * object own uri
     *
     * @var string
     */
    protected $SelfUri = null;

    /**
     * object parent uri
     *
     * @var string
     */
    protected $ParentUri = null;

    /**
     * object stream uri
     *
     * @var string
     */
    protected $StreamUri = null;

    /**
     * object descendants uri
     *
     * @var string
     */
    protected $DescendantsUri = null;



    /**
     * Constructor.
     *
     * @param stdClass $object
     */
    public function __construct( $object )
    {
        $this->setFields( $object );
    }

    /**
     * Sets fields based on \a $object
     */
    protected function setFields( $object )
    {
        $this->Id = isset( $object->id ) ? $object->id : null;
        $this->Title = isset( $object->title ) ? $object->title : null;
        $this->Summary = isset( $object->summary ) ? $object->summary : null;
        $this->Type = isset( $object->type ) ? $object->type : null;
        $this->Author = isset( $object->author ) ? (string) $object->author : null;
        $this->Updated = isset( $object->updated ) ? date_format( $object->updated, 'n/j/Y g:i A' ) : null;
        $this->ChildrenUri = isset( $object->childrenUri ) ? (string) $object->childrenUri : null;
        $this->SelfUri = isset( $object->selfUri ) ? (string) $object->selfUri : null;
        $this->ParentUri = isset( $object->parentUri ) ? (string) $object->parentUri : null;
        $this->StreamUri = isset( $object->streamUri ) ? (string) $object->streamUri : null;
        $this->DescendantsUri = isset( $object->descendantsUri ) ? (string) $object->descendantsUri : null;

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
                                                      'type' => 'getType',
                                                      'updated' => 'getUpdated',
                                                      'author' => 'getAuthor',
                                                      'childrenUri' => 'getChildrenUri',
                                                      'selfUri' => 'getSelfUri',
                                                      'uri' => 'getURI',
                                                      'class_identifier' => 'getClassIdentifier',
                                                      'doc_type' => 'getDocType',
                                                      'encoded_id' => 'getEncodedId',
                                                      'parentUri' => 'getParentURI',
                                                      'parent_id' => 'getParentId',
                                                      'streamUri' => 'getStreamUri',
                                                      'descendantsUri' => 'getDescendantsUri' ) );
    }

    /**
     * @return Object id
     */
    public function getId()
    {
        return $this->Id;
    }

    /**
     * @return Encoded object id
     */
    public function getEncodedId()
    {
        return nxcAlfrescoUtils::urlEncode( $this->Id );
    }

    /**
     * @return Object name
     */
    public function getTitle()
    {
        return $this->Title;
    }

    /**
     * @return Object summary
     */
    public function getSummary()
    {
        return $this->Summary;
    }

    /**
     * @return Object type
     */
    public function getType()
    {
        return $this->Type;
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
     * @note Is needed when we need to define which type is this object like 'folder' or 'image'
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
     * @return Document children
     */
    public function getChildrenUri()
    {
        return $this->ChildrenUri;
    }

    /**
     * @return Document own uri
     */
    public function getSelfUri()
    {
        return $this->SelfUri;
    }

    /**
     * @return Document parent uri
     */
    public function getParentUri()
    {
        return $this->ParentUri;
    }

    /**
     * @return Document stream uri
     */
    public function getStreamUri()
    {
        return $this->StreamUri;
    }

    /**
     * @return Document descendants uri
     */
    public function getDescendantsUri()
    {
        return $this->DescendantsUri;
    }

    /**
     * Fetches object children from alfresco repository
     *
     * @return list of stdClass objects
     */
    public function getAlfrescoChildren( $offset = 0, $limit = 0 )
    {
        $name = __METHOD__ . '_' . $this->Id . '_' . $offset . '_' . $limit;
        if ( isset( $GLOBALS[$name] ) )
        {
            return $GLOBALS[$name];
        }

        try
        {
            $GLOBALS[$name] = nxcAlfresco::getChildren( $this->Id, $offset, $limit );
        }
        catch ( Exception $error )
        {
            return array();
        }

        return $GLOBALS[$name];
    }

    /**
     * Fetches object parent list from alfresco repository
     *
     * @param If true full parent list or just parent object otherwise
     * @return list of stdClass objects
     */
    public function getAlfrescoParentList( $fromRoot = true )
    {
        $name = __METHOD__ . '_' . $this->Id . $fromRoot;
        if ( isset( $GLOBALS[$name] ) )
        {
            return $GLOBALS[$name];
        }

        try
        {
            if ( $this->ParentUri )
            {
                $GLOBALS[$name] = nxcAlfresco::getFolderParent( $this->ParentUri, $fromRoot );
            }
            else
            {
                return array();
            }

        }
        catch ( Exception $error )
        {
            return array();
        }

        return $GLOBALS[$name];
    }

    /**
     * Fetches URI to current object like /root/path/to/current
     *
     * @return string URI
     */
    public function getURI()
    {
        $uri = $this->getParentURI();

        $enscaped = '/' . nxcAlfrescoUtils::urlEncode( $this->Title );
        return  $uri ? $uri . $enscaped : $enscaped;
    }

    /**
     * Fetches parent URI of current object
     *
     * @return string URI
     */
 /*   public function getParentURI()
    {
        $parentFolderList = $this->getAlfrescoParentList();

        $uri = '';
        for ( $i = count( $parentFolderList ) - 1; $i >= 0; $i-- )
        {
            if ( !is_object( $parentFolderList[$i] ) )
            {
                continue;
            }

            $uri .= '/' . nxcAlfrescoUtils::urlEncode( $parentFolderList[$i]->title ) ;
        }

        return $uri;
    }
   */
    /**
     * Fetches parent object id of current object
     *
     * @return string object id
     */
    public function getParentId()
    {
        $parentFolderList = $this->getAlfrescoParentList( false );

        return isset( $parentFolderList[0] ) ? $parentFolderList[0]->id : false;
    }

    /**
     * Removes current object from repository
     *
     * @return true if ok
     */
    public function remove()
    {
        return true;
    }

    /**
     * Stores current object in repository
     *
     * @return true if ok
     */
    public function store()
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
        if ( !$this->Id )
        {
            return false;
        }

        $this->setFields( nxcAlfresco::getProperties( $this->Id ) );

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
                                     'nxcAlfrescoBaseObject::attribute()' );
            }

            return $retVal;
        }

        eZDebug::writeError( "Attribute '$attr' does not exist", 'nxcAlfrescoBaseObject::attribute' );

        return $retVal;
    }
}
?>
