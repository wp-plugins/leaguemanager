<?php
/**
 * display widget statically
 *
 * @param int $league_id ID of league
 */
function leaguemanager_display_widget( $league_id ) {
	$widget = new LeagueManagerWidget();
	
	echo "<ul id='leaguemanger-widget-".$league_id."' class='leaguemanager_widget'>";
	$widget->display( array( 'league_id' => $league_id ) );
	echo "</ul>";
}


/**
 * display standings table manually
 *
 * @param int $league_id ID of league
 * @param string $logo 'true' or 'false' (default: 'true')
 * @param string $mode 'extend' or 'compact' (default: 'extend')
 *
 * @return void
 */
function leaguemanager_standings( $league_id, $logo = 'true', $mode = 'extend' ) {
	$shortcodes = new LeagueManagerShortcodes();
	echo $shortcodes->showStandings( array('league_id' => $league_id, 'logo' => $logo, 'mode' => $mode) );
}


/**
 * display crosstable table manually
 *
 * @param int $league_id ID of league
 * @param string $mode empty or 'popup' (default: empty)
 * @return void
 */
function leaguemanager_crosstable( $league_id, $mode = '' ) {
	$shortcodes = new LeagueManagerShortcodes();
	echo $shortcodes->showCrosstable( array('league_id' => $league_id, 'mode' => $mode) );
}


/**
 * display matches table manually
 *
 * @param int $league_id ID of league
 * @param string $mode empty or 'all' or 'home' (default: empty => matches are displayed ordered by match day)
 * @return void
 */
function leaguemanager_matches( $league_id, $mode = '' ) {
	$shortcodes = new LeagueManagerShortcodes();
	echo $shortcodes->showMatches( array('league_id' => $league_id, 'mode' => $mode) );
}


/**
 * Ajax Response to set match index in widget
 *
 * @param none
 * @return void
 */
function leaguemanager_get_match_box() {
	global $leaguemanager_widget;
	$current = $_POST['current'];
	$element = $_POST['element'];
	$operation = $_POST['operation'];
	$league_id = $_POST['league_id'];
	$match_limit = ( $_POST['match_limit'] == 'false' ) ? false : $_POST['match_limit'];
	
	if ( $operation == 'next' )
		$index = $current + 1;
	elseif ( $operation == 'prev' )
		$index = $current - 1;
	
	$leaguemanager_widget->setMatchIndex( $index, $element );

	if ( $element == 'next' ) {
		$parent_id = 'next_matches';
		$el_id = 'next_match_box';
		$match_box = $leaguemanager_widget->showNextMatchBox($league_id, $match_limit, false);
	} elseif ( $element == 'prev' ) {
		$parent_id = 'prev_matches';
		$el_id = 'prev_match_box';
		$match_box = $leaguemanager_widget->showPrevMatchBox($league_id, $match_limit, false);
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
	global $wpdb, $leaguemanager_loader;
	$ranking = $_POST['ranking'];
	$ranking = $leaguemanager_loader->adminPanel->getRanking($ranking);
	foreach ( $ranking AS $rank => $team_id ) {
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `rank` = '%d' WHERE `id` = '%d'", $rank, $team_id ) );
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
?>