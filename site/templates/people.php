<?php namespace ProcessWire;
  include("./head.inc");

  // Test if a player is connected
  if ($user->hasRole('player')) { // Show player's mini-profile
    echo '<div class="row well lead text-center">';
      echo miniProfile($player, 'people');
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
          $helpTitle = __("Not enoigh GC !");
          $helpMessage =  sprintf(__('This item requires %1$s GC ! You have only %2$sGC !'), $item->GC, $player->GC);
          break;
        case 'level' : 
          $helpAlert = true;
          $helpTitle = __("Not enough GC !");
          $helpMessage =  sprintf(__('This item requires a level %1$s ! You are only at level %2$sGC !'), $item->level, $player->level);
          break;
        default: 
          $helpAlert = true;
          $helpTitle = __("You can't buy this item for the moment. Sorry.");
      }
    echo '</div>';
    include("./helpAlert.inc.php");
  }

  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    echo '<a class="pdfLink btn btn-info" href="'. $page->url.'?pages2pdf=1">Get PDF ['.$page->title.']</a>';
  }
  if ($user->isSuperuser()) {
    echo '<p>'.$page->feel(array("fields" => "title,summary")).'</p>';
  }

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
          <h1 class="panel-title"><?php echo __('Level').' : '.$page->level; ?></h1>
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
      <?php 
      if ($user->language->name != 'french') {
        $page->of(false);
        if ($page->summary->getLanguageValue($french) != '') {
          echo '<a class="btn btn-sm btn-primary" data-toggle="collapse" href="#collapseDiv" aria-expanded="false" aria-controls="collapseDiv">'.__("[French version]").'</a>';
          echo '<div class="collapse" id="collapseDiv">';
          echo '<div class="well">';
          echo $page->summary->getLanguageValue($french);
          echo '</div>';
          echo '</div>';
        }
      }
      ?>
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
