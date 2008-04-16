<?php
/*
Plugin Name: LeagueManager
Plugin URI: http://wordpress.org/extend/plugins/leaguemanager/
Description: Manage and present sports league results.
Version: 1.2.1
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
define( 'LEAGUEMANAGER_VERSION', '1.2.1' );
define( 'LEAGUEMANAGER_URL', get_bloginfo( 'wpurl' ).'/'.PLUGINDIR.'/leaguemanager' );

include_once( 'leaguemanager.php' );

$leaguemanager = new WP_LeagueManager();


// Actions
add_action( 'admin_head', array(&$leaguemanager, 'add_header_code') );
add_action( 'activate_leaguemanager/plugin-hook.php', array(&$leaguemanager, 'init') );
add_action( 'admin_menu', array(&$leaguemanager, 'add_admin_menu') );
add_action( 'plugins_loaded', array(&$leaguemanager, 'init_widget') );

// Filters
add_filter( 'the_content', array(&$leaguemanager, 'print_standings_table') );
add_filter( 'the_content', array(&$leaguemanager, 'print_competitions_table') );

// Load textdomain for translation
load_plugin_textdomain( 'leaguemanager', $path = PLUGINDIR.'/leaguemanager' );

// Uninstall Plugin
if ( isset($_GET['leaguemanager']) AND 'uninstall' == $_GET['leaguemanager'] AND ( isset($_GET['delete_plugin']) AND 1 == $_GET['delete_plugin'] ) )
	$leaguemanager->uninstall();

/**
 * Wrapper function to display widget statically
 *
 * @param array $args
 */
function leaguemanager_display_widget( $args = array() )
{
	global $leaguemanager;
	$leaguemanager->display_widget( $args );
}
?>