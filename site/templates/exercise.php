<?php
  include("./head.inc"); 

  // Get exercise type
  include('./exTemplates/'.$page->type->name.'.php');

  /*
  $file = $page->exFile->filename;
  $handle = fopen($file, "r");
  if ($handle) {
    while (($line = fgets($handle)) !== false) {
      // process the line read.
      $type = $page->type->name;
      switch($type) {
      case 'matching' : // Simple text matching exercise
        $words[] = explode(',', $line);
        break;
      default :
        echo $line;
      }
    }

    $i = 0;
    foreach ($words as $l=>$w) {
      $out .= '<ul>';
      $out .= '<li>'.$w[0].'</li>';
      $out .= '</ul>';
      $i++;
    }
    foreach ($words as $l=>$w) {
      $out .= '<ul>';
      $out .= '<li>'.$w[1].'</li>';
      $out .= '</ul>';
      $i++;
    }

    echo $out;

    fclose($handle);
  } else {
    // error opening the file.
    echo "Sorry. An error occurred while trying to open the file.";
  } 
   */

  include("./foot.inc"); 
?>
