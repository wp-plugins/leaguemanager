<?php
/**
Template page for the standings table in compact form

The following variables are usable:
	
	$league: contains data about the league
	$teams: contains all teams of current league
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
$racing = new LeagueManagerRacing();
$racer = $racing->getRacerResults($teams);
$racer_id = isset($_GET['show']) ? intval($_GET['show']) : false;
?>
<?php if ( isset($_GET['team']) && !$widget ) : ?>
	<?php global $lmShortcodes; $lmShortcodes->showTeam( array('id' => intval($_GET['team']), 'echo' => 1) ) ?>
<?php elseif ( $racer_id ) : ?>
	<?php do_action('projectmanager_dataset', array('id' => $racer_id, 'echo' => 1), true) ?>
<?php else : ?>

<?php if ( $teams ) : ?>

<table class="leaguemanager standingstable" summary="" title="<?php _e( 'Standings', 'leaguemanager' ) .' '.$league->title ?>">
<tr>
	<th class="num"><?php echo _e( 'Pos', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Racer', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Team', 'leaguemanager' ) ?></th>
	<th class="num"><?php _e( 'Pts', 'leaguemanager' ) ?></th>
</tr>
<?php if ( $racer ) : ?>
<?php foreach( $racer AS $r ) : ?>

<tr class='<?php echo $r['class'] ?>'>
	<td class='rank'><?php echo $r['rank'] ?></td>
	<td><?php echo $r['name'] ?></td>
	<td>
	<?php if ( $r['team_logo'] != '' ) : ?>
		<img src='<?php echo $r['team_logo_url'] ?>' alt='<?php _e('Logo','leaguemanager') ?>' title='<?php _e('Logo','leaguemanager')." ".$r['team_name'] ?>' />
	<?php endif; ?>
	<?php echo $r['team_name']; ?>
	</td>
	<td class='num'><?php echo $r['points'] ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>

<?php endif; ?>

<?php endif; ?>
