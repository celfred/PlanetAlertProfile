<?php namespace ProcessWire;

  include("./head.inc"); 
  $out = '';
  $item = $page;
  if ($user->hasRole('player')) {
    $item = possibleElement($player, $item);
    switch($item->pb) {
      case 'possible' : 
        $pbTitle = __("You can buy this item.").' ⇒ ';
        $pbTitle .=  '<a class="btn btn-primary" href="'.$pages->get('/shop_generator')->url.$player->id.'">'.__("Go to the marketplace").'</a>';
        break;
      case 'helmet' : 
        $pbTitle = __("You must buy the Memory helmet first before buying this item.");
        break;
      case 'already' : 
        $pbTitle = __("You already have this item.");
        break;
      case 'freeActs' : 
        $nbEl = $player->places->count()+$player->people->count();
        $pbTitle = sprintf(__('This item requires %1$s free elements ! You have only %2$s free elements.'), $item->freeActs, $nbEl);
        break;
      case 'GC' : 
        $pbTitle = sprintf(__('This item requires %1$s GC ! You have only %2$s GC.'), $item->GC, $player->GC);
        break;
      case 'level' : 
        $pbTitle = sprintf(__('This item requires a level %1$s ! You are only at level %2$s.'), $item->level, $player->level);
        break;
      case 'maxToday' :
        $pbTitle = __("You have reached the 3 items limit for today ! Come back tomorrow  to go to the Marketplace !");
        break;
      default: 
        $pbTitle = __("You can't buy this item for the moment. Sorry.");
    }
    $out .= '<h3 class="text-center"><span class="label label-danger">'.$pbTitle.'</span></h3>';
  }
  $out .= '<div class="well">';
  $out .= '<h2 class="text-center">';
  $out .= '<strong>'.$item->title.'</strong>';
  $out .= ' → ';
  $out .= '<span class="label label-primary">'.$item->category->title.'</span>';
  $out .= '</h2>';
  $out .= '<div class="row">';
    $out .= '<div class="col-sm-4">';
      if ($item->image) {
        $out .= '<img class="img-thumbnail" src="'.$item->image->getCrop("big")->url.'" alt="Image" />&nbsp;&nbsp;';
      }
      $out .= '<h3>';
      $out .= '<span class="label label-primary"><span class="glyphicon glyphicon-signal"></span> '.$item->level.'</span>';
      $out .= '&nbsp;&nbsp;';
      $out .= '<span class="label label-default"><img src="'.$config->urls->templates.'img/gold_mini.png" alt="GC" /> '.$item->GC.__('GC').'</span>';
      $out .= '&nbsp;&nbsp;';
      if ($item->HP !== 0) {
        if ($item->HP > 0) { $sign = '+'; } else { $sign = ''; }
        $out .= '<span class="label label-primary"><img src="'.$config->urls->templates.'img/heart.png" alt="HP" /> '.$sign.$item->HP.'HP</span>';
        $out .= '&nbsp;&nbsp;';
      }
      if ($item->XP !== 0) {
        if ($item->XP > 0) { $sign = '+'; } else { $sign = ''; }
        $out .= '<span class="label label-primary"><img src="'.$config->urls->templates.'img/star.png" alt="XP" /> '.$sign.$item->XP.'XP</span>';
      }
      $out .= '</h3>';
    $out .= '</div>';
    $out .= '<div class="col-sm-8">';
      $out .= '<h2 class="">'.nl2br($item->summary);
      $out .= '</h2>';
      if ($user->language->name == 'default') {
        $item->of(false);
        if ($item->summary->getLanguageValue($french) != '') {
          $out .= '<a class="" data-toggle="collapse" href="#collapseDiv" aria-expanded="false" aria-controls="collapseDiv">'.__("[French version]").'</a>';
          $out .= '<div class="collapse" id="collapseDiv">';
          $out .= '<div class="well">';
            $out .= nl2br($item->summary->getLanguageValue($french));
          $out .= '</div>';
          $out .= '</div>';
        }
      }
    $out .= '</div>';
  if ($item->is("name=memory-potion")) {
    if ($user->isLoggedin()) {
      $out .= '<hr />';
      $out .= '<h4>'.__("Available texts").' :</h4>';
      $out .= '<ol class="list">';
      if ($user->isSuperuser()) {
        $allTexts = $pages->find("template=memory-text, include=all, sort=index");
      } else if ($user->hasRole('teacher')){
        $allTexts = $profilePage->memoryPotionTexts;
      } else if ($user->hasRole('player')) {
        $allTexts = $pages->get("parent.name=teachers, singleTeacher=$headTeacher")->memoryPotionTexts;
      } else {
        $allTexts = '';
      }
      if ($user->isSuperuser() || $user->hasRole('teacher')) { // Show all texts for admin
        if (count($allTexts) > 0) {
          foreach ($allTexts as $t) {
            $out .= '<li>';
            $out .= '<h4>';
            if ($user->isSuperuser() || $t->created_users_id == $user->id || $user->name == 'flieutaud') {
              $out .= $t->feel(array(
                'fields' => 'title,summary,task'
              ));
            }
            $out .= ' <span>'.$t->title.'</span>';
            $out .= ' <span class="glyphicon glyphicon-eye-open" data-toggle="tooltip" data-html="true" title="'.nl2br($t->summary).'"></span>';
            $out .= ' ('.strlen($t->summary). ' caracters) ';
            if ($t->task) {
              $out .= ' <span class="badge badge-default">'.$t->task->title.'</span>';
            }
            $out .= ' <a class="btn btn-info" href="'. $page->url.'/memory-potion/'.$t->id.'/?pages2pdf=1">'.__("[Get PDF]").'</a>';
            $out .= '</h4>';
            $out .= '</li>';
          }
        }
        if ($user->hasRole('teacher')) {
          $out .= $profilePage->feel(array(
            'text' => __("[Edit list]"),
            'fields'=>'memoryPotionTexts'
          ));
        }
        $out .= $pages->get("name=memory-potion-texts")->feel(array(
          'mode' => 'page-add',
          'text' => __("[Add a new text]"),
          'fields'=>'title,summary,task'
        ));
      } else { // Show possible texts for logged-in player
        $playerBoughtTexts = $player->find("template=event, refPage=$item, task.name=buy");
        if ($playerBoughtTexts->count() > 0) {
          foreach ($playerBoughtTexts as $bt) {
            if (count($allTexts) > 0) {
              $t = $allTexts->eq($bt->linkedId);
              $result = $player->find("template=event, linkedId=$t->id")->last();
              $out .= '<li>';
              $out .= '<h4>';
              $out .= ' <span>'.$t->title.'</span>';
              $out .= ' <span class="glyphicon glyphicon-eye-open" data-toggle="tooltip" data-html="true" title="'.nl2br($t->summary).'"></span>';
              if ($result->id) {
                if ($result->task->HP < 0) {
                  $out .= ' → <i class="glyphicon glyphicon-thumbs-down"></i>';
                } else {
                  $out .= ' → <i class="glyphicon glyphicon-thumbs-up"></i>';
                }
              } else {
                $out .= ' → <span>'.__("You have about 2 weeks to learn this text and tell your teacher when you are ready !").'</span>';
                $out .= ' <span class="label label-danger">'.__('Bought on').' '.date('D, M. j', $t->created).'</span>';
              }
              $out .= '</h4>';
              $out .= '</li>';
            } else {
              $out .= '<li>';
              $out .= __('Error ! Please contact the administrator.');
              $out .= '</li>';
            }
          }
        } else {
          $out .= '<p>'.__("You haven't bought any Memory potions yet.").'</p>';
        }
      }
      $out .= "</ol>";
    }
  }
  $out .= '</div>';
  $out .= '<a class="btn btn-block btn-primary" href="'.$shop->url.'">'.__("Back to the Marketplace").'</a>';
  $out .= '</div>';
  echo $out;

  include("./foot.inc"); 
?>
