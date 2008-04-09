<?php
/*
Plugin Name: LeagueManager
Plugin URI: http://wordpress.org/extend/plugins/leaguemanager/
Description: Manage and present sports league results.
Version: 1.1-RC1
Author: Kolja Schleich

PHP Version 4 and 5

Copyright 2007-2008  Kolja Schleich  (email : kolja.schleich@googlemail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


class WP_LeagueManager
{
	/**
	 * Plugin Version
	 *
	 * @var string
	 */
	var $version = '1.1-RC1';
		 
		 
	/**
	 * Array of months
	 *
	 * @param array
	 */
	var $months = array();
		 
		 
	/**
	 * Types for table columns. Currently supported are `points` and `text`
	 *
	 * @var array
	 */
	var $col_types = array( 1 => 'Points', 2 => 'Text' );


	/**
	 * Array of Leagues
	 *
	 * @var array
	 */
	var $leagues = array();
		
		
	/**
	 * Array of teams of specific league. ID is used as index
	 *
	 * @var array
	 */
	var $teams = array();
		
		 
	/**
	 * include directory
	 *
	 * @var string
	 */
	var $tpl_dir = 'includes/';
	
		
	/**
	 * Plugni URL
	 *
	 * @var string
	 */
	var $plugin_url;
		 
		 
	/**
	 * Initializes plugin
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
	 	global $wpdb, $table_prefix;
		
		/*
		* Initialize database tables
		*/
		$wpdb->leaguemanager = $table_prefix . 'leaguemanager_leagues';
		$wpdb->leaguemanager_teams = $table_prefix . 'leaguemanager_teams';
		$wpdb->leaguemanager_leaguemeta = $table_prefix . 'leaguemanager_leaguemeta';
		$wpdb->leaguemanager_teammeta = $table_prefix . 'leaguemanager_teammeta';
		$wpdb->leaguemanager_competitions = $table_prefix . 'leaguemanager_competitions';

	 	/*
		* Get current locale set in Wordpress
		* Save months as full representation as array in class
		*/
	 	$locale = get_locale();
		setlocale(LC_ALL, $locale);
		for ( $month = 1; $month <= 12; $month++ ) 
			$this->months[$month] = htmlentities( strftime( "%B", mktime( 0,0,0, $month, date("m"), date("Y") ) ) );
			
		$this->plugin_url = get_bloginfo( 'wpurl' ).'/'.PLUGINDIR.'/'.basename(__FILE__, ".php");
			
		// save array of leagues in class for further usage
		$this->get_leagues();
			
		return;
	}
	function WP_LeagueManager()
	{
		$this->__construct();
	}
	
		
	/**
	 * returns current league or all leagues
	 *
	 * @param int $index (optional) id of league to return
	 * @param int $element (optional) index of specific league. Only works with given $index
	 * @return array or string
	 */
	function get_league( $index = null, $element = null )
	{
		if ( null != $index ) {
			if ( null != $element )
				return $this->leagues[$index][$element];
			
			return $this->leagues[$index];
		} else
			return $this->leagues;
	}
		
		
	/**
	 * gets leagues from database and save them in class
	 *
	 * @param none
	 * @return void
	 */
	function get_leagues()
	{
		global $wpdb;
		
		$this->leagues = array();
		if ( $leagues = $wpdb->get_results( "SELECT title, id FROM {$wpdb->leaguemanager} ORDER BY id ASC" ) ) {
			foreach( $leagues AS $league ) {
				$team = $this->get_teams( 'home = 1 AND league_id = "'.$league->id.'"' );
				
				$this->leagues[$league->id]['title'] = $league->title;
				$this->leagues[$league->id]['home_team']['title'] = $team[0]->title;
				$this->leagues[$league->id]['home_team']['short_title'] = $team[0]->short_title;
			}
		} else {
			$this->leagues = false;
		}
			
		return;
	}
		
		
	/**
	 * saves teams of specific league in class
	 *
	 * @param int $league_id
	 * @return void
	 *
	 */
	function save_teams( $league_id )
	{
	 	$this->teams = array();
		if ( $teams = $this->get_teams( 'league_id = "'.$league_id.'"' ) ) {
			foreach ( $teams AS $team ) {
				$this->teams[$team->id]['title'] = $team->title;
				$this->teams[$team->id]['short_title'] = $team->short_title;
				$this->teams[$team->id]['home'] = $team->home;
			}
		}
			
		return;
	}
		
		
	/**
	 * gets team of specific id
	 *
	 * @param int $team_id
	 * @return array
	 *
	 */
	function get_team( $team_id )
	{
	 	if ( null == $team_id )
			return $this->teams;
		else
			return $this->teams[$team_id];
	}
		 
		 
	/**
	 * gets teams from database
	 *
	 * @param string $search search string for WHERE clause.
	 * @return array database results
	 */
	function get_teams( $search )
	{
		global $wpdb;
		
		return ( $wpdb->get_results( "SELECT `title`, `short_title`, `home`, `league_id`, `id` FROM {$wpdb->leaguemanager_teams} WHERE $search ORDER BY id ASC	" ) );
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
				//$team_meta[$i][$meta->col_title] = $meta->col_value;
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
		$this->populate_default_table_data( 'team', mysql_insert_id(), $league_id );
			
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
		if ( 0 == get_magic_quotes_gpc() ) {
			$str = addslashes( $str );
		}
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
				$class = ( 1 == $team->home ) ? 'home' : '' ;
							
				$out .= "<tr class='$class'>";
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
	  	$competitions = $this->get_competitions( "league_id = '".$league_id."'" );
		$this->save_teams( $league_id );
			
		if ( $competitions ) {
			$out = "</p><table class='leaguemanager' summary='' title='".__( 'Competitions Program', 'leaguemanager' )." ".$this->leagues[$league_id]['title']."'>";
			$out .= "<tr>
					<th>".__( 'Date', 'leaguemanager' )."</th>
					<th>".__( 'Competition', 'leaguemanager' )."</th>
					<th>".__( 'Location', 'leaguemanager' )."</th>
					<th>".__( 'Begin', 'leaguemanager' )."</th>
				</tr>";
			foreach ( $competitions AS $competition ) {
				$location = ( '' == $competition->location ) ? 'N/A' : $competition->location;
				$start_time = ( '00' == $competition->hour && '00' == $competition->minutes ) ? 'N/A' : $competition->hour.":".$competition->minutes.' Uhr';
				$match = ( 1 == $competition->home ) ? $this->leagues[$league_id]['home_team']['title']." - ".$this->teams[$competition->competitor]['title'] : $this->teams[$competition->competitor]['title']." - ".$this->leagues[$league_id]['home_team']['title'];
					
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
					$max_order_sql = $wpdb->get_results( $may_order_sql, ARRAY_A );
					$order = $may_order_sql[0]['order'] +1;
				}
					
				$wpdb->query( "INSERT INTO {$wpdb->leaguemanager_leaguemeta} (`title`, `type`, `order`, `order_by`, `league_id`) VALUES ( '".$col_title."', '".$type."', '".$order."', '".$order_by."', '".$league_id."' );" );
				
				$this->populate_default_table_data( 'col', mysql_insert_id(), $league_id, $type );
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
	 * gets data for given league
	 *
	 * @param int $team_id
	 */
	function get_table_data( $team_id )
	{
		global $wpdb;
		return $wpdb->get_results( "SELECT `col_id`, `value` FROM {$wpdb->leaguemanager_teammeta} WHERE `team_id` = {$team_id} ORDER BY `col_id` ASC" );
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
	 * prints breadcrum navigation
	 *
	 * @param int $league_id 
	 */
	function print_breadcrumb_navi( $league_id )
	{
		if ( isset($_GET['mode']) || isset($_GET['show_league']) ) {
			$out = '<div class="wrap"><p class="leaguemanager_breadcrumb">';
			if ( isset( $_GET['show_league'] ) )
				$out .= '<a href="edit.php?page=leaguemanager.php">'.__( 'Leaguemanager', 'leaguemanager' ).'</a> > '.$this->get_league( $league_id, 'title' );
			elseif ( isset($_GET['mode']) AND ( 'add' == $_GET['mode'] || 'edit' == $_GET['mode'] ) ) {
				$out .= '<a href="edit.php?page=leaguemanager.php">'.__( 'Leaguemanager', 'leaguemanager' ).'</a> > ';
				if ( isset($_GET['item']) AND 'league' == $_GET['item'] ) {
					$out .= '<a href="edit.php?page=leaguemanager.php&show_league='.$league_id.'">'.$this->get_league( $league_id, 'title' ).'</a> > ';
					$out .= ( 'add' == $_GET['mode'] ) ? __( 'Add League', 'leaguemanager' ) : __( 'Edit League', 'leaguemanager' );
				} elseif ( isset($_GET['item']) AND 'team' == $_GET['item'] ) {
					$out .= '<a href="edit.php?page=leaguemanager.php&show_league='.$league_id.'">'.$this->get_league( $league_id, 'title' ).'</a> > ';
					$out .= ( 'add' == $_GET['mode'] ) ? __( 'Add Team', 'leaguemanager' ) : __( 'Edit Team', 'leaguemanager' );
				} elseif ( isset($_GET['item']) AND 'competition' == $_GET['item'] ) {
					$out .= '<a href="edit.php?page=leaguemanager.php&show_league='.$league_id.'">'.$this->get_league( $league_id, 'title' ).'</a> > ';
					$out .= ( 'add' == $_GET['mode'] ) ? __( 'Add Competition', 'leaguemanager' ) : __( 'Edit Competition', 'leaguemanager' );
				}
			}		
			$out .= '</p></div>';
	
			echo $out;
		}
		
	}
	
	
	/**
	 * prints administration panel
	 *
	 * @param none
	 */
	function print_admin_page()
	{
		global $wpdb;
	
		if ( isset($_POST['updateLeague']) AND !isset($_POST['deleteit']) ) {
			if ('league' == $_POST['updateLeague'] ) {
				if ( '' == $_POST['league_id'] ) {
					$return_message = $this->add_league( $_POST['league_title'] );
				} else {
					$this->save_table_structure( $_POST['league_id'], $_POST['col_title'], $_POST['col_type'], $_POST['col_order'], $_POST['order_by'], $_POST['new_col_title'], $_POST['new_col_type'], $_POST['new_col_order'], $_POST['new_order_by']);
					$return_message = $this->edit_league( $_POST['league_title'], $_POST['league_id'] );
				}
			} elseif ( 'team' == $_POST['updateLeague'] ) {
				$home = isset( $_POST['home'] ) ? 1 : 0;
				
				if ( '' == $_POST['team_id'] ) {
					$return_message = $this->add_team( $_POST['league_id'], $_POST['short_title'], $_POST['team'], $home );
				} else {
					$return_message = $this->edit_team( $_POST['team_id'], $_POST['short_title'], $_POST['team'], $home );
				}
			} elseif ( 'competition' == $_POST['updateLeague'] ) {
				$date = $_POST['competition_year'].'-'.str_pad($_POST['competition_month'], 2, 0, STR_PAD_LEFT).'-'.str_pad($_POST['competition_day'], 2, 0, STR_PAD_LEFT).' '.str_pad($_POST['begin_hour'], 2, 0, STR_PAD_LEFT).':'.str_pad($_POST['begin_minutes'], 2, 0, STR_PAD_LEFT).':00';
				$home = isset( $_POST['home'] ) ? 1 : 0;
										
				if ( '' == $_POST['cid'] ) {
					$return_message = $this->add_competition( $date, $_POST['competitor'], $home, $_POST['location'], $_POST['league_id'] );
				}  else {
					$return_message = $this->edit_competition( $date, $_POST['competitor'], $home, $_POST['location'], $_POST['league_id'], $_POST['cid'] );
				}
			} elseif ( 'table' == $_POST['updateLeague'] ) {
				$return_message = $this->update_table( $_POST['rank'], $_POST['team'], $_POST['table_data'] );
			}

			echo '<div id="message" class="updated fade"><p><strong>'.__( $return_message, 'leaguemanager' ).'</strong></p></div>';
			
		} elseif ( isset($_POST['deleteit']) AND isset($_POST['delete']) ) {
			if ( 'leagues' == $_POST['item'] ) {
				foreach ( $_POST['delete'] AS $league_id ) $this->del_league( $league_id );
			} elseif ( 'teams' == $_POST['item'] ) {
				foreach ( $_POST['delete'] AS $team_id ) $this->del_team( $team_id);
			} elseif ( 'competitions' == $_POST['item'] ) {
				foreach ( $_POST['delete'] AS $cid ) $this->del_competition( $cid );
			}			
		}
		
		if ( isset($_GET['mode']) AND 'del' != $_GET['mode'] ) {
			$mode = trim( $_GET['mode'] );
			
			if ( isset($_GET['item']) ) {
				$item = trim( $_GET['item'] );
					
				switch ( $item ) {
					case 'league':
						if ( isset($_POST['updateLeague']) ) $this->get_leagues();
			
						if ( 'add' == $mode ) {
							$form_title = 'Add League';
							
							$league_title = ''; $home_team = ''; $league_id = '';
						} elseif ( 'edit' == $mode ) {
							$form_title = 'Edit League';
							
							$league_title = $this->get_league( $_GET['league_id'], 'title' );
							$home_team = $this->leagues[$_GET['league_id']]['home_team']['title'];
							$league_id = $_GET['league_id'];
						}
						include $this->tpl_dir . 'edit-league.php';
							
						break;
					case 'team':
						if ( 'add' == $mode ) {
							$form_title = 'Add Team';
							
							$team_title = ''; $short_title = ''; $home = ''; $team_id = ''; $league_id = $_GET['league_id'];
						} elseif ( 'edit' == $mode ) {
							$form_title = 'Edit Team';
							
							$team = $this->get_teams( "id = '".$_GET['team_id']."'", 'id ASC' );
							if ( $team ) {
								$team_title = $team[0]->title;
								$short_title = $team[0]->short_title;
								$home = ( 1 == $team[0]->home ) ? ' checked="checked"' : '';
								$team_id = $team[0]->id;
								$league_id = $team[0]->league_id;
							}
						}
						include $this->tpl_dir . 'edit-team.php';
						
						break;
					case 'competition':
						if ( 'add' == $mode ) {
							$form_title = 'Add Competition';
							
							$league_id = $_GET['league_id'];
							$competition_day = ''; $competition_month = ''; $competition_year = date("Y"); $competitor = '';
							for ($i = 1; $i <= 2; $i++)
								$home_selection[$i] = 0;
							$begin_hour = ''; $begin_minutes = ''; $location = ''; $competition_id = '';
						} elseif ( 'edit' == $mode ) {
							$form_title = 'Edit Competition';
							
							$competition = $this->get_competitions( "id = '".$_GET['cid']."'" );
							
							if ( $competition ) {
								$league_id = $competition[0]->league_id;
								$competition_day = $competition[0]->date_day;
								$competition_month = $competition[0]->date_month;
								$competition_year = $competition[0]->date_year;
								$begin_hour = $competition[0]->hour;
								$begin_minutes = $competition[0]->minutes;
								$location = $competition[0]->location;
								$competitor = $competition[0]->competitor;
								
								if ( 1 == $competition[0]->home )
									$home_selection = " checked='checked'";
								else
									$home_selection = '';
									
								$competition_id = $competition[0]->id;
							}	
						}
							
						include $this->tpl_dir . 'edit-competition.php';
						break;
				}
			}
		} elseif ( isset($_GET['show_league']) ) {
			include $this->tpl_dir . 'show-leagues.php';
		} else {
			include $this->tpl_dir . 'manage-leagues.php';
		}
	}
		
			
	/**
	 * displays widget
	 *
	 * @param $args
	 *
	 */
	function widget( $args )
	{
		$options = get_option( 'leaguemanager_widget' );
		
		$defaults = array(
			'before_widget' => '<li id="league" class="widget '.get_class($this).'_'.__FUNCTION__.'">',
			'after_widget' => '</li>',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => '</h2>',
		);
		$args = array_merge( $defaults, $args );
		extract( $args );
		
		if ( !$league_id ) $league_id = $options['league_id'];
		$league_title = $this->leagues[$league_id]['title'];
								
		echo $before_widget . $before_title . $league_title . $after_title;
		/*-- Short Results Table --*/
		echo "<ul id='leaguemanager_widget'>";
		if ( 0 != $options['match_display'] ) {
			echo "<li><span class='title'>".__( 'Competitions', 'leaguemanager' )."</span>";
			$competitions = $this->get_competitions( "league_id = '".$league_id."'" );
			$this->save_teams( $league_id );
			
			if ( $competitions ) {
				echo "<ul>";
				foreach ( $competitions AS $competition ) {
					/*
					* Set either full title or short title
					*/
					if ( 1 == $options['match_display'] ) {
						$home_team = $this->leagues[$league_id]['home_team']['title'];
						$competitor = $this->teams[$competition->competitor]['title'];
					} elseif ( 2 == $options['match_display'] ) {
						$home_team = $this->leagues[$league_id]['home_team']['short_title'];
						$competitor = $this->teams[$competition->competitor]['short_title'];
					}
					
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
		if ( 0 != $options['table_display'] ) {
			echo "<li><span class='title'>".__('Short Table', 'leaguemanager')."</span>";
			$teams = $this->get_teams( "league_id = '".$league_id."'", '`points1_plus` DESC, `points2_plus` DESC' );
			if ( $teams ) {
				echo "<ol class='wp_league_results_list'>\n";
				foreach ( $teams AS $team ) {
					/*
					* Set either full or short title
					*/
					if ( 1 == $options['table_display'] )
						$team_title = $team->title;
					elseif ( 2 == $options['table_display'] )
						$team_title = $team->short_title;
					
					if ( $team->title == $this->leagues[$league_id]['home_team']['title'] )
						echo "<li><strong>".$team_title."</strong></li>\n";
					else
						echo "<li>".$team_title."</li>\n";
				}
				echo "</ol>\n";
			} else {
				_e( 'Nothing found', 'leaguemanager' );
			}
			echo "</li>";
		}
		echo "<li class='info'><a href='".get_permalink( $options['info'] )."'>".__( 'More Info', 'leaguemanager' )."</a></li>";
		echo "</ul>";
		echo $after_widget;
	}
		
		
	/**
	 * widget control panel
	 *
	 * @param none
	 */
	function widget_control()
	{
	 	$options = get_option( 'leaguemanager_widget' );
		if ( $_POST['league-submit'] ) {
			$options['league_id'] = $_POST['league_id'];
			$options['table_display'] = $_POST['table_display'];
			$options['match_display'] = $_POST['match_display'];
			$options['info'] = $_POST['info'];
			
			update_option( 'leaguemanager_widget', $options );
		}
		
	 	if ( $this->leagues ) {
			$title = $this->leagues[$options['league_id']]['title'];
	
			echo '<p style="text-align: left;"><label for="league_id" class="leaguemanager-widget">'.__('League','leaguemanager').'</label><select class="leaguemanager-widget" size="1" name="league_id" id="league_id">';
			foreach ( $this->leagues AS $id => $league ) {
				$selected = ( $options['league_id'] == $id ) ? ' selected="selected"' : '';
				echo '<option value="'.$id.'"'.$selected.'>'.$league['title'].'</option>';
			}
			echo '</select></p>';
			
			for ( $i = 1; $i <= 3; $i++ )
				$checked[$i] = ($options['match_display'] == $i) ? ' checked="checked"' : '';

			echo '<p style="text-align: left;"><label for="match_display" class="leaguemanager-widget">'.__( 'Dates','leaguemanager' ).'</label>';
			echo '<input type="radio" name="match_display" id="match_display_full" value="1"'.$checked[1].'><label for="match_display_full" class="leaguemanager-widget right">'.__( 'Full', 'leaguemanager' ).'</label>';
			echo '<input type="radio" name="match_display" id="match_display_short" value="2"'.$checked[2].'><label for="match_display_short" class="leaguemanager-widget right">'.__( 'Short', 'leaguemanager' ).'</label>';
			echo '<input type="radio" name="match_display" id="match_display_none" value="0"'.$checked[3].'><label for="match_display_none" class="leaguemanager-widget right">'.__( 'None','leaguemanager' ).'</label>';
			echo '</p>';
			
			for ( $i = 1; $i <= 3; $i++ )
				$checked[$i] = ( $options['table_display'] == $i ) ? ' checked="checked"' : '';
			
			echo '<p style="text-align: left;"><label for="table_display" class="leaguemanager-widget">'.__( 'Table', 'leaguemanager' ).'</label>';
			echo '<input type="radio" name="table_display" id="table_display_full" value="1"'.$checked[1].'><label for="table_display_full" class="leaguemanager-widget right">'.__( 'Full', 'leaguemanager' ).'</label>';
			echo '<input type="radio" name="table_display" id="table_display_short" value="2"'.$checked[2].'><label for="table_display_short" class="leaguemanager-widget right">'.__( 'Short', 'leaguemanager' ).'</label>';
			echo '<input type="radio" name="table_display" id="table_display_none" value="0"'.$checked[3].'><label for="table_display_none" class="leaguemanager-widget right">'.__( 'None','leaguemanager' ).'</label>';
			echo '</p>';
			echo '<p style="text-align: left;"><label for="info" class="leaguemanager-widget">'.__( 'Info Page', 'leaguemanager' ).'<label><input type="text" size="3" name="info" id="info" value="'.$options['info'].'" /></p>';
				
			echo '<input type="hidden" name="league-submit" id="league-submit" value="1" />';
		}
	}
		 
		 
	/**
	 * ads code to Wordpress head
	 *
	 * @param none
	 */
	function add_header_code()
	{
		echo "\n\n<!-- WP Leagues Plugin START -->\n";
		echo "<link rel='stylesheet' href='".$this->plugin_url."/style.css' type='text/css' />\n";
		wp_register_script( 'leaguemanager', $this->plugin_url.'/leaguemanager.js', array(), '1.0' );
		wp_print_scripts( 'leaguemanager' );
		echo "<script type='text/javascript'>\n";
			echo "var LEAGUEMANAGER_HTML_FORM_FIELD_TYPES = \"";
			foreach ($this->col_types AS $col_type_id => $col_type)
				echo "<option value='".$col_type_id."'>".__( $col_type, 'leaguemanager' )."</option>";
			echo "\";\n";
		echo "</script>\n";
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
		
		$options = array();
		add_option( 'leaguemanager_widget', $options, 'Leaguemanager Widget Options', 'yes' );
		register_sidebar_widget( __( 'League', 'leaguemanager' ), array( &$this, 'widget' ) );
		register_widget_control( __( 'League', 'leaguemanager' ), array( &$this, 'widget_control' ), 350, 200 );
	}
		 
		 
	/**
	 * initialize plugin
	 *
	 * @param none
	 */
	function init()
	{
		global $wpdb;
		include_once( ABSPATH.'/wp-admin/upgrade-functions.php' );
			
		$create_leagues_sql = "CREATE TABLE {$wpdb->leaguemanager} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`title` varchar( 30 ) NOT NULL,
						PRIMARY KEY ( `id` ))";
		maybe_create_table( $wpdb->leaguemanager, $create_leagues_sql );
			
		$create_leagues_teams_sql = "CREATE TABLE {$wpdb->leaguemanager_teams} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`title` varchar( 25 ) NOT NULL ,
						`short_title` varchar( 25 ) NOT NULL,
						`home` tinyint( 1 ) NOT NULL ,
						`league_id` int( 11 ) NOT NULL ,
						PRIMARY KEY ( `id` ))";
		maybe_create_table( $wpdb->leaguemanager_teams, $create_leagues_teams_sql );
		
		$create_leagues_tablestructure_sql = "CREATE TABLE {$wpdb->leaguemanager_leaguemeta} (
							`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
							`title` varchar( 100 ) NOT NULL ,
							`type` int( 11 ) NOT NULL ,
							`order` int( 10 ) NOT NULL ,
							`order_by` tinyint( 1 ) NOT NULL ,
							`league_id` int( 11 ) NOT NULL ,
							PRIMARY KEY ( `id` ))";
		maybe_create_table( $wpdb->leaguemanager_leaguemeta, $create_leagues_tablestructure_sql );
		
		$create_leagues_tabledata_sql = "CREATE TABLE {$wpdb->leaguemanager_teammeta} (
							`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
							`col_id` int( 11 ) NOT NULL ,
							`value` longtext NOT NULL default '' ,
							`team_id` int( 11 ) NOT NULL ,
							PRIMARY KEY ( `id` ))";
		maybe_create_table( $wpdb->leaguemanager_teammeta, $create_leagues_tabledata_sql );
		
		$create_leagues_competitions_sql = "CREATE TABLE {$wpdb->leaguemanager_competitions} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`date` datetime NOT NULL ,
						`competitor` int( 11 ) NOT NULL ,
						`location` varchar( 100 ) NOT NULL ,
						`home` tinyint( 1 ) NOT NULL ,
						`league_id` int( 11 ) NOT NULL ,
						PRIMARY KEY ( `id` ))";
		maybe_create_table( $wpdb->leaguemanager_competitions, $create_leagues_competitions_sql );
		
		$options = array();
		$options['version'] = $this->version;
		
		$old_options = get_option( 'leaguemanager' );
		if ( $old_options['version'] < $this->version || strlen($old_options['version']) > strlen($this->version) ) {
			update_option( 'leaguemanager', $options );
		}
		
		add_option( 'leaguemanager', $options, 'Leaguemanager Options', 'yes' );
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
		
		$plugin = 'leaguemanager/leaguemanager.php';
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
		if ( function_exists('add_submenu_page') )
			add_submenu_page( 'edit.php',  __( 'Leaguemanager', 'leaguemanager' ), __( 'Leaguemanager', 'leaguemanager' ), 7, basename(__FILE__), array(&$this, 'print_admin_page') );
	}
}


$leaguemanager = new WP_LeagueManager();

// Actions
add_action( 'admin_head', array(&$leaguemanager, 'add_header_code') );
add_action( 'wp_head', array(&$leaguemanager, 'add_header_code') );
add_action( 'activate_leaguemanager/leaguemanager.php', array(&$leaguemanager, 'init') );
add_action( 'admin_menu', array(&$leaguemanager, 'add_admin_menu') );
add_action( 'plugins_loaded', array(&$leaguemanager, 'init_widget') );
	
// Filters
add_filter( 'the_content', array(&$leaguemanager, 'print_standings_table') );
add_filter( 'the_content', array(&$leaguemanager, 'print_competitions_table') );
	
// Load textdomain for translation
load_plugin_textdomain( 'leaguemanager', $path = 'wp-content/plugins/leaguemanager' );

// Uninstall Plugin
if ( isset($_GET['leaguemanager']) AND 'uninstall' == $_GET['leaguemanager'] AND ( isset($_GET['delete_plugin']) AND 1 == $_GET['delete_plugin'] ) )
	$leaguemanager->uninstall();
?>