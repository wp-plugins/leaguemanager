<?php
/*
 * Do some upgrade stuff
 */

 /*
 * Upgrade for Version 1.5
 */
$lmm_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->leaguemanager_leaguemeta}" );
if ( !in_array('widget', $lmm_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_leaguemeta} ADD `widget` TINYINT( 1 ) NOT NULL AFTER `order_by`" );
 
 
$lm_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->leaguemanager}" );
if ( !in_array('active', $lm_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `active` tinyint( 1 ) NOT NULL default '1';" );
	

//update_option( 'leaguemanager', $options );

?>