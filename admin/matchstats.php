<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
else :
	$match = $leaguemanager->getMatch( $_GET['match_id'] );
	$league = $leaguemanager->getLeague( $match->league_id );

	$home = $leaguemanager->getTeam($match->home_team);
	$away = $leaguemanager->getTeam($match->away_team);
?>

	<div class="wrap">
		<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league->id ?>"><?php echo $league->title ?></a> &raquo; <?php _e( 'Match Statistics', 'leaguemanager' ) ?></p>

		<h2><?php printf(__( 'Match Statistics &#8211; %s v.s. %s', 'leaguemanager'), $home->title, $away->title) ?></h2>

		<form action="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league->id ?>" method="post">

		<div class="narrow">

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
		<tbody id="goals_<?php echo $match->id ?>" class="form-table">
		<?php foreach( (array)$match->goals AS $g => $goal ) : ?>
			<?php if ( !empty($goal) ) :  $goal = explode(";", $goal); ?>
			<?php $class1 = ( 'alternate' == $class1 ) ? '' : 'alternate'; ?>
			<tr id="goal_<?php echo $match->id ?>" class="<?php echo $class1 ?>">
				<td><input type="text" size="10" name="goal_time_<?php echo $match->id ?>" id="goal_time_<?php echo $match->id ?>_<?php echo $g ?>" value="<?php echo $goal[0] ?>" /></td>
				<td>
				<input type="text" size="20" name="goal_scorer_<?php echo $match->id ?>" id="goal_scorer_<?php echo $match->id ?>_<?php echo $g ?>" value="<?php echo $goal[1] ?>" />
				<?php if ( $leaguemanager->isBridge() ) : ?>
				<?php echo __('OR', 'leaguemanager') . $lmBridge->getPlayerSelection($goal[1], "goal_scorer_".$match->id, "goal_scorer_".$match->id."_".$g); ?>
				<?php endif; ?>
				</td>
				<td><input type="text" size="5" name="goal_standing_<?php echo $match->id ?>" id="goal_standing_<?php echo $match->id ?>_<?php echo $g ?>" value="<?php echo $goal[2] ?>" /></td>
				<td style="text-align: center; width: 12px; vertical-align: middle;"><a class="image_link" href="#" onclick='return Leaguemanager.removeField("goal_<?php echo $match->id ?>", "goals_<?php echo $match->id ?>");'><img src="../wp-content/plugins/leaguemanager/images/trash.gif" alt="<?php _e( 'Delete', 'leaguemanager' ) ?>" title="<?php _e( 'Delete', 'leaguemanager' ) ?>" /></a>
			</tr>
			<?php endif; ?>
		<?php endforeach; ?>
		</tbody>
		</table>
		
		<p><a href='#' onclick='return Leaguemanager.addGoal(<?php echo $match->id ?>);'><?php _e( 'Insert more', 'leaguemanager' ) ?></a></p>


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
		<tbody id="cards_<?php echo $match->id ?>" class="form-table">
			<?php foreach( (array)$match->cards AS $g => $card ) : ?>
			<?php if ( !empty($card) ) : $card = explode(";", $card); ?>
			<?php $class2 = ( 'alternate' == $class2 ) ? '' : 'alternate'; ?>
			<tr id="card_<?php echo $match->id ?>" class="<?php echo $class2 ?>">
				<td><input type="text" size="10" name="card_time_<?php echo $match->id ?>" id="card_time_<?php echo $match->id ?>_<?php echo $g ?>" value="<?php echo $card[0] ?>" /></td>
				<td>
				<input type="text" size="20" name="card_player_<?php echo $match->id ?>" id="card_player_<?php echo $match->id ?>_<?php echo $g ?>" value="<?php echo $card[1] ?>" />
				<?php if ( $leaguemanager->isBridge() ) : ?>
					<?php echo __('OR', 'leaguemanager') . $lmBridge->getPlayerSelection($goal[1], "card_player_".$match->id, "card_player_".$match->id."_".$g); ?>
				<?php endif; ?>
				</td>
				<td>
					<select size="1" name="card_type_<?php echo $match->id ?>" id="card_type_<?php echo $match->id ?>_<?php echo $g ?>">
						<option value="yellow"<?php if ( 'yellow' == $card[2] ) echo ' selected="selected"' ?>><?php _e( 'Yellow', 'leaguemanager' ) ?></option>
						<option value="red"<?php if ( 'red' == $card[2] ) echo ' selected="selected"' ?>><?php _e( 'Red', 'leaguemanager' ) ?></option>
						<option value="yellow-red"<?php if ( 'yellow-red' == $card[2] ) echo ' selected="selected"' ?>><?php _e( 'Yellow/Red', 'leaguemanager' ) ?></option>
					</select>
				</td>
				<td style="text-align: center; width: 12px; vertical-align: middle;"><a class="image_link" href="#" onclick='return Leaguemanager.removeField("card_<?php echo $match->id ?>", "cards_<?php echo $match->id ?>");'><img src="../wp-content/plugins/leaguemanager/images/trash.gif" alt="<?php _e( 'Delete', 'leaguemanager' ) ?>" title="<?php _e( 'Delete', 'leaguemanager' ) ?>" /></a>
			</tr>
			<?php endif; ?>
		<?php endforeach; ?>
		</tbody>
		</table>
		
		<p><a href='#' onclick='return Leaguemanager.addCard(<?php echo $match->id ?>);'><?php _e( 'Insert more', 'leaguemanager' ) ?></a></p>


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
		<tbody id="exchanges_<?php echo $match->id ?>" class="form-table">
		<?php foreach( (array)$match->exchanges AS $g => $exchange ) : ?>
			<?php if ( !empty($exchange) ) :  $exchange = explode(";", $exchange); ?>
			<?php $class3 = ( 'alternate' == $class3 ) ? '' : 'alternate'; ?>
			<tr id="exchange_<?php echo $match->id ?>" class="<?php echo $class3 ?>">
				<td><input type="text" size="10" name="exchange_time_<?php echo $match->id ?>" id="exchange_time_<?php echo $match->id ?>_<?php echo $g ?>" value="<?php echo $exchange[0] ?>" /></td>
				<td>
				<input type="text" size="20" name="exchange_in_<?php echo $match->id ?>" id="exchange_in_<?php echo $match->id ?>_<?php echo $g ?>" value="<?php echo $exchange[1] ?>" />
				<?php if ( $leaguemanager->isBridge() ) : ?>
					<?php echo __('OR', 'leaguemanager') . $lmBridge->getPlayerSelection($goal[1], "exchange_in_".$match->id, "exchange_in_".$match->id."_".$g); ?>
				<?php endif; ?>
				</td>
				<td>
				<input type="text" size="20" name="exchange_out_<?php echo $match->id ?>" id="exchange_out_<?php echo $match->id ?>_<?php echo $g ?>" value="<?php echo $exchange[2] ?>" />
				<?php if ( $leaguemanager->isBridge() ) : ?>
					<?php echo __('OR', 'leaguemanager') . $lmBridge->getPlayerSelection($goal[1], "exchange_out_".$match->id, "exchange_out_".$match->id."_".$g); ?>
				<?php endif; ?>
				</td>
				<td style="text-align: center; width: 12px; vertical-align: middle;"><a class="image_link" href="#" onclick='return Leaguemanager.removeField("exchange_<?php echo $match->id ?>", "exchanges_<?php echo $match->id ?>");'><img src="../wp-content/plugins/leaguemanager/images/trash.gif" alt="<?php _e( 'Delete', 'leaguemanager' ) ?>" title="<?php _e( 'Delete', 'leaguemanager' ) ?>" /></a>
			</tr>
			<?php endif; ?>
		<?php endforeach; ?>
		</tbody>
		</table>
		
		<p><a href='#' onclick='return Leaguemanager.addPlayerExchange(<?php echo $match->id ?>);'><?php _e( 'Insert more', 'leaguemanager' ) ?></a></p>

		<input type="hidden" name="match_id" value="<?php echo $match->id ?>" />
		<p class="submit"><input type="submit" name="updatMatchStats" value="<?php _e( 'Save Statistics', 'leaguemanager' ) ?> &raquo;" class="button" /></p>

		</div>
	</div>
<?php endif; ?>
