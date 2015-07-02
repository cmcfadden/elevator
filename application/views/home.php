

<div class="jumbotron <?=$this->instance->getFeaturedAsset()?"col-lg-8 col-sm-12 col-md-7":null?>">
	<div class="container">
		<h2><?=$this->instance->getName()?></h2>
		<p><?=$this->instance->getIntroText()?></p>

	</div>
</div>

<?if($this->instance->getFeaturedAsset()):?>

<div class="col-lg-3 col-sm-6 col-md-4 featuredAssetColumn">

<div class="row">
	<div class="col-sm-12">
		<p><strong>Featured Item:</strong></p>
		<p><?=$this->instance->getFeaturedAssetText()?></p>
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
<div class="resultContainer searchContainer">
	<h5><a class="assetLink" href="<?=instance_url("asset/viewAsset/" . $assetData['objectId'])?>"><?=$assetData['title']?></a></h5>
	<div class="previewCrop ">
		<a href="<?=instance_url("asset/viewAsset/" . $assetData['objectId'])?>" class="assetLink"><img class="img-responsive previewImage" src="<?=$assetData['primaryHandlerThumbnail']?>" data-at2x="<?=$assetData['primaryHandlerThumbnail2x']?>"></a>
	<?if(isset($assetData['fileAssets'])):?>
		<span class="badge alert-success"><span class="glyphicon glyphicon-eye-open"></span><?=$assetData['fileAssets']?></span>
	<?endif?>
	</div>
	<div class="previewContent">


		<?foreach($assetData['entries'] as $entry):?>
		<div class="previewEntry"> <strong><?=$entry['label']?>:</strong><ul>
					<?foreach($entry['entries'] as $value):?>
						<li><?=$value?></li>
					<?endforeach?>
				</ul>
		</div>
		<?endforeach?>
	</div>
	</div>
</div>
</div>
</div>






<?endif?>


