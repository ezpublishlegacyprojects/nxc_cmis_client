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

$http = eZHTTPTool::instance();
$Module = $Params['Module'];

$userParameters = $Params['UserParameters'];
$select = !empty($userParameters["select"]) ?
			$userParameters["select"] :
			null;
$_SESSION['ktdmsSelect'] = $select ? true : false;

$parentObjectURI = $http->hasPostVariable( 'ParentObjectURI' ) ? $http->postVariable( 'ParentObjectURI' ) : $Module->functionURI( 'browser' );

// Will redirect to this path after module processing
$http->setSessionVariable( 'ParentAlfrescoObjectURI', $parentObjectURI );

if ( $http->hasPostVariable( 'RemoveButton' ) )
{
    if ( $http->hasPostVariable( 'DeleteIDArrayAlfresco' ) )
    {
        $deleteIDArray = $http->postVariable( 'DeleteIDArrayAlfresco' );

        if ( is_array( $deleteIDArray ) && count( $deleteIDArray ) > 0 )
        {
            $http->setSessionVariable( 'DeleteIDArrayAlfresco', $deleteIDArray );

            return $Module->redirectTo( $Module->functionURI( 'remove' ) . '/' );
        }
    }

    return $Module->redirectTo( $parentObjectURI );
}
elseif ( $http->hasPostVariable( 'NewButton' ) )
{
    $classID = $http->hasPostVariable( 'ClassID' ) ? $http->postVariable( 'ClassID' ) : false;
    $parentObjectID = $http->hasPostVariable( 'ParentObjectID' ) ? $http->postVariable( 'ParentObjectID' ) : false;
    if ( !$classID or !$parentObjectID )
    {
        return $Module->redirectTo( $parentObjectURI );
    }

    $http->setSessionVariable( 'AlfrescoClassID', $classID );
    $http->setSessionVariable( 'ParentAlfrescoObjectID', $parentObjectID );

    return $Module->redirectTo( $Module->functionURI( 'edit' ) . '/' );
}
else if ( $http->hasPostVariable( 'CurrentObjectID' )  )
{
    $objectID = $http->postVariable( 'CurrentObjectID' );

    if ( $http->hasPostVariable( 'ActionRemove' ) )
    {
        $currentObjectURI = $http->hasPostVariable( 'CurrentObjectURI' ) ? $http->postVariable( 'CurrentObjectURI' ) : $parentURI;
        $http->setSessionVariable( 'CurrentAlfrescoObjectURI', $currentObjectURI );
        $http->setSessionVariable( 'DeleteIDArrayAlfresco', array( $objectID ) );

        return $Module->redirectTo( $Module->functionURI( 'remove' ) . '/' );
    }
    elseif ( $http->hasPostVariable( 'ActionEdit' ) )
    {
        return $Module->redirectTo( $Module->functionURI( 'edit' ) . '/(id)/' . urlencode( $objectID ) );
    }

    return $Module->redirectTo( $parentObjectURI );
}
else if ( !isset( $result ) )
{
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

// return module contents
$Result = array();
$Result['content'] = isset( $result ) ? $result : null;

?>
