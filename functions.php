<?php
/**
 * display widget statically
 *
 * @param int $league_id ID of league
 * @param mixed $season (optional)
 * @param int $number (optional), needed for multiple widgets
 */
function leaguemanager_display_widget( $league_id, $season = false, $number = 1 ) {
	$widget = new LeagueManagerWidget();
	
	echo "<ul id='leaguemanger-widget-".$league_id."' class='leaguemanager_widget'>";
	$widget->display( array( 'league_id' => $league_id, 'season' => $season ), array('number' => $number) );
	echo "</ul>";
}


/**
 * display standings table manually
 *
 * @param int $league_id League ID
 * @param array $args assoziative array of parameters, see default values (optional)
 * @return void
 */
function leaguemanager_standings( $league_id, $args = array() ) {
	global $lmShortcodes;
	$defaults = array( 'season' => false, 'template' => 'extend', 'logo' => 'true' );
	$args = array_merge($defaults, $args);
	extract($args, EXTR_SKIP);
	echo $lmShortcodes->showStandings( array('league_id' => $league_id, 'logo' => $logo, 'season' => $season, 'template' => $template) );
}


/**
 * display crosstable table manually
 *
 * @param int $league_id
 * @param array $args assoziative array of parameters, see default values (optional)
 * @return void
 */
function leaguemanager_crosstable( $league_id, $args = array() ) {
	global $lmShortcodes;
	$defaults = array('season' => false, 'template' => '', 'mode' => '');
	$args = array_merge($defaults, $args);
	extract($args, EXTR_SKIP);
	echo $lmShortcodes->showCrosstable( array('league_id' => $league_id, 'mode' => $mode, 'template' => $temaplate, 'season' => $season) );
}


/**
 * display matches table manually
 *
 * @param int $league_id
 * @param array $args assoziative array of parameters, see default values (optional)
 * @return void
 */
function leaguemanager_matches( $league_id, $args = array() ) {
	global $lmShortcodes;
	$defaults = array('season' => false, 'template' => '', 'mode' => '', 'archive' => false);
	$args = array_merge($defaults, $args);
	extract($args, EXTR_SKIP);
	echo $lmShortcodes->showMatches( array('league_id' => $league_id, 'mode' => $mode, 'season' => $season, 'archive' => $archive) );
}


/**
 * display one match manually
 *
 * @param int $match_id
 * @param array $args additional arguments as assoziative array (optional)
 * @return void
 */
function leaguemanager_match( $match_id, $args = array() ) {
	global $lmShortcodes;
	$defaults = array('template' => '');
	$args = array_merge($defaults, $args);
	extract($args, EXTR_SKIP);

	echo $lmShortcodes->showMatch( array('id' => $match_id, 'template' => $template) );
}


/**
 * display team list manually
 *
 * @param int|string $league_id
 * @param array $args additional arguments as assoziative array (optional)
 * @return void
 */
function leaguemanager_teams( $league_id, $args = array() ) {
	global $lmShortcodes;
	$defaults = array('season' => false, 'template' => '');
	$args = array_merge($defaults, $args);
	extract($args, EXTR_SKIP);

	echo $lmShortcodes->showTeams( array('league_id' => $league_id, 'season' => $season, 'template' => $template) );
}


/**
 * display one team manually
 *
 * @param int $team_id
 * @param array $args additional arguments as assoziative array (optional)
 * @return void
 */
function leaguemanager_team( $team_id, $args = array() ) {
	global $lmShortcodes;
	$defaults = array('template' => '');
	$args = array_merge($defaults, $args);
	extract($args, EXTR_SKIP);

	echo $lmShortcodes->showTeam( array('id' => $match_id, 'template' => $template) );
}
/**
 * Ajax Response to set match index in widget
 *
 * @param none
 * @return void
 */
function leaguemanager_get_match_box() {
	global $lmWidget;
	$current = $_POST['current'];
	$element = $_POST['element'];
	$operation = $_POST['operation'];
	$league_id = $_POST['league_id'];
	$match_limit = ( $_POST['match_limit'] == 'false' ) ? false : $_POST['match_limit'];
	$widget_number = $_POST['widget_number'];
	$season = $_POST['season'];

	if ( $operation == 'next' )
		$index = $current + 1;
	elseif ( $operation == 'prev' )
		$index = $current - 1;
	
	$lmWidget->setMatchIndex( $index, $element );

	if ( $element == 'next' ) {
		$parent_id = 'next_matches_'.$widget_number;
		//$el_id = 'next_match_box';
		$match_box = $lmWidget->showNextMatchBox($widget_number, $league_id, $season, $match_limit, false);
	} elseif ( $element == 'prev' ) {
		$parent_id = 'prev_matches_'.$widget_number;
		//$el_id = 'prev_match_box';
		$match_box = $lmWidget->showPrevMatchBox($widget_number, $league_id, $season, $match_limit, false);
	}

	die( "jQuery('div#".$parent_id."').fadeOut('fast', function() {
		jQuery('div#".$parent_id."').html('".addslashes_gpc($match_box)."').fadeIn('fast');
	});");
}


/**
 * SACK response to manually set team ranking
 *
 * @since 2.8
 */
function leaguemanager_save_team_standings() {
	global $wpdb, $lmLoader, $leaguemanager;
	$ranking = $_POST['ranking'];
	$ranking = $lmLoader->adminPanel->getRanking($ranking);
	foreach ( $ranking AS $rank => $team_id ) {
		$old = $leaguemanager->getTeam( $team_id );
		$oldRank = $old->rank;

		if ( $oldRank != 0 ) {
			if ( $rank == $oldRank )
				$status = '&#8226;';
			elseif ( $rank < $oldRank )
				$status = '&#8593';
			else
				$status = '&#8595';
		} else {
			$status = '&#8226;';
		}

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `rank` = '%d', `status` = '%s' WHERE `id` = '%d'", $rank, $status, $team_id ) );
	}
}

/**
* SACK response to manually set team ranking
*
* @since 2.8
*/
function leaguemanager_save_add_points() {
	global $wpdb;
	$team_id = intval($_POST['team_id']);
	$points = intval($_POST['points']);
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `add_points` = '%d' WHERE `id` = '%d'", $points, $team_id ) );

	die("Leaguemanager.doneLoading('loading_".$team_id."')");
}


/**
 * SACK response to get team data from database and insert into team edit form
 *
 * @since 2.9
 */
function add_team_from_db() {
	global $leaguemanager;

	$team_id = (int)$_POST['team_id'];
	$team = $leaguemanager->getTeam( $team_id );
	
	$roster = '';
	if ( $leaguemanager->hasBridge() ) {
		global $projectmanager;
		$html = '<select size="1" name="roster" id="roster" onChange="Leaguemanager.toggleTeamRosterGroups(this.value);return false;"><option value="">'.__('None','leaguemanager').'</option>';
		foreach ( $projectmanager->getProjects() AS $dataset ) {
			$selected = ( $dataset->id == $team->roster['id'] ) ? ' selected="selected"' : '';
			$html .= '<option value="'.$dataset->id.'"'.$selected.'>'.$dataset->title.'</option>';
		}
		$html .= '</select>';
		$roster = "jQuery('span#rosterbox').fadeOut('fast', function() {
				jQuery('span#rosterbox').html('".addslashes_gpc($html)."').fadeIn('fast')
			   });";

		if ( isset($team->roster['cat_id']) ) {
			$project = $projectmanager->getProject($team->roster['id']);
			$category = $project->category;

			if ( !empty($category) ) {
				$html = wp_dropdown_categories(array('hide_empty' => 0, 'name' => 'roster_group', 'orderby' => 'name', 'echo' => 0, 'show_option_none' => __('Select Group (Optional)', 'leaguemanager'), 'child_of' => $category, 'selected' => $team->roster['cat_id'] ));
				$html = str_replace("\n", "", $html);
			} else {
				$html = "";
			}
			$roster .= "jQuery('span#team_roster_groups').fadeOut('fast', function () {
					jQuery('span#team_roster_groups').html('".addslashes_gpc($html)."').fadeIn('fast');
				   });";
		} else {
			$roster .= "jQuery('span#team_roster_groups').fadeOut('fast');";
		}
	}

	$home = ( $team->home == 1 ) ? "document.getElementById('home').checked = true;" : "document.getElementById('home').checked = false;";

	$logo = ( !empty($team->logo) ) ? "<img src='".$leaguemanager->getImageUrl($team->logo)."' />" : "";	
	die("
		document.getElementById('team').value = '".$team->title."';
		document.getElementById('website').value = '".$team->website."';
		document.getElementById('coach').value = '".$team->coach."';
		document.getElementById('logo_db').value = '".$team->logo."';
		jQuery('div#logo_db_box').html('".addslashes_gpc($logo)."').fadeIn('fast');
		".$home."
		".$roster."
	");
}


/**
 * SACK response to display respective ProjectManager Groups as Team Roster
 *
 * @since not yet
 */
function leaguemanager_set_team_roster_groups() {
	global $projectmanager;

	$roster = (int)$_POST['roster'];
	$project = $projectmanager->getProject($roster);
	$category = $project->category;

	if ( !empty($category) ) {
		$html = wp_dropdown_categories(array('hide_empty' => 0, 'name' => 'roster_group', 'orderby' => 'name', 'echo' => 0, 'show_option_none' => __('Select Group (Optional)', 'leaguemanager'), 'child_of' => $category ));
		$html = str_replace("\n", "", $html);
	} else {
		$html = "";
	}
	
	die("jQuery('span#team_roster_groups').fadeOut('fast', function () {
		jQuery('span#team_roster_groups').html('".addslashes_gpc($html)."').fadeIn('fast');
	});");
}


/**
 * helper function to allocate matches and teams of a league to a aseason and maybe other league
 *
 * @param int $league_id ID of current league
 * @param string $season season to set
 * @param int $new_league_id ID of different league to add teams and matches to (optionl)
 * @param int $old_season (optional) old season if you want to re-allocate teams and matches
 */
function move_league_to_season( $league_id, $season, $new_league_id = false, $old_season = false ) {
	global $leaguemanager, $wpdb;
	if ( !$new_league_id ) $new_league_id = $league_id;
	
	$search = "`league_id` = '".$league_id."'";
	if ( $old_season ) $search .= " AND `season` = '".$old_season."'";

	if ( $teams = $leaguemanager->getTeams($search) ) {
		foreach ( $teams AS $team ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `season` = '%d', `league_id` = '%d' WHERE `id` = '%d'", $season, $new_league_id, $team->id ) );
		}
	}
	if ( $matches = $leaguemanager->getMatches($search) ) {
		foreach ( $matches AS $match ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `season` = '%d', `league_id` = '%d' WHERE `id` = '%d'", $season, $new_league_id, $match->id ) );
		}
	}
}

?>
