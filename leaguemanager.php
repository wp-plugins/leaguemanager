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
	 * array to store leagues
	 *
	 * @var array
	 */
	var $leagues = array();
	
	
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
		$wpdb->leaguemanager_leaguemeta = $wpdb->prefix . 'leaguemanager_leaguemeta';
		$wpdb->leaguemanager_teammeta = $wpdb->prefix . 'leaguemanager_teammeta';
		$wpdb->leaguemanager_competitions = $wpdb->prefix . 'leaguemanager_competitions';

		$this->get_months();
		return;
	}
	function WP_LeagueManager()
	{
		$this->__construct();
	}
	
		
	/**
	 * gets supported column types
	 *
	 * @param none
	 * @return array
	 */
	function get_col_types()
	{
		return array( 1 => 'Points', 2 => 'Text' );
	}
	
	
	/**
	 * get months
	 *
	 * @param none
	 * @return array
	 */
	function get_months()
	{
		$locale = get_locale();
		setlocale(LC_ALL, $locale);
		for ( $month = 1; $month <= 12; $month++ ) 
			$this->months[$month] = htmlentities( strftime( "%B", mktime( 0,0,0, $month, date("m"), date("Y") ) ) );
	}
	
	
	/**
	 * gets leagues from database and returns them in array
	 *
	 * @param int $league_id (default: false)
	 * @return array
	 */
	function get_leagues( $league_id = false, $search = '' )
	{
		global $wpdb;
		
		$leagues = array();
		if ( $league_id ) {
			$leagues_sql = $wpdb->get_results( "SELECT title, id FROM {$wpdb->leaguemanager} WHERE id = '".$league_id."' ORDER BY id ASC" );
			$team =  $this->get_teams( 'home = 1 AND league_id = "'.$leagues_sql[0]->id.'"' );
			
			$leagues['title'] = $leagues_sql[0]->title;
			$leagues['home_team']['title'] = $team[0]->title;
			$leagues['home_team']['short_title'] = $team[0]->short_title;
		} else {
			if ( $leagues_sql = $wpdb->get_results( "SELECT title, id FROM {$wpdb->leaguemanager} $search ORDER BY id ASC" ) ) {
				foreach( $leagues_sql AS $league ) {
					$team = $this->get_teams( 'home = 1 AND league_id = "'.$league->id.'"' );
					
					$leagues[$league->id]['title'] = $league->title;
					$leagues[$league->id]['home_team']['title'] = $team[0]->title;
					$leagues[$league->id]['home_team']['short_title'] = $team[0]->short_title;
				}
			}
		}
			
		return $leagues;
	}
	
	
	/**
	 * gets league title
	 *
	 * @param int $league_id
	 * @return string
	 */
	function get_league_title( $league_id )
	{
		global $wpdb;
		$league = $wpdb->get_results( "SELECT `title` FROM {$wpdb->leaguemanager} WHERE id = '".$league_id."'" );
		return ( $league[0]->title );
	}
	
	
	/**
	 * gets all active leagues
	 *
	 * @param none
	 * @return array
	 */
	function get_active_leagues()
	{
		return ( $this->get_leagues( false, 'WHERE active = 1' ) );
	}
	

	/**
	 * checks if league is active
	 *
	 * @param int $league_id
	 * @return boolean
	 */
	function league_is_active( $league_id )
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
	function activate_league( $league_id )
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
	function deactivate_league( $league_id )
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
	function toggle_league_status_text( $league_id )
	{
		if ( $this->league_is_active( $league_id ) )
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
	function toggle_league_status_action( $league_id )
	{
		if ( $this->league_is_active( $league_id ) )
			echo '<a href="edit.php?page=leaguemanager/manage-leagues.php&amp;deactivate_league='.$league_id.'">'.__( 'Deactivate', 'leaguemanager' ).'</a>';
		else
			echo '<a href="edit.php?page=leaguemanager/manage-leagues.php&amp;activate_league='.$league_id.'">'.__( 'Activate', 'leaguemanager' ).'</a>';
	}
	
	
	/**
	 * gets teams from database
	 *
	 * @param string $search search string for WHERE clause.
	 * @param string $output OBJECT | ARRAY
	 * @return array database results
	 */
	function get_teams( $search, $output = 'OBJECT' )
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
	 * gets meta data for team
	 *
	 * @param int $team_id
	 * @return array database results
	 */
	function get_team_meta( $team_id )
	{
		global $wpdb;
		$team_meta = $wpdb->get_results( "SELECT data.value AS col_value, col.title AS col_title, col.type AS col_type, col.order_by AS order_by, col.id AS col_id FROM {$wpdb->leaguemanager_teammeta} AS data LEFT JOIN {$wpdb->leaguemanager_leaguemeta} AS col ON col.id = data.col_id WHERE data.team_id = {$team_id} ORDER BY col.order ASC" );
		
		return $team_meta;
	}
	
	
	/**
	 * gets number of teams for specific league
	 *
	 * @param int $league_id
	 * @return int number of teams
	 */
	function get_num_teams( $league_id )
	{
		global $wpdb;
	
		$num_teams = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_teams} WHERE `league_id` = '".$league_id."'" );
		return $num_teams;
	}
	
	
	/**
	 * gets number of competitions
	 *
	 * @param int $league_id
	 *
	 * @return number of competitions
	 */
	function get_num_competitions( $league_id )
	{
		global $wpdb;
	
		$num_competitions = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_competitions} WHERE `league_id` = '".$league_id."'" );
		return $num_competitions;
	}
	
	
	/**
	 * gets teams in ranked order, depending on given parameters of table structure
	 *
	 * @param array $teams
	 * @return array $teams ordered
	 */
	function get_ranked_teams( $league_id )
	{
		global $wpdb;
		
		/*
		* Generate array of parameters to sort teams by
		*/
		$to_sort = array();
		foreach ( $this->get_table_structure( $league_id ) AS $col ) {
			if ( 1 == $col->order_by )
				array_push( $to_sort, $col->title );
		}
		
		/*
		* Generate array of team data to sort and indexed array of unsorted teams
		*/
		$i = 0;
		$team_meta = array();
		$teams = array();
		foreach ( $this->get_teams( "league_id = '".$league_id."'" ) AS $team ) {
			foreach ( $this->get_team_meta( $team->id ) AS $meta ) {
				if ( 1 == $meta->col_type ) {
					$meta_col_value = explode( ":", $meta->col_value );
					$meta_col_value = $meta_col_value[0];
				} elseif ( 2 == $meta->col_type )
					$meta_col_value = $meta->col_value;
			
				$team_meta[$i][$meta->col_title] = $meta_col_value;
			}
			$team_meta[$i]['team_id'] = $team->id;
			
			$i++;
				
			$teams[$team->id] = $team;
		}

		/*
		*  Generate order arrays
		*/
		foreach ( $team_meta AS $key => $row ) {
			if ( $to_sort ) {
				$i=0;
				foreach ( $to_sort AS $col_title ) {
					$order[$i][$key] = $row[$col_title];
					$i++;
				}
			} else {
				$order = false;
			}
		}
		
		/*
		* Create array of arguments for array_multisort
		*/
		$func_args = array();
		if ( $order ) {
			foreach ( $order AS $key => $order_array ) {
				array_push( $func_args, $order_array );
				array_push( $func_args, SORT_DESC );
			}
			
			/*
			* sort teams with array_multisort
			*/
			$eval = 'array_multisort(';
			for ( $i = 0; $i < count($func_args); $i++ )
				$eval .= "\$func_args[$i],";
			$eval .= "\$team_meta);";
			eval($eval);
		}
		
			
		/*
		* Create ranked array of temas
		*/
		$ranked_teams = array();
		$rank = 0;
		foreach ( $team_meta AS $key => $row ) {
			$rank++;
			$ranked_teams[$rank] = $teams[$row['team_id']];
		}
	
		return $ranked_teams;
	}
	
	
	/**
	 * gets Competition from database
	 * 
	 * @param string $search
	 * @return array database results
	 */
	function get_competitions( $search )
	{
	 	global $wpdb;
		$sql = "SELECT `competitor`, DATE_FORMAT(`date`, '%e') AS date_day, DATE_FORMAT(`date`, '%c') AS date_month, DATE_FORMAT(`date`, '%Y') AS date_year, DATE_FORMAT(`date`, '%H') AS `hour`, DATE_FORMAT(`date`, '%i') AS `minutes`, `location`, `league_id`, `home`, `id` FROM {$wpdb->leaguemanager_competitions} WHERE $search ORDER BY `date` ASC";
		return $wpdb->get_results( $sql );
	}
		 
		 
	/**
	 * add new League
	 *
	 * @param string $title
	 * @return string
	 */
	function add_league( $title )
	{
		global $wpdb;
		
		$wpdb->query( "INSERT INTO {$wpdb->leaguemanager} (title) VALUES ('".$this->slashes($title)."')" );
		return 'League added';
	}
		
		
	/**
	 * edit League
	 *
	 * @param string $title
	 * @param int $league_id
	 * @return string
	 */
	function edit_league( $title, $league_id )
	{
		global $wpdb;
		$wpdb->query( "UPDATE {$wpdb->leaguemanager} SET `title` = '".$this->slashes($title)."' WHERE `id` = {$league_id}" );
		return 'League updated';
	}
		
		
	/**
	 * delete League
	 *
	 * @param int $league_id
	 * @return void
	 */
	function del_league( $league_id )
	{
		global $wpdb;
		
		foreach ( $this->get_teams( "league_id = '".$league_id."'" ) AS $team )
			$this->del_team( $team->id );

		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager_leaguemeta} WHERE `league_id` = {$league_id}" );
		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager} WHERE `id` = {$league_id}" );
	}
		
		
	/**
	 * add new team
	 *
	 * @param int $league_id
	 * @param string $short_title short form for team
	 * @param string $title full name of club
	 * @param int $home 1 | 0
	 * @return string
	 */
	function add_team( $league_id, $short_title, $title, $home )
	{
		global $wpdb;
			
		$sql = "INSERT INTO {$wpdb->leaguemanager_teams}
				(title, short_title, home, league_id)
			VALUES
				('".$this->slashes($title)."',
				'".$this->slashes($short_title)."',
				'".$home."',
				'".$league_id."')";
		$wpdb->query( $sql );
		
		$this->populate_default_table_data( 'team', $wpdb->insert_id, $league_id );
			
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
	function edit_team( $team_id, $short_title, $title, $home )
	{
		global $wpdb;
		$wpdb->query( "UPDATE {$wpdb->leaguemanager_teams} SET `title` = '".$this->slashes($title)."', `short_title` = '".$this->slashes($short_title)."', `home` = '".$home."' WHERE `id` = {$team_id}" );
		return 'Team updated'	;
	}
		
		
	/**
	 * delete Team
	 *
	 * @param int $team_id
	 * @return void
	 */
	function del_team( $team_id )
	{
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager_competitions} WHERE competitor = '".$team_id."'" );
		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager_teammeta} WHERE `team_id` = '".$team_id."'" );
		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager_teams} WHERE `id` = '".$team_id."'" );
		return;
	}


	/**
	 * add Competition
	 *
	 * @param string $date
	 * @param int $competitor
	 * @param int $home 1 | 0
	 * @param string $location
	 * @param int $league_id
	 * @return string
	 */
	function add_competition( $date, $competitor, $home, $location, $league_id )
	{
	 	global $wpdb;
		$sql = "INSERT INTO {$wpdb->leaguemanager_competitions}
				(date, competitor, home, location, league_id)
			VALUES
				('".$date."',
				'".$this->slashes($competitor)."',
				'".$home."',
				'".$this->slashes($location)."',
				'".$league_id."')";
		$wpdb->query( $sql );
		return 'Competition added';
	}


	/**
	 * edit Competition
	 *
	 * @param string $date
	 * @param int $competitor
	 * @param int $home 1 | 0
	 * @param string $location
	 * @param int $league_id
	 * @param int $cid
	 * @return string
	 */
	function edit_competition( $date, $competitor, $home, $location, $league_id, $cid )
	{
	 	global $wpdb;
		$wpdb->query( "UPDATE {$wpdb->leaguemanager_competitions} SET `date` = '".$date."', `competitor` = '".$this->slashes($competitor)."', `home` = '".$home."', `location` = '".$location."', `league_id` = '".$league_id."' WHERE `id` = '".$cid."'" );
		return 'Competition updated';
	}


	/**
	 * delete Competition
	 *
	 * @param int $cid
	 * @return void
	 */
	function del_competition( $cid )
	{
	  	global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->leaguemanager_competitions} WHERE `id` = '".$cid."'" );
		return;
	}


	/**
	 * updates standings table
	 *
	 * @param array $teams array of teams to update
	 * @param array $teams_meta
	 * @return string
	 */
	function update_table( $teams, $teams_meta )
	{
	   	global $wpdb;
	   	if ( null != $teams ) {
			foreach ( $teams AS $team_id => $title ) {
				foreach ( $teams_meta[$team_id] AS $col_id => $col_value ) {
					$col_value = is_array( $col_value ) ? implode( ":", $col_value ) : $col_value;
					
					$wpdb->query( "UPDATE {$wpdb->leaguemanager_teammeta} SET `value` = '".$this->slashes($col_value)."' WHERE `col_id` = '".$col_id."' AND `team_id` = '".$team_id."'" );
				}
			}
		}			
		return 'Table updated';
	}
 

	/**
	 * adds slashes for MySQL Escapting if magic_quotes_gpc is off 
	 *
	 * @param string $str text string
	 * @return string
	 */
	function slashes( $str )
	{
		if ( 0 == get_magic_quotes_gpc() )
			$str = addslashes( $str );
		return $str;
	}
		
		
	/**
	 * inserts league standings into post content
	 *
	 * @param string $content
	 * @return string
	 */
	function print_standings_table( $content )
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
					
				$replace = $this->get_standings_table( $league_id );
				$content = str_replace( $search, $replace, $content );
			}
		}
		
		return $content;
	}
		
		
	/**
	 * gets league standings table
	 *
	 * @param int $league_id
	 * @return string
	 */
	function get_standings_table( $league_id )
	{
		global $wpdb;
			
		$out = '</p><table class="leaguemanager" summary="" title="'.__( 'Standings', 'leaguemanager' ).' '.$this->leagues[$league_id]['title'].'">';
		$out .= '<tr>'.$this->get_table_head( $league_id ).'</tr>';
				
		$teams = $this->get_ranked_teams( $league_id );
		if ( count($teams) > 0 ) {
			foreach( $teams AS $rank => $team ) {
				$style = ( 1 == $team->home ) ? ' style="font-weight: bold;"' : '';
							
				$out .= "<tr$style>";
				$out .= "<td style='text-align: center;'>$rank</td>";
				$out .= "<td>".$team->title."</td>";
				$out .= $this->get_table_body( $team->id );
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
	 *
	 */
	function print_competitions_table( $content )
	{
	 	$search = "/\[leaguecompetitions\s*=\s*(\w+)\]/i";
		
		preg_match_all( $search, $content , $matches );
			
		if ( is_array($matches[1]) ) {
			for ( $m = 0; $m < count($matches[0]); $m++ ) {
				$search = $matches[0][$m];
				if ( strlen($matches[1][$m]) ) {
					$league_id = $matches[1][$m];
				} else {
					continue;
				}
				
				$replace = $this->get_competitions_table( $league_id );
				$content = str_replace( $search, $replace, $content );
			}
		}
		return $content;
	}
		 
		 
	/**
	 * gets competitions table for given league
	 *
	 * @param int $league_id
	 * @return string
	 *
	 */
	function get_competitions_table( $league_id )
	{
		$leagues = $this->get_leagues( $league_id );
		$teams = $this->get_teams( $league_id, 'ARRAY' );
		$competitions = $this->get_competitions( "league_id = '".$league_id."'" );
			
		if ( $competitions ) {
			$out = "</p><table class='leaguemanager' summary='' title='".__( 'Competitions Program', 'leaguemanager' )." ".$leagues['title']."'>";
			$out .= "<tr>
					<th>".__( 'Date', 'leaguemanager' )."</th>
					<th>".__( 'Competition', 'leaguemanager' )."</th>
					<th>".__( 'Location', 'leaguemanager' )."</th>
					<th>".__( 'Begin', 'leaguemanager' )."</th>
				</tr>";
			foreach ( $competitions AS $competition ) {
				$location = ( '' == $competition->location ) ? 'N/A' : $competition->location;
				$start_time = ( '00' == $competition->hour && '00' == $competition->minutes ) ? 'N/A' : $competition->hour.":".$competition->minutes.' Uhr';
				$match = ( 1 == $competition->home ) ? $leagues[$league_id]['home_team']['title']." - ".$teams[$competition->competitor]['title'] : $teams[$competition->competitor]['title']." - ".$leagues[$league_id]['home_team']['title'];
					
				$style = ( 1 == $competition->home ) ? ' style="font-weight: bold;"' : '';
						
				$out .= "<tr$style>";
				$out .= "<td>".$competition->date_day.'.'.$competition->date_month."</td>";
				$out .= "<td>".$match."</td>";
				$out .= "<td>".$location."</td>";
				$out .= "<td>".$start_time."</td>";
				$out .= "</tr>";
			}
			$out .= "</table><p>";
		}
		
		return $out;
	}
		
		
	/**
	 * saves structure of standings table
	 *
	 * @param int $league_id
	 * @param array $col_name
	 * @param array $col_type
	 * @param array $col_order
	 * @param array $col_order_by
	 * @param array $new_col_name
	 * @param array $new_col_type
	 * @param array $new_col_order
	 * @param array $new_col_order_by
	 * @return string
	 */
	function save_table_structure( $league_id, $col_name, $col_type, $col_order, $col_order_by, $new_col_name, $new_col_type, $new_col_order, $new_col_order_by )
	{
		global $wpdb;
		
		if ( null != $col_name ) {
			foreach ( $wpdb->get_results( "SELECT `id` FROM {$wpdb->leaguemanager_leaguemeta} WHERE `league_id` = '".$league_id."'" ) AS $col) {
				if ( !array_key_exists( $col->id, $col_name ) ) {
					$wpdb->query( "DELETE FROM {$wpdb->leaguemanager_leaguemeta} WHERE `id` = {$col->id}" );
					$wpdb->query( "DELETE FROM {$wpdb->leaguemanager_teammeta} WHERE `col_id` = {$col->id}" );
				}
			}
			
			foreach ( $col_name AS $col_id => $col_title ) {
				$type = $col_type[$col_id];
				$order = $col_order[$col_id];
				$order_by = isset( $col_order_by[$col_id] ) ? 1 : 0;
					
				$wpdb->query( "UPDATE {$wpdb->leaguemanager_leaguemeta} SET `title` = '$col_title', `type` = '$type', `order` = '$order', `order_by` = '$order_by', `league_id` = '$league_id' WHERE `id` = '".$col_id."'" );
				$wpdb->query( "UPDATE {$wpdb->leaguemanager_teammeta} SET `col_id` = '".$col_id."' WHERE `col_id` = {$col_id}" );
			}
		}
			
		if ( null != $new_col_name ) {
			foreach ( $new_col_name AS $col_id => $col_title) {
				$type = $new_col_type[$col_id];
				$order_by = isset( $new_col_order_by[$col_id] ) ? 1 : 0;
								
				$max_order_sql = "SELECT MAX(`order`) AS `order` FROM {$wpdb->leaguemanager_leaguemeta};";
				if ( '' != $new_col_order[$col_id] ) {
					$order = $new_col_order[$col_id];
				} else {
					$max_order_sql = $wpdb->get_results( $max_order_sql, ARRAY_A );
					$order = $max_order_sql[0]['order'] +1;
				}
					
				$wpdb->query( "INSERT INTO {$wpdb->leaguemanager_leaguemeta} (`title`, `type`, `order`, `order_by`, `league_id`) VALUES ( '".$col_title."', '".$type."', '".$order."', '".$order_by."', '".$league_id."' );" );
				
				$this->populate_default_table_data( 'col', $wpdb->insert_id, $league_id, $type );
			}
		}
			
		return;
	}
		 
		 
	/**
	 * populates database with default table standings values
	 *
	 * @param string $mode
	 * @param int $id col_id or team_id
	 * @param int $league_id
	 * @param int $col_type default is false
	 */
	function populate_default_table_data( $mode, $id, $league_id, $col_type = false )
	{
		global $wpdb;
		
		if ( 'team' == $mode ) {
			foreach ( $this->get_table_structure( $league_id ) AS $col ) {
				$value = ( 1 == $col->type ) ? '0:0' : '';
				$wpdb->query( "INSERT INTO {$wpdb->leaguemanager_teammeta} (`col_id`, `value`, `team_id`) VALUES ( '".$col->id."', '".$value."', '".$id."' );" );
			}
		} elseif ( 'col' == $mode ) {
			foreach ( $wpdb->get_results( "SELECT `id` FROM {$wpdb->leaguemanager_teams} WHERE league_id = '".$league_id."'" ) AS $team ) {
				$value = ( 1 == $col_type ) ? '0:0' : '';
				$wpdb->query( "INSERT INTO {$wpdb->leaguemanager_teammeta} (`col_id`, `value`, `team_id`) VALUES ( '".$id."', '".$value."', '".$team->id."' );" );
			}
		}
	}
	
	
	/**
	 * gets table structure from MySQL Database
	 *
	 * @param int $league_id
	 * @return array
	 */
	function get_table_structure( $league_id )
	{
		global $wpdb;
		return $wpdb->get_results( "SELECT `title`, `type`, `order`, `order_by`, `id` FROM {$wpdb->leaguemanager_leaguemeta} WHERE `league_id` = {$league_id} ORDER BY `order` ASC" );
	}
	
	
	/**
	 * returns header for standings table
	 *
	 * @param int $leauge_id
	 */
	function get_table_head( $league_id )
	{
		$out = '<th style="text-align: center;">'.__( 'Rank', 'leaguemanager' ).'</th>
			<th>'.__( 'Club', 'leaguemanager' ).'</th>';
		if ( $table_structure = $this->get_table_structure( $league_id ) ) {
			foreach ( $table_structure AS $col )
				$out .= '<th>'.$col->title.'</th>';
		}
		return $out;
	}
	function print_table_head( $league_id )
	{
		echo $this->get_table_head( $league_id );
	}
	
	
	/**
	 * gets body with data for standings table
	 *
	 * @param int $team_id
	 * @param string $mode 'user' (default) | 'admin'
	 */
	function get_table_body( $team_id, $mode = 'user' )
	{
		$out = '';
		foreach ( $this->get_team_meta( $team_id ) AS $team_meta ) {
			$out .= '<td>';
			if ( 1 == $team_meta->col_type ) {
				$points = explode( ":", $team_meta->col_value );
				if ( 'admin' == $mode ) 
					$out .= '<input type="text" name="table_data['.$team_id.']['.$team_meta->col_id.'][p1]" value="'.$points[0].'" size="3" /> : <input type="text" name="table_data['.$team_id.']['.$team_meta->col_id.'][p2]" value="'.$points[1].'" size="3" />';
				else {
					$points = ( 'NaN' == $points[1] ) ? $points[0] : $points[0].':'.$points[1];
					$out .= $points;
				}
			} elseif ( 2 == $team_meta->col_type )
				$out .= ( 'admin' == $mode ) ? '<input type="text" name="table_data['.$team_id.']['.$team_meta->col_id.']" id="table_data['.$team_id.']['.$team_meta->col_id.']" value="'.$team_meta->col_value.'" />' : $team_meta->col_value;
			$out .= '</td>';
		}
		return $out;
	}
	function print_table_body( $team_id, $mode = 'user' )
	{
		echo $this->get_table_body( $team_id, $mode );
	}
	
		
	/**
	 * displays widget
	 *
	 * @param $args
	 *
	 */
	function display_widget( $args )
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
		
		$league = $this->get_leagues( $league_id );								
		echo $before_widget . $before_title . $league['title'] . $after_title;
		/*-- Short Results Table --*/
		echo "<ul id='leaguemanager_widget'>";
		if ( 1 == $match_display ) {
			echo "<li><span class='title'>".__( 'Competitions', 'leaguemanager' )."</span>";
			$competitions = $this->get_competitions( "league_id = '".$league_id."'" );
			$teams = $this->get_teams( $league_id, 'ARRAY' );
			
			if ( $competitions ) {
				echo "<ul class='leaguemanager_standings'>";
				foreach ( $competitions AS $competition ) {
					$home_team = $league['home_team']['short_title'];
					$competitor = $teams[$competition->competitor]['short_title'];
				
					if ( 1 == $competition->home )
						$match = $home_team." - ".$competitor;
					else
						$match = $competitor." - ".$home_team;
	
					echo "<li><strong>".$competition->date_day.'.'.$competition->date_month."</strong> ".$match."</li>";
				}
				echo "</ul>";
			} else {
				_e( 'Nothing found', 'leaguemanager' );
			}
			echo "</li>";
		}
		if ( 1 == $table_display ) {
			echo "<li><span class='title'>".__('Short Table', 'leaguemanager')."</span>";
			$teams = $this->get_ranked_teams( $league_id );
			if ( $teams ) {
				echo "<ol class='leaguemanager_results_list'>\n";
				foreach ( $teams AS $team ) {
					if ( $team->title == $league['home_team']['title'] )
						echo "<li><strong>".$team->short_title."</strong></li>\n";
					else
						echo "<li>".$team->short_title."</li>\n";
				}
				echo "</ol>\n";
			} else {
				_e( 'Nothing found', 'leaguemanager' );
			}
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
	function widget_control( $args )
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
		echo '<p style="text-align: left;"><label for="match_display_'.$league_id.'" class="leaguemanager-widget">'.__( 'Show Competitions','leaguemanager' ).'</label>';
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
	function add_header_code()
	{
		
		echo "\n\n<!-- WP Leagues Plugin Version ".LEAGUEMANAGER_VERSION." START -->\n";
		echo "<link rel='stylesheet' href='".LEAGUEMANAGER_URL."/style.css' type='text/css' />\n";
		if ( is_admin() AND isset( $_GET['page'] ) AND substr( $_GET['page'], 0, 13 ) == 'leaguemanager' ) {
			wp_register_script( 'leaguemanager', LEAGUEMANAGER_URL.'/leaguemanager.js', false, '1.0' );
			wp_print_scripts( 'leaguemanager' );
			echo "<script type='text/javascript'>\n";
			echo "var LEAGUEMANAGER_HTML_FORM_FIELD_TYPES = \"";
			foreach ($this->get_col_types() AS $col_type_id => $col_type)
				echo "<option value='".$col_type_id."'>".__( $col_type, 'leaguemanager' )."</option>";
			echo "\";\n";
			echo "</script>\n";
		}
		echo "<!-- WP Leagues Plugin END -->\n\n";
	}
			
				
	/**
	 * initialize widget
	 *
	 * @param none
	 */
	function init_widget()
	{
		if ( !function_exists('register_sidebar_widget') )
			return;
		
		foreach ( $this->get_active_leagues() AS $league_id => $league ) {
			$name = __( 'League', 'leaguemanager' ) .' - '. $league['title'];
			register_sidebar_widget( $name , array( &$this, 'display_widget' ) );
			register_widget_control( $name, array( &$this, 'widget_control' ), '', '', array( 'league_id' => $league_id, 'widget_id' => sanitize_title($name) ) );
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
		
		$create_leagues_sql = "CREATE TABLE {$wpdb->leaguemanager} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`title` varchar( 30 ) NOT NULL ,
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
		
		$create_leaguemeta_sql = "CREATE TABLE {$wpdb->leaguemanager_leaguemeta} (
							`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
							`title` varchar( 100 ) NOT NULL ,
							`type` int( 11 ) NOT NULL ,
							`order` int( 10 ) NOT NULL ,
							`order_by` tinyint( 1 ) NOT NULL ,
							`league_id` int( 11 ) NOT NULL ,
							PRIMARY KEY ( `id` ))";
		maybe_create_table( $wpdb->leaguemanager_leaguemeta, $create_leaguemeta_sql );
		
		$create_teammeta_sql = "CREATE TABLE {$wpdb->leaguemanager_teammeta} (
							`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
							`col_id` int( 11 ) NOT NULL ,
							`value` longtext NOT NULL default '' ,
							`team_id` int( 11 ) NOT NULL ,
							PRIMARY KEY ( `id` ))";
		maybe_create_table( $wpdb->leaguemanager_teammeta, $create_teammeta_sql);
		
		$create_competitions_sql = "CREATE TABLE {$wpdb->leaguemanager_competitions} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`date` datetime NOT NULL ,
						`competitor` int( 11 ) NOT NULL ,
						`location` varchar( 100 ) NOT NULL ,
						`home` tinyint( 1 ) NOT NULL ,
						`league_id` int( 11 ) NOT NULL ,
						PRIMARY KEY ( `id` ))";
		maybe_create_table( $wpdb->leaguemanager_competitions, $create_competitions_sql );
		
		$options = array();
		$options['version'] = LEAGUEMANAGER_VERSION;
		
		$old_options = get_option( 'leaguemanager' );
		if ( !isset($old_options['version']) || $old_options['version'] != LEAGUEMANAGER_VERSION )
			require_once( LEAGUEMANAGER_PATH . '/leaguemanager-upgrade.php' );
		
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
	 * uninstall plugin
	 *
	 * @param none
	 */
	function uninstall()
	{
		global $wpdb;
		
		$wpdb->query( "DROP TABLE {$wpdb->leaguemanager_competitions}" );
		$wpdb->query( "DROP TABLE {$wpdb->leaguemanager_teammeta}" );
		$wpdb->query( "DROP TABLE {$wpdb->leaguemanager_leaguemeta}" );
		$wpdb->query( "DROP TABLE {$wpdb->leaguemanager_teams}" );
		$wpdb->query( "DROP TABLE {$wpdb->leaguemanager}" );
		
		delete_option( 'leaguemanager_widget' );
		delete_option( 'leaguemanager' );
		
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
	
	
	/**
	 * adds menu to the admin interface
	 *
	 * @param none
	 */
	function add_admin_menu()
	{
		add_management_page( __( 'Leagues', 'leaguemanager' ), __( 'Leagues', 'leaguemanager' ), 'manage_leagues', basename( __FILE__, ".php" ).'/manage-leagues.php' );
	}
}
?>
