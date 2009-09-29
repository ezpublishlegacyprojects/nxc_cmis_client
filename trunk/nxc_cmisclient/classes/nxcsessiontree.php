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

class nxcSessionTree
{
	/*%navigation tree for ktdms start%*/
	function nav_tree_modify($userParameters)
	{
		$ret_value = $userParameters['id'];
		$cur_node = array();
		$prev_node = array();
		$cur_node['parent_path'] = !empty($userParameters["parent_id"]) ?
			$userParameters["parent_id"] :
			null;
		$back = !empty($userParameters["back"]) ?
			$userParameters["back"] :
			null;
		$cur_node['parent_name'] = !empty($userParameters["parent_name"]) ?
			$userParameters["parent_name"] :
			null;

		if ($back){
			$ret_value = end($_SESSION['parent_tree']);
			$ret_value = $ret_value['parent_path'];
			if (empty($ret_value)) {
				$this->nav_tree_clear();
			} else {
				$this->nav_tree_clear_last();
			}
		} else {
			if (empty($cur_node['parent_path'])) {
				$this->nav_tree_clear();
			} else {
				$prev_node = end($_SESSION['parent_tree']);
				if (
					($cur_node['parent_path'] != $prev_node['parent_path']) ||
					(empty($_SESSION['parent_tree']))
				) {
					$_SESSION['parent_tree'][] = $cur_node;
					$_SESSION['parent_path_last'] = $cur_node['parent_path'];
					$_SESSION['parent_name_last'] = $cur_node['parent_name'];
				}
			}
		}

		return $ret_value;
	}
	
	function nav_tree_clear()
	{
		try {
			unset($_SESSION['parent_tree']);
			unset($_SESSION['parent_path_last']);
			unset($_SESSION['parent_name_last']);
	
			return true;
		} catch ( Exception $error ) {
			eZDebug::writeError( $error->getMessage() );
	
			return false;
		}
	}
	
	function nav_tree_clear_last()
	{
		try {
			end($_SESSION['parent_tree']);
			$last = key($_SESSION['parent_tree']);
			unset($_SESSION['parent_tree'][$last]);

			unset($_SESSION['parent_path_last']);
			unset($_SESSION['parent_name_last']);
			$cur_node = end($_SESSION['parent_tree']);
			$_SESSION['parent_path_last'] = $cur_node['parent_path'];
			$_SESSION['parent_name_last'] = $cur_node['parent_name'];

			return true;
		} catch ( Exception $error ) {
			eZDebug::writeError( $error->getMessage() );
	
			return false;
		}
	}

	/*%navigation tree for ktdms end%*/

	/**
	* This function for debug output
	*/
	public static function pa ($array)
	{
		print('<pre>');
		var_dump($array);
		print('</pre>');
	}
}
