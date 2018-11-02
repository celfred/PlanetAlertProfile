<?php
  include("./head.inc");

  $places = $pages->find("template=place, map.lat!='', name!=places, sort=title");
  $map = $modules->get('MarkupLeafletMap');
  $totalPlacesCount = count($places);
?>
<div class="row">
  <div class="col-sm-12 text-center">
    <h4><?php echo $page->summary . ' ('.$totalPlacesCount.')'; ?></h4>
    <h4>
      See the map with numbers : <a href="http://download.tuxfamily.org/planetalert/map/worldMap-numbers.png" target="_blank" data-toggle="tooltip" title="Write the corresponding numbers on your places in your copybook.">Low resolution (.png)</a> / <a href="http://download.tuxfamily.org/planetalert/map/worldMap.svg" target="_blank" data-toggle="tooltip" title="Write the corresponding numbers on your places in your copybook.">High resolution (.svg)</a>
    </h4>
  </div>
  <div class="col-sm-12">
  <?php
    echo $map->getLeafletMapHeaderLines();
    $options = array('height' => '600px', 'markerIcon' => 'flag', 'markerColour' => 'red');
    echo $map->render($places, 'map', $options); 
  ?>
  </div>
</div>

<?php
  include("./foot.inc");
?>
