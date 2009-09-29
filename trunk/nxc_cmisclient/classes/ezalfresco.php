<?php
/**
 * Definition of eZAlfresco class
 *
 * Created on: <06-Jul-2009 11:00:54 vd>
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
 * Handler to create eZ Alfresco objects in content tree.
 *
 * @file ezalfresco.php
 */

//include_once( 'extension/nxc_alfresco/classes/nxcalfrescoobjecthandler.php' );

class eZAlfresco
{

    /**
     * Fetches eZContentObject by \a $alfrescoID
     *
     * @return eZContentObject or false if failed
     */
    protected static function fetch( $alfrescoID, $parentNodeID, $classID )
    {
        if ( !is_numeric( $parentNodeID ) and !is_array( $parentNodeID ) )
        {
            return false;
        }

        $treeParameters = array( 'AttributeFilter'  => array( array( $classID . '/id', '=', $alfrescoID ) ),
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
     * @return string Parent node id where ezp alfresco objects are located
     */
    public static function getParentNodeID()
    {
        $alfrescoIni = eZINI::instance( 'alfresco.ini' );
        $contentIni = eZINI::instance( 'content.ini' );

        return ( $alfrescoIni->hasVariable( 'eZPublishSettings', 'ParentNodeID' ) and $alfrescoIni->variable( 'eZPublishSettings', 'ParentNodeID' ) != '' )
                        ? $alfrescoIni->variable( 'eZPublishSettings', 'ParentNodeID' )
                        : ( $contentIni->hasVariable( 'NodeSettings', 'MediaRootNode' ) ? $contentIni->variable( 'NodeSettings', 'MediaRootNode' ) : 43 );

    }

    /**
     * @return string Class identifier
     */
    public static function getClassIdentifier()
    {
        $alfrescoIni = eZINI::instance( 'alfresco.ini' );

        return $alfrescoIni->hasVariable( 'eZPublishSettings', 'ClassIdentifier' ) ? $alfrescoIni->variable( 'eZPublishSettings', 'ClassIdentifier' ) : 'alfresco_object';
    }

    /**
     * Returns eZContentObject by \a $alfrescoID.
     * If there is no eZ Alfresco object, need to create new one and return it.
     *
     * @return eZContentObject
     */
    public static function getContentObject( $alfrescoID )
    {
        $object = nxcAlfrescoObjectHandler::instance( $alfrescoID );

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
            throw new Exception( "Could not fetch class by identifier '$classIdentifier'" );
        }

        $contentObject = self::fetch( $alfrescoID, $parentNodeID, $classIdentifier );
        if ( $contentObject )
        {
            return $contentObject;
        }

        $contentObject = $class->instantiate();

        if ( !$contentObject )
        {
            throw new Exception( "Could not instatiate content object by class identifier '$classIdentifier'" );
        }

        $version = $contentObject->attribute( 'current_version' );
        $objectID = $contentObject->attribute( 'id' );

        self::updateAttributes( $contentObject, array( 'id' => $alfrescoID,
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
    public static function updateAttributes( &$object, $list )
    {
        $attributeList = $object->contentObjectAttributes();

        foreach ( array_keys( $attributeList ) as $key )
        {
            $result = false;
            $attr = $attributeList[$key];
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
                // @TODO: Pass 'data_text' by param
                $attr->setAttribute( 'data_text', $result );
                $attr->store();
            }
        }
    }

    /**
     * Updates content object by \a $alfrescoObject
     */
    public static function update( $alfrescoObject )
    {
        if ( !$alfrescoObject )
        {
            return false;
        }

        $classIdentifier = self::getClassIdentifier();
        $parentNodeID = self::getParentNodeID();

        $contentObject = self::fetch( $alfrescoObject->getId(), $parentNodeID, $classIdentifier );

        if ( !$contentObject )
        {
            return false;
        }

        self::updateAttributes( $contentObject, array( 'title' => $alfrescoObject->getTitle() ) );

        $contentObject->setName( $alfrescoObject->getTitle() );
        $contentObject->store();

        $node = eZContentObjectTreeNode::fetchByContentObjectID( $contentObject->attribute( 'id' ) );
        if ( isset( $node[0] ) )
        {
            $node[0]->updateSubTreePath();
        }

        return true;
    }

    /**
     * Removes content object by \a $alfrescoObject
     */
    public static function remove( $alfrescoID )
    {
        $classIdentifier = self::getClassIdentifier();
        $parentNodeID = self::getParentNodeID();

        $contentObject = self::fetch( $alfrescoID, $parentNodeID, $classIdentifier );

        if ( !$contentObject )
        {
            return false;
        }

        eZContentOperationCollection::deleteObject( array( $contentObject->attribute( 'main_node_id' ) ), false );

        return true;
    }
}

?>
