<?php
/*
Plugin Name: LeagueManager
Author URI: http://kolja.galerie-neander.de/
Plugin URI: http://kolja.galerie-neander.de/plugins/leaguemanager/
Description: Manage and present sports league results.
Version: 2.9-RC1
Author: Kolja Schleich

Copyright 2008-2009  Kolja Schleich  (email : kolja.schleich@googlemail.com)

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
* @copyright 	Copyright 2008-2009
*/

class LeagueManagerLoader
{
	/**
	 * plugin version
	 *
	 * @var string
	 */
	var $version = '2.9-RC1';
	
	
	/**
	 * database version
	 *
	 * @var string
	 */
	var $dbversion = '2.9-RC1';
	
	
	/**
	 * check if bridge is active
	 *
	 * @var boolean
	 */
	var $bridge = false;
	
	
	/**
	 * constructor
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		global $leaguemanager, $lmWidget, $wpdb;

		$this->defineConstants();
		$this->defineTables();
		$this->loadOptions();
		$this->loadLibraries();

		register_activation_hook(__FILE__, array(&$this, 'activate') );
		
		if (function_exists('register_uninstall_hook'))
			register_uninstall_hook(__FILE__, array(&$this, 'uninstall'));

		$lmWidget = new LeagueManagerWidget();
		add_action( 'init', array(&$lmWidget, 'register') );
		// Start this plugin once all other plugins are fully loaded
		add_action( 'plugins_loaded', array(&$this, 'initialize') );
		
		$leaguemanager = new LeagueManager( $this->bridge );
		// Load language file
		$this->loadTextdomain();
	}
	function LeagueManagerLoader()
	{
		$this->__construct();
	}
	
	
	/**
	 * initialize plugin
	 *
	 * @param none
	 * @return void
	 */
	function initialize()
	{
		// Add the script and style files
		add_action('wp_head', array(&$this, 'loadScripts') );
		add_action('wp_print_styles', array(&$this, 'loadStyles') );

		// Add TinyMCE Button
		add_action( 'init', array(&$this, 'addTinyMCEButton') );
		add_filter( 'tiny_mce_version', array(&$this, 'changeTinyMCEVersion') );
		
		// Ajax Actions
		add_action( 'wp_ajax_leaguemanager_get_match_box', 'leaguemanager_get_match_box' );
		add_action( 'wp_ajax_leaguemanager_save_team_standings', 'leaguemanager_save_team_standings' );
		add_action( 'wp_ajax_leaguemanager_save_add_points', 'leaguemanager_save_add_points' );
		add_action( 'wp_ajax_leaguemanager_save_goals', 'leaguemanager_save_goals' );
		add_action( 'wp_ajax_leaguemanager_save_cards', 'leaguemanager_save_cards' );
		add_action( 'wp_ajax_leaguemanager_save_exchanges', 'leaguemanager_save_exchanges' );
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
		global $lmShortcodes;
		
		// Global libraries
		require_once (dirname (__FILE__) . '/lib/core.php');
		require_once (dirname (__FILE__) . '/lib/shortcodes.php');
		require_once (dirname (__FILE__) . '/lib/widget.php');
		require_once (dirname (__FILE__) . '/functions.php');
		
		if ( is_admin() ) {
			require_once (dirname (__FILE__) . '/lib/image.php');
			require_once (dirname (__FILE__) . '/admin/admin.php');	
			$this->adminPanel = new LeagueManagerAdminPanel();
		}
			
		if ( file_exists(WP_PLUGIN_DIR . '/projectmanager/projectmanager.php') ) {
			$p = get_option('projectmanager');
			if (version_compare($p['version'], '2.0', '>')) {
				global $lmBridge;
				require_once(dirname (__FILE__) . '/lib/bridge.php');
				$lmBridge = new LeagueManagerBridge();
				$this->bridge = true;
			}
		}
		$lmShortcodes = new LeagueManagerShortcodes($this->bridge);
	}
	
	
	/**
	 * load options
	 *
	 * @param none
	 * @return void
	 */
	function loadOptions()
	{
		$this->options = get_option('leaguemanager');
	}
	
	
	/**
	 * get options
	 *
	 * @param boolean $index (optional)
	 * @return void
	 */
	function getOptions($index = false)
	{
		if ( $index )
			return $this->options[$index];

		return $this->options;
	}
	
	
	/**
	 * load textdomain
	 *
	 * @param none
	 * @return void
	 */
	function loadTextdomain()
	{
		global $leaguemanager;
		
		$textdomain = $this->getOptions('textdomain');
		if ( $textdomain != 'default' && !empty($textdomain) ) {
			$locale = get_locale();
			$path = dirname(__FILE__) . '/languages';
			$domain = 'leaguemanager';
			$mofile = $path . '/'. $domain . '-' . $textdomain . '-' . $locale . '.mo';
			
			if ( file_exists($mofile) ) {
				load_textdomain($domain, $mofile);
				return true;
			}
		}
		
		load_plugin_textdomain( 'leaguemanager', $path = PLUGINDIR.'/leaguemanager/languages' );
		//load_plugin_textdomain( 'leaguemanager', false, dirname( plugin_basename(__FILE__) ) .'/languages' );
	}
	

	/**
	 * load scripts
	 *
	 * @param none
	 * @return void
	 */
	function loadScripts()
	{
		wp_register_script( 'leaguemanager', LEAGUEMANAGER_URL.'/leaguemanager.js', array('thickbox', 'sack', 'jquery'), LEAGUEMANAGER_VERSION );
		wp_print_scripts('leaguemanager');
		?>
		<script type="text/javascript">
		//<![CDATA[
		LeagueManagerAjaxL10n = {
			blogUrl: "<?php bloginfo( 'wpurl' ); ?>", pluginPath: "<?php echo LEAGUEMANAGER_PATH; ?>", pluginUrl: "<?php echo LEAGUEMANAGER_URL; ?>", requestUrl: "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php", Edit: "<?php _e("Edit"); ?>", Post: "<?php _e("Post"); ?>", Save: "<?php _e("Save"); ?>", Cancel: "<?php _e("Cancel"); ?>", pleaseWait: "<?php _e("Please wait..."); ?>", Revisions: "<?php _e("Page Revisions"); ?>", Time: "<?php _e("Insert time"); ?>", Options: "<?php _e("Options") ?>", Delete: "<?php _e('Delete') ?>"
			   }
		//]]>
		</script>
		<?php
	}
	
	
	/**
	 * load styles
	 *
	 * @param none
	 * @return void
	 */
	function loadStyles()
	{
		wp_enqueue_style('leaguemanager', LEAGUEMANAGER_URL . "/style.css", false, '1.0', 'screen');
		
		echo "\n<style type='text/css'>";
		echo "\n\ttable.leaguemanager th { background-color: ".$this->options['colors']['headers']." }";
		echo "\n\ttable.leaguemanager tr { background-color: ".$this->options['colors']['rows'][1]." }";
		echo "\n\ttable.leaguemanager tr.alternate { background-color: ".$this->options['colors']['rows'][0]." }";
		echo "\n\ttable.crosstable th, table.crosstable td { border: 1px solid ".$this->options['colors']['rows'][0]."; }";
		echo "\n</style>";
	}
	
	
	/**
	 * add TinyMCE Button
	 *
	 * @param none
	 * @return void
	 */
	function addTinyMCEButton()
	{
		// Don't bother doing this stuff if the current user lacks permissions
		if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;
		
		// Check for LeagueManager capability
		if ( !current_user_can('manage_leagues') ) return;
		
		// Add only in Rich Editor mode
		if ( get_user_option('rich_editing') == 'true') {
			add_filter("mce_external_plugins", array(&$this, 'addTinyMCEPlugin'));
			add_filter('mce_buttons', array(&$this, 'registerTinyMCEButton'));
		}
	}
	function addTinyMCEPlugin( $plugin_array )
	{
		$plugin_array['LeagueManager'] = LEAGUEMANAGER_URL.'/admin/tinymce/editor_plugin.js';
		return $plugin_array;
	}
	function registerTinyMCEButton( $buttons )
	{
		array_push($buttons, "separator", "LeagueManager");
		return $buttons;
	}
	function changeTinyMCEVersion( $version )
	{
		return ++$version;
	}
	
	
	/**
	 * Activate plugin
	 *
	 * @param none
	 */
	function activate()
	{
		$options = array();
		$options['version'] = $this->version;
		$options['dbversion'] = $this->dbversion;
		$options['textdomain'] = 'default';
		$options['colors']['headers'] = '#dddddd';
		$options['colors']['rows'] = array( '#ffffff', '#efefef' );
		add_option( 'leaguemanager', $options, 'Leaguemanager Options', 'yes' );
		
		/*
		* Set Capabilities
		*/
		$role = get_role('administrator');
		$role->add_cap('manage_leagues');
	
	
		$this->install();
	}
	
	
	
	function install()
	{
		global $wpdb;
		include_once( ABSPATH.'/wp-admin/includes/upgrade.php' );
		
		$charset_collate = '';
		if ( $wpdb->supports_collation() ) {
			if ( ! empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}
		
		$create_leagues_sql = "CREATE TABLE {$wpdb->leaguemanager} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT,
						`title` varchar( 100 ) NOT NULL,
						`sport` varchar( 255 ) NOT NULL default '2',
						`num_match_days` tinyint( 4 ) NOT NULL,
						`active` tinyint( 1 ) NOT NULL default '1',
						`point_rule` longtext NOT NULL,
						`point_format` varchar( 255 ) NOT NULL,
						`save_standings` varchar( 100 ) NOT NULL default 'auto',
						`team_ranking` varchar( 20 ) NOT NULL default 'auto',
						`project_id` int( 11 ) NOT NULL,
						`mode` varchar( 255 ) NOT NULL default 'season',
						PRIMARY KEY ( `id` )) $charset_collate";
		maybe_create_table( $wpdb->leaguemanager, $create_leagues_sql );
			
		$create_teams_sql = "CREATE TABLE {$wpdb->leaguemanager_teams} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`title` varchar( 100 ) NOT NULL ,
						`short_title` varchar( 50 ) NOT NULL,
						`logo` varchar( 150 ) NOT NULL,
						`website` varchar( 255 ) NOT NULL,
						`coach` varchar( 100 ) NOT NULL,
						`home` tinyint( 1 ) NOT NULL ,
						`points_plus` int( 11 ) NOT NULL,
						`points_minus` int( 11 ) NOT NULL,
						`points2_plus` int( 11 ) NOT NULL,
						`points2_minus` int( 11 ) NOT NULL,
						`add_points` int( 11 ) NOT NULL,
						`done_matches` int( 11 ) NOT NULL,
						`won_matches` int( 11 ) NOT NULL,
						`draw_matches` int( 11 ) NOT NULL,
						`lost_matches` int( 11 ) NOT NULL,
						`diff` int( 11 ) NOT NULL,
						`league_id` int( 11 ) NOT NULL ,
						`season` varchar( 255 ) NOT NULL,
						`rank` int( 11 ) NOT NULL,
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
						`season` varchar( 255 ) NOT NULL,
						`home_points` tinyint( 4 ) NULL default NULL,
						`away_points` tinyint( 4 ) NULL default NULL,
						`points2` longtext NOT NULL,
						`winner_id` int( 11 ) NOT NULL,
						`loser_id` int( 11 ) NOT NULL,
						`overtime` LONGTEXT NOT NULL,
						`penalty` LONGTEXT NOT NULL,
						`goals` LONGTEXT NOT NULL,
						`cards` LONGTEXT NOT NULL,
						`exchanges` LONGTEXT NOT NULL,
						`post_id` int( 11 ) NOT NULL,
						PRIMARY KEY ( `id` )) $charset_collate";
		maybe_create_table( $wpdb->leaguemanager_matches, $create_matches_sql );
	}
	
	
	/**
	 * Uninstall Plugin
	 *
	 * @param none
	 */
	function uninstall()
	{
		global $wpdb, $leaguemanager;
		
		$wpdb->query( "DROP TABLE {$wpdb->leaguemanager_matches}" );
		$wpdb->query( "DROP TABLE {$wpdb->leaguemanager_teams}" );
		$wpdb->query( "DROP TABLE {$wpdb->leaguemanager}" );
		
		delete_option( 'leaguemanager_widget' );
		delete_option( 'leaguemanager' );
		
		// Delete Logos
		$dir = $leaguemanager->getImagePath();
		if ( $handle = opendir($dir) ) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..")
					@unlink($file);
			}
			closedir($handle);
		}
		@rmdir($dir);
	}
}

// Run the Plugin
$lmLoader = new LeagueManagerLoader();
// export
if ( isset($_POST['leaguemanager_export']) )
	$lmLoader->adminPanel->export($_POST['league_id'], $_POST['mode']);

?>