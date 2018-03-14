<?php namespace ProcessWire; /* All places template */
  include("./head.inc"); 

  if ($user->isLoggedin() && !$user->isSuperuser()) { // Show player's mini-profile
    echo '<div class="row well text-center">';
      echo miniProfile($player, 'places');
    echo '</div>';
  }

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
    <h2><a href="<?php echo $pages->get('name=map')->url; ?>">See complete Planet Alert World map</a></h2>
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
          $thumbImage = $place->photo->eq(0)->getCrop('thumbnail');
          $city = $place->parent->title;
          $country = $place->parent->parent->title;
          echo "<li><a href='{$place->url}'><img class='img-thumbnail' src='{$thumbImage->url}' alt='' data-toggle='tooltip' data-html='true' title='<h4><span>{$place->mapIndex}</span> - {$place->title}</h4> <h5>{$city},{$country}</h5> <strong>Level {$place->level}, {$place->GC} GC</strong>' data-placement='bottom' /></a></li>";
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
          <img src="<?php echo $place->photo->eq(0)->getCrop('mini')->url; ?>" />
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

  <?php 
    if ($user->isLoggedin() && !$user->isSuperuser()) {
      echo '<a class="btn btn-block btn-primary" href="'.$pages->get('/shop_generator')->url.$player->id.'">Go to the marketplace</a>';
    }
  ?>
</div>

<?php 
  include("./foot.inc");
?>
