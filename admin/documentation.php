<?php
if ( !current_user_can( 'leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
 
else :
?>

<div class="wrap narrow">
	<h2><?php _e( 'LeagueManager Documentation', 'leaguemanager' ) ?></h2>
	
	<h3><?php _e( 'Sport', 'leaguemanager' ) ?></h3>
	<p><?php _e( "The sport is important to enable certain rules. Gymnastics leagues have apparatus points which other leagues don't. Chose <em>Ball games</em> if you want to manage a soccer league or similar. It activates goals and insertion of half time results. Further the ranking of teams is different, namely first by points and second by goal difference. <em>Hockey</em> and <em>Basketball</em> have the characteristics that they are played in thirds and quarters respectively.", 'leaguemanager' ) ?></p>
	<p><?php _e( "I tried to cover certain league types that have different features, but of course I cannot cover all. If you want to manage a league that has specific rules or characteristcs that cannot be covered by the current options, please contact me by <a href='mailto:kolja.schleich@googlemail.com'>email</a>. We could then discuss possibilities to implement it into the plugin.", 'leaguemanager') ?></p>
	
	<h3><?php _e( 'Point Rules', 'leaguemanager' ) ?></h3>
	<p><?php _e( 'The second important option is the point rule, which automatically sets the number of points teams get for won matches, draw matches or lost matches. Some league types have specific rules. See the sections below for details.', 'leaguemanager') ?></p>
	
	<h4><?php _e( 'One-Point-Rule', 'leaguemanager' ) ?></h4>
	<p><?php _e( 'The One-Point-Rule gives simply counts the number of won matches. This point system is used, e.g. in the MLB, NBA and NFL.', 'leaguemanager' ) ?></p>
	
	<h4><?php _e( 'Two-Point-Rule and Three-Point-Rule', 'leaguemanager' ) ?></h4>
	<p><?php _e( 'The Two- and Three-Point-Rules are the most common ones. Teams get two or three points for won matches respectively and one point for draw.', 'leaguemanager' ) ?></p>
	
	<h4><?php _e( 'German Icehockey League (DEL)', 'leaguemanager' ) ?></h4>
	<p><?php _e( 'The DEL uses a more complicated form of the Three-Point-Rule. The winner after regular time gets three points, the loser none. The winner after overtime gets two points and the loser one. This rule was also applied at the Icehockey Worldchampionchip in 2008.', 'leaguemanager' ) ?></p>
	
	<h4><?php _e( 'National Hockey League (NHL)', 'leaguemanager' ) ?></h4>
	<p><?php _e( 'The NHL uses a derivative of the Two-Point-Rule. The winner after regular time and overtime gains two points whereas the loser after overtime and penalty gets one.', 'leaguemanager' ) ?></p>
	
	</p><cite><?php printf( __( "Source: <a href='%s'>Wikipedia</a> (German)", 'leaguemanager' ), 'http://de.wikipedia.org/wiki/3-Punkte-Regel' ) ?></cite></p>
</div>

<?php endif; ?>