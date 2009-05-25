<?php
if ( !current_user_can( 'leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
 
else :
?>

<div class="wrap narrow">
	<h2><?php _e( 'LeagueManager Documentation', 'leaguemanager' ) ?></h2>
	
	<h3><?php _e( 'Sport', 'leaguemanager' ) ?></h3>
	<p><?php _e( "The sport type is important to enable certain rules. Gymnastics leagues have apparatus points which other leagues don't, Soccer has Halftime results, while Hocky is played in Thirds and Basketball in Quarters.", 'leaguemanager' ) ?></p>
	<?php do_action( 'leaguemanager_doc_sports' ) ?>

	
	<h3><?php _e( 'Point Rules', 'leaguemanager' ) ?></h3>
	<p><?php _e( 'The second important option is the point rule, which automatically sets the number of points teams get for won matches, draw matches or lost matches. Some league types have specific rules. See the sections below for details.', 'leaguemanager') ?></p>
	
	<h4><?php _e( 'One-Point-Rule', 'leaguemanager' ) ?></h4>
	<p><?php _e( 'The One-Point-Rule simply counts the number of won matches. This point system is used, e.g. in the MLB, NBA and NFL.', 'leaguemanager' ) ?></p>
	
	<h4><?php _e( 'Two-Point-Rule and Three-Point-Rule', 'leaguemanager' ) ?></h4>
	<p><?php _e( 'The Two- and Three-Point-Rules are the most common ones. Teams get two or three points for won matches respectively and one point for draw.', 'leaguemanager' ) ?></p>
	
	<?php do_action( 'leaguemanager_doc_point_rules' ) ?>
</div>

<?php endif; ?>
