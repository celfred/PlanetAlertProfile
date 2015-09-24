<?php
  include("./head.inc"); 

  $allPlayers = $pages->find("template='player', playerTeam=$input->urlSegment1, sort='group'");
  $team = $allPlayers->first->team->title;
  $totalPlaces = $pages->find("template='place', name!='places', sort=level");
  $globalScore = globalScore($allPlayers, $totalPlaces);
  $teamScore = $globalScore[0];
  $teamOwners = $globalScore[1];
  $totalOwners = $globalScore[2];
  
  // Set team stats
  foreach($totalPlaces as $place) {
    $placeId = $place->id;
    $place->teamOwners = $allPlayers->find("places=$placeId");
    $place->rate = (100*$place->teamOwners->count())/$place->maxOwners;
    $place->rateWidth = ($place->rate*150)/100;
    if ($place->rate == 100) {
      $place->cssClass = 'completed';
    } else {
      $place->cssClass = '';
    }
  }
  $totalPlaces->sort("-rate");
  $completed = $totalPlaces->find("rate=100")->count();

  // Nav tabs
  include("./tabList.inc"); 

  echo '<p class="text-center lead well"><strong title="'.$teamOwners.'/'.$totalOwners.'">'.$team.' - Free world : '.$teamScore.'%</strong></p>';
?>
  <a class="pdfLink btn btn-info" href="<?php echo $page->url.$input->urlSegment1; ?>/places?pages2pdf=1">Get PDF</a>
  <h3 class="text-center"><span class="label label-default"><?php echo $completed; ?> completed out of <?php echo $totalPlaces->count(); ?> places (<?php echo ($totalPlaces->count())-$completed; ?> left)</span></h3>

  <table id="freeWorld" class="table table-condensed table-hover">
    <thead>
    <tr>
      <th>Level</th>
      <th>Place</th>
      <th>City</th>
      <th>Country</th>
      <th>GC</th>
      <th># of owners</th>
      <th>Freedom rate</th>
    </tr>
    </thead>
    <tbody>
    <?php
      $out = '';
      foreach($totalPlaces as $place) {
        //$freedomRate = placeFreedomRate($place, $allPlayers);
        /*
        $rate = 0;
        $maxOwners = $place->maxOwners;
        $placeId = $place->id;
        $teamOwners = $allPlayers->find("places=$placeId")->count();
        $rate = (100*$teamOwners)/$maxOwners;
        if ($rate != 0) {
          $rateWidth = $rate+90;
        } else {
          $rateWidth = 0;
        }
         */
        $out .= '<tr class="'.$place->cssClass.'">';
        $out .= '<td>'.$place->level.'</td>';
        $out .= '<td>'.$place->title.'</td>';
        $out .= '<td>'.$place->city->title.'</td>';
        $out .= '<td>'.$place->country->title.'</td>';
        $out .= '<td>'.$place->GC.'</td>';
        $tooltip = '<ul>';
        foreach ($place->teamOwners as $owner) {
          $tooltip .= '<li>'.$owner->title.'</li>';
        }
        $tooltip .= '</ul>';
        $out .= '<td>'.$place->teamOwners->count().'/'.$place->maxOwners.'&nbsp; <span data-toggle="tooltip" data-html="true" title="'.$tooltip.'" data-container="body" class="glyphicon glyphicon-info-sign"></span></td>';
        $out .= '<td data-order="'.$place->rate.'">';
        $out .= '<div class="progress progress-striped  progress-mini" data-toggle="tooltip" title="'.$place->teamOwners->count().'/'.$place->maxOwners.'">';
        $out .= ' <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100" style="width: '.($place->rateWidth).'px">';
        $out .= ' </div>';
        $out .= ' </div>';
        $out .= ' </td>';
        $out .= '</tr>';
      }
      echo $out;
    ?>
    </tbody>
  </table>

<?php
  include("./foot.inc"); 
?>
