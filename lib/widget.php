<?php
/** Widget class for the WordPress plugin LeagueManager
* 
* @author 	Kolja Schleich
* @package	LeagueManager
* @copyright 	Copyright 2008-2009
*/

class LeagueManagerWidget extends LeagueManager
{

	/**
	 * initialize
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		$this->loadOptions();
	}
	function LeagueManagerWidget()
	{
		$this->__construct();
	}
	
	
	/**
	 * register widget
	 *
	 * @param none
	 */
	function register()
	{
		if ( !function_exists('register_sidebar_widget') )
			return;
		
		// Add options
		add_option( 'leaguemanager_widget', array(), 'Leaguemanager Widget Options', 'yes' );
		foreach ( $this->getActiveLeagues() AS $league_id => $league ) {
			$name = __( 'League', 'leaguemanager' ) .' - '. $league['title'];
			$widget_id = sanitize_title($name);
			$widget_ops = array('classname' => 'widget_leaguemanager', 'description' => __('League results and upcoming matches at a glance', 'leaguemanager') );
			wp_register_sidebar_widget( sanitize_title($name), $name , array( &$this, 'display' ), $widget_ops );
			wp_register_widget_control( sanitize_title($name), $name, array( &$this, 'control' ), array('width' => 250, 'height' => 200), array( 'league_id' => $league_id, 'widget_id' => $widget_id ) );
		
			$this->options[$widget_id] = $league_id;
		}
		update_option( 'leaguemanager_widget', $this->options );
	}
	
	
	/**
	 * load Options
	 *
	 * @param none
	 * @return void
	 */
	function loadOptions()
	{
		$this->options = get_option( 'leaguemanager_widget' );
	}
	
	
	/**
	 * displays widget
	 *
	 * @param $args
	 *
	 */
	function display( $args )
	{
		global $leaguemanager_loader, $leaguemanager;
		
		$widget_id = $args['widget_id'];
		
		$defaults = array(
			'before_widget' => '<li id="'.sanitize_title(get_class($this)).'" class="widget '.get_class($this).'_'.__FUNCTION__.'">',
			'after_widget' => '</li>',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => '</h2>',
			'league_id' => $this->options[$widget_id],
			
		);
		$args = array_merge( $defaults, $args );
		extract( $args );
		
		$leaguemanager->setLeagueID($league_id); // set leagueID

		$options = $this->options[$league_id];
		$match_display = $options['match_display'];
		$table_display = $options['table_display'];
		$info_page_id = $options['info'];
		$date_format = $options['date_format'];
		$time_format = $options['time_format'];
		
		$league = parent::getLeague( $league_id );
		echo $before_widget . $before_title . $league->title . $after_title;
		
		echo "<div class='leaguemanager_widget'>";
		if ( $match_display != 'none' ) {
			$home_only = ( 'home' == $match_display ) ? true : false;
			
//			echo "<p class='title'>".__( 'Upcoming Matches', 'leaguemanager' )."</span>";
			
			//$match_limit = ( is_numeric($match_display) ) ? $match_display : false;
			$match_limit = 1;
			$next_matches = parent::getMatches( "league_id = '".$league_id."' AND DATEDIFF(NOW(), `date`) < 0", $match_limit );
			$prev_matches = parent::getMatches( "league_id = '".$league_id."' AND DATEDIFF(NOW(), `date`) > 0", $match_limit );
			$teams = parent::getTeams( $league_id, 'ARRAY' );
			
			if ( $prev_matches ) {
				echo "<div id='prev_matches'><div class='match_widget_content'>";
				echo "<h4>".__( 'Last Match', 'leaguemanager' )."</h4>";
				
				$logo['home'] = parent::getImageUrl($teams[$prev_matches[0]->home_team]['logo']);
				$logo['away'] = parent::getImageUrl($teams[$prev_matches[0]->away_team]['logo']);
				echo "<p class='match'><img src='".$logo['home']."' alt=".$teams[$prev_matches[0]->home_team]['title']." /> <img src='".$logo['away']."' alt=".$teams[$prev_matches[0]->away_team]['title']." /></p>";
				
				echo "<p class='match_day'><small>".sprintf(__("%d. Match Day", 'leaguemanager'), $prev_matches[0]->match_day)."</small></p>";
				
				echo "<p class='result'>".sprintf("%d - %d", $prev_matches[0]->home_points, $prev_matches[0]->away_points)."</p>";
				
				echo  "<p class='report'><a href='".get_permalink($prev_matches[0]->post_id)."'>".__( 'Report', 'leaguemanager' )."</a></p>";
				
				//$match = array();
				//foreach ( $matches AS $m ) {
				//	if ( !$home_only || ($home_only && (1 == $teams[$m->home_team]['home'] || 1 == $teams[$m->away_team]['home'])) ) {
				//		$start_time = ( $time_format == '' || ('00' == $m->hour && '00' == $m->minutes) ) ? '' : "(".mysql2date($time_format, $m->date).")";
				//		$date = mysql2date($date_format, $m->date);
				//		$match[$date][] = "<li>".$teams[$m->home_team]['short_title'] . "&#8211;" . $teams[$m->away_team]['short_title']." ".$start_time."</li>";
				//	}
				//}
				//foreach ( $match AS $date => $m )
				//	echo "<li><span class='title'>".$date."</span><ul>".implode("", $m)."</ul></li>";
				echo "</div></div>";
			}
			if ( $next_matches ) {
				echo "<div id='next_matches'><div class='match_widget_content'>";
				echo "<h4>".__( 'Next Match', 'leaguemanager' )."</h4>";
				
				$logo['home'] = parent::getImageUrl($teams[$next_matches[0]->home_team]['logo']);
				$logo['away'] = parent::getImageUrl($teams[$next_matches[0]->away_team]['logo']);
				echo "<p class='match'><img src='".$logo['home']."' alt=".$teams[$next_matches[0]->home_team]['title']." /> <img src='".$logo['away']."' alt=".$teams[$next_matches[0]->away_team]['title']." /></p>";
				
				echo "<p class='match_day'><small>".sprintf(__("%d. Match Day", 'leaguemanager'), $next_matches[0]->match_day)."</small></p>";
				
				$date_format = $date_format . " " . get_option('time_format');
				echo "<p class='date'><small>".mysql2date($date_format, $next_matches[0]->date)."</small></p>";
				
				echo "</div></div>";
			}
		}
		if ( 1 == $table_display ) {
			echo "<p class='title'>". __( 'Table', 'leaguemanager' ). "</p>";
			echo $leaguemanager_loader->shortcodes->showStandings( array('league_id' => $league_id, 'mode' => 'compact') );
		}
		//if ( $info_page_id AND '' != $info_page_id )
		//	echo "<li class='info'><a href='".get_permalink( $info_page_id )."'>".__( 'More Info', 'leaguemanager' )."</a></li>";

		echo "</div>";
		echo $after_widget;
	}


	/**
	 * widget control panel
	 *
	 * @param none
	 */
	function control( $args )
	{
		extract( $args );
		
		$options = get_option( 'leaguemanager_widget' );
		$options[$widget_id] = $league_id;
		$leaguemanager->setLeagueID($league_id); // set leagueID
		update_option( 'leaguemanager_widget', $options );
		
		echo '<p>'.sprintf(__( "The Widget Settings are controlled via the <a href='%s'>League Settings</a>", 'leaguemanager'), 'admin.php?page=leaguemanager/settings.php&league_id='.$league_id).'</p>';
	}
	
	
	/**
	 * get all active leagues
	 *
	 * @param none
	 * @return array
	 */
	function getActiveLeagues()
	{
		return ( parent::getLeagues( false, 'WHERE active = 1' ) );
	}
}

?>