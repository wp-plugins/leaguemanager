<?php $this->print_breadcrumb_navi( $league_id ) ?>
<div class="wrap">
<div class="narrow">
	<h2><?php _e( $form_title,'leaguemanager' ) ?></h2>
	
	<form action="edit.php?page=leaguemanager.php&amp;show_league=<?php echo $league_id ?>" class="leaguemanager" method="post">
		<label for="team"><?php _e( 'Team', 'leaguemanager' ) ?>:</label><input type="text" name="team" value="<?php echo $team_title ?>" /><br />
		<label for="short_title"><?php _e( 'Short Name', 'leaguemanager' ) ?>:</label><input type="text" name="short_title" value="<?php echo $short_title ?>" /><br />
		<label for="home"><?php _e( 'Home Team', 'leaguemanager' ) ?>:</label><input type="checkbox" name="home" id="home"<?php echo $home ?>/><br />
					
		<input type="hidden" name="updateLeague" value="team" />
		<input type="hidden" name="team_id" value="<?php echo $team_id ?>" />	
		<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
		
		<p class="submit"><input type="submit" name="addleague" value="<?php _e( $form_title,'leaguemanager' ) ?> &raquo;" class="button" /></p>
	</form>
</div>
</div>