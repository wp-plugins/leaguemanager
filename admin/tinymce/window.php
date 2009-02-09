<?php

$root = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));

if (file_exists($root.'/wp-load.php')) {
	// WP 2.6
	require_once($root.'/wp-load.php');
} else {
	// Before 2.6
	if (!file_exists($root.'/wp-config.php'))  {
		echo "Could not find wp-config.php";	
		die;
	}// stop when wp-config is not there
	require_once($root.'/wp-config.php');
}

require_once(ABSPATH.'/wp-admin/admin.php');

// check for rights
if(!current_user_can('edit_posts')) die;

global $wpdb;

?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php _e('Leaguemanager', 'leaguemanager') ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo LEAGUEMANAGER_URL ?>/admin/tinymce/tinymce.js"></script>
	<base target="_self" />
</head>
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';document.getElementById('table_tag').focus();" style="display: none">
<!-- <form onsubmit="insertLink();return false;" action="#"> -->
<form name="LeagueManager" action="#">
	<div class="tabs">
		<ul>
			<li id="table_tab" class="current"><span><a href="javascript:mcTabs.displayTab('table_tab', 'table_panel');" onmouseover="return false;"><?php _e( "Table", 'leaguemanager' ); ?></a></span></li>
			<li id="match_tab"><span><a href="javascript:mcTabs.displayTab('match_tab', 'match_panel');" onmouseover="return false;"><?php _e( "Matches", 'leaguemanager' ); ?></a></span></li>
			<li id="crosstable_tab"><span><a href="javascript:mcTabs.displayTab('crosstable_tab', 'crosstable_panel');" onmouseover="return false;"><?php _e( "Crosstable", 'leaguemanager' ); ?></a></span></li>
		</ul>
	</div>
	<?php echo $root; ?>
	<div class="panel_wrapper">
	<!-- table panel -->
	<div id="table_panel" class="panel current"><br />
	<table style="border: 0;" cellpadding="5">
	<tr>
		<td><label for="table_tag"><?php _e("League", 'leaguemanager'); ?></label></td>
		<td>
		<select id="table_tag" name="table_tag" style="width: 200px">
        	<option value="0"><?php _e("No League", 'leaguemanager'); ?></option>
		<?php
			$leaguelist = $wpdb->get_results("SELECT * FROM {$wpdb->leaguemanager} ORDER BY id DESC");
			if( is_array($leaguelist) ) {
			foreach( $leaguelist as $league )
				echo '<option value="'.$league->id.'" >'.$league->title.'</option>'."\n";
			}
		?>
        	</select>
		</td>
	</tr>
	<tr>
		<td><label for="standings_display"><?php _e( "Display", 'leaguemanager' ) ?></label></td>
		<td>
			<select size="1" name="standings_display" id="standings_display">
				<option value="extend"><?php _e( 'Extend', 'leaguemanager' ) ?></option>
				<option value="compact"><?php _e( 'Compact', 'leaguemanager' ) ?></option>
			</select>
		</td>
	</tr>
	</table>
	</div>
	
	<!-- match panel -->
	<div id="match_panel" class="panel"><br/>
	<table  style="border: 0;" cellpadding="5">
	<tr>
		<td><label for="match_tag"><?php _e("League", 'leaguemanager'); ?></label></td>
		<td>
		<select id="match_tag" name="match_tag" style="width: 200px">
        	<option value="0"><?php _e("No League", 'leaguemanager'); ?></option>
		<?php
			$leaguelist = $wpdb->get_results("SELECT * FROM {$wpdb->leaguemanager} ORDER BY id DESC");
			if( is_array($leaguelist) ) {
			foreach( $leaguelist as $league )
				echo '<option value="'.$league->id.'" >'.$league->title.'</option>'."\n";
			}
		?>
        	</select>
		</td>
	</tr>
	<tr>
		<td><label for="match_display"><?php _e( "Display", 'leaguemanager' ) ?></label></td>
		<td>
			<select size="1" name="match_display" id="match_display">
				<option value=""><?php _e( 'Match day based', 'leaguemanager' ) ?></option>
				<option value="all"><?php _e( 'All', 'leaguemanager' ) ?></option>
				<option value="home"><?php _e( 'Only Home Team', 'leaguemanager' ) ?></option>
			</select>
		</td>
	</tr>
	</table>
	</div>
	
	<!-- crosstable panel -->
	<div id="crosstable_panel" class="panel"><br/>
	<table>
	<tr>
		<td><label for="crosstable_tag"><?php _e("League", 'leaguemanager'); ?></label></td>
		<td>
		<select id="crosstable_tag" name="crosstable_tag" style="width: 200px">
        	<option value="0"><?php _e("No League", 'leaguemanager'); ?></option>
		<?php
			$leaguelist = $wpdb->get_results("SELECT * FROM {$wpdb->leaguemanager} ORDER BY id DESC");
			if( is_array($leaguelist) ) {
			foreach( $leaguelist as $league )
				echo '<option value="'.$league->id.'" >'.$league->title.'</option>'."\n";
			}
		?>
        	</select>
		</td>
	</tr>
	<tr>
		<td nowrap="nowrap" valign="top"><label><?php _e( 'Display', 'leaguemanager' ) ?></label></td>
		<td>
			<input type="radio" name="crosstable_showtype" id="crosstable_showtype_embed" value="embed" checked="ckecked" /><label for="crosstable_showtype_embed"><?php _e( 'Embed', 'leaguemanager' ) ?></label><br />
			<input type="radio" name="crosstable_showtype" id="crosstable_showtype_popup" value="popup" /><label for="crosstable_showtype_popup"><?php _e( 'Popup', 'leaguemanager' ) ?></label>
		</td>
   	</tr>
	</table>
	</div>
		
	</div>
	
	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'leaguemanager'); ?>" onclick="tinyMCEPopup.close();" />
		</div>

		<div style="float: right">
			<input type="submit" id="insert" name="insert" value="<?php _e("Insert", 'leaguemanager'); ?>" onclick="insertLeagueManagerLink();" />
		</div>
	</div>

</form>
</body>
</html>