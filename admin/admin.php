<?php
/**
* Admin class holding all adminstrative functions for the WordPress plugin LeagueManager
* 
* @author 	Kolja Schleich
* @package	LeagueManager
* @copyright 	Copyright 2009
*/

class LeagueManagerAdminPanel extends LeagueManager
{
	/**
	 * load admin area
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		require_once( ABSPATH . 'wp-admin/includes/template.php' );
		add_action( 'admin_menu', array(&$this, 'menu') );
		
		// Add meta box to post screen
		add_meta_box( 'leaguemanager', __('Match Report','leaguemanager'), array(&$this, 'addMetaBox'), 'post', 'side' );
		add_action( 'publish_post', array(&$this, 'editMatchReport') );
		add_action( 'edit_post', array(&$this, 'editMatchReport') );
		
		add_action('admin_print_scripts', array(&$this, 'loadScripts') );
		add_action('admin_print_styles', array(&$this, 'loadStyles') );
		
		
		$this->leagues = parent::getLeagues();
	}
	function LeagueManagerAdmin()
	{
		$this->__construct();
	}
	
	
	/**
	 * adds menu to the admin interface
	 *
	 * @param none
	 */
	function menu()
	{
		$plugin = 'leaguemanager/leaguemanager.php';
		add_menu_page( __('League','leaguemanager'), __('League','leaguemanager'), 'manage_leagues', LEAGUEMANAGER_PATH, array(&$this, 'display'), LEAGUEMANAGER_URL.'/admin/icon.png' );
		add_submenu_page(LEAGUEMANAGER_PATH, __('Leaguemanager', 'leaguemanager'), __('Overview','leaguemanager'),'manage_leagues', LEAGUEMANAGER_PATH, array(&$this, 'display'));
		add_submenu_page(LEAGUEMANAGER_PATH, __('Settings', 'leaguemanager'), __('Settings','leaguemanager'),'manage_leagues', 'leaguemanager-settings', array( $this, 'display' ));
		add_submenu_page(LEAGUEMANAGER_PATH, __('Documentation', 'leaguemanager'), __('Documentation','leaguemanager'),'manage_leagues', 'leaguemanager-doc', array( $this, 'display' ));
		
		add_filter( 'plugin_action_links_' . $plugin, array( &$this, 'pluginActions' ) );
	}
	
	
	/**
	 * showMenu() - show admin menu
	 *
	 * @param none
	 */
	function display()
	{
		global $leaguemanager;
		
		$options = get_option('leaguemanager');
		if( !isset($options['dbversion']) || $options['dbversion'] != LEAGUEMANAGER_DBVERSION ) {
			include_once ( dirname (__FILE__) . '/upgrade.php' );
			leaguemanager_upgrade_page();
			return;
		}

		switch ($_GET['page']) {
			case 'leaguemanager-doc':
				include_once( dirname(__FILE__) . '/documentation.php' );
				break;
			case 'leaguemanager-settings':
				$this->displayOptionsPage();
				break;
			case 'leaguemanager':
			default:
				switch($_GET['subpage']) {
					case 'show-league':
						include_once( dirname(__FILE__) . '/show-league.php' );
						break;
					case 'settings':
						include_once( dirname(__FILE__) . '/settings.php' );
						break;
					case 'team':
						include_once( dirname(__FILE__) . '/team.php' );
						break;
					case 'match':
						include_once( dirname(__FILE__) . '/match.php' );
						break;
					default:
						include_once( dirname(__FILE__) . '/index.php' );
						break;
				}
				break;
		}
	}
	
	
	/**
	 * display link to settings page in plugin table
	 *
	 * @param array $links array of action links
	 * @return void
	 */
	function pluginActions( $links )
	{
		$settings_link = '<a href="admin.php?page=leaguemanager-settings">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link );
	
		return $links;
	}
	
	
	/**
	 * load scripts
	 *
	 * @param none
	 * @return void
	 */
	function loadScripts()
	{
		wp_register_script( 'leaguemanager', LEAGUEMANAGER_URL.'/admin/leaguemanager.js', array('thickbox', 'colorpicker'), LEAGUEMANAGER_VERSION );
		wp_enqueue_script('leaguemanager');
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
	}
	
	
	/**
	 * checks if league is active
	 *
	 * @param int $league_id
	 * @return boolean
	 */
	function leagueIsActive( $league_id )
	{
		if ( 1 == $this->leagues[$league_id]['status'] )
			return true;
		
		return false;
	}
	
	
	/**
	 * set message by calling parent function
	 *
	 * @param string $message
	 * @param boolean $error (optional)
	 * @return void
	 */
	function setMessage( $message, $error = false )
	{
		parent::setMessage( $message, $error );
	}
	
	
	/**
	 * print message calls parent
	 *
	 * @param none
	 * @return string
	 */
	function printMessage()
	{
		parent::printMessage();
	}
	
	
	/**
	 * activates given league depending on status
	 *
	 * @param int $league_id
	 * @return boolean
	 */
	function activateLeague( $league_id )
	{
		global $wpdb;
		$wpdb->query( "UPDATE {$wpdb->leaguemanager} SET active = '1' WHERE id = '".$league_id."'" );
		return true;
	}
	
	
	/**
	 * deactivate league
	 *
	 * @param int $league_id
	 * @return boolean
	 */
	function deactivateLeague( $league_id )
	{
		global $wpdb;
		$wpdb->query( "UPDATE {$wpdb->leaguemanager} SET active = '0' WHERE id = '".$league_id."'" );	
		return true;
	}
	
	
	/**
	 * toggle league status text
	 *
	 * @param int $league_id
	 * @return string
	 */
	function toggleLeagueStatusText( $league_id )
	{
		if ( $this->leagueIsActive( $league_id ) )
			_e( 'Active', 'leaguemanager');
		else
			_e( 'Inactive', 'leaguemanager');
	}
	
	
	/**
	 * toogle league status action link
	 *
	 * @param int $league_id
	 * @return string
	 */
	function toggleLeagueStatusAction( $league_id )
	{
		if ( $this->leagueIsActive( $league_id ) )
			echo '<a href="edit.php?page=leaguemanager&amp;deactivate_league='.$league_id.'">'.__( 'Deactivate', 'leaguemanager' ).'</a>';
		else
			echo '<a href="edit.php?page=leaguemanager&amp;activate_league='.$league_id.'">'.__( 'Activate', 'leaguemanager' ).'</a>';
	}
	
	
	/**
	 * savePointsManually() - update points manually
	 *
	 * @param int $team_id
	 * @param int $points_plus
	 * @param int $points_minus
	 * @param int $points2_plus
	 * @param int $points2_minus
	 * @param int $num_done_matches
	 * @param int $num_won_matches
	 * @param int $num_draw_matches
	 * @param int $num_lost_matches
	 * @return none
	 */
	function saveStandingsManually( $team_id, $points_plus, $points_minus, $points2_plus, $points2_minus, $num_done_matches, $num_won_matches, $num_draw_matches, $num_lost_matches )
	{side
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `points_plus` = '%d', `points_minus` = '%d', `points2_plus` = '%d', `points2_minus` = '%d', `done_matches` = '%d', `won_matches` = '%d', `draw_matches` = '%d', `lost_matches` = '%d' WHERE `id` = '%d'", $points_plus, $points_minus, $points2_plus, $points2_minus, $num_done_matches, $num_won_matches, $num_draw_matches, $num_lost_matches, $team_id ) );
	}
	
	
	/**
	 * get array of supported point rules
	 *
	 * @param none
	 * @return array
	 */
	function getPointRules()
	{
		$rules = array( 1 => __( 'One-Point-Rule', 'leaguemanager' ), 2 => __('Two-Point-Rule','leaguemanager'), 3 => __('Three-Point-Rule', 'leaguemanager'), 4 => __('German Icehockey League (DEL)', 'leaguemanager'), 5 => __('National Hockey League (NHL)', 'leaguemanager') );
		return $rules;
	}
	
	
	/**
	 * get point rule depending on selection.
	 * For details on point rules see http://de.wikipedia.org/wiki/Drei-Punkte-Regel (German)
	 *
	 * @param int $rule
	 * @return array of points
	 */
	function getPointRule( $rule )
	{
		$point_rules = array();
		// One point rule
		$point_rules[1] = array( 'forwin' => 1, 'fordraw' => 0, 'forloss' => 0, 'forwin_overtime' => 1, 'forloss_overtime' => 0 );
		// Two point rule
		$point_rules[2] = array( 'forwin' => 2, 'fordraw' => 1, 'forloss' => 0, 'forwin_overtime' => 2, 'forloss_overtime' => 0 );
		// Three-point rule
		$point_rules[3] = array( 'forwin' => 3, 'fordraw' => 1, 'forloss' => 0, 'forwin_overtime' => 3, 'forloss_overtime' => 1 );
		// DEL rule
		$points_rules[4] = array( 'forwin' => 3, 'fordraw' => 1, 'forloss' => 0, 'forwin_overtime' => 2, 'forloss_overtime' => 1 );
		// NHl rule
		$point_rules[5] = array( 'forwin' => 2, 'fordraw' => 0, 'forloss' => 0, 'forwin_overtime' => 2, 'forloss_overtime' => 1 );
			
		return $point_rules[$rule];
	}
	
	
	/**
	 * get available point formats
	 *
	 * @param none
	 * @return array
	 */
	function getPointFormats()
	{
		$point_formats = array( '%d:%d', '%d - %d', '%d' );
		return $point_formats;
	}
	
	
	/**
	 * get number of matches for team
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumDoneMatches( $team_id )
	{
		global $wpdb;
		
		$num_matches = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE (`home_team` = '".$team_id."' OR `away_team` = '".$team_id."') AND `home_points` IS NOT NULL AND `away_points` IS NOT NULL" );
		return $num_matches;
	}
	
	
	/**
	 * get number of won matches without overtime
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumWonMatches( $team_id )
	{
		global $wpdb;
		$num_win = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `winner_id` = '".$team_id."' AND overtime = 0" );
		return $num_win;
	}
	
	
	/**
	 * get number of won matches after overtime
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumWonMatchesOvertime( $team_id )
	{
		global $wpdb;
		$num_win_overtime = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `winner_id` = '".$team_id."' AND `overtime` = 1" );
		return $num_win_overtime;
	}
	
	
	/**
	 * get number of draw matches
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumDrawMatches( $team_id )
	{
		global $wpdb;
		$num_draw = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `winner_id` = -1 AND `loser_id` = -1 AND (`home_team` = '".$team_id."' OR `away_team` = '".$team_id."')" );
		return $num_draw;
	}
	
	
	/**
	 * get number of lost matches without overtime
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumLostMatches( $team_id )
	{
		global $wpdb;
		$num_lost = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `loser_id` = '".$team_id."' AND overtime = 0" );
		return $num_lost;
	}
	
	
	/**
	 * get number of lost matches
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumLostMatchesOvertime( $team_id )
	{
		global $wpdb;
		$num_lost_overtime = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `loser_id` = '".$team_id."' AND `overtime` = 1" );
		return $num_lost_overtime;
	}
	
	
	/**
	 * savePoints() - update points for given team
	 *
	 * @param int $team_id
	 * @return none
	 */
	function saveStandings( $team_id )
	{
		global $wpdb;
		
		if ( !defined('LEAGUEMANAGER_MANUAL') ) {
			$points['plus'] = $this->calculatePoints( $team_id, 'plus' );
			$points['minus'] = $this->calculatePoints( $team_id, 'minus' );
				
			if ( parent::isGymnasticsLeague( $this->league_id ) ) {
				$points2['plus'] = $this->calculateApparatusPoints( $team_id, 'plus' );
				$points2['minus'] = $this->calculateApparatusPoints( $team_id, 'minus' );
			} else {
				$points2['plus'] = $this->calculateGoals( $team_id, 'plus' );
				$points2['minus'] = $this->calculateGoals( $team_id, 'minus' );
			}
			
			$done_matches = $this->getNumDoneMatches($team_id);
			$won_matches = $this->getNumWonMatches($team_id);
			$draw_matches = $this->getNumDrawMatches($team_id);
			$lost_matches = $this->getNumLostMatches($team_id);
			
			$wpdb->query ( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `points_plus` = '%d', `points_minus` = '%d', `points2_plus` = '%d', `points2_minus` = '%d', `done_matches` = '%d', `won_matches` = '%d', `draw_matches` = '%d', `lost_matches` = '%d' WHERE `id` = '%d'", $points['plus'], $points['minus'], $points2['plus'], $points2['minus'], $done_matches, $won_matches, $draw_matches, $lost_matches, $team_id ) );
		}
	}
	
	
	/**
	 * calculate points for given team depending on point rule
	 *
	 * @param int $team_id
	 * @param string $option
	 * @return int
	 */
	function calculatePoints( $team_id, $option )
	{
		global $wpdb;
		
		$league = parent::getLeague($this->league_id);
			
		$num_win = $this->getNumWonMatches( $team_id );
		$num_win_overtime = $this->getNumWonMatchesOvertime( $team_id );
		$num_draw = $this->getNumDrawMatches( $team_id );
		$num_lost = $this->getNumLostMatches( $team_id );
		$num_lost_overtime = $this->getNumLostMatchesOvertime( $team_id );
		
		$rule = $this->getPointRule( $league->point_rule );
		extract( $rule );
		
		$points['plus'] = 0; $points['minus'] = 0;
		$points['plus'] = $num_win * $forwin + $num_draw * $fordraw + $num_lost * $forloss + $num_win_overtime * $forwin_overtime + $num_lost_overtime * $forloss_overtime;
		$points['minus'] = $num_draw * $fordraw + $num_lost * $forwin;
		
		return $points[$option];
	}
	
	
	/**
	 * calculate apparatus points
	 *
	 * @param int $team_id
	 * @param string $option
	 * @return int
	 */
	function calculateApparatusPoints( $team_id, $option )
	{
		global $wpdb;
		$apparatus_home = $wpdb->get_results( "SELECT `home_apparatus_points`, `away_apparatus_points` FROM {$wpdb->leaguemanager_matches} WHERE `home_team` = '".$team_id."'" );
		$apparatus_away = $wpdb->get_results( "SELECT `home_apparatus_points`, `away_apparatus_points` FROM {$wpdb->leaguemanager_matches} WHERE `away_team` = '".$team_id."'" );
			
		$apparatus_points['plus'] = 0;
		$apparatus_points['minus'] = 0;
		if ( count($apparatus_home) > 0 )
		foreach ( $apparatus_home AS $home_apparatus ) {
			$apparatus_points['plus'] += $home_apparatus->home_apparatus_points;
			$apparatus_points['minus'] += $home_apparatus->away_apparatus_points;
		}
		
		if ( count($apparatus_away) > 0 )
		foreach ( $apparatus_away AS $away_apparatus ) {
			$apparatus_points['plus'] += $away_apparatus->away_apparatus_points;
			$apparatus_points['minus'] += $away_apparatus->home_apparatus_points;
		}
		
		return $apparatus_points[$option];
	}
	
	
	/**
	 * calculate goals
	 *
	 * @param int $team_id
	 * @param string $option
	 * @return int
	 */
	function calculateGoals( $team_id, $option )
	{
		global $wpdb;
		
		$goals_home = $wpdb->get_results( "SELECT `home_points`, `away_points` FROM {$wpdb->leaguemanager_matches} WHERE `home_team` = '".$team_id."'" );
		$goals_away = $wpdb->get_results( "SELECT `home_points`, `away_points` FROM {$wpdb->leaguemanager_matches} WHERE `away_team` = '".$team_id."'" );
			
		$goals['plus'] = 0;
		$goals['minus'] = 0;
		if ( count($goals_home) > 0 ) {
			foreach ( $goals_home AS $home_goals ) {
				$goals['plus'] += $home_goals->home_points;
				$goals['minus'] += $home_goals->away_points;
			}
		}
		
		if ( count($goals_away) > 0 ) {
			foreach ( $goals_away AS $away_goals ) {
				$goals['plus'] += $away_goals->away_points;
				$goals['minus'] += $away_goals->home_points;
			}
		}
		
		return $goals[$option];
	}

	
	/**
	 * add new League
	 *
	 * @param string $title
	 * @return void
	 */
	function addLeague( $title )
	{
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare ( "INSERT INTO {$wpdb->leaguemanager} (title) VALUES ('%s')", $title ) );
		parent::setMessage( __('League added', 'leaguemanager') );
	}


	/**
	 * edit League
	 *
	 * @param string $title
	 * @param int $point_rule
	 * @param string $point_format
	 * @param int $num_match_days
	 * @param int $show_logo
	 * @param int $league_id
	 * @return void
	 */
	function editLeague( $title, $point_rule, $point_format, $type, $num_match_days, $show_logo, $league_id )
	{
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->leaguemanager} SET `title` = '%s', `point_rule` = '%d', `point_format` = '%s', `type` = '%d', `num_match_days` = '%d', `show_logo` = '%d' WHERE `id` = '%d'", $title, $point_rule, $point_format, $type, $num_match_days, $show_logo, $league_id ) );
		parent::setMessage( __('Settings saved', 'leaguemanager') );
	}


	/**
	 * delete League
	 *
	 * @param int $league_id
	 * @return void
	 */
	function delLeague( $league_id )
	{
		global $wpdb;
		
		foreach ( $this->getTeams( "league_id = '".$league_id."'" ) AS $team )
			$this->delTeam( $team->id );

		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager} WHERE `id` = {$league_id}" );
	}

	
	/**
	 * add new team
	 *
	 * @param int $league_id
	 * @param string $short_title
	 * @param string $title
	 * @param int $home 1 | 0
	 * @return void
	 */
	function addTeam( $league_id, $short_title, $title, $home )
	{
		global $wpdb;
			
		$sql = "INSERT INTO {$wpdb->leaguemanager_teams} (title, short_title, home, league_id) VALUES ('%s', '%s', '%d', '%d')";
		$wpdb->query( $wpdb->prepare ( $sql, $title, $short_title, $home, $league_id ) );
		$team_id = $wpdb->insert_id;

		if ( isset($_FILES['logo']) && $_FILES['logo']['name'] != '' )
			$this->uploadLogo($team_id, $_FILES['logo']);
		
		parent::setMessage( __('Team added','leaguemanager') );
	}


	/**
	 * edit team
	 *
	 * @param int $team_id
	 * @param string $short_title
	 * @param string $title
	 * @param int $home 1 | 0
	 * @param boolean $del_logo
	 * @param string $image_file
	 * @param boolean $overwrite_image
	 * @return void
	 */
	function editTeam( $team_id, $short_title, $title, $home, $del_logo = false, $image_file = '', $overwrite_image = false )
	{
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->leaguemanager_teams} SET `title` = '%s', `short_title` = '%s', `home` = '%d' WHERE `id` = %d", $title, $short_title, $home, $team_id ) );
			
		// Delete Image if options is checked
		if ($del_logo || $overwrite_image) {
			$wpdb->query("UPDATE {$wpdb->leaguemanager_teams} SET `logo` = '' WHERE `id` = {$team_id}");
			$this->delLogo( $image_file );
		}
		
		if ( isset($_FILES['logo']) && $_FILES['logo']['name'] != '' )
			$this->uploadLogo($team_id, $_FILES['logo'], $overwrite_image);
		
		parent::setMessage( __('Team updated','leaguemanager') );
	}


	/**
	 * delete Team
	 *
	 * @param int $team_id
	 * @return void
	 */
	function delTeam( $team_id )
	{
		global $wpdb;
		
		$team = $this->getTeam( $team_id );
		$this->delLogo( $teams->logo );
			
		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager_matches} WHERE `home_team` = '".$team_id."' OR `away_team` = '".$team_id."'" );
		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager_teams} WHERE `id` = '".$team_id."'" );
	}


	/**
	 * set image path in database and upload image to server
	 *
	 * @param int  $team_id
	 * @param string $file
	 * @param string $uploaddir
	 * @param boolean $overwrite_image
	 * @return void | string
	 */
	function uploadLogo( $team_id, $file, $overwrite = false )
	{
		global $wpdb;
		
		$new_file = parent::getImagePath().'/'.basename($file['name']);
		$logo = new LeagueManagerImage($new_file);
		if ( $logo->supported() ) {
			if ( $file['size'] > 0 ) {
				
				if ( file_exists($new_file) && !$overwrite ) {
					parent::setMessage( __('Logo exists and is not uploaded. Set the overwrite option if you want to replace it.','leaguemanager'), true );
				} else {
					if ( move_uploaded_file($file['tmp_name'], $new_file) ) {
						if ( $team = $this->getTeam( $team_id ) )
							if ( $team->logo != '' ) $this->delLogo($team->logo);
							
						$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `logo` = '%s' WHERE id = '%d'", basename($file['name']), $team_id ) );
			
						$logo->createThumbnail();
					} else {
						parent::setMessage( sprintf( __('The uploaded file could not be moved to %s.' ), parent::getImagePath() ), true );
					}
				}
			}
		} else {
			parent::setMessage( __('The file type is not supported.','leaguemanager'), true );
		}
	}
	
	
	/**
	 * delLogo() - delete logo from server
	 *
	 * @param string $image
	 * @return void
	 *
	 */
	function delLogo( $image )
	{
		@unlink( parent::getImagePath($image) );
	}
	
	
	/**
	 * add Match
	 *
	 * @param string $date
	 * @param int $home_team
	 * @param int $away_team
	 * @param int $match_day
	 * @param string $location
	 * @param int $league_id
	 * @return string
	 */
	function addMatch( $date, $home_team, $away_team, $match_day, $location, $league_id )
	{
	 	global $wpdb;
		$sql = "INSERT INTO {$wpdb->leaguemanager_matches} (date, home_team, away_team, match_day, location, league_id) VALUES ('%s', '%d', '%d', '%d', '%s', '%d')";
		$wpdb->query( $wpdb->prepare ( $sql, $date, $home_team, $away_team, $match_day, $location, $league_id ) );
	}


	/**
	 * edit Match
	 *
	 * @param string $date
	 * @param int $home_team
	 * @param int $away_team
	 * @param int $match_day
	 * @param string $location
	 * @param int $league_id
	 * @param int $match_id
	 * @param int $home_points
	 * @param int $away_points
	 * @param array $home_points2
	 * @param array $away_points2
	 * @param int $overtime
	 * @return string
	 */
	function editMatch( $date, $home_team, $away_team, $match_day, $location, $league_id, $match_id, $home_points, $away_points, $home_points2, $away_points2, $overtime )
	{
	 	global $wpdb;
		$this->league_id = $league_id;
		
		$home_points = ($home_points == '') ? 'NULL' : intval($home_points);
		$away_points = ($away_points == '') ? 'NULL' : intval($away_points);
		
		$points2 = array();
		foreach ( $home_points2 AS $i => $points ) {
			$points2[] = array( 'plus' => $points, 'minus' => $away_points2[$i] );
		}
		
		print_r($points2);
		$winner = $this->getMatchResult( $home_points, $away_points, $home_team, $away_team, 'winner' );
		$loser = $this->getMatchResult( $home_points, $away_points, $home_team, $away_team, 'loser' );
			
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->leaguemanager_matches} SET `date` = '%s', `home_team` = '%d', `away_team` = '%d', `match_day` = '%d', `location` = '%s', `league_id` = '%d', `home_points` = ".$home_points.", `away_points` = ".$away_points.", `points2` = '%s', `winner_id` = ".intval($winner).", `loser_id` = ".intval($loser).", `overtime` = '%d' WHERE `id` = %d", $date, $home_team, $away_team, $match_day, $location, $league_id, maybe_serialize($points2), $overtime, $match_id ) );
			
		// update points for each team
		$this->saveStandings($home_team);
		$this->saveStandings($away_team);
	}


	/**
	 * delete Match
	 *
	 * @param int $cid
	 * @return void
	 */
	function delMatch( $match_id )
	{
	  	global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager_matches} WHERE `id` = '".$match_id."'" );
		return;
	}


	/**
	 * update match results
	 *
	 * @param int $league_id
	 * @param array $matches
	 * @param array $home_points2
	 * @param array $away_points2
	 * @param array $home_points
	 * @param array $away_points
	 * @return string
	 */
	function updateResults( $league_id, $matches, $home_points2, $away_points2, $home_points, $away_points, $home_team, $away_team )
	{
		global $wpdb;
		if ( null != $matches ) {
			$this->league_id = $league_id;
			while (list($match_id) = each($matches)) {
				$home_points[$match_id] = ( '' == $home_points[$match_id] ) ? 'NULL' : intval($home_points[$match_id]);
				$away_points[$match_id] = ( '' == $away_points[$match_id] ) ? 'NULL' : intval($away_points[$match_id]);
				
				$points2 = array();
				foreach ( $home_points2[$match_id] AS $i => $points ) {
					$points2[] = array( 'plus' => $points, 'minus' => $away_points2[$match_id][$i] );
				}
				
				$winner = $this->getMatchResult( $home_points[$match_id], $away_points[$match_id], $home_team[$match_id], $away_team[$match_id], 'winner' );
				$loser = $this->getMatchResult( $home_points[$match_id], $away_points[$match_id], $home_team[$match_id], $away_team[$match_id], 'loser' );
				
				$wpdb->query( "UPDATE {$wpdb->leaguemanager_matches} SET `home_points` = ".$home_points[$match_id].", `away_points` = ".$away_points[$match_id].", `points2` = '".maybe_serialize($points2)."', `winner_id` = ".intval($winner).", `loser_id` = ".intval($loser)." WHERE `id` = {$match_id}" );
			
				// update points for each team
				$this->saveStandings($home_team[$match_id]);
				$this->saveStandings($away_team[$match_id]);
			}
		}
		parent::setMessage( __('Updated League Results','leaguemanager') );
	}
	

	/**
	 * determine match result
	 *
	 * @param int $home_points
	 * @param int $away_points
	 * @param int $home_team
	 * @param int $away_team
	 * @param string $option
	 * @return int
	 */
	function getMatchResult( $home_points, $away_points, $home_team, $away_team, $option )
	{
		if ( $home_points > $away_points ) {
			$match['winner'] = $home_team;
			$match['loser'] = $away_team;
		} elseif ( $home_points < $away_points ) {
			$match['winner'] = $away_team;
			$match['loser'] = $home_team;
		} elseif ( 'NULL' === $home_points && 'NULL' === $away_points ) {
			$match['winner'] = 0;
			$match['loser'] = 0;
		} else {
			$match['winner'] = -1;
			$match['loser'] = -1;
		}
		
		return $match[$option];
	}
	
	
	/**
	 * get date selection.
	 *
	 * @param int $day
	 * @param int $month
	 * @param int $year
	 * @param int $index default 0
	 * @return string
	 */
	function getDateSelection( $day, $month, $year, $index = 0 )
	{
		$out = '<select size="1" name="day['.$index.']" class="date">';
		for ( $d = 1; $d <= 31; $d++ ) {
			$selected = ( $d == $day ) ? ' selected="selected"' : '';
			$out .= '<option value="'.str_pad($d, 2, 0, STR_PAD_LEFT).'"'.$selected.'>'.$d.'</option>';
		}
		$out .= '</select>';
		$out .= '<select size="1" name="month['.$index.']" class="date">';
		foreach ( parent::getMonths() AS $key => $m ) {
			$selected = ( $key == $month ) ? ' selected="selected"' : '';
			$out .= '<option value="'.str_pad($key, 2, 0, STR_PAD_LEFT).'"'.$selected.'>'.$m.'</option>';
		}
		$out .= '</select>';
		$out .= '<select size="1" name="year['.$index.']" class="date">';
		for ( $y = date("Y")-1; $y <= date("Y")+1; $y++ ) {
			$selected =  ( $y == $year ) ? ' selected="selected"' : '';
			$out .= '<option value="'.$y.'"'.$selected.'>'.$y.'</option>';
		}
		$out .= '</select>';
		return $out;
	}
	
	
	/**
	 * display global settings page (e.g. color scheme options)
	 *
	 * @param none
	 * @return void
	 */
	function displayOptionsPage()
	{
		$options = get_option('leaguemanager');
		
		if ( isset($_POST['updateLeagueManager']) ) {
			check_admin_referer('leaguemanager_manage-global-league-options');
			$options['colors']['headers'] = $_POST['color_headers'];
			$options['colors']['rows'] = array( $_POST['color_rows_alt'], $_POST['color_rows'] );
			
			update_option( 'leaguemanager', $options );
			parent::setMessage(__( 'Settings saved', 'leaguemanager' ));
			parent::printMessage();
		}
		
		require_once (dirname (__FILE__) . '/settings-global.php');
	}
	
	
	/**
	 * add meta box to post screen
	 *
	 * @param object $post
	 * @return none
	 */
	function addMetaBox( $post )
	{
		global $wpdb, $post_ID;
		
		if ( $leagues = $wpdb->get_results( "SELECT `title`, `id`, `active` FROM {$wpdb->leaguemanager} ORDER BY id ASC" ) ) {
			if ( $post_ID != 0 ) {
				$curr_match = $wpdb->get_results( "SELECT `id` FROM {$wpdb->leaguemanager_matches} WHERE `post_id` = {$post_ID}" );
				$curr_match_id = ( $curr_match[0] ) ? $curr_match[0]->id : 0;
			} else {
				$curr_match_id = 0;
			}
		
			echo "<input type='hidden' name='lm_curr_match' value='".$curr_match_id."' />";
			echo "<select name='lm_match' id='lm_match'>";
			echo "<option value='0'>".__('No Match','leaguemanager')."</option>";
			foreach ( $leagues AS $league ) {
				$teams = parent::getTeams( "league_id = ".$league->id, 'ARRAY' );
				echo "<optgroup label='".$league->title."'>";
				foreach ( parent::getMatches( "league_id = ".$league->id ) AS $match ) {
					$selected = ( $curr_match_id == $match->id ) ? ' selected="selected"' : '';
					echo "<option value='".$match->id."'".$selected.">".str_pad  ('&#160;',5).$teams[$match->home_team]['title']." &#8211; ".$teams[$match->away_team]['title']."</option>";
				}
				echo "</optgroup>";
			}
			echo "</select>";
		}
	}
	
	/**
	 * update post id for match report
	 *
	 * @param none
	 * @return none
	 */
	function editMatchReport()
	{
		global $wpdb;
		
		$post_ID = (int) $_POST['post_ID'];
		$match_ID = (int) $_POST['lm_match'];
		$curr_match_ID = (int) $_POST['lm_curr_match'];
		if ( $curr_match_ID != $match_ID ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `post_id` = '%d' wHERE `id` = '%d'", $post_ID, $match_ID ) );
			if ( $curr_match_ID != 0 )
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `post_id` = 0 wHERE `id` = '%d'", $curr_match_ID ) );
		}
	}
	
	
	/**
	 * get supported image types
	 *
	 * @param none
	 * @return array
	 */
	function getSupportedImageTypes()
	{
		return array( "jpg", "jpeg", "png", "gif" );
	}
}

?>