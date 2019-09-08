<?php namespace ProcessWire; 
/* Place template */

include("./head.inc"); 

  // Test if a player is connected
  if ($user->hasRole('player')) { // Show player's mini-profile
    echo '<div class="row well lead text-center">';
      echo miniProfile($player, 'places');
      $item = possibleElement($player, $page);
      // helpAlerts
      switch($item->pb) {
        case 'possible' : 
          $helpAlert = true;
          $helpTitle = __("You can buy this item !");
          break;
        case 'helmet' : 
          $helpAlert = true;
          $helpTitle = __("Memory helmet required !");
          $link = '<a href="'.$pages->get("name=memory-helmet")->url.'">Memory Helmet</a>';
          $helpMessage = sprintf(__('You must buy the %s first before buying this item'), $link);
          break;
        case 'already' : 
          $helpAlert = true;
          $helpTitle = __("You already own this item !");
          break;
        case 'freeActs' : 
          $nbEl = $player->places->count()+$player->people->count();
          $helpAlert = true;
          $helpTitle = __("More free elements required !");
          $helpMessage = sprintf(__('This item requires %1$s free elements ! You have only %2$s free elements.'), $item->freeActs, $nbEl);
          break;
        case 'GC' : 
          $helpAlert = true;
          $helpTitle = __("Not enough GC !");
          $helpMessage =  sprintf(__('This item requires %1$s GC ! You have only %2$sGC !'), $item->GC, $player->GC);
          break;
        case 'level' : 
          $helpAlert = true;
          $helpTitle = __("Low Level !");
          $helpMessage =  sprintf(__('This item requires a level %1$s ! You are only at level %2$s !'), $item->level, $player->level);
          break;
        default: 
          $helpAlert = true;
          $helpTitle = __("You can't buy this item for the moment. Sorry.");
      }
    echo '</div>';
    include("./helpAlert.inc.php");
  }

  /* $thumbImage = $page->photo->eq(0)->getCrop('thumbnail')->url; */
  $imageHeight = $page->photo->eq(0)->height;
  $imageWidth = $page->photo->eq(0)->width;
  if ($imageWidth > $imageHeight) { // Landscape
    $thumbImage = $page->photo->eq(0)->size(0,200)->url;
  } else { // Portrait
    $thumbImage = $page->photo->eq(0)->size(200,0)->url;
  }
  $imageLink = $page->photo->eq(0)->description;
  $city = $page->city;
  $country = $page->country;

    if ($user->isSuperuser() || $user->hasRole('teacher')) {
      echo '<section class="row">';
      echo '<a class="pdfLink btn btn-info" href="'. $page->url.'?pages2pdf=1">Get PDF ['.$page->title.']</a>';
      echo '</section>';
    }
?>
  
  <section class="row">
    <section class="col-sm-3 text-center">
      <div class="board panel panel-primary">
      <div class="panel-heading">
        <?php
          if ($user->isSuperuser()) {
            echo '<p class="pull-right btn btn-default">'.$page->feel(array("fields" => "title,summary,level,GC,photo,map")).'</p>';
          }
        ?>
        <h1 class="panel-title"><span class="lead"><?php echo $page->title; ?></span></h1>
      </div>
      <div class="panel-body text-center">
        <?php
          if ($imageLink != '') {
             echo '<a target="_blank" href="'.$imageLink.'"><img class="img-rounded" src="'.$thumbImage.'" alt="'.$page->title.' photo" data-toggle="tooltip" data-placement="right" title="'.__("Click to enlarge and see attributions").'" /></a></p>';
          } else {
            echo '<img class="img-thumbnail" src="'.$thumbImage.'" alt="'.$page->title.'" />';
          }
          if ($city) {
            echo '<h4 class="">'.__("City").' : '.$city->title.'</h4>';
          } 
          if ($country) {
            echo '<h4 class="">'.__("Country").' : ';
            echo '<a href="'.$pages->get("name=places")->url.'country/'.$country->name.'" data-toggle="tooltip" data-placement="right" title="'.__("See all places in ").$country->title.' data-placement="bottom">'.$country->title.' </a></h4>';
          } 
        ?>
        <hr />
        <h4 class="">
          <?php echo __('Level').' <span class="">'.$page->level; ?></span>
          &nbsp;&nbsp;
          <span class="badge"><?php echo $page->GC.__("GC"); ?></span>
        </h4>
      </div>
      <div class="panel-footer text-center">
        <a href="<?php echo $pages->get("name=places")->url; ?>"><?php echo __("See all places list"); ?></a>
      </div>
      </div>
    </section>
    <section class="col-sm-9">
      <?php
      echo '<p class="well lead text-justify">';
        echo $page->summary;
        echo '<span class="btn btn-info pull-right"><a href="'.$page->link.'">'.__("[Read more about this place]").'</a></span>';
        if ($user->language->name != 'french') {
          $page->of(false);
          if ($page->summary->getLanguageValue($french) != '') {
            echo ' <a class="frenchVersion" data-toggle="collapse" href="#collapseDiv" aria-expanded="false" aria-controls="collapseDiv">'.__("[French version]").'</a>';
            echo '<div class="collapse" id="collapseDiv">';
            echo '<div class="well">';
            echo $page->summary->getLanguageValue($french);
            echo '</div>';
            echo '</div>';
          }
        }
        echo '</p>';
        $map = $modules->get('MarkupLeafletMap');
        echo $map->getLeafletMapHeaderLines();
        if ($page->map->zoom > 5) {
          $page->map->zoom = 2;
        }
        /* $options = array('markerIcon' => 'flag', 'markerColour' => 'green', 'provider' => 'Stamen.Toner'); */
        /* $options = array('markerIcon' => 'flag', 'markerColour' => 'green', 'provider' => 'OpenTopoMap'); */
        /* $options = array('markerIcon' => 'flag', 'markerColour' => 'green', 'provider' => 'OpenStreetMap.Mapnik'); */
        /* $options = array('markerIcon' => 'flag', 'markerColour' => 'green', 'provider' => 'OpenStreetMap.HOT'); */
        $options = array('markerIcon' => 'flag', 'markerColour' => 'green', 'class' => 'mapBox', 'provider' => 'Stamen.TonerLite');
        echo $map->render($page, 'map', $options); 
      ?>
    </section>
  </section>

<?php
  include("./foot.inc"); 
?>
