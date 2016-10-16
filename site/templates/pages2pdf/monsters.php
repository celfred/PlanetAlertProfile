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
switch ($m->type->name) {
  case 'translate' :
    foreach($allLines as $l) {
      $l = preg_replace('/{\d+}/', "", $l);
      list($left, $right) = preg_split('/,/', $l);
      $words = explode('|', $right);
      if ($words[0] != '') {
        array_push($listWords, $words[0]);
      }
    }
    break;
  case 'quiz' :
    foreach($allLines as $l) {
      $l = preg_replace('/{\d+}/', "", $l);
      list($left, $right) = preg_split('/::/', $l);
      $left = str_replace("(separated by 1 space)", " ", $left);
      if ($left != '') {
        // Basic marker replacements
        // TODO : Add random choice from list
        $left = str_replace("%fname%", "Mike", $left);
        $left = str_replace("%fnamef%", "Sarah", $left);
        $left = str_replace("%fnamem%", "John", $left);
        $left = str_replace("%name%", "Simon Keats", $left);
        $left = str_replace("%age%", "13", $left);
        $left = str_replace("%nationality%", "American", $left);
        array_push($listWords, $left);
      }
    }
    break;
  case 'jumble' :
    foreach($allLines as $l) {
      $l = preg_replace('/{\d+}/', "", $l);
      $l = str_replace("%fname%", "Mike", $l);
      $l = str_replace("%fnamef%", "Sarah", $l);
      $l = str_replace("%fnamem%", "John", $l);
      $l = str_replace("%name%", "Simon Keats", $l);
      $l = str_replace("%age%", "13", $l);
      $l = str_replace("%nationality%", "American", $l);
      // Split chunks
      $allWords = explode('|', $l);
      // Jumble words
      shuffle($allWords);
      $mixedSentence = join(' / ', $allWords);
      array_push($listWords, $mixedSentence);
    }
    break;
  default :
    array_push($listWords, 'TODO');
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
    if ($length < 70) {
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
$out .= '<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ⇒ Sweeping victory - Won fight - Lost fight - Heavy defeat </span>';
if (count($allLines) > 24) {
  $out .= '<span style="font-size:8pt;">&nbsp;&nbsp;&nbsp;[Selection of 25 words out of '.count($allLines).']</span>';
}
$out .= '</p>';

if (count($allLines) < 11) {
  $out .= '<br />'.$out;
}

echo $out;

?>
