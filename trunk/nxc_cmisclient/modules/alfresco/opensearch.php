<?php
/**
 * Created on: <25-Apr-2009 11:00:00 vd>
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
 * Searches objects in CMIS repository
 */

include_once( 'kernel/common/template.php' );
//include_once( 'extension/nxc_alfresco/classes/nxcalfrescoobjecthandler.php' );

$Module = $Params['Module'];
$http = eZHTTPTool::instance();

$userParameters = $Params['UserParameters'];
$offset = ( isset( $userParameters['offset'] ) and is_numeric( $userParameters['offset'] ) ) ? $userParameters['offset'] : 0;
$limit = $http->hasVariable( 'SearchPageLimit' ) ? $http->variable( 'SearchPageLimit' ) : 10;
$searchText = $http->hasVariable( 'SearchText' ) ? $http->variable( 'SearchText' ) : '';
$errorList = array();
$searchResult = array();
$searchCount = 0;

if ( !empty( $searchText ) )
{
    //$startPage = $offset / $limit + 1;
    $startPage = $offset;
    try
    {
        //$result = nxcAlfrescoObjectHandler::openSearch( $searchText, $startPage, $limit );
        $result = nxcAlfrescoObjectHandler::querySearch( $searchText, 'false', 'false', null, $limit, $startPage );
        $searchCount = $result['total_items'];
        $searchResult = $result['entries'];
    }
    catch ( Exception $error )
    {
        // If access is denied
        if ( $error->getCode() == 403 )
        {
            return $Module->redirectTo( 'alfresco/login' );
        }

        $errorList[] = $error->getMessage();
        eZDebug::writeError( $error->getMessage() );
    }
}

$viewParameters = array( 'offset' => $offset );

$tpl = templateInit();

$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'search_result', $searchResult );
$tpl->setVariable( 'search_text', $searchText );
$tpl->setVariable( 'search_count', $searchCount );
$tpl->setVariable( 'page_limit', $limit );
$tpl->setVariable( 'error_list', $errorList );

$Result = array();

$Result['content'] = $tpl->fetch( "design:alfresco/opensearch.tpl" );
$Result['left_menu'] = 'design:alfresco/alfresco_menu.tpl';
$Result['path'] = array( array( 'text' => ezi18n( 'kernel/content', 'Search' ),
                                'url' => false ) );
?>
