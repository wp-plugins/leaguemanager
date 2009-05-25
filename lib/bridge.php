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
	 * @param none
	 * @return void
	 */
	function loadScripts()
	{
		echo "\n<script type='text/javascript'>";
		echo "\nvar lmBridge = true;";
		echo "\nvar lmTeamRoster = \"";
			foreach ($this->getPlayer() AS $id => $player)
				echo "<option value='".$player->name."'>".$player->name."</option>";
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
	 * get datasets from projectmanager
	 *
	 * @param int $project_id
	 * @return array
	 */
	function getPlayer()
	{
		global $wpdb;
		$result = $wpdb->get_results( "SELECT `id`, `name` FROM {$wpdb->projectmanager_dataset} WHERE `project_id` = {$this->project_id}" );
		if ( $result ) {
			$players = array();
			foreach ( $result AS $player ) {
				$players[$player->id] = $player;
			}
			
			return $players;
		}
		
		return false;
	}
	
	
	/**
	 * get player dropdown selection
	 *
	 * @param mixed $selected
	 * @return HTML dropdown menu
	 */
	function getPlayerSelection( $selected, $id )
	{
		if ( $players = $this->getPlayer() ) {
			$out = "<select id='$id' name='$id' style='display: block; margin: 0.5em auto;'>";
			foreach ( $players AS $id => $player ) {
				$player->name = stripslashes($player->name);
				$checked = ( $selected == $player->name ) ? ' selected="selected"' : '';
				$out .= "<option value='".$player->name."'".$selected.">".$player->name."</option>";
			}
			$out .= "</select>";
		}
		return $out;
	}
}
