<?php 
/* Place template */

if ( $page->template != 'list-all' ) { // Single place detailed view
  include("./head.inc"); 
}

if ($page->name != 'places') { // Single place view
  //$thumbImage = $page->photo->eq(0)->getThumb('thumbnail');
  $imageHeight = $page->photo->eq(0)->height;
  $imageWidth = $page->photo->eq(0)->width;
  if ($imageWidth > $imageHeight) { // Landscape
    $thumbImage = $page->photo->eq(0)->size(0,200)->url;
  } else { // Protrait
    $thumbImage = $page->photo->eq(0)->size(200,0)->url;
  }
  $city = $page->parent;
  $country = $page->parent->parent;
  $owners = $pages->find("template=player,places=$page->id");
?>
  <table class="table">
    <tr>
      <td rowspan="2" class="col-sm-2">
        <img class="img-thumbnail" ng-src="<?php echo $thumbImage; ?>" alt="Photo" />
      </td>
      <td class="col-sm-8">
        <h1><?php echo $page->title; ?></h1><?php echo "<h2><a href='places/?type=city&name={$city->name}'>{$city->title}</a>, <a href='places/?type=country&name={$country->name}'>{$country->title}</a></h2>"; ?>
      </td>
      <td class="col-sm-2">
        <h1><img style="float: left;" ng-src="<?php  echo $config->urls->templates?>img/gold.png" alt="Value" width="50" height="50" /> <span class="gcbtn btn-default btn-lg"><?php echo $page->GC; ?></span></h1>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="col-sm-10">
        <p class="lead"><?php echo $page->summary; ?> <span class="btn btn-info"><a href="<?php echo $page->link; ?>">[En savoir plus sur ce lieu]</a></span></p>
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
            echo "<small><span class='glyphicon glyphicon-user'></span> Ce lieu est libéré par {$totalOwners} joueur(s) : ";
            foreach ($owners as $owner) {
              if ($i++ === $totalOwners) {
                echo "{$owner->title} [{$owner->team}]";
              } else {
                echo "{$owner->title} [{$owner->team}], ";
                $i++;
              }
            }
          echo "</small>";
          ?>
      </td>
    </tr>
  </table>
<?php
} else { // All places view, Country view, City view
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
  $totalCount = $pages->find("template=place, name!=places")->count();
  // Get all cities having places
  $cities = $pages->find("template=city, children.count>0, sort=title");
  // Get all countries having places
    $countries = $pages->find("template=country, children.count>0,sort=title");

  if (!$type) {
    $type = 'All places';
    $name = $totalCount.' lieux dans '.$cities->count().' villes, dans '.$countries->count().' pays différents.';
  }
  ?>
  <div class="row" ng-controller="placesCtrl" ng-init="loadPlaces('<?php echo $pageNum.'\',\''.$angularParam; ?>')">
    <div class="text-center">
      <h2><a href="map/">Voir la carte du monde Planet Alert</a></h2>
      <?php
        if ($formattedName) {
          echo "<h2>Lieux dans : {$formattedName} ({$selectedPlaces->count()})</h2>";
        } else {
          echo "<h2>Tous les lieux ({$totalCount})</h2>";
        }
      ?>
      <a class="btn btn-info" href="<?php echo $page->url; ?>">Voir TOUS les lieux</a>
      <div class="dropdown btn-group">
        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">Choisir un pays <span class="caret"></span></button>
        <ul class="dropdown-menu" role="menu">
          <?php
            foreach($countries as $country) {
             echo "<li><a href='{$page->url}?type=country&name={$country->name}'>{$country->title}</a></li>";
            }
          ?>
        </ul>
      </div>
      <div class=" dropdown btn-group">
        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">Choisir une ville <span class="caret"></span></button>
        <ul class="dropdown-menu" role="menu">
          <?php
            foreach($cities as $city) {
              echo "<li><a href='{$page->url}?type=city&name={$city->name}'>{$city->title}</a></li>";
            }
          ?>
        </ul>
      </div>
      <span class="btn btn-primary" ng-click="thumbView = !thumbView" ng-show="thumbView">Voir la liste détaillée</span>
      <span class="btn btn-primary" ng-click="thumbView = !thumbView" ng-hide="thumbView">Retour aux photos</span>
    </div>

  <div ng-show="thumbView" class="text-center">
    <div class="text-center"><?php echo $pagination; ?></div>
    <ul class="list-inline placesList">
      <?php
        foreach($selectedPlaces as $place) {
          $thumbImage = $place->photo->eq(0)->getThumb('thumbnail');
          $city = $place->parent->title;
          $country = $place->parent->parent->title;
          echo "<li><a href='{$place->url}' title=''><img class='img-thumbnail' ng-src='{$thumbImage}' alt='' tooltip-html-unsafe='<h4>$place->title</h4> <h5>{$city},{$country}</h5> <strong>Coût: {$place->GC} or, Niveau {$place->level}</strong>' tooltip-placement='bottom' /></a></li>";
        }
      ?>
    </ul>
    <div class="text-center"><?php echo $pagination; ?></div>
  </div>

  <div id="" ng-hide="thumbView" class="row">
    <div class="text-center"><?php echo $pagination; ?></div>
    <table class="table table-condensed table-hover">
      <tr>
        <th ng-click="predicate = 'name'; reverse=!reverse">Nom</th>
        <th ng-click="predicate = 'country.name'; reverse=!reverse">Pays</th>
        <th ng-click="predicate = 'city.name'; reverse=!reverse">Ville</th>
        <th ng-click="predicate = 'GC'; reverse=!reverse">Or</th>
        <th ng-click="predicate = 'level'; reverse=!reverse">Niveau</th>
        <th ng-click="predicate = 'maxOwners'; reverse=!reverse"># de 'libérateurs'</th>
      </tr>
      <tr ng-repeat="place in places | orderBy:predicate:reverse | filter:search">
        <td>
          {{place.title | filterHtmlChars}}
          <img ng-repeat="photo in place.photo | limitTo:1" ng-src="site/assets/files/{{place.id}}/mini_{{photo.basename}}" />
        </td>
        <td>{{place.country.title}}</td>
        <td>{{place.city.title}}</td>
        <td>{{place.GC}}</td>
        <td>{{place.level}}</td>
        <td>{{place.maxOwners}}</td>
      </tr>
    </table>
    <div class="text-center"><?php echo $pagination; ?></div>
  </div>
</div>
<?php
}

if ( $page->template != 'list-all' ) { // Single place detailed view
  include("./foot.inc"); 
}

