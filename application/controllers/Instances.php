<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Instances extends Instance_Controller {

	public function index()
	{
		if(!$this->user_model->getIsSuperAdmin()) {
			instance_redirect("errorHandler/error/noPermission");
			return;
		}

		$data['instances'] = $this->doctrine->em->getRepository("Entity\Instance")->findAll();

		$this->template->title = 'Instance Index';

		$this->template->content->view('instances/index', $data);
		$this->template->publish();

	}

	public function save()
	{


		//TODO Permissions checking

		if(is_numeric($this->input->post("instanceId"))) {
			$instance = $this->doctrine->em->find('Entity\Instance', $this->input->post("instanceId"));
			$accessLevel = $this->user_model->getAccessLevel("instance", $instance);
			if($accessLevel<PERM_ADMIN) {
				instance_redirect("/errorHandler/error/noPermission");
				return;
			}
		}
		else {
			if(!$this->user_model->getIsSuperAdmin()) {
				instance_redirect("errorHandler/error/noPermission");
				return;
			}
			$instance = new Entity\Instance();
		}

		$instance->setName($this->input->post('name'));
		$instance->setDomain($this->input->post('domain'));
		$instance->setOwnerHomepage($this->input->post('ownerHomepage'));
		$instance->setAmazonS3Key($this->input->post('amazonS3Key'));
		$instance->setAmazonS3Secret($this->input->post('amazonS3Secret'));
		$instance->setS3StorageType($this->input->post('s3StorageType'));
		$instance->setBucketRegion($this->input->post('bucketRegion'));
		$instance->setDefaultBucket($this->input->post('defaultBucket'));
		$instance->setGoogleAnalyticsKey($this->input->post('googleAnalyticsKey'));
		$instance->setClarifaiId($this->input->post('clarifaiId'));
		$instance->setClarifaiSecret($this->input->post('clarifaiSecret'));
		$instance->setBoxKey($this->input->post('boxKey'));
		$instance->setIntroText($this->input->post('introText'));
		$instance->setUseCustomHeader($this->input->post('useCustomHeader')?1:0);
		$instance->setUseHeaderLogo($this->input->post('useHeaderLogo')?1:0);
		$instance->setUseCustomCSS($this->input->post('useCustomCSS')?1:0);
		$instance->setUseCentralAuth($this->input->post('useCentralAuth')?1:0);
		$instance->setFeaturedAsset($this->input->post('featuredAsset'));
		$instance->setFeaturedAssetText($this->input->post('featuredAssetText'));

		$this->doctrine->em->persist($instance);
		$this->doctrine->em->flush();

		instance_redirect('instances/edit/' . $instance->getId());

	}

	public function edit($id=null)
	{

		if($id) {
			$data['instance'] = $this->doctrine->em->find('Entity\Instance', $id);
			$accessLevel = $this->user_model->getAccessLevel("instance", $data['instance']);
			if($accessLevel<PERM_ADMIN) {
				instance_redirect("/errorHandler/error/noPermission");
				return;
			}
		}
		else {
			if(!$this->user_model->getIsSuperAdmin()) {
				instance_redirect("errorHandler/error/noPermission");
				return;
			}
			$data['instance'] = new Entity\Instance();
		}



		if (empty($data['instance']))
		{
			show_404();
		}
		$this->template->title = 'Edit Instance';
		$this->template->loadJavascript(["parsley","bootstrap-show-password"]);
		$this->template->content->view('instances/edit', $data);
		$this->template->publish();
	}


	public function delete($id)
	{
		if(!$this->user_model->getIsSuperAdmin()) {
			instance_redirect("errorHandler/error/noPermission");
			return;
		}

		$instance = $this->doctrine->em->find('Entity\Instance', $id);
		if ($instance === null) {
			show_404();
		}

		$this->doctrine->em->remove($instance);
		$this->doctrine->em->flush();

		instance_redirect('instances/index');
	}


	public function customPages() {
		$pages = $this->instance->getPages();
		$this->template->title = 'Custom Pages';
		$this->template->content->view('instances/pageList', ["pages"=>$pages]);
		$this->template->publish();
	}

	public function editPage($pageId=null) {


		$this->template->javascript->add("assets/tinymce/tinymce.min.js");

		if($pageId) {
			$page = $this->doctrine->em->find("Entity\InstancePage", $pageId);

		}
		else {
			$page = new Entity\InstancePage();
		}

		$this->template->title = 'Edit Page';
		$this->template->content->view('instances/editPage', ["page"=>$page]);
		$this->template->publish();
	}

	public function deletePage($pageId) {
		$page = $this->doctrine->em->find("Entity\InstancePage", $pageId);
		$this->doctrine->em->remove($page);
		$this->doctrine->em->flush();
		instance_redirect("instances/customPages");

	}

	public function savePage() {
		if($this->input->post("pageId")) {
			$page = $this->doctrine->em->find("Entity\InstancePage", $this->input->post("pageId"));

		}
		else {
			$page = new Entity\InstancePage();
		}


		$page->setTitle($this->input->post("title"));
		$page->setBody($this->input->post("body"));
		$page->setIncludeInHeader($this->input->post("includeInHeader")?1:0);
		$page->setInstance($this->instance);
		$this->doctrine->em->persist($page);
		$this->doctrine->em->flush();
		instance_redirect("instances/customPages");

	}


	public function buildIAMandCreateBucket() {
		$s3Secret = $this->input->post("s3Secret");
		$s3Key = $this->input->post("s3Key");
		$s3InstanceName = $this->input->post("name");
		$s3Region = $this->input->post("region");

		$s3InstanceName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($s3InstanceName)) . ".elevator";

		if(!$s3Region) {
			$s3Region = 'us-east-1';
		}

		if($s3Secret && $s3Key && $s3InstanceName) {
			$s3Client = null;
			try {
				$s3Client = new Aws\S3\S3Client(['region'=>'us-east-1', 'version'=>'2006-03-01', 'credentials'=>['secret'=>$s3Secret, 'key'=>$s3Key]]);
				$loopedOnce = false;
				while(1==1) {
					try {
						$s3Client->headBucket(['Bucket' =>$s3InstanceName]);
						break;
					}
					catch (\Aws\S3\Exception\S3Exception $e) {
						if($e->getStatusCode() == "403") {
							if($loopedOnce) {
								echo json_encode(["Error"=>"Can't find a bucket name"]);
								return;
							}
							$s3InstanceName = $s3InstanceName . "_01";
							$loopedOnce = true;
						}
						elseif($e->getStatusCode() == "404") {
							break;
						}

					}

				}
			}
			catch (Exception $e) {
				echo $e;
				return;
			}


			$s3Client->createBucket(["Bucket"=> $s3InstanceName, "region"=>$s3Region]);
			$s3Client->waitUntil('BucketExists', array('Bucket' => $s3InstanceName));

			$result = $s3Client->putBucketCors([
				'Bucket' => $s3InstanceName,
				'CORSConfiguration' => [
				'CORSRules' => [
				[
				'AllowedHeaders' => ['*'],
				'AllowedMethods' => ['PUT','POST','DELETE','GET','HEAD'],
				'AllowedOrigins' => ['*'],
				'MaxAgeSeconds' => 3000,
				]
				]
				]
				]);

			$result = $s3Client->putBucketPolicy([
				'Bucket'=>$s3InstanceName,
				'Policy'=>'{
					"Version": "2008-10-17",
					"Id": "Policy1386963710894",
					"Statement": [
					{
						"Sid": "Stmt1386963653013",
						"Effect": "Allow",
						"Principal": {
							"AWS": "*"
						},
						"Action": "s3:GetObject",
						"Resource": ["arn:aws:s3:::' . $s3InstanceName . '/derivative/*streaming/*", "arn:aws:s3:::' . $s3InstanceName . '/thumbnail/*",  "arn:aws:s3:::' . $s3InstanceName . '/vtt/*"]
					},
					{
						"Sid": "Stmt1387382290061",
						"Effect": "Deny",
						"Principal": {
							"AWS": "*"
						},
						"Action": [
						"s3:DeleteObject",
						"s3:DeleteObjectVersion"
						],
						"Resource": "arn:aws:s3:::' . $s3InstanceName . '/original/*",
						"Condition": {
							"Null": {
								"aws:MultiFactorAuthAge": true
							}
						}
					},
					{
						"Sid": "Stmt1386963706249",
						"Effect": "Deny",
						"Principal": {
							"AWS": "*"
						},
						"Action": "s3:GetObject",
						"Resource": "arn:aws:s3:::' . $s3InstanceName . '/derivative/*streaming/stream1.m3u8"
					}
					]
				}'
			]);

			$useLifecycle = false;
			$useStandardIA = false;
			$transition = array();
			if($this->input->post("useLifecycle")) {
				$useLifecycle = true;
				$transition[] = ['Days' => 60,
	                    			'StorageClass' => 'GLACIER'];
			}
			if($this->input->post("useStandardIA")) {
				$useStandardIA = true;
				$transition[] = ['Days' => 30,
	                    			'StorageClass' => 'STANDARD_IA'];
			}

			if($useStandardIA || $useLifecycle) {
				$s3Client->putBucketLifecycleConfiguration([
					'Bucket'=>$s3InstanceName,
					'LifecycleConfiguration' => [
        				'Rules' =>
        				[ // REQUIRED
	            			[
	                			'Expiration' => [
	                    			'Days' => 7,
	                			],
	                			'Prefix' => 'drawer/', // REQUIRED
	                			'Status' => 'Enabled',
	            			],
	            			[
	                			'Prefix' => 'original/', // REQUIRED
	                			'Status' => 'Enabled',
	                			'Transitions' => $transition,
	            			],
        				],
    				],
				]);
			}


			$newUser = $s3InstanceName . "_bucket_user";
			$client = new Aws\Iam\IamClient(['region'=>'us-east-1', 'version'=>'2010-05-08', 'credentials'=>['secret'=>$s3Secret, 'key'=>$s3Key]]);

			try {
				$result = $client->getUser([
					'UserName' => $newUser
					]);
				if($result) {
					echo json_encode(["Error"=>"User Exists"]);
					return;
				}
			}
			catch (Exception $e) {

			}
			try {
				$result = $client->createUser([
					'UserName' => $newUser
					]);

				$result = $client->putUserPolicy([
					'PolicyDocument' => '{
						"Version": "2012-10-17",
						"Statement": [
						{
							"Sid": "Stmt1439914819000",
							"Effect": "Allow",
							"Action": [
							"s3:AbortMultipartUpload",
							"s3:DeleteBucketPolicy",
							"s3:DeleteBucketWebsite",
							"s3:DeleteObject",
							"s3:DeleteObjectVersion",
							"s3:GetBucketAcl",
							"s3:GetBucketCORS",
							"s3:GetBucketLocation",
							"s3:GetBucketLogging",
							"s3:GetBucketNotification",
							"s3:GetBucketPolicy",
							"s3:GetBucketRequestPayment",
							"s3:GetBucketTagging",
							"s3:GetBucketVersioning",
							"s3:GetBucketWebsite",
							"s3:GetLifecycleConfiguration",
							"s3:GetObject",
							"s3:GetObjectAcl",
							"s3:GetObjectTorrent",
							"s3:GetObjectVersion",
							"s3:GetObjectVersionAcl",
							"s3:GetObjectVersionTorrent",
							"s3:ListAllMyBuckets",
							"s3:ListBucket",
							"s3:ListBucketMultipartUploads",
							"s3:ListBucketVersions",
							"s3:ListMultipartUploadParts",
							"s3:PutObject",
							"s3:PutObjectAcl",
							"s3:PutObjectVersionAcl",
							"s3:RestoreObject"
							],
							"Resource": [
							"arn:aws:s3:::' . $s3InstanceName . '*"
							]
						}
						]
					}',
					'PolicyName' => 'bucket_policy',
					'UserName' => $newUser
					]);

			$userSecrets = $client->createAccessKey(array(
				'UserName' => $newUser,
				));

				$accessKey = $userSecrets["AccessKey"]["AccessKeyId"];
				$secretKey = $userSecrets["AccessKey"]["SecretAccessKey"];

				$bucketName = $s3InstanceName;

				echo json_encode(["accessKey"=>$accessKey, "secretKey"=>$secretKey, "bucketName"=>$bucketName, "bucketRegion"=>$s3Region]);

			}
			catch (Exception $e) {
				echo $e;
			}
		}
		else {
			$this->template->title = 'Create Bucket';
			$this->template->content->view('instances/buildBucket');
			$this->template->publish();
		}
	}
}

