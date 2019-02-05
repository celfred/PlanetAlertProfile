<?php namespace ProcessWire;
  include("./head.inc"); 

  //if ($page->children->get("template='player'")) { // Admin front-end
  if ($page->name == 'players') { // Team or Player Details front-end
    if (!$input->urlSegment2) { // Player details
      include("./team.inc.php");
    } else { // Team
      include("./player_details.php");
    }
  }

  include("./foot.inc");
?>
