<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
else :
	global $lmStats;

	if ( isset($_POST['updateMatchStats']) ) {
		$lmStats->save($_POST['match_id'], $_POST['stats']);
	}

	$match = $leaguemanager->getMatch( $_GET['match_id'] );
	$league = $leaguemanager->getLeague($match->league_id);

	$home = $leaguemanager->getTeam($match->home_team);
	$away = $leaguemanager->getTeam($match->away_team);

	// Load ProjectManager Bridge
	if ( $league->hasBridge ) {
		$lmBridge->setProjectID( $league->project_id );
		$lmBridge->loadScripts();

		$home->teamRoster = $lmBridge->getTeamRoster( $home->roster['id'], $home->roster['cat_id'] );
		$away->teamRoster = $lmBridge->getTeamRoster( $away->roster['id'], $away->roster['cat_id'] );
	} else {
		$home->teamRoster = $away->teamRoster = false;
	}

?>

	<div class="wrap">
		<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league->id ?>"><?php echo $league->title ?></a> &raquo; <?php _e( 'Match Statistics', 'leaguemanager' ) ?></p>

		<h2><?php printf(__( 'Match Statistics &#8211; %s v.s. %s', 'leaguemanager'), $home->title, $away->title) ?></h2>

		<form action="" method="post">

		<h3><?php _e( 'Goals', 'leaguemanager' ) ?></h3>

		<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><?php _e( 'Time' ) ?></th>
				<th scope="col"><?php _e( 'Scorer', 'leaguemanager' ) ?></th>
				<th scope="col"><?php _e( 'Standing', 'leaguemanager' ) ?></th>
				<th scope="col">&#160;</th>
			</tr>
		</thead>
		<tbody id="goals" class="form-table">
		<?php foreach( (array)$match->goals AS $g => $goal ) : ?>
			<?php $class1 = ( 'alternate' == $class1 ) ? '' : 'alternate'; ?>
			<tr id="goal_<?php echo $g ?>" class="<?php echo $class1 ?>">
				<td><input type="text" size="10" name="stats[goals][<?php echo $g ?>][time]" id="goal_time_<?php echo $g ?>" value="<?php echo $goal['time'] ?>" /></td>
				<td>
				<input type="text" size="20" name="stats[goals][<?php echo $g ?>][scorer]" id="goal_scorer_<?php echo $g ?>" value="<?php echo $goal['scorer'] ?>" />

				<?php if ( $league->hasBridge ) : ?>
				<div id="goal_scorer_box_<?php echo $g ?>" style="display: none; overflow: auto;" class="leaguemanager_thickbox">
				<?php echo $lmBridge->getPlayerSelection($goal['scorer'], "goal_scorer_roster_".$g); ?>
				<div style='text-align: center; margin-top: 1em;'><input type="button" value="<?php _e('Insert', 'leaguemanager') ?>" class="button-secondary" onClick="Leaguemanager.insertPlayer('goal_scorer_roster_<?php echo $g ?>', 'goal_scorer_<?php echo $g ?>'); return false;" />&#160;<input type="button" value="<?php _e('Cancel', 'leaguemanager') ?>" class="button-secondary" onClick="tb_remove();" /></div>
				</div>

				<span class="team_roster"><a class="thickbox" href="#TB_inline&height=100&width=300&inlineId=goal_scorer_box_<?php echo $g ?>" title="<?php _e( 'Add Player from Team Roster', 'leaguemanager' ) ?>"><img src="<?php echo LEAGUEMANAGER_URL ?>/admin/icons/player.png" border="0" alt="<?php _e('Insert Player', 'leaguemanager') ?>" /></a></span>
				<?php endif; ?>

				</td>
				<td><input type="text" size="5" name="stats[goals][<?php echo $g ?>][standing]" id="goal_standing_<?php echo $g ?>" value="<?php echo $goal['standing'] ?>" /></td>
				<td style="text-align: center; width: 12px; vertical-align: middle;"><a class="image_link" href="#" onclick='return Leaguemanager.removeField("goal_<?php echo $g ?>", "goals");'><img src="../wp-content/plugins/leaguemanager/images/trash.gif" alt="<?php _e( 'Delete', 'leaguemanager' ) ?>" title="<?php _e( 'Delete', 'leaguemanager' ) ?>" /></a>
			</tr>
		<?php endforeach; ?>
		</tbody>
		</table>
		
		<p><a href='#' onclick='return Leaguemanager.addGoal();'><?php _e( 'Insert more', 'leaguemanager' ) ?></a></p>


		<h3><?php _e( 'Cards', 'leaguemanager' ) ?></h3>
		<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><?php _e( 'Time' ) ?></th>
				<th scope="col"><?php _e( 'Player', 'leaguemanager' ) ?></th>
				<th scope="col"><?php _e( 'Card', 'leaguemanager' ) ?></th>
				<th scope="col">&#160;</th>
			</tr>
		</thead>
		<tbody id="cards" class="form-table">
			<?php foreach( (array)$match->cards AS $g => $card ) : ?>
			<?php $class2 = ( 'alternate' == $class2 ) ? '' : 'alternate'; ?>
			<tr id="card_<?php echo $g ?>" class="<?php echo $class2 ?>">
				<td><input type="text" size="10" name="stats[cards][<?php echo $g ?>][time]" id="card_time_<?php echo $g ?>" value="<?php echo $card['time'] ?>" /></td>
				<td>
				<input type="text" size="20" name="stats[cards][<?php echo $g ?>][player]" id="card_player_<?php echo $g ?>" value="<?php echo $card['player'] ?>" />

				<?php if ( $league->hasBridge ) : ?>
				<div id="cards_player_box_<?php echo $g ?>" style="overflow: auto; display: none;" class="leaguemanager_thickbox">
				<?php echo $lmBridge->getPlayerSelection($goal['player'], "card_player_roster_".$g); ?>
				<div style='text-align: center; margin-top: 1em;'><input type="button" value="<?php _e('Insert', 'leaguemanager') ?>" class="button-secondary" onClick="Leaguemanager.insertPlayer('card_player_roster_<?php echo $g ?>', 'card_player_<?php echo $g ?>'); return false;" />&#160;<input type="button" value="<?php _e('Cancel', 'leaguemanager') ?>" class="button-secondary" onClick="tb_remove();" /></div>
				</div>

				<span class="team_roster"><a class="thickbox" href="#TB_inline&height=100&width=300&inlineId=cards_player_box_<?php echo $g ?>" title="<?php _e( 'Add Player from Team Roster', 'leaguemanager' ) ?>"><img src="<?php echo LEAGUEMANAGER_URL ?>/admin/icons/player.png" border="0" alt="<?php _e('Insert Player', 'leaguemanager') ?>" /></a></span>
				<?php endif; ?>

				</td>
				<td>
					<select size="1" name="stats[cards][<?php echo $g ?>][type]>" id="card_type_<?php echo $g ?>">
					<?php foreach ( $leaguemanager->getCards() AS $key => $name ) : ?>
						<?php $selected = ( $key == $card['type'] ) ? ' selected="selected"' : '' ?>
						<option value="<?php echo $key ?>"<?php echo $selected ?>><?php echo $name ?></option>
					<?php endforeach; ?>
					</select>
				</td>
				<td style="text-align: center; width: 12px; vertical-align: middle;"><a class="image_link" href="#" onclick='return Leaguemanager.removeField("card_<?php echo $g ?>", "cards");'><img src="../wp-content/plugins/leaguemanager/images/trash.gif" alt="<?php _e( 'Delete', 'leaguemanager' ) ?>" title="<?php _e( 'Delete', 'leaguemanager' ) ?>" /></a>
			</tr>
		<?php endforeach; ?>
		</tbody>
		</table>
		
		<p><a href='#' onclick='return Leaguemanager.addCard();'><?php _e( 'Insert more', 'leaguemanager' ) ?></a></p>


		<h3><?php _e( 'Exchanges', 'leaguemanager' ) ?></h3>
		<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><?php _e( 'Time' ) ?></th>
				<th scope="col"><?php _e( 'Player in', 'leaguemanager' ) ?></th>
				<th scope="col"><?php _e( 'Player out', 'leaguemanager' ) ?></th>
				<th scope="col">&#160;</th>
			</tr>
		</thead>
		<tbody id="exchanges" class="form-table">
		<?php foreach( (array)$match->exchanges AS $g => $exchange ) : ?>
			<?php $class3 = ( 'alternate' == $class3 ) ? '' : 'alternate'; ?>
			<tr id="exchange_<?php echo $g ?>" class="<?php echo $class3 ?>">
				<td><input type="text" size="10" name="stats[exchanges][<?php echo $g ?>][time]" id="exchange_time_<?php echo $g ?>" value="<?php echo $exchange['time'] ?>" /></td>
				<td>
				<input type="text" size="20" name="stats[exchanges][<?php echo $g ?>][in]" id="exchange_in_<?php echo $g ?>" value="<?php echo $exchange['in'] ?>" />

				<?php if ( $league->hasBridge ) : ?>
				<div id="exchange_in_box_<?php echo $g ?>" style="overflow: auto; display: none;" class="leaguemanager_thickbox">
				<?php echo $lmBridge->getPlayerSelection($exchange['in'], "exchange_in_roster_".$g); ?>
				<div style='text-align: center; margin-top: 1em;'><input type="button" value="<?php _e('Insert', 'leaguemanager') ?>" class="button-secondary" onClick="Leaguemanager.insertPlayer('exchange_in_roster_<?php echo $g ?>', 'exchange_in_<?php echo $g ?>'); return false;" />&#160;<input type="button" value="<?php _e('Cancel', 'leaguemanager') ?>" class="button-secondary" onClick="tb_remove();" /></div>
				</div>

				<span class="team_roster"><a class="thickbox" href="#TB_inline&height=100&width=300&inlineId=exchange_in_box_<?php echo $g ?>" title="<?php _e( 'Add Player from Team Roster', 'leaguemanager' ) ?>"><img src="<?php echo LEAGUEMANAGER_URL ?>/admin/icons/player.png" border="0" alt="<?php _e('Insert Player', 'leaguemanager') ?>" /></a></span>
				<?php endif; ?>

				</td>
				<td>
				<input type="text" size="20" name="stats[exchanges][<?php echo $g ?>][out]" id="exchange_out_<?php echo $g ?>" value="<?php echo $exchange['out'] ?>" />

				<?php if ( $league->hasBridge ) : ?>
				<div id="exchange_out_box_<?php echo $g ?>" style="overflow: auto; display: none;" class="leaguemanager_thickbox">
				<?php echo $lmBridge->getPlayerSelection($exchange['out'], "exchange_out_roster_".$g); ?>
				<div style='text-align: center; margin-top: 1em;'><input type="button" value="<?php _e('Insert', 'leaguemanager') ?>" class="button-secondary" onClick="Leaguemanager.insertPlayer('exchange_out_roster_<?php echo $g ?>', 'exchange_out_<?php echo $g ?>'); return false;" />&#160;<input type="button" value="<?php _e('Cancel', 'leaguemanager') ?>" class="button-secondary" onClick="tb_remove();" /></div>
				</div>

				<span class="team_roster"><a class="thickbox" href="#TB_inline&height=100&width=300&inlineId=exchange_out_box_<?php echo $g ?>" title="<?php _e( 'Add Player from Team Roster', 'leaguemanager' ) ?>"><img src="<?php echo LEAGUEMANAGER_URL ?>/admin/icons/player.png" border="0" alt="<?php _e('Insert Player', 'leaguemanager') ?>" /></a></span>
				<?php endif; ?>

				</td>
				<td style="text-align: center; width: 12px; vertical-align: middle;"><a class="image_link" href="#" onclick='return Leaguemanager.removeField("exchange_<?php echo $g ?>", "exchanges");'><img src="../wp-content/plugins/leaguemanager/images/trash.gif" alt="<?php _e( 'Delete', 'leaguemanager' ) ?>" title="<?php _e( 'Delete', 'leaguemanager' ) ?>" /></a>
			</tr>
		<?php endforeach; ?>
		</tbody>
		</table>
		
		<p><a href='#' onclick='return Leaguemanager.addPlayerExchange();'><?php _e( 'Insert more', 'leaguemanager' ) ?></a></p>

		<?php do_action( 'leaguemanager_match_stats', &$match ) ?>

		<input type="hidden" name="match_id" value="<?php echo $match->id ?>" />
		<p class="submit"><input type="submit" name="updateMatchStats" value="<?php _e( 'Save Statistics', 'leaguemanager' ) ?> &raquo;" class="button" /></p>

		</form>
	</div>
<?php endif; ?>
