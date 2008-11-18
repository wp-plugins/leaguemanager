<?php
/*
Plugin Name: LeagueManager
Plugin URI: http://wordpress.org/extend/plugins/leaguemanager/
Description: Manage and present sports league results.
Version: 2.2
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
if ( !defined( 'WP_CONTENT_URL' ) )
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( !defined( 'WP_PLUGIN_URL' ) )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( !defined( 'WP_CONTENT_DIR' ) )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( !defined( 'WP_PLUGIN_DIR' ) )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
	
define( 'LEAGUEMANAGER_VERSION', '2.2' );
define( 'LEAGUEMANAGER_URL', WP_PLUGIN_URL.'/leaguemanager' );
define( 'LEAGUEMANAGER_PATH', WP_PLUGIN_DIR.'/leaguemanager' );

// Load LeagueManager Class
include_once( dirname (__FILE__).'/leaguemanager.php' );

$leaguemanager = new WP_LeagueManager();

register_activation_hook(__FILE__, array(&$leaguemanager, 'init') );
// Actions
add_action( 'wp_head', array(&$leaguemanager, 'addHeaderCode') );
add_action( 'admin_head', array(&$leaguemanager, 'addHeaderCode') );
add_action( 'admin_menu', array(&$leaguemanager, 'addAdminMenu') );
add_action( 'widgets_init', array(&$leaguemanager, 'initWidget') );

// Filters
add_filter( 'the_content', array(&$leaguemanager, 'printStandingsTable') );
add_filter( 'the_content', array(&$leaguemanager, 'printMatchTable') );
add_filter( 'the_content', array(&$leaguemanager, 'printCrossTable') );

// TinyMCE Button
add_action( 'init', array(&$leaguemanager, 'addTinyMCEButton') );
add_filter('tiny_mce_version', array(&$leaguemanager, 'changeTinyMCEVersion') );

// Load textdomain for translation
load_plugin_textdomain( 'leaguemanager', $path = WP_PLUGIN_DIR.'/leaguemanager' );

if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook(__FILE__, array(&$leaguemanager, 'uninstall'));

// Uninstall Plugin
if ( version_compare($wp_version, '2.7-hemorrhage', '<') && isset($_GET['leaguemanager']) AND 'uninstall' == $_GET['leaguemanager'] AND ( isset($_GET['delete_plugin']) AND 1 == $_GET['delete_plugin'] ) )
	$leaguemanager->uninstall();

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