<?php 

$logo = $pages->get('/')->photo->eq(0)->getCrop('thumbnail');

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
      $image = $pages->get("/shop/potions")->photo->eq(0)->url;
      $out .= '<img src="'.$image.'" />';
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
    $thumbImage = $item->image->getCrop('mini');
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

echo $out;


?>

