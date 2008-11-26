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
	 * getSupportedImageTypes() - gets supported file types
	 *
	 * @param none
	 * @return array
	 */
	function getSupportedImageTypes()
	{
		return $this->supported_image_types;
	}
	
	
	/**
	 * imageTypeisSupported() - checks if image type is supported
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
	 * getImageType() - gets image type of supplied image
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
	 * @param string | false $file
	 * @return string
	 */
	function getImagePath( $file = false )
	{
		if ( $file )
			return WP_CONTENT_DIR.'/leaguemanager/'.$file;
		else
			return WP_CONTENT_DIR.'/leaguemanager';
	}
	
	
	/**
	 * returns url of image directory
	 *
	 * @param string | false $file image file
	 * @return string
	 */
	function getImageUrl( $file = false )
	{
		if ( $file )
			return WP_CONTENT_URL.'/leaguemanager/'.$file;
		else
			return WP_CONTENT_URL.'/leaguemanager';
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
		
		$preferences = $wpdb->get_results( "SELECT `forwin`, `fordraw`, `forloss`, `match_calendar`, `type`, `colors`, `show_logo` FROM {$wpdb->leaguemanager} WHERE id = '".$league_id."'" );
		
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
	 * gets Competition from database
	 * 
	 * @param string $search
	 * @return array
	 */
	function getMatches( $search, $output = 'OBJECT' )
	{
	 	global $wpdb;
		
		$sql = "SELECT `home_team`, `away_team`, DATE_FORMAT(`date`, '%Y-%m-%d %H:%i') AS date, DATE_FORMAT(`date`, '%e') AS day, DATE_FORMAT(`date`, '%c') AS month, DATE_FORMAT(`date`, '%Y') AS year, DATE_FORMAT(`date`, '%H') AS `hour`, DATE_FORMAT(`date`, '%i') AS `minutes`, `location`, `league_id`, `home_apparatus_points`, `away_apparatus_points`, `home_points`, `away_points`, `winner_id`, `id` FROM {$wpdb->leaguemanager_matches} WHERE $search ORDER BY `date` ASC";
		return $wpdb->get_results( $sql, $output );
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
	 * @param int $match_calendar
	 * @param int $type
	 * @param array $colors
	 * @param int $show_logo
	 * @param int $league_id
	 * @return string
	 */
	function editLeague( $title, $forwin, $fordraw, $forloss, $match_calendar, $type, $colors, $show_logo, $league_id )
	{
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->leaguemanager} SET `title` = '%s', `forwin` = '%d', `fordraw` = '%d', `forloss` = '%d', `match_calendar` = '%d', `type` = '%d', `colors` = '%s', `show_logo` = '%d' WHERE `id` = '%d'", $title, $forwin, $fordraw, $forloss, $match_calendar, $type, maybe_serialize($colors), $show_logo, $league_id ) );
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
		$tail = '';
			
		$sql = "INSERT INTO {$wpdb->leaguemanager_teams} (title, short_title, home, league_id) VALUES ('%s', '%s', '%d', '%d')";
		$wpdb->query( $wpdb->prepare ( $sql, $title, $short_title, $home, $league_id ) );
		$team_id = $wpdb->insert_id;
			
		/*
		* Set Image if supplied
		*/
		if ( isset($_FILES['logo']['name']) AND '' != $_FILES['logo']['name'] )
			$tail = $this->uploadLogo($team_id, $_FILES['logo']['name'], $_FILES['logo']['size'], $_FILES['logo']['tmp_name']);
		
		return __('Team added','leaguemanager').' '.$tail;
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
		$tail = '';
		
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->leaguemanager_teams} SET `title` = '%s', `short_title` = '%s', `home` = '%d' WHERE `id` = %d", $title, $short_title, $home, $team_id ) );
			
		// Delete Image if options is checked
		if ($del_logo || $overwrite_image) {
			$wpdb->query("UPDATE {$wpdb->leaguemanager_teams} SET `logo` = '' WHERE `id` = {$team_id}");
			$this->delLogo( $image_file );
		}
		
		/*
		* Set Image if supplied
		*/
		if ( isset($_FILES['logo']['name']) AND '' != $_FILES['logo']['name'] )
			$tail = $this->uploadLogo($team_id, $_FILES['logo']['name'], $_FILES['logo']['size'], $_FILES['logo']['tmp_name'], $overwrite_image);
			
		return __('Team updated','leaguemanager').' '.$tail;
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
		
		$teams = $this->getTeams( "`id` = {$team_id}" );
		$this->delLogo( $teams[0]->logo );
			
		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager_matches} WHERE `home_team` = '".$team_id."' OR `away_team` = '".$team_id."'" );
		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager_teams} WHERE `id` = '".$team_id."'" );
		return;
	}


	/**
	 * uploadLogo() - set image path in database and upload image to server
	 *
	 * @param int  $team_id
	 * @param string $img_name
	 * @param int $img_size
	 * @param string $img_tmp_name
	 * @param string $uploaddir
	 * @param boolean $overwrite_image
	 * @return void | string
	 */
	function uploadLogo( $team_id, $img_name, $img_size, $img_tmp_name, $overwrite_image = false )
	{
		global $wpdb;
		
		if ( $this->ImageTypeIsSupported($img_name) ) {
			$uploaddir = $this->getImagePath();
				
			/*
			* Delete old images from server and clean database entry
			*/
			if ( $img_size > 0 ) {
				/*
				* Upload Image to Server
				*/
				$uploadfile = $uploaddir.'/'.basename($img_name);
				if ( file_exists($uploadfile) && !$overwrite_image ) {
					return __('File exists and is not uploaded.','leaguemanager');
				} else {
					if ( move_uploaded_file($img_tmp_name, $uploadfile) ) {
						if ( $teams = $this->getTeams( "`id` = {$team_id}" ) )
							if ( $teams[0]->logo != '' ) $this->delLogo($teams[0]->logo);
								$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `logo` = '%s' WHERE id = '%d'", basename($img_name), $team_id ) );
			
						$logo = new Thumbnail($uploadfile);
						$logo->resize( 30, 30 );
						$logo->save($uploadfile);
					} else
						return __('An upload error occured. Please try again.','leaguemanager');
				}
			}
		} else {
			return __('The file type is not supported. No Image was uploaded.','leaguemanager');
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
	 * @param string $location
	 * @param int $league_id
	 * @return string
	 */
	function addMatch( $date, $home_team, $away_team, $location, $league_id )
	{
	 	global $wpdb;
		$sql = "INSERT INTO {$wpdb->leaguemanager_matches} (date, home_team, away_team, location, league_id) VALUES ('%s', '%d', '%d', '%s', '%d')";
		$wpdb->query( $wpdb->prepare ( $sql, $date, $home_team, $away_team, $location, $league_id ) );
	}


	/**
	 * edit Match
	 *
	 * @param string $date
	 * @param int $home_team
	 * @param int $away_team
	 * @param string $location
	 * @param int $league_id
	 * @param int $cid
	 * @return string
	 */
	function editMatch( $date, $home_team, $away_team, $location, $league_id, $match_id )
	{
	 	global $wpdb;
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->leaguemanager_matches} SET `date` = '%s', `home_team` = '%d', `away_team` = '%d', `location` = '%s', `league_id` = '%d' WHERE `id` = %d", $date, $home_team, $away_team, $location, $league_id, $match_id ) );
		return __('Match updated','leaguemanager');
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
			foreach ( $matches AS $match_id ) {
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
			$search = "@\[leaguematches\s*=\s*(\w+)\]@i";
		
			if ( preg_match_all($search, $content , $matches) ) {
				if (is_array($matches)) {
					foreach($matches[1] AS $key => $v0) {
						$league_id = $v0;
						$search = $matches[0][$key];
						$replace = $this->getMatchTable( $league_id );
			
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
			
		$out = '</p><table class="leaguemanager" summary="" title="'.__( 'Standings', 'leaguemanager' ).' '.$this->getLeagueTitle($league_id).'">';
		$out .= '<tr><th class="num">&#160;</th>';
		if ( 1 == $this->preferences->show_logo )
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
			$rank = 0;
			foreach( $teams AS $team ) {
				$rank++;
				$class = ( 'alternate' == $class ) ? '' : 'alternate';
				$home_class = ( 1 == $team['home'] ) ? 'home' : '';
				
			 	$team_title = ( $widget ) ? $team['short_title'] : $team['title'];
			 	if ( $this->isGymnasticsLeague( $league_id ) )
			 		$secondary_points = $team['apparatus_points']['plus'].':'.$team['apparatus_points']['minus'];
				else
					$secondary_points = $team['goals']['plus'].':'.$team['goals']['minus'];
		
				$out .= "<tr class='$class'>";
				$out .= "<td class='rank'>$rank</td>";
				if ( 1 == $this->preferences->show_logo ) {
				$out .= '<td class="logo">';
					if ( $team['logo'] != '' )
					$out .= "<img src='".$this->getImageUrl($team['logo'])."' alt='".__('Logo','leaguemanager')."' title='".__('Logo','leaguemanager')." ".$team['title']."' />";
				$out .= '</td>';
				}
				$out .= "<td><span class='$home_class'>".$team_title."</span></td>";
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
		
		$out .= '</table><p>';
		
		return $out;
	}
		
		
	/**
	 * gets match table for given league
	 *
	 * @param int $league_id
	 * @return string
	 */
	function getMatchTable( $league_id )
	{
		$leagues = $this->getLeagues( $league_id );
		$preferences = $this->getLeaguePreferences( $league_id );
		
		$teams = $this->getTeams( $league_id, 'ARRAY' );
		$matches = $this->getMatches( "league_id = '".$league_id."'" );
		
		$home_only = false;
		if ( 2 == $preferences->match_calendar )
			$home_only = true;
			
		if ( $matches ) {
			$out = "</p><table class='leaguemanager' summary='' title='".__( 'Match Plan', 'leaguemanager' )." ".$leagues['title']."'>";
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
				
				if ( !$home_only || ($home_only && (1 == $teams[$match->home_team]['home'] || 1 == $teams[$match->away_team]['home'])) ) {
					$class = ( 'alternate' == $class ) ? '' : 'alternate';
					$location = ( '' == $match->location ) ? 'N/A' : $match->location;
					$start_time = ( '00' == $match->hour && '00' == $match->minutes ) ? 'N/A' : mysql2date(get_option('time_format'), $match->date);
									
					$matchclass = ( $this->isOwnHomeMatch( $match->home_team, $teams ) ) ? 'home' : '';
							
					$out .= "<tr class='$class'>";
					$out .= "<td class='match'>".mysql2date(get_option('date_format'), $match->date)." ".$start_time." ".$location."<br /><span class='$matchclass'>".$teams[$match->home_team]['title'].' - '. $teams[$match->away_team]['title']."</span></td>";
					$out .= "<td class='score' valign='bottom'>".$match->home_points.":".$match->away_points."</td>";
					if ( $this->isGymnasticsLeague( $league_id ) )
						$out .= "<td class='ap' valign='bottom'>".$match->home_apparatus_points.":".$match->away_apparatus_points."</td>";
					$out .= "</tr>";
				}
			}
			$out .= "</table><p>";
		}
		
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
 			$out .= "<div id='leaguemanager_crosstable' style='width=800px;height=100%;overfow:auto;display:none;'><div>";
		}
		
		$out .= "<table class='leaguemanager crosstable' summary='' title='".__( 'Crosstable', 'leaguemanager' )." ".$leagues['title']."'>";
		$out .= "<th colspan='2' style='text-align: center;'>".__( 'Club', 'leaguemanager' )."</th>";
		for ( $i = 1; $i <= count($teams); $i++ )
			$out .= "<th class='num'>".$i."</th>";
		$out .= "</tr>";
		foreach ( $teams AS $team ) {
			$rank++; $home_class = ( 1 == $team['home'] ) ? 'home' : '';
			
			$out .= "<tr>";
			$out .= "<th scope='row' class='rank'>".$rank."</th><td><span class='$home_class'>".$team['title']."</span></td>";
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
			$out .= "<p><a class='thickbox' href='#TB_inline?width=800&inlineId=leaguemanager_crosstable' title='".__( 'Crosstable', 'leaguemanager' )." ".$leagues['title']."'>".__( 'Crosstable', 'leaguemanager' )." ".$leagues['title']." (".__('Popup','leaguemanager').")</a></p>";
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
	 * test if match is home match
	 *
	 * @param array $teams
	 * @return boolean
	 */
	function isOwnHomeMatch( $home_team, $teams )
	{
		if ( 1 == $teams[$home_team]['home'] )
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

		$defaults = array(
			'before_widget' => '<li id="'.sanitize_title(get_class($this)).'" class="widget '.get_class($this).'_'.__FUNCTION__.'">',
			'after_widget' => '</li>',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => '</h2>',
			'match_display' => $options[$league_id]['match_display'],
			'table_display' => $options[$league_id]['table_display'],
			'info_page_id' => $options[$league_id]['info'],
		);
		$args = array_merge( $defaults, $args );
		extract( $args );
		
		$league = $this->getLeagues( $league_id );
		echo $before_widget . $before_title . $league['title'] . $after_title;
		
		echo "<div id='leaguemanager_widget'>";
		if ( 1 == $match_display ) {
			$home_only = false;
			if ( 2 == $this->preferences->match_calendar )
				$home_only = true;
				
			echo "<p class='title'>".__( 'Upcoming Matches', 'leaguemanager' )."</p>";
			$matches = $this->getMatches( "league_id = '".$league_id."' AND DATEDIFF(NOW(), `date`) < 0" );
			$teams = $this->getTeams( $league_id, 'ARRAY' );
			
			if ( $matches ) {
				echo "<ul class='matches'>";
				foreach ( $matches AS $match ) {
					if ( !$home_only || ($home_only && (1 == $teams[$match->home_team]['home'] || 1 == $teams[$match->away_team]['home'])) )
						echo "<li>".mysql2date(get_option('date_format'), $match->date)." ".$teams[$match->home_team]['short_title']." - ".$teams[$match->away_team]['short_title']."</li>";
				}
				echo "</ul>";
			} else {
				echo "<p>".__( 'Nothing found', 'leaguemanager' )."</p>";
			}
		}
		if ( 1 == $table_display ) {
			echo "<p class='title'>".__( 'Table', 'leaguemanager' )."</p>";
			echo $this->getStandingsTable( $league_id, true );
		}
		if ( $info_page_id AND '' != $info_page_id )
			echo "<p class='info'><a href='".get_permalink( $info_page_id )."'>".__( 'More Info', 'leaguemanager' )."</a></p>";
		
		echo "</div>";
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
		if ( $_POST['league-submit'] ) {
			$options[$widget_id] = $league_id;
			$options[$league_id]['table_display'] = $_POST['table_display'][$league_id];
			$options[$league_id]['match_display'] = $_POST['match_display'][$league_id];
			$options[$league_id]['info'] = $_POST['info'][$league_id];
			
			update_option( 'leaguemanager_widget', $options );
		}
		
		$checked = ( 1 == $options[$league_id]['match_display'] ) ? ' checked="checked"' : '';
		echo '<p style="text-align: left;"><label for="match_display_'.$league_id.'" class="leaguemanager-widget">'.__( 'Show Matches','leaguemanager' ).'</label>';
		echo '<input type="checkbox" name="match_display['.$league_id.']" id="match_display_'.$league_id.'" value="1"'.$checked.'>';
		echo '</p>';
			
		$checked = ( 1 == $options[$league_id]['table_display'] ) ? ' checked="checked"' : '';
		echo '<p style="text-align: left;"><label for="table_display_'.$league_id.'" class="leaguemanager-widget">'.__( 'Show Table', 'leaguemanager' ).'</label>';
		echo '<input type="checkbox" name="table_display['.$league_id.']" id="table_display_'.$league_id.'" value="1"'.$checked.'>';
		echo '</p>';
		echo '<p style="text-align: left;"><label for="info['.$league_id.']" class="leaguemanager-widget">'.__( 'Page' ).'<label>';
		wp_dropdown_pages(array('name' => 'info['.$league_id.']', 'selected' => $options[$league_id]['info']));
		echo '</p>';		

		echo '<input type="hidden" name="league-submit" id="league-submit" value="1" />';
	}
		 
		 
	/**
	 * adds code to Wordpress head
	 *
	 * @param none
	 */
	function addHeaderCode()
	{
		echo "\n\n<!-- WP LeagueManager Plugin Version ".LEAGUEMANAGER_VERSION." START -->\n";
		echo "<link rel='stylesheet' href='".LEAGUEMANAGER_URL."/style.css' type='text/css' />\n";
		if ( is_admin() AND isset( $_GET['page'] ) AND substr( $_GET['page'], 0, 13 ) == 'leaguemanager' ) {
			wp_register_script( 'leaguemanager', LEAGUEMANAGER_URL.'/leaguemanager.js', array('thickbox', 'colorpicker'), LEAGUEMANAGER_VERSION );
			wp_print_scripts( 'leaguemanager' );
			echo '<link rel="stylesheet" href="'.get_option( 'siteurl' ).'/wp-includes/js/thickbox/thickbox.css" type="text/css" media="screen" />';
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
	 * initialize widget
	 *
	 * @param none
	 */
	function initWidget()
	{
		if ( !function_exists('register_sidebar_widget') )
			return;
		
		foreach ( $this->getActiveLeagues() AS $league_id => $league ) {
			$name = __( 'League', 'leaguemanager' ) .' - '. $league['title'];
			register_sidebar_widget( $name , array( &$this, 'displayWidget' ) );
			register_widget_control( $name, array( &$this, 'widgetControl' ), '', '', array( 'league_id' => $league_id, 'widget_id' => sanitize_title($name) ) );
		}
	}
		 
		 
	/**
	 * initialize plugin
	 *
	 * @param none
	 */
	function init()
	{
		global $wpdb;
		include_once( ABSPATH.'/wp-admin/includes/upgrade.php' );
		
		$options = array();
		$options['version'] = LEAGUEMANAGER_VERSION;
		
		$old_options = get_option( 'leaguemanager' );
		if ( !isset($old_options['version']) || version_compare($old_options['version'], LEAGUEMANAGER_VERSION, '<') ) {
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
						`active` tinyint( 1 ) NOT NULL default '1' ,
						PRIMARY KEY ( `id` )) $charset_collate";
		maybe_create_table( $wpdb->leaguemanager, $create_leagues_sql );
			
		$create_teams_sql = "CREATE TABLE {$wpdb->leaguemanager_teams} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`title` varchar( 25 ) NOT NULL ,
						`short_title` varchar( 25 ) NOT NULL,
						`home` tinyint( 1 ) NOT NULL ,
						`league_id` int( 11 ) NOT NULL ,
						PRIMARY KEY ( `id` )) $charset_collate";
		maybe_create_table( $wpdb->leaguemanager_teams, $create_teams_sql );
		
		$create_matches_sql = "CREATE TABLE {$wpdb->leaguemanager_matches} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`date` datetime NOT NULL ,
						`home_team` int( 11 ) NOT NULL ,
						`away_team` int( 11 ) NOT NULL ,
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
			
		/*
		* Create directory for projects
		*/
		if ( !file_exists($this->getImagePath()) )
			mkdir( $this->getImagePath() );
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
		
		if ( !function_exists('register_uninstall_hook') ) {
			$plugin = basename(__FILE__, ".php") .'/plugin-hook.php';
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( function_exists( "deactivate_plugins" ) )
				deactivate_plugins( $plugin );
			else {
				$current = get_option('active_plugins');
				array_splice($current, array_search( $plugin, $current), 1 ); // Array-fu!
				update_option('active_plugins', $current);
				do_action('deactivate_' . trim( $plugin ));
			}
		}
	}
	
	
	/**
	 * adds menu to the admin interface
	 *
	 * @param none
	 */
	function addAdminMenu()
	{
 		add_management_page( __( 'Leagues', 'leaguemanager' ), __( 'Leagues', 'leaguemanager' ), 'manage_leagues', basename( __FILE__, ".php" ).'/manage-leagues.php' );
	}
}
?>
