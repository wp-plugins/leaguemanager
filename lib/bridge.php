<?php
/**
 * Bridge class for the WordPress plugin ProjectManager
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright 	Copyright 2008-2009
*/

class LeagueManagerBridge extends LeagueManager
{
	/**
	 * ID of project to bridge
	 *
	 * @var int
	 */
	var $project_id;
	
	
	/**
	 * initialize bridge
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		add_filter( 'projectmanager_formfields', array($this, 'projectManagerFormFields') );
	}
	function LeagueManagerBrdige()
	{
		$this->__construct();
	}
	
	
	/**
	 * load scripts
	 *
	 * @param array $roster
	 * @return void
	 */
	function loadScripts( $roster )
	{
		echo "\n<script type='text/javascript'>";
		echo "\nvar lmBridge = true;";
		echo "\nvar lmTeamRoster = \"";
			foreach ( $roster AS $team => $players ) {
				echo "<optgroup label='".$team."'>";
				foreach ( $players AS $player )
					echo "<option value='".$player->name."'>".$player->name."</option>";
				echo "</optgroup>";
			}
		echo "\";\n";
		echo "</script>\n";
	}
	
	
	/**
	 * set project ID
	 *
	 * @param int $project_id
	 * @return void
	 */
	function setProjectID( $project_id )
	{
		$this->project_id = $project_id;
	}
	
	
	/**
	 * filter for ProjectManager Formfields
	 *
	 * @param array $formfields
	 * @return array
	 */
	function projectManagerFormFields( $formfields )
	{
		$formfields['goals'] = array( 'name' => __('Goals', 'leaguemanager'), 'callback' => array($this, 'getNumGoals'), 'args' => array() );
		return $formfields;
	}
	
	
	/**
	 * get number of goals for player (of all matches)
	 *
	 * @param array $player
	 * @return int
	 */
	function getNumGoals( $player )
	{
		$goals = 0;
		if ( $matches = parent::getMatches() ) {
			foreach ( $matches AS $match ) {
				if (isset($match->goals)) {
					foreach ( $match->goals AS $goal ) {
						if ( $player['name'] == $goal[1] )
							$goals++;
					}
				} else{
					$goals = false;
				}
			}
		}
		return $goals;
	}
	
	
	/**
	 * get Team Roster
	 *
	 * @param int $project_id
	 * @param false|int $cat_id
	 * @return array
	 */
	function getTeamRoster( $project_id = false, $cat_id = -1 )
	{
		global $wpdb, $projectmanager;

		if ( $cat_id == -1 ) $cat_id = false;
		if ( $project_id ) {
			$projectmanager->initialize($project_id);
			$projectmanager->setCatID($cat_id);

			$search = "`project_id` = {$project_id} ";
			if ( $cat_id ) $search .= $projectmanager->getCategorySearchString();

			return $wpdb->get_results( "SELECT `id`, `name` FROM {$wpdb->projectmanager_dataset} WHERE $search" );
		}

		return false;
	}
	
	
	/**
	 * get team roster
	 *
	 * @param mixed $selected
	 * @return HTML dropdown menu
	 */
	function getTeamRosterSelection( $roster, $selected, $id )
	{
		$out = "<select id='$id' name='$id' style='display: block; margin: 0.5em auto;'>";
		foreach ( $roster AS $team => $players ) {
			$out .= "<optgroup label='".$team."'>";
			foreach ( $players AS $id => $player ) {
				$player->name = stripslashes($player->name);
				$checked = ( $selected == $player->name ) ? ' selected="selected"' : '';
				$out .= "<option value='".$player->name."'".$selected.">".$player->name."</option>";
			}
			$out .= "</optgroup>";
		}
		$out .= "</select>";

		return $out;
	}
}
