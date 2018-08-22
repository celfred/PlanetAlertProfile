<?php 

$logo = 'http://download.tuxfamily.org/planetalert/logo.png';

$weapons = $pages->find("template='equipment', category='weapons', sort='level'");
$protections = $pages->find("template='equipment',category='protections', sort='level'");
if ($user->isSuperuser()) {
  $items = $pages->find("template=item, sort=level");
} else {
  $items = $pages->find("template=item, teacher=$user, sort=level");
}
$groupItems = $pages->find("template=item, parent.name=group-items, sort=level");

$out = '';

if ($input->urlSegment1 && $input->urlSegment1 == 'pictures') {
  switch($input->urlSegment2) {
  case 'weapons' : $items = $weapons; break;
    case 'protections' : $items = $protections; break;
    case 'items' : $items = $items; break;
    case 'group-items' : $items = $groupItems; break;
    default : $items = $weapons; break;
  }
  $maxIndex = count($items);
  $index = 0;
  foreach($items as $item) {
    if ($input->urlSegment2 != 'items') {
      $thumbImage = $item->image->getCrop('small');
      $out .= '<table>';
      for ($i=0; $i<13; $i++) {
        $out .= '<tr>';
        for ($j=0; $j<5; $j++) {
          $out .= '<td style="border: 5px solid #000;"><img style="float: left;" src="'.$thumbImage->url.'" /></td>';
        }
        $out .= '</tr>';
      }
      $out .= '</table>';

      $index++;
      if ($index !== $maxIndex) {
        $out .= '<pagebreak />';
      }
    }
  }
  if ($input->urlSegment2 === 'items') {
      $image1 = "http://download.tuxfamily.org/planetalert/potions-01.png";
      $image2 = "http://download.tuxfamily.org/planetalert/potions-02.png";
      $image3 = "http://download.tuxfamily.org/planetalert/potions-03.png";
      $out .= '<img src="'.$image1.'" />';
      $out .= '<pagebreak />';
      $out .= '<img src="'.$image2.'" />';
      $out .= '<pagebreak />';
      $out .= '<img src="'.$image3.'" />';
    }
} else {
  if ($input->urlSegment1 && $input->urlSegment1 == 'memory-potion') {
    if ($input->urlSegment2) {
      $textId = $input->urlSegment2;
      $text = $pages->get("id=$textId");
    }
    preg_match_all("/(\n)/", $text->summary, $matches);
    $total_lines = count($matches[0]) + 1;
    if ($total_lines > 20) {
      $nbText = 1;
    } else if ($total_lines > 10 && $total_lines <=20){
      $nbText = 2;
    } else {
      $nbText = 3;
    }
    for ($i=0; $i<$nbText; $i++) {
      $out .= '<table class="">';
      $out .= '<tr>';
      $out .= '<th width="50%">';
      $out .= '<h2>Memory Potion : Text n°'.$text->index.'</h2>';
      $out .= '<br />';
      $out .= '<p>Player : _______________________________ (___________) </p>';
      $out .= '</th>';
      $out .= '<th>';
      $out .= '<img style="" src="'.$logo.'" width="75" height="75" />';
      $out .= '</th>';
      $out .= '</tr>';
      $out .= '<tr>';
      if (strlen($text->frenchSummary) > 0) {
        $out .= '<td>';
      } else {
        $out .= '<td colspan="2">';
      }
      $out .= '<img style="" src="'.$config->urls->templates.'img/flag_en.png" alt="English" />';
      $out .= '<h2>'.$text->title.'</h2>';
      $out .= '<p style="font-size: 16px;">'.nl2br($text->summary).'</p>';
      $out .= '</td>';
      if (strlen($text->frenchSummary) > 0) {
        $out .= '<td>';
        $out .= '<p><img style="" src="'.$config->urls->templates.'img/flag_fr.png" alt="French" /></p>';
        $out .= '<p style="font-size:16px;">'.nl2br($text->frenchSummary).'</p>';
        $out .= '</td>';
      }
      $out .= '</tr>';
      $out .= '<tr>';
      $out .= '<td colspan="2"><p>Given date : ______________________________  Due date : ______________________________ </p></td>';
      $out .= '</tr>';
      $out .= '</table>';
      $out .= '<br /><br />';
    }
  } else {
    $out .= '<img style="float: left;" src="'.$logo.'" width="100" height="100" />';
    $out .= '<img style="float: right;" src="'.$logo.'" width="100" height="100" />';
    $out .= '<h1 style="text-align: center; text-decoration : underline;">The Shop</h1>';

    $out .= '<table class="">';

    $out .= '<tr>';
    $out .= '<th colspan="7"><h2>Weapons ('. $weapons->count.' items)</h2></th>';
    $out .= '</tr>';
    $out .= '<tr>';
    $out .= '<th>Minimum level</th>';
    $out .= '<th>GC</th>';
    $out .= '<th>XP</th>';
    $out .= '<th>Name</th>';
    $out .= '<th>&nbsp;</th>';
    $out .= '<th colspan="2">Summary</th>';
    $out .= '</tr>';
    foreach($weapons as $weapon) {
      $thumbImage = $weapon->image->getCrop('mini');
      $out .= '<tr>';
      $out .= '<td>'.$weapon->level.'</td>';
      $out .= '<td>'.$weapon->GC.'</td>';
      $out .= '<td>+'.$weapon->XP.'</td>';
      $out .= '<td>'.$weapon->title.'</td>';
      $out .= '<td><img src="'.$thumbImage->url.'" /></td>';
      $out .= '<td colspan="2">'.$weapon->summary.'</td>';
      $out .= '</tr>';
    }

    $out .= '<tr>';
    $out .= '<th colspan="7"><h2>Protections ('. $protections->count.' items)</h2></th>';
    $out .= '</tr>';
    $out .= '<tr>';
    $out .= '<th>Minimum level</th>';
    $out .= '<th>GC</th>';
    $out .= '<th>HP</th>';
    $out .= '<th>Name</th>';
    $out .= '<th>&nbsp;</th>';
    $out .= '<th colspan="2">Summary</th>';
    $out .= '</tr>';
    foreach($protections as $protection) {
      $thumbImage = $protection->image->getCrop('mini');
      $out .= '<tr>';
      $out .= '<td>'.$protection->level.'</td>';
      $out .= '<td>'.$protection->GC.'</td>';
      $out .= '<td>+'.$protection->HP.'</td>';
      $out .= '<td>'.$protection->title.'</td>';
      $out .= '<td><img src="'.$thumbImage.'" /></td>';
      $out .= '<td colspan="2">'.$protection->summary.'</td>';
      $out .= '</tr>';
    }

    $out .= '<tr>';
    $out .= '<th colspan="7"><h2>Potions ('. $items->count .' items)</th>';
    $out .= '</tr>';
    $out .= '<tr>';
    $out .= '<th>Minimum level</th>';
    $out .= '<th>GC</th>';
    $out .= '<th>HP</th>';
    $out .= '<th>XP</th>';
    $out .= '<th>Name</th>';
    $out .= '<th>&nbsp;</th>';
    $out .= '<th>Summary</th>';
    $out .= '</tr>';
    foreach($items as $item) {
      if ($item->image) {
        $thumbImage = $item->image->getCrop('mini');
      }
      $out .= '<tr>';
      $out .= '<td>'.$item->level.'</td>';
      $out .= '<td>'.$item->GC.'</td>';
      if ($item->HP != 0) {
        $out .= '<td>+'.$item->HP.'</td>';
      } else {
        $out .= '<td>-</td>';
      }
      if ($item->XP != 0) {
        $out .= '<td>+'.$item->XP.'</td>';
      } else {
        $out .= '<td>-</td>';
      }
      $out .= '<td>'.$item->title.'</td>';
      $out .= '<td><img src="'.$thumbImage->url.'" /></td>';
      $out .= '<td>'.$item->summary.'</td>';
      $out .= '</tr>';
    }

    $out .= '</table>';
  }
}

echo $out;


?>

