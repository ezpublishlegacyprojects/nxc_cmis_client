/**
 * Created on: <08-June-2009 11:00:00 vd>
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

/*
 * Functions to support Alfresco in ezoe
 */

/**
 * Generates link to alfresco objects in subitems
 */
eZOEPopupUtils.settings.alfrescoBrowseLinkGenerator = function( n, mode, ed )
{
    if ( n.children_count )
    {
       var tag = document.createElement("a");
       tag.setAttribute('href', 'JavaScript:eZOEPopupUtils.alfrescoBrowse("' + n.url_alias + '");');
       tag.setAttribute('title', ed.getLang('browse') + ': ' + n.url_alias );
       if ( mode !== 'browse' ) ez.$( tag ).addEvent('click', function(){ slides.accordionGoto( 2 ); });

       return tag;
    }

    var tag = document.createElement("span");
    tag.setAttribute('title', n.url_alias );
    return tag;
};

/**
 * Handles return data to create sub items
 */
eZOEPopupUtils.alfrescoBrowseCallBack = function( r, mode, emptyCallBack )
{
    // call back function for the browse() ajax call, generates the html markup with paging and path header (if defined)
    mode = mode || 'browse';
    ez.$( mode + '_progress' ).hide();
    ez.script( 'eZOEPopupUtils.ajaxLoadResponse=' + r.responseText );
    var ed = tinyMCEPopup.editor, tbody = ez.$$('#' + mode + '_box_prev tbody')[0], thead = ez.$$('#' + mode + '_box_prev thead')[0], tfoot = ez.$$('#' + mode + '_box_prev tfoot')[0], tr, td, tag, hasImage, emptyList = true;
    eZOEPopupUtils.removeChildren( tbody.el );
    eZOEPopupUtils.removeChildren( thead.el );
    eZOEPopupUtils.removeChildren( tfoot.el );

    if ( eZOEPopupUtils.ajaxLoadResponse )
    {
        var data = eZOEPopupUtils.ajaxLoadResponse;

        if ( data['error'] )
        {
            tr = document.createElement( "tr" ), td = document.createElement( "td" );

            tr.appendChild( td );
            thead.el.appendChild( tr );

            tr = document.createElement( "tr" ), td = document.createElement( "td" );

            tag = document.createElement( "div" );
            tag.innerHTML = data['error'];
            td.appendChild( tag );

            tr.appendChild( td );

            if ( data['login_url'] )
            {
                td = document.createElement( "td" );
                tag = document.createElement( "a" );
                tag.setAttribute( 'href', data['login_url'] );
                tag.setAttribute( 'title', 'Login' );
                tag.setAttribute( 'target', '_blank' );
                tag.innerHTML = 'login';
                td.appendChild( tag );
                tr.appendChild( td );
            }

            tbody.el.appendChild( tr );

            return false;
        }

        var classGenerator = eZOEPopupUtils.settings.browseClassGenerator, linkGenerator = eZOEPopupUtils.settings.alfrescoBrowseLinkGenerator;
        var fn = 'alfrescoBrowse' + ( mode === 'browse' ? '( "'+ data['node']['url_alias'] + '" ,' : '(' );

        if ( data['node'] && data['node']['name'] )
        {
            tr = document.createElement( "tr" ), td = document.createElement( "td" );
            td.className = 'thight';
            tr.appendChild( td );
            td = document.createElement( "td" )
            td.setAttribute('colspan', '3');

            if ( data['node']['path'] !== false )
            {
                // Prepend root node so you can browse to the root of the installation
                ez.$c( data['node']['path'] ).forEach( function( n )
                {
                    if ( n.url !== false )
                    {
                        tag = document.createElement( "a" );
                        tag.setAttribute( 'href', 'JavaScript:eZOEPopupUtils.alfrescoBrowse( "' + n.url + '" );' );
                        tag.setAttribute( 'title', ed.getLang('advanced.type') + ': ' + n.text );
                        tag.innerHTML = n.text;
                        td.appendChild( tag );
                        tag = document.createElement( "span" );
                        tag.innerHTML = ' / ';
                        td.appendChild( tag );
                    }
                    else
                    {
                        tag = document.createElement( "span" );
                        tag.innerHTML = data['node']['name'];
                        td.appendChild( tag );
                    }
                } );
            }

            tr.appendChild( td );
            thead.el.appendChild( tr );
        }

        if ( data['list'] )
        {
           ez.$c( data['list'] ).forEach( function( n )
           {
               tr = document.createElement( "tr" ), td = document.createElement( "td" ), isImage = false;

               tag = document.createElement( 'input' );
               tag.setAttribute( 'type', 'radio' );
               tag.setAttribute( 'name', 'selectembedobject' );
               tag.className = 'input_noborder';
               tag.setAttribute( 'value', n.id );
               tag.setAttribute( 'title', ed.getLang( 'advanced.select' ) );
               tag.onclick = ez.fn.bind( eZOEPopupUtils.selectByAlfrescoEmbedId, eZOEPopupUtils, n.encoded_id );

               td.appendChild( tag );
               td.className = 'thight';
               tr.appendChild( td );

               td = document.createElement( "td" );
               if ( linkGenerator.call !== undefined )
               {
                   tag = linkGenerator.call( this, n, mode, ed );
               }
               else if ( n.children_count )
               {
                   tag = document.createElement( "a" );
                   tag.setAttribute( 'href', 'JavaScript:eZOEPopupUtils.alfrescoBrowse( "' + n.id + '" );' );
                   tag.setAttribute( 'title', ed.getLang( 'browse' ) + ': ' + n.url_alias );
               }
               else
               {
                   tag = document.createElement( "span" );
                   tag.setAttribute( 'title', n.url_alias );
               }

               tag.innerHTML = n.name;
               td.appendChild( tag );
               tr.appendChild( td );

               td = document.createElement( "td" );
               tag = document.createElement( "span" );
               tag.innerHTML = n.class_name;
               td.appendChild( tag );
               tr.appendChild( td );

               td = document.createElement( "td" );
               tr.appendChild( td );
               tr.className = classGenerator.call( this, n, n.class_identifier == 'image' );
               tbody.el.appendChild( tr );
               emptyList = false;
            } );
        }

        tr = document.createElement( "tr" ), td = document.createElement( "td" );
        if ( data['offset'] !== 0 )
        {
            tag = document.createElement( "a" );
            tag.setAttribute( 'href', 'JavaScript:eZOEPopupUtils.' + fn + ( data['offset'] - data['limit'] ) + ');' );
            tag.innerHTML = '&lt;&lt; ' + ed.getLang( 'advanced.previous' );
            td.appendChild( tag );
        }

        tr.appendChild( document.createElement( "td" ) );
        tr.appendChild( td );
        td = document.createElement( "td" );
        td.setAttribute( 'colspan', '2' );
        if ( ( data['offset'] + data['limit'] ) < data['total_count'] )
        {
            tag = document.createElement( "a" );
            tag.setAttribute( 'href', 'JavaScript:eZOEPopupUtils.' + fn + ( data['offset'] + data['limit'] ) + ');' );
            tag.innerHTML = ed.getLang( 'advanced.next' ) + ' &gt;&gt;';
            td.appendChild( tag );
        }

        tr.appendChild( td );
        tfoot.el.appendChild( tr );
    }

    if ( emptyList && emptyCallBack !== undefined && emptyCallBack.call !== undefined )
    {
        emptyCallBack.call( this, tbody, mode, ed );
    }

    return false;
};

