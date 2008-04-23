<?php
/*
 * Do some upgrade stuff
 */

$lm_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->leaguemanager}" );
if ( !in_array('active', $lm_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `active` tinyint( 1 ) NOT NULL default '1';" );
	

update_option( 'leaguemanager', $options );

?>