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

/**
* Loading class for the WordPress plugin LeagueManager
* 
* @author 	Kolja Schleich
* @package	LeagueManager
* @copyright 	Copyright 2009
*/

class LeagueManagerLoader extends LeagueManager
{
	/**
	 * plugin version
	 *
	 * @var string
	 */
	var $version = '2.6';
	
	
	/**
	 * database version
	 *
	 * @var string
	 */
	var $dbversion = '1.0';
	
	
	/**
	 * constructor
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		// Load language file
		$this->loadTextdomain();

		$this->defineTables();
		$this->defineConstants();
		$this->loadLibraries();
	}
	function LeagueManagerLoader()
	{
		$this->__construct();
	}
	
	
	/**
	 * define constants
	 *
	 * @param none
	 * @return void
	 */
	function defineConstants()
	{
		if ( !defined( 'WP_CONTENT_URL' ) )
			define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
		if ( !defined( 'WP_PLUGIN_URL' ) )
			define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
		if ( !defined( 'WP_CONTENT_DIR' ) )
			define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		if ( !defined( 'WP_PLUGIN_DIR' ) )
			define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
			
		define( 'LEAGUEMANAGER_VERSION', $this->version );
		define( 'LEAGUEMANAGER_DBVERSION', $this->dbversion );
		define( 'LEAGUEMANAGER_URL', WP_PLUGIN_URL.'/leaguemanager' );
		define( 'LEAGUEMANAGER_PATH', WP_PLUGIN_DIR.'/leaguemanager' );
	}
	
	
	/**
	 * define database tables
	 *
	 * @param none
	 * @return void
	 */
	function defineTables()
	{
		global $wpdb;
		$wpdb->leaguemanager = $wpdb->prefix . 'leaguemanager_leagues';
		$wpdb->leaguemanager_teams = $wpdb->prefix . 'leaguemanager_teams';
		$wpdb->leaguemanager_matches = $wpdb->prefix . 'leaguemanager_matches';
	}
	
	
	/**
	 * load libraries
	 *
	 * @param none
	 * @return void
	 */
	function loadLibraries()
	{
		// Global libraries
		require_once (dirname (__FILE__) . '/lib/core.php');
		require_once (dirname (__FILE__) . '/lib/widget.php');
		
		if ( is_admin() ) {
			require_once (dirname (__FILE__) . '/admin/admin.php');
			require_once (dirname (__FILE__) . '/lib/image.php');
		} else {
			require_once (dirname (__FILE__) . '/lib/shortcodes.php');
		}
	}
	
	
	/**
	 * load textdomain
	 *
	 * @param none
	 * @return void
	 */
	function loadTextdomain()
	{
		load_plugin_textdomain( 'leaguemanager', false, dirname( plugin_basename(__FILE__) ) .'/languages' );
	}
	
	
	/**
	 * load scripts
	 *
	 * @param none
	 * @return void
	 */
	function loadScripts()
	{
	}
	
	
	/**
	 * load styles
	 *
	 * @param none
	 * @return void
	 */
	function loadStyles()
	{
	}
	
	
	/**
	 * Activate plugin
	 *
	 * @param none
	 */
	function activate()
	{
		global $wpdb;
		include_once( ABSPATH.'/wp-admin/includes/upgrade.php' );
		
		$options = array();
		$options['version'] = LEAGUEMANAGER_VERSION;
		$options['colors']['headers'] = '#dddddd';
		$options['colors']['rows'] = array( '#ffffff', '#efefef' );
		
		$old_options = get_option( 'leaguemanager' );
		if ( version_compare($old_options['version'], LEAGUEMANAGER_VERSION, '<') ) {
			require_once( LEAGUEMANAGER_PATH . '/update.php' );
			update_option( 'leaguemanager', $options );
		}
		
		$charset_collate = '';
		if ( $wpdb->supports_collation() ) {
			if ( ! empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}
		
		$create_leagues_sql = "CREATE TABLE {$wpdb->leaguemanager} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`title` varchar( 30 ) NOT NULL ,
						`forwin` tinyint( 4 ) NOT NULL default '2',
						`fordraw` tinyint( 4 ) NOT NULL default '1',
						`forloss` tinyint( 4 ) NOT NULL default '0',
						`match_calendar` tinyint( 1 ) NOT NULL default '1',
						`type` tinyint( 1 ) NOT NULL default '2',
						`num_match_days` tinyint( 4 ) NOT NULL,
						`show_logo` tinyint( 1 ) NOT NULL default '0',
						`active` tinyint( 1 ) NOT NULL default '1' ,
						PRIMARY KEY ( `id` )) $charset_collate";
		maybe_create_table( $wpdb->leaguemanager, $create_leagues_sql );
			
		$create_teams_sql = "CREATE TABLE {$wpdb->leaguemanager_teams} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`title` varchar( 25 ) NOT NULL ,
						`short_title` varchar( 25 ) NOT NULL,
						`logo` varchar( 50 ) NOT NULL,
						`home` tinyint( 1 ) NOT NULL ,
						`points_plus` int( 11 ) NOT NULL,
						`points_minus` int( 11 ) NOT NULL,
						`points2_plus` int( 11 ) NOT NULL,
						`points2_minus` int( 11 ) NOT NULL,
						`done_matches` int( 11 ) NOT NULL,
						`won_matches` int( 11 ) NOT NULL,
						`draw_matches` int( 11 ) NOT NULL,
						`lost_matches` int( 11 ) NOT NULL,
						`league_id` int( 11 ) NOT NULL ,
						PRIMARY KEY ( `id` )) $charset_collate";
		maybe_create_table( $wpdb->leaguemanager_teams, $create_teams_sql );
		
		$create_matches_sql = "CREATE TABLE {$wpdb->leaguemanager_matches} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`date` datetime NOT NULL ,
						`home_team` int( 11 ) NOT NULL ,
						`away_team` int( 11 ) NOT NULL ,
						`match_day` tinyint( 4 ) NOT NULL ,
						`location` varchar( 100 ) NOT NULL ,
						`league_id` int( 11 ) NOT NULL ,
						`home_apparatus_points` tinyint( 4 ) NULL default NULL,
						`away_apparatus_points` tinyint( 4 ) NULL default NULL,
						`home_points` tinyint( 4 ) NULL default NULL,
						`away_points` tinyint( 4 ) NULL default NULL,
						`winner_id` int( 11 ) NOT NULL,
						`loser_id` int( 11 ) NOT NULL,
						`post_id` int( 11 ) NOT NULL,
						PRIMARY KEY ( `id` )) $charset_collate";
		maybe_create_table( $wpdb->leaguemanager_matches, $create_matches_sql );
			
		add_option( 'leaguemanager', $options, 'Leaguemanager Options', 'yes' );
		
		/*
		* Add widget options
		*/
		if ( function_exists('register_sidebar_widget') ) {
			$options = array();
			add_option( 'leaguemanager_widget', $options, 'Leaguemanager Widget Options', 'yes' );
		}
		
		/*
		* Set Capabilities
		*/
		$role = get_role('administrator');
		$role->add_cap('manage_leagues');
	}
	
	
	/**
	 * Uninstall Plugin
	 *
	 * @param none
	 */
	function uninstall()
	{
		global $wpdb;
		
		$wpdb->query( "DROP TABLE {$wpdb->leaguemanager_matches}" );
		$wpdb->query( "DROP TABLE {$wpdb->leaguemanager_teams}" );
		$wpdb->query( "DROP TABLE {$wpdb->leaguemanager}" );
		
		delete_option( 'leaguemanager_widget' );
		delete_option( 'leaguemanager' );
	}
}

?>