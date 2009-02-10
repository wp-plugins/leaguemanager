<?php
/**
Template page for the match table

The following variables are usable:
	
	$league: contains data of current league
	$matches: contains all matches for current league
	$teams: contains teams of current league in an assosiative array
	$all: boolean, if true all matches are displayed in a single table without weekly ordering
	$home_only: boolean, if true only matches of home team are displayed without weekly ordering
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php if ( !$all && !$home_only && $league->num_match_days > 0 ) : ?>
<div style='float: left; margin-top: 1em;'>
	<form method='get' action='<?php the_permalink(get_the_ID()) ?>'>
	<input type='hidden' name='page_id' value='<?php the_ID() ?>' />
		<select size='1' name='match_day'>
		<?php for ($i = 1; $i <= $league->num_match_days; $i++) : ?>
			<option value='<?php echo $i ?>'<?php if ($leaguemanager->getMatchDay(true) == $i) echo ' selected="selected"'?>><?php printf(__( '%d. Match Day', 'leaguemanager'), $i) ?></option>
		<?php endfor; ?>
		</select>&#160;
		<input type='submit' value='<?php _e('Show') ?>' />
	</form>
</div>
<br style='clear: both;' />
<?php endif; ?>
	
<?php if ( $matches ) : ?>

<table class='leaguemanager matchtable' summary='' title='<?php _e( 'Match Plan', 'leaguemanager' )." ".$league->title ?>'>
<tr>
	<th class='match'><?php _e( 'Match', 'leaguemanager' ) ?></th>
	<th class='score'><?php _e( 'Score', 'leaguemanager' ) ?></th>
	<?php if ( $league->isGymnastics ) : ?>
	<th class='ap'><?php _e( 'AP', 'leaguemanager' ) ?></th>
	<?php endif; ?>
</tr>
<?php foreach ( $matches AS $match ) : ?>

<?php if ( ( !$all && !$home_only ) || $all || ( $home_only && (1 == $teams[$match->home_team]['home'] || 1 == $teams[$match->away_team]['home'])) ) : ?>

<tr class='<?php echo $match->class ?>'>
	<td class='match'><?php echo mysql2date(get_option('date_format'), $match->date)." ".$match->start_time." ".$match->location ?><br /><?php echo $match->title." ".$match->report ?></td>
	<td class='score' valign='bottom'><?php echo $match->score ?></td>
	<?php if ( $league->isGymnastics ) : ?>
	<td class='ap' valign='bottom'><?php echo $match->home_apparatus_points.":".$match->away_apparatus_points ?></td>
	<?php endif; ?>
</tr>

<?php endif; endforeach; ?>
</table>

<?php endif; ?>