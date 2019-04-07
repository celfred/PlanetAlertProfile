<?php namespace ProcessWire; /* All places template */
  include("./head.inc"); 

  if ($user->hasRole('player')) { // Show player's mini-profile
    echo '<div class="row well text-center">';
      echo miniProfile($player, 'people');
    echo '</div>';
  }

  // Get any limiting elements from urlSegments
  $country = $input->urlSegment1;
  // Set parent accordingly
  if ($input->urlSegment1) {
    $allPeople = $pages->find("template=people, country.name=$country, sort=title");
  } else {
    $allPeople = $pages->find("template=people, sort=title, limit=30");
  }
  $totalPeopleCount = count($allPeople);

  // Get selected places (or all places if $parent=/places)
  /* $selector = "template=place, name!=places, has_parent=$parent, sort=name, limit=30"; */
  /* $selectedPlaces = $pages->find($selector); */
  $pagination = $allPeople->renderPager(array(
    'nextItemLabel' => ">",
    'previousItemLabel' => "<",
    'listMarkup' => "<ul class='pagination pagination-sm'>{out}</ul>",
    'currentItemClass' => "active",
  ));
  $pageNum = $input->pageNum;
  
  // Count ALL people in the game (for information)
  $totalCount = $pages->count("template=people");

  $out = '<div class="row">';
  $out .= '<div class="text-center">';

    if ($country) {
      $selectedCountry = $pages->get("template=country, name=$country");
      $out .= '<h2>'.__('People from ').$selectedCountry->title.'</h2>';
    } else {
      $out .= '<h2>'.__('All people'). ' ('.$totalCount.')</h2>';
    }

    $peopleCountriesMenu = $cache->get("cache__peopleCountriesMenu-".$user->language->name, 2678400, function($page, $pages) {
      $allPlanetAlertPeople = $pages->find("template=people");
      $countries = new pageArray();
      foreach ($allPlanetAlertPeople as $p) {
        $countries->add($p->country);
      }
      $countries->sort("title");
      $out = '';
      $out .= '<a class="btn btn-info" href="'.$page->url.'">'.__('See ALL poeple').'</a>';
      $out .= '<div class="dropdown btn-group">';
      $out .= '<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">'.__('Select a country').' <span class="caret"></span></button>';
      $out .= '<ul class="dropdown-menu" role="menu">';
        foreach($countries as $country) {
         $out .= '<li><a href="'.$page->url.$country->name.'">'.$country->title.'</a></li>';
        }
      $out .= '</ul>';
      $out .= '</div>';
      $out .= '<span id="switchGallery" class="btn btn-primary">'.__('Change view').'</span>';
      $out .= '</div>';
      return $out;
    });
    $out .= $peopleCountriesMenu;

    if (!$country) { // Get all places from cache
      $cacheName = "cache__allPeoplesGallery-".$user->language->name."-page".$pageNum;
      $cacheUpdateTemplate = $templates->get("name=people");
      $allPeoplesGallery = $cache->get($cacheName, $cacheUpdateTemplate, function() use($allPeople, $pagination) {
        $out = '<div id="galleryList" class="text-center">';
          $out .= '<div class="text-center">'.$pagination.'</div>';
          $out .= '<ul class="list-inline placesList">';
            foreach($allPeople as $p) {
              $thumbImage = $p->photo->eq(0)->getCrop('thumbnail');
              $out .= '<li><a href="'.$p->url.'"><img class="img-thumbnail" src="'.$thumbImage->url.'" alt="" data-toggle="tooltip" data-html="true" title="<h4>'.$p->title.'</h4> <h5>'.__('Country').':'.$p->country->title.', '.__("Nationality").':'.$p->nationality.'</h5> <strong>'.__('Level').' '.$p->level.', '.$p->GC.' '.__('GC').'</strong>" data-placement="bottom" /></a></li>';
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
              $out .= '<th>'.__("Nationality").'</th>';
              $out .= '<th>'.__("GC").'</th>';
              $out .= '<th>'.__("Level").'</th>';
            $out .= '</tr>';
            $out .= '</thead>';
            $out .= '<tbody>';
              foreach ($allPeople as $p) {
                $out .= '<tr>';
                $out .= '<td>';
                $out .= $p->title;
                $out .= '<img src="'.$p->photo->eq(0)->getCrop('mini')->url.'"  alt="" />';
                $out .= '</td>';
                $out .= '<td>'.$p->country->title.'</td>';
                $out .= '<td>'.$p->nationality.'</td>';
                $out .= '<td>'.$p->GC.'</td>';
                $out .= '<td>'.$p->level.'<td>';
                $out .= '</tr>';
              }
            $out .= '</tbody>';
          $out .= '</table>';
          $out .= '<div class="text-center">'.$pagination.'</div>';
        $out .= '</div>';
        return $out;
      });
      $out .= $allPeoplesGallery;
    } else { // Load limited gallery
      $out .= '<div id="galleryList" class="text-center">';
        $out .= '<div class="text-center">'.$pagination.'</div>';
        $out .= '<ul class="list-inline placesList">';
          foreach($allPeople as $p) {
            $thumbImage = $p->photo->eq(0)->getCrop('thumbnail');
            $out .= '<li><a href="'.$p->url.'"><img class="img-thumbnail" src="'.$thumbImage->url.'" alt="" data-toggle="tooltip" data-html="true" title="<h4>'.$p->title.'</h4> <h5>'.$p->nationality.','.$p->country->title.'</h5> <strong>Level '.$p->level.', '.$p->GC.' GC</strong>" data-placement="bottom" /></a></li>';
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
            $out .= '<th>'.__("Nationality").'</th>';
            $out .= '<th>'.__("GC").'</th>';
            $out .= '<th>'.__("Level").'</th>';
          $out .= '</tr>';
          $out .= '</thead>';
          $out .= '<tbody>';
            foreach ($allPeople as $p) {
              $out .= '<tr>';
              $out .= '<td>';
              $out .= $p->title;
              $out .= '<img src="'.$p->photo->eq(0)->getCrop('mini')->url.'"  alt="" />';
              $out .= '</td>';
              $out .= '<td>'.$p->country->title.'</td>';
              $out .= '<td>'.$p->nationality.'</td>';
              $out .= '<td>'.$p->GC.'</td>';
              $out .= '<td>'.$p->level.'<td>';
              $out .= '</tr>';
            }
          $out .= '</tbody>';
        $out .= '</table>';
        $out .= '<div class="text-center">'.$pagination.'</div>';
      $out .= '</div>';
    }
  $out .= '</div>';

  echo $out;
  ?>

<?php 
  include("./foot.inc");
?>
