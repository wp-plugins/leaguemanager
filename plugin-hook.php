<?php
/*
Plugin Name: LeagueManager
Plugin URI: http://wordpress.org/extend/plugins/leaguemanager/
Description: Manage and present sports league results.
Version: 2.6
Author: Kolja Schleich

Copyright 2007-2008  Kolja Schleich  (email : kolja.schleich@googlemail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

	
// Load LeagueManager Class
require_once(ABSPATH.'wp-admin/includes/template.php');
include_once( LEAGUEMANAGER_PATH.'/leaguemanager.php' );

$leaguemanager = new LeagueManager();


register_activation_hook(__FILE__, array(&$leaguemanager, 'activate') );
// Actions
add_action( 'admin_head', array(&$leaguemanager, 'addHeaderCode') );
add_action( 'wp_head', array(&$leaguemanager, 'addHeaderCode') );
add_action( 'admin_menu', array(&$leaguemanager, 'addAdminMenu') );
add_action( 'widgets_init', array(&$leaguemanager, 'activateWidget') );

// TinyMCE Button
add_action( 'init', array(&$leaguemanager, 'addTinyMCEButton') );
add_filter('tiny_mce_version', array(&$leaguemanager, 'changeTinyMCEVersion') );

// Add meta box to post screen
add_meta_box( 'leaguemanager', __('Match Report','leaguemanager'), array(&$leaguemanager, 'addMetaBox'), 'post', 'side' );
add_action( 'publish_post', array(&$leaguemanager, 'editMatchReport') );
add_action( 'edit_post', array(&$leaguemanager, 'editMatchReport') );

if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook(__FILE__, array(&$leaguemanager, 'uninstall'));

/**
 * Wrapper function to display widget statically
 *
 * @param array $args
 */
if ( !function_exists("leaguemanager_display_widget") ) {
function leaguemanager_display_widget( $args = array() )
{
	global $leaguemanager;
	$leaguemanager->displayWidget( $args );
}
}
?>
