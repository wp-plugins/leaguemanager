<?php
if ( !current_user_can( 'leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
 
else :
?>

<div class="wrap narrow">
<h2 id="top"><?php _e( 'LeagueManager Documentation', 'leaguemanager' ) ?></h2>

<h3><?php _e( 'Content', 'projectmanager') ?></h3>
<ul>
	<li><a href="#shortcodes"><?php _e( 'Shortcodes', 'leaguemanager' ) ?></a></li>
	<li><a href="#templates"><?php _e( 'Templates', 'leaguemanager' ) ?></a></li>
	<li><a href="#settings"><?php _e( 'League Settings', 'leaguemanager' ) ?></a></li>
	<li><a href="#team_roster"><?php _e( 'Team Roster', 'leaguemanager' ) ?></a></li>
	<li><a href="#customization"><?php _e( 'Customization', 'leaguemanager' ) ?></a></li>
	<li>
		<a href="#howto_intro"><?php _e( 'Howto', 'leaguemanager' ) ?></a>
		<ul style="margin-left: 2em;">
			<li><a href="#standard"><?php _e( 'Standard Mode', 'leaguemanager' ) ?></a></li>
			<li><a href="#championship"><?php _e( 'Championship Mode', 'leaguemanager' ) ?></a></li>
			<li><a href="#team_roster"><?php _e( 'Team Roster', 'leaguemanager' ) ?></a></li>
			<li><a href="#match_statistics"><?php _e( 'Match Statistics', 'leaguemanager' ) ?></a></li>

		</ul>
	</li>
	<li><a href="#donations"><?php _e( 'Donations', 'leaguemanager' ) ?></a></li>
</ul>


<a href="#top" class="alignright"><?php _e( 'Top', 'leaguemanager' ) ?></a>
<h3 id="shortcodes"><?php _e( 'Shortcodes', 'leaguemanager' ) ?></h3>

<a href="#top" class="alignright"><?php _e( 'Top', 'leaguemanager' ) ?></a>
<h3 id="templates"><?php _e( 'Templates', 'leaguemanager' ) ?></h3>

<a href="#top" class="alignright"><?php _e( 'Top', 'leaguemanager' ) ?></a>
<h3 id="settings"><?php _e( 'League Settings', 'leaguemanager' ) ?></h3>

<h4><?php _e( 'Sport', 'leaguemanager' ) ?></h4>
<p><?php _e( "The sport type is important to enable certain rules. Gymnastics leagues have apparatus points which other leagues don't, Soccer has Halftime results, while Hocky is played in Thirds and Basketball in Quarters.", 'leaguemanager' ) ?></p>
<?php do_action( 'leaguemanager_doc_sports' ) ?>

<h4><?php _e( 'Point Rules', 'leaguemanager' ) ?></h4>
<p><?php _e( 'The second important option is the point rule, which automatically sets the number of points teams get for won matches, draw matches or lost matches. Some league types have specific rules. See the sections below for details.', 'leaguemanager') ?></p>
	
<h5><?php _e( 'One-Point-Rule', 'leaguemanager' ) ?></h5>
<p><?php _e( 'The One-Point-Rule simply counts the number of won matches. This point system is used, e.g. in the MLB, NBA and NFL.', 'leaguemanager' ) ?></p>
	
<h5><?php _e( 'Two-Point-Rule and Three-Point-Rule', 'leaguemanager' ) ?></h5>
<p><?php _e( 'The Two- and Three-Point-Rules are the most common ones. Teams get two or three points for won matches respectively and one point for draw.', 'leaguemanager' ) ?></p>
	
<?php do_action( 'leaguemanager_doc_point_rules' ) ?>


<a href="#top" class="alignright"><?php _e( 'Top', 'leaguemanager' ) ?></a>
<h3><?php _e( 'Customization', 'leaguemanager' ) ?></h3>
<p><?php _e( 'The Plugin is built iin a modular way with several Wordpress hooks to make customization as easy as possible. I here provide a list of available hooks with a short description.', 'leaguemanager' ) ?></p>

<h4><?php _e( 'List of Wordpress Filters', 'leaguemanager' ) ?></h4>
<ul>
	<li>
	<strong>leaguemanager_sports</strong>
	<p><?php _e( 'Can be used to add a new sport type to the settings selection menu.', 'leaguemanager' ) ?></p>
	<code><pre>
	&lt;?php
	add_filter('leaguemanager_sports', 'my_sport_type');
	function my_sport_type( $sports ) {
		$sports['sport'] = 'Sport Name';
		return $sports;
	}
	?&gt;
	</pre></code>
	</li>
	<li>
	<strong>rank_teams_<em>$sport</em></strong>
	<p><?php _e( 'Change Team Ranking based on sport specific rules. The sport type needs to be appended so its only called, when this type is active. I will use the example sport type <em>sport</em> from above.', 'leaguemanager' ) ?></p>
	<code><pre>
	&lt;?php
	add_filter('rank_teams_sport', 'my_ranking');
	function my_ranking( $teams ) {
		// rank teams using array_multisort
	}
	</code></pre>
</ul>


<a href="#top" class="alignright"><?php _e( 'Top', 'leaguemanager' ) ?></a>
<h3 id="howto_intro"><?php _e( 'HowTo', 'leaguemanager' ) ?></h3>

<h4 id="standard"><?php _e( 'Standard Mode', 'leaguemanager' ) ?></h4>

<h4 id="championship"><?php _e( 'Championship Mode', 'leaguemanager' ) ?></h4>

<h4 id="team_roster"><?php _e( 'Team Roster', 'leaguemanager' ) ?></h4>

<h4 id="match_statistics"><?php _e( 'Match Statistics', 'leaguemanager' ) ?></h4>


<a href="#top" class="alignright"><?php _e( 'Top', 'leaguemanager' ) ?></a>
<h3 id="donations"><?php _e( 'Donations', 'leaguemanager' ) ?></h3>
<p><?php _e( 'If you like my plugin and want to support me, I am grateful for any donation.', 'leaguemanager' ) ?></p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float: left; margin-right: 1em;">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="hosted_button_id" value="2329191">
	<input type="image" src="<?php echo LEAGUEMANAGER_URL ?>/admin/doc/donate_eur.gif" border="0" name="submit" alt="Donate in Euro">
</form>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="hosted_button_id" value="3408441">
	<input type="image" src="<?php echo LEAGUEMANAGER_URL ?>/admin/doc/donate_usd.gif" border="0" name="submit" alt="Donate in USD">
</form>

</div>

<?php endif; ?>
