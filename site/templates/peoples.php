<?php namespace ProcessWire;
  include("./head.inc");

  $allPeople = $pages->find("template=people, sort=title");
  $totalPeopleCount = count($allPeople);
  $out = '';

  if ($user->hasRole('player')) { // Show player's mini-profile
    echo '<div class="row well text-center">';
      echo miniProfile($player, 'people');
    echo '</div>';
  }

?>
<div class="row">
  <p class="label label-success"><?php echo __('Total people'); ?> : <?php echo $totalPeopleCount; ?></p>
  <?php
    $out .= '<table id="peopleTable" class="table table-hover table-condensed">';
    $out .= '<thead>';
      $out .= '<tr>';
      $out .= '<th colspan="2">'.__("Name").'</th>';
      $out .= '<th>'.__("Level").'</th>';
      $out .= '<th>'.__("GC").'</th>';
      $out .= '<th>'.__("Nationality").'</th>';
      $out .= '<th>'.__("Summary").'</th>';
      $out .= '</tr>';
    $out .= '</thead>';
    $out .= '<tbody>';
    foreach($allPeople as $p) {
      $photo = $p->photo->eq(0)->getCrop("thumbnail");
      $out .= '<tr>';
      $out .= '<td><img src="'.$photo->url.'" alt="'.$p->title.'." /></td>';
      $out .= '<td>'.$p->title;
      if ($user->hasRole('teacher') || $user->isSuperuser()) {
        $out .= '<a class="pdfLink btn btn-info" href="'. $p->url.'?pages2pdf=1&id='.$p->id.'">Get PDF</a>';
      }
      $out .= '</td>';
      $out .= '<td>'.$p->level.'</td>';
      $out .= '<td>'.$p->GC.'</td>';
      $out .= '<td>'.$p->nationality.'</td>';
      $out .= '<td class="text-justify">'.$p->summary;
      $out .= '</td>';
      $out .= '</tr>';
    }
    $out .= '</tbody>';
    $out .= '</table>';

    echo $out;
  ?>
</div>

<?php
  include("./foot.inc");
?>
