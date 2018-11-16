<?php
  if (isset($helpAlert)) {
    echo '<section id="helpAlert">';
    if (isset($helpTitle)) {
      echo '<div id="helpTitle">';
      echo $helpTitle;
      echo '</div>';
    }
    if (isset($helpMessage)) {
      echo '<div id="helpMessage">';
      echo $helpMessage;
      echo '</div>';
    }
    echo '</section>';
  }
?>
