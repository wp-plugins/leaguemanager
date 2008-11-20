<?php

//$root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
$root = '/var/www/wordpress';

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
	<script language="javascript" type="text/javascript" src="<?php echo LEAGUEMANAGER_URL ?>/tinymce/tinymce.js"></script>
	<base target="_self" />
</head>
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';document.getElementById('table_tag').focus();" style="display: none">
<!-- <form onsubmit="insertLink();return false;" action="#"> -->
	<form name="LeagueManager" action="#">
	<div class="tabs">
		<ul>
			<li id="table_tab" class="current"><span><?php _e( "Leaguemanager", 'leaguemanager' ); ?></span></li>
		</ul>
	</div>
	
	<div class="panel_wrapper">
	<!-- Main panel -->
	<div id="leaguemanager_panel" class="panel current"><br />
	<table style="border: 0;" cellpadding="5">
	<tr>
		<td><label for="table_tag"><?php _e("Table", 'leaguemanager'); ?></label></td>
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
		<td><label for="match_tag"><?php _e("Matches", 'leaguemanager'); ?></label></td>
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
		<td><label for="crosstable_tag"><?php _e("Crosstable", 'leaguemanager'); ?></label></td>
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