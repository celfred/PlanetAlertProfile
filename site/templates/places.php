<?php namespace ProcessWire; /* All places template */
  include("./head.inc"); 

  if ($user->hasRole('player')) { // Show player's mini-profile
    echo '<div class="row well text-center">';
      echo miniProfile($player, 'places');
    echo '</div>';
  }

  // Get any limiting elements from urlSegments
  $type = $input->urlSegment1;
  $name = $input->urlSegment2;
  $formattedName = $pages->get("name=$name")->title;
  // Set parent accordingly
  if ($type && $name) {
    $parent = $pages->get("template=$type, name=$name");
  } else {
    $type = 'all';
    $parent = $pages->get("/places");
  }

  // Get selected places (or all places if $parent=/places)
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
  $totalCount = $pages->find("template=place")->count();
  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    echo '<a class="pdfLink btn btn-info" href="'. $selectedPlaces->first()->url.'all?pages2pdf=1">'.__("Get PDF [Places catalogue]").'</a>';
  }

  ?>
  <div class="row">
    <div class="text-center">
    <h2><a href="<?php echo $pages->get('name=map')->url; ?>"><?php echo __("See complete Planet Alert World map"); ?></a></h2>
  <?php
      if ($formattedName) {
        echo "<h2>".__('Places in')." : {$formattedName} ({$selectedPlaces->count()})</h2>";
      } else {
        echo "<h2>".__('All places')." ({$totalCount})</h2>";
      }

      $placesMenu = $cache->get("cache__placesMenu-".$user->language->name, 2678400, function($page, $pages) {
        $cities = $pages->find("template=city, children.count>0, sort=title");
        $countries = $pages->find("template=country, children.count>0,sort=title");
        $out = '';
        $out .= '<a class="btn btn-info" href="'.$page->url.'">'.__('See ALL places').'</a>';
        $out .= '<div class="dropdown btn-group">';
        $out .= '<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">'.__('Select a country').' <span class="caret"></span></button>';
        $out .= '<ul class="dropdown-menu" role="menu">';
          foreach($countries as $country) {
           $out .= '<li><a href="'.$page->url.'country/'.$country->name.'">'.$country->title.'</a></li>';
          }
        $out .= '</ul>';
        $out .= '</div>';
        $out .= '<div class=" dropdown btn-group">';
        $out .= '<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">'.__('Select a city').' <span class="caret"></span></button>';
        $out .= '<ul class="dropdown-menu" role="menu">';
          foreach($cities as $city) {
            $out .= '<li><a href="'.$page->url.'city/'.$city->name.'">'.$city->title.'</a></li>';
          }
        $out .= '</ul>';
        $out .= '</div>';
        $out .= '<span id="switchGallery" class="btn btn-primary">'.__('Change view').'</span>';
        $out .= '</div>';
        return $out;
      });
      echo $placesMenu;

    if ($type == 'all') { // Get all places from cache
      $cacheName = "cache__allPlacesGallery-".$user->language->name."-page".$pageNum;
      $cacheUpdateTemplate = $templates->get("name=place");
      $allPlacesGallery = $cache->get($cacheName, $cacheUpdateTemplate, function() use($selectedPlaces, $pagination) {
      $out = '<div id="galleryList" class="text-center">';
        $out .= '<div class="text-center">'.$pagination.'</div>';
        $out .= '<ul class="list-inline placesList">';
          foreach($selectedPlaces as $place) {
            $thumbImage = $place->photo->eq(0)->getCrop('thumbnail');
            $city = $place->parent->title;
            $country = $place->parent->parent->title;
            $out .= '<li><a href="'.$place->url.'"><img class="img-thumbnail" src="'.$thumbImage->url.'" alt="'.$place->title.'." data-toggle="tooltip" data-html="true" title="<h4><span>'.$place->mapIndex.'</span> - '.$place->title.'</h4> <h5>'.$city.','.$country.'</h5> <strong>Level '.$place->level.', '.$place->GC.' GC</strong>" data-placement="bottom" /></a></li>';
          }
        $out .= '</ul>';
        $out .= '<div class="text-center">'.$pagination.'</div>';
      $out .= '</div>';
      $out .= '<div id="detailedList" style="display: none;" class="row">';
        $out .= '<div class="text-center">'.$pagination.'</div>';
        $out .= '<table id="mapTable" class="table table-condensed table-hover">';
          $out .= '<thead>';
          $out .= '<tr>';
            $out .= '<th>'.__("Name").'</th>';
            $out .= '<th>'.__("Country").'</th>';
            $out .= '<th>'.__("City").'</th>';
            $out .= '<th>'.__("GC").'</th>';
            $out .= '<th>'.__("Level").'</th>';
          $out .= '</tr>';
          $out .= '</thead>';
          $out .= '<tbody>';
            foreach ($selectedPlaces as $place) {
              $out .= '<tr>';
              $out .= '<td>';
              $out .= $place->title;
              $out .= '<img src="'.$place->photo->eq(0)->getCrop('mini')->url.'"  alt="'.$place->title.'." />';
              $out .= '</td>';
              $out .= '<td>'.$place->country->title.'</td>';
              $out .= '<td>'.$place->city->title.'</td>';
              $out .= '<td>'.$place->GC.'</td>';
              $out .= '<td>'.$place->level.'<td>';
              $out .= '</tr>';
            }
          $out .= '</tbody>';
        $out .= '</table>';
        $out .= '<div class="text-center">'.$pagination.'</div>';
      $out .= '</div>';
      return $out;
      });
      echo $allPlacesGallery;
    } else { // Load limited gallery
      $out = '<div id="galleryList" class="text-center">';
        $out .= '<div class="text-center">'.$pagination.'</div>';
        $out .= '<ul class="list-inline placesList">';
          foreach($selectedPlaces as $place) {
            $thumbImage = $place->photo->eq(0)->getCrop('thumbnail');
            $city = $place->parent->title;
            $country = $place->parent->parent->title;
            $out .= '<li><a href="'.$place->url.'"><img class="img-thumbnail" src="'.$thumbImage->url.'" alt="'.$place->title.'." data-toggle="tooltip" data-html="true" title="<h4><span>'.$place->mapIndex.'</span> - '.$place->title.'</h4> <h5>'.$city.','.$country.'</h5> <strong>Level '.$place->level.', '.$place->GC.' GC</strong>" data-placement="bottom" /></a></li>';
          }
        $out .= '</ul>';
        $out .= '<div class="text-center">'.$pagination.'</div>';
      $out .= '</div>';
      $out .= '<div id="detailedList" style="display: none;" class="row">';
        $out .= '<div class="text-center">'.$pagination.'</div>';
        $out .= '<table id="mapTable" class="table table-condensed table-hover">';
          $out .= '<thead>';
          $out .= '<tr>';
            $out .= '<th>'.__("Name").'</th>';
            $out .= '<th>'.__("Country").'</th>';
            $out .= '<th>'.__("City").'</th>';
            $out .= '<th>'.__("GC").'</th>';
            $out .= '<th>'.__("Level").'</th>';
          $out .= '</tr>';
          $out .= '</thead>';
          $out .= '<tbody>';
            foreach ($selectedPlaces as $place) {
              $out .= '<tr>';
              $out .= '<td>';
              $out .= $place->title;
              $out .= '<img src="'.$place->photo->eq(0)->getCrop('mini')->url.'"  alt="'.$place->title.'." />';
              $out .= '</td>';
              $out .= '<td>'.$place->country->title.'</td>';
              $out .= '<td>'.$place->city->title.'</td>';
              $out .= '<td>'.$place->GC.'</td>';
              $out .= '<td>'.$place->level.'<td>';
              $out .= '</tr>';
            }
          $out .= '</tbody>';
        $out .= '</table>';
        $out .= '<div class="text-center">'.$pagination.'</div>';
      $out .= '</div>';
      echo $out;
    }

    if ($user->hasRole('player')) {
      echo '<a class="btn btn-block btn-primary" href="'.$pages->get('/shop_generator')->url.$player->id.'">'.__("Go to the marketplace").'</a>';
    }
  ?>
</div>

<?php 
  include("./foot.inc");
?>
