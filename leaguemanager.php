<?php

class WP_LeagueManager
{
	/**
	 * supported image types
	 *
	 * @var array
	 */
	var $supported_image_types = array( "jpg", "jpeg", "png", "gif" );
	
	
	/**
	 * Array of months
	 *
	 * @param array
	 */
	var $months = array();

	
	/**
	 * Preferences of League
	 *
	 * @param array
	 */
	var $preferences = array();
	
	
	/**
	 * error handling
	 *
	 * @param boolean
	 */
	var $error = false;
	
	
	/**
	 * error message
	 *
	 * @param string
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
	 	global $wpdb;
	 	
		$wpdb->leaguemanager = $wpdb->prefix . 'leaguemanager_leagues';
		$wpdb->leaguemanager_teams = $wpdb->prefix . 'leaguemanager_teams';
		$wpdb->leaguemanager_matches = $wpdb->prefix . 'leaguemanager_matches';		

		$this->getMonths();
		return;
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
			$this->months[$month] = htmlentities( strftime( "%B", mktime( 0,0,0, $month, date("m"), date("Y") ) ) );
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
		foreach ( $this->months AS $key => $m ) {
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
	 * return error message
	 *
	 * @param none
	 */
	function getErrorMessage()
	{
		if ($this->error)
			return $this->message;
	}
	
	
	/**
	 * print formatted error message
	 *
	 * @param none
	 */
	function printErrorMessage()
	{
		echo "\n<div class='error'><p>".$this->getErrorMessage()."</p></div>";
	}
	
	
	/**
	 * gets supported file types
	 *
	 * @param none
	 * @return array
	 */
	function getSupportedImageTypes()
	{
		return $this->supported_image_types;
	}
	
	
	/**
	 * checks if image type is supported
	 *
	 * @param string $filename image file
	 * @return boolean
	 */
	function imageTypeIsSupported( $filename )
	{
		if ( in_array($this->getImageType($filename), $this->supported_image_types) )
			return true;
		else
			return false;
	}
	
	
	/**
	 * gets image type of supplied image
	 *
	 * @param string $filename image file
	 * @return string
	 */
	function getImageType( $filename )
	{
		$file_info = pathinfo($filename);
		return strtolower($file_info['extension']);
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
			$leagues_sql = $wpdb->get_results( "SELECT title, id FROM {$wpdb->leaguemanager} WHERE id = '".$league_id."' ORDER BY id ASC" );
			
			$leagues['title'] = $leagues_sql[0]->title;
			$this->preferences = $this->getLeaguePreferences( $league_id );
		} else {
			if ( $leagues_sql = $wpdb->get_results( "SELECT title, id FROM {$wpdb->leaguemanager} $search ORDER BY id ASC" ) ) {
				foreach( $leagues_sql AS $league ) {
					$leagues[$league->id]['title'] = $league->title;
				}
			}
		}
			
		return $leagues;
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
		global $wpdb;
		$league = $wpdb->get_results( "SELECT `title` FROM {$wpdb->leaguemanager} WHERE id = '".$league_id."'" );
		return ( $league[0]->title );
	}
	
	
	/**
	 * get all active leagues
	 *
	 * @param none
	 * @return array
	 */
	function getActiveLeagues()
	{
		return ( $this->getLeagues( false, 'WHERE active = 1' ) );
	}
	

	/**
	 * checks if league is active
	 *
	 * @param int $league_id
	 * @return boolean
	 */
	function leagueIsActive( $league_id )
	{
		global $wpdb;
		$league = $wpdb->get_results( "SELECT active FROM {$wpdb->leaguemanager} WHERE id = '".$league_id."'" );
		if ( 1 == $league[0]->active )
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
		
		$teams_sql = $wpdb->get_results( "SELECT `title`, `short_title`, `logo`, `home`, `league_id`, `id` FROM {$wpdb->leaguemanager_teams} WHERE $search ORDER BY id ASC" );
		
		if ( 'ARRAY' == $output ) {
			$teams = array();
			foreach ( $teams_sql AS $team ) {
				$teams[$team->id]['title'] = $team->title;
				$teams[$team->id]['short_title'] = $team->short_title;
				$teams[$team->id]['logo'] = $teams->logo;
				$teams[$team->id]['home'] = $team->home;
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
	 * calculate points for given team
	 *
	 * @param int $team_id
	 * @param int $league_id
	 * @param string $option
	 * @return int
	 */
	function calculatePoints( $team_id, $league_id, $option )
	{
		global $wpdb;
		
		$num_win = $this->getNumWonMatches( $team_id );
		$num_draw = $this->getNumDrawMatches( $team_id );
		$num_lost = $this->getNumLostMatches( $team_id );
		
		$points['plus'] = 0; $points['minus'] = 0;
		$points['plus'] = $num_win * $this->preferences->forwin + $num_draw * $this->preferences->fordraw + $num_lost * $league_settings->forloss;
		$points['minus'] = $num_draw * $this->preferences->fordraw + $num_lost * $this->preferences->forwin;
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
	 * calculate points differences
	 *
	 * @param int $plus
	 * @param int $minus
	 * @return int
	 */
	function calculateDiff( $plus, $minus )
	{
		$diff = $plus - $minus;
		if ( $diff >= 0 )
			$diff = '+'.$diff;
		
		return $diff;
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
	 * check if league is gymnastics league
	 *
	 * @param none
	 * @return boolean
	 */
	function isGymnasticsLeague( $league_id )
	{
		if ( 1 == $this->preferences->type )
			return true;
		else
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

		$teams = array();
		foreach ( $this->getTeams( "league_id = '".$league_id."'" ) AS $team ) {
			$p['plus'] = $this->calculatePoints( $team->id, $league_id, 'plus' );
			$p['minus'] = $this->calculatePoints( $team->id, $league_id, 'minus' );
			
			$ap['plus'] = $this->calculateApparatusPoints( $team->id, 'plus' );
			$ap['minus'] = $this->calculateApparatusPoints( $team->id, 'minus' );
			
			$match_points['plus'] = $this->calculateGoals( $team->id, 'plus' );
			$match_points['minus'] = $this->calculateGoals( $team->id, 'minus' );
			
			if ( $this->isGymnasticsLeague( $league_id ) )
				$d = $this->calculateDiff( $ap['plus'], $ap['minus'] );
			else
				$d = $this->calculateDiff( $match_points['plus'], $match_points['minus'] );
						
			$teams[] = array('id' => $team->id, 'home' => $team->home, 'title' => $team->title, 'short_title' => $team->short_title, 'logo' => $team->logo, 'points' => array('plus' => $p['plus'], 'minus' => $p['minus']), 'apparatus_points' => array('plus' => $ap['plus'], 'minus' => $ap['minus']), 'goals' => array('plus' => $match_points['plus'], 'minus' => $match_points['minus']), 'diff' => $d );
		}
		
		foreach ( $teams AS $key => $row ) {
			$points[$key] = $row['points']['plus'];
			$apparatus_points[$key] = $row['apparatus_points']['plus'];
			$diff[$key] = $row['diff'];
		}
		
		if ( count($teams) > 0 ) {
			if ( $this->isGymnasticsLeague($league_id) )
				array_multisort($points, SORT_DESC, $apparatus_points, SORT_DESC, $teams);
			else
				array_multisort($points, SORT_DESC, $diff, SORT_DESC, $teams);
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
		
		$sql = "SELECT `home_team`, `away_team`, DATE_FORMAT(`date`, '%Y-%m-%d %H:%i') AS date, DATE_FORMAT(`date`, '%e') AS day, DATE_FORMAT(`date`, '%c') AS month, DATE_FORMAT(`date`, '%Y') AS year, DATE_FORMAT(`date`, '%H') AS `hour`, DATE_FORMAT(`date`, '%i') AS `minutes`, `match_day`, `location`, `league_id`, `home_apparatus_points`, `away_apparatus_points`, `home_points`, `away_points`, `winner_id`, `id` FROM {$wpdb->leaguemanager_matches} WHERE $search ORDER BY `date` ASC";
			
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
	 * add new League
	 *
	 * @param string $title
	 * @return string
	 */
	function addLeague( $title )
	{
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare ( "INSERT INTO {$wpdb->leaguemanager} (title) VALUES ('%s')", $title ) );
		return __('League added', 'leaguemanager');
	}


	/**
	 * edit League
	 *
	 * @param string $title
	 * @param int $forwin
	 * @param int $fordraw
	 * @param int $forloss
	 * @param int $type
	 * @param int $num_match_days
	 * @param int $show_logo
	 * @param int $league_id
	 * @return string
	 */
	function editLeague( $title, $forwin, $fordraw, $forloss, $type, $num_match_days, $show_logo, $league_id )
	{
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->leaguemanager} SET `title` = '%s', `forwin` = '%d', `fordraw` = '%d', `forloss` = '%d', `type` = '%d', `num_match_days` = '%d', `show_logo` = '%d' WHERE `id` = '%d'", $title, $forwin, $fordraw, $forloss, $type, $num_match_days, $show_logo, $league_id ) );
		return __('Settings saved', 'leaguemanager');
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
	 * @return string
	 */
	function addTeam( $league_id, $short_title, $title, $home )
	{
		global $wpdb;
			
		$sql = "INSERT INTO {$wpdb->leaguemanager_teams} (title, short_title, home, league_id) VALUES ('%s', '%s', '%d', '%d')";
		$wpdb->query( $wpdb->prepare ( $sql, $title, $short_title, $home, $league_id ) );
		$team_id = $wpdb->insert_id;

		if ( isset($_FILES['logo']) && $_FILES['logo']['name'] != '' )
			$this->uploadLogo($team_id, $_FILES['logo']);
		
		if ( $this->error ) $this->printErrorMessage();
			
		return __('Team added','leaguemanager');
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
	 * @return string
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
		
		if ( $this->error ) $this->printErrorMessage();
			
		return __('Team updated','leaguemanager');
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
		return;
	}


	/**
	 * uploadLogo() - set image path in database and upload image to server
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
		
		$this->error = false;
		if ( $this->ImageTypeIsSupported($file['name']) ) {
			if ( $file['size'] > 0 ) {
				$new_file = $this->getImagePath().'/'.basename($file['name']);
				if ( file_exists($new_file) && !$overwrite ) {
					$this->error = true;
					$this->message = __('Logo exists and is not uploaded. Set the overwrite option if you want to replace it.','leaguemanager');
				} else {
					if ( move_uploaded_file($file['tmp_name'], $new_file) ) {
						if ( $team = $this->getTeam( $team_id ) )
							if ( $team->logo != '' ) $this->delLogo($team->logo);
							
						$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `logo` = '%s' WHERE id = '%d'", basename($file['name']), $team_id ) );
			
						$logo = new Thumbnail($new_file);
						$logo->resize( 30, 30 );
						$logo->save($new_file);
					} else {
						$this->error = true;
						$this->message = sprintf( __('The uploaded file could not be moved to %s.' ), $this->getImagePath() );
					}
				}
			}
		} else {
			$this->error = true;
			$this->message = __('The file type is not supported.','leaguemanager');
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
		@unlink( $this->getImagePath($image) );
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
	 * @param int|string $home_apparatus_points
	 * @param int|string $away_apparatus_points
	 * @return string
	 */
	function editMatch( $date, $home_team, $away_team, $match_day, $location, $league_id, $match_id, $home_points, $away_points, $home_apparatus_points, $away_apparatus_points )
	{
	 	global $wpdb;
		$home_points = ($home_points == '') ? 'NULL' : intval($home_points);
		$away_points = ($away_points == '') ? 'NULL' : intval($away_points);
		$home_apparatus_points = ($home_apparatus_points == '') ? 'NULL' : intval($home_apparatus_points);
		$away_apparatus_points = ($away_apparatus_points == '') ? 'NULL' : intval($away_apparatus_points);
		
		$winner = $this->getMatchResult( $home_points, $away_points, $home_team, $away_team, 'winner' );
		$loser = $this->getMatchResult( $home_points, $away_points, $home_team, $away_team, 'loser' );
			
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->leaguemanager_matches} SET `date` = '%s', `home_team` = '%d', `away_team` = '%d', `match_day` = '%d', `location` = '%s', `league_id` = '%d', `home_points` = ".$home_points.", `away_points` = ".$away_points.", `home_apparatus_points` = ".$home_apparatus_points.", `away_apparatus_points` = ".$away_apparatus_points.", `winner_id` = ".intval($winner).", `loser_id` = ".intval($loser)." WHERE `id` = %d", $date, $home_team, $away_team, $match_day, $location, $league_id, $match_id ) );
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
	 * @param array $match_id
	 * @param array $home_apparatus_points
	 * @param array $away_apparatus_points
	 * @param array $home_points
	 * @param array $away_points
	 * @return string
	 */
	function updateResults( $matches, $home_apparatus_points, $away_apparatus_points, $home_points, $away_points, $home_team, $away_team )
	{
		global $wpdb;
		if ( null != $matches ) {
			while (list($match_id) = each($matches)) {
				$home_points[$match_id] = ( '' == $home_points[$match_id] ) ? 'NULL' : intval($home_points[$match_id]);
				$away_points[$match_id] = ( '' == $away_points[$match_id] ) ? 'NULL' : intval($away_points[$match_id]);
				$home_apparatus_points[$match_id] = ( '' == $home_apparatus_points[$match_id] ) ? 'NULL' : intval($home_apparatus_points[$match_id]);
				$away_apparatus_points[$match_id] = ( '' == $away_apparatus_points[$match_id] ) ? 'NULL' : intval($away_apparatus_points[$match_id]);
				
				$winner = $this->getMatchResult( $home_points[$match_id], $away_points[$match_id], $home_team[$match_id], $away_team[$match_id], 'winner' );
				$loser = $this->getMatchResult( $home_points[$match_id], $away_points[$match_id], $home_team[$match_id], $away_team[$match_id], 'loser' );
				
				$wpdb->query( "UPDATE {$wpdb->leaguemanager_matches} SET `home_points` = ".$home_points[$match_id].", `away_points` = ".$away_points[$match_id].", `home_apparatus_points` = ".$home_apparatus_points[$match_id].", `away_apparatus_points` = ".$away_apparatus_points[$match_id].", `winner_id` = ".intval($winner).", `loser_id` = ".intval($loser)." WHERE `id` = {$match_id}" );
			}
		}
		return __('Updated League Results','leaguemanager');
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
	 * replace shortcodes with respective HTML in posts or pages
	 *
	 * @param string $content
	 * @return string
	 */
	function insert( $content )
	{
		if ( stristr( $content, '[leaguestandings' )) {
			$search = "@\[leaguestandings\s*=\s*(\w+)\]@i";
			
			if ( preg_match_all($search, $content , $matches) ) {
				if (is_array($matches)) {
					foreach($matches[1] AS $key => $v0) {
						$league_id = $v0;
						$search = $matches[0][$key];
						$replace = $this->getStandingsTable( $league_id );
			
						$content = str_replace($search, $replace, $content);
					}
				}
			}
		}
		
		if ( stristr ( $content, '[leaguematches' )) {
			$search = "@\[leaguematches\s*=\s*(\w+),(||\w+|)\]@i";
		
			if ( preg_match_all($search, $content , $matches) ) {
				if (is_array($matches)) {
					foreach($matches[1] AS $key => $v0) {
						$league_id = $v0;
						$search = $matches[0][$key];
						$replace = $this->getMatchTable( $league_id, $matches[2][$key] );
			
						$content = str_replace($search, $replace, $content);
					}
				}
			}
		}
		
		if ( stristr ( $content, '[leaguecrosstable' )) {
			$search = "@\[leaguecrosstable\s*=\s*(\w+),(|embed|popup|)\]@i";
			
			if ( preg_match_all($search, $content , $matches) ) {
				if ( is_array($matches) ) {
					foreach($matches[1] AS $key => $v0) {
						$league_id = $v0;
						$search = $matches[0][$key];
						$replace = $this->getCrossTable( $league_id, $matches[2][$key] );
						
						$content = str_replace( $search, $replace, $content );
					}
				}
			}
		}
		
		$content = str_replace('<p></p>', '', $content);
		return $content;
	}


	/**
	 * gets league standings table
	 *
	 * @param int $league_id
	 * @param boolean $widget
	 * @return string
	 */
	function getStandingsTable( $league_id, $widget = false )
	{
		global $wpdb;
		
		$this->preferences = $this->getLeaguePreferences( $league_id );
		$secondary_points_title = ( $this->isGymnasticsLeague( $league_id ) ) ? __('AP','leaguemanager') : __('Goals','leaguemanager');
		
		if ( !$widget ) $out .= '</p>';
		$out = '<table class="leaguemanager standingstable" summary="" title="'.__( 'Standings', 'leaguemanager' ).' '.$this->getLeagueTitle($league_id).'">';
		$out .= '<tr><th class="num">&#160;</th>';
		if ( 1 == $this->preferences->show_logo && !$widget )
			$out .= '<th class="logo">&#160;</th>';
		$out .= '<th>'.__( 'Club', 'leaguemanager' ).'</th>';
		$out .= ( !$widget ) ? '<th class="num">'.__( 'Pld', 'leaguemanager' ).'</th>' : '';
		$out .= ( !$widget ) ? '<th class="num">'.__( 'W','leaguemanager' ).'</th>' : '';
		$out .= ( !$widget ) ? '<th class="num">'.__( 'T','leaguemanager' ).'</th>' : '';
		$out .= ( !$widget ) ? '<th class="num">'.__( 'L','leaguemanager' ).'</th>' : '';
		$out .= ( !$widget ) ? '<th class="num">'.$secondary_points_title.'</th>' : '';
		$out .= ( !$widget ) ? '<th class="num">'.__( 'Diff', 'leaguemanager' ).'</th>' : '';
		$out .= '<th class="num">'.__( 'Pts', 'leaguemanager' ).'</th>
		   	</tr>';

		$teams = $this->rankTeams( $league_id );
		if ( count($teams) > 0 ) {
			$rank = 0; $class = array();
			foreach( $teams AS $team ) {
				$rank++;
				
				$class = ( in_array('alternate', $class) ) ? array() : array('alternate');
				// Add Divider class
				if ( $rank == 1 || $rank == 3 || count($teams)-$rank == 3 || count($teams)-$rank == 1)
					$class[] =  'divider';
				
			 	$team_title = ( $widget ) ? $team['short_title'] : $team['title'];
				if ( 1 == $team['home'] ) $team_title = '<strong>'.$team_title.'</strong>';
				
			 	if ( $this->isGymnasticsLeague( $league_id ) )
			 		$secondary_points = $team['apparatus_points']['plus'].':'.$team['apparatus_points']['minus'];
				else
					$secondary_points = $team['goals']['plus'].':'.$team['goals']['minus'];
		
				$out .= "<tr class='".implode(' ', $class)."'>";
				$out .= "<td class='rank'>$rank</td>";
				if ( 1 == $this->preferences->show_logo && !$widget) {
					$out .= '<td class="logo">';
					if ( $team['logo'] != '' )
					$out .= "<img src='".$this->getImageUrl($team['logo'])."' alt='".__('Logo','leaguemanager')."' title='".__('Logo','leaguemanager')." ".$team['title']."' />";
					$out .= '</td>';
				}
				$out .= "<td>".$team_title."</td>";
				$out .= ( !$widget ) ? "<td class='num'>".$this->getNumDoneMatches( $team['id'] )."</td>" : '';
				$out .= ( !$widget ) ? '<td class="num">'.$this->getNumWonMatches( $team['id'] ).'</td>' : '';
				$out .= ( !$widget ) ? '<td class="num">'.$this->getNumDrawMatches( $team['id'] ).'</td>' : '';
				$out .= ( !$widget ) ? '<td class="num">'.$this->getNumLostMatches( $team['id'] ).'</td>' : '';
				if ( $this->isGymnasticsLeague( $league_id ) && !$widget )
					$out .= "<td class='num'>".$team['apparatus_points']['plus'].":".$team['apparatus_points']['minus']."</td><td class='num'>".$team['diff']."</td>";
				elseif ( !$widget )
					$out .= "<td class='num'>".$team['goals']['plus'].":".$team['goals']['minus']."</td><td class='num'>".$team['diff']."</td>";
				
				if ( $this->isGymnasticsLeague( $league_id ) )
					$out .= "<td class='num'>".$team['points']['plus'].":".$team['points']['minus']."</td>";
				else
					$out .= "<td class='num'>".$team['points']['plus']."</td>";
				$out .= "</tr>";
			}
		}
		
		$out .= '</table>';
		if ( !$widget ) $out .= '<p>';
		
		return $out;
	}


	/**
	 * gets match table for given league
	 *
	 * @param int $league_id
	 * @param string $display
	 * @return string
	 */
	function getMatchTable( $league_id, $display )
	{
		global $wp_query;
		$this->league_id = $league_id;
		$leagues = $this->getLeagues( $league_id );
		$preferences = $this->getLeaguePreferences( $league_id );
		
		$all = false; $home_only = false;
		if ( $display == 'all' ) $all = true;
		elseif ( $display == 'home' ) $home_only = true;
		
		$page_obj = $wp_query->get_queried_object();
		$page_ID = $page_obj->ID;
		
		$teams = $this->getTeams( $league_id, 'ARRAY' );
			
		$search = "league_id = '".$league_id."'";
		if ( !$all && !$home_only )
			$search .= " AND match_day = '".$this->getMatchDay(true)."'";
		$matches = $this->getMatches( $search , false );
		
		$out = "</p>";
		
		if ( !$all && !$home_only ) {
			$out .= "<div style='float: left; margin-top: 1em;'><form method='get' action='".get_permalink($page_ID)."'><input type='hidden' name='page_id' value='".$page_ID."' /><select size='1' name='match_day'>";
			for ($i = 1; $i <= $preferences->num_match_days; $i++) {
				$selected = ($this->getMatchDay(true) == $i) ? ' selected="selected"' : '';
				$out .= "<option value='".$i."'".$selected.">".sprintf(__( '%d. Match Day', 'leaguemanager'), $i)."</option>";
			}
			$out .= "</select>&#160;<input type='submit' value='".__('Show')."' /></form></div><br style='clear: both;' />";
		}
			
		if ( $matches ) {
			$out .= "<table class='leaguemanager matchtable' summary='' title='".__( 'Match Plan', 'leaguemanager' )." ".$leagues['title']."'>";
			$out .= "<tr>
					<th class='match'>".__( 'Match', 'leaguemanager' )."</th>
					<th class='score'>".__( 'Score', 'leaguemanager' )."</th>";
					if ( $this->isGymnasticsLeague( $league_id ) )
					$out .= "<th class='ap'>".__( 'AP', 'leaguemanager' )."</th>";	
			$out .=	"</tr>";
			foreach ( $matches AS $match ) {
				$match->home_apparatus_points = ( NULL == $match->home_apparatus_points ) ? '-' : $match->home_apparatus_points;
				$match->away_apparatus_points = ( NULL == $match->away_apparatus_points ) ? '-' : $match->away_apparatus_points;
				$match->home_points = ( NULL == $match->home_points ) ? '-' : $match->home_points;
				$match->away_points = ( NULL == $match->away_points ) ? '-' : $match->away_points;
				
				if ( ( !$all && !$home_only ) || $all || ( $home_only && (1 == $teams[$match->home_team]['home'] || 1 == $teams[$match->away_team]['home'])) ) {
					$class = ( 'alternate' == $class ) ? '' : 'alternate';
					$start_time = ( '00' == $match->hour && '00' == $match->minutes ) ? '' : mysql2date(get_option('time_format'), $match->date);
										
					$match_title = $teams[$match->home_team]['title'].' - '. $teams[$match->away_team]['title'];
					if ( $this->isHomeTeamMatch( $match->home_team, $match->away_team, $teams ) ) $match_title = '<strong>'.$match_title.'</strong>';
		
					$out .= "<tr class='$class'>";
					$out .= "<td class='match'>".mysql2date(get_option('date_format'), $match->date)." ".$start_time." ".$match->location."<br />".$match_title."</td>";
					$out .= "<td class='score' valign='bottom'>".$match->home_points.":".$match->away_points."</td>";
					if ( $this->isGymnasticsLeague( $league_id ) )
						$out .= "<td class='ap' valign='bottom'>".$match->home_apparatus_points.":".$match->away_apparatus_points."</td>";
					$out .= "</tr>";
				}
			}
			$out .= "</table>";
		}	
		$out .= '<p>';
		
		return $out;
	}
	

	/**
	 * get cross-table with home team down the left and away team across the top
	 *
	 * @param int $league_id
	 * @return string
	 */
	function getCrossTable( $league_id, $mode )
	{
		$leagues = $this->getLeagues( $league_id );
		$teams = $this->rankTeams( $league_id );
		$rank = 0;
		
		$out = "</p>";
		
		// Thickbox Popup
		if ( 'popup' == $mode ) {
 			$out .= "<div id='leaguemanager_crosstable' style='overfow:auto;display:none;'><div>";
		}
		
		$out .= "<table class='leaguemanager crosstable' summary='' title='".__( 'Crosstable', 'leaguemanager' )." ".$leagues['title']."'>";
		$out .= "<tr><th colspan='2' style='text-align: center;'>".__( 'Club', 'leaguemanager' )."</th>";
		for ( $i = 1; $i <= count($teams); $i++ )
			$out .= "<th class='num'>".$i."</th>";
		$out .= "</tr>";
		foreach ( $teams AS $team ) {
			$rank++;
			if ( 1 == $team['home'] ) $team['title'] = '<strong>'.$team['title'].'</strong>';
			
			$out .= "<tr>";
			$out .= "<th scope='row' class='rank'>".$rank."</th><td>".$team['title']."</td>";
			for ( $i = 1; $i <= count($teams); $i++ ) {
				if ( ($rank == $i) )
					$out .= "<td class='num'>-</td>";
				else
					$out .= $this->getScore($team['id'], $teams[$i-1]['id']);
			}
			$out .= "</tr>";
		}
		$out .= "</table>";
	
		// Thickbox Popup End
		if ( 'popup' == $mode ) {
			$out .= "</div></div>";
			$out .= "<p><a class='thickbox' href='#TB_inline&width=800&height=500&inlineId=leaguemanager_crosstable' title='".__( 'Crosstable', 'leaguemanager' )." ".$leagues['title']."'>".__( 'Crosstable', 'leaguemanager' )." ".$leagues['title']." (".__('Popup','leaguemanager').")</a></p>";
		}
		
		$out .= "<p>";
	
		return $out;
	}
	

	/**
	 * get match and score for teams
	 *
	 * @param int $curr_team_id
	 * @param int $opponent_id
	 * @return string
	 */
	function getScore($curr_team_id, $opponent_id)
	{
		global $wpdb;

		$match = $this->getMatches("(`home_team` = $curr_team_id AND `away_team` = $opponent_id) OR (`home_team` = $opponent_id AND `away_team` = $curr_team_id)");
		$out = "<td class='num'>-:-</td>";
		if ( $match ) {
			// match at home
			if ( NULL == $match[0]->home_points && NULL == $match[0]->away_points )
				$out = "<td class='num'>-:-</td>";
			elseif ( $curr_team_id == $match[0]->home_team )
				$out = "<td class='num'>".$match[0]->home_points.":".$match[0]->away_points."</td>";
			// match away
			elseif ( $opponent_id == $match[0]->home_team )
				$out = "<td class='num'>".$match[0]->away_points.":".$match[0]->home_points."</td>";
			
		}

		return $out;
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
	 * displays widget
	 *
	 * @param $args
	 *
	 */
	function displayWidget( $args )
	{
		$options = get_option( 'leaguemanager_widget' );
		$widget_id = $args['widget_id'];
		$league_id = $options[$widget_id];
		$options = $options[$league_id];
		
		$defaults = array(
			'before_widget' => '<li id="'.sanitize_title(get_class($this)).'" class="widget '.get_class($this).'_'.__FUNCTION__.'">',
			'after_widget' => '</li>',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => '</h2>',
			'match_display' => $options['match_display'],
			'table_display' => $options['table_display'],
			'info_page_id' => $options['info'],
			'date_format' => $options['date_format'],
			'time_format' => $options['time_format'],
			'match_show' => $options['match_show'],
		);
		$args = array_merge( $defaults, $args );
		extract( $args );
		
		$league = $this->getLeagues( $league_id );
		echo $before_widget . $before_title . $league['title'] . $after_title;
		
		echo "<ul class='leaguemanager_widget'>";
		if ( $match_display >= 0 ) {
			$home_only = ( 2 == $match_show ) ? true : false;
			
			echo "<li><span class='title'>".__( 'Upcoming Matches', 'leaguemanager' )."</span>";
			
			$match_limit = ( 0 == $match_display ) ? false : $match_display;
			$matches = $this->getMatches( "league_id = '".$league_id."' AND DATEDIFF(NOW(), `date`) < 0", $match_limit );
			$teams = $this->getTeams( $league_id, 'ARRAY' );
			
			if ( $matches ) {
				echo "<ul class='matches'>";
				$match = array();
				foreach ( $matches AS $m ) {
					if ( !$home_only || ($home_only && (1 == $teams[$m->home_team]['home'] || 1 == $teams[$m->away_team]['home'])) ) {
						$start_time = ( $time_format == '' || ('00' == $m->hour && '00' == $m->minutes) ) ? '' : "(".mysql2date($time_format, $m->date).")";
						$date = mysql2date($date_format, $m->date);
						$match[$date][] = "<li>".$teams[$m->home_team]['short_title'] . "&#8211;" . $teams[$m->away_team]['short_title']." ".$start_time."</li>";
					}
				}
				foreach ( $match AS $date => $m )
					echo "<li><span class='title'>".$date."</span><ul>".implode("", $m)."</ul></li>";
				echo "</ul>";
			} else {
				echo "<p>".__( 'Nothing found', 'leaguemanager' )."</p>";
			}
			echo "</li>";
		}
		if ( 1 == $table_display ) {
			echo "<li><span class='title'>".__( 'Table', 'leaguemanager' )."</span>";
			echo $this->getStandingsTable( $league_id, true );
			echo "</li>";
		}
		if ( $info_page_id AND '' != $info_page_id )
			echo "<li class='info'><a href='".get_permalink( $info_page_id )."'>".__( 'More Info', 'leaguemanager' )."</a></li>";
		
		echo "</ul>";
		echo $after_widget;
	}


	/**
	 * widget control panel
	 *
	 * @param none
	 */
	function widgetControl( $args )
	{
		extract( $args );
		
		$options = get_option( 'leaguemanager_widget' );
		$options[$widget_id] = $league_id;
		update_option( 'leaguemanager_widget', $options );
		
		echo '<p>'.sprintf(__( "The Widget Settings are controlled via the <a href='%s'>League Settings</a>", 'leaguemanager'), 'admin.php?page=leaguemanager/settings.php&league_id='.$league_id).'</p>';
		
		/*
	 	
		if ( $_POST['league-submit'] ) {
			$options[$widget_id] = $league_id;
			$options[$league_id]['table_display'] = $_POST['table_display'][$league_id];
			$options[$league_id]['match_display'] = $_POST['match_display'][$league_id];
			$options[$league_id]['info'] = $_POST['info'][$league_id];
			
			update_option( 'leaguemanager_widget', $options );
		}
		
		echo '<div class="leaguemanager_widget_control">';
		echo '<p><label for="match_display_'.$league_id.'">'.__( 'Matches','leaguemanager' ).'</label>&#160;<select size="1" name="match_display['.$league_id.']" id="match_display_'.$league_id.'">';
		$selected[0] = ( -1 == $options[$league_id]['match_display'] ) ? ' selected="selected"' : '';
		echo '<option value="-1"'.$selected[0].'>'.__('Do not show', 'leaguemanager').'</option>';
		$selected[1] = ( 0 == $options[$league_id]['match_display'] ) ? ' selected="selected"' : '';
		echo '<option value="0"'.$selected[1].'>'.__('All', 'leaguemanager').'</option>';
		for($i = 1; $i <= 10;$i++) {
			$selected = ( $i == $options[$league_id]['match_display'] ) ? ' selected="selected"' : '';
			echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
		}
		echo '</select>';
		echo '</p>';
			
		$checked = ( 1 == $options[$league_id]['table_display'] ) ? ' checked="checked"' : '';
		echo '<p><input type="checkbox" name="table_display['.$league_id.']" id="table_display_'.$league_id.'" value="1"'.$checked.'>&#160;<label for="table_display_'.$league_id.'">'.__( 'Show Table', 'leaguemanager' ).'</label></p>';

		echo '<p><label for="info['.$league_id.']">'.__( 'Page', 'leaguemanager' ).'<label>&#160;'.wp_dropdown_pages(array('name' => 'info['.$league_id.']', 'selected' => $options[$league_id]['info'], 'echo' => 0)).'</p>';

		echo '<input type="hidden" name="league-submit" id="league-submit" value="1" />';
		
		echo '</div>';
	*/
	}


	/**
	 * adds code to Wordpress head
	 *
	 * @param none
	 */
	function addHeaderCode($show_all=false)
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

		if ( is_admin() AND (isset( $_GET['page'] ) AND substr( $_GET['page'], 0, 13 ) == 'leaguemanager' || $_GET['page'] == 'leaguemanager') || $show_all ) {
			wp_register_script( 'leaguemanager', LEAGUEMANAGER_URL.'/leaguemanager.js', array('thickbox', 'colorpicker', 'sack' ), LEAGUEMANAGER_VERSION );
			wp_print_scripts( 'leaguemanager' );
			echo '<link rel="stylesheet" href="'.get_option( 'siteurl' ).'/wp-includes/js/thickbox/thickbox.css" type="text/css" media="screen" />';
			
			?>
			<!-- No Ajax at the moment
			<script type='text/javascript'>
			//<![CDATA[
				   LeagueManagerAjaxL10n = {
				   blogUrl: "<?php bloginfo( 'wpurl' ); ?>", pluginPath: "<?php echo LEAGUEMANAGER_PATH; ?>", pluginUrl: "<?php echo LEAGUEMANAGER_URL; ?>", requestUrl: "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php", imgUrl: "<?php echo LEAGUEMANAGER_URL; ?>/images", Edit: "<?php _e("Edit"); ?>", Post: "<?php _e("Post"); ?>", Save: "<?php _e("Save"); ?>", Cancel: "<?php _e("Cancel"); ?>", pleaseWait: "<?php _e("Please wait..."); ?>", Revisions: "<?php _e("Page Revisions"); ?>", Time: "<?php _e("Insert time"); ?>"
				   }
			//]]>
			  </script>
			  -->
			<?php
		}
		
		echo "<!-- WP LeagueManager Plugin END -->\n\n";
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
		$plugin_array['LeagueManager'] = LEAGUEMANAGER_URL.'/tinymce/editor_plugin.js';
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
			echo '<div id="message" class="updated fade"><p><strong>'.__( 'Settings saved', 'leaguemanager' ).'</strong></p></div>';
		}
		
		
		echo "\n<form action='' method='post' name='colors'>";
		wp_nonce_field( 'leaguemanager_manage-global-league-options' );
		echo "\n<div class='wrap'>";
		echo "\n\t<h2>".__( 'Leaguemanager Global Settings', 'leaguemanager' )."</h2>";
		echo "\n\t<h3>".__( 'Color Scheme', 'leaguemanager' )."</h3>";
		echo "\n\t<table class='form-table'>";
		echo "\n\t<tr valign='top'>";
		echo "\n\t\t<th scope='row'><label for='color_headers'>".__( 'Table Headers', 'leaguemanager' )."</label></th><td><input type='text' name='color_headers' id='color_headers' value='".$options['colors']['headers']."' size='10' /><a href='#' class='colorpicker' onClick='cp.select(document.forms[\"colors\"].color_headers,\"pick_color_headers\"); return false;' name='pick_color_headers' id='pick_color_headers'>&#160;&#160;&#160;</a></td>";
		echo "\n\t</tr>";
		echo "\n\t<tr valign='top'>";
		echo "\n\t<th scope='row'><label for='color_rows'>".__( 'Table Rows', 'leaguemanager' )."</label></th>";
		echo "\n\t\t<td>";
		echo "\n\t\t\t<p class='table_rows'><input type='text' name='color_rows_alt' id='color_rows_alt' value='".$options['colors']['rows'][0]."' size='10' /><a href='#' class='colorpicker' onClick='cp.select(document.forms[\"colors\"].color_rows_alt,\"pick_color_rows_alt\"); return false;' name='pick_color_rows_alt' id='pick_color_rows_alt'>&#160;&#160;&#160;</a></p>";
		echo "\n\t\t\t<p class='table_rows'><input type='text' name='color_rows' id='color_rows' value='".$options['colors']['rows'][1]."' size='10' /><a href='#' class='colorpicker' onClick='cp.select(document.forms[\"colors\"].color_rows,\"pick_color_rows\"); return false;' name='pick_color_rows' id='pick_color_rows'>&#160;&#160;&#160;</a></p>";
		echo "\n\t\t</td>";
		echo "\n\t</tr>";
		echo "\n\t</table>";
		echo "\n<input type='hidden' name='page_options' value='color_headers,color_rows,color_rows_alt' />";
		echo "\n<p class='submit'><input type='submit' name='updateLeagueManager' value='".__( 'Save Preferences', 'leaguemanager' )." &raquo;' class='button' /></p>";
		echo "\n</form>";
	
		echo "<script language='javascript'>
			syncColor(\"pick_color_headers\", \"color_headers\", document.getElementById(\"color_headers\").value);
			syncColor(\"pick_color_rows\", \"color_rows\", document.getElementById(\"color_rows\").value);
			syncColor(\"pick_color_rows_alt\", \"color_rows_alt\", document.getElementById(\"color_rows_alt\").value);
		</script>";
	}
	
	
	/**
	 * initialize widget
	 *
	 * @param none
	 */
	function activateWidget()
	{
		if ( !function_exists('register_sidebar_widget') )
			return;
		$options = get_option( 'leaguemanager_widget' );
		foreach ( $this->getActiveLeagues() AS $league_id => $league ) {
			$name = __( 'League', 'leaguemanager' ) .' - '. $league['title'];
			$widget_id = sanitize_title($name);
			$widget_ops = array('classname' => 'widget_leaguemanager', 'description' => __('League results and upcoming matches at a glance', 'leaguemanager') );
			wp_register_sidebar_widget( sanitize_title($name), $name , array( &$this, 'displayWidget' ), $widget_ops );
			wp_register_widget_control( sanitize_title($name), $name, array( &$this, 'widgetControl' ), array('width' => 250, 'height' => 200), array( 'league_id' => $league_id, 'widget_id' => $widget_id ) );
		
			$options[$widget_id] = $league_id;
		}
		update_option( 'leaguemanager_widget', $options );
	}


	/**
	 * initialize plugin
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
			require_once( LEAGUEMANAGER_PATH . '/leaguemanager-upgrade.php' );
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
	
	
	/**
	 * adds menu to the admin interface
	 *
	 * @param none
	 */
	function addAdminMenu()
	{
		$plugin = 'leaguemanager/plugin-hook.php';
		$page = 'admin.php?page=leaguemanager/manage-leagues.php';
		add_menu_page( __('League','leaguemanager'), __('League','leaguemanager'), 'manage_leagues', $page, '', LEAGUEMANAGER_URL.'/menu.png' );
		add_submenu_page($page, __('Overview', 'leaguemanager'), __('Overview','leaguemanager'),'manage_leagues', $page,'');
		add_submenu_page($page, __('Settings', 'leaguemanager'), __('Settings','leaguemanager'),'manage_leagues', 'leaguemanager', array( $this, 'displayOptionsPage' ));
		
		add_filter( 'plugin_action_links_' . $plugin, array( &$this, 'pluginActions' ) );
	}
	
	
		
	/**
	 * pluginActions() - display link to settings page in plugin table
	 *
	 * @param array $links array of action links
	 * @return void
	 */
	function pluginActions( $links )
	{
		$settings_link = '<a href="admin.php?page=leaguemanager">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link );
	
		return $links;
	}
}
?>
