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
	 * checks if bridge is active
	 *
	 * @var boolean
	 */
	var $bridge = false;
	
	
	/**
	 * initialize shortcodes
	 *
	 * @param boolean $bridge
	 * @return void
	 */
	function __construct($bridge = false)
	{
		global $lmLoader;
		
		$this->addShortcodes();
		if ( $bridge ) {
			global $lmBridge;
			$this->bridge =  true;
			$this->lmBridge = $lmBridge;
		}
	}
	function LeagueManagerShortcodes($bridge = false)
	{
		$this->__construct($bridge);
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
		add_shortcode( 'leaguematch', array(&$this, 'showMatch') );
		add_shortcode( 'leaguecrosstable', array(&$this, 'showCrosstable') );
		add_shortcode( 'leaguearchive', array(&$this, 'showArchive') );
		
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
			'league_name' => '',
			'logo' => 'true',
			'template' => 'extend',
			'season' => false
		), $atts ));
		
		$search = !empty($league_name) ? $league_name : $league_id;
		$league = $leaguemanager->getLeague( $search );
		$teams = $leaguemanager->rankTeams( $league_id, $season );
		
		$i = 0; $class = array();
		foreach ( $teams AS $team ) {
			$class = ( in_array('alternate', $class) ) ? array() : array('alternate');
			// Add divider class
			if ( $rank == 1 || $rank == 3 || count($teams)-$rank == 3 || count($teams)-$rank == 1) $class[] =  'divider';
			
			if ( $league->team_ranking == 'auto' ) $teams[$i]->rank = $i+1;
			$teams[$i]->class = implode(' ', $class);
			$teams[$i]->logoURL = parent::getThumbnailUrl($team->logo);
			$teams[$i]->title = ( 'widget' == $mode ) ? $team->short_title : $team->title;
			if ( 1 == $team->home ) $teams[$i]->title = '<strong>'.$team->title.'</strong>';
			if ( $team->website != '' ) $teams[$i]->title = '<a href="http://'.$team->website.'" target="_blank">'.$team->title.'</a>';
			
			$team->points['plus'] += $team->add_points; // add or substract points
			$teams[$i]->points = sprintf($league->point_format, $team->points['plus'], $team->points['minus']);
			$teams[$i]->points2 = sprintf("%d:%d", $team->points2['plus'], $team->points2['minus']);
			$i++;
		}
		
		$league->show_logo = ( $logo == 'true' ) ? true : false;

		$filename = 'standings-'.$template;
		$out = $this->loadTemplate( $filename, array('league' => $league, 'teams' => $teams) );
			
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
			'league_name' => '',
			'team_id' => 0,
			'template' => '',
			'mode' => '',
			'season' => '',
			'archive' => false,
		), $atts ));
		
		$search = !empty($league_name) ? $league_name : $league_id;
		$league = $leaguemanager->getLeague( $search );
		$this->league_id = $league->id;

		if ( !isset($_GET['match']) ) {
			if (empty($season)) {
				$season = $leaguemanager->getSeason(&$league);
				$season = $season['name'];
			}
			$league->match_days = ( $mode != 'all' && $mode != 'home' && $season['num_match_days'] > 0 ) ? true : false;
			$league->isCurrMatchDay = ( $archive ) ? false : true;
				
			$teams = $leaguemanager->getTeams( "`league_id` = ".$league_id." AND `season` = {$season}", 'ARRAY' );

			$search = "`league_id` = '".$league_id."' AND `season` = '".$season."'";
			if ( isset($_GET['team_id']) && !empty($_GET['team_id']) ) $team_id = (int)$_GET['team_id'];
			if ( $team_id )
				$search .= " AND ( `home_team`= {$team_id} OR `away_team` = {$team_id} )";
			if ( $mode != 'all' && $mode != 'home' && !$team_id )
				$search .= " AND `match_day` = '".parent::getMatchDay(true)."'";

			if ( $mode == 'home' )
				$search .= parent::buildHomeOnlyQuery($league_id);
				
			$matches = $leaguemanager->getMatches( $search , false );
			$i = 0;
			foreach ( $matches AS $match ) {
				$class = ( 'alternate' == $class ) ? '' : 'alternate';
				
				$matches[$i]->class = $class;
				$matches[$i]->hadPenalty = ( isset($match->penalty) && !empty($match->penalty) ) ? true : false;
				$matches[$i]->hadOvertime = ( isset($match->overtime) && !empty($match->overtime) ) ? true : false;
				$matches[$i]->home_points = ( NULL == $match->home_points ) ? '-' : $match->home_points;
				$matches[$i]->away_points = ( NULL == $match->away_points ) ? '-' : $match->away_points;
	
				$matches[$i]->start_time = ( '00' == $match->hour && '00' == $match->minutes ) ? '' : mysql2date(get_option('time_format'), $match->date);
	
				$matches[$i]->title = $teams[$match->home_team]['title'].' - '. $teams[$match->away_team]['title'];
				if ( parent::isHomeTeamMatch( $match->home_team, $match->away_team, $teams ) )
					$matches[$i]->title = '<strong>'.$matches[$i]->title.'</strong>';
				
				$matches[$i]->report = ( $match->post_id != 0 ) ? '(<a href="'.get_permalink($match->post_id).'">'.__('Report', 'leaguemanager').'</a>)' : '';
	
				$matches[$i]->score =  $match->home_points.":".$match->away_points;
				
				$i++;
			}
		}
		
		$filename = ( !empty($template) ) ? 'matches-'.$template : 'matches';
		$out = $this->loadTemplate( $filename, array('league' => $league, 'matches' => $matches, 'teams' => $teams) );

		return $out;
	}
	
	
	/**
	 * Function to display single match
	 *
	 * [leaguematch id="1" template="name"]
	 *
	 * - id is the ID of the match to display
	 * - league_id is the ID of league
	 * - template is the template used for displaying. Replace name appropriately. Templates must be named "match-template.php" (optional)
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showMatch( $atts )
	{
		global $leaguemanager;
		extract(shortcode_atts(array(
			'id' => 0,
			'template' => '',
		), $atts ));
		
		$match = $leaguemanager->getMatch($id);
		$league = $leaguemanager->getLeague($match->league_id);
		$teams = $leaguemanager->getTeams( "`league_id` = ".$match->league_id, 'ARRAY' );
		
		$this->lmBridge->setProjectID( $league->project_id );
		$this->player = $this->lmBridge->getPlayer();

		$match->home_points = ( NULL == $match->home_points ) ? '-' : $match->home_points;
		$match->away_points = ( NULL == $match->away_points ) ? '-' : $match->away_points;

		$match->homeTeam = $teams[$match->home_team]['title'];
		$match->awayTeam = $teams[$match->away_team]['title'];
		$match->title = $teams[$match->home_team]['title'] . "&#8211;" . $teams[$match->away_team]['title'];

		$match->homeLogo = $leaguemanager->getImageUrl($teams[$match->home_team]['logo']);
		$match->awayLogo = $leaguemanager->getImageUrl($teams[$match->away_team]['logo']);

		$match->start_time = ( '00' == $match->hour && '00' == $match->minutes ) ? '' : mysql2date(get_option('time_format'), $match->date);

		$match->report = ( $match->post_id != 0 ) ? '(<a href="'.get_permalink($match->post_id).'">'.__('Report', 'leaguemanager').'</a>)' : '';

		$match->score = sprintf("%d:%d", $match->home_points, $match->away_points);
		
		$filename = ( !empty($template) ) ? 'match-'.$template : 'match';
		$out = $this->loadTemplate( $filename, array('league' => $league, 'match' => $match) );

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
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showCrosstable( $atts )
	{
		global $leaguemanager;
		extract(shortcode_atts(array(
			'league_id' => 0,
			'league_name' => '',
			'template' => '',
			'mode' => '',
			'season' => false
		), $atts ));
		
		$search = !empty($league_name) ? $league_name : $league_id;
		$league = $leaguemanager->getLeague( $search );	
		if (empty($season)) {
			$season = $leaguemanager->getSeason(&$league);
			$season = $season['name'];
		}
		$teams = $leaguemanager->rankTeams( $league_id, $season );
		
		$filename = ( !empty($template) ) ? 'crosstable-'.$template : 'crosstable';
		$out = $this->loadTemplate( $filename, array('league' => $league, 'teams' => $teams, 'mode' => $mode) );
		
		return $out;
	}
	
	
	/**
	 * show Archive
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showArchive( $atts )
	{
		global $leaguemanager;
		extract(shortcode_atts(array(
			'league_id' => false,
			'league_name' => '',
			'season' => false,
			'template' => ''
		), $atts ));
		
		// get all leagues, needed for dropdown
		$leagues = $leaguemanager->getLeagues();
		$league = false; // Initialize league variable

		// Get League by Name
		if (!empty($league_name)) {
			$league = $leaguemanager->getLeague( $league_name );
			$league_id = $league->id;
		}
		
		if ( isset($_GET['season']) && !empty($_GET['season']) )
			$season = $_GET['season'];

		// Get League ID from shortcode or $_GET
		$league_id = ( !$league_id && isset($_GET['league_id']) && !empty($_GET['league_id']) ) ? (int)$_GET['league_id'] : false;

		// select first league
		if ( !$league_id )
			$league_id = $leagues[0]->id;

		// Get League and first Season if not set
		if ( !$league ) $league = $leaguemanager->getLeague( $league_id );
		if ( !$season ) {
			$season = reset($league->seasons);
			$season = $season['name'];
		}

		$seasons = array();
		foreach ( $leagues AS $league ) {
			foreach( $league->seasons AS $l_season ) {
				if ( !in_array($l_season['name'], $seasons) && !empty($l_season['name']) )
					$seasons[] = $l_season['name'];
			}
		}
		sort($seasons);
		
		$filename = (!empty($template) ) ? 'archive-'.$template : 'archive';
		$out = $this->loadTemplate( $filename, array('leagues' => $leagues, 'seasons' => $seasons, 'league_id' => $league_id, 'season' => $season) );
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
