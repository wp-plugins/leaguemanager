<?php
/**
 * US Football Sports Class 
 * 
 * @author 	LaMonte Forthun
 * @package	LeagueManager
 * @copyright 	Copyright 2014
*/
class LeagueManagerUSFootball extends LeagueManager
{

	/**
	 * sports key
	 *
	 * @var string
	 */
	var $key = 'us-football';


	/**
	 * load specific settings
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		$this->keys = array( 'nfl' => __( 'US Football (NFL)', 'leaguemanager' ), 'ncaa' => __( 'US Collegiate Football (NCAA)', 'leaguemanager' ), 'hs' => __( 'US High School Football (HS)', 'leaguemanager' ), 'fb' => __( 'US Football (fb)', 'leaguemanager' ) );
		add_filter( 'leaguemanager_sports', array(&$this, 'sports') );
		add_filter( 'leaguemanager_point_rules_list', array(&$this, 'getPointRuleList') );
		add_filter( 'leaguemanager_point_rules',  array(&$this, 'getPointRules') );
		add_action( 'leaguemanager_doc_point_rules', array(&$this, 'pointRuleDocumentation') );
		foreach ( $this->keys AS $key => $name ) {
			add_filter( 'rank_teams_'.$key, array(&$this, 'rankTeams') );
			add_filter( 'team_points_'.$key, array(&$this, 'calculatePoints'), 10, 3 );
			add_filter( 'team_points2_'.$key, array(&$this, 'calculateGoalStatistics') );
			add_action( 'leaguemanager_standings_header_'.$key, array(&$this, 'displayStandingsHeader') );
			add_action( 'leaguemanager_standings_columns_'.$key, array(&$this, 'displayStandingsColumns'), 10, 2 );
		}
	}

	function LeagueManagerUSFootball()
	{
		$this->__construct();
	}


	/**
	 * add sports to list
	 *
	 * @param array $sports
	 * @return array
	 */
	function sports( $sports )
	{
		foreach ( $this->keys AS $key => $name )
			$sports[$key] = $name;

		return $sports;
	}
	
	
	/**
	 * get Point Rule list
	 *
	 * @param array $rules
	 * @return array
	 */
	function getPointRuleList( $rules )
	{
		foreach ( $this->keys AS $key => $name )
			$rules[$key] = $name;

		return $rules;
	}


	/**
	 * get Point rules
	 *
	 * @param array $rules
	 * @return array
	 */
	function getPointRules( $rules )
	{
		// nfl rule
		$rules['nfl'] = array( 'forwin' => 1, 'fordraw' => 0, 'forloss' => 0, 'forwin_overtime' => 1, 'forloss_overtime' => 0, 'forscoring' => 0 );
		// ncaa rule
		$rules['ncaa'] = array( 'forwin' => 1, 'fordraw' => 0, 'forloss' => 0, 'forwin_overtime' => 1, 'forloss_overtime' => 0, 'forscoring' => 0 );
		// nfl rule
		$rules['hs'] = array( 'forwin' => 1, 'fordraw' => 0, 'forloss' => 0, 'forwin_overtime' => 1, 'forloss_overtime' => 0, 'forscoring' => 0 );
		// ncaa rule
		$rules['fb'] = array( 'forwin' => 1, 'fordraw' => 0, 'forloss' => 0, 'forwin_overtime' => 1, 'forloss_overtime' => 0, 'forscoring' => 0 );
		
		return $rules;
	}
	
	/**
	 * rank Teams
	 *
	 * @param array $teams
	 * @return array of teams
	 */
	function rankTeams( $teams )
	{
		foreach ( $teams AS $key => $row ) {
			$points[$key] = $row->points['plus']+$row->add_points;
			$done[$key] = $row->done_matches;
			$diff[$key] = $row->diff;
            $WinPerc[$key] =  round((($row->won_matches) > 0 ? (($row->done_matches) > 0 ? ((($row->won_matches)/($row->done_matches))*100) : 100) : 0));
		}

		array_multisort( $WinPerc, SORT_DESC, SORT_NUMERIC, $diff, SORT_DESC, $points, SORT_DESC, $teams );
		return $teams;
	}


	/**
	 * extend header for Standings Table in Backend
	 *
	 * @param none
	 * @return void
	 */
	function displayStandingsHeader()
	{
		echo '<th class="num">'._x( 'Pct', 'leaguemanager' ).'</th><th class="num">'._x( 'Scores', 'leaguemanager' ).'</th><th class="num">'.__( 'Diff', 'leaguemanager').'</th>';
	}


	/**
	 * extend columns for Standings Table in Backend
	 *
	 * @param object $team
	 * @param string $rule
	 * @return void
	 */
	function displayStandingsColumns( $team, $rule )
	{
		global $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();
		$WinPerc = number_format((($team->won_matches) > 0 ? (($team->done_matches) > 0 ? (($team->won_matches)/($team->done_matches)) : 1) : 0), 3, '.', '');
		echo '<td class="num">'.$WinPerc.'</td>';
		echo '<td class="num">';

		if ( is_admin() && $rule == 'manual' )
			echo '<input type="text" size="2" name="custom['.$team->id.'][points2][plus]" value="'.$team->points2_plus.'" /> : <input type="text" size="2" name="custom['.$team->id.'][points2][minus]" value="'.$team->points2_minus.'" />';
		else
			printf($league->point_format2, $team->points2_plus, $team->points2_minus);

		echo '</td>';
		echo '<td class="num">'.$team->diff.'</td>';
	}


	/**
	 * re-calculate points
	 *
	 * @param array $points
	 * @param int $team_id
	 * @param array $rule
	 * @return array with modified points
	 */
	function calculatePoints( $points, $team_id, $rule )
	{
		extract($rule);
		
		$points['plus'] = $points['plus'] - $num_won_overtime * $forwin + $num_won_overtime * $forwin_overtime + $num_lost_overtime * $forloss_overtime;
		$points['minus'] = $points['minus'] - $num_lost_overtime * $forwin + $num_won_overtime * $forloss_overtime + $num_lost_overtime * $forwin_overtime;
	
		return $points;
	}
	
	
	/**
	 * calculate goals. Penalty is not counted in statistics
	 *
	 * @param int $team_id
	 * @param string $option
	 * @return int
	 */
	function calculateGoalStatistics( $team_id )
	{
		global $wpdb, $leaguemanager;
		
		$goals = array( 'plus' => 0, 'minus' => 0 );
				
		$matches = $wpdb->get_results( "SELECT `home_points`, `away_points`, `custom` FROM {$wpdb->leaguemanager_matches} WHERE `home_team` = '".$team_id."'" );
		if ( $matches ) {
			foreach ( $matches AS $match ) {
				$custom = maybe_unserialize($match->custom);
				$home_goals = $match->home_points;
				$away_goals = $match->away_points;
				
				$goals['plus'] += $home_goals;
				$goals['minus'] += $away_goals;
			}
		}
		
		$matches = $wpdb->get_results( "SELECT `home_points`, `away_points`, `custom` FROM {$wpdb->leaguemanager_matches} WHERE `away_team` = '".$team_id."'" );
		if ( $matches ) {
			foreach ( $matches AS $match ) {
				$custom = maybe_unserialize($match->custom);
				$home_goals = $match->home_points;
				$away_goals = $match->away_points;
				
				$goals['plus'] += $away_goals;
				$goals['minus'] += $home_goals;
			}
		}
		
		return $goals;
	}
}

$us_football = new LeagueManagerUSFootball();
?>
