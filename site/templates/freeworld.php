<?php
  include("./head.inc"); 

  $allPlayers = $pages->find("template='player', playerTeam=$input->urlSegment1, sort='group'");
  $rank = $allPlayers->first()->rank->name;
  $team = $allPlayers->first->playerTeam;
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

  displayScores($team);
  $allElements = teamFreeworld($team);
  $nbCompleted = $allElements->find("completed=1")->count();
?>
  <!-- <a class="pdfLink btn btn-info" href="<?php echo $page->url.$input->urlSegment1; ?>/places?pages2pdf=1">Get PDF</a> -->
  <h3 class="text-center"><span class="label label-default"><?php echo $nbCompleted.' completed (out of '.$allElements->count().' '.$title; ?>)</span></h3>
  <h4 class="text-center">Completing team rate : <?php echo $teamRate; ?></h4> <!-- (20% of team members) !-->

  <table id="freeWorld" class="table table-condensed table-hover">
    <thead>
    <tr>
      <th>Level</th>
      <th>Place / People</th>
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
