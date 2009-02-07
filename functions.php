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

?>