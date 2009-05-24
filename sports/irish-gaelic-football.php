<?php
/**
 * Irish Gaelic Football Class 
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright 	Copyright 2008-2009
*/
class LeagueManagerGaelicFootball extends LeagueManager
{

	/**
	 * sports key
	 *
	 * @var string
	 */
	var $key = 'irish-gaelic-football';


	/**
	 * load specifif settings
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		add_filter( 'leaguemanager_sports', array(&$this, 'sports') );

		add_filter( 'leaguemanager_export_matches_header_'.$this->key, array(&$thhis, 'exportMatchesHeader') );
		add_filter( 'leaguemanager_export_matches_data_'.$this->key, array(&$this, 'exportMatchesData'), 10, 2 );
		add_filter( 'leaguemanager_import_matches_'.$this->key, array(&$this, 'importMatches'), 10, 3 );

		add_action( 'matchtable_header_'.$this->key, array(&$this, 'displayMatchesHeader'), 10, 0);
		add_action( 'matchtable_columns_'.$this->key, array(&$this, 'displayMatchesColumns') );
	}
	function LeagueManagerGaelicFootball()
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
		$sports[$this->key] = __( 'Irish Gaelic Football', 'leaguemanager' );
		return $sports;
	}


	/**
	 * display Table Header for Match Administration
	 *
	 * @param none
	 * @return void
	 */
	function displayMatchesHeader()
	{
		echo '<th>'.__( 'Goals', 'leaguemanager' ).'</th><th>'.__( 'Points', 'leaguemanager' ).'</th>';
	}


	/**
	 * display Table columns for Match Administration
	 *
	 * @param object $match
	 * @return void
	 */
	function displayMatchesColumns( $match )
	{
		echo '<td><input class="points" type="text" size="2" id="num_goals_home_'.$match->id.'" name="custom['.$match->id.'][num_goals][home]" value="'.$match->num_goals['home'].'" /> : <input clas="points" type="text" size="2" id="num_goals_away_'.$match->id.'" name="custom['.$match->id.'][num_goals][away]" value="'.$match->num_goals['away'].'" /></td>';
		echo '<td><input class="points" type="text" size="2" id="num_points_home_'.$match->id.'" name="custom['.$match->id.'][num_points][home]" value="'.$match->num_points['home'].'" /> : <input class="points" type="text" size="2" id="num_points_away_'.$match->id.'" name="custom['.$match->id.'][num_points][away]" value="'.$match->num_points['away'].'" /></td>';
	}


	/**
	 * export matches header
	 *
	 * @param string $content
	 * @return the content
	 */
	function exportMatchesHeader( $content )
	{
		$content .= "\t".__( 'Goals', 'leaguemanager' )."\t".__('Points', 'leaguemanager');
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
		if ( isset($match->num_goals) )
			$content .= "\t".sprintf("%d-%d", $match->num_goals['home'], $match->num_goals['away'])."\t".sprintf("%d-%d", $match->num_points['home'], $match->points['away']);
		else
			$content .= "\t\t";

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
		$num_goals = explode("-", $line[8]);
		$num_pints = explode("-", $line[9]);
		$custom[$match_id]['num_goals'] = array( 'home' => $num_goals[0], 'away' => $num_goals[1] );
		$custom[$match_id]['num_points'] = array( 'home' => $num_points[0], 'away' => $num_points[1] );

		return $custom;
	}
}

$gaelic_football = new LeagueManagerGaelicFootball();
?>
