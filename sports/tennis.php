<?php
/**
 * Tennis Class 
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright 	Copyright 2008-2009
*/
class LeagueManagerTennis extends LeagueManager
{

	/**
	 * sports key
	 *
	 * @var string
	 */
	var $key = 'tennis';


	/**
	 * load specifif settings
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		add_filter( 'leaguemanager_sports', array(&$this, 'sports') );
		add_filter( 'rank_teams_'.$this->key, array(&$this, 'rankTeams') );
		add_filter( 'team_points_'.$this->key, array(&$this, 'calculatePoints'), 10, 3 );

		add_filter( 'leaguemanager_point_rules_list', array(&$this, 'getPointRuleList') );
		add_filter( 'leaguemanager_point_rules',  array(&$this, 'getPointRules') );

		add_filter( 'leaguemanager_export_matches_header_'.$this->key, array(&$this, 'exportMatchesHeader') );
		add_filter( 'leaguemanager_export_matches_data_'.$this->key, array(&$this, 'exportMatchesData'), 10, 2 );
		add_filter( 'leaguemanager_import_matches_'.$this->key, array(&$this, 'importMatches'), 10, 3 );
		add_filter( 'leaguemanager_export_teams_header_'.$this->key, array(&$this, 'exportTeamsHeader') );
		add_filter( 'leaguemanager_export_teams_data_'.$this->key, array(&$this, 'exportTeamsData'), 10, 2 );
		add_filter( 'leaguemanager_import_teams_'.$this->key, array(&$this, 'importTeams'), 10, 2 );

		add_action( 'matchtable_header_'.$this->key, array(&$this, 'displayMatchesHeader'), 10, 0);
		add_action( 'matchtable_columns_'.$this->key, array(&$this, 'displayMatchesColumns') );
		add_action( 'leaguemanager_standings_header_'.$this->key, array(&$this, 'displayStandingsHeader') );
		add_action( 'leaguemanager_standings_columns_'.$this->key, array(&$this, 'displayStandingsColumns'), 10, 2 );
		add_action( 'team_edit_form_'.$this->key, array(&$this, 'editTeam') );

		add_action( 'leaguemanager_save_standings_'.$this->key, array(&$this, 'saveStandings') );

		add_action( 'league_settings_'.$this->key, array(&$this, 'leagueSettings') );
	}
	function LeagueManagerSoccer()
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
		$sports[$this->key] = __( 'Tennis', 'leaguemanager' );
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
		$rules[$this->key] = __('Tennis', 'leaguemanager');

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
		$rules[$this->key] = array( 'forwin' => 3, 'fordraw' => 0, 'forloss' => 0, 'forwin_split' => 2, 'forloss_split' => 1 );

		return $rules;
	}


	/**
	 * add league settings
	 *
	 * @param object $league
	 * @return void
	 */
	function leagueSettings( $league )
	{
		echo "<tr valign='top'>";
		echo "<th scope='row'><label for='num_sets'>".__('Number of Sets', 'leaguemanager')."</label></th>";
		echo "<td><input type='text' name='settings[num_sets]' id='num_sets' value='".$league->num_sets."' size='3' /></td>";
		echo "</tr>";
	}


	/**
	 * calculate Points: add match score
	 *
	 * @param array $points
	 * @param int $team_id
	 * @param array $rule
	 */
	function calculatePoints( $points, $team_id, $rule )
	{
		global $leaguemanager;

		extract($rule);

		$data = $this->getStandingsData($team_id);
		$points['plus'] = $data['straight_set']['win'] * $forwin + $data['split_set']['win'] * $forwin_split + $data['split_set']['lost'] * $forloss_split;
		$points['minus'] = $data['straight_set']['lost'] * $forwin + $data['split_set']['win'] * $forloss_split + $data['split_set']['lost'] * $forwin_split;

		return $points;
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
			$points[$key] = $row->points['plus'];
			$games_allowed[$key] = $row->games_allowed;
		}

		array_multisort( $points, SORT_DESC, $games_allowed, SORT_ASC, $teams );
		return $teams;
	}


	/**
	 * save custom standings
	 *
	 * @param int $team_id
	 * @return void
	 */
	function saveStandings( $team_id )
	{
		global $wpdb, $leaguemanager;

		$team = $wpdb->get_results( "SELECT `custom` FROM {$wpdb->leaguemanager_teams} WHERE `id` = {$team_id}" );
		$custom = maybe_unserialize($team->custom);
		$custom = $this->getStandingsData($team_id, $custom);

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `custom` = '%s' WHERE `id` = '%d'", maybe_serialize($custom), $team_id ) );
	}


	/**
	 * get standings data for given team
	 *
	 * @param int $team_id
	 * @param array $data
	 * @return array number of runs for and against as assoziative array
	 */
	function getStandingsData( $team_id, $data = array() )
	{
		global $leaguemanager;
		
		$data['straight_set'] = $data['split_set'] = array( 'win' => 0, 'lost' => 0 );
		$data['games_allowed'] = 0;

		$league = $leaguemanager->getCurrentLeague();

		$matches = $leaguemanager->getMatches( "( `home_team` = {$team_id} OR `away_team` = {$team_id} )" );
		foreach ( $matches AS $match ) {
			$index = ( $team_id == $match->home_team ) ? 'player2' : 'player1';

			// First check for Split Set, else it's straight set
			if ( $match->sets[3]['player1'] != '' && $match->sets[3]['player2'] != '' ) {
				if ( $match->winner_id == $team_id ) {
					$data['split_set']['win'] += 1;
					for ( $j = 1; $j <= $league->num_sets-1; $j++  ) {
						$data['games_allowed'] += $match->sets[$j][$index];
					}
				} elseif ( $match->loser_id == $team_id ) {
					$data['split_set']['lost'] += 1;
					for ( $j = 1; $j <= $league->num_sets-1; $j++  ) {
						$data['games_allowed'] += $match->sets[$j][$index];
					}
					$data['games_allowed'] += 1;
				}
			} else {
				if ( $match->winner_id == $team_id ) {
					$data['straight_set']['win'] += 1;
					for ( $j = 1; $j <= $league->num_sets-1; $j++  ) {
						$data['games_allowed'] += $match->sets[$j][$index];
					}
				} elseif ( $match->loser_id == $team_id ) {
					$data['straight_set']['lost'] += 1;
					for ( $j = 1; $j <= $league->num_sets-1; $j++  ) {
						$data['games_allowed'] += $match->sets[$j][$index];
					}
				}
			}
		}
		
		return $data;
	}


	/**
	 * extend header for Standings Table in Backend
	 *
	 * @param none
	 * @return void
	 */
	function displayStandingsHeader()
	{
		echo '<th class="num">'.__( 'Straight Set', 'leaguemanager' ).'</th><th>'.__( 'Split Set', 'leaguemanager' ).'</th><th>'.__( 'Games Allowed', 'leaguemanager' ).'</th>';
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
		if ( is_admin() && $rule == 'manual' )
			echo '<td><input type="text" size="2" name="custom['.$team->id.'][straight_set][win]" value="'.$team->straight_set['win'].'" />:<input type="text" size="2" name="custom['.$team->id.'][straight_set][lost]" value="'.$team->straight_set['lost'].'" /></td><td><input type="text" size="2" name="custom['.$team->id.'][split_set][win]" value="'.$team->split_set['win'].'" />:<input type="text" size="2" name="custom['.$team->id.'][split_set][lost]" value="'.$team->split_set['lost'].'" /></td><td><input type="text" size="2" name="custom['.$team->id.'][games_allowed]" value="'.$team->games_allowed.'" /></td>';
		else
			echo '<td class="num">'.sprintf("%d-%d", $team->straight_set['win'], $team->straight_set['lost']).'</td><td class="num">'.sprintf("%d-%d", $team->split_set['win'], $team->split_set['lost']).'</td><td class="num">'.$team->games_allowed.'</td>';
	}


	/**
	 * display hidden fields in team edit form
	 *
	 * @param object $team
	 * @return void
	 */
	function editTeam( $team )
	{
		echo '<input type="hidden" name="custom[straight_set][win]" value="'.$team->straight_set['win'].'" /><input type="hidden" name="custom[straight_set][lost]" value="'.$team->straight_set['lost'].'" /><input type="hidden" name="custom[split_set][win]" value="'.$team->split_set['win'].'" /><input type="hidden" name="custom[split_set][lost]" value="'.$team->split_set['lost'].'" /><input type="hidden" name="custom[games_allowed]" value="'.$team->games_allowed.'" />';
	}


	/**
	 * display Table Header for Match Administration
	 *
	 * @param none
	 * @return void
	 */
	function displayMatchesHeader()
	{
		global $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();
		echo '<th colspan="'.$league->num_sets.'" style="text-align: center;">'.__( 'Sets', 'leaguemanager' ).'</th>';
	}


	/**
	 * display Table columns for Match Administration
	 *
	 * @param object $match
	 * @return void
	 */
	function displayMatchesColumns( $match )
	{
		global $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();

		for ( $i = 1; $i <= $league->num_sets; $i++ ) {
			echo '<td><input class="points" type="text" size="2" id="set_'.$match->id.'_'.$i.'_player1" name="custom['.$match->id.'][sets]['.$i.'][player1]" value="'.$match->sets[$i]['player1'].'" /> : <input class="points" type="text" size="2" id="set_'.$match->id.'_'.$i.'_player2" name="custom['.$match->id.'][sets]['.$i.'][player2]" value="'.$match->sets[$i]['player2'].'" /></td>';
		}
	}


	/**
	 * export matches header
	 *
	 * @param string $content
	 * @return the content
	 */
	function exportMatchesHeader( $content )
	{
		global $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();

		$content .= "\t"._c( 'Sets', 'leaguemanager' ).str_repeat("\t", $league->num_sets-1);
		return $content;
	}


	/**
	 * export matches data
	 *
	 * @param string $content
	 * @param object $match
	 * @return the content
	 */
	function exportMatchesData( $content, $match )
	{
		global $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();

		if ( isset($match->sets) ) {
			foreach ( $match->sets AS $j => $set ) {
				$content .= "\t".implode("-", $set);
			}
		} else {
			$content .= str_repeat("\t", $league->num_sets);
		}

		return $content;
	}

	
	/**
	 * import matches
	 *
	 * @param array $custom
	 * @param array $line elements start at index 8
	 * @param int $match_id
	 * @return array
	 */
	function importMatches( $custom, $line, $match_id )
	{
		for( $x = 8; $x <= 10; $x++ ) {
			$set = explode("-",$line[$x]);
			$custom[$match_id]['sets'][] = array( 'player1' => $set[0], 'player2' => $set[1] );
		}

		return $custom;
	}


	/**
	 * export teams header
	 *
	 * @param string $content
	 * @return the content
	 */
	function exportTeamsHeader( $content )
	{
		$content .= "\t".__( 'Straight Set Win', 'leaguemanager' )."\t".__('Split Set Win', 'leaguemanager')."\t".__('Straight Set Lost')."\t".__('Split Set Lost', 'leaguemanager')."\t".__('Games Allowed', 'leaguemanager');
		return $content;
	}


	/**
	 * export teams data
	 *
	 * @param string $content
	 * @param object $team
	 * @return the content
	 */
	function exportTeamsData( $content, $team )
	{
		if ( isset($team->straight_set) )
			$content .= "\t".$team->straight_set['win']."\t".$team->split_set['win']."\t".$team->straight_set['lost']."\t".$team->split_set['lost']."\t".$team->games_allowed;
		else
			$content .= "\t\t\t\t\t";

		return $content;
	}

	
	/**
	 * import teams
	 *
	 * @param array $custom
	 * @param array $line elements start at index 8
	 * @return array
	 */
	function importTeams( $custom, $line )
	{
		$custom['straight_set'] = array( 'win' => $line[8], 'lost' => $line[10] );
		$custom['split_set'] = array( 'win' => $line[9], 'lost' => $line[11] );
		$custom['games_allowed'] = $line[12];

		return $custom;
	}
}

$tennis = new LeagueManagerTennis();
?>
