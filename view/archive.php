<?php
/**
Template page for the Archive

The following variables are usable:
	
	$league_id: ID of league
	$season: current Season
	$seasons: available seasons of all leagues
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php $leagues = $leaguemanager->getLeagues(); ?>
<div id="leaguemanager_archive_selections">
	<form method="get" action="<?php get_permalink(get_the_ID()) ?>">
		<input type="hidden" name="page_id" value="<?php the_ID() ?>" />
		<select size="1" name="league_id">
			<option value=""><?php _e( 'League', 'leaguemanager' ) ?></option>
			<?php foreach ( $leagues AS $l_id => $league ) : ?>
			<option value="<?php echo $l_id ?>"<?php if ( $l_id == $league_id ) echo ' selected="selected"' ?>><?php echo $league['title'] ?></option>
			<?php endforeach ?>
		</select>
		<select size="1" name="season">
			<option value=""><?php _e( 'Season', 'leaguemanager' ) ?></option>
			<?php foreach ( $seasons AS $curr_season ) : ?>
			<option value="<?php echo $i ?>"<?php if ( $curr_season == $season ) echo ' selected="selected"' ?>><?php echo $curr_season ?></option>
			<?php endfor ?>
		</select>
		<input type="submit" class="submit" value="<?php _e( 'Show' ) ?>" />
	</form>
</div>

<!-- Standings Table -->
<?php leaguemanager_standings( $league_id, $season ) ?>

<!-- Match Overview -->
<?php leaguemanager_matches( $league_id, $season, '', true ) ?>

<!-- Crosstable -->
<?php leaguemanager_crosstable( $league_id, $season ) ?>