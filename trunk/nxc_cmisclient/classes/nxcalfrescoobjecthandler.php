<?php
/**
 * Definition of nxcAlfrescoObjectHandler class
 *
 * Created on: <25-Apr-2009 21:50:00 vd>
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
 * Definition of alfresco object container.
 * It stores alfresco object and provides some functionalities.
 *
 * @file nxcalfrescoobjecthandler.php
 */

//include_once( 'extension/nxc_alfresco/classes/nxcalfresco.php' );
//include_once( 'extension/nxc_alfresco/classes/nxcalfrescoutils.php' );

//include_once( 'extension/nxc_alfresco/classes/nxcalfrescofolder.php' );
//include_once( 'extension/nxc_alfresco/classes/nxcalfrescodocument.php' );

class nxcAlfrescoObjectHandler
{
    /**
     * Alfresco object: folder or document
     *
     * @var nxcAlfrescoBaseObject descendant
     */
    protected $Object = null;

    /**
     * Constructor
     * @param $key can be id or path to object
     */
    public function __construct( $key , $object = null )
    {
        if ( $key == null )
        {
            $this->Object = self::createObject($object);
        }
        else
        {
            $this->Object = self::fetch( $key );
        }
    }

    /**
     * Definition of function attributes
     */
    public static function definition()
    {
        return array( 'function_attributes' => array( 'children' => 'getChildren',
                                                      'can_create_classes' => 'getCreateClasses',
                                                      'bread_crumbs' => 'getBreadCrumbs',
                                                      'has_object' => 'hasObject' ) );
    }

    /**
     * @return true if the attribute \a $attr is part of the definition fields or function attributes.
     */
    public function hasAttribute( $attr )
    {
        $def = $this->definition();
        if ( !isset( $def['function_attributes'][$attr] ) )
        {
            if ( !$this->hasObject() )
            {
                return false;
            }

            $objectDef = $this->Object->definition();

            return isset( $objectDef['function_attributes'][$attr] );
        }

        return true;
    }

    /**
     * @return the attribute data for \a $attr, this is a member function depending on function attributes matched.
     */
    public function attribute( $attr )
    {
        $def = $this->definition();
        $attrFunctions = isset( $def['function_attributes'] ) ? $def['function_attributes'] : null;

        if ( isset( $attrFunctions[$attr] ) )
        {
            $functionName = $attrFunctions[$attr];
            $retVal = null;
            if ( method_exists( $this, $functionName ) )
            {
                $retVal = $this->$functionName();
            }
            else
            {
                eZDebug::writeError( 'Could not find function : "' . get_class( $this ) . '::' . $functionName . '()".',
                                     'nxcAlfrescoObjectHandler::attribute()' );
            }

            return $retVal;
        }
        else
        {
            if ( !$this->Object )
            {
                eZDebug::writeError( "Attribute '$attr' does not exist", 'nxcAlfrescoObjectHandler::attribute' );
                $attrValue = null;

                return $attrValue;
            }

            $objectDef = $this->Object->definition();
            $attrFunctions = isset( $objectDef['function_attributes'] ) ? $objectDef['function_attributes'] : null;
            $functionName = $attrFunctions[$attr];
            $retVal = null;

            if ( method_exists( $this->Object, $functionName ) )
            {
                $retVal = $this->Object->$functionName();
            }
            else
            {
                eZDebug::writeError( 'Could not find function : "' . get_class( $this->Object ) . '::' . $functionName . '()".',
                                     'nxcAlfrescoObjectHandler::attribute()' );
            }

            return $retVal;
        }
    }

    /**
     * Fetches alfresco object by \a $key.
     *
     * @param $key can be either id or path to object in repository.
     * @return nxcAlfrescoBaseObject descendant
     */
    public static function fetch( $key )
    {
        return self::createObject( nxcAlfresco::getProperties( $key ) );
    }

    /**
     * Creates new instance of current handler
     *
     * @param Can be either path to object in repository or object id
     * @return Instance of nxcAlfrescoObjectHandler
     */
    public static function instance( $key )
    {
        $name = __METHOD__ . '_' . $key;
        if ( !isset( $GLOBALS[$name] ) )
        {
            $GLOBALS[$name] = new nxcAlfrescoObjectHandler( $key );
        }

        return $GLOBALS[$name];
    }

    /**
     * Creates alfresco content object
     *
     * @param stdClass $object
     * @return nxcAlfrescoBaseObject descendant
     */
    public static function createObject( $object )
    {
        $alfrescoObject = null;
        if ( !( $object instanceof stdClass ) )
        {
            throw new Exception( 'Object is not instance of stdClass: ' . print_r( $object, true ) );
        }

        $className = 'nxcAlfresco' . ucfirst( $object->type );

        if ( !class_exists( $className ) )
        {
            throw new Exception( 'Class "' . $className . '" does not exist.' );
        }
        return new $className( $object );
    }

    /**
     * @return nxcAlfrescoBaseObject descendant
     */
    public function getObject()
    {
        return $this->Object;
    }

    /**
     * @return true if object from repository was fetched correctly
     */
    public function hasObject()
    {
        return $this->Object ? true : false;
    }

    /**
     * @return List of child of current object
     */

    public function getChildren( $offset = 0, $limit = 0 )
    {
        $object = $this->getObject();
        if ( !$object )
        {
            return false;
        }



        //$xml = nxcAlfrescoUtils::invokeService(  $object->getChildrenUri() . '?skipCount=' . $offset . '&maxItems=' . $limit ); //0.61
        if ( $object->getChildrenUri()  )
        {
            //$xml = nxcAlfrescoUtils::invokeService(  $object->getChildrenUri() ); //0.62
           /* if ( nxcAlfresco::getVersionSpecificProperty( 'getChildrenwithSkip' )  )
            {
                $xml = nxcAlfrescoUtils::invokeService(  $object->getChildrenUri() . '?skipCount=' . $offset . '&maxItems=' . $limit ); //0.61
            }
            else
            {
                $xml = nxcAlfrescoUtils::invokeService(  $object->getChildrenUri() );
            }*/
            $xml = nxcAlfrescoUtils::invokeService(  $object->getChildrenUri() );

        }
        else
        {
            return array();
        }
        $children = $xml ? nxcAlfrescoUtils::getEntries( nxcAlfrescoUtils::processXML( $xml, '//D:entry' ) ) : false;

        //$children = $object->getAlfrescoChildren( $offset, $limit );

        $name = __METHOD__ . '_' . $object->getId() . '_' . $offset . '_' . $limit;
        if ( isset( $GLOBALS[$name] ) )
        {
            return $GLOBALS[$name];
        }

        $GLOBALS[$name] = array();
        foreach ( $children as $child )
        {
            $GLOBALS[$name][] = self::createObject( $child );
        }

        return $GLOBALS[$name];
    }

    /**
     * Removes tree from repository
     */
    public static function removeTreeById( $objectID )
    {
        return nxcAlfresco::deteleTree( $objectID );
    }

    /**
     * Creates path list to current object
     *
     * @return Path list with uri and text
     */
    public function getBreadCrumbs( $browserView = 'alfresco/browser' )
    {
        $pathList = array();
        $object = $this->getObject();
        if ( !$object )
        {
            return $pathList;
        }

        $parentList = $object->getAlfrescoParentList();

        if ( $parentList )
        {
            for ( $i = count( $parentList ) - 1; $i >= 0; $i-- )
            {
                if ( !$parentList[$i] )
                {
                    continue;
                }


                $pathList[] = array( 'text' => urldecode( $parentList[$i]->title ),
                                     'url' => $browserView . '/(id)/' . urlencode( urlencode( $parentList[$i]->selfUri ) ) );

            }
        }

        // Add current object
        $pathList[] = array( 'text' => urldecode( $object->getTitle() ),
                             'url' => false );

        return $pathList;
    }

    public function getParentSelfUri( )
    {
        $parentSelfUri = "";
        $object = $this->getObject();
        if ( !$object )
        {
            return $parentSelfUri;
        }

        $parentList = $object->getAlfrescoParentList( false );

        if ( $parentList )
        {
            for ( $i = count( $parentList ) - 1; $i >= 0; $i-- )
            {
                if ( !$parentList[$i] )
                {
                    continue;
                }

                $parentSelfUri = $parentList[$i]->selfUri;

            }
        }

        return $parentSelfUri;
    }


    /**
     * Defines which classes can be instantiated
     */
    public static function getCreateClasses()
    {
        // Define classes that can be created in "Create here" feature
        $canCreateClasses = array( 'text' => 'Content', 'folder' => 'Space' );
        if ( ini_get( 'file_uploads' ) != 0 )
        {
            $canCreateClasses['file'] = 'File';
        }

        return $canCreateClasses;
    }

    /**
     * Defines alfresco class of current object based on getCreateClasses() i.e. it's content or space
     */
    public function getBaseClass()
    {
        if ( !$this->hasObject() )
        {
            return false;
        }

        $classList = self::getCreateClasses();
        $classID = $this->getObject()->getClassIdentifier();

        return isset( $classList[$classID] ) ? $classList[$classID] : $classList['file'];
    }

    /**
     * Searches text of objects in CMIS repository
     *
     * @param string keywords
     * @param int number of page
     * @param int limit items per page
     * @return list of nxcAlfrescoDocument objects
     */
    public static function openSearch( $searchText, $startPage = 1, $limit = 20 )
    {
        $items = nxcAlfresco::openSearch( $searchText, $startPage, $limit );

        $entries = isset( $items['entries'] ) ? $items['entries'] : array();
        $objectList = array();
        foreach ( $entries as $item )
        {
            $objectList[] = self::createObject( $item );
        }

        $items['entries'] = $objectList;
        return $items;
    }

    public static function querySearch( $searchText, $searchAllVersions = false, $includeAllAllowableActions = false, $includeRelationships = null, $startPage = 1, $limit = 20 )
    {
        $items = nxcAlfresco::query( $searchText, $searchAllVersions, $includeAllAllowableActions, $includeRelationships, $startPage, $limit );
        $entries = isset( $items['entries'] ) ? $items['entries'] : array();
        $objectList = array();
        foreach ( $entries as $item )
        {
            $objectList[] = self::createObject( $item );
        }

        $items['entries'] = $objectList;
        return $items;
    }

    /**
     * Defines is object container or not
     *
     * @return bool
     */
    public function isContainer()
    {
        if ( !$this->hasObject() )
        {
            return false;
        }

        return $this->getObject()->getDocType() == 'Space';
    }
}
?>
