<?php
/**
 * Definition of nxcCMISUtils class
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
 * Common CMIS Related Utility Functions for CMIS module
 *
 * @file nxccmisutils.php
 */

class nxcCMISUtils
{
    const PROPERTY_TPL = 'cmis:object/cmis:properties/cmis:*[@cmis:name="%NAME%"]/cmis:value';
    const COLLECTION_TPL = '/app:service/app:workspace/app:collection[@cmis:collectionType="%NAME%"]';

    /**
     * Logs in the user.
     *
     * @return String ticket
     */
    public static function login( $user = false, $password = false, $endPoint = false )
    {
        $ini      = eZINI::instance( 'cmis.ini' );
        $user     = !$user     ? ( $ini->hasVariable( 'CMISSettings', 'DefaultUser' )     ? $ini->variable( 'CMISSettings', 'DefaultUser' )     : '' ) : $user;
        $password = !$password ? ( $ini->hasVariable( 'CMISSettings', 'DefaultPassword' ) ? $ini->variable( 'CMISSettings', 'DefaultPassword' ) : '' ) : $password;
        $endPoint = !$endPoint ? self::getEndPoint() : $endPoint;
        $http     = eZHTTPTool::instance();

        $http->setSessionVariable( 'CMISUser', $user );
        $http->setSessionVariable( 'CMISPassword', $password );
    }

    /**
     * Logs out the user.
     *
     * @TODO Logout from CMIS Repository as well
     */
    public static function logout()
    {
        $http = eZHTTPTool::instance();

        $http->removeSessionVariable( 'CMISUser' );
        $http->removeSessionVariable( 'CMISPassword' );
    }

    /**
     * @return string User name if it logged in.
     */
    public static function getLoggedUserName()
    {
        $ini = eZINI::instance( 'cmis.ini' );
        $http = eZHTTPTool::instance();

        $user = $ini->hasVariable( 'CMISSettings', 'DefaultUser' ) ? $ini->variable( 'CMISSettings', 'DefaultUser' ) : '';
        $storedUser = $http->hasSessionVariable( 'CMISUser' ) ? $http->sessionVariable( 'CMISUser' ) : '';

        return $user != $storedUser ? $storedUser : '' ;
    }

    /**
     * Returns End Point URI.
     *
     * @return string
     */
    public static function getEndPoint()
    {
        $name = __METHOD__;
        if ( isset( $GLOBALS[$name] ) and $GLOBALS[$name] )
        {
            return $GLOBALS[$name];
        }

        $ini = eZINI::instance( 'cmis.ini' );
        $GLOBALS[$name] = $ini->hasVariable( 'CMISSettings', 'EndPoint' ) ? $ini->variable( 'CMISSettings', 'EndPoint' ) : false;

        return $GLOBALS[$name];
    }

    /**
     * Invokes url
     *
     * @return string Response data
     */
    public static function invokeService( $url, $method = 'GET', $headers = array(), $data = null )
    {
        $name = __METHOD__ . '_' . $url . '_' . $method . '_' . implode( '_', $headers ) . '_' . $data;

        if ( isset( $GLOBALS[$name] ) )
        {
            return $GLOBALS[$name];
        }

        // Check if uri does not contain 'http'. If so prepend end point to it
        if ( !empty( $url ) and strpos( $url, 'http' ) === false )
        {
            $url = self::getHost( self::getEndPoint() ) . $url;
        }

        $response = self::httpRequest( $url, $method, $headers, $data );

        if ( $response->code == 200 or $response->code == 201 )
        {
            $GLOBALS[$name] = $response->data;

            return $response->data;
        }
        elseif ( $response->code == 204 )
        {
            return true;
        }
        elseif ( in_array( $response->code, array( 403, 401, 302 ) ) )
        {
            // @TODO: Create custom exceptions
            throw new Exception( 'Access denied', 403 );
        }

        eZDebug::writeError( 'Failed to invoke service [' . $method . '] ' . $url . ' Code:' . $response->code . "\n" . $response->error, __METHOD__ );

        // Do not need to throw exception there because the function can be called under tpl
        // If so, execption will not be handled properly
        //throw new Exception( 'Failed to invoke service [' . $method . '] ' . $url . ' Code:' . $response->code );

        return false;
    }

    /**
     * Requests \a $url by HTTP \a $method, \a $headers and post \a $data
     *
     * @return stdClass with data, code and error fields
     */
    public static function httpRequest( $url, $method = 'GET', $headers = array(), $data = null )
    {
        // Prepare curl session
        $session = curl_init( $url );
        curl_setopt( $session, CURLOPT_VERBOSE, 1 );

        // Add additonal headers
        curl_setopt( $session, CURLOPT_HTTPHEADER, $headers );

        // Don't return HTTP headers. Do return the contents of the call
        curl_setopt( $session, CURLOPT_HEADER, false );
        curl_setopt( $session, CURLOPT_RETURNTRANSFER, true );

        $http = eZHTTPTool::instance();
        $user = $http->hasSessionVariable( 'CMISUser' ) ? $http->sessionVariable( 'CMISUser' ) : false;
        // @TODO: It is quite bad to store pass in session
        $password = $http->hasSessionVariable( 'CMISPassword' ) ? $http->sessionVariable( 'CMISPassword' ) : '';

        if ( $user )
        {
            curl_setopt( $session, CURLOPT_USERPWD, "$user:$password" );
        }

        curl_setopt( $session, CURLOPT_CUSTOMREQUEST, $method );

        if ( in_array( $method, array( 'POST', 'PUT' ) ) )
        {
            curl_setopt( $session, CURLOPT_POSTFIELDS, $data );
        }

        // Make the call
        $returnData = curl_exec( $session );
        $error = $returnData === false ? curl_error( $session ) : '';

        // Get return http status code
        $httpcode = curl_getinfo( $session, CURLINFO_HTTP_CODE );

        // Close HTTP session
        curl_close( $session );

        // Prepare return
        $result = new stdClass();
        $result->code = $httpcode;
        $result->data = $returnData;
        $result->error = $error;

        return $result;
    }

    /**
     * Provides CMIS version that is supported by repository
     *
     * @return string
     */
    public static function getCMISVersionSupported()
     {
         $name = __METHOD__;
         if ( isset( $GLOBALS[$name] ) )
         {
             return $GLOBALS[$name];
         }

         $response = self::invokeService( self::getEndPoint() );

         $cmisVersion = self::processXML( $response, '//cmis:cmisVersionSupported' );
         if ( !isset( $cmisVersion[0] ) )
         {
             $cmisVersion = self::processXML( $response, '//cmis:cmisVersionsSupported' );
         }

         // Remove words from version: was 0.61c, now 0.61
         $version = isset( $cmisVersion[0] ) ? (string)(float) $cmisVersion[0] : false;

         if ( $version )
         {
             $GLOBALS[$name] = $version;
         }

         return $version;
     }

     /**
      * This service is used to retrieve information about the CMIS repository and the capabilities it supports.
      *
      * @return stdClass
      */
     public static function getRepositoryInfo()
     {
         $name = __METHOD__;
         if ( isset( $GLOBALS[$name] ) )
         {
             return $GLOBALS[$name];
         }

         $response = self::invokeService( self::getEndPoint() );

         $repoInfo = self::processXML( $response, self::getVersionSpecificValue( '/app:service/app:workspace/cmis:repositoryInfo' ) );
         if ( !isset( $repoInfo[0] ) )
         {
             throw new Exception( ezi18n( 'cmis', 'Could not fetch repository info:'  ) . "\n$response" );
         }

         $collectionRootChildren = self::processXML( $response, self::getVersionSpecificCollection( self::getVersionSpecificValue( 'rootchildren' ) ) );
         $collectionTypes = self::processXML( $response, self::getVersionSpecificCollection( self::getVersionSpecificValue( 'typesdescendants' ) ) );
         $collectionQuery = self::processXML( $response, self::getVersionSpecificCollection( self::getVersionSpecificValue( 'query' ) ) );

         $repository = new stdClass();
         $repository->repositoryId = (string) self::getXMLvalue( $repoInfo[0], 'cmis:repositoryId' );
         $repository->repositoryName = (string) self::getXMLvalue( $repoInfo[0], 'cmis:repositoryName' );
         $repository->repositoryDescription = (string) self::getXMLvalue( $repoInfo[0], 'cmis:repositoryDescription' );
         $repository->vendorName = (string) self::getXMLvalue( $repoInfo[0], 'cmis:vendorName' );
         $repository->productName = (string) self::getXMLvalue( $repoInfo[0], 'cmis:productName' );
         $repository->productVersion = (string) self::getXMLvalue( $repoInfo[0], 'cmis:productVersion' );
         $repository->rootFolderId = (string) self::getXMLvalue( $repoInfo[0], 'cmis:rootFolderId' );
         $repository->cmisVersionSupported = self::getCMISVersionSupported();

         $repository->children = isset( $collectionRootChildren[0] ) ? (string) self::getXMLAttribute( $collectionRootChildren[0], 'href' ) : '';
         $repository->types = isset( $collectionTypes[0] ) ? (string) self::getXMLAttribute( $collectionTypes[0], 'href' ) : '';
         $repository->query = isset( $collectionQuery[0] ) ? (string) self::getXMLAttribute( $collectionQuery[0], 'href' ) : '';

         $response = self::invokeService( $repository->types );

         $keyList = self::processXML( $response, self::getVersionSpecificValue( '//cmis:typeId' ) );
         $valueList = self::processXML( $response, self::getVersionSpecificValue( '//cmis:baseType' ) );

         $cmisTypes = array();
         foreach( $keyList as $keyEntry => $key )
         {
             $curKey = strtolower( str_replace( 'cmis:', '', $key ) );
             $cmisTypes[$curKey] = str_replace( 'cmis:', '', $valueList[$keyEntry] );
         }

         $repository->cmisTypes = $cmisTypes;
         $GLOBALS[$name] = $repository;

         return $repository;
     }

     /**
      * Provides root folder id
      *
      * @return string
      */
     public static function getRootFolderId()
     {
         $repositoryInfo = nxcCMISUtils::getRepositoryInfo();

         if ( !isset( $repositoryInfo->rootFolderId ) )
         {
             throw new Exception( ezi18n( 'cmis', "Could not fetch 'rootFolderId' from repository info" ) );
         }

         return $repositoryInfo->rootFolderId;
     }

     /**
      * Provides base type by \a $objectTypeId
      *
      * @return string
      */
     public static function getBaseType( $objectTypeId )
     {
         $cmisTypes = self::getCMISTypes();
         if ( !isset( $cmisTypes[$objectTypeId] ) )
         {
             throw new Exception( ezi18n( 'cmis', 'Unknown ObjectTypeId:'  ) . " '$objectTypeId'" );
         }

         return $cmisTypes[$objectTypeId];
     }

     /**
      * Provides cmis types
      *
      * @return array
      */
     public static function getCMISTypes()
     {
         $repositoryInfo = nxcCMISUtils::getRepositoryInfo();

         return $repositoryInfo->cmisTypes;
     }

     /**
      * Provides object type id by \a $baseType
      *
      * @return string
      * @TODO It returns first found value of provided base type
      */
     public static function getObjectTypeId( $baseType )
     {
         $types = self::getCMISTypes();
         $result = $baseType;

         foreach ( $types as $objectTypeId => $type )
         {
             if ( $type == $baseType )
             {
                 $result = $objectTypeId;
                 break;
             }
         }

         return $objectTypeId;
     }

     /**
      * Provides value if it differs between CMIS versions
      *
      * @return string
      */
     public static function getVersionSpecificValue( $value )
     {
         $versionSpecificValues = array( '/app:service/app:workspace/cmis:repositoryInfo'
                                            => array( '0.62' => '/app:service/app:workspace/cmisra:repositoryInfo' ),
                                         self::COLLECTION_TPL
                                            => array( '0.62' => '/app:service/app:workspace/app:collection[@cmisra:collectionType="%NAME%"]' ),
                                         'rootchildren'
                                            => array( '0.62' => 'root' ),
                                         'typesdescendants'
                                            => array( '0.62' => 'types' ),
                                         '//cmis:typeId'
                                            => array( '0.62' => '//cmis:id' ),
                                         '//cmis:baseType'
                                            => array( '0.62' => '//cmis:baseTypeId' ),
                                         self::PROPERTY_TPL
                                            => array( '0.62' => 'cmisra:object/cmis:properties/cmis:*[@pdid="cmis:%NAME%"]/cmis:value' ),
                                         'BaseType'
                                            => array( '0.62' => 'BaseTypeId' ),
                                         'children'
                                            => array( '0.62' => 'down' ),
                                         'parents'
                                            => array( '0.62' => 'up' ),
                                         'descendants'
                                            => array( '0.62' => 'down' ),
                                         'type'
                                            => array( '0.62' => 'describedby' ),
                                         'cmis:object'
                                            => array( '0.62' => 'cmisra:object' ),
                                         'children_with_skip'
                                            => array( '0.62' => false )
                                         );

         $currentVersion = self::getCMISVersionSupported();

         return isset( $versionSpecificValues[$value][$currentVersion] ) ? $versionSpecificValues[$value][$currentVersion] : $value;
     }

     /**
      * Provides version specific object property
      *
      * @return string
      */
     public static function getVersionSpecificValueByTpl( $name, $tpl, $tplValue = '%NAME%' )
     {
         $property = self::getVersionSpecificValue( $tpl );

         return str_replace( $tplValue, $name, $property );
     }

     /**
      * Provides version specific object property
      *
      * @return string
      */
     public static function getVersionSpecificProperty( $name )
     {
         return self::getVersionSpecificValueByTpl( $name, self::PROPERTY_TPL );
     }

     /**
      * Provides version specific collection value
      *
      * @return string
      */
     public static function getVersionSpecificCollection( $name )
     {
         return self::getVersionSpecificValueByTpl( $name, self::COLLECTION_TPL );
     }

     /**
      * Removes namespace \a $ns from \a $value
      *
      * @TODO: Is it needed to fetch all namespaces and remove it from the string?
      */
     public static function removeNameSpace( $value, $ns = 'cmis' )
     {
         return str_replace( $ns . ':', '', $value );
     }

     /**
      * Fetches entries from \a $xml
      *
      * @return List of SimpleXMLElement
      */
     public static function fetchEntries( $xml, $name = 'entry' )
     {
         return nxcCMISUtils::processXML( $xml, '//atom:' . $name );
     }

     /**
      * Fetches entry from \a $xml
      *
      * @return SimpleXMLElement
      */
     public static function fetchEntry( $xml, $name = 'entry' )
     {
         $entries = self::fetchEntries( $xml, $name );

         return isset( $entries[0] ) ? $entries[0] : false;
     }

     /**
      * Provides 'href' value of a link
      *
      * @return string
      */
     public static function getLinkUri( $entry, $name )
     {
         if ( !$entry )
         {
             return null;
         }

         $linkXML = $entry->xpath( '*[@rel="' . $name . '"]' );

         return isset( $linkXML[0] ) ? nxcCMISUtils::getXMLAttribute( $linkXML[0] , 'href' ) : '';
     }

     /**
      * Fetches xml data by \a $uri.
      * If the xml contains <entry> and <ectry> contains <link rel="NAME" href="VALUE">
      * "VALUE" will be fetched
      *
      * @return string
      */
     public static function fetchLinkValue( $uri, $value )
     {
         if ( empty( $uri ) )
         {
             return '';
         }

         $name = __METHOD__ . $uri . $value;
         if ( isset( $GLOBALS[$name] ) )
         {
             return $GLOBALS[$name];
         }

         $result = '';
         try
         {
             $xml = self::invokeService( $uri );
             $entry = self::fetchEntry( $xml );
             if ( $entry )
             {
                 $result = self::getHostlessUri( nxcCMISUtils::getLinkUri( $entry, $value ) );
                 $GLOBALS[$name] = $result;
             }

         }
         catch ( Exception $error )
         {
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
         if ( empty( $xml ) )
         {
             return '';
         }

         try
         {
             // @ prevents uneeded PHP wanrings
             $cmisService = @( new SimpleXMLElement( $xml ) );
         }
         catch ( Exception $e )
         {
             throw new Exception( "Bad XML: '" . $xml . "'" );
         }

         foreach( self::getNamespaceList() as $ns => $value )
         {
             $cmisService->registerXPathNamespace( $ns, $value );
         }

         $entry = $cmisService->xpath( $xpath );

         return $entry;
     }

     /**
      * Get XML node value.
      * $entry CMIS XML Node.
      * $xpath xpath expression.
      */
     public static function getXMLValue( SimpleXMLElement $entry, $xpath )
     {
         if ( is_null( $entry ) )
         {
             return null;
         }

         $value = $entry->xpath( $xpath );

         return isset( $value[0] ) ? $value[0] : null;
     }

     /**
      * Fetches value from simple xml element
      *
      * @return SimpleXMLElement|bool
      */
     public static function getValue( SimpleXMLElement $entry, $name, $ns = 'atom' )
     {
         if ( !is_object( $entry ) )
         {
             return null;
         }

         // First try to fetch from entry
         if ( $entry->$name )
         {
             return $entry->$name;
         }

         $result = false;

         // @TODO: Review it for necessity
         // Go throught namespaces and try to find value in these namespaces
         $nsList = $entry->getNamespaces( true );
         foreach ( array_keys( $nsList ) as $nsName )
         {
             if ( !empty( $nsName ) )
             {
                 $nsName .= ':';
             }

             $value = $entry->xpath( "//$nsName$name" );

             if ( isset( $value[0] ) )
             {
                 $result = $value[0];
                 break;
             }
         }

         return $result;
     }

     /**
      * Provides attribute value
      *
      * @return string
      */
     public static function getXMLAttribute( SimpleXMLElement $entry, $name )
     {
         if ( is_null( $entry ) )
         {
             return null;
         }

         $attrs = $entry->attributes();

         return isset( $attrs[$name] ) ? (string) $attrs[$name] : null;
     }

     /**
      * Provides host that is located in \a $url
      *
      * @return string
      */
     public static function getHost( $url = false )
     {
         if ( !$url )
         {
             $url = self::getEndPoint();
         }

         return preg_match( "/^(http|https):\/\/.+?\//", $url, $regs ) ? $regs[0] : '';
     }

     /**
      * Provides encoded uri string
      *
      * @var string
      */
     public static function getEncodedUri( $uri )
     {
         return base64_encode( $uri );
     }

     /**
      * Provides decoded uri string
      *
      * @var string
      */
     public static function getDecodedUri( $uri )
     {
         return base64_decode( $uri );
     }

     /**
      * Provides url without protocol, host and port
      *
      * @return string
      */
     public static function getHostlessUri( $uri )
     {
         $host = self::getHost( $uri );

         return !empty( $host ) ? str_replace( $host, '', $uri ) : $uri;
     }

     /**
      * Provides namespaces
      *
      * @return array
      */
     public static function getNamespaceList()
     {
         return array( 'atom' => 'http://www.w3.org/2005/Atom',
                       'app'  => 'http://www.w3.org/2007/app',
                       'cmis' => 'http://docs.oasis-open.org/ns/cmis/core/200901',
                       'cmisra' => 'http://docs.oasis-open.org/ns/cmis/restatom/200901' );
     }

     /**
      * Creates DOM document
      *
      * @return DOMDocument
      */
     public static function createDocument()
     {
         $doc = new DOMDocument( '1.0', 'UTF-8' );
         $doc->formatOutput = true;

         return $doc;
     }

    /**
     * Creates root node by \a $documentType
     */
    public static function createRootNode( DOMDocument $doc, $documentType, $mainNs = 'atom' )
    {
        $namespaces = self::getNamespaceList();
        $root = $doc->createElementNS( $namespaces[$mainNs], $documentType );
        // @TODO: Review it, quite strange behaviour
        $addNs = $mainNs == 'atom' ? 'app' : 'atom';

        foreach( array( $addNs, 'cmis', 'cmisra' ) as $prefix )
        {
            $root->setAttributeNS( 'http://www.w3.org/2000/xmlns/', 'xmlns:' . $prefix, $namespaces[$prefix] );
        }

        return $root;
    }

    /**
     * Creates headers for HTTPD request
     *
     * @return array
     */
    public static function createHeaders( $length = 0, $contentType = 'application/atom+xml;type=entry' )
    {
        return array( 'Content-type: ' . $contentType,
                      'Content-length: ' . $length,
                      'MIME-Version: 1.0' );
    }
}
?>
