<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Page extends Instance_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function view($pageId = null)
	{
		if(!$pageId) {
			instance_redirect("/");
		}
		$page = $this->doctrine->em->find("Entity\InstancePage", $pageId);
		if(!$page) {
			show_404();
		}
		$this->template->title = $page->getTitle();
		$this->template->content->view("staticPage", ["content"=>$page->getBody()]);
		$this->template->publish();
	}

}

/* End of file page.php */
/* Location: ./application/controllers/page.php */