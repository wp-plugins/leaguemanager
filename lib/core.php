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
	 * array of leagues
	 *
	 * @var array
	 */
	var $leagues = array();
	

	/**
	 * data of certain league
	 *
	 * @var array
	 */
	var $league = array();


	/**
	 * ID of current league
	 *
	 * @var int
	 */
	var $league_id;

	
	/**
	 * current season
	 *
	 * @var mixed
	 */
	var $season;


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
	 * control variable if bridge is active
	 *
	 * @var boolean
	 */
	var $bridge = false;


	/**
	 * Initializes plugin
	 *
	 * @param boolean $bridge
	 * @return void
	 */
	function __construct( $bridge )
	{
		if (isset($_GET['league_id'])) {
			$this->setLeagueID( $_GET['league_id'] );
			$this->league = $this->getLeague($this->getLeagueID());
		}

		$this->loadOptions();
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
	 * get current league object
	 *
	 * @param none
	 * @return object
	 */
	function getCurrentLeague()
	{
		return $this->league;
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
		$types = array( 'other' => __('Other', 'leaguemanager') );
		$types = apply_filters('leaguemanager_sports', $types);
		asort($types);

		return $types;
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
		elseif ( $this->match_day )
			$match_day = $this->match_day;
		elseif ( $current && $match = $this->getMatches( "league_id = '".$this->league_id."' AND `season` = '".$this->season."' AND DATEDIFF(NOW(), `date`) < 0", 1 ) )
			$match_day = $match[0]->match_day;
		else
			$match_day = 1;

		return $match_day;
	}
	
	
	/**
	 * get current season
	 *
	 * @param object $league
	 * @param mixed $season
	 * @return array
	 */
	function getSeason($league, $season = false)
	{
		if ( isset($_GET['season']) && !empty($_GET['season']) )
			return $league->seasons[$_GET['season']];
		elseif ( $season )
			return $league->seasons[$season];
		elseif ( !empty($league->seasons) )
			return end($league->seasons);
 		else
			return false;
	}


	/**
	 * get leagues from database
	 *
	 * @param int $league_id (default: false)
	 * @param string $search
	 * @return array
	 */
	function getLeagues( $search = '' )
	{
		global $wpdb;
		
		$leagues = $wpdb->get_results( "SELECT `title`, `id`, `point_rule`, `point_format`, `sport`, `team_ranking`, `mode`, `seasons`, `project_id`, `custom` FROM {$wpdb->leaguemanager} ORDER BY id ASC" );

		$i = 0;
		foreach ( $leagues AS $league ) {
			$leagues[$i]->seasons = $league->seasons = maybe_unserialize($league->seasons);
			$leagues[$i]->point_rule = $league->point_rule = maybe_unserialize($league->point_rule);
			$league->custom = maybe_unserialize($league->custom);

			$leagues[$i] = (object)array_merge((array)$league,(array)$league->custom);
			unset($leagues[$i]->custom, $league->custom);

			$this->leagues[$league->id] = $league;
			$i++;
		}
		return $leagues;
	}
	
	
	/**
	 * get league
	 *
	 * @param mixed $league_id either ID of League or title
	 * @return league object
	 */
	function getLeague( $league_id )
	{
		global $wpdb;
		
		$league = $wpdb->get_results( "SELECT `title`, `id`, `point_rule`, `point_format`, `sport`, `team_ranking`, `seasons`, `project_id`, `mode`, `custom` FROM {$wpdb->leaguemanager} WHERE `id` = '".$league_id."' OR `title` = '".$league_id."'" );
		$league = $league[0];
		$league->seasons = maybe_unserialize($league->seasons);
		$league->point_rule = maybe_unserialize($league->point_rule);
		$league->custom = maybe_unserialize($league->custom);

		if(!is_array($league->seasons)) $league->seasons = array();

		// Disable bridge if project_id is not set
		if ( empty($league->project_id) ) $this->bridge = false;

		$this->league_id = $league->id;

		$league = (object)array_merge((array)$league,(array)$league->custom);
		unset($league->custom);

		$this->league = $league;
		return $league;
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
		
		$teamlist = $wpdb->get_results( "SELECT `title`, `website`, `coach`, `logo`, `home`, `points_plus`, `points_minus`, `points2_plus`, `points2_minus`, `add_points`, `done_matches`, `won_matches`, `draw_matches`, `lost_matches`, `diff`, `league_id`, `id`, `season`, `rank`, `custom` FROM {$wpdb->leaguemanager_teams} WHERE $search ORDER BY `rank` ASC, `id` ASC" );
		$teams = array(); $i = 0;
		foreach ( $teamlist AS $team ) {
			$team->custom = maybe_unserialize($team->custom);
			if ( 'ARRAY' == $output ) {
				$teams[$team->id]['title'] = $team->title;
				$teams[$team->id]['rank'] = $team->rank;
				$teams[$team->id]['season'] = $team->season;
				$teams[$team->id]['website'] = $team->website;
				$teams[$team->id]['coach'] = $team->coach;
				$teams[$team->id]['logo'] = $team->logo;
				$teams[$team->id]['home'] = $team->home;
				$teams[$team->id]['points'] = array( 'plus' => $team->points_plus, 'minus' => $team->points_minus );
				$teams[$team->id]['points2'] = array( 'plus' => $team->points2_plus, 'minus' => $team->points2_minus );
				$teams[$team->id]['add_points'] = $team->add_points;
				foreach ( (array)$team->custom AS $key => $value )
					$teams[$team->id][$key] = $value;
			} else {
				$teamlist[$i] = (object)array_merge((array)$team, (array)$team->custom);
			}

			unset($teamlist[$i]->custom, $team->custom);
			$i++;
		}

		if ( 'ARRAY' == $output )
			return $teams;

		return $teamlist;
	}
	
	
	/**
	 * get single team
	 *
	 * @param int $team_id
	 * @return object
	 */
	function getTeam( $team_id )
	{
		global $wpdb;

		$team = $wpdb->get_results( "SELECT `title`, `website`, `coach`, `logo`, `home`, `points_plus`, `points_minus`, `points2_plus`, `points2_minus`, `add_points`, `done_matches`, `won_matches`, `draw_matches`, `lost_matches`, `diff`, `league_id`, `id`, `season`, `rank`, `custom` FROM {$wpdb->leaguemanager_teams} WHERE `id` = '".$team_id."' ORDER BY `rank` ASC, `id` ASC" );
		$team = $team[0];

		$team->custom = maybe_unserialize($team->custom);
		$team = (object)array_merge((array)$team,(array)$team->custom);
		unset($team->custom);
		
		return $team;
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
	 * rank teams
	 *
	 * @param int $league_id
	 * @param mixed $season
	 * @return array $teams ordered
	 */
	function rankTeams( $league_id, $season = false )
	{
		global $wpdb;
		$league = $this->getLeague( $league_id );

		$search = "`league_id` = '".$league_id."'";
		if ( !$season ) {
			$season = $this->getSeason(&$league);
		}

		$season = is_array($season) ? $season['name'] : $season;
		$search .= " AND `season` = '".$season."'";

		$teams = array();
		foreach ( $this->getTeams( $search ) AS $team ) {
			$team->diff = ( $team->diff > 0 ) ? '+'.$team->diff : $team->diff;
			$team->points = array( 'plus' => $team->points_plus, 'minus' => $team->points_minus );
			$team->points2 = array( 'plus' => $team->points2_plus, 'minus' => $team->points2_minus );
			$team->winPercent = ($team->done_matches > 0) ? ($team->won_matches/$team->done_matches) * 100 : 0;

			$teams[] = $team;
		}
		
		if ( !empty($teams) && $league->team_ranking == 'auto' ) {
			if ( $league->sport != 'other' ) {
				$teams = apply_filters( 'rank_teams_'.$league->sport, &$teams );
			} else {
				foreach ( $teams AS $key => $row ) {
					$points[$key] = $row->points['plus'];
					$done[$key] = $row->done_matches;
				}
		
				array_multisort($points, SORT_DESC, $done, SORT_ASC, $teams);
			}
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

		$sql = "SELECT `home_team`, `away_team`, DATE_FORMAT(`date`, '%Y-%m-%d %H:%i') AS date, DATE_FORMAT(`date`, '%e') AS day, DATE_FORMAT(`date`, '%c') AS month, DATE_FORMAT(`date`, '%Y') AS year, DATE_FORMAT(`date`, '%H') AS `hour`, DATE_FORMAT(`date`, '%i') AS `minutes`, `match_day`, `location`, `league_id`, `home_points`, `away_points`, `winner_id`, `post_id`, `points2`, `season`, `id`, `custom` FROM {$wpdb->leaguemanager_matches}";
		if ( $search ) $sql .= " WHERE $search";
		$sql .= " ORDER BY $order";
		if ( $limit ) $sql .= " LIMIT 0,".$limit."";
		
		$matches = $wpdb->get_results( $sql, $output );

		$i = 0;
		foreach ( $matches AS $match ) {
			$match->custom = maybe_unserialize($match->custom);
			$match->points2 = maybe_unserialize($match->points2);
			$matches[$i] = (object)array_merge((array)$match, (array)$match->custom);
			unset($matches[$i]->custom);

			$i++;
		}
		return $matches;
	}
	
	
	/**
	 * get single match
	 *
	 * @param int $match_id
	 * @return object
	 */
	function getMatch( $match_id )
	{
		global $wpdb;

		$match = $wpdb->get_results( "SELECT `home_team`, `away_team`, DATE_FORMAT(`date`, '%Y-%m-%d %H:%i') AS date, DATE_FORMAT(`date`, '%e') AS day, DATE_FORMAT(`date`, '%c') AS month, DATE_FORMAT(`date`, '%Y') AS year, DATE_FORMAT(`date`, '%H') AS `hour`, DATE_FORMAT(`date`, '%i') AS `minutes`, `match_day`, `location`, `league_id`, `home_points`, `away_points`, `winner_id`, `post_id`, `points2`, `season`, `id`, `custom` FROM {$wpdb->leaguemanager_matches} WHERE `id` = {$match_id}" );
		$match = $match[0];

		$match->custom = maybe_unserialize($match->custom);
		$match->points2 = maybe_unserialize($match->points2);
		$match = (object)array_merge((array)$match, (array)$match->custom);

		return $match;
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
}
?>
