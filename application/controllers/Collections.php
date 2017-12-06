<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Collections extends Instance_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function browseCollection($collectionId=null) {

		if(!$collectionId) {
			instance_redirect("/search");
		}


		$knownCollections = array();
		foreach($this->instance->getCollections() as $collection) {
			$knownCollections[] = $collection->getId();
		}

		if(!in_array($collectionId, $knownCollections)) {
			instance_redirect("/search");
		}

		$searchArray["searchText"] = "";
		$searchArray["collection"] = [$collectionId];
		$searchArray["fuzzySearch"] = false;

		if(count($searchArray) == 0) {
			return;
		}
		$collection = $this->doctrine->em->find('Entity\Collection', $collectionId);
		$this->user_model->addRecentCollection($collection);

		$searchArchive = new Entity\SearchEntry;
		$searchArchive->setUser($this->user_model->user);
		$searchArchive->setInstance($this->instance);
		$searchArchive->setSearchText($searchArray['searchText']);
		$searchArchive->setSearchData($searchArray);
		$searchArchive->setCreatedAt(new DateTime());
		$searchArchive->setUserInitiated(false);

		$this->doctrine->em->persist($searchArchive);
		$this->doctrine->em->flush();

		$this->searchId = $searchArchive->getId();
		instance_redirect("search/s/".$this->searchId);

	}

	public function collectionHeader($collectionId) {
		$accessLevel = $this->user_model->getAccessLevel("instance",$this->instance);

		if($accessLevel < PERM_SEARCH) {
			if($this->user_model) {
				$allowedCollections = $this->user_model->getAllowedCollections(PERM_SEARCH);
				if(!in_array($collectionId, $allowedCollections)) {
					$this->errorhandler_helper->callError("noPermission");
				}
			}
			else {
				$this->errorhandler_helper->callError("noPermission");
			}
		}

		$collection= $this->collection_model->getCollection($collectionId);
		
		echo $this->load->view("collectionHeader", ["collectionDescription"=>$collection->getCollectionDescription(), "collectionTitle"=>$collection->getTitle()], true);

	}

}

/* End of file  */
/* Location: ./application/controllers/ */
