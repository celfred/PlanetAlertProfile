<?php
namespace ProcessWire;
  // TODO : Add comments for each news?
  include("./head.inc"); 

  // Admin news
  if ($user->isLoggedin()) {
    if ($user->isSuperuser() || $user->hasRole('teacher')) { // Admin and teacher get all news
      $newsAdmin = $pages->get("/newsboard")->children("limit=5")->sort("-created");
    } else {
      if ($user->hasRole('player')) { // Players get public and ranked news
        $newsAdmin = $pages->get("/newsboard")->children("limit=5, public=0|1, ranks=''|$player->rank")->sort("-created");
      }
    }
  } else { // Guests get public news only
    $newsAdmin = $pages->get("/newsboard")->children("limit=5, public=1")->sort("-created");
  }
  if ($newsAdmin->count() > 0) {

    // Pagination
    $pagination = $newsAdmin->renderPager();
    echo $pagination;

    foreach($newsAdmin as $n) {
    ?>
      <div id="<?php echo $n->id; ?>" class="news panel panel-success">
        <div class="panel-heading">
          <h4 class="panel-title">
          <?php
          $logo = $homepage->photo->eq(1)->size(40,40); 
          echo '<img src="'.$logo->url.'" alt="" /> ';
          echo strftime("%d %b %Y", $n->created);
          echo ' - ';
          echo 'Official Announcement : '.$n->title;
          if ($n->public == 0) {
            if ($n->ranks) {
              foreach ($n->ranks as $r) {
                echo ' <span class="label label-default">'.$r->title.'</span>';
              }
            }
          } else {
            echo ' <span class="label label-default">Public News</span>';
          }
          ?>
          <button type="button" class="close" data-id="<?php echo '#'.$n->id; ?>" aria-label="Close"><span aria-hidden="true">&times;</span></button>
         </h4>
         </div>
         <div class="panel-body">
         <?php
         echo $n->body;
         echo '<br />';
        if ($user->language->name != 'french') {
          $n->of(false);
          if ($n->body->getLanguageValue($french) != '') {
            echo '<a class="" data-toggle="collapse" href="#collapseDiv'.$n->id.'" aria-expanded="false" aria-controls="collapseDiv">'.__("[French version]").'</a>';
            echo '<div class="collapse" id="collapseDiv'.$n->id.'">';
            echo '<div class="well">';
            echo nl2br($n->body->getLanguageValue($french));
            echo '</div>';
            echo '</div>';
          }
        }
         ?>
         </div>
         <?php
         if ($user->isSuperuser()) {
           if ($n->publish == 0) {
            $checked = '';
           } else {
            $checked = 'checked="checked"';
           }
         ?>
         <div class="panel-footer text-right">
         <label for="unpublish_<?php echo $n->id; ?>"><input type="checkbox" id="unpublish_<?php echo $n->id; ?>" class="ajaxUnpublish" value="<?php echo $pages->get('name=submitforms')->url.'?form=unpublish&newsId='.$n->id; ?>"<?php echo $checked; ?> /> Published on Newsboard<span id="feedback"></span></label>
         </div>
         <?php
           }
         ?>
      </div>
  <?php
    }
    // Pagination
    echo $pagination;
  } else {
    echo 'No news.';
  }

  include("./foot.inc"); 
?>
