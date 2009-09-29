<?php
/**
 * Definition of nxcAlfresco class
 *
 * Created on: <18-Apr-2009 11:00:54 vd>
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
 * This class contains API to access to alfresco CMIS
 *
 * @file nxcalfresco.php
 */

//include_once( 'extension/nxc_alfresco/classes/nxcalfrescoutils.php' );

class nxcAlfresco
{

    /************************
     * Repository servicies *
     ************************/

    /**
     *
     */
    public static function getRepositories()
    {
        return null;
    }

    /**
     * This service is used to retrieve information about the CMIS repository and the capabilities it supports.
     */
    public static function getRepositoryInfo()
    {

        $response = nxcAlfrescoUtils::invokeService( nxcAlfrescoUtils::getEndPoint(), 'basic' );
        $repoInfo = nxcAlfrescoUtils::processXML( $response, '/app:service/app:workspace' ); // 0.61

        $cmisVersion = nxcAlfrescoUtils::processXML( $response, '//cmis:cmisVersionSupported' );
        if ( !$cmisVersion )
        {
            $cmisVersion = nxcAlfrescoUtils::processXML( $response, '//cmis:cmisVersionsSupported' );
        }

        $cmisVersion = (string) $cmisVersion[0];

        $http = eZHTTPTool::instance();
        $http->setSessionVariable( 'CmisVersion', $cmisVersion );
        $repoInfo = nxcAlfrescoUtils::processXML( $response, self::getVersionSpecificProperty('repositoryInfoXMLElement') ); //multiversion

        $collectionRootChildren = nxcAlfrescoUtils::processXML( $response, self::getVersionSpecificProperty('rootChildren') ); //multiversion
        $collectionTypes = nxcAlfrescoUtils::processXML( $response, self::getVersionSpecificProperty('typesCollection') ); //multiversion
        $collectionQuery = nxcAlfrescoUtils::processXML( $response, self::getVersionSpecificProperty('queryCollection') ); //multiversion

        $repository = new stdClass();

        if ( !$repoInfo )
        {
            throw new Exception( 'Unable to get repository information' );
        }

        $repository->repositoryId = (string) nxcAlfrescoUtils::getXMLvalue( $repoInfo[0], 'cmis:repositoryId' );
        $repository->repositoryName = (string) nxcAlfrescoUtils::getXMLvalue( $repoInfo[0], 'cmis:repositoryName' );
        $repository->repositoryDescription = (string) nxcAlfrescoUtils::getXMLvalue( $repoInfo[0], 'cmis:repositoryDescription' );
        $repository->vendorName = (string) nxcAlfrescoUtils::getXMLvalue( $repoInfo[0], 'cmis:vendorName' );
        $repository->productName = (string) nxcAlfrescoUtils::getXMLvalue( $repoInfo[0], 'cmis:productName' );
        $repository->productVersion = (string) nxcAlfrescoUtils::getXMLvalue( $repoInfo[0], 'cmis:productVersion' );
        $repository->rootFolderId = (string) nxcAlfrescoUtils::getXMLvalue( $repoInfo[0], 'cmis:rootFolderId' );
        $repository->childrens = isset( $collectionRootChildren[0] ) ? (string) nxcAlfrescoUtils::getXMLAttribute( $collectionRootChildren[0], 'href' ) : '';
        $repository->types = isset( $collectionTypes[0] ) ? (string) nxcAlfrescoUtils::getXMLAttribute( $collectionTypes[0], 'href' ) : '';
        $repository->query =  isset( $collectionQuery[0] ) ? (string) nxcAlfrescoUtils::getXMLAttribute( $collectionQuery[0], 'href' ) : '';

        $response = nxcAlfrescoUtils::invokeService( $repository->types , 'basic' );

        $keyList = nxcAlfrescoUtils::processTypesXML( $response,  self::getVersionSpecificProperty('typesTypeID')  ); ///multiversion
        $valueList = nxcAlfrescoUtils::processTypesXML( $response, self::getVersionSpecificProperty('typesBaseType') ); ///multiversion


        $cmisTypes = array();
        foreach( $keyList as $keyentry => $key )
        {
            $keystr = (string) $key;
            $curKey = strtolower( str_replace( 'cmis:', '', $keystr ) );
            $cmisTypes[ $curKey ] = str_replace( 'cmis:', '',  (string) $valueList[$keyentry] );
        }

        if ( !$http->hasSessionVariable( 'CmisTypeKeyArray' ) )
        {
            $http->setSessionVariable( 'CmisTypeKeyArray',serialize( $cmisTypes) );
        }
        if ( !$http->hasSessionVariable( 'RepositoryInfo' ) )
        {
            $http->setSessionVariable( 'RepositoryInfo', serialize( $repository) );
        }

        return $repository;
    }

    /**
     * @return string Root folder of repository
     */
    public static function getRootFolder()
    {
        $repository = self::getRepositoryInfo();
        return  $repository;
    }

    /**
     * Returns the list of all types in the repository.
     */
    public static function getTypes()
    {
        return null;
    }

    /**
     * Gets the definition for specified object type
     */
    public function getTypeDefinition( $typeId )
    {
        return null;
    }

    /************************
     * Navigation servicies *
     ************************/

    /**
     * Gets the list of descendant objects contained at one or more levels in the tree rooted at the specified folder.
     * Only the filter-selected properties associated with each object are returned. The content-stream is not returned.
     */
    public function getDescendants( $folderId )
    {
        return null;
    }

    /**
     * Gets the list of child objects contained in the specified folder.
     * Only the filter-selected properties associated with each object are returned.
     * The content-streams of documents are not returned.
     */
    public static function getChildren( $folderId, $offset = 0, $limit = 0 )
    {
       /* $alfrescoFolderId = nxcAlfrescoUtils::objectId( $folderId );

        $url = isset( $alfrescoFolderId['noderef_url'] ) ? '/api/node/' . $alfrescoFolderId['noderef_url'] : ( isset( $alfrescoFolderId['url'] ) ? $alfrescoFolderId['url'] : false );
        if ( $url === false )
        {
            throw new Exception( 'Unable to find destination folder: ' . $folderId );
        }
         */

        if ( self::getVersionSpecificProperty( 'getChildrenwithSkip' )  )
        {
            $xml = nxcAlfrescoUtils::invokeService( $folderId .'?skipCount=' . $offset . '&maxItems=' . $limit );
        }
        else
        {
            $xml = nxcAlfrescoUtils::invokeService( $folderId );
        }

        return $xml ? nxcAlfrescoUtils::getEntries( nxcAlfrescoUtils::processXML( $xml, '//D:entry' ) ) : false;
    }

    /**
     * Returns the parent folder object, and optionally all ancestor folder objects, above a specified folder object.
     *
     * @param ID folderId: Source folder to get the parent or ancestors of.
     * @param (Optional) Bool returnToRoot: If false, return only the immediate parent of the folder.
     *        If true, return an ordered list of all ancestor folders from the specified folder to the root folder. Default=False.
     */
    public static function getFolderParent( $folderId, $returnToRoot = false )
    {
       /* $alfrescoFolderId = nxcAlfrescoUtils::objectId( $folderId );

        $url = isset( $alfrescoFolderId['noderef_url'] ) ? '/api/node/' . $alfrescoFolderId['noderef_url'] : ( isset( $alfrescoFolderId['url'] ) ? $alfrescoFolderId['url'] : false );
        if ( $url === false )
        {
            throw new Exception( 'Unable to find destination folder: ' . $folderId );
        }
         */

        $returnToRootStr = $returnToRoot ? 'true' : 'false';

        $xml = nxcAlfrescoUtils::invokeService( $folderId  );
       // $xml = nxcAlfrescoUtils::invokeService( $folderId . '?returnToRoot=' . $returnToRootStr );
        //$xml = nxcAlfrescoUtils::invokeService( $folderId  );

        return $xml ? nxcAlfrescoUtils::getEntries( nxcAlfrescoUtils::processXML( $xml, '//D:entry' ) ) : false;
    }

    /**
     * Returns the parent folders for the specified non-folder, fileable object
     *
     * @param ID objectId: ID of a non-folder, fileable object.
     */
    public static function getObjectParents( $folderId )
    {
        /*$alfrescoFolderId = nxcAlfrescoUtils::objectId( $folderId );

        $url = isset( $alfrescoFolderId['noderef_url'] ) ? '/api/node/' . $alfrescoFolderId['noderef_url'] : ( isset( $alfrescoFolderId['url'] ) ? $alfrescoFolderId['url'] : false );
        if ( $url === false )
        {
            throw new Exception( 'Unable to find destination object: ' . $folderId );
        }
          */
        $xml = nxcAlfrescoUtils::invokeService( $folderId );

        return $xml ? nxcAlfrescoUtils::getEntries( nxcAlfrescoUtils::processXML( $xml, '//D:entry' ) ) : false;
    }

    /**
     * Gets the list of documents that are checked out that the user has access to.
     * Most likely this will be the set of documents checked out by the user. Content-streams are not returned.
     */
    public static function getCheckedoutDocuments( $folderId )
    {
        return null;
    }

    /*******************
     * Object services *
     *******************/

    /**
     * Creates a document object of the specified type.
     *
     * @param Collection properties
     * @param ID folderId: Parent folder for this new document
     * @param (Optional) ContentStream contentStream
     * @param ID typeId: Document type
     * @param (Optional) Enum versioningState: CheckedOut, CheckedInMinor, CheckedInMajor (Default)
     *
     * @return ID objectId: Id of the created document object
     */
    public static function createDocument( $properties = array(), $folderId = null, $content = null, $objectTypeId = 'document', $versioningState = null )
    {
        /*$parentFolderId = nxcAlfrescoUtils::objectId( $folderId );

        if ( !isset( $parentFolderId['noderef_url'] ) )
        {
            throw new Exception( 'Unable to find destination object: ' . $folderId );
        } */

        $title = isset( $properties['title'] ) ? $properties['title'] : 'New document';
        $summary = isset( $properties['summary'] ) ? $properties['summary'] : '';
        $contentType = isset( $properties['content-type'] ) ? $properties['content-type'] : 'text/plain';

        $postvars = '<?xml version="1.0" encoding="utf-8"?>' .
                    '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app" xmlns:cmis="http://docs.oasis-open.org/ns/cmis/core/200901" xmlns:alf="http://www.alfresco.org">' .
                    '<title>' . $title . '</title>' .
                    '<summary>' . $summary . '</summary>' .
                    '<content type="' . $contentType . '">' . base64_encode( $content ) . '</content>' .
                    '<cmis:object>' .
                    '<cmis:properties>' .
                    '<cmis:propertyId cmis:name="ObjectTypeId">' .
                    '<cmis:value>' . $objectTypeId . '</cmis:value>' .
                    '</cmis:propertyId>';
        
        if ($_SESSION['knowledgeTreeDMS']) {
                    $postvars .= '<cmis:propertyId cmis:name="ObjectId">' .
                    '<cmis:value>' . $folderId . '</cmis:value>' .
                    '</cmis:propertyId>';
        }
        
        $postvars .= '</cmis:properties>' .
                    '</cmis:object>' .
                    '</entry>';


        $header = array();
        $header[] = 'Content-type: application/atom+xml;type=entry';
        $header[] = 'Content-length: ' . strlen( $postvars );
        $header[] = 'MIME-Version: 1.0';


        $xml = nxcAlfrescoUtils::invokeService( $folderId, 'basic', $header, 'CUSTOM-POST', $postvars );
        $entry = $xml ? nxcAlfrescoUtils::processXML( $xml, '/D:entry' ) : false;
        $entry = isset( $entry[0][0] ) ? $entry[0][0] : false;
        $linkSelfXML = $entry->xpath( '*[@rel="self"]' );
        $objectId = isset( $linkSelfXML[0] ) ? (string) nxcAlfrescoUtils::getXMLAttribute($linkSelfXML[0] , 'href') : false;

        //$objectId = $entry ? (string) $entry->id  : false;

        return  $objectId ;
    }

    /**
     * Creates a folder object of the specified type
     *
     * @param Collection properties
     * @param ID folderId: Parent folder for this new folder
     * @param ID typeId: Folder type
     */
    public static function createFolder( $properties, $folderId, $typeId = 'folder' )
    {
       /* $parentFolderId = nxcAlfrescoUtils::objectId( $folderId );

        if ( !isset( $parentFolderId['noderef_url'] ) )
        {
            throw new Exception( 'Unable to find destination folder: ' . $folderId );
        }
         */
        $title = isset( $properties['title'] ) ? $properties['title'] : 'New folder';
        $summary = isset( $properties['summary'] ) ? $properties['summary'] : '';

        $postvars = '<?xml version="1.0" encoding="utf-8"?>' .
                    '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app" xmlns:cmis="http://docs.oasis-open.org/ns/cmis/core/200901" xmlns:alf="http://www.alfresco.org">' .
                    '<title>' . $title . '</title>' .
                    '<summary>' . $summary . '</summary>' .
                    '<cmis:object>' .
                    '<cmis:properties>' .
                    '<cmis:propertyId cmis:name="ObjectTypeId">' .
                    '<cmis:value>' . $typeId . '</cmis:value>' .
                    '</cmis:propertyId>';

        if ($_SESSION['knowledgeTreeDMS']) {
        	$postvars .= '<cmis:propertyId cmis:name="ObjectId">' .
                    '<cmis:value>' . $folderId . '</cmis:value>' .
                    '</cmis:propertyId>';
        }

        $postvars .= '</cmis:properties>' .
                    '</cmis:object>' .
                    '</entry>';

        $header = array();
        $header[] = 'Content-type: application/atom+xml;type=entry';
        $header[] = 'Content-length: ' . strlen( $postvars );
        $header[] = 'MIME-Version: 1.0';
        $xml = nxcAlfrescoUtils::invokeService( $folderId , 'basic', $header, 'CUSTOM-POST', $postvars );

        $entry = $xml ? nxcAlfrescoUtils::processXML( $xml, '/D:entry' ) : false;

        $entry = isset( $entry[0][0] ) ? $entry[0][0] : false;

        $objectId = $entry ? (string) $entry->id : false;

        return $objectId;
    }

    /**
     *
     */
    public static function createRelationship( $repositoryId, $typeId, $properties, $sourceObjectId, $targetObjectId)
    {
        return null;
    }

    /**
     *
     */
    public static function createPolicy( $repositoryId, $typeId, $properties, $folderId)
    {
        return null;
    }

    /**
     *
     */
    public static function getAllowableActions( $repositoryId, $objectId, $asUser = null )
    {
        return null;
    }

    /**
     * Returns the properties of an object, and optionally the operations that the user is allowed to perform on the object
     */
    public static function getProperties( $objectId )
    {
        //$objectId = nxcAlfrescoUtils::objectId( $objectId );
        //$xml = nxcAlfrescoUtils::invokeService( '/api/node/' . $objectId['noderef_url'] );
        $xml = nxcAlfrescoUtils::invokeService( $objectId );

        $entries = $xml ? nxcAlfrescoUtils::getEntries( nxcAlfrescoUtils::processXML( $xml, '//D:entry' ) ) : false;

        if ( !isset( $entries[0] ) )
        {
            throw new Exception( 'Unknown objectId: ' . print_r( $objectId, true ) );
        }

        return $entries[0];
    }

    /**
     * The service returns the content-stream for a document. This is the only service that returns content-stream.
     */
    public static function getContentStream( $objectId )
    {
        return nxcAlfrescoUtils::invokeService( $objectId );
    }

    /**
     * This service updates properties of the specified object. As per the data model, content-streams are not properties
     *
     * @note not all properties can be updated. Just the title.
     */
    public static function updateProperties( $objectId, $properties = array(), $content = false, $changeToken = null )
    {
       /* $alfrescoObjectId = nxcAlfrescoUtils::objectId( $objectId );
        if ( !isset( $alfrescoObjectId['noderef_url'] ) )
        {
            throw new Exception( 'Unable to find destination object: ' . $objectId );
        }*/

        $title = isset( $properties['title'] ) ? $properties['title'] : 'Updated object';
        $contentType = isset( $properties['content-type'] ) ? ' type="' . $properties['content-type'] . '"' : '';
        $contentStr = $content ? '<content' . $contentType .'>' . $content . '</content>' : '';

        $postvars = '<?xml version="1.0" encoding="utf-8"?>' .
                    '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app" xmlns:cmis="http://docs.oasis-open.org/ns/cmis/core/200901" xmlns:alf="http://www.alfresco.org">' .
                    '<title>' . $properties['title'] . '</title>' .
                    $contentStr .
                    '</entry>';

        $header = array();
        $header[] = 'Content-type: application/atom+xml;type=entry';
        $header[] = 'Content-length: ' . strlen( $postvars );
        $header[] = 'MIME-Version: 1.0';
        $xml = nxcAlfrescoUtils::invokeService( $objectId, 'basic', $header, 'CUSTOM-PUT', $postvars );

        $entry = $xml ? nxcAlfrescoUtils::processXML( $xml, '/D:entry' ) : false;
        $entry = isset( $entry[0][0] ) ? $entry[0][0] : false;
        $objectId = $entry ? (string) $entry->id  : false;

        return $objectId;
    }

    /**
     *
     */
    public static function moveObject( $objectId, $targetFolderId, $sourceFolderId = null )
    {
        return null;
    }

    /**
     * Deletes specified object
     *
     * @param ID objectId
     */
    public static function deleteObject( $objectId, $includeChildren = true )
    {
        /*$alfrescoObjectId = nxcAlfrescoUtils::objectId( $objectId );
        if ( !isset( $alfrescoObjectId['noderef_url'] ) )
        {
            throw new Exception( 'Unable to find destination object: ' . $objectId );
        } */

        $includeChildrenStr = $includeChildren ? 'True' : 'False';
        if ( strstr($objectId, '?') )
        {
            $url = $objectId . '&includeChildren=' . $includeChildrenStr;
        }
        else
        {
            $url = $objectId . '?includeChildren=' . $includeChildrenStr;
        }
        
        $header = array();
        $method = 'CUSTOM-DELETE';
        $op = 'basic';
        $xml = nxcAlfrescoUtils::invokeService( $url , $op, $header, $method);

        return !is_bool( $xml ) ? nxcAlfrescoUtils::getEntries( nxcAlfrescoUtils::processXML( $xml, '//D:entry' ) ) : $xml;
    }

    /**
     * Deletes the tree rooted at specified folder (including that folder)
     *
     * @param ID folderId
     * @param Enum unfileNonfolderObjects:
     *        o Unfile ? unfile all non-folder objects from folders in this tree.
     *          They may remain filed in other folders, or may become unfiled.
     *        o DeleteSingleFiled ? delete non-folder objects filed only in this tree,
     *          and unfile the others so they remain filed in other folders.
     *        o Delete ? delete all non-folder objects in this tree (Default)
     * @param (Optional) Bool continueOnFailure: False (Default)
     */
    public static function deteleTree( $folderId, $unfileNonfolderObjects = 'Delete', $continueOnFailure = true )
    {
      /*  $alfrescoObjectId = nxcAlfrescoUtils::objectId( $folderId );
        if ( !isset( $alfrescoObjectId['noderef_url'] ) )
        {
            throw new Exception( 'Unable to find destination folder: ' . $folderId );
        }
        */
        $continueOnFailureStr = $continueOnFailure ? 'True' : 'False';
        $unfileNonfolderObjectslist = array( 'Unfile', 'DeleteSingleFiled', 'Delete' );

        if ( !in_array( $unfileNonfolderObjects, $unfileNonfolderObjectslist  ) )
        {
            $unfileNonfolderObjects = 'Delete';
        }

        if ( strstr( $folderId, '?') )
        {
            $url = $folderId . '&continueOnFailure=' . $continueOnFailureStr . '&unfileMultiFiledDocuments=' . $unfileNonfolderObjects;
        }
        else
        {
            $url = $folderId . '?continueOnFailure=' . $continueOnFailureStr . '&unfileMultiFiledDocuments=' . $unfileNonfolderObjects;
        }

        $xml = nxcAlfrescoUtils::invokeService( $url, 'basic', $header = array(), 'CUSTOM-DELETE' );

        return !is_bool( $xml ) ? nxcAlfrescoUtils::getEntries( nxcAlfrescoUtils::processXML( $xml, '//D:entry' ) ) : $xml;
    }

    /**
     *
     */
    public static function setContentStream( $objectId, $overwriteFlag = true, $content = null, $properties = array() )
    {
        return null;
    }

    public static function deleteContentStream( $repositoryId, $objectId )
    {
        return null;
    }

    /**************************
     * Multi-filling services *
     **************************/

    /**
     *
     */
    public static function addObjectToFolder( $objectId, $folderId )
    {
        return null;
    }

    /**
     *
     */
    public static function removeObjectFromFolder( $objectId, $folderId = null )
    {
        return null;
    }

    /***********************
     * Discovery servicies *
     ***********************/

    /**
     * Queries the repository for queryable object based on properties or an optional full-text string. Relationship objects are not queryable. Content-streams are not returned as part of query.
     *
     * Inputs:
     * String statement: Query statement
     * (Optional) Bool searchAllVersions: False (Default)
     * (Optional) Boolean includeAllowableActions: False (default)
     * (Optional) Enum includeRelationships: none (default), source, target, both
     * (Optional) int maxItems: 0 = Repository-default number of items (Default)
     * (Optional) int skipCount: 0 = Start at first position (Default)
     */
    public static function query( $searchText, $searchAllVersions = false, $includeAllAllowableActions = false, $includeRelationships = null, $maxItems = 0, $skipCount = 0 )
    {
        //$statement = 'select * from document where contains ("'.$searchText.'") ';
        $postvars = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>" .
                    "<cmis:query xmlns:cmis='http://docs.oasis-open.org/ns/cmis/core/200901' xmlns:p='http://www.w3.org/1999/xhtml' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'>" .
                        "<cmis:statement>select * from document where contains ('".$searchText."')</cmis:statement>" .
                        "<cmis:searchAllVersions>" . $searchAllVersions . "</cmis:searchAllVersions>" .
                        "<cmis:pageSize>" . $maxItems . "</cmis:pageSize>" .
                        "<cmis:skipCount>" . $skipCount . "</cmis:skipCount>" .
                        "<cmis:returnAllowableActions>" . $includeAllAllowableActions . "</cmis:returnAllowableActions>" .
                    "</cmis:query>";


        $header[] = 'Content-type: application/cmisquery+xml';
        $header[] = 'Content-length: ' . strlen($postvars);
        $header[] = 'MIME-Version: 1.0';

        $http  = eZHTTPTool::instance();
        $resositoryInfo =  $http->hasSessionVariable( 'RepositoryInfo' ) ? unserialize( $http->sessionVariable( 'RepositoryInfo' ) ) : false;
        //$cmisTypes = $http->hasSessionVariable( 'CmisTypeKeyArray' )  ? unserialize( $http->sessionVariable( 'CmisTypeKeyArray' ) )     : false;

        $queryURL = (string) $resositoryInfo->query;

        if ( $queryURL )
        {
            $xml = nxcAlfrescoUtils::invokeService( $queryURL, 'basic', $header, 'CUSTOM-POST', $postvars );

            if ( !$xml )
            {
                throw new Exception( 'Unable to fetch data by keyword: ' . $keyword );
            }

            $entries = nxcAlfrescoUtils::processOpenSearchXml( $xml, '//D:entry' );
            $feed = nxcAlfrescoUtils::processOpenSearchXml( $xml, '/D:feed' );

            $totalItems = (int) nxcAlfrescoUtils::getXMLValue( $feed[0], 'opensearch:totalResults' );
            $itemsPerPage = (int) nxcAlfrescoUtils::getXMLValue( $feed[0], 'opensearch:itemsPerPage' );
            $result = array();
            $result['total_items'] = $totalItems;
            $result['start_page'] = $skipCount;
            $result['items_per_page'] = $itemsPerPage;
            $result['entries'] = nxcAlfrescoUtils::getEntriesQuery( $entries );
            /*foreach ( $entries as $entry )
            {
                $cmis_object = new stdClass();
                $cmis_object->id = (string) $entry->id;
                $cmis_object->title = (string) $entry->title;
                $cmis_object->summary = (string) $entry->summary;
                $typekey = (string) nxcAlfrescoUtils::getXMLValue( $entry, 'cmis:object/cmis:properties/cmis:propertyId[@cmis:name="ObjectTypeId"]/cmis:value' ); //0.61
                $cmis_object->type = $cmisTypes[ $typekey ];
                $cmis_object->updated = date_create( (string) $entry->updated );
                $cmis_object->author = (string) $entry->author->name;
                $cmis_object->icon = (string) nxcAlfrescoUtils::getXMLValue($entry, 'alf:icon');
                $result['entries'][] = $cmis_object;
            }
            */

            return $result;


        }
        else
        {
            return false;
        }



    }

    /**
     * Searches keywords using Open Search support
     *
     * @param string key word
     * @param int number of page
     * @param int items count per page
     * @return list of objects
     * @note Searches objects only, not spaces
     */
    public static function openSearch( $keyword, $startPage = 1, $limit = 20  )
    {
        $xml = nxcAlfrescoUtils::invokeService( '/api/search/keyword.atom?q=' . urlencode( $keyword ) . '&p=' . $startPage . '&c=' . $limit, 'ticket' );

        if ( !$xml )
        {
            throw new Exception( 'Unable to fetch data by keyword: ' . $keyword );
        }

        // Process the returned XML
        $entries = nxcAlfrescoUtils::processOpenSearchXml( $xml, '//D:entry' );
        $feed = nxcAlfrescoUtils::processOpenSearchXml( $xml, '/D:feed' );

        $totalItems = (int) nxcAlfrescoUtils::getXMLValue( $feed[0], 'opensearch:totalResults' );
        $itemsPerPage = (int) nxcAlfrescoUtils::getXMLValue( $feed[0], 'opensearch:itemsPerPage' );
        $result = array();
        $result['total_items'] = $totalItems;
        $result['start_page'] = $startPage;
        $result['items_per_page'] = $itemsPerPage;

        foreach ( $entries as $entry )
        {
            $cmis_object = new stdClass();

            $tmp_objectId = nxcAlfrescoUtils::objectId( (string) $entry->id );
            $cmis_object->id = $tmp_objectId['noderef'];
            $cmis_object->title = (string) $entry->title;
            $cmis_object->summary = (string) $entry->summary;
            $cmis_object->type = 'document';
            $cmis_object->updated = date_create( (string) $entry->updated );
            $cmis_object->author = $entry->author->name;
            $cmis_object->icon = (string) $entry->icon;

            $result['entries'][] = $cmis_object;
        }

        return $result;
    }

    /***********************
     * Versioning services *
     ***********************/

    /**
     * Create a private working copy of the object, copies the metadata and optionally content.
     * It is up to the repository to determine if updates to the current version (not PWC) and prior versions are allowed if checked-out.
     */
    public static function checkOut( $documentId )
    {
        return null;
    }

    /**
     * Reverses the effect of a check-out.
     * Removes the private working copy of the checked-out document object,
     * allowing other documents in the version series to be checked out again.
     */
    public static function cancelCheckOut( $documentId )
    {
        return null;
    }

    /**
     * Makes the private working copy the current version of the document.
     */
    public static function checkIn( $documentId, $major = null, $bag = null, $content = null, $checkinComment = null )
    {
        return null;
    }

    /**
     *
     */
    public static function getPropertiesOfLatestVersion( $versionSeriesId )
    {
        return null;
    }

    /**
     * Returns the list of all document versions for the specified version series, sorted by CREATION_DATE descending.
     */
    public static function getAllVersions( $versionSeriesId )
    {
        return null;
    }

    /**
     *
     */
    public function deleteAllVersions( $versionSeriesId )
    {
        return null;
    }

    /**************************
     * Relationships services *
     **************************/

    /**
     *
     */
    public static function getRelationships( $objectId )
    {
        return null;
    }

    /*******************
     * Policy services *
     *******************/

    /**
     *
     */
    public static function applyPolicy( $policyId, $objectId )
    {
        return null;
    }

    /**
     *
     */
    public static function removePolicy( $policyId, $objectId )
    {
        return null;
    }

    /**
     *
     */
    public static function getAppliedPolicies( $objectId )
    {
        return null;
    }

    public static function getVersionSpecificProperty( $property )
    {
      $http = eZHTTPTool::instance();
      $curVersion = $http->sessionVariable( 'CmisVersion' );
      $curVersion = substr( trim($curVersion), 0, 4 );
      if ( !$curVersion )
      {
         $curVersion = '0.62';
      }

      if($curVersion == '0.61c')
      {
      	$curVersion = '0.61';
      }

      $versionSpecificValues = array(
          'repositoryInfoXMLElement' => array( '0.61' => '/app:service/app:workspace/cmis:repositoryInfo',
                                               '0.62' => '/app:service/app:workspace/cmisra:repositoryInfo'
                                             ),
          'rootChildren'             => array( '0.61' => '/app:service/app:workspace/app:collection[@cmis:collectionType="rootchildren"]',
                                               '0.62' => '/app:service/app:workspace/app:collection[@cmisra:collectionType="root"]'
                                             ),
          'typesCollection'          => array( '0.61' => '/app:service/app:workspace/app:collection[@cmis:collectionType="typesdescendants"]',
                                               '0.62' => '/app:service/app:workspace/app:collection[@cmisra:collectionType="types"]'
                                             ),
          'queryCollection'          => array( '0.61' => '/app:service/app:workspace/app:collection[@cmis:collectionType="query"]',
                                               '0.62' => '/app:service/app:workspace/app:collection[@cmisra:collectionType="query"]'
                                             ),
          'typesTypeID'              => array( '0.61' => '//cmis:typeId',
                                               '0.62' => '//cmis:id'
                                             ),
          'typesBaseType'            => array( '0.61' => '//cmis:baseType',
                                               '0.62' => '//cmis:baseTypeId'
                                             ),
          'getChildrenwithSkip'      => array( '0.61' => true,
                                               '0.62' => false ),
          'getObjectTypeID'          => array( '0.61' => 'cmis:object/cmis:properties/cmis:propertyId[@cmis:name="ObjectTypeId"]/cmis:value',
                                               '0.62' => 'cmisra:object/cmis:properties/cmis:propertyId[@pdid="cmis:ObjectTypeId"]/cmis:value' ),
          'getChildrenLink'          => array( '0.61' => '*[@rel="children"]',
                                               '0.62' => '*[@rel="down"]' ),
          'getParentLink'            => array( '0.61' => '*[@rel="parents"]',
                                               '0.62' => '*[@rel="up"]' ),
          'getDescendantsLink'       => array( '0.61' => '*[@rel="descendants"]',
                                               '0.62' => '*[@rel="down"]' ),
        );

      if ( !isset( $versionSpecificValues[$property] ) )
      {
          throw new Exception( 'Can\'t find version specific property:'.$property. ' for version: '.$curVersion );
      }
      else
      {
          if ( isset( $versionSpecificValues[$property][$curVersion] ) )
          {
              $result =  $versionSpecificValues[$property][$curVersion];
          }
          else
          {
              $result ='';
          }
      }

      return $result;
    }
}
?>
