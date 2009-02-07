<?php
/**
Template page for the match table

The following variables are usable:
	
	$league_id: contains ID of current league
	$matches: contains all matches for current league
	$teams: contains teams of current league in an assosiative array
	$preferences: preferences of current league
	$all: boolean, if true all matches are displayed in a single table without weekly ordering
	$home_only: boolean, if true only matches of home team are displayed without weekly ordering
	$page_ID: contains ID of current page
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php if ( !$all && !$home_only && $preferences->num_match_days > 0 ) : ?>
<div style='float: left; margin-top: 1em;'>
	<form method='get' action='<?php echo get_permalink($page_ID) ?>'>
	<input type='hidden' name='page_id' value='<?php echo $page_ID ?>' />
		<select size='1' name='match_day'>
		<?php for ($i = 1; $i <= $preferences->num_match_days; $i++) : ?>
			<option value='<?php echo $i ?>'<?php if ($this->getMatchDay(true) == $i) echo ' selected="selected"'?>><?php printf(__( '%d. Match Day', 'leaguemanager'), $i) ?></option>
		<?php endfor; ?>
		</select>&#160;
		<input type='submit' value='<?php _e('Show') ?>' />
	</form>
</div>
<br style='clear: both;' />
<?php endif; ?>
	
<?php if ( $matches ) : ?>

<table class='leaguemanager matchtable' summary='' title='<?php _e( 'Match Plan', 'leaguemanager' )." ".$leagues['title'] ?>'>
<tr>
	<th class='match'><?php _e( 'Match', 'leaguemanager' ) ?></th>
	<th class='score'><?php _e( 'Score', 'leaguemanager' ) ?></th>
	<?php if ( parent::isGymnasticsLeague( $league_id ) ) : ?>
	<th class='ap'><?php _e( 'AP', 'leaguemanager' ) ?></th>
	<?php endif; ?>
</tr>
<?php foreach ( $matches AS $match ) : ?>

<?php
$match->home_apparatus_points = ( NULL == $match->home_apparatus_points ) ? '-' : $match->home_apparatus_points;
$match->away_apparatus_points = ( NULL == $match->away_apparatus_points ) ? '-' : $match->away_apparatus_points;
$match->home_points = ( NULL == $match->home_points ) ? '-' : $match->home_points;
$match->away_points = ( NULL == $match->away_points ) ? '-' : $match->away_points;

if ( ( !$all && !$home_only ) || $all || ( $home_only && (1 == $teams[$match->home_team]['home'] || 1 == $teams[$match->away_team]['home'])) ) :

$class = ( 'alternate' == $class ) ? '' : 'alternate';
$start_time = ( '00' == $match->hour && '00' == $match->minutes ) ? '' : mysql2date(get_option('time_format'), $match->date);

$match_title = $teams[$match->home_team]['title'].' - '. $teams[$match->away_team]['title'];
if ( parent::isHomeTeamMatch( $match->home_team, $match->away_team, $teams ) ) $match_title = '<strong>'.$match_title.'</strong>';

$match_report = ( $match->post_id != 0 ) ? '(<a href="'.get_permalink($match->post_id).'">'.__('Report', 'leaguemanager').'</a>)' : '';

$score = ( parent::isGymnasticsLeague($league_id) ) ? $match->home_points.":".$match->away_points : $match->home_points.":".$match->away_points." (".$match->home_apparatus_points.":".$match->away_apparatus_points.")";
?>
<tr class='<?php echo $class ?>'>
	<td class='match'><?php echo mysql2date(get_option('date_format'), $match->date)." ".$start_time." ".$match->location ?><br /><?php echo $match_title." ".$match_report ?></td>
	<td class='score' valign='bottom'><?php echo $score ?></td>
	<?php if ( $this->isGymnasticsLeague( $league_id ) ) : ?>
	<td class='ap' valign='bottom'><?php echo $match->home_apparatus_points.":".$match->away_apparatus_points ?></td>
	<?php endif; ?>
</tr>
<?php endif; endforeach; ?>
</table>

<?php endif; ?>