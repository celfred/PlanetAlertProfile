<?php /* list-all template */
  include("./head.inc"); 

  //if ($page->children->get("template='player'")) { // Admin front-end
  if ($page->name == 'players') { // Admin front-end
    if ($input->urlSegment2) { // Player detail
      include("./player_details.php");
    } else { // Team list
      include("./team.php");
    }
  //} elseif ($page->children->get("template=country")) { // All places View
  } elseif ($page->name == 'places') { // All places View
      include("./place.php");
  } else {// Guest front-end
    echo "<p>You need to login to edit the game.</p>";
  }

  include("./foot.inc");
?>
