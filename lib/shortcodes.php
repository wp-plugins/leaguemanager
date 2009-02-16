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
						$replace = "[leaguestandings league_id=".$league_id."]";
			
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
						$replace = "[leaguematches league_id=".$league_id."]";
			
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
						$replace = "[leaguecrosstable league_id=".$league_id." mode='".$matches[2][$key]."']";
						
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
	 *	[leaguestandings league_id="1" mode="extend|compact" template="name"]
	 *
	 * - league_id is the ID of league
	 * - mode is either extend or compact (will default to 'extend' if missing)
	 * - template is the template used for displaying. Replace name appropriately. Templates must be named "standings-template.php" (optional)
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showStandings( $atts )
	{
		global $wpdb, $leaguemanager;
		
		extract(shortcode_atts(array(
			'league_id' => 0,
			'template' => '',
			'logo' => 'true',
			'mode' => 'extend',
		), $atts ));
		
		$league = $leaguemanager->getLeague( $league_id );
		$teams = $leaguemanager->rankTeams( $league_id );
		
		$i = 0; $class = array();
		foreach ( $teams AS $team ) {
			$class = ( in_array('alternate', $class) ) ? array() : array('alternate');
			// Add divider class
			if ( $rank == 1 || $rank == 3 || count($teams)-$rank == 3 || count($teams)-$rank == 1) $class[] =  'divider';
			
			$teams[$i]->rank = $i+1;
			$teams[$i]->class = implode(' ', $class);
			$teams[$i]->logoURL = parent::getThumbnailUrl($team->logo);
			$teams[$i]->title = ( 'widget' == $mode ) ? $team->short_title : $team->title;
			if ( 1 == $team->home ) $teams[$i]->title = '<strong>'.$team->title.'</strong>';
			if ( $team->website != '' ) $teams[$i]->title = '<a href="http://'.$team->website.'" target="_blank">'.$team->title.'</a>';
			
			$i++;
		}
		
		$league->isGymnastics = ( $leaguemanager->isGymnasticsLeague( $league_id ) ) ? true : false;
		$league->show_logo = ( $logo == 'true' ) ? true : false;

		$filename = ( !empty($template) ) ? 'standings-'.$template : 'standings';
		$out = $this->loadTemplate( $filename, array('league' => $league, 'teams' => $teams, 'mode' => $mode) );
			
		return $out;
	}
	
	
	/**
	 * Function to display League Matches
	 *
	 *	[leaguematches league_id="1" mode="all|home" template="name"]
	 *
	 * - league_id is the ID of league
	 * - mode can be either "all" or "home". If it is not specified the matches are displayed on a weekly basis
	 * - template is the template used for displaying. Replace name appropriately. Templates must be named "matches-template.php" (optional)
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showMatches( $atts )
	{
		global $leaguemanager;
		
		extract(shortcode_atts(array(
			'league_id' => 0,
			'template' => '',
			'mode' => ''
		), $atts ));
		
		$this->league_id = $league_id;
		$league = $leaguemanager->getLeague( $league_id );
		$league->isGymnastics = ( parent::isGymnasticsLeague( $league->id ) ) ? true : false;
		$league->match_days = ( $mode != 'all' && $mode != 'home' && $league->num_match_days > 0 ) ? true : false;

		$teams = $leaguemanager->getTeams( "`league_id` = ".$league_id, 'ARRAY' );
		
		$search = "league_id = '".$league_id."'";
		if ( $mode != 'all' && $mode != 'home' )
			$search .= " AND match_day = '".parent::getMatchDay(true)."'";
		if ( $mode == 'home' )
			$search .= parent::buildHomeOnlyQuery($league_id);
			
		$matches = $leaguemanager->getMatches( $search , false );
		
		$i = 0;
		foreach ( $matches AS $match ) {
			$class = ( 'alternate' == $class ) ? '' : 'alternate';
			
			$matches[$i]->class = $class;
			$matches[$i]->hadPenalty = ( !empty($match->penalty) ) ? true : false;
			$matches[$i]->hadOvertime = ( !empty($match->overtime) ) ? true : false;

			$matches[$i]->overtime = maybe_unserialize($match->overtime);
			$matches[$i]->penalty = maybe_unserialize($match->penalty);

			$matches[$i]->home_points = ( NULL == $match->home_points ) ? '-' : $match->home_points;
			$matches[$i]->away_points = ( NULL == $match->away_points ) ? '-' : $match->away_points;

			$matches[$i]->start_time = ( '00' == $match->hour && '00' == $match->minutes ) ? '' : mysql2date(get_option('time_format'), $match->date);

			$matches[$i]->title = $teams[$match->home_team]['title'].' - '. $teams[$match->away_team]['title'];
			if ( parent::isHomeTeamMatch( $match->home_team, $match->away_team, $teams ) )
				$matches[$i]->title = '<strong>'.$matches[$i]->title.'</strong>';
			
			$matches[$i]->report = ( $match->post_id != 0 ) ? '(<a href="'.get_permalink($match->post_id).'">'.__('Report', 'leaguemanager').'</a>)' : '';

			$points2 = maybe_unserialize($match->points2);
			if ( $leaguemanager->isBallGameLeague( $league->id ) ) {
				if ( $matches[$i]->hadPenalty )
					$matches[$i]->score = sprintf("%d:%d", $matches[$i]->penalty['home'], $matches[$i]->penalty['away'])." ".__( 'o.P.', 'leaguemanager' );
				elseif ( $matches[$i]->hadOvertime )
					$matches[$i]->score = sprintf("%d:%d", $matches[$i]->overtime['home'], $matches[$i]->overtime['away'])." ".__( 'AET', 'leaguemanager' );
				else
					$matches[$i]->score = sprintf("%d:%d", $match->home_points, $match->away_points);
				
				$reverse = false;
				if ( $matches[$i]->hadOvertime ) {
					$points2[] = array( 'home' => $match->home_points, 'away' => $match->away_points );
					$reverse = true;
				}
				if ( $matches[$i]->hadPenalty ) {
					$points2[] = array( 'home' => $matches[$i]->overtime['home'], 'away' => $matches[$i]->overtime['away'] );
					$reverse = true;
				}
				
				if ( $reverse ) {
					$points2 = array_reverse($points2);
					foreach ( $points2 AS $x => $points )
						$points2[$x] = implode(":", $points);
						
					$matches[$i]->score .= " <span class='parts'>(".implode(", ", $points2).")</span>";
				} else {
					$matches[$i]->score .= ( $points2[0]['plus'] != '' ) ? " (".$points2[0]['plus'].":".$points2[0]['minus'].")" : '';
				}
			} elseif ( $leaguemanager->getMatchParts($league->type) > 1 ) {
				foreach ( $points2 AS $x => $points )
					$points2[$x] = implode(":", $points);

				if ( $matches[$i]->hadPenalty )
					$matches[$i]->score = sprintf("%d:%d", $matches[$i]->penalty['home'], $matches[$i]->penalty['away'])." ".__( 'o.P.', 'leaguemanager' );
				elseif ( $matches[$i]->hadOvertime )
					$matches[$i]->score = sprintf("%d:%d", $matches[$i]->overtime['home'], $matches[$i]->overtime['away'])." ".__( 'AET', 'leaguemanager' );
				else
					$matches[$i]->score = sprintf("%d:%d", $match->home_points, $match->away_points);

				$matches[$i]->score .= " (".implode(" ",$points2).")";
			} else {
				$matches[$i]->apparatus_points = (isset($points2[0]['plus']) && $points2[0]['plus'] != '') ? $points2[0]['plus'].":".$points2[0]['minus'] : "-:-";
				$matches[$i]->score =  $match->home_points.":".$match->away_points;
			}
			
			$i++;
		}
		
		$filename = ( !empty($template) ) ? 'matches-'.$template : 'matches';
		$out = $this->loadTemplate( $filename, array('league' => $league, 'matches' => $matches, 'teams' => $teams) );

		return $out;
	}
	
	
	/**
	 * Function to display Crosstable
	 *
	 * [leaguecrosstable league_id="1" mode="popup" template="name"]
	 *
	 * - league_id is the ID of league to display
	 * - mode set to "popup" makes the crosstable be displayed in a thickbox popup window.
	 * - template is the template used for displaying. Replace name appropriately. Templates must be named "crosstable-template.php" (optional)
	 *
	 * If this is not set the table will simply be displayed embeded in the page/post
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showCrosstable( $atts )
	{
		global $leaguemanager;
		extract(shortcode_atts(array(
			'league_id' => 0,
			'template' => '',
			'mode' => ''
		), $atts ));
		
		$league = $leaguemanager->getLeague( $league_id );
		$teams = $leaguemanager->rankTeams( $league_id );
		
		$filename = ( !empty($template) ) ? 'crosstable-'.$template : 'crosstable';
		$out = $this->loadTemplate( $filename, array('league' => $league, 'teams' => $teams, 'mode' => $mode) );
		
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
		global $wpdb, $leaguemanager;

		$match = $leaguemanager->getMatches("(`home_team` = $curr_team_id AND `away_team` = $opponent_id) OR (`home_team` = $opponent_id AND `away_team` = $curr_team_id)");
		$match = $match[0];
		
		$out = "<td class='num'>-:-</td>";
		if ( $match ) {
			if ( !empty($match->penalty) ) {
				$match->penalty = maybe_unserialize($match->penalty);
				$points = array( 'home' => $match->penalty['home'], 'away' => $match->penalty['away']);
			} elseif ( !empty($match->overtime) ) {
				$match->overtime = maybe_unserialize($match->overtime);
				$points = array( 'home' => $match->overtime['home'], 'away' => $match->overtime['away']);
			} else {
				$points = array( 'home' => $match->home_points, 'away' => $match->away_points );
			}
			
			// match at home
			if ( NULL == $match->home_points && NULL == $match->away_points )
				$out = "<td class='num'>-:-</td>";
			elseif ( $curr_team_id == $match->home_team )
				$out = "<td class='num'>".sprintf("%d:%d", $points['home'], $points['away'])."</td>";
			// match away
			elseif ( $opponent_id == $match->home_team )
				$out = "<td class='num'>".sprintf("%d:%d", $points['away'], $points['home'])."</td>";
			
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
		global $leaguemanager;
		extract($vars);
		
		ob_start();
		if ( file_exists( TEMPLATEPATH . "/leaguemanager/$template.php")) {
			include(TEMPLATEPATH . "/leaguemanager/$template.php");
		} elseif ( file_exists(LEAGUEMANAGER_PATH . "/view/".$template.".php") ) {
			include(LEAGUEMANAGER_PATH . "/view/".$template.".php");
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