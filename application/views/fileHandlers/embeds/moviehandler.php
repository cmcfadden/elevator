<?
$fileObjectId = $fileObject->getObjectId();

$mediaArray = array();
if(isset($fileContainers['stream'])) {
  $entry["type"] = "hls";
  $entry["file"] = stripHTTP(instance_url("/fileManager/getStream/" . $fileObjectId . "/base"));
  $entry["label"] = "Streaming";
  $mediaArray["stream"] = $entry;
}

if(isset($fileContainers['mp4sd'])) {
  $entry["type"] = "mp4";
  $entry["file"] = stripHTTP($fileContainers['mp4sd']->getProtectedURLForFile());
  $entry["label"] = "SD";
  $mediaArray["mp4sd"] = $entry;
}

if(isset($fileContainers['mp4hd'])) {
  $entry["type"] = "mp4";
  $entry["file"] = stripHTTP($fileContainers['mp4hd']->getProtectedURLForFile());
  $entry["label"] = "HD";
  $mediaArray["mp4hd"] = $entry;
}

if(isset($fileContainers['mp4hd1080'])) {
  $entry["type"] = "mp4";
  $entry["file"] = stripHTTP($fileContainers['mp4hd1080']->getProtectedURLForFile());
  $entry["label"] = "HD1080";
  $mediaArray["mp4hd1080"] = $entry;
}

$derivatives = array();
if($fileObject->sourceFile->metadata["duration"] < 300) {
  if(array_key_exists("stream", $mediaArray)) {
    $derivatives[] = $mediaArray["stream"];
  }
  if(array_key_exists("mp4sd", $mediaArray)) {
    $derivatives[] = $mediaArray["mp4sd"];
  }
  if(array_key_exists("mp4hd", $mediaArray)) {
    $derivatives[] = $mediaArray["mp4hd"];
  }

  
}
else {
  if(array_key_exists("stream", $mediaArray)) {
    $derivatives[] = $mediaArray["stream"];
  }
  if(array_key_exists("mp4sd", $mediaArray)) {
    $derivatives[] = $mediaArray["mp4sd"];
  }
  if(array_key_exists("mp4hd", $mediaArray)) {
    $derivatives[] = $mediaArray["mp4hd"];
  }

  
}



$playlist = [];

$playlist["image"] = null;

if(isset($fileContainers['imageSequence'])) {
  $playlist["image"] = stripHTTP($fileContainers['imageSequence']->getProtectedURLForFile("/2"));
}
if(isset($fileObject->sourceFile->metadata["spherical"])) {
  $playlist["stereomode"] = isset($fileObject->sourceFile->metadata["stereo"])?"stereoscopicLeftRight":"monoscopic";
}
$playlist["sources"] = [];
foreach($derivatives as $entry) {
  $playlist["sources"][] = [
    "type"=>$entry["type"], 
    "file"=>$entry["file"], 
    "label"=>$entry["label"],
    "default"=>($entry["label"]=="HD")?"true":"false"
  ];
}

$playlist["tracks"] = [];
if(isset($fileContainers['vtt'])) {
  $playlist["tracks"][] = [
    "file" => isset($fileContainers['vtt'])?stripHTTP($fileContainers['vtt']->getProtectedURLForFile(".vtt")):null,
    "kind" => "thumbnails"
  ];
}

if(isset($widgetObject->sidecars) && array_key_exists("captions", $widgetObject->sidecars) && strlen($widgetObject->sidecars['captions'])>5) {
  $playlist["tracks"][] = [
    "file" => stripHTTP(instance_url("fileManager/getSidecar/" . $fileObjectId . "/captions")),
    "label" => "English",
    "kind" => "captions"
  ];
}

if(isset($widgetObject->sidecars) && array_key_exists("chapters", $widgetObject->sidecars) && strlen($widgetObject->sidecars['chapters'])>5) {
  $playlist["tracks"][] = [
    "file" => stripHTTP(instance_url("fileManager/getSidecar/" . $fileObjectId . "/chapters")),
    "kind" => "chapters"
  ];
}


?>
<? // https://github.com/jwplayer/jwplayer can build from ?>
<script src="https://cdn.jwplayer.com/libraries/pTP0K0kA.js"></script>
<script src="/assets/js/excerpt.js"></script>

<script>

if(typeof objectId == 'undefined') {
  objectId = "<?=$fileObjectId?>";
}
</script>

<? if(!isset($fileContainers) || count($derivatives) == 0):?>
  <p class="alert alert-info">No derivatives found.
  <?if(!$this->user_model->userLoaded):?>
    <?$this->load->view("errors/loginForPermissions")?>
    <?if($embedded):?>
      <?$this->load->view("login/login")?>
    <?endif?>
  <?endif?>
  </p>
  
<?else:?>    
    <div id="videoElement">Loading the player...</div>
    <script type="text/javascript">

  var haveSeeked = false;
  var havePaused = false;
  var currentPosition = null;
  var firstPlay = false;
  var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor); 
  var chromeVersion = false;
  if(isChrome) {
    var raw = navigator.userAgent.match(/Chrom(e|ium)\/([0-9]+)\./);
    var version = parseInt(raw[2], 10);
    if(!isNaN(version)) {
      chromeVersion = version;
    }
  }
  var weAreHosed = false;
  var firstPlay = false;
  var seekTime = null;
  var needSeek = false;
  function buildPlayer() {
    jwplayer("videoElement").setup({
      ga: { label:"label"},
      playlist: <?=json_encode($playlist) ?>,
      width: "100%",
      height: "100%",
      preload: 'none'
      });
    }
    
    function registerJWHandlers() {
      jwplayer().onReady(function(event) {
        // jwplayer().onQualityLevels(function(event) {
        //   if(event.levels.length > 1 && screen.width > 767) {
        //     jwplayer().setCurrentQuality(1);
        //   }
          
        // });
        
        jwplayer().on('seek', function(event) {
          haveSeeked=true;
          if(jwplayer().getState('paused') == 'paused') {
            seekTime = event.offset;
            needSeek = true;
          }
          
        });
        jwplayer().on('pause', function(event) {
          havePaused=true;
        });
        jwplayer().on('play', function(event) {
          if(!firstPlay) {
            firstPlay = true;
            return;
          }
          else {
            console.log("on second play");
          }

          if(event.playReason == "external") {
            return;
          }
          console.log(chromeVersion);
          if((haveSeeked || havePaused) && isChrome && chromeVersion < 78) {
            var playlist = jwplayer().getPlaylist();

            if(playlist[0].label == "Streaming" || playlist[0].sources[0].label == "Streaming") {
              return;
            }

            rebuilding = true;
            haveSeeked=false;
            havePaused=false;
            weAreHosed = true;
            firstPlay = false;
            currentPosition= jwplayer().getPosition();
            buildPlayer();
            registerJWHandlers();
            // jwplayer().play();
            if(needSeek) {
              console.log("seeking to existing location" + seekTime)
              jwplayer().seek(seekTime);
              needSeek = false;
            }
            else {
              console.log("seeking to new position:" + currentPosition)
              jwplayer().seek(currentPosition);
            }
            needSeek = false;
            currentPosition = null;
            
            
            
          }
          
        })
        
      });
      
    }

    buildPlayer();
    registerJWHandlers();

        // JW player is dumb about default to HD footage so we do it manually if possible
    
    
    $(".videoColumn").on("remove", function() {
      jwplayer("videoElement").remove();
    });

  </script>
  <?endif?>
