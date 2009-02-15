<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
else :
	if ( isset( $_GET['edit'] ) ) {
		if ( $team = $leaguemanager->getTeam( $_GET['edit'] ) ) {
			$team_title = $team->title;
			$short_title = $team->short_title;
			$home = ( 1 == $team->home ) ? ' checked="checked"' : '';
			$team_id = $team->id;
			$logo = $team->logo;
			$website = $team->website;
			$coach = $team->coach;
			$league_id = $team->league_id;
		}
		$form_title = __( 'Edit Team', 'leaguemanager' );
	} else {
		$form_title = __( 'Add Team', 'leaguemanager' );
		$team_title = $short_title = $home = $team_id =  $logo = $website = $coach = ''; $league_id = $_GET['league_id'];
	}
	$league = $leaguemanager->getLeague( $league_id );
	
	if ( !wp_mkdir_p( $leaguemanager->getImagePath() ) )
		echo "<div class='error'><p>".sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $leaguemanager->getImagePath() )."</p></div>";
	?>

	<div class="wrap">
		<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;id=<?php echo $league->id ?>"><?php echo $league->title ?></a> &raquo; <?php echo $form_title ?></p>
		<h2><?php echo $form_title ?></h2>
		
		<form action="admin.php?page=leaguemanager&amp;subpage=show-league&amp;id=<?php echo $league->id ?>" method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'leaguemanager_manage-teams' ) ?>
			
			<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="team"><?php _e( 'Team', 'leaguemanager' ) ?></label></th><td><input type="text" id="team" name="team" value="<?php echo $team_title ?>" /></td>
			</tr>
			<!--
			<tr valign="top">
				<th scope="row"><label for="short_title"><?php _e( 'Short Name', 'leaguemanager' ) ?></label></th><td><input type="text" id="short_title" name="short_title" value="<?php echo $short_title ?>" /></td>
			</tr>
			-->
			<tr valing="top">
				<th scope="row"><label for="logo"><?php _e( 'Logo', 'leaguemanager' ) ?></label></th>
				<td>
					<?php if ( '' != $logo ) : ?>
					<img src="<?php echo $leaguemanager->getImageUrl($logo)?>" class="alignright" />
					<?php endif; ?>
					<input type="file" name="logo" id="logo" size="35"/><p><?php _e( 'Supported file types', 'leaguemanager' ) ?>: <?php echo implode( ',',$this->getSupportedImageTypes() ); ?></p>
					<?php if ( '' != $logo ) : ?>
					<p style="float: left;"><label for="overwrite_image"><?php _e( 'Overwrite existing image', 'leaguemanager' ) ?></label><input type="checkbox" id="overwrite_image" name="overwrite_image" value="1" style="margin-left: 1em;" /></p>
					<input type="hidden" name="image_file" value="<?php echo $logo ?>" />
					<p style="float: right;"><label for="del_logo"><?php _e( 'Delete Logo', 'leaguemanager' ) ?></label><input type="checkbox" id="del_logo" name="del_logo" value="1" style="margin-left: 1em;" /></p>
					<?php endif; ?>
				</td>
			</tr>
			<tr valing="top">
				<th scope="row"><label for="website"><?php _e( 'Website', 'leaguemanager' ) ?></label></th><td>http://<input type="text" name="website" id="website" value="<?php echo $website ?>" size="30" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="coach"><?php _e( 'Coach', 'leaguemanager' ) ?></label></th><td><input type="text" name="coach" id="coach" value="<?php echo $coach ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="home"><?php _e( 'Home Team', 'leaguemanager' ) ?></label></th><td><input type="checkbox" name="home" id="home"<?php echo $home ?>/></td>
			</tr>
			</table>
						
			<input type="hidden" name="team_id" value="<?php echo $team_id ?>" />	
			<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
			<input type="hidden" name="updateLeague" value="team" />
			
			<p class="submit"><input type="submit" value="<?php echo $form_title ?> &raquo;" class="button" /></p>
		</form>
	</div>
<?php endif; ?>