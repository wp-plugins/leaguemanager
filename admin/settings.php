<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :
	$options = get_option('leaguemanager');
	$league = $leaguemanager->getCurrentLeague();
	if ( isset($_POST['updateSettings']) ) {
		check_admin_referer('leaguemanager_manage-league-options');

		// Set textdomain
		$options['textdomain'] = $_POST['sport'];
		update_option('leaguemanager', $options);
		
		$point_rule = isset($_POST['forwin']) ? array( 'forwin' => $_POST['forwin'], 'fordraw' => $_POST['fordraw'], 'forloss' => $_POST['forloss'], 'forwin_overtime' => $_POST['forwin'], 'forloss_overtime' => $_POST['forloss'] ) : $_POST['point_rule'];
		$this->editLeague( $_POST['league_title'], $point_rule, $_POST['point_format'], $_POST['sport'], $_POST['team_ranking'], $_POST['mode'], $_POST['custom'], $_POST['league_id'] );
		$this->printMessage();
	} elseif ( isset($_POST['addSeason']) ) {
		if ( !empty($_POST['season']) ) {
			$add_teams = isset($_POST['no_add_teams']) ? false : true;
			$this->addSeason( $_POST['season'], $_POST['num_match_days'], $league->id, $add_teams );
		} else {
			$leaguemanager->setMessage( __( 'Season was empty', 'leaguemanager' ), true );
			$leaguemanager->printMessage();
		}
	} elseif ( isset($_POST['doaction']) ) {
		check_admin_referer('seasons-bulk');
		if ( 'delete' == $_POST['action'] ) {
			foreach ( $_POST['del_season'] AS $season ) {
				$this->delSeason( $season, $league->id );
			}
		}
	}
	
	$options = get_option('leaguemanager');
	$league = $leaguemanager->getLeague( $_GET['league_id'] );
	$forwin = $fordraw = $forloss = 0;
	// Manual point rule
	if ( is_array($league->point_rule) ) {
		$forwin = $league->point_rule['forwin'];
		$fordraw = $league->point_rule['fordraw'];
		$forloss = $league->point_rule['forloss'];
		$league->point_rule = 6;
	}
	
	if ( empty($league->seasons) ) { 
		$leaguemanager->setMessage( __( 'You need to add at least one season', 'leaguemanager' ), true );
		$leaguemanager->printMessage();
	}
?>

<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league->id ?>"><?php echo $league->title ?></a> &raquo; <?php _e( 'League Preferences', 'leaguemanager' ) ?></p>
			
	<h2><?php _e( 'League Preferences', 'leaguemanager' ) ?></h2>
	<form action="" method="post">
		<?php wp_nonce_field( 'leaguemanager_manage-league-options' ) ?>
			
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="league_title"><?php _e( 'Title', 'leaguemanager' ) ?></label></th><td><input type="text" name="league_title" id="league_title" value="<?php echo $league->title ?>" size="30" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="sport"><?php _e( 'Sport', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="sport" id="sport">
						<?php foreach ( $leaguemanager->getLeagueTypes() AS $id => $title ) : ?>
							<option value="<?php echo $id ?>"<?php if ( $id == $league->sport ) echo ' selected="selected"' ?>><?php echo $title ?></option>
						<?php endforeach; ?>
					</select>
					<span class="setting-description"><?php printf( __( "Check the <a href='%s'>Documentation</a> for details", 'leaguemanager'), admin_url() . 'admin.php?page=leaguemanager-doc' ) ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="point_rule"><?php _e( 'Point Rule', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="point_rule" id="point_rule" onchange="Leaguemanager.checkPointRule(<?php echo $forwin ?>, <?php echo $fordraw ?>, <?php echo $forloss ?>)">
					<?php foreach ( $this->getPointRules() AS $id => $point_rule ) : ?>
					<option value="<?php echo $id ?>"<?php if ( $id == $league->point_rule ) echo ' selected="selected"'; ?>><?php echo $point_rule ?></option>
					<?php endforeach; ?>
					</select>
					<span class="setting-description"><?php printf( __("For details on point rules see the <a href='%s'>Documentation</a>", 'leaguemanager'), admin_url() . 'admin.php?page=leaguemanager-doc' ) ?></span>
					<div id="point_rule_manual" style="display: block;">
					<?php if ( $league->point_rule == 6 ) : ?>
						<div id="point_rule_manual_content">
							<input type='text' name='forwin' id='forwin' value='<?php echo $forwin ?>' size='2' />
							<input type='text' name='fordraw' id='fordraw' value='<?php echo $fordraw ?>' size='2' />
							<input type='text' name='forloss' id='forloss' value='<?php echo $forloss ?>' size='2' />
							&#160;<span class='setting-description'><?php _e( 'Order: Forwin, Fordraw, Forloss', 'leaguemanager' ) ?></span>
						</div>
					<?php endif; ?>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="point_format"><?php _e( 'Point Format', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="point_format" id="point_format" >
					<?php foreach ( $this->getPointFormats() AS $format ) : ?>
					<option value="<?php echo $format ?>"<?php if ( $format == $league->point_format  ) echo ' selected="selected"'; ?>><?php echo $format ?></option>
					<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="team_ranking"><?php _e( 'Team Ranking', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="team_ranking" id="team_ranking" >
						<option value="auto"<?php if ( 'auto' == $league->team_ranking  ) echo ' selected="selected"'; ?>><?php _e( 'Automatic', 'leaguemanager' ) ?></option>
						<option value="manual"<?php if ( 'manual' == $league->team_ranking  ) echo ' selected="selected"'; ?>><?php _e( 'Manual', 'leaguemanager' ) ?></option>
					</select>
					&#160;<span class="setting-description"><?php _e( 'Team Ranking via Drag & Drop probably will only work in Firefox', 'leaguemanager' ) ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mode"><?php _e( 'Mode', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="mode" id="mode">
					<?php foreach ( $this->getModes() AS $id => $mode ) : ?>
						<option value="<?php echo $id ?>"<?php if ( $id == $league->mode ) echo ' selected="selected"' ?>><?php echo $mode ?></option>
					<?php endforeach; ?>
					</select>
				</td>
			</tr>

			<?php do_action( 'league_settings_'.$league->sport, &$league ); ?> 
			<?php do_action( 'league_settings_'.$league->mode, &$league ); ?> 
			<?php do_action( 'league_settings', &$league ); ?> 
		</table>
		
		<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
		<p class="submit"><input type="submit" name="updateSettings" value="<?php _e( 'Save Preferences', 'leaguemanager' ) ?> &raquo;" class="button" /></p>
	</form>
</div>

<div class="wrap narrow">
	<h2><?php _e( 'Seasons', 'leaguemanager' ) ?></h2>
	<form id="seaons-filter" action="" method="post">
		<?php wp_nonce_field( 'seasons-bulk' ) ?>
		
		<div class="tablenav" style="margin-bottom: 0.1em;">
			<!-- Bulk Actions -->
			<select name="action" size="1">
				<option value="-1" selected="selected"><?php _e('Bulk Actions') ?></option>
				<option value="delete"><?php _e('Delete')?></option>
			</select>
			<input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
		</div>
		<table class="widefat">
		<thead>
		<tr>
			<th scope="col" class="check-column"><input type="checkbox" onclick="Leaguemanager.checkAll(document.getElementById('seaons-filter'));" /></th>
			<th scope="col"><?php _e( 'Season', 'leaguemanager' ) ?></th>
			<th scope="col"><?php _e( 'Match Days', 'leaguemanager' ) ?></th>
		</tr>
		</thead>
		<tbody id="the-list">
			<?php if ( !empty($league->seasons) ) : ?>
			<?php foreach( $league->seasons AS $key => $season ) : $class = ( 'alternate' == $class ) ? '' : 'alternate' ?>
			<tr class="<?php echo $class ?>">
				<th scope="row" class="check-column"><input type="checkbox" value="<?php echo $key ?>" name="del_season[<?php echo $key ?>]" /></th>
				<td><?php echo $season['name'] ?></td>
				<td><?php echo $season['num_match_days'] ?></td>
			</tr>
			<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
		</table>
	</form>
	
	<h3><?php _e( 'Add new Season', 'leaguemanager' ) ?></h3>
	<form action="" method="post">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="season"><?php _e( 'Season', 'leaguemanager' ) ?></th>
				<td>
					<input type="text" name="season" id="season" value="" size="8" />&#160;<span class="setting-description"><?php _e('Usually 4-digit year, e.g. 2008. Can also be any kind of string, e.g. 0809', 'leaguemanager') ?></span><br />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="num_match_days"><?php _e( 'Number of Match Days', 'leaguemanager' ) ?></label></th>
				<td>
					<input type="text" name="num_match_days" id="num_match_days" value="" size="2" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="no_add_teams"><?php _e( 'No Teams', 'leaguemanager' ) ?></th>
				<td>
					<input type="checkbox" name="no_add_teams" id="no_add_teams" value="1" />&#160;<span class="setting-description"><?php _e( 'Check this to not automatically get teams from database and add them to the season', 'leaguemanager' ) ?></span>
				</td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="addSeason" class="button" value="<?php _e( 'Add Season', 'leaguemanager' ) ?>" /></p>
	</form>
</div>


<?php endif; ?>
