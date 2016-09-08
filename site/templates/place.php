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
          $mini = "<img class='img-thumbnail' data-toggle='tooltip' data-html='true' data-original-title='".$place->title."' src='".$place->photo->eq(0)->getThumb('thumbnail')."' alt='Photo' />";
          echo $mini;
        }
      } else {
        echo '<span class="label label-info">No places.</span>';
      }
      echo '<a class="btn btn-block btn-primary" href="'.$pages->get('/shop_generator')->url.$player->id.'">Go to the marketplace</a>';
      echo '</div>';
    }
  }

if ($page->name != 'places') { // Single place view
  if ($user->isSuperuser()) {
    echo '<a class="pdfLink btn btn-info" href="'. $page->url.'?pages2pdf=1">Get PDF ['.$page->title.']</a>';
  }

  //$thumbImage = $page->photo->eq(0)->getThumb('thumbnail');
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
  <?php echo '<a class="btn btn-block btn-primary" href="'.$pages->get('/shop_generator')->url.$player->id.'">Go to the marketplace</a>'; ?>
<?php
} else { // All places view, Country view, City view

  if ($user->isSuperuser()) {
    echo '<a class="pdfLink btn btn-info" href="'. $page->url.'all?pages2pdf=1">Get PDF [The Map]</a>';
  }

  // Get any limiting elements in URL
  $type = $input->get->type;
  $name = $input->get->name;
  $formattedName = $pages->get("name=$name")->title;
  // Set parent accordingly
  if ($type && $name) {
    $parent = $pages->get("template=$type, name=$name");
    $angularParam = "&".$type."=".$name;
  } else {
    $parent = $pages->get("/places");
    $angularParam = '';
  }

  // Get selected places (or all places if $parent=/places
  $selector = "template=place, name!=places, has_parent=$parent, sort=name, limit=30";
  $selectedPlaces = $pages->find($selector);
  $pagination = $selectedPlaces->renderPager(array(
    'nextItemLabel' => ">",
    'previousItemLabel' => "<",
    'listMarkup' => "<ul class='pagination pagination-sm'>{out}</ul>",
    'currentItemClass' => "active",
  ));
  $pageNum = $input->pageNum;
  
  // Count ALL places in the game (for information)
  $totalCount = $pages->find("template=place, name!='places'")->count();
  // Get all cities having places
  $cities = $pages->find("template=city, children.count>0, sort=title");
  // Get all countries having places
    $countries = $pages->find("template=country, children.count>0,sort=title");

  if (!$type) {
    $type = 'All places';
    $name = $totalCount.' places in '.$cities->count().' cities, in '.$countries->count().' different countries.';
  }
  ?>
  <div class="row">
    <div class="text-center">
      <h2><a href="map/">See complete Planet Alert World map</a></h2>
      <?php
        if ($formattedName) {
          echo "<h2>Places in : {$formattedName} ({$selectedPlaces->count()})</h2>";
        } else {
          echo "<h2>All places ({$totalCount})</h2>";
        }
      ?>
      <a class="btn btn-info" href="<?php echo $page->url; ?>">See ALL places</a>
      <div class="dropdown btn-group">
        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">Select a country <span class="caret"></span></button>
        <ul class="dropdown-menu" role="menu">
          <?php
            foreach($countries as $country) {
             echo "<li><a href='{$page->url}?type=country&name={$country->name}'>{$country->title}</a></li>";
            }
          ?>
        </ul>
      </div>
      <div class=" dropdown btn-group">
        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">Select a city <span class="caret"></span></button>
        <ul class="dropdown-menu" role="menu">
          <?php
            foreach($cities as $city) {
              echo "<li><a href='{$page->url}?type=city&name={$city->name}'>{$city->title}</a></li>";
            }
          ?>
        </ul>
      </div>
      <span id="switchGallery" class="btn btn-primary" onclick="">Change view</span>
    </div>

  <div id="galleryPlacesList" class="text-center">
    <div class="text-center"><?php echo $pagination; ?></div>
    <ul class="list-inline placesList">
      <?php
        foreach($selectedPlaces as $place) {
          $thumbImage = $place->photo->eq(0)->getThumb('thumbnail');
          $city = $place->parent->title;
          $country = $place->parent->parent->title;
          echo "<li><a href='{$place->url}'><img class='img-thumbnail' src='{$thumbImage}' alt='' data-toggle='tooltip' data-html='true' title='<h4>$place->title</h4> <h5>{$city},{$country}</h5> <strong>Coût: {$place->GC} or, Niveau {$place->level}</strong>' dta-placement='bottom' /></a></li>";
        }
      ?>
    </ul>
    <div class="text-center"><?php echo $pagination; ?></div>
  </div>

  <div id="detailedPlacesList" style="display: none;" class="row">
    <div class="text-center"><?php echo $pagination; ?></div>
    <table id="mapTable" class="table table-condensed table-hover">
      <thead>
      <tr>
        <th>Name</th>
        <th>Country</th>
        <th>City</th>
        <th>GC</th>
        <th>Level</th>
      </tr>
      </thead>
      <tbody>
      <?php foreach ($selectedPlaces as $place) { ?>
      <tr>
        <td>
          <?php echo $place->title; ?>
          <img src="<?php echo $place->photo->eq(0)->getThumb('mini'); ?>" />
        </td>
        <td><?php echo $place->country->title; ?></td>
        <td><?php echo $place->city->title; ?></td>
        <td><?php echo $place->GC; ?></td>
        <td><?php echo $place->level; ?></td>
      </tr>
      <?php } ?>
      </tbody>
    </table>
    <div class="text-center"><?php echo $pagination; ?></div>
  </div>

  <?php echo '<a class="btn btn-block btn-primary" href="'.$pages->get('/shop_generator')->url.$player->id.'">Go to the marketplace</a>'; ?>
</div>
<?php
}

  include("./foot.inc"); 

