<?php namespace ProcessWire;
  include("./head.inc");
  $out = '';

  $out .= '<div class="text-center">';
  $out .= '<h3><span class="label label-primary">'.__("Import results from a SACoche CSV file")."</span></h3>";
  $out .= '<span class="label label-danger">'.__('Experimental - Use with caution').'</span>';
  $out .= '</div>';

  if ($input->post->events) { // Save form was submitted
    // Battle actions
    $battleVV = $pages->get("name=battle-vv");
    $battleV = $pages->get("name=battle-v");
    $battleR = $pages->get("name=battle-r");
    $battleRR = $pages->get("name=battle-rr");
    foreach ($input->post->events as $details=>$value) {
      list($playerId, $result, $itemIndex) = explode('-', $details, 3);
      switch($result) {
        case '1': $task = $battleRR; break;
        case '2': $task = $battleR; break;
        case '3': $task = $battleV; break;
        case '4': $task = $battleVV; break;
        default : $task = false;
      }
      if ($task) {
        $task->comment = $input->post->items[$itemIndex];
        $player = $pages->get("id=$playerId");
        updateScore($player, $task, true);
      }
    }
    $out .= '<h3 class="text-center"><span>'.__("Results have been saved !")."</span></h3>";
    $out .= '<form name="uploadFile" action="'.$page->url.'" method="post" enctype="multipart/form-data">';
    $out .= '<input type="file" name="myFile" />';
    $out .= '<input type="submit" class="btn btn-primary" value="'.__("Upload file").'"></input>';
    $out .= '</form>';
  } else {
    $tempDir = $files->tempDir('userUploads')->get();
    $uploaded = (new WireUpload('myFile')) // same as form field name
        ->setValidExtensions(['csv'])
        ->setMaxFiles(1) // remove this to allow multiple files
        ->setMaxFileSize(10 * pow(2, 20))// 10MB
        ->setDestinationPath($tempDir)
        ->execute();
    $out .= '<form name="uploadFile" action="'.$page->url.'" method="post" enctype="multipart/form-data">';
    $out .= '<input type="file" name="myFile" />';
    $out .= '<input type="submit" class="btn btn-primary" value="'.__("Upload file").'"></input>';
    $out .= '</form>';
    foreach ($uploaded as $file) {
      $out .= '<h3 class="text-center"><span>'.__("Chosen file")." : ".$file."</span></h3>";
      $filePath = $tempDir . $file;
      $handle = fopen($filePath, "r");
      if ($handle) {
        $row = 0;
        while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
          $num = count($data);
          if ($row == 0) {
            $firstNum = $num;
          }
          if ($num == $firstNum && $row > 0) { // Skip first line (SACoche pupils' IDs)
            $results[$row] = [];
            /* $out .= "<p> $num fields in line $row: <br /></p>\n"; */
            for ($c=0; $c < $num; $c++) {
              // Build results data
              // In each row, first item is item's id, last item is item's name
              // Last row is players' names
              $results[$row][$c] = utf8_encode($data[$c]);
            }
          }
          $row++;
        }
        $results = array_values($results);
        // Read SACoche extra lines
        fclose($handle);
        $handle = fopen($filePath, "r");
        $row = 0;
        while(!feof($handle)) {
          $line = fgets($handle);
          if ($row == count($results)+2) {
            $teamName = $sanitizer->pagename($line);
          }
          if ($row == count($results)+4) {
            $testTitle = utf8_encode($line);
          }
          $row++;
        }
        fclose($handle);
      } else {
        $out .= __('Error opening the file !');
      } 

      /* $handle = fopen($filePath, "r"); */
      /* if ($handle) { */
      /*   $row = 0; */
      /*   while (($data = fgetcsv($handle, 0, ";")) !== FALSE) { */
      /*     $num = count($data); */
      /*     if ($row == 0) { */
      /*       $firstNum = $num; */
      /*     } */
      /*     if ($num == $firstNum && $row > 0) { // Skip first line (SACoche pupils' IDs) */
      /*       $results[$row] = []; */
      /*       /1* $out .= "<p> $num fields in line $row: <br /></p>\n"; *1/ */
      /*       for ($c=0; $c < $num; $c++) { */
      /*         // Build results data */
      /*         // In each row, first item is item's id, last item is item's name */
      /*         // Last row is players' names */
      /*         $results[$row][$c] = utf8_encode($data[$c]); */
      /*       } */
      /*     } */
      /*     $row++; */
      /*   } */
      /*   $results = array_values($results); */
      /*   // Read SACoche extra lines */
      /*   fclose($handle); */
      /*   $handle = fopen($page->csvFile->path.$page->csvFile->filename, "r"); */
      /*   $row = 0; */
      /*   while(!feof($handle)) { */
      /*     $line = fgets($handle); */
      /*     if ($row == count($results)+2) { */
      /*       $teamName = $sanitizer->pagename($line); */
      /*     } */
      /*     if ($row == count($results)+4) { */
      /*       $testTitle = utf8_encode($line); */
      /*     } */
      /*     $row++; */
      /*   } */
      /*   fclose($handle); */
      /* } else { */
      /*   $out .= __('Error opening the file !'); */
      /* } */ 

      if ($results) {
        $namesRowIndex = count($results)-1;
        // Build events and items
        $events = [];
        $items = [];
        for($j=0; $j<$namesRowIndex; $j++) { // Skip names row
          array_push($items, end($results[$j]));
          for($k = 1; $k<count($results[$namesRowIndex]); $k++) { // Skip first value (SACoche's itemId)
            /* $events[$pupils[$k]][end($results[$j])] = $results[$j][$k+1]; */
            if ($results[$namesRowIndex][$k] != '') {
              $events[$results[$namesRowIndex][$k]][end($results[$j])] = $results[$j][$k];
            }
          }
        }
      }
      $out .= '<p class="alert alert-danger text-center">'.__("Check carefully all data before submitting !").'</p>';
      $out .= '<form name="saveResultsForm" action="'.$page->url.'" method="post">';
      $out .= '<div class="well">';
      $out .= '<p>'.__("Title found");
      $out .= ' → '.$testTitle;
      $out .= '&nbsp;&nbsp;&nbsp;'.__('Items found').' → '.$namesRowIndex.'</p>';
      $out .= '<ol>';
      $index = 1;
      foreach($items as $i) { // Prepare for task comment
        $itemDetails = explode(' ', $i, 4);
        $out .= '<li>';
        $out .= $i;
        $out .= '<br />';
        $out .= ' → ';
        $out .= '<input type="text" name="items['.$index.']" value="'.$itemDetails[3].' ['.$testTitle.']" size="50"></input>';
        $out .= '</li>';
        $index++;
      }
      $out .= '</ol>';

      $out .= '<table class="table table-hover table-condensed">';
      $out .= '<thead>';
      $out .= '<th class="text-right">'.__("Name in file").' → </th>';
      $out .= '<th class="text-left">'.__("Recognized as").'</th>';
      $index = 1;
      foreach($items as $i) { // Prepare for task comment
        $out .= '<th><label for="box-'.$index.'"><input type="checkbox" checked="checked" class="toggleCheckboxes" name="box-'.$index.'" id="box-'.$index.'" data-col="'.$index.'"> '.__("Item").' '.$index.'</input></label></th>';
        $index++;
      }
      $out .= '</thead>';
      $out .= '<tbody>';
      foreach($events as $name=>$event) { // Prepare events
        $out .= '<tr>';
        $out .= '<td class="text-right">';
        $out .= $name.' → ';
        $out .= '</td>';
        $out .= '<td class="text-left">';
        list($firstName, $lastName) = explode(' ', $name, 2); 
        $paPlayer = $pages->get("title~=$firstName, lastName~=$lastName, team.name=$teamName");
        if ($paPlayer->id) {
          $out .= ' <span class="label label-success">'.$paPlayer->title.' ['.$paPlayer->team->title.']</span>';
        } else {
          $out .= ' <span class="label label-danger">'.__("Not found !").'</span>';
        }
        $out .= '</td>';
        $index = 1;
        foreach ($event as $itemName=>$itemResult) {
          switch($itemResult) {
            case '1': $checked = "checked='checked'"; $resultTag = 'RR'; $class='danger'; break;
            case '2': $checked = "checked='checked'"; $resultTag = 'R'; $class='danger'; break;
            case '3': $checked = "checked='checked'"; $resultTag = 'V'; $class='success'; break;
            case '4': $checked = "checked='checked'"; $resultTag = 'VV'; $class='success';break;
            default : $checked = ''; $resultTag = '-'; $class='primary';
          }
          $out .= '<td>';
          if ($paPlayer->id) {
            $out .= '<label for="'.$paPlayer->id.'-'.$itemResult.'-'.$index.'">';
            $out .= '<input type="checkbox" '.$checked.' name="events['.$paPlayer->id.'-'.$itemResult.'-'.$index.']" id="'.$paPlayer->id.'-'.$itemResult.'-'.$index.'">';
            $out .= $index.'→';
            $out .= '<span class="label label-'.$class.'">'.$resultTag.'</span>';
            $out .= '</input>';
            $out .= '</label>';
          }
          $index++;
          $out .= '</td>';
        }
        $out .= '</tr>';
      }
      $out .= '</tbody>';
      $out .= '</table>';

      $out .= '<p class="alert alert-danger text-center">'.__("Check carefully all data before submitting !").'</p>';
      $out .= '<input type="submit" class="confirmSubmit btn btn-block btn-primary" value="'.__("Save all results").'"></input>';
      $out .= '</form>';
      $out .= '</div>';
    }
  }
  echo $out;

  include("./foot.inc");
?>

