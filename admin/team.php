<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
else :
	$edit = false;
	if ( isset( $_GET['edit'] ) ) {
		$edit = true;
		$team = $leaguemanager->getTeam($_GET['edit']);
		$league_id = $team->league_id;
		$form_title = __( 'Edit Team', 'leaguemanager' );
	} else {
		$form_title = __( 'Add Team', 'leaguemanager' );
		$league_id = $_GET['league_id'];
		$team = (object)array( 'title' => '', 'home' => 0, 'id' => '', 'logo' => '', 'website' => '', 'coach' => '' );
	}
	$league = $leaguemanager->getLeague( $league_id );
	$season = isset($_GET['season']) ? $_GET['season'] : '';
	
	if ( !wp_mkdir_p( $leaguemanager->getImagePath() ) )
		echo "<div class='error'><p>".sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $leaguemanager->getImagePath() )."</p></div>";
	?>

	<div class="wrap">
		<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league->id ?>"><?php echo $league->title ?></a> &raquo; <?php echo $form_title ?></p>
		<h2><?php echo $form_title ?></h2>
		
		<form action="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league_id ?>&amp;season=<?php echo $season ?>" method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'leaguemanager_manage-teams' ) ?>
			
			<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="team"><?php _e( 'Team', 'leaguemanager' ) ?></label></th>
				<td>
					<input type="text" id="team" name="team" value="<?php echo $team->title ?>" />
					<?php if ( !$edit ) : ?>
					<span><?php _e( 'OR', 'leaguemanager' ) ?></span>
					<select size="1" name="team_from_db" id="team_from_db">
						<option value=""><?php _e( 'Choose Team from Database', 'leaguemanager' ) ?></option>
						<?php $this->teamsDropdownCleaned() ?>
					</select>
					<?php endif; ?>
				</td>
			</tr>
			<tr valing="top">
				<th scope="row"><label for="logo"><?php _e( 'Logo', 'leaguemanager' ) ?></label></th>
				<td>
					<?php if ( '' != $logo ) : ?>
					<img src="<?php echo $leaguemanager->getImageUrl($team->logo)?>" class="alignright" />
					<?php endif; ?>
					<input type="file" name="logo" id="logo" size="35"/><p><?php _e( 'Supported file types', 'leaguemanager' ) ?>: <?php echo implode( ',',$this->getSupportedImageTypes() ); ?></p>
					<?php if ( '' != $team->logo ) : ?>
					<p style="float: left;"><label for="overwrite_image"><?php _e( 'Overwrite existing image', 'leaguemanager' ) ?></label><input type="checkbox" id="overwrite_image" name="overwrite_image" value="1" style="margin-left: 1em;" /></p>
					<input type="hidden" name="image_file" value="<?php echo $team->logo ?>" />
					<p style="float: right;"><label for="del_logo"><?php _e( 'Delete Logo', 'leaguemanager' ) ?></label><input type="checkbox" id="del_logo" name="del_logo" value="1" style="margin-left: 1em;" /></p>
					<?php endif; ?>
				</td>
			</tr>
			<tr valing="top">
				<th scope="row"><label for="website"><?php _e( 'Website', 'leaguemanager' ) ?></label></th><td>http://<input type="text" name="website" id="website" value="<?php echo $team->website ?>" size="30" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="coach"><?php _e( 'Coach', 'leaguemanager' ) ?></label></th><td><input type="text" name="coach" id="coach" value="<?php echo $team->coach ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="home"><?php _e( 'Home Team', 'leaguemanager' ) ?></label></th><td><input type="checkbox" name="home" id="home"<?php if ($team->home == 1) echo ' checked="checked""' ?>/></td>
			</tr>
			<?php do_action( 'team_edit_form', &$team ) ?>
			</table>
						
			<input type="hidden" name="team_id" value="<?php echo $team->id ?>" />	
			<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
			<input type="hidden" name="updateLeague" value="team" />
			<input type="hidden" name="season" value="<?php echo $season ?>" />
			
			<p class="submit"><input type="submit" value="<?php echo $form_title ?> &raquo;" class="button" /></p>
		</form>
	</div>
<?php endif; ?>
