<?php
/**
 * display widget statically
 *
 * @param array $args
 */
function leaguemanager_display_widget( $args = array() ) {
	global $leaguemanager;
	$leaguemanager->widget->display( $args );
}

?>