<?php
/**
 * Core class for the WordPress plugin LeagueManager
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright 	Copyright 2008-2009
*/
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
	var $message;
	
	
	/**
	 * Initializes plugin
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		$this->loadOptions();
		$this->league_id = false;
	}
	function LeagueManager()
	{
		$this->__construct();
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
	 * @param none
	 * @return void
	 */
	function getOptions()
	{
		return $this->options;
	}
	
	
	/**
	 * set league id
	 *
	 * @param int $league_id
	 * @return void
	 */
	function setLeagueID( $league_id )
	{
		$this->league_id = $league_id;
	}
	
	
	/**
	 * retrieve league ID
	 *
	 * @param none
	 * @return int ID of current league
	 */
	function getLeagueID()
	{
		return $this->league_id;
	}
	
	
	/**
	 * get league types
	 *
	 * @param none
	 * @return array
	 */
	function getLeagueTypes()
	{
		return array( 1 => __('Gymnastics', 'leaguemanager'), 2 => __('Ball game', 'leaguemanager'), 3 => __('Hockey', 'leaguemanager'), 4 => __('Basketball', 'leaguemanager'), 5 => __('Other', 'leaguemanager') );
	}
	
	
	/**
	 * get supported image types from Image class
	 *
	 * @param none
	 * @return array
	 */
	function getSupportedImageTypes()
	{
		return LeagueManagerImage::getSupportedImageTypes();
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
	 * get Thumbnail image
	 *
	 * @param string $file
	 * @return string
	 */
	function getThumbnailUrl( $file )
	{
		return $this->getImageUrl( 'thumb.'.basename($file) );
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
	 * Set match day
	 *
	 * @param int 
	 * @return void
	 */
	function setMatchDay( $match_day )
	{
		$this->match_day = $match_day;
	}
	
	
	/**
	* retrieve match daynggallery
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
					$this->leagues[$league->id] = array( 'title' => $league->title, 'status' => $league->active, 'preferences' => $this->getLeaguePreferences($league->id) );
					$leagues[$league->id] = array( 'title' => $league->title, 'status' => $league->active, 'preferences' => $this->getLeaguePreferences($league->id) );
				}
			}
			return $leagues;
		}	
	}
	
	
	/**
	 * get league
	 *
	 * @param int $league_id
	 * @return league object
	 */
	function getLeague( $league_id )
	{
		global $wpdb;
		
		$league = $wpdb->get_results( "SELECT `title`, `id`, `active`, `point_rule`, `point_format`, `type`, `num_match_days`, `show_logo` FROM {$wpdb->leaguemanager} WHERE id = '".$league_id."'" );

		return $league[0];
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
		
		$preferences = $wpdb->get_results( "SELECT `point_rule`, `point_format`, `type`, `num_match_days`, `show_logo` FROM {$wpdb->leaguemanager} WHERE id = '".$league_id."'" );
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
	 * get teams from database
	 *
	 * @param string $search search string for WHERE clause.
	 * @param string $output OBJECT | ARRAY
	 * @return array database results
	 */
	function getTeams( $search, $output = 'OBJECT' )
	{
		global $wpdb;
		
		$teams_sql = $wpdb->get_results( "SELECT `title`, `short_title`, `website`, `logo`, `home`, `points_plus`, `points_minus`, `points2_plus`, `points2_minus`, `done_matches`, `won_matches`, `draw_matches`, `lost_matches`, `diff`, `league_id`, `id` FROM {$wpdb->leaguemanager_teams} WHERE $search ORDER BY id ASC" );
		
		if ( 'ARRAY' == $output ) {
			$teams = array();
			foreach ( $teams_sql AS $team ) {
				$teams[$team->id]['title'] = $team->title;
				$teams[$team->id]['short_title'] = $team->short_title;
				$teams[$team->id]['website'] = $team->website;
				$teams[$team->id]['logo'] = $team->logo;
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
	
		$num_teams = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_teams} WHERE `league_id` = '".$league_id."'" );
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
	 * check if league is gymnastics league (has apparatus points)
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
	 * check if league is ball game (has half time results)
	 *
	 * @param none
	 * @return boolean
	 */
	function isBallGameLeague( $league_id )
	{
		$preferences = $this->getLeaguePreferences( $league_id );
		if ( 2 == $preferences->type )
			return true;
			
		return false;
	}
	
	
	/**
	 * check if league is hockey league (played in thirds)
	 *
	 * @param none
	 * @return boolean
	 */
	function isHockeyLeague( $league_id )
	{
	$preferences = $this->getLeaguePreferenisBallGameLeagueces( $league_id );
		if ( 3 == $preferences->type )
			return true;
			
		return false;
	}
	
	
	/**
	 * check if league is basketball league (played in quarters)
	 *
	 * @param none
	 * @return boolean
	 */
	function isBasketballLeague( $league_id )
	{
		$preferences = $this->getLeaguePreferences( $league_id );
		if ( 4 == $preferences->type )
			return true;
			
		return false;
	}
	
	
	/**
	 * print match parts title depending on league type
	 *
	 * @param int $league_type
	 * @return string
	 */
	function matchPartsTitle( $league_type )
	{
		if ( 1 == $league_type )
			_e( 'Apparatus Points', 'leaguemanager' );
		elseif ( 2 == $league_type )
			_e( 'Halftime', 'leaguemanager' );
		elseif ( 3 == $league_type )
			_e( 'Thirds', 'leaguemanager' );
		elseif ( 4 == $league_type )
			_e( 'Quarters', 'leaguemanager');
	}
	
	
	/**
	 * get number of match parts
	 * e.g 1 for ball game (halftime) and gymnastics (apparatus points), 3 for hockey, 4 for basketball
	 *
	 * @param int $league_type
	 * @return int number of parts
	 */
	function getMatchParts( $league_type )
	{
		if ( 1 == $league_type || 2 == $league_type )
			return 1;
		elseif ( 3 == $league_type )
			return 3;
		elseif ( 4 == $league_type )
			return 4;
			
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
							
			$d = ( $team->diff > 0 ) ? '+'.$team->diff : $team->diff;	
			$teams[] = array('id' => $team->id, 'home' => $team->home, 'title' => $team->title, 'short_title' => $team->short_title, 'website' => $team->website, 'logo' => $team->logo, 'done_matches' => $team->done_matches, 'won_matches' => $team->won_matches, 'draw_matches' => $team->draw_matches, 'lost_matches' => $team->lost_matches, 'points' => array('plus' => $points['plus'], 'minus' => $points['minus']), 'points2' => array('plus' => $points2['plus'], 'minus' => $points2['minus']), 'diff' => $d );
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
		
		$sql = "SELECT `home_team`, `away_team`, DATE_FORMAT(`date`, '%Y-%m-%d %H:%i') AS date, DATE_FORMAT(`date`, '%e') AS day, DATE_FORMAT(`date`, '%c') AS month, DATE_FORMAT(`date`, '%Y') AS year, DATE_FORMAT(`date`, '%H') AS `hour`, DATE_FORMAT(`date`, '%i') AS `minutes`, `match_day`, `location`, `league_id`, `home_points`, `away_points`, `overtime`, `winner_id`, `post_id`, `points2`, `id` FROM {$wpdb->leaguemanager_matches} WHERE $search ORDER BY `date` ASC";
		
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
		$matches[0]->points2 = maybe_unserialize($matches[0]->points2);
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
}
?>
