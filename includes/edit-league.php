<?php if ( isset($_POST['updateLeague']) ) $this->get_leagues(); ?>
<?php $this->print_breadcrumb_navi( $league_id ) ?>
<form class="leaguemanager" action="" method="post">
<div class="wrap">
	<h2><?php _e( $form_title, 'leaguemanager' ) ?></h2>
		<label for="league_title"><?php _e( 'League', 'leaguemanager' ) ?>:</label><input type="text" name="league_title" id="league_title" value="<?php echo $league_title ?>" size="30" style="margin-bottom: 1em;" /><br />
		
		<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
		<input type="hidden" name="updateLeague" value="league" />
		
		<?php if ( !isset($_GET['mode']) ) : ?>
		<p class="submit"><input type="submit" value="<?php _e( $form_title, 'leaguemanager' ) ?> &raquo;" class="button" /></p>
		<?php endif ?>
</div>

<?php if ( isset($_GET['mode']) AND 'edit' == $_GET['mode'] ) : ?>
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
			<?php if ( $table_structure = $this->get_table_structure( $league_id ) ) : ?>
			<?php foreach( $table_structure AS $col) : ?>
				<tr id="col_id_<?php echo $col->id ?>">
					<td><input type="text" name="col_title[<?php echo $col->id ?>]" value="<?php echo $col->title ?>" /></td>
					<td>
						<select name="col_type[<?php echo $col->id ?>]" size="1">
							<?php foreach( $this->col_types AS $col_type_id => $col_type ) : 
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
					<td style="text-align: center; width: 12px; vertical-align: middle;"><a class="image_link" href="#" onclick='return leaguemanagerRemoveCol("col_id_<?php echo $col->id ?>", <?php echo $col->id ?>);'><img src="../wp-content/plugins/leaguemanager/images/trash.gif" alt="<?php _e( 'Delete', 'leaguemanager' ) ?>" title="<?php _e( 'Delete column', 'leaguemanager' ) ?>" /></a>
				</tr>
			<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
		<p><a href='#' onclick='return leaguemanagerAddCol();'><?php _e( 'Add new Table Column', 'leaguemanager' ) ?></a></p>
		
		<input type="hidden" name="updateLeague" value="league" />
		<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
		<p class="submit"><input type="submit" value="<?php _e( $form_title, 'leaguemanager' ) ?> &raquo;" class="button" /></p>
</div>
<?php endif; ?>
</form>
