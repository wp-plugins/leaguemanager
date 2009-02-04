<?php

class LeagueManager
{
	/**
	 * array of leagues with preferences
	 *
	 * @var array
	 */
	var $leagues = array();
	

	/**
	 * error handling
	 *
	 * @var boolean
	 */
	var $error = false;
	
	
	/**
	 * message
	 *
	 * @var string
	 */
	var $message = '';
	
	
	/**
	 * Initializes plugin
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
	}
	function WP_LeagueManager()
	{
		$this->__construct();
	}
	
	
	/**
	 * get months
	 *
	 * @param none
	 * @return void
	 */
	function getMonths()
	{
		$locale = get_locale();
		setlocale(LC_ALL, $locale);
		for ( $month = 1; $month <= 12; $month++ ) 
			$months[$month] = htmlentities( strftime( "%B", mktime( 0,0,0, $month, date("m"), date("Y") ) ) );
			
		return $months;
	}
	
	
	/**
	 * returns image directory
	 *
	 * @param string|false $file
	 * @return string
	 */
	function getImagePath( $file = false )
	{
		if ( $file )
			return WP_CONTENT_DIR.'/uploads/leaguemanager/'.$file;
		else
			return WP_CONTENT_DIR.'/uploads/leaguemanager';
	}
	
	
	/**
	 * returns url of image directory
	 *
	 * @param string|false $file image file
	 * @return string
	 */
	function getImageUrl( $file = false )
	{
		if ( $file )
			return WP_CONTENT_URL.'/uploads/leaguemanager/'.$file;
		else
			return WP_CONTENT_URL.'/uploads/leaguemanager';
	}

	
	/**
	 * set message
	 *
	 * @param string $message
	 * @param boolean $error triggers error message if true
	 * @return none
	 */
	function setMessage( $message, $error = false )
	{
		$type = 'success';
		if ( $error ) {
			$this->error = true;
			$type = 'error';
		}
		$this->message[$type] = $message;
	}
	
	
	/**
	 * return message
	 *
	 * @param none
	 * @return string
	 */
	function getMessage()
	{
		if ( $this->error )
			return $this->message['error'];
		else
			return $this->message['success'];
	}
	
	
	/**
	 * print formatted message
	 *
	 * @param none
	 * @return string
	 */
	function printMessage()
	{
		if ( $this->error )
			echo "<div class='error'><p>".$this->getMessage()."</p></div>";
		else
			echo "<div id='message' class='updated fade'><p><strong>".$this->getMessage()."</strong></p></div";
	}

	
	/**
	 * retrieve match day
	 *
	 * @param none
	 * @return int
	 */
	function getMatchDay( $current = false )
	{
		global $wpdb;
		
		if ( isset($_GET['match_day']) )
			$match_day = (int)$_GET['match_day'];
		elseif ( isset($this->match_day) )
			$match_day = $this->match_day;
		elseif ( $current && $match = $this->getMatches( "league_id = '".$this->league_id."' AND DATEDIFF(NOW(), `date`) < 0", 1 ) )
			$match_day = $match[0]->match_day;
		else
			$match_day = 1;

		return $match_day;
	}
	
	
	/**
	 * retrieve number of match days
	 *
	 * @param int $league_id
	 * @return int
	 */
	function getNumMatchDays( $league_id )
	{
		$prefs = $this->getLeaguePreferences( $league_id );
		return $prefs->num_match_days;
	}
	
	
	/**
	 * get leagues from database
	 *
	 * @param int $league_id (default: false)
	 * @param string $search
	 * @return array
	 */
	function getLeagues( $league_id = false, $search = '' )
	{
		global $wpdb;
		
		$leagues = array();
		if ( $league_id ) {
			$leagues = $wpdb->get_results( "SELECT `title`, `id`, `active` FROM {$wpdb->leaguemanager} WHERE id = '".$league_id."' ORDER BY id ASC" );
			
			$this->leagues[$leagues[0]->id] = array( 'title' => $leagues[0]->title, 'status' => $leagues[0]->active, 'preferences' => $this->getLeaguePreferences($leagues[0]->id) );
			return $this->leagues[$leagues[0]->id];
		} else {
			if ( $leagues_sql = $wpdb->get_results( "SELECT `title`, `id`, `active` FROM {$wpdb->leaguemanager} $search ORDER BY id ASC" ) ) {
				foreach( $leagues_sql AS $league ) {
					$this->leagues[$league->id] = array( 'title' => $league->title, 'status' => $leagues[0]->active, 'preferences' => $this->getLeaguePreferences($league->id) );
					$leagues[$league->id] = array( 'title' => $league->title, 'status' => $leagues[0]->active, 'preferences' => $this->getLeaguePreferences($league->id) );
				}
			}
			return $leagues;
		}	
	}
	
	
	/**
	 * get league settings
	 * 
	 * @param int $league_id
	 * @return array
	 */
	function getLeaguePreferences( $league_id )
	{
		global $wpdb;
		
		$preferences = $wpdb->get_results( "SELECT `forwin`, `fordraw`, `forloss`, `type`, `num_match_days`, `show_logo` FROM {$wpdb->leaguemanager} WHERE id = '".$league_id."'" );
		$preferences[0]->colors = maybe_unserialize($preferences[0]->colors);
		return $preferences[0];
	}
	
	
	/**
	 * gets league title
	 *
	 * @param int $league_id
	 * @return string
	 */
	function getLeagueTitle( $league_id )
	{
		return $this->leagues[$league_id]['title'];
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
			echo '<a href="edit.php?page=leaguemanager/manage-leagues.php&amp;deactivate_league='.$league_id.'">'.__( 'Deactivate', 'leaguemanager' ).'</a>';
		else
			echo '<a href="edit.php?page=leaguemanager/manage-leagues.php&amp;activate_league='.$league_id.'">'.__( 'Activate', 'leaguemanager' ).'</a>';
	}
	
	
	/**
	 * get teams from database
	 *
	 * @param string $search search string for WHERE clause.
	 * @param string $output OBJECT | ARRAY
	 * @return array database results
	 */
	function getTeams( $search, $output = 'OBJECT' )
	{
		global $wpdb;
		
		$teams_sql = $wpdb->get_results( "SELECT `title`, `short_title`, `logo`, `home`, `points_plus`, `points_minus`, `points2_plus`, `points2_minus`, `done_matches`, `won_matches`, `draw_matches`, `lost_matches`, `league_id`, `id` FROM {$wpdb->leaguemanager_teams} WHERE $search ORDER BY id ASC" );
		
		if ( 'ARRAY' == $output ) {
			$teams = array();
			foreach ( $teams_sql AS $team ) {
				$teams[$team->id]['title'] = $team->title;
				$teams[$team->id]['short_title'] = $team->short_title;
				$teams[$team->id]['logo'] = $teams->logo;
				$teams[$team->id]['home'] = $team->home;
				$teams[$team->id]['points'] = array( 'plus' => $team->points_plus, 'minus' => $team->points_minus );
				$teams[$team->id]['points2'] = array( 'plus' => $team->points2_plus, 'minus' => $team->points2_minus );
			}
			
			return $teams;
		}
		return $teams_sql;
	}
	
	
	/**
	 * get single team
	 *
	 * @param int $team_id
	 * @return object
	 */
	function getTeam( $team_id )
	{
		$teams = $this->getTeams( "`id` = {$team_id}" );
		return $teams[0];
	}
	
	
	/**
	 * gets number of teams for specific league
	 *
	 * @param int $league_id
	 * @return int
	 */
	function getNumTeams( $league_id )
	{
		global $wpdb;
	
		$num_teams = $wpdb->get_var( "SELECT COUNT(ID) leagueIsActiveFROM {$wpdb->leaguemanager_teams} WHERE `league_id` = '".$league_id."'" );
		return $num_teams;
	}
	
	
	/**
	 * gets number of matches
	 *
	 * @param string $search
	 * @return int
	 */
	function getNumMatches( $league_id )
	{
		global $wpdb;
	
		$num_matches = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `league_id` = '".$league_id."'" );
		return $num_matches;
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
	 * get number of won matches
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumWonMatches( $team_id )
	{
		global $wpdb;
		$num_win = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `winner_id` = '".$team_id."'" );
		return $num_win;
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
	 * get number of lost matches
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumLostMatches( $team_id )
	{
		global $wpdb;
		$num_lost = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `loser_id` = '".$team_id."'" );
		return $num_lost;
	}
	
	
	/**
	 * check if league is gymnastics league
	 *
	 * @param none
	 * @return boolean
	 */
	function isGymnasticsLeague( $league_id )
	{
		$preferences = $this->getLeaguePreferences( $league_id );
		if ( 1 == $preferences->type )
			return true;
		
		return false;
	}
	
	
	/**
	 * rank teams
	 *
	 * @param array $teams
	 * @return array $teams ordered
	 */
	function rankTeams( $league_id )
	{
		global $wpdb;
		$this->league_id = $league_id;
			
		$teams = array();
		foreach ( $this->getTeams( "league_id = '".$league_id."'" ) AS $team ) {
			$points = array( 'plus' => $team->points_plus, 'minus' => $team->points_minus );
			$points2 = array( 'plus' => $team->points2_plus, 'minus' => $team->points2_minus );

			$d = $this->calculateDiff( $points2['plus'], $points2['minus'] );
							
			$teams[] = array('id' => $team->id, 'home' => $team->home, 'title' => $team->title, 'short_title' => $team->short_title, 'logo' => $team->logo, 'done_matches' => $team->done_matches, 'won_matches' => $team->won_matches, 'draw_matches' => $team->draw_matches, 'lost_matches' => $team->lost_matches, 'points' => array('plus' => $points['plus'], 'minus' => $points['minus']), 'points2' => array('plus' => $points2['plus'], 'minus' => $points2['minus']), 'diff' => $d );
		}
		
		foreach ( $teams AS $key => $row ) {
			$points_1[$key] = $row['points']['plus'];
			$points_2[$key] = $row['points2']['plus'];
			$diff[$key] = $row['diff'];
		}
		
		if ( count($teams) > 0 ) {
			if ( $this->isGymnasticsLeague($league_id) )
				array_multisort($points_1, SORT_DESC, $points_2, SORT_DESC, $teams);
			else
				array_multisort($points_1, SORT_DESC, $diff, SORT_DESC, $teams);
		}
		
		return $teams;
	}
	
	
	/**
	 * gets matches from database
	 * 
	 * @param string $search
	 * @return array
	 */
	function getMatches( $search, $limit = false, $output = 'OBJECT' )
	{
	 	global $wpdb;
		
		$sql = "SELECT `home_team`, `away_team`, DATE_FORMAT(`date`, '%Y-%m-%d %H:%i') AS date, DATE_FORMAT(`date`, '%e') AS day, DATE_FORMAT(`date`, '%c') AS month, DATE_FORMAT(`date`, '%Y') AS year, DATE_FORMAT(`date`, '%H') AS `hour`, DATE_FORMAT(`date`, '%i') AS `minutes`, `match_day`, `location`, `league_id`, `home_apparatus_points`, `away_apparatus_points`, `home_points`, `away_points`, `winner_id`, `post_id`, `id` FROM {$wpdb->leaguemanager_matches} WHERE $search ORDER BY `date` ASC";
			
		if ( $limit ) $sql .= " LIMIT 0,".$limit."";
		
		return $wpdb->get_results( $sql, $output );
	}
	
	
	/**
	 * get single match
	 *
	 * @param int $match_id
	 * @return object
	 */
	function getMatch( $match_id )
	{
		$matches = $this->getMatches( "`id` = {$match_id}" );
		return $matches[0];
	}
	
	
	/**
	 * test if it's a match of home team
	 *
	 * @param int $home_team
	 * @param int $away_team
	 * @param array $teams
	 * @return boolean
	 */
	function isHomeTeamMatch( $home_team, $away_team, $teams )
	{
		if ( 1 == $teams[$home_team]['home'] )
			return true;
		elseif ( 1 == $teams[$away_team]['home'] )
			return true;
		else
			return false;
	}
	

	/**
	 * adds code to Wordpress head
	 *
	 * @param none
	 */
	function addHeaderCode()
	{
		$options = get_option('leaguemanager');
		
		echo "\n\n<!-- WP LeagueManager Plugin Version ".LEAGUEMANAGER_VERSION." START -->\n";
		echo "<link rel='stylesheet' href='".LEAGUEMANAGER_URL."/style.css' type='text/css' />\n";

		if ( !is_admin() ) {
			// Table styles
			echo "\n<style type='text/css'>";
			echo "\n\ttable.leaguemanager th { background-color: ".$options['colors']['headers']." }";
			echo "\n\ttable.leaguemanager tr { background-color: ".$options['colors']['rows'][1]." }";
			echo "\n\ttable.leaguemanager tr.alternate { background-color: ".$options['colors']['rows'][0]." }";
			echo "\n\ttable.crosstable th, table.crosstable td { border: 1px solid ".$options['colors']['rows'][0]."; }";
			echo "\n</style>";
		}

		wp_register_script( 'leaguemanager', LEAGUEMANAGER_URL.'/leaguemanager.js', array('thickbox', 'colorpicker'), LEAGUEMANAGER_VERSION );
		wp_print_scripts( 'leaguemanager' );
		//echo '<link rel="stylesheet" href="'.get_option( 'siteurl' ).'/wp-includes/js/thickbox/thickbox.css" type="text/css" media="screen" />';
		
		echo "<!-- WP LeagueManager Plugin END -->\n\n";
	}
}
?>
