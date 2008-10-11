<?php

class WP_LeagueManager
{
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
		
		$preferences = $wpdb->get_results( "SELECT `date_format`, `forwin`, `fordraw`, `forloss`, `home_teams_only`, `gymnastics` FROM {$wpdb->leaguemanager} WHERE id = '".$league_id."'" );
				
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
		
		$teams_sql = $wpdb->get_results( "SELECT `title`, `short_title`, `home`, `league_id`, `id` FROM {$wpdb->leaguemanager_teams} WHERE $search ORDER BY id ASC" );
				
		if ( 'ARRAY' == $output ) {
			$teams = array();
			foreach ( $teams_sql AS $team ) {
				$teams[$team->id]['title'] = $team->title;
				$teams[$team->id]['short_title'] = $team->short_title;
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
		
		$num_win = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `winner_id` = '".$team_id."'" );
		$num_draw = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `winner_id` = -1 AND `loser_id` = -1 AND (`home_team` = '".$team_id."' OR `away_team` = '".$team_id."')" );
		$num_lose = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `loser_id` = '".$team_id."'" );
		
		$points['plus'] = 0; $points['minus'] = 0;
		$points['plus'] = $num_win * $this->preferences->forwin + $num_draw * $this->preferences->fordraw + $num_lose * $league_settings->forloss;
		$points['minus'] = $num_draw * $this->preferences->fordraw + $num_lose * $this->preferences->forwin;
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
		if ( count($goals_home) > 0 )
		foreach ( $goals_home AS $home_goals ) {
			$goals['plus'] += $home_goals->home_points;
			$goals['minus'] += $home_goals->away_points;
		}
		
		if ( count($goals_away) > 0 )
		foreach ( $goals_away AS $away_goals ) {
			$goals['plus'] += $away_goals->away_points;
			$goals['minus'] += $away_goals->home_points;
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
		
		$num_matches = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE (`home_team` = '".$team_id."' OR `away_team` = '".$team_id."') AND `home_points` != 0 AND `away_points` != 0" );
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
		if ( 1 == $this->preferences->gymnastics )
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
			
			$teams[] = array('id' => $team->id, 'title' => $team->title, 'short_title' => $team->short_title, 'points' => array('plus' => $p['plus'], 'minus' => $p['minus']), 'apparatus_points' => array('plus' => $ap['plus'], 'minus' => $ap['minus']), 'goals' => array('plus' => $match_points['plus'], 'minus' => $match_points['minus']) );
		}
		
		foreach ( $teams AS $key => $row ) {
			$points[$key] = $row['points']['plus'];
			$apparatus_points[$key] = $row['apparatus_points']['plus'];
			$goals[$key] = $row['goals']['plus'];
		}
		
		if ( count($teams) > 0 ) {
			if ( $this->isGymnasticsLeague($league_id) )
				array_multisort($points, SORT_DESC, $apparatus_points, SORT_DESC, $teams);
			else
				array_multisort($points, SORT_DESC, $goals, SORT_DESC, $teams);
		}
		
		return $teams;
	}
	
	
	/**
	 * gets Competition from database
	 * 
	 * @param string $search
	 * @param string $date_format
	 * @return array
	 */
	function getMatches( $search, $date_format = '%e.%c' )
	{
	 	global $wpdb;
		$day_format = substr($date_format, 0, 2);
		$month_format = substr($date_format, 3, 2);
		$year_format = substr($date_format, 6, 2);
		
		$sql = "SELECT `home_team`, `away_team`, DATE_FORMAT(`date`, '$date_format') AS date, DATE_FORMAT(`date`, '$day_format') AS day, DATE_FORMAT(`date`, '$month_format') AS month, DATE_FORMAT(`date`, '$year_format') AS year, DATE_FORMAT(`date`, '%H') AS `hour`, DATE_FORMAT(`date`, '%i') AS `minutes`, `location`, `league_id`, `home_apparatus_points`, `away_apparatus_points`, `home_points`, `away_points`, `winner_id`, `id` FROM {$wpdb->leaguemanager_matches} WHERE $search ORDER BY `date` ASC";
		return $wpdb->get_results( $sql );
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
		return 'League added';
	}
		
		
	/**
	 * edit League
	 *
	 * @param string $title
	 * @param string $date_format
	 * @param int $forwin
	 * @param int $fordraw
	 * @param int $forloss
	 * @param int $home_teams_only 1 | 0
	 * @param int $gymnastics 1 | 0
	 * @param int $league_id
	 * @return string
	 */
	function editLeague( $title, $date_format, $forwin, $fordraw, $forloss, $home_teams_only, $gymnastics, $league_id )
	{
		global $wpdb;
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->leaguemanager} SET `title` = '%s', `date_format` = '%s', `forwin` = '%d', `fordraw` = '%d', `forloss` = '%d', `home_teams_only` = '%d', `gymnastics` = '%d' WHERE `id` = '%d'", $title, $date_format, $forwin, $fordraw, $forloss, $home_teams_only, $gymnastics, $league_id ) );
		return 'Settings saved';
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
			
		return 'Team added';
	}
		
		
	/**
	 * edit team
	 *
	 * @param int $team_id
	 * @param string $short_title
	 * @param string $title
	 * @param int $home 1 | 0
	 * @return string
	 */
	function editTeam( $team_id, $short_title, $title, $home )
	{
		global $wpdb;
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->leaguemanager_teams} SET `title` = '%s', `short_title` = '%s', `home` = '%d' WHERE `id` = %d", $title, $short_title, $home, $team_id ) );
		return 'Team updated'	;
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
		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager_matches} WHERE `home_team` = '".$team_id."' OR `away_team` = '".$team_id."'" );
		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager_teams} WHERE `id` = '".$team_id."'" );
		return;
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
		return 'Match added';
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
		return 'Match updated';
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
				$winner = $this->getMatchResult( $home_points[$match_id], $away_points[$match_id], $home_team[$match_id], $away_team[$match_id], 'winner' );
				$loser = $this->getMatchResult( $home_points[$match_id], $away_points[$match_id], $home_team[$match_id], $away_team[$match_id], 'loser' );
				
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `home_apparatus_points` = '%d', `away_apparatus_points` = '%d', `home_points` = '%d', `away_points` = '%d', `winner_id` = '%d', `loser_id` = '%d' WHERE `id` = %d", $home_apparatus_points[$match_id], $away_apparatus_points[$match_id], $home_points[$match_id], $away_points[$match_id], $winner, $loser, $match_id ) );
			}
		}
		return 'Updated League Results';
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
		} elseif ( 0 == $home_points && 0 == $away_points ) {
			$match['winner'] = 0;
			$match['loser'] = 0;
		} else {
			$match['winner'] = -1;
			$match['loser'] = -1;
		}
		
		return $match[$option];
	}
	
	
	/**
	 * inserts league standings into post content
	 *
	 * @param string $content
	 * @return string
	 */
	function printStandingsTable( $content )
	{
		$search = "/\[leaguestandings\s*=\s*(\w+)\]/i";
			
		preg_match_all( $search, $content , $matches );
			
		if ( is_array($matches[1]) ) {
			for ( $m = 0; $m < count($matches[0]); $m++ ) {
				$search = $matches[0][$m];
				if ( strlen($matches[1][$m]) ) {
					$league_id = $matches[1][$m];
				} else {
					continue;
				}
					
				$replace = $this->getStandingsTable( $league_id );
				$content = str_replace( $search, $replace, $content );
			}
		}
		
		return "</p>".$content."<p>";
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
		$class = ( $widget ) ? "leaguemanager_standings_widget" : "leaguemanager_standings";
		$secondary_points_title = ( $this->isGymnasticsLeague( $league_id ) ) ? 'Apparatus Points' : 'Goals';
			
		$out = '</p><table class="'.$class.'" summary="" title="'.__( 'Standings', 'leaguemanager' ).' '.$this->getLeagueTitle($league_id).'">';
		$out .= '<tr><th style="text-align: center;">#</th>';
		$out .= '<th>'.__( 'Club', 'leaguemanager' ).'</th>';
		$out .= ( !$widget ) ? '<th>'.__( 'Pld', 'leaguemanager' ).'</th>' : '';
		$out .= ( !$widget ) ? '<th>'.__( $secondary_points_title, 'leaguemanager' ).'</th>' : '';
		$out .= ( !$widget ) ? '<th>'.__( 'Diff', 'leaguemanager' ).'</th>' : '';
		$out .= '<th>'.__( 'Points', 'leaguemanager' ).'</th>
		   	</tr>';
				
		$teams = $this->rankTeams( $league_id );
		if ( count($teams) > 0 ) {
			$rank = 0;
			foreach( $teams AS $team ) {
				$rank++;
				$style = ( 1 == $team->home ) ? ' style="font-weight: bold;"' : '';
			 	$team_title = ( $widget ) ? $team['short_title'] : $team['title'];
			 	if ( $this->isGymnasticsLeague( $league_id ) )
			 		$secondary_points = $team['apparatus_points']['plus'].':'.$team['apparatus_points']['minus'];
				else
					$secondary_points = $team['goals']['plus'].':'.$team['goals']['minus'];
		
				$out .= "<tr$style>";
				$out .= "<td class='num'>$rank</td>";
				$out .= "<td>".$team_title."</td>";
				$out .= ( !$widget ) ? "<td class='num'>".$this->getNumDoneMatches( $team['id'] )."</td>" : '';
				if ( $this->isGymnasticsLeague( $league_id ) && !$widget )
					$out .= "<td class='num'>".$team['apparatus_points']['plus'].":".$team['apparatus_points']['minus']."</td><td class='num'>".$this->calculateDiff( $team['apparatus_points']['plus'], $team['apparatus_points']['minus'] )."</td>";
				elseif ( !$widget )
					$out .= "<td class='num'>".$team['goals']['plus'].":".$team['goals']['minus']."</td><td class='num'>".$this->calculateDiff( $team['goals']['plus'], $team['goals']['minus'] )."</td>";
				
				if ( $this->isGymnasticsLeague( $league_id ) && !$widget )
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
	 * inserts competitions table into post content
	 *
	 * @param string $content
	 * @return string
	 */
	function printMatchTable( $content )
	{
	 	$search = "/\[leaguematches\s*=\s*(\w+)\]/i";
		
		preg_match_all( $search, $content , $matches );
			
		if ( is_array($matches[1]) ) {
			for ( $m = 0; $m < count($matches[0]); $m++ ) {
				$search = $matches[0][$m];
				if ( strlen($matches[1][$m]) ) {
					$league_id = $matches[1][$m];
				} else {
					continue;
				}
				
				$replace = $this->getMatchTable( $league_id );
				$content = str_replace( $search, $replace, $content );
			}
		}
		return "</p>".$content."<p>";
	}
		 
		 
	/**
	 * gets competitions table for given league
	 *
	 * @param int $league_id
	 * @return string
	 */
	function getMatchTable( $league_id )
	{
		$leagues = $this->getLeagues( $league_id );
		$preferences = $this->getLeaguePreferences( $league_id );
		
		$teams = $this->getTeams( $league_id, 'ARRAY' );
		$matches = $this->getMatches( "league_id = '".$league_id."'", $preferences->date_format );
		
		$home_only = false;
		if ( 1 == $preferences->home_teams_only )
			$home_only = true;
			
		if ( $matches ) {
			$out = "</p><table class='leaguemanager_matches' summary='' title='".__( 'Match Plan', 'leaguemanager' )." ".$leagues['title']."'>";
			$out .= "<tr>
					<th>".__( 'Date', 'leaguemanager' )."</th>
					<th>".__( 'Match', 'leaguemanager' )."</th>
					<th>".__( 'Location', 'leaguemanager' )."</th>
					<th>".__( 'Begin', 'leaguemanager' )."</th>
				</tr>";
			foreach ( $matches AS $match ) {
				if ( !$home_only || ($home_only && (1 == $teams[$match->home_team]['home'] || 1 == $teams[$match->away_team]['home'])) ) {
					$location = ( '' == $match->location ) ? 'N/A' : $match->location;
					$start_time = ( '00' == $match->hour && '00' == $match->minutes ) ? 'N/A' : $match->hour.":".$match->minutes.' Uhr';
									
					$style = ( $this->isHomeMatch( $match->home_team, $teams ) ) ? ' style="font-weight: bold;"' : '';
							
					$out .= "<tr$style>";
					$out .= "<td>".$match->date."</td>";
					$out .= "<td>".$teams[$match->home_team]['title'].' - '. $teams[$match->away_team]['title']."</td>";
					$out .= "<td>".$location."</td>";
					$out .= "<td>".$start_time."</td>";
					$out .= "</tr>";
				}
			}
			$out .= "</table><p>";
		}
		
		return $out;
	}
		
		
	/**
	 * test if match is home match
	 *
	 * @param array $teams
	 * @return boolean
	 */
	function isHomeMatch( $home_team, $teams )
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
			'before_widget' => '<li id="league" class="widget '.get_class($this).'_'.__FUNCTION__.'">',
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
		if ( 1 == $match_display ) {
			$home_only = false;
			if ( 1 == $this->preferences->home_teams_only )
				$home_only = true;
				
			echo "<h3>".__( 'Matches', 'leaguemanager' )."</h3>";
			$matches = $this->getMatches( "league_id = '".$league_id."'" );
			$teams = $this->getTeams( $league_id, 'ARRAY' );
			
			if ( $matches ) {
				echo "<ul class='leaguemanager_matches'>";
				foreach ( $matches AS $match ) {
					if ( !$home_only || ($home_only && (1 == $teams[$match->home_team]['home'] || 1 == $teams[$match->away_team]['home'])) )
						echo "<li><strong>".$match->date."</strong> ".$teams[$match->home_team]['short_title']." - ".$teams[$match->away_team]['short_title']."</li>";
				}
				echo "</ul>";
			} else {
				_e( 'Nothing found', 'leaguemanager' );
			}
		}
		if ( 1 == $table_display ) {
			echo "<h3>".__('Standings', 'leaguemanager')."</h3>";
			echo $this->getStandingsTable( $league_id, true );
		}
		if ( $info_page_id AND '' != $info_page_id )
			echo "<li class='info'><a href='".get_permalink( $info_page_id )."'>".__( 'More Info', 'leaguemanager' )."</a></li>";
			
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
		echo '<p style="text-align: left;"><label for="info_'.$league_id.'" class="leaguemanager-widget">'.__( 'Page ID', 'leaguemanager' ).'<label><input type="text" size="3" name="info['.$league_id.']" id="info_'.$league_id.'" value="'.$options[$league_id]['info'].'" /></p>';
			
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
			wp_register_script( 'leaguemanager', LEAGUEMANAGER_URL.'/leaguemanager.js', false, '1.0' );
			wp_print_scripts( 'leaguemanager' );
		}
		echo "<!-- WP LeagueManager Plugin END -->\n\n";
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
		
		$create_leagues_sql = "CREATE TABLE {$wpdb->leaguemanager} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`title` varchar( 30 ) NOT NULL ,
						`forwin` tinyint( 4 ) NOT NULL default '2',
						`fordraw` tinyint( 4 ) NOT NULL default '1',
						`forloss` tinyint( 4 ) NOT NULL default '0',
						`date_format` varchar( 25 ) NOT NULL default '%e.%c',
						`home_teams_only` tinyint( 1 ) NOT NULL default '0',
						`gymnastics` tinyint( 1 ) NOT NULL default '0',
						`active` tinyint( 1 ) NOT NULL default '1' ,
						PRIMARY KEY ( `id` ))";
		maybe_create_table( $wpdb->leaguemanager, $create_leagues_sql );
			
		$create_teams_sql = "CREATE TABLE {$wpdb->leaguemanager_teams} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`title` varchar( 25 ) NOT NULL ,
						`short_title` varchar( 25 ) NOT NULL,
						`home` tinyint( 1 ) NOT NULL ,
						`league_id` int( 11 ) NOT NULL ,
						PRIMARY KEY ( `id` ))";
		maybe_create_table( $wpdb->leaguemanager_teams, $create_teams_sql );
		
		$create_matches_sql = "CREATE TABLE {$wpdb->leaguemanager_matches} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`date` datetime NOT NULL ,
						`home_team` int( 11 ) NOT NULL ,
						`away_team` int( 11 ) NOT NULL ,
						`location` varchar( 100 ) NOT NULL ,
						`league_id` int( 11 ) NOT NULL ,
						`home_apparatus_points` tinyint( 4 ) NOT NULL,
						`away_apparatus_points` tinyint( 4 ) NOT NULL,
						`home_points` tinyint( 4 ) NOT NULL,
						`away_points` tinyint( 4 ) NOT NULL,
						`winner_id` int( 11 ) NOT NULL,
						`loser_id` int( 11 ) NOT NULL,
						PRIMARY KEY ( `id` ))";
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
		
		if ( version_compare($wp_version, '2.7-hemorrhage', '<') ) {
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
