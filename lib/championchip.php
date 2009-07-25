<?php
/**
 * Core class for the WordPress plugin LeagueManager
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright 	Copyright 2008-2009
*/
class LeagueManagerChampionchip extends LeagueManager
{
	/**
	 * page key
	 *
	 * @var string
	 */
	var $page = 'championchip';


	/**
	 * initialize Champtionchip Mode
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		add_filter( 'league_menu_'.$this->page, array(&$this, 'leagueMenu'), 10, 3 );
		add_filter( 'leaguemanager_modes', array(&$this, 'modes') );
		add_action( 'league_settings_'.$this->page, array(&$this, 'settingsPage') );
	}
	function LeagueManagerChampionchip()
	{
		$this->__construct();
	}


	/**
	 * extend league menu
	 *
	 * @param array $menu
	 * @param int $league_id
	 * @param mixed $season
	 * @return void
	 */
	function leagueMenu( $menu, $league_id, $season )
	{
		global $leaguemanager;
		$league = $leaguemanager->getLeague($league_id);

		if ( $league->mode == $this->page )
			$menu[$this->page] = array( 'title' => __( 'Championchip', 'leaguemanager'), 'file' => LEAGUEMANAGER_PATH . '/admin/championchip.php' );
	
		return $menu;
	}


	/**
	 * add championchip mode
	 *
	 * @param array $modes
	 * @return array
	 */
	function modes( $modes )
	{
		$modes[$this->page] = __( 'Championchip', 'leaguemanager' );
		return $modes;
	}


	/**
	 * add settings
	 *
	 * @param object $league
	 * @return void
	 */
	function settingsPage( $league )
	{
		echo '<tr>';
		echo '<th scope="row"><label for="num_advance">'.__('Teams Advance', 'leaguemanager').'</label></th>';
		echo '<td><input type="text" size="3" id="num_advance" name="settings[num_advance]" value="'.$league->num_advance.'" /></td>';
		echo '</tr>';
	}


	/**
	 * get name of final depending on number of teams
	 *
	 * @param string $key
	 * @return the name
	 */
	function getFinalName( $key )
	{
		if ( 'final' == $key )
			return __( 'Final', 'leaguemanager' );
		elseif ( 'semi' == $key )
			return __( 'Semi Final', 'leaguemanager' );
		elseif ( 'quarter' == $key )
			return __( 'Quarter Final', 'leaguemanager' );
		else {
			$tmp = explode("-", $key);
			return sprintf(__( 'Last-%d', 'leaguemanager'), $tmp[1]);
		}
	}
	
	
	/**
	 * get key of final depending on number of teams
	 *
	 * @param int $num_teams
	 * @return the key
	 */
	function getFinalKey( $num_teams )
	{
		if ( 2 == $num_teams )
			return 'final';
		elseif ( 4 == $num_teams )
			return 'semi';
		elseif ( 8 == $num_teams )
			return 'quarter';
		else
			return 'last-'.$num_teams;
	}
	
	
	/**
	 * get array of teams for finals
	 *
	 * @param int $num_matches
	 * @param boolean $start true if first round of finals
	 * @param string $round 'prev' | 'current'
	 * @return array of teams
	 */
	function getFinalTeams( $num_matches, $start, $output = 'OBJECT' )
	{
		// set matches of previous round
		$num_matches = $num_matches * 2; 
			
		$num_teams = $num_matches * 2;
		
		$num_advance = 2; // First and Second of each group qualify for finals
		$teams = array();
		if ( !$start ) {
			for ( $x = 1; $x <= $num_matches; $x++ ) {
				$key = $this->getFinalKey($num_teams);
				if( $output == 'ARRAY' ) {
					$teams['1-'.$key.'-'.$x] = "Winner ".$this->getFinalName($key)." ".$x;
				} else {
					$data = array( 'id' => '1-'.$key.'-'.$x, 'title' => "Winner ".$this->getFinalName($key)." ".$x );
					$teams[] = (object) $data;
				}
			}
		} else {
			for ( $group = 1; $group <= $this->getNumGroups( $this->league_id ); $group++ ) {
				for ( $a = 1; $a <= $num_advance; $a++ ) {
					if( $output == 'ARRAY' ) {
						$teams[$a.'-'.$group] = $a.'. Group '.$this->getGroupCharacter($group);
					} else {
						$data = array( 'id' => $a.'-'.$group, 'title' => $a.'. Group '.$this->getGroupCharacter($group) );
						$teams[] = (object) $data;
					}
				}
			}
		}
		return $teams;
	}
	
	
	/**
	 * get ascii text for given group
	 *
	 * @param int $group
	 * @param boolean $lc outputs lowercase character if true
	 * @return character
	 *
	 *  See http://www.asciitable.com/ for an ASCII Table
	 */
	function getGroupCharacter( $group, $lc = false )
	{
		$ascii = $lc ? $group + 96 : $group + 64;
		return chr($ascii);
	}
	
	
	/**
	 * get number of groups for championchip
	 *
	 * @param int $league_id
	 * @return int number of groups
	 */
	function getNumGroups( $league_id )
	{
		 return 8;
	}
}

global $championchip;
$championchip = new LeagueManagerChampionchip();
?>
