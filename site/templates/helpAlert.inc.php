<?php
  if (isset($helpAlert)) {
    $out .= '<section id="helpAlert">';
    $out .= '<div id="helpTitle">';
    $out .= $helpTitle;
    $out .= '</div>';
    $out .= '<div id="helpMessage">';
    $out .= $helpMessage;
    $out .= '</div>';
    $out .= '</section>';
  }
?>
