function init() {
	tinyMCEPopup.resizeToInnerSize();
}


function LeagueManagergetCheckedValue(radioObj) {
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

function insertLeagueManagerLink() {

	var tagtext;

	var table = document.getElementById('table_panel');
	var matches = document.getElementById('match_panel');
	var crosstable = document.getElementById('crosstable_panel');

	// who is active?
	if (table.className.indexOf('current') != -1) {
		var leagueId = document.getElementById('table_tag').value;
		
		if (leagueId != 0)
			tagtext = "[leaguestandings=" + leagueId + "]";
		else
			tinyMCEPopup.close();
	}

	if (matches.className.indexOf('current') != -1) {
		var leagueId = document.getElementById('match_tag').value;
		var match_display = document.getElementById('match_display').value;
		
		if (leagueId != 0)
			tagtext = "[leaguematches=" + leagueId + "," + match_display + "]";
		else
			tinyMCEPopup.close();
	}

	if (crosstable.className.indexOf('current') != -1) {
		var leagueId = document.getElementById('crosstable_tag').value;
		var showtype = LeagueManagergetCheckedValue(document.getElementsByName('crosstable_showtype'));
		
		if (leagueId != 0)
			tagtext = "[leaguecrosstable=" + leagueId + "," + showtype + "]";
		else
			tinyMCEPopup.close();
	}

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