<?php
/**
 * Definition of eZCMISObject class
 *
 * Created on: <06-Jul-2009 11:00:54 vd>
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
 * Handler to create eZ CMIS objects in content tree.
 *
 * @file ezcmisobject.php
 */

//include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisobjecthandler.php' );

class eZCMISObject
{
    /**
     * Fetches eZContentObject by \a $uri to fetch cmis entry
     *
     * @return eZContentObject or false if failed
     */
    protected static function fetch( $uri, $parentNodeID, $classID )
    {
        if ( !is_numeric( $parentNodeID ) and !is_array( $parentNodeID ) )
        {
            return false;
        }

        $treeParameters = array( 'AttributeFilter'  => array( array( $classID . '/uri', '=', $uri ) ),
                                 'ClassFilterArray' => array( $classID ),
                                 'MainNodeOnly'     => true,
                                 'AsObject'         => true,
                                 );

        $children = eZContentObjectTreeNode::subTreeByNodeID( $treeParameters, $parentNodeID );

        if ( !$children )
        {
            return false;
        }

        return ( isset( $children[0] ) and is_object( $children[0] ) ) ? $children[0]->object() : false;
    }

    /**
     * Provides parent node id
     *
     * @return string Parent node id where ezp cmis objects are located
     */
    public static function getParentNodeID()
    {
        $cmisIni = eZINI::instance( 'cmis.ini' );
        $contentIni = eZINI::instance( 'content.ini' );

        return ( $cmisIni->hasVariable( 'eZPublishSettings', 'ParentNodeID' ) and $cmisIni->variable( 'eZPublishSettings', 'ParentNodeID' ) != '' )
                        ? $cmisIni->variable( 'eZPublishSettings', 'ParentNodeID' )
                        : ( $contentIni->hasVariable( 'NodeSettings', 'MediaRootNode' ) ? $contentIni->variable( 'NodeSettings', 'MediaRootNode' ) : 43 );

    }

    /**
     * @return string Class identifier
     */
    public static function getClassIdentifier()
    {
        $cmisIni = eZINI::instance( 'cmis.ini' );

        return $cmisIni->hasVariable( 'eZPublishSettings', 'ClassIdentifier' ) ? $cmisIni->variable( 'eZPublishSettings', 'ClassIdentifier' ) : 'cmis_object';
    }

    /**
     * Returns eZContentObject by \a $cmisID.
     * If there is no eZ CMIS object, need to create new one and return it.
     *
     * @param string Encoded uri
     * @return eZContentObject
     */
    public static function getContentObject( $uri )
    {
        $object = nxcCMISObjectHandler::instance( nxcCMISUtils::getDecodedUri( $uri ) );

        if ( !$object->hasObject() )
        {
            return null;
        }

        $classIdentifier = self::getClassIdentifier();
        $parentNodeID = self::getParentNodeID();

        $title = $object->getObject()->getTitle();
        $class = eZContentClass::fetchByIdentifier( $classIdentifier );
        if ( !$class )
        {
            throw new Exception( ezpI18n::tr( 'cmis', "Could not fetch class by identifier '%class%'", null, array( '%class%' => $classIdentifier ) ) );
        }

        $contentObject = self::fetch( $uri, $parentNodeID, $classIdentifier );
        if ( $contentObject )
        {
            return $contentObject;
        }

        $contentObject = $class->instantiate();

        if ( !$contentObject )
        {
            throw new Exception( ezpI18n::tr( 'cmis', "Could not instatiate content object by class identifier '%class%'", null, array( '%class%' => $classIdentifier ) ) );
        }

        $version = $contentObject->attribute( 'current_version' );
        $objectID = $contentObject->attribute( 'id' );

        self::updateAttributes( $contentObject, array( 'uri' => $uri,
                                                       'title' => $title ) );
        $contentObject->setName( $title );
        $contentObject->store();

        $nodeAssignment = eZNodeAssignment::create( array( 'contentobject_id' => $objectID,
                                                           'contentobject_version' => $version,
                                                           'parent_node' => $parentNodeID,
                                                           'is_main' => 1 ) );
        $nodeAssignment->store();

        $operationResult = eZOperationHandler::execute( 'content', 'publish', array( 'object_id' => $objectID,
                                                                                     'version' => $version ) );

        return $contentObject;
    }

    /**
     * Updates attributes of \a $object by \a $list of attribute values
     */
    public static function updateAttributes( $object, $list )
    {
        $attributeList = $object->contentObjectAttributes();

        foreach ( array_keys( $attributeList ) as $key )
        {
            $result = false;
            $attr = $attributeList[$key];
            $dataType = $attr->dataType();
            if ( !$dataType or !$dataType->isSimpleStringInsertionSupported() )
            {
                continue;
            }

            foreach ( $list as $attrName => $value )
            {
                if ( $attr->contentClassAttributeIdentifier() == $attrName )
                {
                    $result = $value;
                    break;
                }
            }

            if ( $result )
            {
                $returned = '';
                $dataType->insertSimpleString( $object, $object->currentVersion(), false, $attr, $result, $returned );
                $attr->sync();
            }
        }
    }

    /**
     * Updates content object by \a $cmisObject
     */
    public static function update( $cmisObject )
    {
        if ( !$cmisObject )
        {
            return false;
        }

        $classIdentifier = self::getClassIdentifier();
        $parentNodeID = self::getParentNodeID();

        $contentObject = self::fetch( $cmisObject->getSelfUri(), $parentNodeID, $classIdentifier );

        if ( !$contentObject )
        {
            return false;
        }

        self::updateAttributes( $contentObject, array( 'title' => $cmisObject->getTitle() ) );

        $contentObject->setName( $cmisObject->getTitle() );

        $contentObject->store();

        $node = $contentObject->mainNode();
        if ( $node )
        {
            $node->updateSubTreePath();
        }

        return true;
    }

    /**
     * Removes content object by \a $cmisObject
     */
    public static function remove( $uri )
    {
        $classIdentifier = self::getClassIdentifier();
        $parentNodeID = self::getParentNodeID();

        $contentObject = self::fetch( $uri, $parentNodeID, $classIdentifier );

        if ( !$contentObject )
        {
            return false;
        }

        eZContentOperationCollection::deleteObject( array( $contentObject->attribute( 'main_node_id' ) ), false );

        return true;
    }
}

?>