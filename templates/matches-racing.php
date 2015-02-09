<?php
/**
Template page for full racing results

The following variables are usable:
	
	$league: contains data of current league
	$matches: contains all matches for current league
	$teams: contains teams of current league in an assosiative array
	$season: current season
	$roster: ID of individual team member or false
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
if ( !empty($match->raceresult) ) {

}
?>
<?php if (isset($_GET['match']) ) : ?>
	<?php leaguemanager_match($_GET['match']); ?>
<?php else : ?>

<?php if ( ($league->show_match_day_selection || $league->show_team_selection) && $league->mode != 'championship' ) : ?>
<div style='float: left; margin-top: 1em;'>
<form method='get' action='<?php the_permalink(get_the_ID()) ?>'>
<div>
	<input type='hidden' name='page_id' value='<?php the_ID() ?>' />
	<input type="hidden" name="season" value="<?php echo $season ?>" />
	<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />

	<?php if ($league->show_match_day_selection) : ?>
	<select size='1' name='match_day'>
		<?php $selected = ( isset($_GET['match_day']) && $_GET['match_day'] == -1 ) ? ' selected="selected"' : ''; ?>
		<option value="-1"<?php echo $selected ?>><?php _e( 'Show all Matches', 'leaguemanager' ) ?></option>
	<?php for ($i = 1; $i <= $league->num_match_days; $i++) : ?>
		<option value='<?php echo $i ?>'<?php if ($leaguemanager->getMatchDay() == $i) echo ' selected="selected"'?>><?php printf(__( '%d. Match Day', 'leaguemanager'), $i) ?></option>
	<?php endfor; ?>
	</select>
	<?php endif; ?>
	<input type='submit' value='<?php _e('Show') ?>' />
</div>
</form>
</div>
<br style='clear: both;' />
<?php endif; ?>


<?php if ( $matches ) : ?>

<table class='leaguemanager matchtable' summary='' title='<?php echo __( 'Races', 'leaguemanager' )." ".$league->title ?>'>
<tr>
	<th><?php _e( 'Date', 'leaguemanager' ) ?></th>
	<?php if (!$roster) : ?>
	<th><?php _e( 'Name', 'leaguemanager' ) ?></th>
	<?php endif; ?>
	<th><?php _e( 'Points', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Time', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Event', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Category', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Race Type', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Other Info', 'leaguemanager' ) ?></th>
</tr>
<?php foreach ( $matches AS $match ) : ?>

<?php
if ($match && !empty($match->raceresult)) {
	$points = array();
	foreach ($match->raceresult AS $id => $racer) {
		$points[$id] = $racer['points'];
	}
	arsort($points);
}
?>

<?php if ( !empty($match->raceresult) ) : $class = ''; ?>
<?php foreach ( $points AS $id => $p ) : $racer = $match->raceresult[$id]; ?>

<?php if ( !$roster || ( $roster && ($roster == $id || $roster == $racer['name']) ) ) : ?>
<?php $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
<tr class='<?php echo $class ?>'>
	<td><?php echo $match->date ?></td>
	<?php if (!$roster) : ?>
	<td><?php echo $racer['name'] ?></td>
	<?php endif; ?>
	<td><?php echo $racer['points'] ?></td>
	<td><?php echo $racer['time'] ?></td>
	<td><a href="<?php echo $match->pageURL ?>"><?php echo $match->title ?></a></td>
	<td><?php echo $racer['category'] ?></td>
	<td><?php echo $match->racetype ?></td>
	<td><?php echo $racer['info'] ?></td>
</tr>
<?php endif; ?>

<?php endforeach; ?>
<?php endif; ?>

<?php endforeach; ?>
</table>

<?php endif; ?>

<?php endif; ?>
