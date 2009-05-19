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
		//$this->leagues = parent::getLeagues();
		require_once( ABSPATH . 'wp-admin/includes/template.php' );
		add_action( 'admin_menu', array(&$this, 'menu') );
		
		// Add meta box to post screen
		add_meta_box( 'leaguemanager', __('Match-Report','leaguemanager'), array(&$this, 'addMetaBox'), 'post', 'side' );
		add_action( 'publish_post', array(&$this, 'editMatchReport') );
		add_action( 'edit_post', array(&$this, 'editMatchReport') );
		
		add_action('admin_print_scripts', array(&$this, 'loadScripts') );
		add_action('admin_print_styles', array(&$this, 'loadStyles') );
	}
	function LeagueManagerAdminPanel()
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

		if ( function_exists('add_object_page') )
			add_object_page( __('League','leaguemanager'), __('League','leaguemanager'), 'leagues', LEAGUEMANAGER_PATH, array(&$this, 'display'), LEAGUEMANAGER_URL.'/admin/icon.png' );
		else
			add_menu_page( __('League','leaguemanager'), __('League','leaguemanager'), 'leagues', LEAGUEMANAGER_PATH, array(&$this, 'display'), LEAGUEMANAGER_URL.'/admin/icon.png' );

		add_submenu_page(LEAGUEMANAGER_PATH, __('Leaguemanager', 'leaguemanager'), __('Overview','leaguemanager'),'leagues', LEAGUEMANAGER_PATH, array(&$this, 'display'));
		add_submenu_page(LEAGUEMANAGER_PATH, __('Settings', 'leaguemanager'), __('Settings','leaguemanager'),'manage_leagues', 'leaguemanager-settings', array( $this, 'display' ));
		add_submenu_page(LEAGUEMANAGER_PATH, __('Import'), __('Import'),'manage_leagues', 'leaguemanager-import', array( $this, 'display' ));
		add_submenu_page(LEAGUEMANAGER_PATH, __('Export'), __('Export'),'manage_leagues', 'leaguemanager-export', array( $this, 'display' ));
		add_submenu_page(LEAGUEMANAGER_PATH, __('Documentation', 'leaguemanager'), __('Documentation','leaguemanager'),'leagues', 'leaguemanager-doc', array( $this, 'display' ));
		
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
		if( $options['dbversion'] != LEAGUEMANAGER_DBVERSION ) {
			include_once ( dirname (__FILE__) . '/upgrade.php' );
			leaguemanager_upgrade_page();
			return;
		}

		if ( $leaguemanager->isBridge() ) global $lmBridge;

		switch ($_GET['page']) {
			case 'leaguemanager-doc':
				include_once( dirname(__FILE__) . '/documentation.php' );
				break;
			case 'leaguemanager-settings':
				$this->displayOptionsPage();
				break;
			case 'leaguemanager-import':
				include_once( dirname(__FILE__) . '/import.php' );
				break;
			case 'leaguemanager-export':
				include_once( dirname(__FILE__) . '/export.php' );
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
					case 'championchip':
						include_once( dirname(__FILE__) . '/championchip.php' );
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
		wp_register_script( 'leaguemanager_ajax', LEAGUEMANAGER_URL.'/admin/js/ajax.js', array('sack' ), LEAGUEMANAGER_VERSION );
		wp_register_script( 'leaguemanager', LEAGUEMANAGER_URL.'/admin/js/functions.js', array('thickbox', 'colorpicker', 'scriptaculous', 'prototype', 'leaguemanager_ajax' ), LEAGUEMANAGER_VERSION );
		
		wp_enqueue_script('leaguemanager');
		
		?>
		<script type='text/javascript'>
		//<![CDATA[
		LeagueManagerAjaxL10n = {
			requestUrl: "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php", manualPointRuleDescription: "<?php _e( 'Order: Forwin, Fordraw, Forloss', 'leaguemanager' ) ?>", pluginPath: "<?php echo LEAGUEMANAGER_PATH; ?>", pluginUrl: "<?php echo LEAGUEMANAGER_URL; ?>", Edit: "<?php _e("Edit"); ?>", Post: "<?php _e("Post"); ?>", Save: "<?php _e("Save"); ?>", Cancel: "<?php _e("Cancel"); ?>", pleaseWait: "<?php _e("Please wait..."); ?>", Delete: "<?php _e('Delete', 'projectmanager') ?>", Yellow: "<?php _e( 'Yellow', 'leaguemanager') ?>", Red: "<?php _e( 'Red', 'leaguemanager') ?>", Yellow_Red: "<?php _e('Yellow/Red', 'leaguemanager') ?>"
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
	 * get available league modes
	 *
	 * @param none
	 * @return array
	 */
	function getModes()
	{
		$modes = array( 'season' => __( 'Seasonal', 'leaguemanager' ), 'championchip' => __( 'Championchip', 'leaguemanager' ) );
		return $modes;
	}
	
	
	/**
	 * get textdomain dependent on league sport
	 *
	 * @param int $sport
	 * @return string
	 */
	function getTextdomain( $sport )
	{
		if ( $sport == 'gymnastics' )
			return 'gymnastics';
		else
			return 'default';
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
	 * @param int $add_points
	 * @return none
	 */
	function saveStandingsManually( $team_id, $points_plus, $points_minus, $points2_plus, $points2_minus, $num_done_matches, $num_won_matches, $num_draw_matches, $num_lost_matches, $add_points )
	{
		global $wpdb;
		$diff = $points2_plus - $points2_minus;
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `points_plus` = '%d', `points_minus` = '%d', `points2_plus` = '%d', `points2_minus` = '%d', `done_matches` = '%d', `won_matches` = '%d', `draw_matches` = '%d', `lost_matches` = '%d', `diff` = '%d', `add_points` = '%d' WHERE `id` = '%d'", $points_plus, $points_minus, $points2_plus, $points2_minus, $num_done_matches, $num_won_matches, $num_draw_matches, $num_lost_matches, $diff, $add_points, $team_id ) );
	}
	
	
	/**
	 * get array of supported point rules
	 *
	 * @param none
	 * @return array
	 */
	function getPointRules()
	{
		$rules = array( 0 => __( 'Update Standings Manually', 'leaguemanager' ), 1 => __( 'One-Point-Rule', 'leaguemanager' ), 2 => __('Two-Point-Rule','leaguemanager'), 3 => __('Three-Point-Rule', 'leaguemanager'), 4 => __('German Icehockey League (DEL)', 'leaguemanager'), 5 => __('National Hockey League (NHL)', 'leaguemanager'), 6 => __('User defined', 'leaguemanager') );
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
		$rule = maybe_unserialize($rule);
		
		// Manual point rule
		if ( is_array($rule) ) {
			return $rule;
		} else {
			$point_rules = array();
			// One point rule
			$point_rules[1] = array( 'forwin' => 1, 'fordraw' => 0, 'forloss' => 0, 'forwin_overtime' => 1, 'forloss_overtime' => 0 );
			// Two point rule
			$point_rules[2] = array( 'forwin' => 2, 'fordraw' => 1, 'forloss' => 0, 'forwin_overtime' => 2, 'forloss_overtime' => 0 );
			// Three-point rule
			$point_rules[3] = array( 'forwin' => 3, 'fordraw' => 1, 'forloss' => 0, 'forwin_overtime' => 3, 'forloss_overtime' => 0 );
			// DEL rule
			$point_rules[4] = array( 'forwin' => 3, 'fordraw' => 1, 'forloss' => 0, 'forwin_overtime' => 2, 'forloss_overtime' => 1 );
			// NHL rule
			$point_rules[5] = array( 'forwin' => 2, 'fordraw' => 0, 'forloss' => 0, 'forwin_overtime' => 2, 'forloss_overtime' => 1 );
				
			return $point_rules[$rule];
		}
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
		$num_win = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `winner_id` = '".$team_id."' AND overtime = ''" );
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
		$num_win_overtime = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `winner_id` = '".$team_id."' AND `overtime` != ''" );
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
		$num_lost = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `loser_id` = '".$team_id."' AND overtime = ''" );
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
		$num_lost_overtime = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `loser_id` = '".$team_id."' AND `overtime` != ''" );
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
		
		$league = parent::getLeague($this->league_id);
		if ( $league->point_rule != 0 ) {
			$points['plus'] = $this->calculatePoints( $team_id, 'plus' );
			$points['minus'] = $this->calculatePoints( $team_id, 'minus' );
				
			if ( parent::isGymnasticsLeague( $this->league_id ) ) {
				$points2['plus'] = $this->calculateApparatusPoints( $team_id, 'plus' );
				$points2['minus'] = $this->calculateApparatusPoints( $team_id, 'minus' );
			} else {
				$points2['plus'] = $this->calculateGoalStatistics( $team_id, 'plus' );
				$points2['minus'] = $this->calculateGoalStatistics( $team_id, 'minus' );
			}
			
			$done_matches = $this->getNumDoneMatches($team_id);
			$won_matches = $this->getNumWonMatches($team_id) + $this->getNumWonMatchesOvertime($team_id);
			$draw_matches = $this->getNumDrawMatches($team_id);
			$lost_matches = $this->getNumLostMatches($team_id) + $this->getNumLostMatchesOvertime($team_id);
			$diff = $points2['plus'] - $points2['minus'];
			
			$wpdb->query ( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `points_plus` = '%d', `points_minus` = '%d', `points2_plus` = '%d', `points2_minus` = '%d', `done_matches` = '%d', `won_matches` = '%d', `draw_matches` = '%d', `lost_matches` = '%d', `diff` = '%d' WHERE `id` = '%d'", $points['plus'], $points['minus'], $points2['plus'], $points2['minus'], $done_matches, $won_matches, $draw_matches, $lost_matches, $diff, $team_id ) );
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
		
		$points = array( 'plus' => 0, 'minus' => 0 );
		$points['plus'] = $num_win * $forwin + $num_draw * $fordraw + $num_lost * $forloss + $num_win_overtime * $forwin_overtime + $num_lost_overtime * $forloss_overtime;
		$points['minus'] = $num_draw * $fordraw + $num_lost * $forwin + $num_lost_overtime * $forwin_overtime + $num_win_overtime * $forloss_overtime + $num_win * $forloss;
		
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
		$home = $wpdb->get_results( "SELECT `points2` FROM {$wpdb->leaguemanager_matches} WHERE `home_team` = '".$team_id."'" );
		$away = $wpdb->get_results( "SELECT `points2` FROM {$wpdb->leaguemanager_matches} WHERE `away_team` = '".$team_id."'" );
		
		$apparatus_points = array( 'plus' => 0, 'minus' => 0);
		if ( count($home) > 0 ) {
			foreach ( $home AS $home_apparatus ) {
				$ap = maybe_unserialize($home_apparatus->points2);
				if ( !is_array($ap) ) $ap = array();
				$apparatus_points['plus'] += intval($ap[0]['plus']);
				$apparatus_points['minus'] += intval($ap[0]['minus']);
			}
		}
		
		if ( count($away) > 0 ) {
			foreach ( $away AS $away_apparatus ) {
				$ap = maybe_unserialize($away_apparatus->points2);
				if ( !is_array($ap) ) $ap = array();
				$apparatus_points['plus'] += intval($ap[0]['minus']);
				$apparatus_points['minus'] += intval($ap[0]['plus']);
			}
		}
		
		return $apparatus_points[$option];
	}
	
	
	/**
	 * calculate goals. Penalty is not counted in statistics
	 *
	 * @param int $team_id
	 * @param string $option
	 * @return int
	 */
	function calculateGoalStatistics( $team_id, $option )
	{
		global $wpdb;
		
		$goals = array( 'plus' => 0, 'minus' => 0 );
				
		$matches = $wpdb->get_results( "SELECT `home_points`, `away_points`, `overtime` FROM {$wpdb->leaguemanager_matches} WHERE `home_team` = '".$team_id."'" );
		if ( $matches ) {
			foreach ( $matches AS $match ) {
				if ( !empty($match->overtime) ) {
					$match->overtime = maybe_unserialize($match->overtime);
					$home_goals = $match->overtime['home'];
					$away_goals = $match->overtime['away'];
				} else {
					$home_goals = $match->home_points;
					$away_goals = $match->away_points;
				}
				
				$goals['plus'] += $home_goals;
				$goals['minus'] += $away_goals;
			}
		}
		
		$matches = $wpdb->get_results( "SELECT `home_points`, `away_points`, `overtime` FROM {$wpdb->leaguemanager_matches} WHERE `away_team` = '".$team_id."'" );
		if ( $matches ) {
			foreach ( $matches AS $match ) {
				if ( !empty($match->overtime) ) {
					$match->overtime = maybe_unserialize($match->overtime);
					$home_goals = $match->overtime['home'];
					$away_goals = $match->overtime['away'];
				} else {
					$home_goals = $match->home_points;
					$away_goals = $match->away_points;
				}
				
				$goals['plus'] += $away_goals;
				$goals['minus'] += $home_goals;
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
	 * @param string $sport
	 * @param string $ranking
	 * @param string $mode
	 * @param int $project_id
	 * @param int $league_id
	 * @return void
	 */
	function editLeague( $title, $point_rule, $point_format, $sport, $ranking, $mode, $project_id, $league_id )
	{
		global $wpdb;

		$point_rule = maybe_serialize( $point_rule );
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->leaguemanager} SET `title` = '%s', `point_rule` = '%s', `point_format` = '%s', `sport` = '%s', `team_ranking` = '%s', `mode` = '%s',`project_id` = '%d' WHERE `id` = '%d'", $title, $point_rule, $point_format, $sport, $ranking, $mode, $project_id, $league_id ) );
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
		
		// Delete all seasons of this league
		$options = get_option('leaguemanager');
		unset($options['seasons'][$league_id]);
		update_option('leaguemanager', $options);
		
		// Delete Teams and with it Matches
		foreach ( parent::getTeams( "league_id = '".$league_id."'" ) AS $team )
			$this->delTeam( $team->id );

		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager} WHERE `id` = {$league_id}" );
	}

	
	/**
	 * add new season to league
	 *
	 * @param string $season
	 * @param int $num_match_days
	 * @param int $league_id
	 * @param boolean $add_teams
	 * @return void
	 */
	function addSeason( $season, $num_match_days, $league_id, $add_teams )
	{
		global $leaguemanager;
		$league = $leaguemanager->getLeague($league_id);
		if ( $add_teams && !empty($league->seasons) ) {
			$last_season = end($league->seasons);
			if ( $teams = $leaguemanager->getTeams("`league_id` = ".$league_id." AND `season` = ".$last_season) ) {
				foreach ( $teams AS $team ) {
					$this->addTeamFromDB( $league_id, $season, $team->id, false );
				}
			}
		}
			
		array_push($league->seasons, array( 'name' => $season, 'num_match_days' => $num_match_days ));
		$this->saveSeasons($league->seasons, $league->id);

		parent::setMessage( sprintf(__('Season <strong>%s</strong> added','leaguemanager'), $season ) );
		parent::printMessage();
	}
	
	
	/**
	 * delete season of league
	 *
	 * @param string $season
	 * @param int $league_id
	 * @return array of new options
	 */
	function delSeason( $key, $league_id )
	{
		global $leaguemanager;
		$league = $leaguemanager->getLeague($league_id);

		$season = $league->seasons[$key];

		// Delete teams and matches if there are any
		if ( $teams = $leaguemanager->getTeams("`league_id` = ".$league_id." AND `season` = ".$season['name']) ) {
			foreach ( $teams AS $team )
				$this->delTeam($team->id);
		}
		
		unset($league->seasons[$key]);
		$this->saveSeasons(array_values($league->seasons), $league->id);
	}
	
	
	/**
	 * save seasons array to database
	 *
	 * @param array $seasons
	 * @param int $league_id
	 */
	function saveSeasons($seasons, $league_id)
	{
		global $wpdb;
		$wpdb->query( "UPDATE {$wpdb->leaguemanager} SET `seasons` = '".maybe_serialize($seasons)."' WHERE `id` = {$league_id}" );
	}


	/**
	 * add new team
	 *
	 * @param int $league_id
	 * @param mixed $season
	 * @param string $title
	 * @param string $website
	 * @param string $coach
	 * @param int $home 1 | 0
	 * @param boolean $message (optional)
	 * @return void
	 */
	function addTeam( $league_id, $season, $title, $website, $coach, $home, $message = true )
	{
		global $wpdb;

		$sql = "INSERT INTO {$wpdb->leaguemanager_teams} (title, website, coach, home, season, league_id) VALUES ('%s', '%s', '%s', '%d', '%s', '%d')";
		$wpdb->query( $wpdb->prepare ( $sql, $title, $website, $coach, $home, $season, $league_id ) );
		$team_id = $wpdb->insert_id;

		if ( isset($_FILES['logo']) && $_FILES['logo']['name'] != '' )
			$this->uploadLogo($team_id, $_FILES['logo']);
		
		if ( $message )
			parent::setMessage( __('Team added','leaguemanager') );
			
		return $team_id;
	}


	/**
	 * add new team with data from existing team
	 *
	 * @param int $league_id
	 * @param string $season
	 * @param int $team_id
	 * @param boolean $message (optional)
	 * @return void
	 */
	function addTeamFromDB( $league_id, $season, $team_id, $message = true )
	{
		global $wpdb;
		$team = parent::getTeam($team_id);
		$new_team_id = $this->addTeam($league_id, $season, $team->title, $team->website, $team->coach, $team->home, $message);
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `logo` = '%s' WHERE `id` = '%d'", $team->logo, $new_team_id ) );
	}
	
	
	/**
	 * edit team
	 *
	 * @param int $team_id
	 * @param string $title
	 * @param string $website
	 * @param string $coach
	 * @param int $home 1 | 0
	 * @param boolean $del_logo
	 * @param string $image_file
	 * @param boolean $overwrite_image
	 * @return void
	 */
	function editTeam( $team_id, $title, $website, $coach, $home, $del_logo = false, $image_file = '', $overwrite_image = false )
	{
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->leaguemanager_teams} SET `title` = '%s', `website` = '%s', `coach` = '%s', `home` = '%d' WHERE `id` = %d", $title, $website, $coach, $home, $team_id ) );
			
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
		
		$team = parent::getTeam( $team_id );
		$this->delLogo( $team->logo );
			
		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager_matches} WHERE `home_team` = '".$team_id."' OR `away_team` = '".$team_id."'" );
		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager_teams} WHERE `id` = '".$team_id."'" );
	}


	/**
	 * display dropdon menu of teams (cleaned from double entries)
	 *
	 * @param none
	 * @return void
	 */
	function teamsDropdownCleaned()
	{
		global $wpdb;
		$all_teams = $wpdb->get_results( "SELECT `title`, `id` FROM {$wpdb->leaguemanager_teams} ORDER BY `title` ASC" );
		$teams = array();
		foreach ( $all_teams AS $team ) {
			if ( !in_array($team->title, $teams) )
				$teams[$team->id] = $team->title;
		}
		foreach ( $teams AS $team_id => $name )
			echo "<option value='".$team_id."'>".$name."</option>";
	}
	
	
	/**
	 * gets ranking of teams
	 *
	 * @param string $input serialized string with order
	 * @param string $listname ID of list to sort
	 * @return sorted array of parameters
	 */
	function getRanking( $input, $listname = 'the-list-standings' )
	{
		parse_str( $input, $input_array );
		$input_array = $input_array[$listname];
		$order_array = array();
		for ( $i = 0; $i < count($input_array); $i++ ) {
			if ( $input_array[$i] != '' )
				$order_array[$i+1] = $input_array[$i];
		}
		return $order_array;	
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
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `logo` = '%s' WHERE id = '%d'", basename($file['name']), $team_id ) );
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
		@unlink( parent::getThumbnailPath($image) );
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
	 * @param mixed $season
	 * @param string $final
	 * @return string
	 */
	function addMatch( $date, $home_team, $away_team, $match_day, $location, $league_id, $season, $final )
	{
	 	global $wpdb;
		$sql = "INSERT INTO {$wpdb->leaguemanager_matches} (date, home_team, away_team, match_day, location, league_id, season, final) VALUES ('%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s')";
		$wpdb->query( $wpdb->prepare ( $sql, $date, $home_team, $away_team, $match_day, $location, $league_id, $season, $final ) );
		return $wpdb->insert_id;
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
	 * @param array $overtime
	 * @param array $penalty
	 * @return string
	 */
	function editMatch( $date, $home_team, $away_team, $match_day, $location, $league_id, $match_id, $home_points, $away_points, $home_points2, $away_points2, $overtime, $penalty )
	{
	 	global $wpdb;
		$this->league_id = $league_id;
		
		$home_points = ($home_points == '') ? 'NULL' : $home_points;
		$away_points = ($away_points == '') ? 'NULL' : $away_points;
		
		$points2 = array();
		if (!is_array($home_points2)) $home_points2 = array();
		foreach ( $home_points2 AS $i => $points ) {
			$points2[] = array( 'plus' => $points, 'minus' => $away_points2[$i] );
		}
		
		$overtime_points = $penalty_points = '';
		if ( !empty($penalty['home']) && !empty($penalty['away']) )
			$points = array( 'home' => $penalty['home'], 'away' => $penalty['away'] );
		elseif ( !empty($overtime['home']) && !empty($overtime['away']) )
			$points = array( 'home' => $overtime['home'], 'away' => $overtime['away'] );
		else
			$points = array( 'home' => $home_points, 'away' => $away_points );
			
		$winner = $this->getMatchResult( $points['home'], $points['away'], $home_team, $away_team, 'winner' );
		$loser = $this->getMatchResult( $points['home'], $points['away'], $home_team, $away_team, 'loser' );
		
		$overtime_points = ( !empty($overtime['home']) && !empty($overtime['away']) ) ? array( 'home' => $overtime['home'], 'away' => $overtime['away'] ) : '';
		$penalty_points = ( !empty($penalty['home']) && !empty($penalty['away']) ) ? array( 'home' => $penalty['home'], 'away' => $penalty['away'] ) : '';

		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->leaguemanager_matches} SET `date` = '%s', `home_team` = '%s', `away_team` = '%s', `match_day` = '%d', `location` = '%s', `league_id` = '%d', `home_points` = ".$home_points.", `away_points` = ".$away_points.", `points2` = '%s', `winner_id` = ".intval($winner).", `loser_id` = ".intval($loser).", `overtime` = '%s', `penalty` = '%s' WHERE `id` = %d", $date, $home_team, $away_team, $match_day, $location, $league_id, maybe_serialize($points2), maybe_serialize($overtime_points), maybe_serialize($penalty_points), $match_id ) );
			
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
	 * @param array $overtiem
	 * @param array $penalty
	 * @return string
	 */
	function updateResults( $league_id, $matches, $home_points2, $away_points2, $home_points, $away_points, $home_team, $away_team, $overtime, $penalty )
	{
		global $wpdb;
		if ( null != $matches ) {
			$this->league_id = $league_id;
			while ( list($match_id) = each($matches) ) {
				$home_points[$match_id] = ( '' == $home_points[$match_id] ) ? 'NULL' : $home_points[$match_id];
				$away_points[$match_id] = ( '' == $away_points[$match_id] ) ? 'NULL' : $away_points[$match_id];
				
				$points2 = array();
				if (!is_array($home_points2)) $home_points2 = array();
				foreach ( $home_points2[$match_id] AS $i => $points ) {
					$points2[] = array( 'plus' => $points, 'minus' => $away_points2[$match_id][$i] );
				}
				
				$overtime_points = $penalty_points = '';
				if ( !empty($penalty[$match_id]['home']) && !empty($penalty[$match_id]['away']) )
					$points = array( 'home' => $penalty[$match_id]['home'], 'away' => $penalty[$match_id]['away'] );
				elseif ( !empty($overtime[$match_id]['home']) && !empty($overtime[$match_id]['away']) )
					$points = array( 'home' => $overtime[$match_id]['home'], 'away' => $overtime[$match_id]['away'] );
				else
					$points = array( 'home' => $home_points[$match_id], 'away' => $away_points[$match_id] );
				
				$penalty_points = ( !empty($penalty[$match_id]['home']) && !empty($penalty[$match_id]['away']) ) ? array('home' => $penalty[$match_id]['home'], 'away' => $penalty[$match_id]['away'] ) : '';
				$overtime_points = ( !empty($overtime[$match_id]['home']) && !empty($overtime[$match_id]['away']) ) ? array('home' => $overtime[$match_id]['home'], 'away' => $overtime[$match_id]['away'] ) : '';
			
				$winner = $this->getMatchResult( $points['home'], $points['away'], $home_team[$match_id], $away_team[$match_id], 'winner' );
				$loser = $this->getMatchResult($points['home'], $points['away'], $home_team[$match_id], $away_team[$match_id], 'loser' );
				
				$wpdb->query( "UPDATE {$wpdb->leaguemanager_matches} SET `home_points` = ".$home_points[$match_id].", `away_points` = ".$away_points[$match_id].", `points2` = '".maybe_serialize($points2)."', `winner_id` = ".intval($winner).", `loser_id` = ".intval($loser).", `overtime` = '".maybe_serialize($overtime_points)."', `penalty` = '".maybe_serialize($penalty_points)."' WHERE `id` = {$match_id}" );
			
				// update points for each team
				$this->saveStandings($home_team[$match_id]);
				$this->saveStandings($away_team[$match_id]);
			}
		}
		parent::setMessage( __('Updated League Results','leaguemanager') );
	}
	
	
	/**
	 * save results for final rounds
	 *
	 * @param int $league_id
	 * @param
	 */
	function updateFinalResults()
	{
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
		for ( $y = date("Y")-20; $y <= date("Y")+10; $y++ ) {
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
	
	
	/**
	 * import data from CSV file
	 *
	 * @param int $league_id
	 * @param array $file CSV file
	 * @param string $delimiter
	 * @param array $mode 'teams' | 'matches'
	 * @return string
	 */
	function import( $league_id, $file, $delimiter, $mode )
	{
		if ( $file['size'] > 0 ) {
			/*
			* Upload CSV file to image directory, temporarily
			*/
			$new_file =  parent::getImagePath().'/'.basename($file['name']);
			if ( move_uploaded_file($file['tmp_name'], $new_file) ) {
				$this->league_id = $league_id;
				if ( 'teams' == $mode )
					$this->importTeams($new_file, $delimiter);
				elseif ( 'matches' == $mode )
					$this->importMatches($new_file, $delimiter);
			} else {
				parent::setMessage(sprintf( __('The uploaded file could not be moved to %s.' ), parent::getImagePath()) );
			}
			@unlink($new_file); // remove file from server after import is done
		} else {
			parent::setMessage( __('The uploaded file seems to be empty', 'projectmanager'), true );
		}
	}
	
	
	/**
	 * import teams from CSV file
	 *
	 * @param string $file
	 * @param string $delimiter
	 */
	function importTeams( $file, $delimiter )
	{
		global $leaguemanager;
		
		$handle = @fopen($file, "r");
		if ($handle) {
			$league = $leaguemanager->getLeague( $this->league_id );
			if ( "TAB" == $delimiter ) $delimiter = "\t"; // correct tabular delimiter
			
			$i = 0;
			while (!feof($handle)) {
				$buffer = fgets($handle, 4096);
				$line = explode($delimiter, $buffer);
				
				// ignore header and empty lines
				if ( $i > 0 && $line ) {
					$team = $line[0]; $website = $line[1]; $coach = $line[2]; $home = $line[3]; $season = $line[10];
					$team_id = $this->addTeam( $this->league_id, $season, $team, $website, $coach, $home, false );
	
					$pld = $line[4]; $won = $line[5]; $draw = $line[6]; $lost = $line[7]; $points2 = explode(":", $line[8]); $points = explode(str_replace("%d","",$league->point_format), $line[9]);
					$this->saveStandingsManually($team_id, $points[0], $points[1], $points2[0], $points2[1], $pld, $won, $draw, $lost);
				}
				
				$i++;
			}
			fclose($handle);
			
			parent::setMessage(sprintf(__( '%d Teams imported', 'leaguemanager' ), $i-1));
		}
	}
	
	
	/**
	 * import matches from CSV file
	 *
	 * @param string $file
	 * @param string $delimiter
	 */
	function importMatches( $file, $delimiter )
	{
		global $leaguemanager;
		
		$handle = @fopen($file, "r");
		if ($handle) {
			if ( "TAB" == $delimiter ) $delimiter = "\t"; // correct tabular delimiter
			
			$league = $leaguemanager->getLeague( $this->league_id );
	
			$i = 0;
			while (!feof($handle)) {
				$buffer = fgets($handle, 4096);
				$line = explode($delimiter, $buffer);
				
				// ignore header and empty lines
				if ( $i > 0 && $line ) {
					$date = ( !empty($line[6]) ) ? $line[0]." ".$line[6] : $line[0]. " 00:00";
					$match_day = $line[2];
					$date = trim($date);
					$home_team = $this->getTeamID($line[3]);
					$away_team = $this->getTeamID($line[4]);
					$location = $line[5];
					
					$match_id = $this->addMatch($date, $home_team, $away_team, $match_day, $location, $this->league_id);
		
					$x = 7; // define column index
					if ( $leaguemanager->getMatchParts($league->sport) ) {
						$p = explode(",", $line[$x]);
						$home_points2 = $away_points2 = array();
						if ( is_array($p) ) {
							foreach ( $p AS $pts ) {
								$points2 = explode(":", $pts);
								$home_points2[] = $points2[0];
								$away_points2[] = $points2[1];
							}
						}
						
						$x++; // increment column index
					}
					// score
					if ( !empty($line[$x]) )
						$score = explode(":", $line[$x]);
					else
						$score = array('','');
					
					if ( !$leaguemanager->isGymnasticsLeague( $this->league_id ) ) {
						$overtime = explode(":",$line[$x+1]);
						$overtime = array('home' => $overtime[0], 'away' => $overtime[1]);
						$penalty = explode(":",$line[$x+2]);
						$penalty = array('home' => $penalty[0], 'away' => $penalty[1]);
					}
					
					$this->editMatch( $date, $home_team, $away_team, $match_day, $location, $this->league_id, $match_id, $score[0], $score[1], $home_points2, $away_points2, $overtime, $penalty );
				}
				
				$i++;
			}
			fclose($handle);
			
			parent::setMessage(sprintf(__( '%d Matches imported', 'leaguemanager' ), $i-1));
		}
	}
	
	
	/**
	 * get Team ID for given string
	 *
	 * @param string $title
	 * @return int
	 */
	function getTeamID( $title )
	{
		global $wpdb;
		
		$team = $wpdb->get_results( "SELECT `id` FROM {$wpdb->leaguemanager_teams} WHERE `title` = '".$title."' AND `league_id` = {$this->league_id}" );
		return $team[0]->id;
	}
	
	
	/**
	 * export league data
	 *
	 * @param int $league_id
	 * @param string $mode
	 * @return file
	 */
	function export( $league_id, $mode )
	{
		global $leaguemanager;
		
		$this->league_id = $league_id;
		$league = $leaguemanager->getLeague($league_id);
		$filename = sanitize_title($league->title)."-".$mode."_".date("Y-m-d").".csv";
		
		if ( 'teams' == $mode )
			$contents = $this->exportTeams();
		elseif ( 'matches' ==  $mode )
			$contents = $this->exportMatches();
		
		
		header('Content-Type: text/csv');
    		header('Content-Disposition: inline; filename="'.$filename.'"');
		echo $contents;
		exit();
	}
	
	
	/**
	 * export teams
	 *
	 * @param none
	 * @return string
	 */
	function exportTeams()
	{
		global $leaguemanager;
		
		$league = $leaguemanager->getLeague($this->league_id);

		$teams = parent::getTeams( "league_id =".$this->league_id );
		
		if ( $teams ) {
			$contents = __('Team','leaguemanager')."\t".__('Website','leaguemanager')."\t".__('Coach','leaguemanager')."\t".__('Home Team','leaguemanager')."\t".__('Pld','leaguemanager')."\t"._c('W|Won','leaguemanager')."\t"._c('T|Tie','leaguemanager')."\t"._c('L|Lost','leaguemanager')."\t";
			if ( $leaguemanager->isGymnasticsLeague( $league->id ) )
				$contents .= _c('AP|apparatus points','leaguemanager');
			else
				$contents .= __('Goals','leaguemanager');
			$contents .= "\t".__('Pts','leaguemanager')."\t".__('Season','leaguemanager');
			
			foreach ( $teams AS $team ) {
				$home = ( $team->home == 1 ) ? 1 : 0;
				$contents .= "\n".$team->title."\t".$team->website."\t".$team->coach."\t".$home."\t".$team->done_matches."\t".$team->won_matches."\t".$team->draw_matches."\t".$team->lost_matches."\t".sprintf("%d:%d",$team->points2_plus, $team->points2_minus)."\t".sprintf($league->point_format, $team->points_plus, $team->points_minus)."\t".$team->season;
			}
			return $contents;
		}
		return false;
	}
	
	
	/**
	 * export matches
	 *
	 * @param none
	 * @return string
	 */
	function exportMatches()
	{
		global $leaguemanager;
		
		$matches = parent::getMatches( "league_id=".$this->league_id );
		if ( $matches ) {
			$league = $leaguemanager->getLeague( $this->league_id );
			$teams = parent::getTeams( "league_id=".$this->league_id, 'ARRAY' );
		
			// Build header
			$contents = __('Date','leaguemanager')."\t"._('Season','leaguemanager')."\t".__('Match Day','leaguemanager')."\t".__('Home','leaguemanager')."\t".__('Guest','leaguemanager')."\t".__('Location','leaguemanager')."\t".__('Begin','leaguemanager');
			if ( $leaguemanager->getMatchParts($league->sport) )
				$contents .= "\t".$leaguemanager->getMatchPartsTitle( $league->sport );
			$contents .= "\t".__('Score','leaguemanager');
			if ( !$leaguemanager->isGymnasticsLeague( $this->league_id ) ) $contents .= "\t".__('Overtime','leaguemanager')."\t".__('Penalty','leaguemanager');
	
			foreach ( $matches AS $match ) {
				$contents .= "\n".mysql2date('Y-m-d', $match->date)."\t".$match->season."\t".$match->match_day."\t".$teams[$match->home_team]['title']."\t".$teams[$match->away_team]['title']."\t".$match->location."\t".mysql2date("H:i", $match->date);

				if ( $leaguemanager->getMatchParts($league->sport) ) {
					$points2 = maybe_unserialize( $match->points2 );
					if ( !is_array($points2) ) $points2 = array($points2);
					
					$p = array();
					for ( $x = 1; $x <= $leaguemanager->getMatchParts($league->sport); $x++ )
						$p[] = sprintf("%d:%d", $points2[$x-1]['plus'], $points2[$x-1]['minus']);
						
					$contents .= "\t".implode(",", $p);
				}
			
				$contents .= !empty($match->home_points) ? "\t".sprintf("%d:%d",$match->home_points, $match->away_points) : "\t";
							
				if ( !$leaguemanager->isGymnasticsLeague( $this->league_id ) ) {
					$match->overtime = maybe_unserialize($match->overtime);
					$match->penalty = maybe_unserialize($match->penalty);
	
					if ( !empty($match->overtime) )
						$match->overtime = sprintf("%d:%d", $match->overtime['home'], $match->overtime['away']);
					if ( !empty($match->penalty) )
						$match->penalty = sprintf("%d:%d", $match->penalty['home'], $match->penalty['away']);
							
					$contents .= "\t".$match->overtime."\t".$match->penalty;
				}
			}
			return $contents;
		}
		
		return false;
	}
}
?>
