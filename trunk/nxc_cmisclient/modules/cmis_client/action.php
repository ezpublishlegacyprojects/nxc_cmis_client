<?php
/**
 * Created on: <17-Apr-2009 11:00:00 vd>
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
 * Actions for repository
 */

include_once( eZExtension::baseDirectory() . '/nxc_cmisclient/classes/nxccmisutils.php' );

$http = eZHTTPTool::instance();
$Module = $Params['Module'];

$parentURI = $http->hasPostVariable( 'ParentSelfURI' ) ? $Module->functionURI( 'browser' ) . '/' . $http->postVariable( 'ParentSelfURI' ) : $Module->functionURI( 'browser' );
$parentChildrenURI = $http->hasPostVariable( 'ParentChildrenURI' ) ? nxcCMISUtils::getDecodedUri( $http->postVariable( 'ParentChildrenURI' ) ) : false;

// Will redirect to this path after module processing
$http->setSessionVariable( 'CMISParentSelfURI', $parentURI );
$http->setSessionVariable( 'CMISParentChildrenURI', $parentChildrenURI );

if ( $http->hasPostVariable( 'RemoveButton' ) )
{
    if ( $http->hasPostVariable( 'CMISDeleteURIArray' ) )
    {
        $deleteURIArray = $http->postVariable( 'CMISDeleteURIArray' );

        if ( is_array( $deleteURIArray ) && count( $deleteURIArray ) > 0 )
        {
            $http->setSessionVariable( 'CMISDeleteURIArray', $deleteURIArray );

            return $Module->redirectTo( $Module->functionURI( 'remove' ) );
        }
    }

    return $Module->redirectTo( $parentURI );
}
elseif ( $http->hasPostVariable( 'NewButton' ) )
{
    $classID = $http->hasPostVariable( 'ClassID' ) ? strtolower( $http->postVariable( 'ClassID' ) ) : false;
    if ( !$classID or !$parentChildrenURI )
    {
        return $Module->redirectTo( $parentURI );
    }

    $http->setSessionVariable( 'CMISClassID', $classID );

    return $Module->redirectTo( $Module->functionURI( 'edit' ) . '/' );
}
else if ( $http->hasPostVariable( 'CurrentSelfURI' )  )
{
    $currentSelfURI = $http->postVariable( 'CurrentSelfURI' );

    if ( $http->hasPostVariable( 'ActionRemove' ) )
    {
        $http->setSessionVariable( 'CMISCurrentSelfURI', $Module->functionURI( 'browser' ) . '/' . $currentSelfURI );
        $http->setSessionVariable( 'CMISDeleteURIArray', array( $currentSelfURI ) );

        return $Module->redirectTo( $Module->functionURI( 'remove' ) );
    }
    elseif ( $http->hasPostVariable( 'ActionEdit' ) )
    {
        return $Module->redirectTo( $Module->functionURI( 'edit' ) . '/' . $currentSelfURI );
    }

    return $Module->redirectTo( $parentURI );
}
else if ( !isset( $result ) )
{
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

// return module contents
$Result = array();
$Result['content'] = isset( $result ) ? $result : null;

?>
