<?php
/**
Template page for the match table

The following variables are usable:
	
	$league: contains data of current league
	$matches: contains all matches for current league
	$teams: contains teams of current league in an assosiative array
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php if ( $matches ) : $season = $leaguemanager->getSeason( &$league ); ?>

<?php if ( $league->match_days  ) : ?>
<div style='float: left; margin-top: 1em;'>
	<form method='get' action='<?php the_permalink(get_the_ID()) ?>'>
	<input type='hidden' name='page_id' value='<?php the_ID() ?>' />
	<input type="hidden" name="season" value="<?php echo $season['name'] ?>" />
	<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
		<select size='1' name='match_day'>
		<?php for ($i = 1; $i <= $season['num_match_days']; $i++) : ?>
			<option value='<?php echo $i ?>'<?php if ($leaguemanager->getMatchDay($league->isCurrMatchDay) == $i) echo ' selected="selected"'?>><?php printf(__( '%d. Match Day', 'leaguemanager'), $i) ?></option>
		<?php endfor; ?>
		</select>
		<select size="1" name="team_id">
		<option value=""><?php _e( 'Chose Team', 'leaguemanager' ) ?></option>
		<?php foreach ( $teams AS $team_id => $team ) : ?>
			<?php $selected = (isset($_GET['team_id']) && $_GET['team_id'] == $team_id) ? ' selected="selected"' : ''; ?>
			<option value="<?php echo $team_id ?>"<?php echo $selected ?>><?php echo $team['title'] ?></option>
		<?php endforeach; ?>
		</select>
		<input type='submit' value='<?php _e('Show') ?>' />
	</form>
</div>
<br style='clear: both;' />
<?php endif; ?>
	
<?php if ( $matches ) : ?>

<table class='leaguemanager matchtable' summary='' title='<?php echo __( 'Match Plan', 'leaguemanager' )." ".$league->title ?>'>
<tr>
	<th class='match'><?php _e( 'Match', 'leaguemanager' ) ?></th>
	<th class='score'><?php _e( 'Score', 'leaguemanager' ) ?></th>
	<?php if ( $league->isGymnastics ) : ?>
	<th class='ap'><?php echo _c( 'AP|apparatus points', 'leaguemanager' ) ?></th>
	<?php endif; ?>
</tr>
<?php foreach ( $matches AS $match ) : ?>

<tr class='<?php echo $match->class ?>'>
	<td class='match'><?php echo mysql2date(get_option('date_format'), $match->date)." ".$match->start_time." ".$match->location ?><br /><?php echo $match->title." ".$match->report ?></td>
	<td class='score' valign='bottom'><?php echo $match->score ?></td>
	<?php if ( $league->isGymnastics ) : ?>
	<td class='ap' valign='bottom'><?php echo $match->apparatus_points ?></td>
	<?php endif; ?>
</tr>

<?php endforeach; ?>
</table>

<?php endif; ?>

<?php endif; ?>
