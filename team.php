<?php
if ( isset( $_GET['edit'] ) ) {
	if ( $team = $leaguemanager->get_teams( "id = '".$_GET['edit']."'" ) ) {
		$team_title = $team[0]->title;
		$short_title = $team[0]->short_title;
		$home = ( 1 == $team[0]->home ) ? ' checked="checked"' : '';
		$team_id = $team[0]->id;
		$league_id = $team[0]->league_id;
	}
	$league = $leaguemanager->get_leagues( $league_id );
	$league_title = $league['title'];
	
	$form_title = 'Edit Team';
	$league_title = $league['title'];
} else {
	$form_title = 'Add Team'; $team_title = ''; $short_title = ''; $home = ''; $team_id = ''; $league_id = $_GET['league_id'];
	
	$league = $leaguemanager->get_leagues( $league_id );
	$league_title = $league['title'];
}
?>

<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="edit.php?page=leaguemanager/manage-leagues.php"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> > <a href="edit.php?page=leaguemanager/show-league.php&amp;id=<?php echo $league_id ?>"><?php echo $league_title ?></a> > <?php _e( $form_title, 'leaguemanager' ) ?></p>
</div>
<div class="wrap">
<div class="narrow">
	<h2><?php _e( $form_title,'leaguemanager' ) ?></h2>
	
	<form action="edit.php?page=leaguemanager/show-league.php&amp;id=<?php echo $league_id ?>" class="leaguemanager" method="post">
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