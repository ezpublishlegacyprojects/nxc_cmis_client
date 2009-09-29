<?php
/**
 * Definition of nxcAlfrescoUtils class
 *
 * Created on: <18-Apr-2009 12:00:00 vd>
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
 * Common Alfresco Related Utility Functions for Alfresco CMIS module
 *
 * @file nxcalfrescoutils.php
 */

class nxcAlfrescoUtils
{

    /**
     * Logs in the user.
     *
     * @return String ticket
     */
    public static function login( $user = false, $password = false, $endPoint = false )
    {
        $ini      = eZINI::instance( 'alfresco.ini' );
        $user     = !$user     ? ( $ini->hasVariable( 'AlfrescoSettings', 'DefaultUser' )     ? $ini->variable( 'AlfrescoSettings', 'DefaultUser' )     : '' ) : $user;
        $password = !$password ? ( $ini->hasVariable( 'AlfrescoSettings', 'DefaultPassword' ) ? $ini->variable( 'AlfrescoSettings', 'DefaultPassword' ) : '' ) : $password;
        $endPoint = !$endPoint ? ( $ini->hasVariable( 'AlfrescoSettings', 'EndPoint' )        ? $ini->variable( 'AlfrescoSettings', 'EndPoint' )        : '' ) : $endPoint;
        $http     = eZHTTPTool::instance();

        $http->setSessionVariable( 'AlfrescoEndPoint', $endPoint );
        $http->setSessionVariable( 'AlfrescoUser', $user );
        $http->setSessionVariable( 'AlfrescoPassword', $password );
        if ( $http->hasSessionVariable( 'CmisTypeKeyArray' ) )
        {
            $http->removeSessionVariable( 'CmisTypeKeyArray');
        }
        if ( $http->hasSessionVariable( 'RepositoryInfo' ) )
        {
            $http->removeSessionVariable( 'RepositoryInfo' );
        }

        return;
    }

    /**
     * Logs out the user.
     *
     * @TODO Logout from alfresco as well
     */
    public static function logout()
    {
        $http = eZHTTPTool::instance();
        $http->removeSessionVariable( 'AlfrescoUser' );
        $http->removeSessionVariable( 'AlfrescoPassword' );
        $http->removeSessionVariable( 'AlfrescoEndPoint' );
        $http->removeSessionVariable( 'AlfrescoTicket' );
    }

    /**
     * @return string User name if it logged in..
     */
    public static function getLoggedUserName()
    {
        $ini = eZINI::instance( 'alfresco.ini' );
        $http = eZHTTPTool::instance();
        $user = $ini->hasVariable( 'AlfrescoSettings', 'DefaultUser' ) ? $ini->variable( 'AlfrescoSettings', 'DefaultUser' ) : '';
        $storedUser = $http->hasSessionVariable( 'AlfrescoUser' ) ? $http->sessionVariable( 'AlfrescoUser' ) : '';

        return ( $http->hasSessionVariable( 'AlfrescoTicket' ) and $user != $storedUser ) ? $storedUser : '' ;
    }

    /**
     * Utility function for getting alfresco ticket.
     *
     * $op Option for refreshing ticket or not.
     */
    public static function getTicket( $op = 'norefresh' )
    {
        $http = eZHTTPTool::instance();
        $ticket = $http->hasSessionVariable( 'AlfrescoTicket' ) ? $http->sessionVariable( 'AlfrescoTicket' ) : null;

        if ( $ticket == null || $op == 'refresh' )
        {
            $user     = $http->hasSessionVariable( 'AlfrescoUser' )     ? $http->sessionVariable( 'AlfrescoUser' )     : false;
            $password = $http->hasSessionVariable( 'AlfrescoPassword' ) ? $http->sessionVariable( 'AlfrescoPassword' ) : false;
            $endPoint = $http->hasSessionVariable( 'AlfrescoEndPoint' ) ? $http->sessionVariable( 'AlfrescoEndPoint' ) : false;

            $ticket = nxcAlfrescoUtils::login( $user, $password, $endPoint );
        }

        return $ticket;
    }

    /**
     * Return service url for HTTP basic authentication.
     */
    public static function getURL( $url )
    {
        $http = eZHTTPTool::instance();
        $endPoint = $http->hasSessionVariable( 'AlfrescoEndPoint' ) ? $http->sessionVariable( 'AlfrescoEndPoint' ) : '';

        return $endPoint . '/service' . $url;
    }

    /**
     * Return End Point URI.
     */
    public static function getEndPoint()
    {
        $http     = eZHTTPTool::instance();
        $endPoint = $http->hasSessionVariable( 'AlfrescoEndPoint' ) ? $http->sessionVariable( 'AlfrescoEndPoint' ) : '';
        if ( !$endPoint )
        {
           $ini      = eZINI::instance( 'alfresco.ini' );
           $endPoint = $ini->hasVariable( 'AlfrescoSettings', 'EndPoint' ) ? $ini->variable( 'AlfrescoSettings', 'EndPoint' ) : '';
        }
        return $endPoint;
    }

    /**
     * Return service url for ticket based authentication.
     * $op Option for refreshing ticket or not.
     */
    public static function getWCURL( $url, $op = 'norefresh' )
    {
        $http = eZHTTPTool::instance();
        $ticket = nxcAlfrescoUtils::getTicket( $op );
        $endPoint = $http->hasSessionVariable( 'AlfrescoEndPoint' ) ? $http->sessionVariable( 'AlfrescoEndPoint' ) : '';
        $wcEndPoint = $endPoint . '/service';

        if ( false === strstr( $url, '?' ) )
        {
            if ( $url[0] == '/' )
            {
                return $wcEndPoint . $url . '?alf_ticket=' . $ticket;
            }

            return $url . '?alf_ticket=' . $ticket;
        }

        return $wcEndPoint . str_replace( '?', '?alf_ticket=' . $ticket . '&', $url );
    }

    /**
     * Invoke Alfresco Webscript based Service.
     * $op Option for service authentication.
     * 'ticket' is for ticket based and 'basic' for http basic authentication.
     */
    public static function invokeService( $serviceURL, $op = 'basic', $headers = array(), $method = 'GET', $data = NULL, $retry = 3 )
    {
        $response = nxcAlfrescoUtils::httpRequest( $serviceURL, $op, $headers, $method, $data, $retry );

        if (!empty($response->code) && ($response->code == 401)) {
            nxcAlfrescoUtils::login();
            $response = nxcAlfrescoUtils::httpRequest( $serviceURL, $op, $headers, $method, $data, $retry );
        }

        if ( $response->code == 200 || $response->code == 201 )
        {
            $content = $response->data;

            if ( strstr( $content, 'Alfresco Web Client - Login' ) === false )
            {
                return $content;
            }

            $response2 = nxcAlfrescoUtils::httpRequest( $serviceURL, 'refresh', $headers, $method, $data, $retry );
            if ( $response2->code == 200 || $response->code == 201 )
            {
                return $response2->data;
            }

            throw new Exception( 'Failed to invoke service ' . $serviceURL . ' Code:' . $response2->code );
        }
        elseif ( $response->code == 302 || $response->code == 505 )
        {
            $response2 = nxcAlfrescoUtils::httpRequest( $serviceURL, 'refresh', $headers, $method, $data, $retry );

            if ( $response2->code == 200 || $response->code == 201 )
            {
                return $response2->data;
            }

            throw new Exception( 'Failed to invoke service ' . $serviceURL . ' Code:' . $response2->code );
        }
        elseif ( $response->code == 204 )
        {
            return true;
        }
        elseif ( ( $response->code == 500 and strstr( $response->data, 'AccessDeniedException - Access is denied.' ) !== false ) or ($response->code == 403) or ($response->code == 401))
        {
            throw new Exception( 'Access denied', 403 );
        }

        throw new Exception( 'Failed to invoke service ' . $serviceURL . ' Code:' . $response->code );
    }

    /**
     * Invoke Webscript based Service using curl apis.
     * It is intended to be a utility function that handles authentication (basic, ticket),
     * http headers (if necessary) and custom post ( cmisquery+xml, atom+xml etc.).
     *
     * $serviceURL Alfreco webscript service url without /service or /wcservice prefix.
     * $auth Option for service authentication.
     * $headers Additional http headers for making the http call.
     * 'ticket' is for ticket based, 'basic' for http basic authentication, 'refresh' for ticket based but with ticket refresh.
     * $method
     * $data
     * $retry
     */
    public static function httpRequest( $serviceURL, $auth = 'ticket', $headers = array(), $method = 'GET', $data = NULL, $retry = 3 )
    {
        if ( $auth == 'basic' )
        {
            $url = $serviceURL;
//            $url = nxcAlfrescoUtils::getURL( $serviceURL );
        }
        elseif ( $auth == 'refresh' )
        {
            $url = nxcAlfrescoUtils::getWCURL( $serviceURL, 'refresh' );
        }
        else
        {
            $url = nxcAlfrescoUtils::getWCURL( $serviceURL );
        }

        // Prepare curl session
        $session = curl_init( $url );
        curl_setopt( $session, CURLOPT_VERBOSE, 1 );

        // Add additonal headers
        curl_setopt( $session, CURLOPT_HTTPHEADER, $headers );

        // Don't return HTTP headers. Do return the contents of the call
        curl_setopt( $session, CURLOPT_HEADER, false );
        curl_setopt( $session, CURLOPT_RETURNTRANSFER, true );

        if ( $auth == 'basic' )
        {
            $http     = eZHTTPTool::instance();
            $user     = $http->hasSessionVariable( 'AlfrescoUser' )     ? $http->sessionVariable( 'AlfrescoUser' )     : '';
            $password = $http->hasSessionVariable( 'AlfrescoPassword' ) ? $http->sessionVariable( 'AlfrescoPassword' ) : '';

            curl_setopt( $session, CURLOPT_USERPWD, "$user:$password" );
        }

        switch ( $method )
        {
            case 'CUSTOM-POST':
            {
                curl_setopt( $session, CURLOPT_CUSTOMREQUEST, 'POST' );
                curl_setopt( $session, CURLOPT_POSTFIELDS, $data );
                //curl_setopt( $session, CURLOPT_ERRORBUFFER, 1 );

            } break;

            case 'CUSTOM-PUT':
            {
                curl_setopt( $session, CURLOPT_CUSTOMREQUEST, 'PUT' );
                curl_setopt( $session, CURLOPT_POSTFIELDS, $data );
                //curl_setopt( $session, CURLOPT_ERRORBUFFER, 1 );

            } break;

            case 'CUSTOM-DELETE':
            {
                curl_setopt( $session, CURLOPT_CUSTOMREQUEST, 'DELETE' );
                //curl_setopt( $session, CURLOPT_ERRORBUFFER, 1 );

            } break;

        }
        // Make the call
        $returnData = curl_exec( $session );
        // Get return http status code
        $httpcode = curl_getinfo( $session, CURLINFO_HTTP_CODE );

        // Close HTTP session
        curl_close( $session );

        // Prepare return
        $result = new stdClass();
        $result->code = $httpcode;
        $result->data = $returnData;

        return $result;
    }

    /**
     * Utility function for returning CMIS objects from cmis response(ie. getChildren, query, getDescendants)
     * @param $entries
     * @return array
     */
    public static function getEntries( $entries )
    {
    	nxcAlfresco::getRepositoryInfo();
        $result = array();
        $http  = eZHTTPTool::instance();
        $cmisTypes = $http->hasSessionVariable( 'CmisTypeKeyArray' )  ? unserialize ( $http->sessionVariable( 'CmisTypeKeyArray' ) )  : false;

        foreach ( $entries as $entry )
        {
            $cmis_object = new stdClass();
            $cmis_object->id = (string) $entry->id;

            $cmis_object->title = (string) $entry->title;
            $cmis_object->summary = (string) $entry->summary;

            $typekey = (string) nxcAlfrescoUtils::getXMLValue( $entry, nxcAlfresco::getVersionSpecificProperty('getObjectTypeID') );


            $typekey = strtolower( $typekey );
            if ( isset( $cmisTypes[ $typekey ] ) )
            {
                $cmis_object->type = $cmisTypes[ $typekey ];
            }
            else
            {
                $cmis_object->type = 'folder';
            }




            $cmis_object->updated = date_create( (string) $entry->updated );
            $cmis_object->author = (string) $entry->author->name;
            $linkChildrenXML = $entry->xpath( nxcAlfresco::getVersionSpecificProperty('getChildrenLink') );

            $cmis_object->childrenUri = isset( $linkChildrenXML[0] ) ? (string) nxcAlfrescoUtils::getXMLAttribute($linkChildrenXML[0] , 'href') : "";

            $linkSelfXML = $entry->xpath( '*[@rel="self"]' );
            $cmis_object->selfUri = isset( $linkSelfXML[0] ) ? (string) nxcAlfrescoUtils::getXMLAttribute($linkSelfXML[0] , 'href') : "";
            $linkParentXML = $entry->xpath( nxcAlfresco::getVersionSpecificProperty('getParentLink') );
            $cmis_object->parentUri = isset( $linkParentXML[0] ) ? (string) nxcAlfrescoUtils::getXMLAttribute($linkParentXML[0] , 'href') : "";

            $linkDescendantsXML = $entry->xpath( nxcAlfresco::getVersionSpecificProperty('getDescendantsLink') );  //0.61
            $cmis_object->descendantsUri = isset( $linkDescendantsXML[0] ) ? (string) nxcAlfrescoUtils::getXMLAttribute( $linkDescendantsXML[0] , 'href') : "";
            //$cmis_object->icon = (string) nxcAlfrescoUtils::getXMLValue($entry, 'alf:icon');

            //$cmis_object->childrens =

            if ( $cmis_object->type == 'document' )
            {
                $cmis_object->size = nxcAlfrescoUtils::getXMLValue( $entry, 'cmis:object/cmis:properties/cmis:propertyInteger[@cmis:name="ContentStreamLength"]/cmis:value' );
                $cmis_object->contentMimeType = nxcAlfrescoUtils::getXMLValue( $entry, 'cmis:object/cmis:properties/cmis:propertyString[@cmis:name="ContentStreamMimeType"]/cmis:value' );
                $cmis_object->versionSeriesCheckedOutBy = nxcAlfrescoUtils::getXMLValue( $entry, 'cmis:object/cmis:properties/cmis:propertyString[@cmis:name="VersionSeriesCheckedOutBy"]/cmis:value' );
                $linkStreamXML = $entry->xpath( '*[@rel="cmis-stream"]' );
                $streamUri = isset( $linkStreamXML[0] ) ? (string) nxcAlfrescoUtils::getXMLAttribute( $linkStreamXML[0] , 'href') : "";
                $linkStreamXML = $entry->xpath( '*[@rel="stream"]' );
                $cmis_object->streamUri = isset( $linkStreamXML[0] ) ? (string) nxcAlfrescoUtils::getXMLAttribute( $linkStreamXML[0] , 'href') : $streamUri;

            }

            $result[] = $cmis_object;
        }

        return $result;
    }



     public static function getEntriesQuery( $entries )
    {
        $result = array();
        $http  = eZHTTPTool::instance();
        $cmisTypes = $http->hasSessionVariable( 'CmisTypeKeyArray' )     ? unserialize( $http->sessionVariable( 'CmisTypeKeyArray' ) )    : false;


        foreach ( $entries as $entry )
        {

            $cmis_object = new stdClass();
            $cmis_object->id = (string) $entry->id;

            $cmis_object->title = (string) $entry->title;
            $cmis_object->summary = (string) $entry->summary;
            if ( $cmisTypes )
            {
                $typekey = (string) nxcAlfrescoUtils::getXMLValue( $entry, 'cmis:object/cmis:properties/cmis:propertyId[@cmis:name="ObjectTypeId"]/cmis:value' ); //0.61
                $cmis_object->type = $cmisTypes[$typekey];
            }
            else
            {
                $cmis_object->type = (string) nxcAlfrescoUtils::getXMLValue( $entry, 'cmis:object/cmis:properties/cmis:propertyString[@cmis:name="BaseType"]/cmis:value' ); //0.61
            }

            // $cmis_object->type = str_replace( 'cmis:', '', (string) nxcAlfrescoUtils::getXMLValue( $entry, 'cmisra:object/cmis:properties/cmis:propertyId[@pdid="cmis:BaseTypeId"]/cmis:value' )); //0.62

            $cmis_object->updated = date_create( (string) $entry->updated );
            $cmis_object->author = (string) $entry->author->name;
            $linkChildrenXML = $entry->xpath( '*[@rel="children"]' );  //0.61
            //  $linkChildrenXML = $entry->xpath( '*[@rel="down"]' ); //0.62

            $cmis_object->childrenUri = isset( $linkChildrenXML[0] ) ? (string) nxcAlfrescoUtils::getXMLAttribute($linkChildrenXML[0] , 'href') : "";

            $linkSelfXML = $entry->xpath( '*[@rel="self"]' );
            $cmis_object->selfUri = isset( $linkSelfXML[0] ) ? (string) nxcAlfrescoUtils::getXMLAttribute($linkSelfXML[0] , 'href') : "";
            $linkParentXML = $entry->xpath( '*[@rel="parents"]' );  //0.61
            $cmis_object->parentUri = isset( $linkParentXML[0] ) ? (string) nxcAlfrescoUtils::getXMLAttribute($linkParentXML[0] , 'href') : "";

            $linkDescendantsXML = $entry->xpath( '*[@rel="descendants"]' );  //0.61
            $cmis_object->descendantsUri = isset( $linkDescendantsXML[0] ) ? (string) nxcAlfrescoUtils::getXMLAttribute( $linkDescendantsXML[0] , 'href') : "";
            $cmis_object->icon = (string) nxcAlfrescoUtils::getXMLValue($entry, 'alf:icon');

            //$cmis_object->childrens =

            if ( $cmis_object->type == 'document' )
            {
                $cmis_object->size = nxcAlfrescoUtils::getXMLValue( $entry, 'cmis:object/cmis:properties/cmis:propertyInteger[@cmis:name="ContentStreamLength"]/cmis:value' );
                $cmis_object->contentMimeType = nxcAlfrescoUtils::getXMLValue( $entry, 'cmis:object/cmis:properties/cmis:propertyString[@cmis:name="ContentStreamMimeType"]/cmis:value' );
                $cmis_object->versionSeriesCheckedOutBy = nxcAlfrescoUtils::getXMLValue( $entry, 'cmis:object/cmis:properties/cmis:propertyString[@cmis:name="VersionSeriesCheckedOutBy"]/cmis:value' );
                $linkStreamXML = $entry->xpath( '*[@rel="stream"]' );
                $cmis_object->streamUri = isset( $linkStreamXML[0] ) ? (string) nxcAlfrescoUtils::getXMLAttribute( $linkStreamXML[0] , 'href') : "";

            }

            $result[] = $cmis_object;
        }

        return $result;
    }


    /**
     * Process CMIS XML.
     * $xml CMIS response XML.
     * $xpath xpath expression.
     */
    public static function processXML( $xml, $xpath )
    {
        try
        {
            $cmisService = new SimpleXMLElement( $xml );
        }
        catch ( Exception $e )
        {
            throw new Exception( 'Bad XML: ' . $xml );
        }

        $cmisService->registerXPathNamespace( 'cmisra', 'http://docs.oasis-open.org/ns/cmis/restatom/200901' ); // 0.62
        //$cmisService->registerXPathNamespace( 'cmis', 'http://www.cmis.org/2008/05' );
        $cmisService->registerXPathNamespace( 'cmis', 'http://docs.oasis-open.org/ns/cmis/core/200901' );
        $cmisService->registerXPathNamespace( 'D', 'http://www.w3.org/2005/Atom' );
        $cmisService->registerXPathNamespace( 'alf', 'http://www.alfresco.org' );
        $cmisService->registerXPathNamespace( 'app', 'http://www.w3.org/2007/app' );

        $entry = $cmisService->xpath( $xpath );

        return $entry;
    }

    public static function processTypesXML( $xml, $xpath )
    {
        try
        {
            $cmisService = new SimpleXMLElement( $xml );
        }
        catch ( Exception $e )
        {
            throw new Exception( 'Bad XML: ' . $xml );
        }


        $cmisService->registerXPathNamespace( 'cmis', 'http://docs.oasis-open.org/ns/cmis/core/200901' );
        $cmisService->registerXPathNamespace( 'D', 'http://www.w3.org/2005/Atom' );
        $entry = $cmisService->xpath( $xpath );

        return $entry;
    }

    /**
     * Process Open Search XML.
     * $xml Open Search response XML.
     * $xpath xpath expression.
     */
    public static function processOpenSearchXML( $xml, $xpath )
    {
        try
        {
            $openSearchService = new SimpleXMLElement( $xml );
        }
        catch ( Exception $e )
        {
            throw new Exception( 'Bad XML: ' . $xml );
        }

        $openSearchService->registerXPathNamespace( 'opensearch', 'http://a9.com/-/spec/opensearch/1.1/' );
        $openSearchService->registerXPathNamespace( 'D', 'http://www.w3.org/2005/Atom' );
        $openSearchService->registerXPathNamespace( 'alf', 'http://www.alfresco.org/opensearch/1.0/' );
        $openSearchService->registerXPathNamespace( 'relevance', 'http://a9.com/-/opensearch/extensions/relevance/1.0/' );

        $entry = $openSearchService->xpath( $xpath );

        return $entry;
    }


    /**
     * Get XML node value.
     * $entry CMIS XML Node.
     * $xpath xpath expression.
     */
    public static function getXMLValue( $entry, $xpath )
    {
        if ( is_null( $entry ) )
        {
            return null;
        }

        $value = $entry->xpath( $xpath );

        return isset( $value[0][0] ) ? $value[0][0] : null;
    }

    public static function getXMLAttribute( $entry, $name )
    {
        if ( is_null( $entry ) )
        {
            return null;
        }

        $attrs = $entry->attributes();
        $value = $attrs[$name];

        return isset( $value ) ? $value : null;
    }
    /**
     * Resolves objectId from various formats.
     *
     * Known formats:
     *  - url: http://localhost:8080/alfresco/service/api/path/workspace/SpacesStore/Company%20Home
     *  - path: Company Home/path fo file
     *  - noderef: workspace://SpacesStore/91298612309871e
     *  - id: 91298612309871esdf
     *
     * @todo: Review objectId handling.
     *
     * @param $objectId
     * @return array
     */
    public static function objectId( $objectId )
    {
        // Probably is already resolved ("instanceof" would have been more appropriate)
        if ( is_array( $objectId ) )
        {
            return $objectId;
        }

        // Unable to resolve node
        if ( !is_string( $objectId ) )
        {
            throw new Exception( 'Unable to resolve node due to object id is not string' );
        }

        $parts = parse_url( $objectId );

        if ( isset( $parts['scheme'] ) and substr( $parts['scheme'], 0, 4 ) == 'http' )
        {
            // @todo: lookup id by path
            $parts['url'] = $objectId;
            return $parts;
        }

        if ( isset( $parts['scheme'] ) and in_array( $parts['scheme'], array( 'workspace', 'archive', 'user', 'system', 'avm' ) ) )
        {
            $parts['noderef'] = $objectId;
            $parts['noderef_url'] = $parts['scheme'] . '/' . $parts['host'] . $parts['path'];

            return $parts;
        }

        if ( isset( $parts['scheme'] ) and $parts['scheme'] == 'urn' )
        {
            // Assuming that comes from workspace://SpacesStore/
            // @todo: Review this assumption
            $tmp_parts = parse_url( $parts['path'] );
            $parts['path'] = $tmp_parts['path'];

            $parts['noderef'] = 'workspace://SpacesStore/' . $parts['path'];
            $parts['scheme'] = 'workspace';
            $parts['host'] = 'SpacesStore';
            $parts['noderef_url'] = 'workspace/SpacesStore/' . $parts['path'];

            return $parts;
        }

        if ( $parts['path'][0] == '/' and empty( $parts['scheme'] ) )
        {
            // Assuming that id looks like "/Company Home/path/to/object"
            if ( substr( $parts['path'], -1 ) == '/' )
            {
                $parts['path'] = substr_replace( $parts['path'], '', -1 );
            }

            // strtoupper() is needed to prevent passing reserved words as a path like 'content' or 'children',
            // otherwise wrong data will be returned.
            $response = nxcAlfrescoUtils::invokeService( '/api/path/workspace/SpacesStore' . strtoupper( $parts['path'] ) );
            $objectInfo = nxcAlfrescoUtils::processXML( $response, '//D:entry' );

            if ( false != $objectInfo )
            {
                return nxcAlfrescoUtils::objectId( (string) $objectInfo[0]->id );
            }
        }

        // unknown format
        throw new Exception( "Unable to resolve objectId: $objectId" );
    }

    /**
     * Perform an HTTP request.
     *
     * This is a flexible and powerful HTTP client implementation. Correctly handles
     * GET, POST, PUT or any other HTTP requests. Handles redirects.
     *
     * @param $url
     *   A string containing a fully qualified URI.
     * @param $headers
     *   An array containing an HTTP header => value pair.
     * @param $method
     *   A string defining the HTTP request to use.
     * @param $data
     *   A string containing data to include in the request.
     * @param $retry
     *   An integer representing how many times to retry the request in case of a
     *   redirect.
     * @return
     *   An object containing the HTTP request headers, response code, headers,
     *   data and redirect status.
     */
    public static function drupalHTTPRequest( $url, $headers = array(), $method = 'GET', $data = NULL, $retry = 3 )
    {

        $result = new stdClass();

        // Parse the URL and make sure we can handle the schema.
        $uri = parse_url( $url );

        if ( $uri == false )
        {
            $result->error = 'unable to parse URL';
            return $result;
        }

        if ( !isset( $uri['scheme'] ) )
        {
            $result->error = 'missing schema';
            return $result;
        }

        switch ( $uri['scheme'] )
        {
            case 'http':
            {
                $port = isset( $uri['port'] ) ? $uri['port'] : 80;
                $host = $uri['host'] . ( $port != 80 ? ':'. $port : '' );
                $fp = @fsockopen( $uri['host'], $port, $errno, $errstr, 15 );
            }
            break;

            case 'https':
            {
                // Note: Only works for PHP 4.3 compiled with OpenSSL.
                $port = isset( $uri['port'] ) ? $uri['port'] : 443;
                $host = $uri['host'] . ($port != 443 ? ':'. $port : '');
                $fp = @fsockopen( 'ssl://' . $uri['host'], $port, $errno, $errstr, 20 );
            }
            break;

            default:
            {
                $result->error = 'invalid schema '. $uri['scheme'];
                return $result;
            }
        }

        // Make sure the socket opened properly.
        if ( !$fp )
        {
            // When a network error occurs, we use a negative number so it does not
            // clash with the HTTP status codes.
            $result->code = -$errno;
            $result->error = trim( $errstr );

            return $result;
        }

        // Construct the path to act on.
        $path = isset( $uri['path'] ) ? $uri['path'] : '/';
        if ( isset( $uri['query'] ) )
        {
            $path .= '?'. $uri['query'];
        }

        // Create HTTP request.
        $defaults = array(
                            // RFC 2616: "non-standard ports MUST, default ports MAY be included".
                            // We don't add the port to prevent from breaking rewrite rules checking the
                            // host that do not take into account the port number.
                            'Host' => "Host: $host",
                            'User-Agent' => 'User-Agent: eZ Publish (+http://ez.no/)',
                            'Content-Length' => 'Content-Length: '. strlen( $data )
                            );

        // If the server url has a user then attempt to use basic authentication
        if ( isset( $uri['user'] ) )
        {
            $defaults['Authorization'] = 'Authorization: Basic '. base64_encode( $uri['user'] . ( !empty($uri['pass'] ) ? ":". $uri['pass'] : '' ) );
        }

        foreach ( $headers as $header => $value )
        {
            $defaults[$header] = $header .': '. $value;
        }

        $request = $method .' '. $path ." HTTP/1.0\r\n";
        $request .= implode( "\r\n", $defaults );
        $request .= "\r\n\r\n";
        $request .= $data;

        $result->request = $request;

        fwrite( $fp, $request );

        // Fetch response.
        $response = '';
        while ( !feof( $fp ) && $chunk = fread( $fp, 1024 ) )
        {
            $response .= $chunk;
        }

        fclose( $fp );

        // Parse response.
        list( $split, $result->data ) = explode( "\r\n\r\n", $response, 2 );
        $split = preg_split( "/\r\n|\n|\r/", $split );

        list( $protocol, $code, $text ) = explode( ' ', trim( array_shift( $split ) ), 3 );
        $result->headers = array();

        // Parse headers.
        while ( $line = trim( array_shift( $split ) ) )
        {
            list( $header, $value ) = explode( ':', $line, 2 );
            if ( isset( $result->headers[$header] ) && $header == 'Set-Cookie' )
            {
                // RFC 2109: the Set-Cookie response header comprises the token Set-
                // Cookie:, followed by a comma-separated list of one or more cookies.
                $result->headers[$header] .= ','. trim( $value );
            }
            else
            {
                $result->headers[$header] = trim( $value );
            }
        }

        $responses = array( 100 => 'Continue',
                            101 => 'Switching Protocols',
                            200 => 'OK',
                            201 => 'Created',
                            202 => 'Accepted',
                            203 => 'Non-Authoritative Information',
                            204 => 'No Content',
                            205 => 'Reset Content',
                            206 => 'Partial Content',
                            300 => 'Multiple Choices',
                            301 => 'Moved Permanently',
                            302 => 'Found',
                            303 => 'See Other',
                            304 => 'Not Modified',
                            305 => 'Use Proxy',
                            307 => 'Temporary Redirect',
                            400 => 'Bad Request',
                            401 => 'Unauthorized',
                            402 => 'Payment Required',
                            403 => 'Forbidden',
                            404 => 'Not Found',
                            405 => 'Method Not Allowed',
                            406 => 'Not Acceptable',
                            407 => 'Proxy Authentication Required',
                            408 => 'Request Time-out',
                            409 => 'Conflict',
                            410 => 'Gone',
                            411 => 'Length Required',
                            412 => 'Precondition Failed',
                            413 => 'Request Entity Too Large',
                            414 => 'Request-URI Too Large',
                            415 => 'Unsupported Media Type',
                            416 => 'Requested range not satisfiable',
                            417 => 'Expectation Failed',
                            500 => 'Internal Server Error',
                            501 => 'Not Implemented',
                            502 => 'Bad Gateway',
                            503 => 'Service Unavailable',
                            504 => 'Gateway Time-out',
                            505 => 'HTTP Version not supported'
                            );
        // RFC 2616 states that all unknown HTTP codes must be treated the same as the
        // base code in their class.
        if ( !isset( $responses[$code] ) )
        {
            $code = floor( $code / 100 ) * 100;
        }

        switch ($code)
        {
            case 200: // OK
            case 304: // Not modified
                break;

            case 301: // Moved permanently
            case 302: // Moved temporarily
            case 307: // Moved temporarily
            {
                $location = $result->headers['Location'];

                if ( $retry )
                {
                    $result = nxcAlfrescoUtils::drupalHTTPRequest( $result->headers['Location'], $headers, $method, $data, --$retry );
                    $result->redirect_code = $result->code;
                }
                $result->redirect_url = $location;
            }
            break;

            default:
            {
                $result->error = $text;
            }
        }

        $result->code = $code;
        return $result;
    }

    /**
     * Wrapper around urlencode() which avoids Apache quirks.
     *
     * Should be used when placing arbitrary data in an URL.
     * Notes:
     * - For esthetic reasons, we do not escape slashes. This also avoids a 'feature'
     *   in Apache where it 404s on any path containing '%2F'.
     * - mod_rewrite unescapes %-encoded ampersands, hashes, and slashes when clean
     *   URLs are used, which are interpreted as delimiters by PHP. These
     *   characters are double escaped so PHP will still see the encoded version.
     * - With clean URLs, Apache changes '//' to '/', so every second slash is
     *   double escaped.
     *
     * @param $text
     *   String to encode
     */
    public static function urlEncode( $text )
    {
        return str_replace( array( '%2F', '%26', '%23', '//' ),
                            array( '/', '%2526', '%2523', '/%252F' ),
                            rawurlencode( $text ) );
    }
}
?>
