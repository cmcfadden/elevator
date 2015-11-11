<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AssetManager extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->template->javascript->add("/assets/js/handlebars-v1.1.2.js");
		$this->template->javascript->add("assets/js/formSubmission.js");
		$this->template->javascript->add("assets/js/serializeForm.js");
		$this->template->javascript->add("assets/js/interWindow.js");
		$this->template->javascript->add("assets/js/sugar.js");
		$this->template->javascript->add("//maps.google.com/maps/api/js?sensor=false");
		$this->template->javascript->add("assets/js/jquery.gomap-1.3.2.js");
		$this->template->javascript->add("assets/js/markerclusterer.js");
		$this->template->javascript->add("assets/js/mapWidget.js");
		$this->template->javascript->add("assets/js/dateWidget.js");
		$this->template->javascript->add("assets/js/multiselectWidget.js");
		$this->template->javascript->add("assets/js/mule2.js");
		$this->template->javascript->add("assets/js/uploadWidget.js");
		$this->template->javascript->add("assets/js/parsley.js");
		$this->template->javascript->add("assets/js/bootstrap-datepicker.js");
		$this->template->stylesheet->add("assets/css/datepicker.css");
		$this->template->javascript->add("assets/js/bootstrap-tagsinput.js");
		$this->template->javascript->add("assets/js/typeahead.jquery.min.js");
		$this->template->stylesheet->add("assets/css/bootstrap-tagsinput.css");
		$this->template->javascript->add("assets/tinymce/tinymce.min.js");

		$this->load->model("asset_model");
	}

	public function addAssetModal() {
		if(isset($this->instance) && $this->user_model->userLoaded) {
			if($this->user_model->getIsSuperAdmin() || $this->user_model->getAccessLevel("instance",$this->instance)>=PERM_ADDASSETS || $this->user_model->getMaxCollectionPermission() >= PERM_ADDASSETS) {
				$this->load->view("addAsset_modal");
			}
		}
	}

	/**
	 * AJAX call to add a new widget to an asset entry form.
	 * gets the current count so we can build the fields with the right ID
	 * Dear god, why isn't this all done client side?  Please rebuild this in React and a proper design.
	 */
	public function getWidget($widgetId, $widgetCount) {
		$widgetObject = $this->doctrine->em->find('Entity\Widget', $widgetId);
		if(!$widgetObject) {
			$this->output->set_status_header(500, 'error loading widget');
			$this->logging->logError("getWidget", "could not load widget" . $widgetId . "with count " . $widgetCount);
			return;
		}
		$this->load->library("widget_router");
		$widget = $this->widget_router->getWidget($widgetObject);
		$widget->offsetCount = $widgetCount;
		echo $widget->getForm();
	}


	/**
	 * This is the main page for creating a new asset
	 */
	public function addAsset($templateId=null, $collectionId = null, $inlineForm = false)
	{

		$accessLevel = max($this->user_model->getAccessLevel("instance",$this->instance), $this->user_model->getMaxCollectionPermission());

		if($accessLevel < PERM_ADDASSETS) {
			instance_redirect("errorHandler/error/noPermission");
		}


		if($this->input->post("templateId")) {
			$templateId = $this->input->post("templateId");
		}

		if(!$templateId) {
			$this->logging->logError("no template", $_SERVER);
			instance_redirect("/errorHandler/error/unknownTemplate");
		}
		$this->template->javascript->add("assets/js/widgetHelpers.js");


		$template = $this->asset_template->getTemplate($templateId);

		$this->template->title = "Add Asset | ". $template->getName() . "";

		// if this is a nested form, we might be trying to force a collectionId
		if(is_numeric($collectionId)) {
			$this->template->collectionId = $collectionId;
		}

		if($inlineForm) {
			$template->displayInline = true;
			$this->template->set_template("chromelessTemplate");
		}

		$templateContents = $template->templateForm();

		$this->template->content->set($templateContents);

		$this->template->publish();

	}


	function editAsset($objectId, $inlineForm=false) {

		$accessLevel = $this->user_model->getAccessLevel("instance",$this->instance);

		$this->asset_model->loadAssetById($objectId);

		if($accessLevel < PERM_ADDASSETS) {
			if($this->user_model->getAccessLevel("collection",$this->collection_model->getCollection($this->asset_model->getGlobalValue("collectionId"))) < PERM_ADDASSETS) {
				instance_redirect("errorHandler/error/noPermission");
			}
		}

		$this->load->model("asset_model");
		$this->asset_model->loadAssetById($objectId);
		$this->template->javascript->add("assets/js/widgetHelpers.js");
		$assetTemplate = $this->asset_template->getTemplate($this->asset_model->templateId);

		$this->template->title = "Edit Asset | ". $assetTemplate->getName() . "";

		if($inlineForm) {
			$assetTemplate->displayInline = true;
			$this->template->set_template("chromelessTemplate");
		}

		$this->template->content->set($assetTemplate->templateForm($this->asset_model));

		$this->template->publish();

	}

	// Used for restoring an asset state from our history table.  We copy it out of the history and save it with the
	// existing object id
	function restoreAsset($objectId) {
		$accessLevel = max($this->user_model->getAccessLevel("instance",$this->instance), $this->user_model->getMaxCollectionPermission());

		if($accessLevel < PERM_ADDASSETS) {
			instance_redirect("errorHandler/error/noPermission");
		}

		$asset = new Asset_model();
		$asset->loadAssetById($objectId);

		$entries = $asset->assetObject->getRevisions();

		$assetArray = array();
		$this->load->model("asset_model");
		foreach($entries as $entry) {
			$tempAsset = new Asset_model();
			$tempAsset->loadAssetFromRecord($entry);
			$assetArray[] = $tempAsset;
		}

		$this->template->content->view('assetManager/restore', ['assetArray'=>$assetArray]);
		$this->template->publish();

	}

	function restore($objectId) {

		$accessLevel = max($this->user_model->getAccessLevel("instance",$this->instance), $this->user_model->getMaxCollectionPermission());

		if($accessLevel < PERM_ADDASSETS) {
			instance_redirect("errorHandler/error/noPermission");
		}

		$restoreObject = $this->doctrine->em->find("Entity\Asset", $objectId);
		$currentParent = $restoreObject->getRevisionSource();

		$restoreObject->setAssetId($currentParent->getAssetId());
		$restoreObject->setRevisionSource(null);
		$restoreObject->setDeleted(false);
		if($currentParent) {
			$currentParent->setAssetId(null);
			$currentParent->setRevisionSource($restoreObject);
			$currentParent->setDeleted(false);
		}

		$qb = $this->doctrine->em->createQueryBuilder();
		$q = $qb->update('Entity\Asset', 'a')
        ->set('a.revisionSource', $restoreObject->getId())
        ->where('a.revisionSource = ?1')
        ->setParameter(1, $currentParent->getId())
        ->getQuery();
		$p = $q->execute();


		$this->doctrine->em->flush();

		$this->asset_model->loadAssetById($restoreObject->getAssetId());
		$files = $this->asset_model->getAllWithinAsset("Upload");
		foreach($files as $file) {
			foreach($file->fieldContentsArray as $entry) {
				if($entry->fileHandler->deleted == true) {
					$entry->fileHandler->regenerate = true;
					$entry->fileHandler->undeleteFile();
				}

			}
		}

		$this->asset_model->save(true,false);


		instance_redirect("asset/viewAsset/" .$restoreObject->getAssetId());
	}

	// save an asset
	public function submission() {

		$accessLevel = max($this->user_model->getAccessLevel("instance",$this->instance), $this->user_model->getMaxCollectionPermission());

		if($accessLevel < PERM_ADDASSETS) {
			instance_redirect("errorHandler/error/noPermission");
		}

		$data = $this->input->post("formData");
		$objectArray = json_decode($data, true);
		unset($data);


		$asset = new Asset_model;
		$firstSave = true;

		if(isset($objectArray["objectId"]) && strlen(($objectArray["objectId"])) == 24) {
			$asset->loadAssetById($objectArray["objectId"]);
			$firstSave = FALSE;
		}

		$asset->templateId = $objectArray["templateId"];
		$asset->createObjectFromJSON($objectArray);

		unset($objectArray);

		if($firstSave) {
			$objectId = $asset->save(false);
		}
		else {
			$objectId = $asset->save(true);
		}


		echo json_encode(["objectId"=>(string)$objectId, "success"=>true]);


		if($firstSave) {
			/**
			 * BEWARE
			 * SOME REALLY HACKY STUFF HERE!
			 * Because we need files to know about their parent, we need to reload the asset and resave so that all the
			 * children get the parent id (because we don't know it at the time of the first save)
			 * Wish we had futures.
			 */

			$asset = new Asset_model();
			if($asset->loadAssetById($objectId)) {
				$asset->save(true,false);
			}


			/**
			 * END HACKY STUFF
			 */

		}




	}

	/**
	 * Because the uploader goes directly to S3, we need to pre-create a place for the asset to live on our end.  That way
	 * we can give it an appropriate objectId on S3.
	 * This can result in stray file containers sitting in the DB - eventually we should probably have some code to purge these or reconnect them
	 */
	public function getFileContainer() {
		$collectionId = $this->input->post("collectionId");
		$filename = $this->input->post("filename");
		// TODO: check that we can actually write to this bucket
		// right now, it'll fail silently (though if you look at the browser debugger it's obvious)
		$collection = $this->collection_model->getCollection($collectionId);
		$fileObjectId = $this->input->post("fileObjectId");
		$fileContainer = new fileContainerS3();
		$fileContainer->originalFilename = $filename;
		$fileContainer->path = "original";

		if(!$fileObjectId) {
			$fileContainer->ready = false;

			// this handler type may get overwritten later - for example, once we identify the contents of a zip
			$fileHandler = $this->filehandler_router->getHandlerForType($fileContainer->getType());

			$fileHandler->sourceFile = $fileContainer;
			$fileHandler->collectionId = $collectionId;
			$fileId = $fileHandler->save();

		}
		else {
			$fileId = $fileObjectId;
		}

		echo json_encode(["fileObjectId"=>$fileId, "collectionId"=>$collectionId, "bucket"=>$collection->getBucket(), "bucketKey"=>$collection->getS3Key(), "path"=>$fileContainer->path]);

	}


	public function cancelSourceFile($fileObjectId) {
		$fileHandler = $this->filehandler_router->getHandlerForObject($fileObjectId);

		if($this->user_model->getAccessLevel("instance",$this->instance)<PERM_ADDASSETS) {
			$this->errorhandler_helper->callError("noPermission");
		}

		$fileHandler->loadByObjectId($fileObjectId);
		$fileHandler->deleteFile();
	}

	public function deleteAsset($objectId) {


		$accessLevel = $this->user_model->getAccessLevel("instance",$this->instance);

		$this->load->model("search_model");
		$this->asset_model->loadAssetById($objectId);

		if($accessLevel < PERM_ADDASSETS) {
			if($this->user_model->getAccessLevel("collection",$this->collection_model->getCollection($this->asset_model->getGlobalValue("collectionId"))) < PERM_ADDASSETS) {
				instance_redirect("errorHandler/error/noPermission");
			}

		}

		$this->accessLevel = $this->user_model->getAccessLevel("asset", $this->asset_model);
		if($this->accessLevel < PERM_ADDASSETS) {
			instance_redirect("errorHandler/error/noPermission");
		}
		$this->search_model->remove($this->asset_model);

		$this->asset_model->delete();

		$qb = $this->doctrine->em->createQueryBuilder();
		$qb->delete('Entity\DrawerItem', 's');
		$qb->andWhere($qb->expr()->eq('s.asset', ':assetId'));
		$qb->setParameter(':assetId', $objectId);
		$qb->getQuery()->execute();

		instance_redirect("/");
	}

	// List all of the assets touched by the user.  Offset specifies page number essentially.
	public function userAssets($offset=0) {

		$qb = $this->doctrine->em->createQueryBuilder();
		$qb->from("Entity\Asset", 'a')
			->select("a")
			->where("a.createdBy = :userId")
			->setParameter(":userId", (int)$this->user_model->userId)
			->andWhere("a.assetId IS NOT NULL")
			->orderBy("a.modifiedAt", "DESC")
			->setMaxResults(50)
			->setFirstResult($offset);

		$assets = $qb->getQuery()->execute();

		$hiddenAssetArray = array();
		foreach($assets as $entry) {
			$this->asset_model->loadAssetFromRecord($entry);
			$hiddenAssetArray[] = ["objectId"=>$this->asset_model->getObjectId(), "title"=>$this->asset_model->getAssetTitle(true), "readyForDisplay"=>$this->asset_model->getGlobalValue("readyForDisplay"), "modifiedDate"=>$this->asset_model->getGlobalValue("modified")];
		}

		if($offset>0) {
			$this->load->view('user/hiddenAssets', ["isOffset"=>true, "hiddenAssets"=>$hiddenAssetArray]);
		}
		else {
			$this->template->content->view('user/hiddenAssets', ["isOffset"=>false, "hiddenAssets"=>$hiddenAssetArray]);
			$this->template->javascript->add("assets/js/scrollAssets.js");
			$this->template->publish();

		}

	}


	/**
	 * Fired when a file has finished uploading
	 */
	public function completeSourceFile($fileObjectId) {

		$fileHandler = $this->filehandler_router->getHandlerForObject($fileObjectId);
		$fileHandler->loadByObjectId($fileObjectId);
		$fileHandler->sourceFile->ready = true;
		$fileHandler->regenerate = true;
		$fileHandler->save();
		echo "success";
	}

	/**
	 * compareTemplates is called via AJAX when a user wants to switch from one metadata template
	 * to another.  It allows us to warn them if data will be lost in the process.
	 */
	public function compareTemplates($sourceTemplate, $destinationTemplate) {

		$sourceTemplate = $this->asset_template->getTemplate($sourceTemplate);
		$destinationTemplate = $this->asset_template->getTemplate($destinationTemplate);

		$missingFields = array();

		foreach($sourceTemplate->widgetArray as $widgetTitle=>$sourceWidget) {
			if(!array_key_exists($widgetTitle, $destinationTemplate->widgetArray)) {
				$missingFields[$widgetTitle] = "missing";
				continue;
			}

			if(get_class($sourceWidget) != get_class($destinationTemplate->widgetArray[$widgetTitle])) {
				$missingFields[$widgetTitle] = "type mismatch";
			}

		}


		$outputArray = array();
		foreach($missingFields as $key=>$failure) {
			$outputArray[$key] = ["type"=>$failure, "label"=>$sourceTemplate->widgetArray[$key]->getLabel()];

		}

		echo json_encode($outputArray);

	}

	/**
	 * If changing collections will result in the file being migrated from one bucket to another, we
	 * need to alert the user.  This will lock the record from updating until the migration is complete.
	 * That migration is actually handled by the beltdrive backend.
	 */
	public function compareCollections($sourceCollection, $destinationCollection) {

		$sourceCollection = $this->collection_model->getCollection($sourceCollection);
		$destinationCollection = $this->collection_model->getCollection($destinationCollection);

		if($sourceCollection->getBucket() == $destinationCollection->getBucket()) {
			echo json_encode(["migration"=>false]);
		}
		else {
			echo json_encode(["migration"=>true]);
		}


	}


}

/* End of file  */
/* Location: ./application/controllers/ */