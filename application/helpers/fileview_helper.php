<?php


function renderFileMenu($menuArray) {
	$outputText = '';
	$outputText .= '<div class="row infoRow"><div class="col-md-12">';

	if($menuArray['fileInfo']) {
		$outputText = '<span class="glyphicon glyphicon-info-sign infoPopover" data-placement="bottom" data-toggle="popover" title="File Info" data-html=true data-content=\'<ul class="list-group">';
		foreach($menuArray['fileInfo'] as $entryKey=>$fileInfoEntry) {
			if($entryKey == "Location") {
				$outputText = '<li class="list-group-item assetDetails"><strong>Location: </strong><A href="#mapModal"  data-toggle="modal" data-latitude="' . $fileInfoEntry['latitude'] . '" data-longitude="' . $fileInfoEntry['longitude'] . '">View Location</a></li>';
			}
			else {
				$outputText .= '<li class="list-group-item assetDetails"><strong>' . $entryKey . ':</strong> ' . htmlentities($fileInfoEntry, ENT_QUOTES) . ' </li>';	
			}
			
		}
		$outputText .= '</ul>\'></span>';
	}
	
	if($menuArray['download']) {

		$outputText .= '<span class="glyphicon glyphicon-download infoPopover" data-placement="bottom" data-toggle="popover" title="Download" data-html="true" data-content=\'<ul>';
		foreach($menuArray['download'] as $downloadKey=>$downloadEntry) {
			$outputText .= '<li class="list-group-item assetDetails"><a href="' . $downloadEntry . '">' . $downloadKEy . '</a></li>';
		}
		$outputText .= '</ul>\'></span>';

	}
	if($menuArray['embed']) {
		$outputText .= '  <span class="glyphicon glyphicon-share infoPopover" data-placement="bottom" data-toggle="popover" title="Share" data-html="true" data-content=\'<ul>';
		$outputText .= '<li class="list-group-item assetDetails"><strong>Embed: </strong><input class="form-control embedControl" value="' . htmlspecialchars($menuArray['embed'], ENT_QUOTES) . '"></li>';
		$outputText .= '</ul>\'></span>';
	}

	if($menuArray['excerpt']) {
		$outputText .= '<span data-toggle="collapse" data-target="#excerptGroup" class="glyphicon glyphicon-time excerptTooltip" data-toggle="tooltip" title="Create Excerpt"></span>';
	}
	
	$outputText .= "</div></div>";

	return $outputText;

}


?>
