<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :
	$error = $is_finals = false;
	if ( isset($_GET['league_id']) ) $league_id = (int)$_GET['league_id'];
	
	$matches = array();
	if ( isset( $_GET['edit'] ) ) {
		$mode = 'edit';
		$edit = true; $bulk = false;
		$form_title  = $submit_title = __( 'Edit Match', 'leaguemanager' );
		
		$id = (int)$_GET['edit'];
		$match = $leaguemanager->getMatch($id);
		$league_id = $match->league_id;
		$matches[0] = $match;
		$match_day = $match->match_day;

		$max_matches = 1;
	} elseif ( isset($_GET['match_day']) ) {
		$mode = 'edit';
		$edit = true; $bulk = true;
		$order = false;
		
		$league_id = (int)$_GET['league_id'];
		$match_day = (int)$_GET['match_day'];

		$search = "`league_id` = '".$league_id."'";
		$search .= " AND `match_day` = '".$match_day."' AND `season` = '".$_GET['season']."'";

		$form_title = sprintf(__( 'Edit Matches &#8211; %d. Match Day', 'leaguemanager' ), $match_day);
		$submit_title = __('Edit Matches', 'leaguemanager');
		
		$matches = $leaguemanager->getMatches( $search, false, $order );
		$max_matches = count($matches);
	} else {
		$mode = 'add';
		$edit = false; $bulk = false;
		$form_title = $submit_title = __( 'Add Matches', 'leaguemanager' );
		$league = $leaguemanager->getLeague( $league_id );

		$max_matches = ceil($leaguemanager->getNumTeams($league->id)/2);
		$match_day = 1;
		$matches[0]->year = ( isset($_GET['season']) && is_numeric($_GET['season']) ) ? (int)$_GET['season'] : date("Y");
		for ( $h = 0; $h < $max_matches; $h++ ) {
			$matches[$h]->hour = $league->default_match_start_time['hour'];
			$matches[$h]->minutes = $league->default_match_start_time['minutes'];
		}
	}

	$league = $leaguemanager->getLeague( $league_id );
	$season = $leaguemanager->getSeason( $league );
	$teams = $leaguemanager->getTeams( "league_id = '".$league->id."' AND `season`  = '".$season['name']."'" );
	?>
	
	<div class="wrap">
		<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league->id ?>"><?php echo $league->title ?></a> &raquo; <?php echo $form_title ?></p>
		<h2><?php echo $form_title ?></h2>
		
		<?php if ( has_action( 'leaguemanager_edit_match_'.$league->sport ) ) : ?>
			<?php do_action( 'leaguemanager_edit_match_'.$league->sport, $league, $teams, $season, $max_matches, $matches, $submit_title, $mode ) ?> 
		<?php else : ?>
		<form action="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league->id?>&amp;season=<?php echo $season['name'] ?>" method="post">
			<?php wp_nonce_field( 'leaguemanager_manage-matches' ) ?>
			
			<table class="form-table">
			<?php if ( !$bulk ) : ?>
			<tr>
				<th scope="row"><label for="date"><?php _e('Date', 'leaguemanager') ?></label></th>
				<td><?php echo $this->getDateSelection( $matches[0]->day, $matches[0]->month, $matches[0]->year) ?></td>
			</tr>
			<?php endif; ?>
			<tr>
				<th scope="row"><label for="match_day"><?php _e('Match Day', 'leaguemanager') ?></label></th>
				<td>
					<select size="1" name="match_day">
						<?php for ($i = 1; $i <= $season['num_match_days']; $i++) : ?>
						<option value="<?php echo $i ?>"<?php if($i == $match_day) echo ' selected="selected"' ?>><?php echo $i ?></option>
						<?php endfor; ?>
					</select>
				</td>
			</tr>
			</table>
			
			
			<p class="match_info"><?php if ( !$edit ) : ?><?php _e( 'Note: Matches with different Home and Guest Teams will be added to the database.', 'leaguemanager' ) ?><?php endif; ?></p>
		
			<table class="widefat">
				<thead>
					<tr>
						<?php if ( $bulk ) : ?>
						<th scope="col"><?php _e( 'Date', 'leaguemanager' ) ?></th>
						<?php endif; ?>
						<th scope="col"><?php _e( 'Home', 'leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Guest', 'leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Location','leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Begin','leaguemanager' ) ?></th>
						<?php do_action('edit_matches_header_'.$league->sport) ?>
					</tr>
				</thead>
				<tbody id="the-list" class="form-table">
				<?php for ( $i = 0; $i < $max_matches; $i++ ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
				<tr class="<?php echo $class; ?>">
					<?php if ( $bulk ) : ?>
					<td><?php echo $this->getDateSelection( $matches[$i]->day, $matches[$i]->month, $matches[$i]->year, $i) ?></td>
					<?php endif; ?>
					<td>
						<select size="1" name="home_team[<?php echo $i ?>]">
						<?php foreach ( $teams AS $team ) : ?>
							<option value="<?php echo $team->id ?>"<?php selected($team->id, $matches[$i]->home_team ) ?>><?php echo $team->title ?></option>
						<?php endforeach; ?>
						</select>
					</td>
					<td>
						<select size="1" name="away_team[<?php echo $i ?>]">
						<?php foreach ( $teams AS $team ) : ?>
							<option value="<?php echo $team->id ?>"<?php selected( $team->id, $matches[$i]->away_team ) ?>><?php echo $team->title ?></option>
						<?php endforeach; ?>
						</select>
					</td>
					<td><input type="text" name="location[<?php echo $i ?>]" id="location[<?php echo $i ?>]" size="20" value="<?php echo $matches[$i]->location ?>" size="30" /></td>
					<td>
						<select size="1" name="begin_hour[<?php echo $i ?>]">
						<?php for ( $hour = 0; $hour <= 23; $hour++ ) : ?>
							<option value="<?php echo str_pad($hour, 2, 0, STR_PAD_LEFT) ?>"<?php selected( $hour, $matches[$i]->hour ) ?>><?php echo str_pad($hour, 2, 0, STR_PAD_LEFT) ?></option>
						<?php endfor; ?>
						</select>
						<select size="1" name="begin_minutes[<?php echo $i ?>]">
						<?php for ( $minute = 0; $minute <= 60; $minute++ ) : ?>
							<?php if ( 0 == $minute % 5 && 60 != $minute ) : ?>
							<option value="<?php  echo str_pad($minute, 2, 0, STR_PAD_LEFT) ?>"<?php selected( $minute, $matches[$i]->minutes ) ?>><?php echo str_pad($minute, 2, 0, STR_PAD_LEFT) ?></option>
							<?php endif; ?>
						<?php endfor; ?>
						</select>
					</td>
					<?php do_action('edit_matches_columns_'.$league->sport, $matches[$i], $league, $season, $teams, $i) ?>
				</tr>
				<input type="hidden" name="match[<?php echo $i ?>]" value="<?php echo $matches[$i]->id ?>" />
				<?php endfor; ?>
				</tbody>
			</table>
			
			<input type="hidden" name="mode" value="<?php echo $mode ?>" />
			<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
			<input type="hidden" name="season" value="<?php echo $season['name'] ?>" />
			<input type="hidden" name="updateLeague" value="match" />
			
			<p class="submit"><input type="submit" value="<?php echo $submit_title ?> &raquo;" class="button" /></p>
		</form>
		<?php endif; ?>
	</div>
<?php endif; ?>
