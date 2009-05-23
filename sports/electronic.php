<?php
/**
 * Electronic Class 
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright 	Copyright 2008-2009
*/
class LeagueManagerElectronic extends LeagueManager
{
	/**
	 * sports keys
	 *
	 * @var string
	 */
	var $keys = array();


	/**
	 * load specific settings
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		$this->keys = array( 'shooter' => __( 'PC &#8211; Shooter', 'leaguemanager' ), 'strategy' => __( 'PC &#8211; Strategy', 'leaguemanager'), 'role-playing-game' => __( 'PC &#8211; Role-Playing Game', 'leaguemanager') );

		add_filter( 'leaguemanager_sports', array(&$this, 'sports') );
		foreach ( $this->keys AS $key => $name )
			add_filter( 'rank_teams_'.$key, array(&$this, 'rankTeams') );
	}
	function LeagueManagerElectronic()
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
	 * rank Teams
	 *
	 * @param array $teams
	 * @return array of teams
	 */
	function rankTeams( $teams )
	{
		foreach ( $teams AS $key => $row ) {
			$points[$key] = $row->points['plus'];
			$done[$key] = $row->done_matches;
		}

		array_multisort( $points, SORT_DESC, $done, SORT_ASC, $teams );
		return $teams;
	}
}

$electronic = new LeagueManagerElectronic();
?>
