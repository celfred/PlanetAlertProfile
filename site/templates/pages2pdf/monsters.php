<?php 

$logo = $pages->get('/')->photo->eq(0)->getCrop('thumbnail');
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
        $left = str_replace("%fname%", __("Mike"), $left);
        $left = str_replace("%fnamef%", __("Sarah"), $left);
        $left = str_replace("%fnamem%", __("John"), $left);
        $left = str_replace("%name%", __("Simon Keats"), $left);
        $left = str_replace("%age%", "13", $left);
        $left = str_replace("%nationality%", __("American"), $left);
        array_push($listWords, $left);
      }
    }
    break;
  case 'jumble' :
    foreach($allLines as $l) {
      $l = preg_replace('/{\d+}/', "", $l);
      // Get rid of $...$
      $l = preg_replace('/\$.+?\$/', "", $l);
      // Replace %...% with data
      // TODO : Random data from a list
      $l = str_replace("%fname%", __("Mike"), $l);
      $l = str_replace("%fnamef%", __("Sarah"), $l);
      $l = str_replace("%fnamem%", __("John"), $l);
      $l = str_replace("%name%", __("Simon Keats"), $l);
      $l = str_replace("%age%", "13", $l);
      $l = str_replace("%nationality%", __("American"), $l);
      // Split chunks
      $allWords = explode('|', $l);
      // Jumble words
      shuffle($allWords);
      $mixedSentence = join(' / ', $allWords);
      array_push($listWords, $mixedSentence);
    }
    break;
  case 'image-map' :
    foreach($allLines as $l) {
      list($left, $right) = preg_split('/::/', $l);
      array_push($listWords, $left);
    }
    break;
  default :
    array_push($listWords, 'TODO');
}

$out = '';
$out .= '<img style="float: left;" src="'.$logo->url.'" width="45" />';
$out .= $mini;
$out .= '<h2 style="text-align: center;">'.__("Monster Fight vs").' '.$m->title.'</h2>';
$out .= '<h5 style="text-align: left;">'.__("Name (Class)").' : _______________________________________ &nbsp;&nbsp;&nbsp;';
$out .= __('Date').' : ___________________________________</h5>';
$out .= '<p style="text-align: left;">'.$m->instructions.'</p>';

if (count($allLines) > 24) {
  $nb = 24;
} else {
  $nb = count($allLines);
}

if ($m->type->name == "image-map") {
  if (count($allLines) > 15) { // Limit to 15
    $nb = 14;
  } else {
    $nb = count($allLines);
  }
  // Get image map
  if ($m->imageMap) {
    $options = array(
      'cropping' => false,
      'upscaling' => false
    );
    $imageMap = "<img alt=\"image\" src='".$m->imageMap->size('500', '350', $options)->url."' />";
    $out .= '<div style="text-align: center;">';
    $out .= $imageMap;
    $out .= '</div>';
  } else {
    $imageMap = '';
  }
}

$out .= '<table class="table">';
  for($i=0; $i<=$nb; $i++) {
    if (trim($listWords[$i]) != '') {
      $length = strlen($listWords[$i]);
      if ($length < 70) {
        $fontSize = '14pt';
        $wrap = 'white-space:nowrap;';
      } else {
        $fontSize = '12pt';
        $wrap = 'white-space:normal;';
      }
      $out .= '<tr>';
      $j = $i+1;
      if ($m->type->name != "image-map") {
        $out .= '<td class="text-center" style="font-size:14pt;">'.$j.'</td>';
      }
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
$out .= '<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ⇒ '.__("Successful Fight - Won fight - Lost fight - Disastrous Fight").'</span>';
if (count($allLines) > 24) {
  $out .= '<span style="font-size:8pt;">&nbsp;&nbsp;&nbsp;['.("Selection of 25 words out of").' '.count($allLines).']</span>';
}
$out .= '</p>';

if (count($allLines) < 11) {
  $out .= '<br />'.$out;
}

echo $out;

?>
