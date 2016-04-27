<?php 

$logo = $pages->get('/')->photo->eq(0)->getThumb('thumbnail');
$monsterId = $input->get("id");
$m = $pages->get("id=$monsterId");
if ($m->image) {
  $options = array(
    'cropping' => false,
    'upscaling' => false
  );
  $mini = "<img style='float: right;' alt=\"image\" src='".$m->image->size('150', '60', $options)->url."' />";
} else {
  $mini = '';
}

$exData = $m->exData;
$allLines = preg_split('/$\r|\n/', $exData);
shuffle($allLines);
$listWords = [];
if ($m->type->name == 'translate') {
  foreach($allLines as $l) {
    list($left, $right) = preg_split('/,/', $l);
    $words = explode('|', $right);
    if ($words[0] != '') {
      array_push($listWords, $words[0]);
    }
  }
} else { // Quiz type
  foreach($allLines as $l) {
    list($left, $right) = preg_split('/::/', $l);
    $left = str_replace("(separated by 1 space)", " ", $left);
    if ($left != '') {
      array_push($listWords, $left);
    }
  }
}

$out = '';
$out .= '<img style="float: left;" src="'.$logo.'" width="45" />';
$out .= $mini;
$out .= '<h2 style="text-align: center;">Monster Fight vs '.$m->title.'</h2>';
$out .= '<h5 class="text-left;">Name (Class) : _______________________________________ &nbsp;&nbsp;&nbsp; Date : ___________________________________</h5>';

if (count($allLines) > 24) {
  $nb = 24;
} else {
  $nb = count($allLines);
}

$out .= '<table class="table">';
for($i=0; $i<=$nb; $i++) {
  if (trim($listWords[$i]) != '') {
    $lenght = strlen($listWords[$i]);
    if ($lenght < 70) {
      $fontSize = '14pt';
      $wrap = 'white-space:nowrap;';
    } else {
      $fontSize = '12pt';
      $wrap = 'white-space:normal;';
    }

    $out .= '<tr>';
    $j = $i+1;
    $out .= '<td class="text-center" style="font-size:14pt;">'.$j.'</td>';
    $out .= '<td style="'.$wrap.' padding: 4pt;">';
    $out .= '<p style="font-size: '.$fontSize.';">'.$listWords[$i].'</p>';
    $out .= '</td>';
    $out .= '<td style="width:60%">';
    $out .= '</td>';
    $out .= '</tr>';
  }
}
$out .= '</table>';
$out .= '<p class="text-center" style="margin: 0pt;">';
$out .= '<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ⇒ Successful - Well done - Failed - Disastrous </span>';
if (count($allLines) > 24) {
  $out .= '&nbsp;&nbsp;&nbsp;[Selection of 25 words out of '.count($allLines).']';
}
$out .= '</p>';

if (count($allLines) < 11) {
  $out .= '<br />'.$out;
}

echo $out;

?>

