<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
 
else :
?>

<div class="wrap">
	<h2><?php _e( 'LeagueManager Documentation', 'leaguemanager' ) ?></h2>
	
	<h3><?php _e( 'League Types', 'leaguemanager' ) ?></h3>
	
	<h3><?php _e( 'Point Rules', 'leaguemanager' ) ?></h3>
	
	<h4><?php _e( 'One-Point-Rule', 'leaguemanager' ) ?></h4>
	
	<h4><?php _e( 'Two-Point-Rule and Three-Point-Rule', 'leaguemanager' ) ?></h4>
		
	<h4><?php _e( 'German Icehockey League (DEL)', 'leaguemanager' ) ?></h4>
	
	<h4><?php _e( 'National Hockey League (NHL)', 'leaguemanager' ) ?></h4>
</div>

<?php endif; ?>