<?php Namespace ProcessWire;
  include("./head.inc"); 

  $team = $pages->get("template=team, name=$input->urlSegment1");
  $allPlayers = $pages->find("template=player, team=$team")->sort("group.name");
  /* $allPlayers = $pages->find("template=player, team=$team, sort=group.name"); */
  $rank = $allPlayers->first()->rank->name;
  if ($rank == '4emes' || $rank == '3emes') {
    $totalElements = $pages->find("template=place|people, name!=places|people, sort=level");
    $title = 'places or people';
  } else {
    $totalElements = $pages->find("template=place, name!=places, sort=level");
    $title = 'places';
  }
  
  // Set team stats
  $teamRate = round(($allPlayers->count()*20)/100);

  // Nav tabs
  include("./tabList.inc"); 

  showScores($team);
  $allElements = teamFreeworld($team);
  $nbCompleted = $allElements->find("completed=1")->count();
?>
  <!-- <a class="pdfLink btn btn-info" href="<?php echo $page->url.$input->urlSegment1; ?>/places?pages2pdf=1">Get PDF</a> -->
  <h4 class="text-center"><span class="label label-info"><?php echo $nbCompleted.'/'.$allElements->count().'</span> completed '.$title; ?> (team rate : <?php echo $teamRate; ?> <span data-toggle="tooltip" data-html="true" title="# of players required to complete a place and increase %." class="glyphicon glyphicon-question-sign"></span>)</h4>

  <table id="freeWorld" class="table table-condensed table-hover">
    <thead>
    <tr>
      <th>Level</th>
      <th>Type</th>
      <th>Name</th>
      <th>City / Nationality</th>
      <th>Country</th>
      <th>GC</th>
    </tr>
    </thead>
    <tbody>
    <?php
      $out = '';
      foreach($allElements as $el) {
        $out .= '<tr class="'.$el->cssClass.'">';
        $out .= '<td>'.$el->level.'</td>';
        if ($el->template == "place") { $type = 'Place'; }
        if ($el->template == "people") { $type = 'People'; }
        $out .= '<td>'.$type.'</td>';
        $out .= '<td>'.$el->title.'</td>';
        if ($el->template == 'place') {
          $out .= '<td>'.$el->city->title.'</td>';
        }
        if ($el->template == 'people') {
          $out .= '<td>'.$el->nationality.'</td>';
        }
        $out .= '<td>'.$el->country->title.'</td>';
        $out .= '<td>'.$el->GC.'</td>';
        $tooltip = '<ul>';
        foreach ($el->teamOwners as $owner) {
          $tooltip .= '<li>'.$owner->title.'</li>';
        }
        $tooltip .= '</ul>';
        $out .= '<td>'.$el->teamOwners->count().'&nbsp; <span data-toggle="tooltip" data-html="true" title="'.$tooltip.'" data-container="body" class="glyphicon glyphicon-info-sign"></span></td>';
        $out .= '</tr>';
      }
      echo $out;
    ?>
    </tbody>
  </table>

<?php
  include("./foot.inc"); 
?>
