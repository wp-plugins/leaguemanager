<?php
/*
* Temporariliy just store some codde
*/


add_action( 'wp_ajax_leaguemanager_save_goals', 'leaguemanager_save_goals' );
add_action( 'wp_ajax_leaguemanager_save_cards', 'leaguemanager_save_cards' );
add_action( 'wp_ajax_leaguemanager_save_exchanges', 'leaguemanager_save_exchanges' );



/**
 * SACK response to save shot goals
 *
 * @since 2.9
 */
function leaguemanager_save_goals() {
	global $wpdb;
	$match_id = intval($_POST['match_id']);
	$goals = $_POST['goals'];
	//$goals = str_replace('-new-', "\n", $goals);
	
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `goals` = '%s' WHERE `id` = '%d'", $goals, $match_id ) );

	die("tb_remove();");
}

/**
 * SACK response to save cards
 *
 * @since 2.9
 */
function leaguemanager_save_cards() {
	global $wpdb;
	$match_id = intval($_POST['match_id']);
	$cards = $_POST['cards'];

	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `cards` = '%s' WHERE `id` = '%d'", $cards, $match_id ) );

	die("tb_remove();");
}

/**
 * SACK response to save exchanges
 *
 * @since 2.9
 */
function leaguemanager_save_exchanges() {
	global $wpdb;
	$match_id = intval($_POST['match_id']);
	$exchanges = $_POST['exchanges'];

	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `exchanges` = '%s' WHERE `id` = '%d'", $exchanges, $match_id ) );

	die("tb_remove();");
}

	/**
	 * get card name
	 *
	 * @param string $type
	 * @return nice card name
	 */
	function getCardName( $type )
	{
		$cards = array( 'red' => __( 'Red', 'leaguemanager' ), 'yellow' => __( 'Yellow', 'leaguemanager' ), 'yellow-red' => __( 'Yellow/Red', 'leaguemanager' ) );
		return $cards[$type];
	}



/*
* Match Admin Table
*/
/*
			<?php if ( $leaguemanager->getMatchParts($league->sport) ) : ?>
			<th><?php echo $leaguemanager->getMatchPartsTitle( $league->sport ) ?></th>
			<?php endif; ?>
			<?php if ( $leaguemanager->isIrishGaelicFootball( $league->id ) ) : ?>
			<th><?php _e( 'Goals', 'leaguemanager' ) ?></th>
			<?php else : ?>
			<th><?php _e( 'Score', 'leaguemanager' ) ?></th>
			<?php endif; ?>
			<?php if ( !$leaguemanager->isGymnasticsLeague( $league->id ) ) : ?>
			<th><?php _e( 'Overtime', 'leaguemanager' ) ?>*</th>
			<th><?php _e( 'Penalty', 'leaguemanager' ) ?>*</th>
			<th><?php _e( 'Goals', 'leaguemanager' ) ?></th>
			<th><?php _e( 'Cards', 'leaguemanager') ?></th>
			<th><?php _e( 'Exchanges', 'leaguemanager' ) ?></th>
			<?php endif; ?>

				<?php if ( $leaguemanager->getMatchParts( $league->sport ) ) : ?>
				<?php $points2 = maybe_unserialize( $match->points2 ); if ( !is_array($points2) ) $points2 = array($points2); ?>
				<td>
				<?php for ( $i = 1; $i <= $leaguemanager->getMatchParts($league->sport); $i++ ) : ?>
					<input class="points" type="text" size="2" id="home_points2_<?php echo $match->id ?>_<?php echo $i ?>" name="home_points2[<?php echo $match->id ?>][<?php echo $i ?>]" value="<?php echo $points2[$i-1]['plus'] ?>" /> : <input class="points" type="text" size="2" id="away_points_<?php echo $match->id ?>_<?php echo $i ?>" name="away_points2[<?php echo $match->id ?>][<?php echo $i ?>]" value="<?php echo $points2[$i-1]['minus'] ?>" />
					<br />
				<?php endfor; ?>
				</td>
				<?php endif; ?>

				<?php if ( !$leaguemanager->isGymnasticsLeague( $league->id ) ) : ?>
				<?php $match->overtime = maybe_unserialize($match->overtime); if ( !is_array($match->overtime) ) $match->overtime = array(); ?>
				<?php $match->penalty = maybe_unserialize($match->penalty); if ( !is_array($match->penalty) ) $match->penalty = array(); ?>
				<td>
					<input class="points" type="text" size="2" id="overtime_home_<?php echo $match->id ?>" name="overtime[<?php echo $match->id ?>][home]" value="<?php echo $match->overtime['home'] ?>" /> : <input class="points" type="text" size="2" id="overtime_away_<?php echo $match->id ?>" name="overtime[<?php echo $match->id ?>][away]" value="<?php echo $match->overtime['away'] ?>" />
				</td>
				<td>
					<input class="points" type="text" size="2" id="penalty_home_<?php echo $match->id ?>" name="penalty[<?php echo $match->id ?>][home]" value="<?php echo $match->penalty['home'] ?>" /> : <input class="points" type="text" size="2" id="penalty_away_<?php echo $match->id ?>" name="penalty[<?php echo $match->id ?>][away]" value="<?php echo $match->penalty['away'] ?>" />
				</td>
				<?php endif; ?>
				<td>
					<?php $match->goals = explode("-new-", $match->goals); ?>
					<div id="goals_container<?php echo $match->id ?>">
						<div id="goals_div<?php echo $match->id ?>" style="width: 400px; height: 400px;" class="leaguemanager_thickbox"><form>
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
							<?php foreach( $match->goals AS $g => $goal ) : ?>
							<?php if ( !empty($goal) || count($match->goals) == 1 ) :  $goal = explode(";", $goal); ?>
								<?php $class3 = ( 'alternate' == $class3 ) ? '' : 'alternate'; ?>
								<tr id="goal_<?php echo $match->id ?>" class="<?php echo $class3 ?>">
									<td><input type="text" size="10" name="goal_time_<?php echo $match->id ?>" id="goal_time_<?php echo $match->id ?>_<?php echo $g ?>" value="<?php echo $goal[0] ?>" /></td>
									<td>
										<input type="text" size="20" name="goal_scorer_<?php echo $match->id ?>" id="goal_scorer_<?php echo $match->id ?>_<?php echo $g ?>" value="<?php echo $goal[1] ?>" />
										<?php if ( $leaguemanager->isBridge() ) : ?>
										<?php _e( 'OR', 'leaguemanager' ) ?> <?php echo $lmBridge->getPlayerSelection($goal[1], "goal_scorer_".$match->id, "goal_scorer_".$match->id."_".$g); ?>
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
						<div style="text-align:center; margin-top: 1em;"><input type="button" value="<?php _e('Save') ?>" class="button-secondary" onclick="Leaguemanager.ajaxSaveGoals(<?php echo $match->id; ?>);return false;" />&#160;<input type="button" value="<?php _e('Cancel') ?>" class="button" onclick="tb_remove();" /></div></form>
						</div>
					</div>
					<span>&#160;<a href='#TB_inline?width=400&heigth=400&inlineId=goals_div<?php echo $match->id ?>' style="display: inline;" id="goals_link<?php echo $match->id ?>" class="thickbox" title="<?php _e('Insert Goals', 'leaguemanager' ) ?>"><?php _e('Insert') ?></a></span>
				</td>
				<td>
					<?php $match->cards = explode("-new-", $match->cards); ?>
					<div id="cards_container<?php echo $match->id ?>" style="display: inline;">
						<div id="cards_div<?php echo $match->id ?>" style="width: 400px; height: 400px;" class="leaguemanager_thickbox"><form>
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
							<?php foreach( $match->cards AS $g => $card ) : ?>
							<?php if ( !empty($card) || count($match->cards) == 1 ) : $card = explode(";", $card); ?>
								<?php $class4 = ( 'alternate' == $class4 ) ? '' : 'alternate'; ?>
								<tr id="card_<?php echo $match->id ?>" class="<?php echo $class4 ?>">
									<td><input type="text" size="10" name="card_time_<?php echo $match->id ?>" id="card_time_<?php echo $match->id ?>_<?php echo $g ?>" value="<?php echo $card[0] ?>" /></td>
									<td>
										<?php if ( $leaguemanager->isBridge() ) : ?>
										<?php echo $lmBridge->getPlayerSelection($goal[1], "card_player_".$match->id, "card_player_".$match->id."_".$g); ?>
										<?php else : ?>
										<input type="text" size="20" name="card_player_<?php echo $match->id ?>" id="card_player_<?php echo $match->id ?>_<?php echo $g ?>" value="<?php echo $card[1] ?>" />
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
						<div style="text-align:center; margin-top: 1em;"><input type="button" value="<?php _e('Save') ?>" class="button-secondary" onclick="Leaguemanager.ajaxSaveCards(<?php echo $match->id; ?>);return false;" />&#160;<input type="button" value="<?php _e('Cancel') ?>" class="button" onclick="tb_remove();" /></div></form>
						</div>
					</div>
					<span>&#160;<a href='#TB_inline?width=400&heigth=400&inlineId=cards_div<?php echo $match->id ?>' style="display: inline;" id="cards_link<?php echo $match->id ?>" class="thickbox" title="<?php _e('Insert Cards', 'leaguemanager' ) ?>"><?php _e('Insert') ?></a></span>
				</td>
				<td>
					<?php $match->exchanges = explode("-new-", $match->exchanges); ?>
					<div id="exchanges_container<?php echo $match->id ?>" style="display: inline;">
						<div id="exchanges_div<?php echo $match->id ?>" style="width: 400px; height: 400px;" class="leaguemanager_thickbox"><form>
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
							<?php foreach( $match->exchanges AS $g => $exchange ) : ?>
							<?php if ( !empty($exchange) || count($match->exchanges) == 1 ) :  $exchange = explode(";", $exchange); ?>
								<?php $class5 = ( 'alternate' == $class5 ) ? '' : 'alternate'; ?>
								<tr id="exchange_<?php echo $match->id ?>" class="<?php echo $class5 ?>">
									<td><input type="text" size="10" name="exchange_time_<?php echo $match->id ?>" id="exchange_time_<?php echo $match->id ?>_<?php echo $g ?>" value="<?php echo $exchange[0] ?>" /></td>
									<td>
										<?php if ( $leaguemanager->isBridge() ) : ?>
										<?php echo $lmBridge->getPlayerSelection($goal[1], "exchange_in_".$match->id, "exchange_in_".$match->id."_".$g); ?>
										<?php else : ?>
										<input type="text" size="20" name="exchange_in_<?php echo $match->id ?>" id="exchange_in_<?php echo $match->id ?>_<?php echo $g ?>" value="<?php echo $exchange[1] ?>" />
										<?php endif; ?>
									</td>
									<td>
										<?php if ( $leaguemanager->isBridge() ) : ?>
										<?php echo $lmBridge->getPlayerSelection($goal[1], "exchange_out_".$match->id, "exchange_out_".$match->id."_".$g); ?>
										<?php else : ?>
										<input type="text" size="20" name="exchange_out_<?php echo $match->id ?>" id="exchange_out_<?php echo $match->id ?>_<?php echo $g ?>" value="<?php echo $exchange[2] ?>" />
										<?php endif; ?>
									</td>
									<td style="text-align: center; width: 12px; vertical-align: middle;"><a class="image_link" href="#" onclick='return Leaguemanager.removeField("exchange_<?php echo $match->id ?>", "exchanges_<?php echo $match->id ?>");'><img src="../wp-content/plugins/leaguemanager/images/trash.gif" alt="<?php _e( 'Delete', 'leaguemanager' ) ?>" title="<?php _e( 'Delete', 'leaguemanager' ) ?>" /></a>
								</tr>
							<?php endif; ?>
							<?php endforeach; ?>
						</tbody>
						</table>
						<p><a href='#' onclick='return Leaguemanager.addPlayerExchange(<?php echo $match->id ?>);'><?php _e( 'Insert more', 'leaguemanager' ) ?></a></p>
						<div style="text-align:center; margin-top: 1em;"><input type="button" value="<?php _e('Save') ?>" class="button-secondary" onclick="Leaguemanager.ajaxSaveExchanges(<?php echo $match->id; ?>);return false;" />&#160;<input type="button" value="<?php _e('Cancel') ?>" class="button" onclick="tb_remove();" /></div></form>
						</div>
					</div>
					<span>&#160;<a href='#TB_inline?width=400&heigth=400&inlineId=exchanges_div<?php echo $match->id ?>' style="display: inline;" id="goals_link<?php echo $match->id ?>" class="thickbox" title="<?php _e('Insert Exchanges', 'leaguemanager' ) ?>"><?php _e('Insert') ?></a></span>
				</td>
				</td>

		<?php if ( !$leaguemanager->isGymnasticsLeague( $league->id ) ) : ?>
		<p class="info"><span class="setting-description">*<?php _e( 'Always enter final results after overtime and penalty. Leave empty if not needed.', 'leaguemanager' ) ?></span></p>
		<?php endif; ?>
*/
?>
