<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :
	
 	if ( isset($_POST['updateLeague']) && check_admin_referer('leaguemanager_manage-league-options') && !isset($_POST['deleteit']) ) {
		if ( '' == $_POST['league_id'] ) {
			$return_message = $leaguemanager->add_league( $_POST['league_title'] );
		} else {
			$leaguemanager->save_table_structure( $_POST['league_id'], $_POST['col_title'], $_POST['col_type'], $_POST['col_order'], $_POST['order_by'], $_POST['new_col_title'], $_POST['new_col_type'], $_POST['new_col_order'], $_POST['new_order_by']);
			$return_message = $leaguemanager->edit_league( $_POST['league_title'], $_POST['league_id'] );
		}
		echo '<div id="message" class="updated fade"><p><strong>'.__( $return_message, 'leaguemanager' ).'</strong></p></div>';
	}
	
	if ( isset( $_GET['edit'] ) ) {
		$league_id = $_GET['edit'];
		$league = $leaguemanager->get_leagues( $league_id );
		$form_title = 'Edit League';
		$league_title = $league['title'];
	} else {
		$league_id = $_GET['league_id']; $form_title = 'Add League'; $league_title = '';
	}
	?>
	<div class="wrap">
		<p class="leaguemanager_breadcrumb"><a href="edit.php?page=leaguemanager/manage-leagues.php"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="edit.php?page=leaguemanager/show-league.php&amp;id=<?php echo $league_id ?>"><?php echo $league_title ?></a> &raquo; <?php _e( $form_title, 'leaguemanager' ) ?></p>
	</div>
	
	<form class="leaguemanager" action="" method="post">
		<?php wp_nonce_field( 'leaguemanager_manage-league-options' ) ?>
		
		<div class="wrap">
			<h2><?php _e( $form_title, 'leaguemanager' ) ?></h2>
			<label for="league_title"><?php _e( 'League', 'leaguemanager' ) ?>:</label><input type="text" name="league_title" id="league_title" value="<?php echo $league_title ?>" size="30" style="margin-bottom: 1em;" /><br />
				
			<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
		</div>
	
		<div class="wrap">
			<h2><?php _e(  'Edit Table Structure', 'leaguemanager' ) ?></h2>
			<table class="leaguemanager">
				<thead>
				<tr>
					<th><?php _e( 'Title', 'leaguemanager' ) ?></th>
					<th><?php _e( 'Type', 'leaguemanager' ) ?></th>
					<th><?php _e( 'Order BY', 'leaguemanager' ) ?></th>
					<th><?php _e( 'Order', 'leaguemanager' ) ?></th>
					<th>&#160;</th>
				</tr>
				</thead>
				<tbody id="leaguemanager_table_structure">
				<?php if ( $table_structure = $leaguemanager->get_table_structure( $league_id ) ) : ?>
				<?php foreach( $table_structure AS $col) : ?>
					<tr id="col_id_<?php echo $col->id ?>">
						<td><input type="text" name="col_title[<?php echo $col->id ?>]" value="<?php echo $col->title ?>" /></td>
						<td>
							<select name="col_type[<?php echo $col->id ?>]" size="1">
								<?php foreach( $leaguemanager->get_col_types() AS $col_type_id => $col_type ) : 
									$selected = '';
									if ( $col_type_id == $col->type )
										$selected = "selected='selected'";
								?>
								<option value="<?php echo $col_type_id ?>"<?php echo $selected ?>><?php _e( $col_type, 'leaguemanager' ) ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<?php $selected = ( 1 == $col->order_by ) ? ' checked="checked"' : ''; ?>
						<td><input type="checkbox" name="order_by[<?php echo $col->id ?>]"<?php echo $selected ?> value="1" /></td>
						<td><input type="text" size="2" name="col_order[<?php echo $col->id ?>]" value="<?php echo $col->order ?>" /></td>
						<td style="text-align: center; width: 12px; vertical-align: middle;"><a class="image_link" href="#" onclick='return Leaguemanager.removeCol("col_id_<?php echo $col->id ?>", <?php echo $col->id ?>);'><img src="../wp-content/plugins/leaguemanager/images/trash.gif" alt="<?php _e( 'Delete', 'leaguemanager' ) ?>" title="<?php _e( 'Delete column', 'leaguemanager' ) ?>" /></a>
					</tr>
				<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
			<p><a href='#' onclick='return Leaguemanager.addCol();'><?php _e( 'Add new Table Column', 'leaguemanager' ) ?></a></p>
			
			<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
			<p class="submit"><input type="submit" name="updateLeague" value="<?php _e( $form_title, 'leaguemanager' ) ?> &raquo;" class="button" /></p>
		</div>
	</form>
<?php endif; ?>
