<?php 

$logo = '<img style="float: left;" src="http://download.tuxfamily.org/planetalert/logo.png" width="100" height="100" /> ';

$weapons = $pages->find("template='equipment', category='weapons', sort='level'");
$protections = $pages->find("template='equipment',category='protections', sort='level'");
$items = $pages->find("template='item', sort='level'");
$groupItems = $pages->find("template='item', parent.name='group-items', sort='level'");

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
      $thumbImage = $item->image;
      $out .= '<table>';
      for ($i=0; $i<8; $i++) {
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
      /*
      $out .= '<tr>';
      $out .= '<td><img style="" src="'.$logo->url.'" /></td>';
      $out .= '<td colspan="2"><h1>'.$item->title.'</h1></td>';
      $out .= '<td style="border: 0">&nbsp;</td>';
      $out .= '</tr>';
      $out .= '<tr>';
      $out .= '<td>&nbsp;</td>';
      $out .= '<td colspan="2" rowspan="2" style=""><h2>'.$item->summary.'</h2></td>';
      $out .= '<td style="border: 0"><img style="" src="'.$thumbImage.'" /></td>';
      $out .= '</tr>';
       */
/* $logo = '<img style="float: left;" src="http://download.tuxfamily.org/planetalert/logo.png" width="100" height="100" /> '; */
      /* $image = $pages->get("/shop/potions")->photo->eq(0)->url; */
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
    for ($i=0; $i<3; $i++) {
      $out .= '<table class="">';
      $out .= '<tr>';
      $out .= '<th width="60%">';
      $out .= '<h2>Memory Potion : Text n°'.$text->index.'</h2>';
      $out .= '<p>Player : _______________________________ (___________) </p>';
      $out .= '</th>';
      $out .= '<th>';
      $out .= '<img style="float: right;" src="'.$logo->url.'" />';
      $out .= '</th>';
      $out .= '</tr>';
      $out .= '<tr>';
      $out .= '<td>';
      $out .= '<img style="float: left;" src="'.$iconfig->urls->templates.'img/flag_en.png" alt="English" />';
      $out .= '<h2>'.$text->title.'</h2>';
      $out .= nl2br($text->summary);
      $out .= '</td>';
      $out .= '<td>';
      $out .= '<p><img style="float: right;" src="'.$iconfig->urls->templates.'img/flag_fr.png" alt="French" /></p>';
      $out .= nl2br($text->frenchSummary);
      $out .= '</td>';
      $out .= '</tr>';
      $out .= '<tr>';
      $out .= '<td colspan="2"><p>Given date : ______________________________  Due date : ______________________________ </p></td>';
      $out .= '</tr>';
      $out .= '</table>';
      $out .= '<br /><br />';
    }
  } else {
    $out .= '<img style="float: left;" src="'.$logo->url.'" />';
    $out .= '<img style="float: right;" src="'.$logo->url.'" />';
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

