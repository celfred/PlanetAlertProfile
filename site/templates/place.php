<?php namespace ProcessWire; 
/* Place template */

include("./head.inc"); 

  // Test if a player is connected
  if ($user->hasRole('player')) { // Show player's mini-profile
    echo '<div class="row well text-center">';
      echo miniProfile($player, 'places');
      $item = possibleElement($player, $page);
      switch($item->pb) {
        case 'possible' : 
          echo "<p class='lead'>You can buy this item.</p>";
          echo  '<a class="btn btn-block btn-primary" href="'.$pages->get('/shop_generator')->url.$player->id.'">Go to the marketplace</a>';
          break;
        case 'helmet' : 
          echo '<p class="lead">You must buy the <a href="'.$pages->get("name=memory-helmet")->url.'">Memory Helmet</a> first before buying this item.</p>';
          break;
        case 'already' : 
          echo "<p class='lead'>".__("You already own this item.")."</p>";
          break;
        case 'freeActs' : 
          $nbEl = $player->places->count()+$player->people->count();
          echo "<p class='lead'>".sprintf(__("This item requires %1$s free elements ! You have only %2$s free elements."), $item->freeActs, $nbEl)."</p>";
          break;
        case 'GC' : 
          echo "<p class='lead'>This item requires ".$item->GC."GC ! You have only ".$player->GC."GC.</p>";
          break;
        case 'level' : 
          echo "<p class='lead'>This item requires a level ".$item->level." ! You are only at level ".$player->level.".</p>";
          break;
        default: 
          echo "<p class='lead'>You can't buy this item for the moment. Sorry.</p>";
      }
    echo '</div>';
  }

  echo '<h4 class="text-center"><a href="http://download.tuxfamily.org/planetalert/map/worldMap-numbers.png" target="_blank" data-toggle="tooltip" title="'.__("This map should be in your copybook.").'">';
  echo __('See the map with numbers').'</a></h4>';

  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    echo '<a class="pdfLink btn btn-info" href="'. $page->url.'?pages2pdf=1">Get PDF ['.$page->title.']</a>';
  }
  if ($user->isSuperuser()) {
    echo '<p>'.$page->feel(array("fields" => "title,summary")).'</p>';
  }

  /* $thumbImage = $page->photo->eq(0)->getCrop('thumbnail')->url; */
  $imageHeight = $page->photo->eq(0)->height;
  $imageWidth = $page->photo->eq(0)->width;
  if ($imageWidth > $imageHeight) { // Landscape
    $thumbImage = $page->photo->eq(0)->size(0,200)->url;
  } else { // Portrait
    $thumbImage = $page->photo->eq(0)->size(200,0)->url;
  }
  $city = $page->parent;
  $country = $page->parent->parent;
?>
  <table class="table">
    <tr>
      <td rowspan="2" class="col-sm-3">
        <img class="img-thumbnail" src="<?php echo $thumbImage; ?>" alt="Photo" />
      </td>
      <td class="col-sm-7">
        <h1><span class="label label-danger" data-toggle="tooltip" title="<?php echo __("Map index (Write it in your copybook)"); ?>"><?php echo $page->mapIndex; ?></span> <?php echo $page->title; ?></h1>
        <?php echo "<h2><a href='places/?type=city&name={$city->name}' data-toggle='tooltip' title='See all places in {$city->title}' data-placement='bottom'>{$city->title}</a>, <a href='places/?type=country&name={$country->name}' data-toggle='tooltip' title='".__("See all places in ")."{$country->title}' data-placement='bottom'>{$country->title}</a></h2>"; ?>
      </td>
      <td class="col-sm-2">
        <div class="panel panel-success">
        <div class="panel-heading">
          <h1 class="panel-title">Level : <?php echo $page->level; ?></h1>
        </div>
        <div class="panel-body text-center">
          <h1><img style="float: left;" src="<?php  echo $config->urls->templates?>img/gold.png" alt="Value" width="50" height="50" /><span class="lead btn btn-default btn-lg"><?php echo $page->GC; ?></span></h1>
        </div>
        </div>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="col-sm-10 text-justify">
      <p class="lead"><?php echo $page->summary; ?> <span class="btn btn-info"><a href="<?php echo $page->link; ?>"><?php echo __("[Read more about this place]"); ?></a></span></p>
      </td>
    <tr>
      <td colspan="3" class="col-sm-12">
        <?php 
          $map = $modules->get('MarkupLeafletMap');
          echo $map->getLeafletMapHeaderLines();
          $options = array('markerIcon' => 'flag', 'markerColour' => 'red');
          echo $map->render($page, 'map', $options); 
        ?>
      </td>
    </tr>
  </table>
<?php
  include("./foot.inc"); 
?>
