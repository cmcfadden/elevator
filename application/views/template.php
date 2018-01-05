<!DOCTYPE html>
<html>
<head>
<script>
var basePath = "<?=$this->template->relativePath?>";
</script>


<?if(isset($this->instance) && $this->instance->getUseCentralAuth()):?>
<script>

  if(window.name == 'loginRedirectWindow') {
    window.opener.childWindowWillClose();
    window.close();
  }

function inIframe () {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}

function popitup() {
  newwindow=window.open("https://" + window.location.hostname + "/autoclose.html",'name','height=200,width=150');

  setTimeout(function() { location.reload();}, 1200);
  return false;
}

if(window.location.hash  == "#secondFrame" && inIframe()) {
    window.addEventListener("message", function(event) {
        if(event.data == "pageLoaded") {
            location.reload();
        }
    }, false);
}

</script>

<?=$this->user_model->getAuthHelper()->templateView();?>

<?endif?>
  <title><?= $this->template->title->default(isset($this->instance)?$this->instance->getName():"Elevator"); ?></title>
  <meta charset="utf-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?=$this->template->meta; ?>
  <?=$this->template->stylesheet; ?>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<?if(isset($this->instance) && $this->instance->getUseCustomCSS()):?>
<link rel="stylesheet" href="/assets/instanceAssets/<?=$this->instance->getId()?>.css">
<?endif?>

<script src="/assets/minifiedjs/jquery.min.js"></script>
</head>

<?if(isset($this->instance) && $this->instance->getGoogleAnalyticsKey()):?>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '<?=$this->instance->getGoogleAnalyticsKey()?>', 'lobstermonkey.com');
  ga('send', 'pageview');

</script>

<?endif?>

<body>

<?if(isset($this->instance) && $this->instance->getUseCustomHeader()):?>
<?=file_get_contents("assets/instanceAssets/" . $this->instance->getId() . ".html");?>
<?endif?>


 <nav class="navbar navbar-default" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->



      <?if(isset($this->instance)):?>
        <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="<?=instance_url("/")?>">
        <?if(isset($this->instance) && $this->instance->getUseHeaderLogo()):?>
        <img src="/assets/instanceAssets/<?=$this->instance->getId()?>.png" alt="<?=$this->instance->getName()?>" class="headerLogoImage"/>
        <?elseif(isset($this->instance)):?>
        <div class="headerLogoText"><?=$this->instance->getName()?></div>
        <?else:?>
        Elevator
        <?endif?>
      </a>
    </div>
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
<?if(!$this->useUnauthenticatedTemplate):?>
  <div class="col-sm-3">
    <form class="navbar-form navbar-input-group navbar-left searchForm" role="search">
      <input type="hidden" name="collection[]" id="collection" value=0>
      <input type="hidden" name="specificSearchField[]" id="specificSearchField">
      <input type="hidden" name="specificSearchText[]" id="specificSearchText">
      <input type="hidden" name="specificSearchFuzzy[]" id="specificSearchFuzzy">
      <input type="hidden" name="fuzzySearch" value=0>
      <div class="input-group">
      
        <input type="text" class="form-control searchText"  aria-label="Search" autocomplete="off"  id="searchText" name="searchText" placeholder="Search">
        <div class="input-group-btn search-panel">
          <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            <div id="search_concept" class="hidden-md hidden-sm">All Collections</span> <span class="caret"></div>
          </button>
        <ul class="dropdown-menu dropdown-menu-right" role="menu">
          <li><a href="#contains" class="collectionFilterSelect" data-collection-id="0">All Collections</a></li>
          <li class="divider"></li>
          <?

          function drawNestedCollections($collectionList, $indentMarker=null) {
            foreach($collectionList as $collection) {
              echo '<li><a href="#' .$collection->getId() . '" class="collectionFilterSelect" data-collection-id="' . $collection->getId() . '">' . $indentMarker . " " . $collection->getTitle(). '</a></li>';
              if($collection->hasChildren()) {
                drawNestedCollections($collection->getChildren(), $indentMarker . "-");
              }  
            }

          }
          drawNestedCollections($this->collection_model->getUserCollections());

          ?>
        </ul>
      </div>
        <button type="submit" class="btn btn-default searchButton"><span class="glyphicon glyphicon-search"></span></button>
        <div class="btn-group  hidden-md hidden-sm">
        <button type="button" class="btn btn-default dropdown-toggle advanced-search-toggle" data-toggle="dropdown">
          <span class="caret"></span>
          <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu " role="menu">
          <li><a href="<?=instance_url("/search/advancedSearchModal")?>" data-toggle="modal" data-target="#advancedSearchModal">Advanced Search</a></li>
          <li class="divider"></li>
          <li class="disabled"><a href="#">Recent Searches:</a></li>
          <?if($this->user_model->userLoaded):?>
            <?foreach($this->user_model->getRecentSearches() as $search):?>
              <li><a href="<?=instance_url("search/s/". $search->getId())?>"><?=$search->getSearchText()?></a></li>
              <?endforeach?>
              <?endif?>
              <?if($this->user_model->userLoaded && $this->user_model->getIsSuperAdmin() || $this->user_model->getAccessLevel("instance",$this->instance)>=PERM_ADDASSETS || $this->user_model->getMaxCollectionPermission() >= PERM_ADDASSETS):?>
                <li class="divider"></li>
                <li><a href="<?=instance_url("search/searchList")?>">Custom Searches</a></li>
              <?endif?>
          </ul>
        </div>
          </div>

          </div>
        <input type="hidden" name="sort" class="hiddenSort" value="0" />
      </form>
    </div>
<?endif?>
      <ul class="nav navbar-nav">
        <?if(isset($this->instance)):?>
        <li>
        <p class="navbar-btn">
          <button class="hide btn addToPlugin btn-default"></button>
          </p>
          </li>
        <?foreach($this->instance->getPages()->filter(function($entry) { return ($entry->getIncludeInHeader() && $entry->getParent()==null);}) as $page):?>
          <?if(count($page->getChildren())>0):?>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?=$page->getTitle()?> <b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li><a href="<?=instance_url("page/view/". $page->getId())?>"><?=$page->getTitle()?></a></li>
              <?foreach($page->getChildren() as $child):?>
              <li><a href="<?=instance_url("page/view/". $child->getId())?>"><?=$child->getTitle()?></a></li>
              <?endforeach?>
            </ul>
          </li>
          <?else:?>
          <li><a href="<?=instance_url("page/view/". $page->getId())?>"><?=$page->getTitle()?></a></li>
          <?endif?>
        <?endforeach?>

        <?endif?>


        <?if(!$this->useUnauthenticatedTemplate):?>
          <li class="dropdown collectionToggle">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Collections <b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li class="disabled"><a href="#">Recent Collections:</a></li>
              <?if($this->user_model->userLoaded):?>
              <?foreach($this->user_model->getRecentCollections() as $collection):?>
              <li><a href="<?=instance_url("collections/browseCollection/".$collection->getCollection()->getId())?>"><?=$collection->getCollection()->getTitle()?></a></li>
              <?endforeach;?>
              <?endif?>
              <li class="divider"></li>
              <li><a href="<?=instance_url("search/listCollections")?>">All Collections</a></li>
            </ul>
          </li>
          <?if($this->user_model->userLoaded):?>

            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Drawers <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li class="disabled"><a href="#">Recent Drawers:</a></li>
                <?foreach($this->user_model->getRecentDrawers() as $drawer):?>
                <li><a href="<?=instance_url("drawers/viewDrawer/".$drawer->getDrawer()->getId())?>"><?=$drawer->getDrawer()->getTitle()?></a></li>
                <?endforeach;?>
                <li class="divider"></li>
                <li><a href="<?=instance_url("drawers/listDrawers")?>">All Drawers</a></li>
                <?if($this->user_model->getIsSuperAdmin() || $this->user_model->getAccessLevel("instance",$this->instance)>=PERM_CREATEDRAWERS || $this->user_model->getMaxCollectionPermission() >= PERM_CREATEDRAWERS):?>
                <?=$this->template->addToDrawer?>
                <?endif?>
              </ul>
            </li>
             <?if($this->user_model->getIsSuperAdmin() || $this->user_model->getAccessLevel("instance",$this->instance)>=PERM_ADDASSETS || $this->user_model->getMaxCollectionPermission() >= PERM_ADDASSETS):?>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Edit <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="<?=instance_url("/assetManager/addAssetModal")?>" data-toggle="modal" data-target="#addAssetModal">Add Asset</a></li>
                <?if(isset($assetModel)):?>
                <li><a href="<?=instance_url("assetManager/editAsset/" . $assetModel->getObjectId())?>">Edit Asset</a></li>
                <li><a href="<?=instance_url("assetManager/restoreAsset/" . $assetModel->getObjectId())?>">Restore Asset</a></li>
                 <li class="divider"></li>
                <li><a href="<?=instance_url("assetManager/deleteAsset/" . $assetModel->getObjectId())?>" onclick="return confirm('Are you sure you wish to delete this asset and all derivatives?')">Delete Asset</a></li>
                <?endif?>
                <li class="divider"></li>
                <li><a href="<?=instance_url("assetManager/userAssets/")?>">All my Assets</a></li>
              </ul>
            </li>
            <?endif?>
             <?if($this->user_model->isInstanceAdmin() || $this->user_model->getIsSuperAdmin()):?>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="<?=instance_url("instances/edit/" . $this->instance->getId())?>">Instance Settings</a></li>
                <li><a href="<?=instance_url("permissions/edit/instance/" . $this->instance->getId())?>">Instance Permissions</a></li>
                <li><a href="<?=instance_url("instances/customPages/")?>">Instance Pages</a></li>
                <li><a href="<?=instance_url("reports/")?>">Reports</a></li>
                <li><a href="<?=instance_url("templates/")?>">Edit Templates</a></li>
                <li><a href="<?=instance_url("collectionManager")?>">Edit Collections</a></li>
                <li><a href="<?=instance_url("assetManager/importFromCSV")?>">Import from CSV</a></li>
                <li><a href="<?=instance_url("assetManager/exportCSV")?>">Export to CSV</a></li>
                <?if($this->user_model->getIsSuperAdmin()):?>
                 <li class="divider"></li>
                <li><a href="<?=instance_url("admin")?>">Elevator Admin</a></li>
                <li><a href="<?=instance_url("admin/logs")?>">Elevator Logs</a></li>
                <?endif?>
              </ul>
            </li>
            <?endif?>
        <?endif?>
      <?endif?>


  <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Help <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="http://www.elevatorapp.net">General Help and Info</span></a></li>
                <?if($this->instance->getOwnerHomepage()):?>
                <li><a href="<?=$this->instance->getOwnerHomepage()?>">Contact Us</span></a></li>
                <?endif?>
                </ul>
                </li>
      </ul>
      <?if($this->user_model->userLoaded && !$this->user_model->assetOverride):?>
      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></b></a>
          <ul class="dropdown-menu">
            <li><A href="#">Logged in as <?=$this->user_model->getDisplayName()?></a></li>
            <li><a href="<?=instance_url("loginManager/logout/")?>">Logout</a></li>
            <li><a href="<?=instance_url("permissions/editUser/".$this->user_model->getId())?>">Preferences</a></li>
          </ul>
        </li>
      </ul>
      <?else:?>
        <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"><b class="caret"></b></span><span class="signInLink">Sign In</span></a>
          <ul class="dropdown-menu">
            <?if(isset($this->instance) && $this->instance->getUseCentralAuth()):?>
            <li class="universityAuth"><a class="loginLink" href="<?=instance_url("loginManager/remoteLogin/?redirect=".current_url())?>"><?=$this->config->item("remoteLoginLabel")?> User</a></li>
            <?endif?>
            <li><a class="loginLink" href="<?=instance_url("loginManager/localLogin/?redirect=".current_url())?>"><?=$this->config->item("guestLoginLabel")?> User</a></li>
            <script>
            if(window.location.hash) {
              $('.loginLink').each(function(index, el) {
                $(el).attr('href', $(el).attr('href') + window.location.hash.replace("#","%23"));  
              });
              
            }
            </script>
          </ul>
        </li>
      </ul>
      <?endif?>

    </div><!-- /.navbar-collapse -->
<?endif?>


  </nav>



  <div class="container mainContent">

    <?php
    // This is the main content partial
    echo $this->template->content;
    ?>


</div>

    <footer class="footer">
      <p>
        <img src="/assets/images/elevatorSolo.png" class="elevatorFooterImage">Powered by Elevator, developed by the <A href="http://www.umn.edu">University of Minnesota</a>
      </p>
    </footer>

<div class="modal fade" id="addAssetModal" tabindex="-1" role="dialog" aria-labelledby="addAssetModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="advancedSearchModal" tabindex="-1" role="dialog" aria-labelledby="advancedSearchModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?=$this->template->javascript; ?>
<script>
var lazyInstance;
$(document).ready(function() {
   lazyInstance = $('.lazy').Lazy({ chainable: false, autoDestroy:false });
});
</script>


<script>
// listen for messages from our javascript pluginSetup
function receiveMessage(e) {
  if (typeof e.data.pluginSetup !== 'undefined') {
    sessionStorage.elevatorPlugin = e.data.elevatorPlugin;
    sessionStorage.elevatorCallbackType = e.data.elevatorCallbackType;
    sessionStorage.apiKey = e.data.apiKey;
    sessionStorage.timeStamp = e.data.timeStamp;
    sessionStorage.entangledSecret = e.data.entangledSecret;
    sessionStorage.includeMetadata = e.data.includeMetadata;
    testAndShowEmbedButton();
  }

}

window.addEventListener("load",function(){
  window.addEventListener('message', receiveMessage);
  if (this.window.name == "elevatorPlugin") {
    this.window.opener.postMessage("parentLoaded", "*");
  }
});

$(".addToPlugin").on("click", function() {
  fileObjectId = $("#embedView").data("objectid");
  if(sessionStorage.elevatorCallbackType == "lti") {
    $.post(basePath + "api/v1/lti/ltiPayload", {object: fileObjectId}, function(data, textStatus, xhr) {
        returnForm = '<form id="ltiForm" action="' + sessionStorage.returnURL + '" method="post" encType="application/x-www-form-urlencoded"> \
                  <input type="hidden" name="lti_message_type" value="ContentItemSelection" /> \
                  <input type="hidden" name="lti_version" value="LTI-1p0" /> \
                  <input type=hidden name="content_items" value="" id="lti_content" /> \
                </form>';

        $("body").append(returnForm);
        $("#lti_content").val(data);
        $("#ltiForm").submit();      
    });


  }
  else if(sessionStorage.elevatorCallbackType == "JS") {
    var currentLocation = window.location.toString();
    var originalWindow = window.opener;
    originalWindow.postMessage({"pluginResponse": true, "fileObjectId": fileObjectId, "objectId":objectId, "currentLink": currentLocation}, "*");   
  }

});


var testAndShowEmbedButton =function() {
  if(sessionStorage.elevatorPlugin) {
    if($("#embedView").length > 0) {
      $(".addToPlugin").text("Add to " + sessionStorage.elevatorPlugin);
      $(".addToPlugin").removeClass('hide');  
    }
    
  }
}


$(document).ready(function() {
  testAndShowEmbedButton();
});
</script>


</body>
</html>
