function init() {
	tinyMCEPopup.resizeToInnerSize();
}

/*
function getCheckedValue(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}
*/

function insertLeagueManagerLink() {
	
	var tagtext;
	
	var table = document.getElementById('table_panel');
	var matches = document.getElementById('match_panel');
	var crosstable = document.getElementById('crosstable_panel');
	
	var table_league_id = document.getElementById('table_tag').value;
	var match_league_id = document.getElementById('match_tag').value;
	var crosstable_league_id = document.getElementById('crosstable_tag').value;
		
	if ( table_league_id == 0 && match_league_id == 0 && crosstable_league_id == 0 )
		tinyMCEPopup.close();
	
	tagtext = "";
	if (table_league_id != 0)
		tagtext = "[leaguestandings=" + table_league_id + "]<br/><br/>";
	if (match_league_id != 0)
		tagtext = tagtext + "[leaguematches=" + match_league_id + "]<br/><br/>";
	if (crosstable_league_id != 0)
		tagtext = tagtext + "[leaguecrosstable=" + crosstable_league_id + "]<br/><br/>";

	if(window.tinyMCE) {
		window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
		//Peforms a clean up of the current editor HTML. 
		//tinyMCEPopup.editor.execCommand('mceCleanup');
		//Repaints the editor. Sometimes the browser has graphic glitches. 
		tinyMCEPopup.editor.execCommand('mceRepaint');
		tinyMCEPopup.close();
	}
	return;
}