<?php
/**
* Shortcodes class for the WordPress plugin LeagueManager
* 
* @author 	Kolja Schleich
* @package	LeagueManager
* @copyright 	Copyright 2008-2009
*/

class LeagueManagerShortcodes extends LeagueManager
{
	/**
	 * initialize shortcodes
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		$this->addShortcodes();
	}
	function LeagueManagerShortcodes()
	{
		$this->__construct();
	}
	
	
	/**
	 * replace old shortcodes with new ones
	 *
	 * @param string $content
	 * @return string
	 */
	function convert( $content )
	{
		if ( stristr( $content, '[leaguestandings' )) {
			$search = "@\[leaguestandings\s*=\s*(\w+)\]@i";
			
			if ( preg_match_all($search, $content , $matches) ) {
				if (is_array($matches)) {
					foreach($matches[1] AS $key => $v0) {
						$league_id = $v0;
						$search = $matches[0][$key];
						$replace = "[leaguestandings league_id=".$league_id." /]";
			
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
						$replace = "[leaguematches league_id=".$league_id." /]";
			
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
						$replace = "[leaguecrosstable league_id=".$league_id." mode='".$matches[2][$key]."' /]";
						
						$content = str_replace( $search, $replace, $content );
					}
				}
			}
		}

		return $content;
	}


	/**
	 * Adds shortcodes
	 *
	 * @param none
	 * @return void
	 */
	function addShortcodes()
	{
		add_shortcode( 'leaguestandings', array(&$this, 'showStandings') );
		add_shortcode( 'leaguematches', array(&$this, 'showMatches') );
		add_shortcode( 'leaguecrosstable', array(&$this, 'showCrosstable') );
		
		add_filter( 'the_content', array(&$this, 'convert') );
	}
	
	
	/**
	 * Function to display League Standings
	 *
	 *	[leaguestandings league_id="1" mode="extend|compact" /]
	 *
	 * - league_id is the ID of league
	 * - mode is either extend or compact (will default to 'extend' if missing)
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showStandings( $atts )
	{
		global $wpdb;
		
		extract(shortcode_atts(array(
			'league_id' => 0,
			'mode' => 'extend',
		), $atts ));
		
		$preferences = parent::getLeaguePreferences( $league_id );
		$teams = parent::rankTeams( $league_id );
		
		$show_logo = ( 1 == $preferences->show_logo ) ? true : false;
		$gymnastics = ( parent::isGymnasticsLeague( $league_id ) ) ? true : false;
		$league_name = parent::	getLeagueTitle( $league_id );
		
		//if ( !$widget ) $out .= '</p>';
		$out .= $this->loadTemplate( 'standings', array('teams' => $teams, 'show_logo' => $show_logo, 'gymnastics' => $gymnastics, 'league_name' => $league_name, 'mode' => $mode) );
		//if ( !$widget ) $out .= '<p>';
		
		return $out;
	}
	
	
	/**
	 * Function to display League Matches
	 *
	 *	[leaguematches league_id="1" mode="all|home" /]
	 *
	 * - league_id is the ID of league
	 * - mode can be either "all" or "home". If it is not specified the matches are displayed on a weekly basis
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showMatches( $atts )
	{
		global $wp_query;
		
		extract(shortcode_atts(array(
			'league_id' => 0,
			'mode' => ''
		), $atts ));
		
		$this->league_id = $league_id;
		$leagues = parent::getLeagues( $league_id );
		$preferences = parent::getLeaguePreferences( $league_id );
		
		$all = false; $home_only = false;
		if ( $mode == 'all' ) $all = true;
		elseif ( $mode == 'home' ) $home_only = true;
		
		$page_obj = $wp_query->get_queried_object();
		$page_ID = $page_obj->ID;
		
		$teams = parent::getTeams( $league_id, 'ARRAY' );
			
		$search = "league_id = '".$league_id."'";
		if ( !$all && !$home_only )
			$search .= " AND match_day = '".parent::getMatchDay(true)."'";
		$matches = parent::getMatches( $search , false );
		
		//$out = "</p>";
		$out .= $this->loadTemplate( 'matches', array('league_id' => $league_id, 'matches' => $matches, 'teams' => $teams, 'preferences' => $preferences, 'all' => $all, 'home_only' => $home_only, 'page_ID' => $page_ID) );
		//$out .= '<p>';
		
		return $out;
	}
	
	
	/**
	 * Function to display Crosstable
	 *
	 * [leaguecrosstable league_id="1" mode="popup" /]
	 *
	 * - league_id is the ID of league to display
	 * - mode set to "popup" makes the crosstable be displayed in a thickbox popup window.
	 * If this is not set the table will simply be displayed embeded in the page/post
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showCrosstable( $atts )
	{
		extract(shortcode_atts(array(
			'league_id' => 0,
			'mode' => ''
		), $atts ));
		
		$leagues = parent::getLeagues( $league_id );
		$teams = parent::rankTeams( $league_id );
		
		//$out = "</p>";
		$out .= $this->loadTemplate( 'crosstable', array('league_id' => $league_id, 'leagues' => $leagues, 'teams' => $teams, 'mode' => $mode) );
		//$out .= "<p>";
		
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

		$match = parent::getMatches("(`home_team` = $curr_team_id AND `away_team` = $opponent_id) OR (`home_team` = $opponent_id AND `away_team` = $curr_team_id)");
		$match = $match[0];
		
		$out = "<td class='num'>-:-</td>";
		if ( $match ) {
			// match at home
			if ( NULL == $match->home_points && NULL == $match->away_points )
				$out = "<td class='num'>-:-</td>";
			elseif ( $curr_team_id == $match->home_team )
				$out = "<td class='num'>".$match->home_points.":".$match->away_points."</td>";
			// match away
			elseif ( $opponent_id == $match->home_team )
				$out = "<td class='num'>".$match->away_points.":".$match->home_points."</td>";
			
		}

		return $out;
	}
	
	
	/**
	 * Load template for user display. First the current theme directory is checked for a template
	 * before defaulting to the plugin
	 *
	 * @param string $template Name of the template file (without extension)
	 * @param array $vars Array of variables name=>value available to display code (optional)
	 * @return the content
	 */
	function loadTemplate( $template, $vars = array() )
	{
		extract($vars);
		
		ob_start();
		if ( file_exists( TEMPLATEPATH . "/leaguemanager/$template.php")) {
			include(TEMPLATEPATH . "/leaguemanager/$template.php");
		} elseif ( file_exists(LEAGUEMANAGER_PATH . "/view/$template.php") ) {
			include(LEAGUEMANAGER_PATH . "/view/$template.php");
		} else {
			parent::setMessage( sprintf(__('Could not load template %s.php', 'leaguemanager'), $template), true );
			parent::printMessage();
		}
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}
}

?>