<?php
/**
 * Definition of nxcCMISObjectHandler class
 *
 * Created on: <25-Apr-2009 21:50:00 vd>
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
 * Definition of CMIS object container.
 * It stores CMIS object and provides some functionalities.
 *
 * @file nxccmisobjecthandler.php
 */

//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisutils.php' );
//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/objects/nxccmisfolder.php' );
//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/objects/nxccmisdocument.php' );

class nxcCMISObjectHandler
{
    /**
     * CMIS object: folder or document
     *
     * @var nxcCMISBaseObject descendant
     */
    protected $Object = null;

    /**
     * Constructor
     *
     * @param nxcCMISBaseObject
     */
    protected function __construct( $object )
    {
        if ( $object instanceof nxcCMISBaseObject )
        {
            $this->Object = $object;
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
                                     __METHOD__ );
            }

            return $retVal;
        }
        else
        {
            if ( !$this->Object )
            {
                eZDebug::writeError( "Attribute '$attr' does not exist", __METHOD__ );
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
                                     __METHOD__ );
            }

            return $retVal;
        }
    }

    /**
     * Fetches CMIS object by \a $uri.
     *
     * @param $uri
     * @return nxcCMISBaseObject descendant
     */
    public static function fetch( $uri )
    {
        $response = nxcCMISUtils::invokeService( $uri );
        $entry =  nxcCMISUtils::fetchEntry( $response );

        return self::createObject( $entry );
    }

    /**
     * Creates new instance of current handler
     *
     * @param $uri Self uri of CMIS object
     * @return Instance of nxcCMISObjectHandler
     */
    public static function instance( $uri )
    {
        $name = __METHOD__ . '_' . $uri;
        if ( isset( $GLOBALS[$name] ) )
        {
            return $GLOBALS[$name];
        }

        $object = false;

        if ( !$uri )
        {
            $rootFolderId = nxcCMISUtils::getRootFolderId();
            // Check if root folder id is not uri of root folder
            // @TODO Remove it after supported servers go to 0.62
            if ( strpos( $rootFolderId, 'http' ) === false )
            {
                $repositoryInfo = nxcCMISUtils::getRepositoryInfo();
                if ( $repositoryInfo->children and !empty( $repositoryInfo->children ) )
                {
                    $object = self::createObjectByBaseType( 'folder' );
                    $object->setChildrenUri( $repositoryInfo->children );
                    $object->setTitle( $repositoryInfo->repositoryName );
                    $object->setSummary( $repositoryInfo->repositoryDescription );
                }
            }
            else
            {
                $uri = $rootFolderId;
            }
        }

        if ( !$object and $uri )
        {
            $response = nxcCMISUtils::invokeService( $uri );
            $entry = nxcCMISUtils::fetchEntry( $response ) ;
            $object = self::createObject( $entry );
        }

        $GLOBALS[$name] = new self( $object );

        return $GLOBALS[$name];
    }

    /**
     * Creates object by \a $baseType
     *
     * @return nxcCMISBaseObject descendant
     */
    public static function createObjectByBaseType( $baseType )
    {
        $className = 'nxcCMIS' . ucfirst( strtolower( $baseType ) );

        if ( !class_exists( $className ) )
        {
            throw new Exception( ezi18n( 'cmis', "Class '%class%' does not exist", null, array( '%class%' => $className ) ) );
        }

        return new $className();
    }

    /**
     * Creates CMIS content object
     *
     * @param SimpleXMLElement $object
     * @return nxcCMISBaseObject descendant
     */
    public static function createObject( $entry )
    {
        if ( !$entry )
        {
            return false;
        }

        $baseTypeValue = nxcCMISUtils::getVersionSpecificValue( 'BaseType' );
        $baseType = (string) nxcCMISUtils::getXMLValue( $entry, nxcCMISUtils::getVersionSpecificProperty( $baseTypeValue ) );

        if ( empty( $baseType ) )
        {
            throw new Exception( ezi18n( 'cmis', "Could not fetch 'BaseType'" ) );
        }

        $object = self::createObjectByBaseType( nxcCMISUtils::removeNamespaces( $baseType ) );
        $object->setFields( $entry );

        return $object;
    }

    /**
     * @return nxcCMISBaseObject descendant
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
     * Provides children list
     *
     * @return array List of nxcCMISBaseObject descedants
     * @note We use fetching of children here because need to instance objects.
     *       Folder can contain documents or another objects and it must not know about definitions of another classes.
     */
    public function getChildren( $offset = 0, $limit = 0 )
    {
        $object = $this->getObject();
        if ( !$object or !$object->isContainer() )
        {
            return array();
        }

        $name = __METHOD__ . '_' . $object->getId() . '_' . $offset . '_' . $limit;
        if ( isset( $GLOBALS[$name] ) )
        {
            return $GLOBALS[$name];
        }

        $list = array();

        $children = $object->getChildren( $offset, $limit );
        if ( $children )
        {
            foreach ( $children as $child )
            {
                $object = self::createObject( $child );
                $list[] = new self( $object );
            }

            $GLOBALS[$name] = $list;
        }

        return $list;
    }

    /**
     * Creates path list to current object
     *
     * @return Path list with uri and text
     */
    public function getBreadCrumbs( $browserView = 'cmis_client/browser' )
    {
        $pathList = array();
        $object = $this->getObject();
        if ( !$object )
        {
            return $pathList;
        }

        if ( !empty( $browserView ) )
        {
            $browserView .= '/';
        }

        $parentList = $object->getParentList();

        if ( count( $parentList ) )
        {
            for ( $i = count( $parentList ) - 1; $i >= 0; $i-- )
            {
                if ( !$parentList[$i] )
                {
                    continue;
                }

                $uri = nxcCMISUtils::getEncodedUri( nxcCMISUtils::getHostlessUri( nxcCMISUtils::getLinkUri( $parentList[$i], 'self' ) ) );
                $pathList[] = array( 'text' => urldecode( (string) nxcCMISUtils::getValue( $parentList[$i], 'title' ) ),
                                     'url' => $browserView . $uri );

            }
        }

        // Add current object
        $pathList[] = array( 'text' => urldecode( $object->getTitle() ),
                             'url' => false );

        return $pathList;
    }

    /**
     * Defines which classes can be instantiated
     */
    public static function getCreateClasses()
    {
        // Define classes that can be created in "Create here" feature
        $canCreateClasses = array( 'content' => ezi18n( 'cmis', 'Content' ), 'folder' => ezi18n( 'cmis', 'Folder' ) );
        if ( ini_get( 'file_uploads' ) != 0 )
        {
            $canCreateClasses['file'] = ezi18n( 'cmis', 'File' );
        }

        return $canCreateClasses;
    }

    /**
     * Defines CMIS class of current object based on getCreateClasses() i.e. it's content or folder
     */
    public function getBaseClass()
    {
        if ( !$this->hasObject() )
        {
            return false;
        }

        $classList = self::getCreateClasses();
        $classID = $this->getObject()->getClassIdentifier();

        return isset( $classList[$classID] ) ? $classID : 'file';
    }

    /**
     * Makes query searching
     *
     * @return array of nxcCMISObjectHandler
     */
    public static function search( $searchText, $limit = 20, $startPage = 1, $searchAllVersions = false, $includeAllAllowableActions = false, $includeRelationships = false )
    {
        $repositoryInfo = nxcCMISUtils::getRepositoryInfo();
        if ( !$repositoryInfo->query or empty( $repositoryInfo->query ) )
        {
            return array();
        }

        $uri = $repositoryInfo->query;

        $doc = nxcCMISUtils::createDocument();
        $root =  nxcCMISUtils::createRootNode( $doc, 'cmis:query' );

        $doc->appendChild( $root );
        // @TODO: Hardcoded query
        $statement = $doc->createElement( 'cmis:statement', "select * from document where contains ( '$searchText' )" );
        $root->appendChild( $statement );
        $allVersions = $doc->createElement( 'cmis:searchAllVersions', $searchAllVersions ? 'true' : 'false' );
        $root->appendChild( $allVersions );
        $pageSize = $doc->createElement( 'cmis:pageSize', $limit );
        $root->appendChild( $pageSize );
        $skipCount = $doc->createElement( 'cmis:skipCount', $startPage );
        $root->appendChild( $skipCount );
        $actions = $doc->createElement( 'cmis:returnAllowableActions', $includeAllAllowableActions ? 'true' : 'false' );
        $root->appendChild( $actions );

        $xml = $doc->saveXML();

        $response = nxcCMISUtils::invokeService( $uri, 'POST', nxcCMISUtils::createHeaders( strlen( $xml ), 'application/cmisquery+xml' ), $xml );

        $entries = nxcCMISUtils::fetchEntries( $response );

        $objectList = array();
        foreach ( $entries as $entry )
        {
            $object = self::createObject( $entry );
            $objectList[] = new self( $object );
        }

        return $objectList;
    }
}
?>
