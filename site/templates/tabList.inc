<?php namespace ProcessWire;

  $out = '';
  $monthName = strtolower(date("F"));
  $monthId = $pages->get("name=$monthName")->id;
  $currentPeriod = $team->periods;

  $out .= '<ul class="tabList list-inline">';

  $class = $page->is("template=main-office") ? "active" : "";
  $out .= '<li class="'.$class.'"><a href="'.$office->url.$team->name.'">'.$office->title.'</a></li>';
  $class = $page->is("name=players") ? "active" : "";
  $out .= '<li class="'.$class.'"><a href="'.$homepage->url.'players/'.$team->name.'">'.__("The team").'</a></li>';
  $class = $page->is("name=world") ? "active" : "";
  $out .= '<li class="'.$class.'"><a href="'.$homepage->url.'world/'.$team->name.'">'.__("The world").'</a></li>';
  $class = $page->is("name=admintable") ? "active" : "";
  $out .= '<li class="'.$class.'"><a href="'.$homepage->url.'adminTable/'.$team->name.'">'.__("Admin table").'</a></li>';
  $class = $page->is("name=shop") ? "active" : "";
  $out .= '<li class="'.$class.'"><a href="'.$shop->url.$team->name.'">'.$shop->title.'</a></li>';
  $class = $page->is("name=makedonation") ? "active" : "";
  $out .= '<li class="'.$class.'"><a href="'.$donation->url.$team->name.'">'.$donation->title.'</a></li>';
  $class = $page->is("name=quiz") ? "active" : "";
  $out .= '<li class="'.$class.'"><a href="'.$quiz->url.$team->name.'">'.$quiz->title.'</a></li>';

  if (($user->isSuperuser() || $user->name == 'flieutaud') && $team->name != 'no-team') { // TODO Depends on teacher 
    if ($currentPeriod != false) {
      $out .= '<li><a target="_blank" href="'.$homepage->url.'report_generator/participation/'.$team->id.'/'.$currentPeriod->id.'?sort=title">'.__("Current period participation").'</a></li>';
    }
  }

  $out .= '</ul>';

  echo $out;
?>
