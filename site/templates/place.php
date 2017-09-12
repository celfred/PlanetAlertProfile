<?php 
/* Place template */

include("./head.inc"); 

  // Test if a player is connected
  if ($user->isLoggedin()) {
    if ($user->isSuperuser() == false ) { // Not admin
      echo '<div class="row well">';
      echo '<h4>';
      echo '<span class="lead label label-default"><span class="glyphicon glyphicon-signal"></span>'.$player->level.'&nbsp;&nbsp;<img src="'.$config->urls->templates.'img/gold_mini.png" alt="GC" />'.$player->GC.'</span>';
      echo '</h4>';
      echo '<img src="'.$player->avatar->url.'" alt="No avatar" />';
      if ($player->places->count > 0) {
        $playerPlaces = [];
        foreach ($player->places as $place) {
          array_push($playerPlaces, $place->id);
          $mini = "<img class='img-thumbnail' data-toggle='tooltip' data-html='true' data-original-title='".$place->title."' src='".$place->photo->eq(0)->getCrop('thumbnail')->url."' alt='Photo' />";
          echo $mini;
        }
      } else {
        echo '<span class="label label-info">No places.</span>';
      }
      echo '<a class="btn btn-block btn-primary" href="'.$pages->get('/shop_generator')->url.$player->id.'">Go to the marketplace</a>';
      echo '</div>';
    }
  }

  echo '<h4 class="text-center"><a href="'.$pages->get("name=places")->photo->eq(0)->url.'" target="_blank" data-toggle="tooltip" title="Write the corresponding number on your places in your copybook.">See the map with numbers</a></h4>';

  if ($user->isSuperuser()) {
    echo '<a class="pdfLink btn btn-info" href="'. $page->url.'?pages2pdf=1">Get PDF ['.$page->title.']</a>';
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
  $owners = $pages->find("template=player,places=$page->id");
?>
  <table class="table">
    <tr>
      <td rowspan="2" class="col-sm-2">
        <img class="img-thumbnail" src="<?php echo $thumbImage; ?>" alt="Photo" />
      </td>
      <td class="col-sm-8">
        <h1><?php echo $page->title; ?></h1>
        <?php echo "<h2><a href='places/?type=city&name={$city->name}' data-toggle='tooltip' title='See all places in {$city->title}' data-placement='bottom'>{$city->title}</a>, <a href='places/?type=country&name={$country->name}' data-toggle='tooltip' title='See all places in {$city->country}' data-placement='bottom'>{$country->title}</a></h2>"; ?>
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
      <td colspan="2" class="col-sm-10">
        <p class="lead"><?php echo $page->summary; ?> <span class="btn btn-info"><a href="<?php echo $page->link; ?>">[Read more about this place]</a></span></p>
      </td>
    <tr>
      <td colspan="3" class="col-sm-12">
        <?php
          $map = $modules->get('MarkupGoogleMap');
          echo $map->render($page, 'mapMarker'); 
        ?>
      </td>
    </tr>
    <tr>
      <td colspan="3">
          <?php 
            $totalOwners = count($owners);
            $i=0;
            echo "<small><span class='glyphicon glyphicon-user'></span> This place has been freed by {$totalOwners} player(s) : ";
            foreach ($owners as $owner) {
              if ($owner == $owners->last()) {
                echo "{$owner->title} [{$owner->team->title}]";
              } else {
                echo "{$owner->title} [{$owner->team->title}], ";
              }
            }
          echo "</small>";
          ?>
      </td>
    </tr>
  </table>
  <?php
    if ($user->isLoggedin() && !$user->isSuperuser()) {
      echo '<a class="btn btn-block btn-primary" href="'.$pages->get('/shop_generator')->url.$player->id.'">Go to the marketplace</a>';
    }

  include("./foot.inc"); 
?>
