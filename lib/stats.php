<?php
/**
 * Statistics class for the WordPress plugin LeagueManager
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright 	Copyright 2008-2009
*/
class LeagueManagerStats extends LeagueManager
{
	/**
	 * sports that have match statistics
	 *
	 * @var array
	 */
	var $sports = array();


	/**
	 * page key
	 *
	 * @var string
	 */
	var $page = 'matchstats';


	/**
	 * league object
	 *
	 * @var object
	 */
	var $league;


	/**
	 * ID of current league
	 *
	 * @var int
	 */
	var $league_id;


	/**
	 * initialize class
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		global $leaguemanager;

		add_action( 'league_settings', array(&$this, 'settings') );
		
		$league = $leaguemanager->getCurrentLeague();

		if ( isset($league->use_stats) && 1 == $league->use_stats )
			$this->addSport($league->sport);

		if ( !empty($this->sports) ) {
			foreach ( $this->sports AS $sport ) {
				add_filter( 'matchtable_header_'.$sport, array(&$this, 'displayMatchesHeader'), 10, 0);
				add_filter( 'matchtable_columns_'.$sport, array(&$this, 'displayMatchesColumns') );
				add_filter( 'league_menu_'.$sport, array(&$this, 'leagueMenu') );
			}
		}
	}
	function LeagueManagerStats()
	{
		$this->__construct();
	}


	/**
	 * add sports type
	 *
	 * @param string $sport
	 * @return void
	 */
	function addSport( $sport )
	{
		array_push($this->sports, $sport);
	}


	/**
	 * add settings
	 *
	 * @param object $league
	 * @return void
	 */
	function settings( $league )
	{
		echo '<tr>';
		echo '<th scope="row"><label for="use_stats">'.__('Activate Match Statistics', 'leaguemanager').'</label></th>';
		$checked = ( isset($league->use_stats) && 1 == $league->use_stats ) ? ' checked="checked"' : '';
		echo '<td><input type="checkbox" id="use_stats" name="settings[use_stats]" value="1"'.$checked.' /></td>';
		echo '</tr>';
	}


	/**
	 * display Table Header for Match Administration
	 *
	 * @param none
	 * @return void
	 */
	function displayMatchesHeader()
	{
		echo '<th>'.__('Stats', 'leaguemanager').'</th>';
	}


	/**
	 * display Table columns for Match Administration
	 *
	 * @param object $match
	 * @return void
	 */
	function displayMatchesColumns( $match )
	{
		echo '<td><a href="admin.php?page=leaguemanager&subpage='.$this->page.'&league_id='.$match->league_id.'&match_id='.$match->id.'">'.__('Stats', 'leaguemanager').'</td>';
	}


	/**
	 * extend league menu
	 *
	 * @param array $menu
	 * @return array
	 */
	function leagueMenu( $menu )
	{
		$menu[$this->page] = array( 'title' => __('Match Statistics', 'leaguemanager'), 'file' => LEAGUEMANAGER_PATH . '/admin/matchstats.php', 'show' => false );
		return $menu;
	}


	/**
	 * save match statistics
	 *
	 * The first parameter is simply the match ID. The second is a multidimensional array holding all statistics.
	 *
	 * @param int $match_id
	 * @param array $stats
	 * @return string
	 */
	function save( $match_id, $stats )
	{
		global $wpdb;
		$match = $wpdb->get_results( "SELECT `custom` FROM {$wpdb->leaguemanager_matches} WHERE `id` = {$match_id}" );
		$custom = $match->custom;
		foreach ( $stats AS $stat => $data ) {
			$custom[$stat] = array_values($data);
		}
		$custom['hasStats'] = true;

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `custom` = '%s' WHERE `id` = '%s'", maybe_serialize($custom), $match_id ) );

		parent::setMessage(__('Saved Statstics', 'leaguemanager'));
		parent::printMessage();
	}
}
?>
