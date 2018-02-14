<?php namespace ProcessWire; /* list-all template */
  include("./head.inc"); 

  //if ($page->children->get("template='player'")) { // Admin front-end
  if ($page->name == 'players') { // Team or Player Details front-end
    if ($input->urlSegment2) { // Player details
      include("./player_details.php");
    } else { // Team
      include("./team.inc.php");
    }
  } elseif ($page->name == 'places') { // All places front-end
      include("./place.php");
  }

  include("./foot.inc");
?>
