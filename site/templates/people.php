<?php
  include("./head.inc");

  $people = $pages->find("template=people, name!='people', sort=title");
  $totalPeopleCount = count($people);
  $out = '';

?>
<div class="row">
  <p class="label label-success">Total people : <?php echo $totalPeopleCount; ?></p>
  <?php
    $out .= '<table id="peopleTable" class="table table-hover table-condensed">';
    $out .= '<thead>';
      $out .= '<tr>';
      $out .= '<th colspan="2">Name</th>';
      $out .= '<th>Level</th>';
      $out .= '<th>GC</th>';
      $out .= '<th>Nationality</th>';
      $out .= '<th>Summary</th>';
      $out .= '</tr>';
    $out .= '</thead>';
    $out .= '<tbody>';
    foreach($people as $p) {
      $photo = $p->photo->eq(0)->getThumb("thumbnail");
      $out .= '<tr>';
      $out .= '<td><img src="'.$photo.'" alt="photo" /></td>';
      $out .= '<td>'.$p->title;
      if ($user->isSuperuser()) {
        $out .= '<a class="pdfLink btn btn-info" href="'. $page->url.'?pages2pdf=1&id='.$p->id.'">Get PDF</a>';
      }
      $out .= '</td>';
      $out .= '<td>'.$p->level.'</td>';
      $out .= '<td>'.$p->GC.'</td>';
      $out .= '<td>'.$p->nationality.'</td>';
      $out .= '<td>'.$p->summary;
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
