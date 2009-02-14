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
 * Ajax Response to set match index in widget
 *
 * @param none
 * @return void
 */
function leaguemanager_set_match_index() {
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

?>