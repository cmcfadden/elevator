

<script>
var startTimeValue = <?=$startTime?>;
var endTimeValue = <?=$endTime?>;
var excerptId = <?=$excerptId?>;
var objectId = "<?=$asset->getObjectId()?>";

</script>

<?if($isEmbedded):?>
<?=$embed?>
<?else:?>
<div class="row">
	<div class="col-md-8 col-md-offset-2">
		<h2><?=$label?><a href="<?=instance_url("asset/viewAsset/".$asset->getObjectId())?>" class="btn btn-primary pull-right">View Asset</a></h2>
		<?=$embed?>
	</div>
</div>
<?endif?>

<script>

$(document).ready(function(){
	// console.log(startTimeValue);
	$(".videoEmbedFrame").on("load", function() {
		$(".videoEmbedFrame")[0].contentWindow.setPlayBounds(startTimeValue, endTimeValue);
	});
	
	$(".excerptTooltip").hide();
	$(".glyphicon-info-sign").hide();
	//bootstrap pushes these into the data over the span for a popover,so we can't update it until it's revealed.
	$(".infoPopover").on("shown.bs.popover", function() {
		$(".frameEmbed").val("<?=addslashes($frameLink)?>");
		$(".linkEmbed").val("http:<?=addslashes($embedLink)?>");
	});



});

</script>