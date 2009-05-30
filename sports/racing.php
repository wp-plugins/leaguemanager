<?php
/**
 * Racing Class 
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright 	Copyright 2008-2009
*/
class LeagueManagerRacing extends LeagueManager
{
	/**
	 * sports keys
	 *
	 * @var string
	 */
	var $key = 'racing';


	/**
	 * load specific settings
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		add_filter( 'leaguemanager_sports', array(&$this, 'sports') );

		add_action('leaguemanager_custom_standings_'.$this->key, array(&$this, 'standingsTable'));
		add_action('leaguemanager_custom_matches_'.$this->key, array(&$this, 'matchTable'));
	}
	function LeagueManagerElectronic()
	{
		$this->__construct();
	}


	/**
	 * add sports to list
	 *
	 * @param array $sports
	 * @return array
	 */
	function sports( $sports )
	{
		$sports[$this->key] = __( 'Racing', 'leaguemanager' );
		return $sports;
	}


	/**
	 * custom standings table
	 *
	 * @param none
	 * @return void
	 */
	function standingsTable()
	{
		echo '<p>'.__('No Standings available for Racing', 'leaguemanager').'</p>';
	}


	/**
	 * custom match table
	 *
	 * @param none
	 * @return void
	 */
	function matchTable( $league )
	{
		global $leaguemanager;
		$match_search = '`league_id` = "'.$league->id.'" AND `final` = ""';
?>
		<table class="widefat" summary="" title="<?php _e( 'Match Plan','leaguemanager' ) ?>" style="margin-bottom: 2em;">
		<thead>
		<tr>
			<th><?php _e( 'Date','leaguemanager' ) ?></th>
			<th><?php _e( 'Location','leaguemanager' ) ?></th>
			<th><?php _e( 'Begin','leaguemanager' ) ?></th>
		</tr>
		</thead>
		<tbody id="the-list" class="form-table">
		<?php if ( $matches = $leaguemanager->getMatches( $match_search ) ) : $class2 = ''; ?>
			<?php foreach ( $matches AS $match ) : $class2 = ( 'alternate' == $class2 ) ? '' : 'alternate'; ?>
			<tr class="<?php echo $class2 ?>">
				<td><?php echo mysql2date(get_option('date_format'), $match->date) ?></td>
				<td><?php echo ( '' == $match->location ) ? 'N/A' : $match->location ?></td>
				<td><?php echo ( '00:00' == $match->hour.":".$match->minutes ) ? 'N/A' : mysql2date(get_option('time_format'), $match->date) ?></td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		</table>
	<?php
	}


	/**
	 * custom match editing form
	 *
	 * @param none
	 * @return void
	 */
	function matchForm()
	{
	}
}

$racing = new LeagueManagerRacing();
?>
