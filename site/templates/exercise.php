<?php
  include("./head.inc"); 

  echo '<div ng-app="exerciseApp">';
  
  // Get exercise type
  include('./exTemplates/'.$page->type->name.'.php');

  echo '</div>';

  include("./foot.inc"); 
?>
