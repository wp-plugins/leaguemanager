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
	 * Adds shortcodes
	 *
	 * @param none
	 * @return void
	 */
	function addShortcodes()
	{
		add_shortcode( 'standings', array(&$this, 'showStandings') );
		add_shortcode( 'matches', array(&$this, 'showMatches') );
		add_shortcode( 'match', array(&$this, 'showMatch') );
		add_shortcode( 'crosstable', array(&$this, 'showCrosstable') );
		add_shortcode( 'teams', array(&$this, 'showTeams') );
		add_shortcode( 'team', array(&$this, 'showTeam') );
		add_shortcode( 'leaguearchive', array(&$this, 'showArchive') );

		add_action( 'leaguemanager_teampage', array(&$this, 'showTeam') );
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

		if ( $this->checkTemplate('standings-'.$league->sport) )
			$filename = 'standings-'.$league->sport;
		else
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
				$matches[$i]->hadPenalty = $match->hadPenalty = ( isset($match->penalty) && $match->penalty['home'] != '' && $match->penalty['away'] != '' ) ? true : false;
				$matches[$i]->hadOvertime = $match->hadOvertime = ( isset($match->overtime) && $match->overtime['home'] != '' && $match->overtime['away'] != '' ) ? true : false;
				$matches[$i]->home_points = ( NULL == $match->home_points ) ? '-' : $match->home_points;
				$matches[$i]->away_points = ( NULL == $match->away_points ) ? '-' : $match->away_points;
	
				$matches[$i]->start_time = ( '00' == $match->hour && '00' == $match->minutes ) ? '' : mysql2date(get_option('time_format'), $match->date);
	
				$matches[$i]->title = $teams[$match->home_team]['title'].' - '. $teams[$match->away_team]['title'];
				if ( parent::isHomeTeamMatch( $match->home_team, $match->away_team, $teams ) )
					$matches[$i]->title = '<strong>'.$matches[$i]->title.'</strong>';
				
				$matches[$i]->report = ( $match->post_id != 0 ) ? '(<a href="'.get_permalink($match->post_id).'">'.__('Report', 'leaguemanager').'</a>)' : '';
	
				if ( $match->hadPenalty )
					$matches[$i]->score = sprintf("%d - %d", $match->penalty['home'], $match->penalty['away'])." "._c( 'o.P.|on penalty', 'leaguemanager' );
				elseif ( $match->hadOvertime )
					$matches[$i]->score = sprintf("%d - %d", $match->overtime['home'], $match->overtime['away'])." "._c( 'AET|after extra time', 'leaguemanager' );
				else
					$matches[$i]->score = sprintf("%d - %d", $match->home_points, $match->away_points);
				
				$i++;
			}
		}
		
		if ( $this->checkTemplate('matches-'.$league->sport) )
			$filename = 'matches-'.$league->sport;
		else
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
//		$teams = $leaguemanager->getTeams( "`league_id` = ".$match->league_id, 'ARRAY' );
		$home = $leaguemanager->getTeam($match->home_team);
		$away = $leaguemanager->getTeam($match->away_team);
		
		$this->lmBridge->setProjectID( $league->project_id );
		$this->player = $this->lmBridge->getPlayer();

		$match->hadPenalty = ( isset($match->penalty) && $match->penalty['home'] != '' && $match->penalty['away'] != '' ) ? true : false;
		$match->hadOvertime = ( isset($match->overtime) && $match->overtime['home'] != '' && $match->overtime['away'] != '' ) ? true : false;

		$match->home_points = ( NULL == $match->home_points ) ? '-' : $match->home_points;
		$match->away_points = ( NULL == $match->away_points ) ? '-' : $match->away_points;

		$match->homeTeam = $home->title;
		$match->awayTeam = $away->title;
		$match->title = $match->homeTeam . "&#8211;" . $match->awayTeam;

		$match->homeLogo = $leaguemanager->getImageUrl($home->logo);
		$match->awayLogo = $leaguemanager->getImageUrl($away->logo);

		$match->start_time = ( '00' == $match->hour && '00' == $match->minutes ) ? '' : mysql2date(get_option('time_format'), $match->date);

		$match->report = ( $match->post_id != 0 ) ? '(<a href="'.get_permalink($match->post_id).'">'.__('Report', 'leaguemanager').'</a>)' : '';

		if ( $match->hadPenalty )
			$match->score = sprintf("%d - %d", $match->penalty['home'], $match->penalty['away'])." "._c( 'o.P.|on penalty', 'leaguemanager' );
		elseif ( $match->hadOvertime )
			$match->score = sprintf("%d - %d", $match->overtime['home'], $match->overtime['away'])." "._c( 'AET|after extra time', 'leaguemanager' );
		else
			$match->score = sprintf("%d - %d", $match->home_points, $match->away_points);
		
		if ( $this->checkTemplate('match-'.$league->sport) )
			$filename = 'match-'.$league->sport;
		else
			$filename = ( !empty($template) ) ? 'match-'.$template : 'match';

		$out = $this->loadTemplate( $filename, array('league' => $league, 'match' => $match) );

		return $out;
	}
	
	
	/**
	 * Function to display Team list
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showTeams( $atts )
	{
		global $leaguemanager;
		extract(shortcode_atts(array(
			'league_id' => 0,
			'league_name' => '',
			'template' => '',
			'season' => false
		), $atts ));

		$league = $leaguemanager->getLeague($league_id);
		if (empty($season)) {
			$season = $leaguemanager->getSeason(&$league);
			$season = $season['name'];
		}

		$teams = $leaguemanager->getTeams( "`league_id` = {$league_id} AND `season` = '".$season."'" );

		if ( $this->checkTemplate('teams-'.$league->sport) )
			$filename = 'teams-'.$league->sport;
		else
			$filename = ( !empty($template) ) ? 'teams-'.$template : 'teams';

		$out = $this->loadTemplate( $filename, array('league' => $league, 'teams' => $teams) );

		return $out;
	}


	/**
	 * Function to display Team Info Page
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showTeam( $atts )
	{
		global $leaguemanager;
		extract(shortcode_atts(array(
			'id' => 0,
			'template' => '',
			'echo' => 0,
		), $atts ));

		$team = $leaguemanager->getTeam( $id );
		$league = $leaguemanager->getLeague( $team->league_id );

		// Get next match
		$next_matches = $leaguemanager->getMatches("( `home_team` = {$team->id} OR `away_team` = {$team->id} ) AND DATEDIFF(NOW(), `date`) <= 0");
		$next_match = $next_matches[0];
		if ( $next_match ) {
			if ( $next_match->home_team == $team->id ) {
				$opponent = $leaguemanager->getTeam($next_match->away_team);
				$next_match->match = $team->title . " &#8211; " . $opponent->title;
			} else {
				$opponent = $leaguemanager->getTeam($next_match->home_team);
				$next_match->match = $opponent->title  . " &#8211; " . $team->title;
			}
		}

		// Get last match
		$prev_matches = $leaguemanager->getMatches("( `home_team` = {$team->id} OR `away_team` = {$team->id} ) AND DATEDIFF(NOW(), `date`) > 0");
		$prev_match = $prev_matches[0];
		if ( $prev_match ) {
			if ( $prev_match->home_team == $team->id ) {
				$opponent = $leaguemanager->getTeam($prev_match->away_team);
				$prev_match->match = $team->title . " &#8211; " . $opponent->title;
			} else {
				$opponent = $leaguemanager->getTeam($prev_match->home_team);
				$prev_match->match = $opponent->title  . " &#8211; " . $team->title;
			}
		
			$prev_match->hadOvertime = ( isset($prev_match->overtime) && $prev_match->overtime['home'] != '' && $prev_match->overtime['away'] != '' ) ? true : false;
			$prev_match->hadPenalty = ( isset($prev_match->penalty) && $prev_match->penalty['home'] != '' && $prev_match->penalty['away'] != '' ) ? true : false;

			if ( $prev_match->hadPenalty )
				$prev_match->score = sprintf("%d - %d", $prev_match->penalty['home'], $prev_match->penalty['away'])." "._c( 'o.P.|on penalty', 'leaguemanager' );
			elseif ( $prev_match->hadOvertime )
				$prev_match->score = sprintf("%d - %d", $prev_match->overtime['home'], $prev_match->overtime['away'])." "._c( 'AET|after extra time', 'leaguemanager' );
			else
				$prev_match->score = sprintf("%d - %d", $prev_match->home_points, $prev_match->away_points);
		}


		if ( $this->checkTemplate('team-'.$league->sport) )
			$filename = 'team-'.$league->sport;
		else
			$filename = ( !empty($template) ) ? 'team-'.$template : 'team';

		$out = $this->loadTemplate( $filename, array('league' => $league, 'team' => $team, 'next_match' => $next_match, 'prev_match' => $prev_match) );

		if ( $echo )
			echo $out;
		else
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

	
	/**
	 * check if template exists
	 *
	 * @param string $template
	 * @return boolean
	 */
	function checkTemplate( $template )
	{
		if ( file_exists( TEMPLATEPATH . "/leaguemanager/$template.php")) {
			return true;
		} elseif ( file_exists(LEAGUEMANAGER_PATH . "/view/".$template.".php") ) {
			return true;
		}

		return false;
	}
}

?>
