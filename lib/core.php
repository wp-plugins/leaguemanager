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
	 * @param boolean $bridge
	 * @return void
	 */
	function __construct( $bridge )
	{
		$this->loadOptions();
		$this->league_id = false;
		$this->bridge = $bridge;
	}
	function LeagueManager( $bridge )
	{
		$this->__construct($bridge);
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
	 * check if bridge is active
	 *
	 * @param none
	 * @return boolean
	 */
	function isBridge()
	{
		return $this->bridge;
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
	 * set season
	 *
	 * @param mixed $season
	 * @return void
	 */
	function setSeason( $season )
	{
		$this->season = $season;
	}
	
	
	/**
	 * get league types
	 *
	 * @param none
	 * @return array
	 */
	function getLeagueTypes()
	{
		return array( 'gymnastics' => __('Gymnastics', 'leaguemanager'), 'ballgame' => __('Ball game', 'leaguemanager'), 'hockey' => __('Hockey', 'leaguemanager'), 'basketball' => __('Basketball', 'leaguemanager'), 'irish-gaelic-football' => __('Irish Gaelic Football', 'leaguemanager'), 'baseball' => __('Softball/Baseball', 'leaguemanager'), 'other' => __('Other', 'leaguemanager') );
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
	 * build home only query
	 *
	 * @param int $league_id
	 * @return string MySQL search query
	 */
	function buildHomeOnlyQuery($league_id)
	{
		global $wpdb;
		
		$queries = array();
		$teams = $wpdb->get_results( "SELECT `id` FROM {$wpdb->leaguemanager_teams} WHERE `league_id` = {$league_id} AND `home` = 1" );
		if ( $teams ) {
			foreach ( $teams AS $team )
				$queries[] = "`home_team` = {$team->id} OR `away_team` = {$team->id}";
		
			$query = " AND (".implode(" OR ", $queries).")";
			
			return $query;
		}
		
		return false;
		
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
	 * get Thumbnail path
	 *
	 * @param string $file
	 * @return string
	 */
	function getThumbnailPath( $file )
	{
		return $this->getImagePath( 'thumb.'.basename($file) );
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
		elseif ( $current && $match = $this->getMatches( "league_id = '".$this->league_id."' AND `season` = '".$this->season."' AND DATEDIFF(NOW(), `date`) < 0", 1 ) )
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
	 * get current season
	 *
	 * @param int $league_id
	 * @return mixed
	 */
	function getCurrentSeason( $league_id )
	{
		$options = $this->getOptions();
		if ( isset($_GET['season']) && !empty($_GET['season']) )
			return $this->getSeasonData($_GET['season'], $league_id);
		elseif ( isset($options['seasons'][$league_id]) )
			return end($options['seasons'][$league_id]);
 		else
			return false;
	}
	
	
	/**
	 * get season data
	 *
	 * @param mixed $season
	 * @param int $league_id
	 * @return array season data
	 */
	function getSeasonData( $season, $league_id )
	{
		$options = $this->getOptions();
		$key = array_search($season, $options['seasons'][$league_id]);
		return $options['seasons'][$league_id][$key];
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
		
		$league = $wpdb->get_results( "SELECT `title`, `id`, `active`, `point_rule`, `point_format`, `sport`, `num_match_days`, `team_ranking`, `project_id`, `mode` FROM {$wpdb->leaguemanager} WHERE id = '".$league_id."'" );

		// Disable bridge if project_id is not set
		if ( empty($league[0]->project_id) ) $this->bridge = false;

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
		
		$preferences = $wpdb->get_results( "SELECT `point_rule`, `point_format`, `sport`, `num_match_days`, `team_ranking`, `mode`, `project_id` FROM {$wpdb->leaguemanager} WHERE id = '".$league_id."'" );
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
		
		$teams_sql = $wpdb->get_results( "SELECT `title`, `website`, `coach`, `logo`, `home`, `points_plus`, `points_minus`, `points2_plus`, `points2_minus`, `add_points`, `done_matches`, `won_matches`, `draw_matches`, `lost_matches`, `diff`, `league_id`, `id`, `rank` FROM {$wpdb->leaguemanager_teams} WHERE $search ORDER BY `rank` ASC, `id` ASC" );
		
		if ( 'ARRAY' == $output ) {
			$teams = array();
			foreach ( $teams_sql AS $team ) {
				$teams[$team->id]['title'] = $team->title;
				$teams[$team->id]['rank'] = $team->rank;
				$teams[$team->id]['website'] = $team->website;
				$teams[$team->id]['coach'] = $team->coach;
				$teams[$team->id]['logo'] = $team->logo;
				$teams[$team->id]['home'] = $team->home;
				$teams[$team->id]['points'] = array( 'plus' => $team->points_plus, 'minus' => $team->points_minus );
				$teams[$team->id]['points2'] = array( 'plus' => $team->points2_plus, 'minus' => $team->points2_minus );
				$teams[$team->id]['add_points'] = $team->add_points;
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
	 * gets number of seasons for specific league
	 *
	 * @param int $league_id
	 * @return int
	 */
	function getNumSeasons( $league_id )
	{
		$options = $this->getOptions();
		if ( isset($options['seasons'][$league_id]) )
			return count($options['seasons'][$league_id]);
			
		return 0;
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
	 * @param int $league_id
	 * @return boolean
	 */
	function isGymnasticsLeague( $league_id )
	{
		$preferences = $this->getLeaguePreferences( $league_id );
		if ( 'gymnastics' == $preferences->sport )
			return true;
		
		return false;
	}
	

	/**
	 * check if league is ball game (has half time results)
	 *
	 * @param int $league_id
	 * @return boolean
	 */
	function isBallGameLeague( $league_id )
	{
		$preferences = $this->getLeaguePreferences( $league_id );
		if ( 'ballgame' == $preferences->sport || 'irish-gaelic-football' == $preferences->sport )
			return true;
			
		return false;
	}
	
	
	/**
	 * check if league is hockey league (played in thirds)
	 *
	 * @param int $league_id
	 * @return boolean
	 */
	function isHockeyLeague( $league_id )
	{
		$preferences = $this->getLeaguePreferenisBallGameLeagueces( $league_id );
		if ( 'hockey' == $preferences->sport )
			return true;
			
		return false;
	}
	
	
	/**
	 * check if league is basketball league (played in quarters)
	 *
	 * @param int $league_id
	 * @return boolean
	 */
	function isBasketballLeague( $league_id )
	{
		$preferences = $this->getLeaguePreferences( $league_id );
		if ( 'basketball' == $preferences->sport )
			return true;
			
		return false;
	}
	
	
	/**
	 * check if league is irish gaelic football
	 *
	 * @param int $league_id
	 * @return boolean
	 */
	function isIrishGaelicFootball( $league_id )
	{
		$preferences = $this->getLeaguePreferences( $league_id );
		if ( 'irish-gaelic-football' == $preferences->sport )
			return true;
			
		return false;
	}
	
	
	/**
	 * print match parts title depending on league type
	 *
	 * @param string $sport
	 * @return string
	 */
	function getMatchPartsTitle( $sport )
	{
		if ( 'gymnastics' == $sport )
			return __( 'Apparatus Points', 'leaguemanager' );
		elseif ( 'ballgame' == $sport )
			return __( 'Halftime', 'leaguemanager' );
		elseif ( 'hockey' == $sport )
			return __( 'Thirds', 'leaguemanager' );
		elseif ( 'basketball' == $sport )
			return __( 'Quarters', 'leaguemanager');
		elseif ( 'irish-gaelic-football' == $sport )
			return __( 'Points', 'leaguemanager' );
	}
	
	
	/**
	 * get number of match parts
	 * e.g 1 for ball game (halftime) and gymnastics (apparatus points), 3 for hockey, 4 for basketball
	 *
	 * @param string $sport
	 * @return int number of parts
	 */
	function getMatchParts( $sport )
	{
		if ( 'gymnastics' == $sport || 'ballgame' == $sport || 'irish-gaelic-football' == $sport)
			return 1;
		elseif ( 'hockey' == $sport )
			return 3;
		elseif ( 'basketball' == $sport )
			return 4;
			
		return false;
	}
	
	
	/**
	 * rank teams
	 *
	 * @param array $teams
	 * @param mixed $season
	 * @return array $teams ordered
	 */
	function rankTeams( $league_id, $season = false )
	{
		global $wpdb;
		$this->league_id = $league_id;
		$league = $this->getLeague( $league_id );

		$search = "`league_id` = '".$league_id."'";
		if ( !$season )
			$season = $this->getCurrentSeason($league_id);
		$search .= " AND `season` = '".$season."'";

		$teams = array();
		foreach ( $this->getTeams( $search ) AS $team ) {
			$team->diff = ( $team->diff > 0 ) ? '+'.$team->diff : $team->diff;
			$team->points = array( 'plus' => $team->points_plus, 'minus' => $team->points_minus );
			$team->points2 = array( 'plus' => $team->points2_plus, 'minus' => $team->points2_minus );
			$team->winPercent = ($team->won_matches/$team->done_matches) * 100

			$teams[] = $team;
			//$teams[] = array('id' => $team->id, 'home' => $team->home, 'title' => $team->title, 'short_title' => $team->short_title, 'website' => $team->website, 'logo' => $team->logo, 'done_matches' => $team->done_matches, 'won_matches' => $team->won_matches, 'draw_matches' => $team->draw_matches, 'lost_matches' => $team->lost_matches, 'points' => array('plus' => $points['plus'], 'minus' => $points['minus']), 'points2' => array('plus' => $points2['plus'], 'minus' => $points2['minus']), 'diff' => $d );
		}
		
		if ( $teams && $league->team_ranking == 'auto' ) {
			foreach ( $teams AS $key => $row ) {
				$points_1[$key] = $row->points['plus'];
				$points_2[$key] = $row->points2['plus'];
				$diff[$key] = $row->diff;
			}
		
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
	 * @param string $search (optional)
	 * @param int $limit (optional)
	 * @param string $order (optional)
	 * @param string $output (optional)
	 * @return array
	 */
	function getMatches( $search = false, $limit = false, $order = false, $output = 'OBJECT' )
	{
	 	global $wpdb;
		
	 	if ( !$order ) $order = "`date` ASC";
	 	
		$sql = "SELECT `home_team`, `away_team`, DATE_FORMAT(`date`, '%Y-%m-%d %H:%i') AS date, DATE_FORMAT(`date`, '%e') AS day, DATE_FORMAT(`date`, '%c') AS month, DATE_FORMAT(`date`, '%Y') AS year, DATE_FORMAT(`date`, '%H') AS `hour`, DATE_FORMAT(`date`, '%i') AS `minutes`, `match_day`, `location`, `league_id`, `home_points`, `away_points`, `overtime`, `penalty`, `winner_id`, `post_id`, `points2`, `id`, `goals`, `cards`, `exchanges` FROM {$wpdb->leaguemanager_matches}";
		if ( $search ) $sql .= " WHERE $search";
		$sql .= " ORDER BY $order";
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
		$matches[0]->hadPenalty = ( !empty($matches[0]->penalty) && !$this->isGymnasticsLeague($matches[0]->league_id) ) ? true : false;
		$matches[0]->hadOvertime = ( !empty($matches[0]->overtime) && !$this->isGymnasticsLeague($matches[0]->league_id) ) ? true : false;
		$matches[0]->points2 = maybe_unserialize($matches[0]->points2);
		$matches[0]->overtime = maybe_unserialize($matches[0]->overtime);
		$matches[0]->penalty = maybe_unserialize($matches[0]->penalty);
		$matches[0]->goals = explode("-new-",$matches[0]->goals);
		$matches[0]->cards = explode("-new-",$matches[0]->cards);
		$matches[0]->exchanges = explode("-new-",$matches[0]->exchanges);

		if ( !is_array($matches[0]->overtime) ) $matches[0]->overtime = array( 'home' => '', 'away' => '' );
		if ( !is_array($matches[0]->penalty) ) $matches[0]->penalty = array( 'home' => '', 'away' => '' );
							
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
	 * get card name
	 *
	 * @param string $type
	 * @return nice card name
	 */
	function getCardName( $type )
	{
		$cards = array( 'red' => __( 'Red', 'leaguemanager' ), 'yellow' => __( 'Yellow', 'leaguemanager' ), 'yellow-red' => __( 'Yellow/Red', 'leaguemanager' ) );
		return $cards[$type];
	}
	
	
	/**
	 * get name of final depending on number of teams
	 *
	 * @param string $key
	 * @return the name
	 */
	function getFinalName( $key )
	{
		if ( 'final' == $key )
			return __( 'Final', 'leaguemanager' );
		elseif ( 'semi' == $key )
			return __( 'Semi Final', 'leaguemanager' );
		elseif ( 'quarter' == $key )
			return __( 'Quarter Final', 'leaguemanager' );
		else {
			$tmp = explode("-", $key);
			return sprintf(__( 'Last-%d', 'leaguemanager'), $tmp[1]);
		}
	}
	
	
	/**
	 * get key of final depending on number of teams
	 *
	 * @param int $num_teams
	 * @return the key
	 */
	function getFinalKey( $num_teams )
	{
			if ( 2 == $num_teams )
				return 'final';
			elseif ( 4 == $num_teams )
				return 'semi';
			elseif ( 8 == $num_teams )
				return 'quarter';
			else
				return 'last-'.$num_teams;
	}
	
	
	/**
	 * get array of teams for finals
	 *
	 * @param int $num_matches
	 * @param boolean $start true if first round of finals
	 * @param string $round 'prev' | 'current'
	 * @return array of teams
	 */
	function getFinalTeams( $num_matches, $start, $output = 'OBJECT' )
	{
		// set matches of previous round
		$num_matches = $num_matches * 2; 
			
		$num_teams = $num_matches * 2;
		
		$num_advance = 2; // First and Second of each group qualify for finals
		$teams = array();
		if ( !$start ) {
			for ( $x = 1; $x <= $num_matches; $x++ ) {
				$key = $this->getFinalKey($num_teams);
				if( $output == 'ARRAY' ) {
					$teams['1-'.$key.'-'.$x] = "Winner ".$this->getFinalName($key)." ".$x;
				} else {
					$data = array( 'id' => '1-'.$key.'-'.$x, 'title' => "Winner ".$this->getFinalName($key)." ".$x );
					$teams[] = (object) $data;
				}
			}
		} else {
			for ( $group = 1; $group <= $this->getNumGroups( $this->league_id ); $group++ ) {
				for ( $a = 1; $a <= $num_advance; $a++ ) {
					if( $output == 'ARRAY' ) {
						$teams[$a.'-'.$group] = $a.'. Group '.$this->getGroupCharacter($group);
					} else {
						$data = array( 'id' => $a.'-'.$group, 'title' => $a.'. Group '.$this->getGroupCharacter($group) );
						$teams[] = (object) $data;
					}
				}
			}
		}
		return $teams;
	}
	
	
	/**
	 * get ascii text for given group
	 *
	 * @param int $group
	 * @param boolean $lc outputs lowercase character if true
	 * @return character
	 *
	 *  See http://www.asciitable.com/ for an ASCII Table
	 */
	function getGroupCharacter( $group, $lc = false )
	{
		$ascii = $lc ? $group + 96 : $group + 64;
		return chr($ascii);
	}
	
	
	/**
	 * get number of groups for championchip
	 *
	 * @param int $league_id
	 * @return int number of groups
	 */
	function getNumGroups( $league_id )
	{
		 return 8;
	}
}
?>
