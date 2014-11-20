<div ng-controller="teamCtrl" ng-init="loadData('<?php echo $input->urlSegment1; ?>')">
  <?php 
  /* list-all template */

  $allPlaces = $pages->find("template=place, name!=places");
  $allTeam = $pages->find("template=player, team=");
  $reportLink = $pages->get("/report_generator")->url;

  // Returns a multidimensional array from $values based on the
  // $prefix of the keys
  function getMultiDimensional($values, $prefix) {
    // Validate the arguments
    if(!is_array($values) and !($values instanceof Traversable))
      throw new Exception("Invalid values");
    $len = strlen($prefix);
    if(!$len)
      throw new Exception("Invalid prefix");
   
    $output = Array();

    foreach($values as $key=>$value)
    {
      // The key needs to match our prefix
      if(strcmp(substr($key,0,$len), $prefix) != 0)
        continue;

      // We expect the other part of the key to hold numeric IDs
      $id = intval(substr($key,$len));
      if(!$id)
        continue;

      $output[$id] = $value;
    }
    return $output;
  }

  function updateScore($player, $task) {
    // Task details to calculate new score
    $tXP = $task->XP;
    $tHP = $task->HP;
    $tGC = $task->GC;
    
    // Ponderate task's impact according to player's equipment
    $deltaXP = 0;
    $deltaHP = 0;
    if ($player->equipment) {
      foreach ($player->equipment as $item) {
        if ($item.category.name == 'weapons') {
          $deltaXP += $item.XP;
        }
        if ($item.category.name == 'protections') {
          $deltaHP += $item.HP;
        }
      }
      if ($tHP < 0) { // Negative task
        // Loss of 1 minimum whatever the equipment
        if ( $tHP+$deltaHP > 0 ) {
          $deltaHP = $tHP-1;
        }
      } else { // Positive task
        $deltaHP = 0;
      }
    }

    // Calculate player's new score
    $player->HP = $player->HP + $tHP + $deltaHP;
    $player->XP = $player->XP + $tXP + $deltaXP;
    $player->GC += $tGC;
    // Check GC
    if ($player->GC < 0) { $player->GC = 0; }
    // Check death
    if ($player->HP <= 0) {
      // Loose 1 level
      if ($player->level > 1) {
        // TODO : loose equipment?)
        $player->level -= 1;
      } else {
        // TODO : Make an important team loss? (all players get HP loss? Extra free spots on all places?)
        // For the moment : init player scores for a new start
        $player->level = 1;
        $player->HP = 50;
        $player->GC = 0;
        $player->XP = 0;
      }
    }

    checkLevel($player);

    // Check new level
    /*
    $threshold = ($player->level*10)+90;
    if ($player->XP >= $threshold) {
      $player->level += 1;
      $player->XP -= $threshold;
      $player->HP = 50;
    }
     */
  }

  function checkLevel($player) {
    // Check new level
    $threshold = ($player->level*10)+90;
    if ($player->XP >= $threshold) {
      $player->level += 1;
      $player->XP -= $threshold;
      $player->HP = 50;
    }
  }

/*
  function updateKarma($player) {
    $player->karma=0;
    if ($player->level > 1) {
      $player->karma = $player->XP;$player->karma = 100*$player->level; // level = +100
    }
    //$player->karma += $player->GC; // GC : +1
    $player->karma += $player->XP; // XP : +1
    $player->karma += $player->equipment->count()*10; // Equipment : +10 for each
    $player->karma += $player->places->count()*20; // Freed places : +20 for each
    if (50-$player->HP > 0) $player->karma -= 50-$player->HP; // HP loss = -1
    if ($player->karma < 0) $player->karma = 0;
  }
*/

  function saveHistory($player, $task) {
    $p = new Page();
    $p->template = 'event';
    $history = $player->child("name=history");
    if (!$history->id) { // Creation of history page if doesn't exist
      $history = new Page();
      $history->parent = $player;
      $history->template = 'basic-page';
      $history->name = 'history';
      $history->title = 'History';
      $history->save();
    }
    $p->parent = $history;
    // Save title
    // Get today's date
    date_default_timezone_set('Paris/France');
    $today = date('d/m', time());
    // Get new values
    $newValues = ' ['.$player->level.'lvl, '.$player->HP.'HP, '.$player->XP.'XP, '.$player->GC.'GC, '.$player->places->count.'P, '.$player->equipment->count.'E]';
    $p->title = $today.' - '.str_replace('&#039;', '\'', $task->title).$newValues;
    // Save task
    $p->task = $task;
    // Save comment
    $p->summary = $taskComment;
    $p->save(); 
  }

  if ($user->isSuperuser()) { // Admin front-end
    if($input->post->submitShop) { // shopForm was submitted, process it
      $checked_players = $input->post->playerId;
      $checked_equipments = $input->post->equipmentId;
      $checked_GC = $input->post->GC;
      if ($checked_players) {
        $playerIndex = 0;
        foreach($checked_players as $checked_player) {
          // Modify player's page
          $player = $pages->get($checked_player);
          $player->of(false);
          
          // Get item's data
          $item = $pages->get($checked_equipments[$playerIndex]);
          // Set new values
          $player->GC = (int) $input->post->GC[$playerIndex];
          switch($item->parent->name) {
            case 'potions' : // instant use potions?
              $player->HP += $item->HP;
              if ($player->HP > 50) {
                $player->HP = 50;
              }
              break;
            default:
              $player->equipment->add($item);
              break;
          }

          // Save player's new scores
          $player->save();

          // Record history
          $task = $pages->get("name='buy'");
          saveHistory($player, $task);

          $playerIndex++;
        }
      }
    }

    if($input->post->submitMap) { // mapForm was submitted, process it
      $checked_players = $input->post->playerId;
      $checked_places = $input->post->placeId;
      $checked_GC = $input->post->GC;
      // print_r($checked_players);
      // print_r($checked_places);
      // print_r($checked_GC);
      if ($checked_players) {
        $playerIndex = 0;
        foreach($checked_players as $checked_player) {
          // Modify player's page
          $player = $pages->get($checked_player);
          $player->of(false);
          
          // Get item's data
          $item = $pages->get($checked_places[$playerIndex]);
          // Set new values
          $player->GC = (int) $input->post->GC[$playerIndex];
          // Set new XP : Freeing a place triggers an XP increase proportionnal to place level
          $deltaXP = (int) $input->post->placeLevel[$playerIndex];
          $deltaXP = $deltaXP+5;
          $player->XP = $player->XP+$deltaXP;

          $player->places->add($item);

          // Check new level
          checkLevel($player);

          // Save player's new scores
          $player->save();

          // Record history
          $task = $pages->get("name='free'");
          saveHistory($player, $task);

          $playerIndex++;
        }
      }
    }

    // TODO : Import csv file from SACoche
    if($input->post->submitTasks) { // taskForm was submitted, process it
      // Consider checked players only
      $checked_players = $input->post->player;
      if ($checked_players) {
        foreach($checked_players as $checked_player) {
          $player = $pages->get($checked_player);
          $player->of(false);

          // Set new values
          $player->HP = (int) $input->post->HP[$checked_player];
          $player->GC = (int) $input->post->GC[$checked_player];
          $player->XP = (int) $input->post->XP[$checked_player];
          $player->level = (int) $input->post->level[$checked_player];
          $htask = (string) $input->post->htask[$checked_player];
          $customTask = (string) $input->post->customTask[$checked_player];
          $comment = (string) $input->post->customTask[$checked_player];
          $taskType = (int) $input->post->taskType[$checked_player];
          if ($htask) { // A task is selected
            // TODO : replace htask by taskId;
            $task = $pages->get($taskId);
            $taskTitle = $htask;
          } else { // No task selected
            if (trim($customTask) == '') {  // No comment set : use default comment
              $taskTitle = 'Manual edit (bug correction?)';
            } else { // A comment is set : use it
              $taskTitle = $customTask;
            }
            $task = $pages->get("name='manual'");
          }

          // Save player's new scores
          $player->save();

          // Record history
          saveHistory($player, $task);
        }
      }
    }

    if($input->post->adminTableSubmit) { // adminTableForm was submitted, process it
      // Consider checked players only
      $checked_players = $input->post->player;
      $checkedPlayers = $input->post->plyr;
      
      foreach($checkedPlayers as $playerId => $checkedTasks) {
        if ($checkedTasks) {
          $checkedTasks = json_decode($checkedTasks, true);
          foreach($checkedTasks as $taskId => $taskComment) {
            $taskComment = trim($taskComment);
            //echo 'Player ID:'.$playerId.' , task id:'.$taskId.' , comment: '.$taskComment.'<br />';

            $player = $pages->get($playerId);
            $player->of(false);

            // Update player's scores
            $task = $pages->get($taskId); 
            updateScore($player, $task);

            // Save player's new scores
            $player->save();

            // Record history
            saveHistory($player, $task);
          }
        }
      }
    }
  } // End if superUser
  ?>


  <ul class="tabList list-inline">
    <li ng-class="{active: selected == 1}" ng-click="selected = 1">État de l'équipe</li>
    <li ng-class="{active: selected == 2}" ng-click="selected = 2">État du monde</li>
    <?php if ( $user->isSuperuser()) { // Admin front-end ?>
    <li ng-class="{active: selected == 3}" ng-click="selected = 3">Les actions</li>
    <li ng-class="{active: selected == 4}" ng-click="selected = 4">S'équiper</li>
    <li ng-class="{active: selected == 5}" ng-click="selected = 5">Libérer un lieu</li>
    <li ng-class="{active: selected == 6}" ng-click="selected = 6">Admin Table</li>
    <li><a href='<?php echo $reportLink.$input->urlSegment1; ?>/participation/10' target="_blank">Participation des 10 derniers cours</a></li>
    <!-- <li ng-class="{active: selected == 7}" ng-click="selected = 7">Reports</li> -->
    <?php } //Guest contact form ?>
  </ul>

  <div ng-show="selected == 1">
    <table class="table table-hover table-condensed teamView">
      <tr>
        <th ng-click="predicate = 'name'; reverse=!reverse">Nom</th>
        <th ng-click="predicate = 'karma'; reverse=!reverse">Karma</th>
        <th ng-click="predicate = 'level'; reverse=!reverse"><span class="glyphicon glyphicon-signal"></span> Niveau</th>
        <th ng-click="predicate = 'HP'; reverse=!reverse"><img ng-src="<?php  echo $config->urls->templates?>img/heart.png" alt="" /> Santé</th>
        <th ng-click="predicate = 'XP'; reverse=!reverse"><img ng-src="<?php  echo $config->urls->templates?>img/star.png" alt="" /> Expérience</th>
        <th ng-click="predicate = 'GC'; reverse=!reverse"><img ng-src="<?php  echo $config->urls->templates?>img/gold_mini.png" alt="" width="20" height="20" /> Or</th>
        <th ng-click="predicate = 'places.length'; reverse=!reverse"><img ng-src="<?php  echo $config->urls->templates?>img/globe.png" alt="" /> Lieux libres</th>
        <th ng-click="predicate = 'equipment.length'; reverse=!reverse"><span class="glyphicon glyphicon-user"></span> Équipement</th>
      </tr>
      <tr ng-repeat="player in players | orderBy:predicate:reverse">
        <td>
          <img ng-mouseover="getPos($event); showImg = !showImg" ng-mouseOut="showImg = !showImg" ng-src="site/assets/files/{{player.id}}/mini_{{player.avatar.basename}}" alt="" />
          <a href="players/{{sanitizedTeam}}/{{player.name}}">{{player.title}}</a>
          <ul class="list-unstyled tipList-light" ng-style="{left: posLeft+'px'}" ng-show="showImg"><li><img ng-src='site/assets/files/{{player.id}}/{{player.avatar.basename}}' /></li></ul>
        </td>
        <td>{{player.karma}}</td>
        <td>{{player.level}}</td>
        <td>
          <div class="progress progress-striped progress-mini" tooltip-html-unsafe="<span class='glyphicon glyphicon-heart'></span> {{player.HP}}/50">
            <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="{'width': player.hpWidth+'px'}" aria-valuemin="0" aria-valuemax="100" ng-style="{'width': player.hpWidth+'px'}">
            </div>
          </div>
        </td>
        <td>
          <div class="progress progress-striped progress-mini" tooltip-html-unsafe="<span class='glyphicon glyphicon-star'></span> {{player.XP}}/{{player.level*10+90}}">
            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{'width': player.xpWidth+'px'}" aria-valuemin="0" aria-valuemax="100" ng-style="{'width': player.xpWidth+'px'}">
            </div>
          </div>
        </td>
        <td>{{player.GC}}</td>
        <td>
          <span>{{player.places.length}}</span>
          <span ng-hide="player.places.length == 0" ng-mouseover="showPlacesList = !showPlacesList" ng-mouseout="showPlacesList = ! showPlacesList" class="glyphicon glyphicon-info-sign"></span>
          <ul ng-show="showPlacesList" class="list-unstyled tipList">
            <li ng-repeat="place in player.places">{{place.title}}</li>
          </ul>
        </td>
        <td>
          <span>{{player.equipment.length}}</span>
          <span ng-hide="(player.equipment|filter:isEquipment).length == 0" ng-mouseover="showEquipmentList = !showEquipmentList" ng-mouseout="showEquipmentList = ! showEquipmentList" class="glyphicon glyphicon-info-sign"></span>
          <ul ng-show="showEquipmentList" class="list-unstyled tipList">
            <li ng-repeat="equipment in player.equipment">{{equipment.title}}</li>
          </ul>
        </td>
      </tr>
    </table>
  </div>

  <div ng-show="selected == 2" ng-controller="mapCtrl" ng-init="">
    <h2 class="text-center"><span class="label label-default">Taux de libération de l'équipe : {{completedRate}}% du monde</span> [{{completed}}/{{allPlaces}}]</h2>
    <table class="table table-condensed table-hover">
      <tr>
        <th ng-click="predicate = 'name'; reverse=!reverse">Lieux</th>
        <th ng-click="predicate = 'city.name'; reverse=!reverse">Villes</th>
        <th ng-click="predicate = 'country.name'; reverse=!reverse">Pays</th>
        <th ng-click="predicate = 'owners.length'; reverse=!reverse"># de 'libérateurs'</th>
        <th ng-click="predicate = 'freedomRate'; reverse=!reverse">Taux de libération</th>
      </tr>
      <tr ng-repeat="place in places | orderBy:predicate:reverse" ng-class="{completed: place.freedomRate == 100, almost: place.freedomRate > 60 && place.freedomRate < 100}">
        <td>{{place.title | filterHtmlChars}}</td>
        <td>{{place.city.title | filterHtmlChars}}</td>
        <td>{{place.country.title | filterHtmlChars}}</td>
        <td>{{place.owners.length}}/{{place.maxOwners}}
          <span ng-mouseover="showOwnersList = !showOwnersList" ng-mouseout="showOwnersList = !showOwnersList" class="glyphicon glyphicon-info-sign"></span>
          <ul ng-show="showOwnersList"  ng-style="{left: posLeft+'px'}" class="list-unstyled tipList">
            <li ng-repeat="owner in place.owners">
              {{owner.title}}
            </li>
          </ul>
        </td>
        <td>
          <div class="progress progress-striped  progress-mini" tooltip-html-unsafe="{{place.freedomRate}}% [{{place.owners.length+'/'+place.maxOwners}}]">
            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{'width': place.freedomRateWidth+'px'}" aria-valuemin="0" aria-valuemax="100" ng-style="{'width': place.freedomRateWidth+'px'}">
            </div>
          </div>
        </td>
      </tr>
    </table>
  </div>

  <?php if ( $user->isSuperuser()) { // Admin front-end ?>
  <div ng-show="selected == 3" ng-init="">
    <form id="taskForm" name="taskForm" action="players/<?php echo $input->urlSegment1; ?>" method="post" class="form-horizontal" role="form">

    <input type="submit" name="submitTasks" value="Enregistrer" class="btn btn-block btn-primary" ng-disabled="!anyChecked" />

    <table class="table table-condensed teamView">
      <tr>
        <td></td>
        <td><input class="form-control input-sm" type="text" size="2" ng-model="setHP" ng-disabled="!anyChecked" ng-change="commonSet('HP', setHP)" /></td>
        <td><input class="form-control input-sm" type="text" size="2" ng-model="setGC" ng-disabled="!anyChecked" ng-change="commonSet('GC', setGC)" /></td>
        <td><input class="form-control input-sm" type="text" size="2" ng-model="setXP" ng-disabled="!anyChecked" ng-change="commonSet('XP', setXP)" /></td>
        <td><input class="form-control input-sm" type="text" size="2" ng-model="setLevel" ng-disabled="!anyChecked" ng-change="commonSet('level', setLevel)" /></td>
        <td>
          <select class="form-control input-sm" ng-model="commonTask" ng-options="task as task.title group by task.category.title for task in tasks" ng-disabled="!anyChecked" ng-change="setCommonTask()" on-keyup="nextTab('task', 0)" keys="[17]">
            <option value="">Action commune</option>
          </select>
          <input class="form-control input-sm customTask" type="text" ng-model="customCommonTask" ng-change="setCustomCommonTask()" ng-disabled="!anyChecked" name="customTaskInput" placeholder="Commentaire commun" on-keyup="nextTab('customTask', 0)" keys="[17]" />
        </td>
      </tr>
      <tr>
        <th><label class="checkbox-inline" title="Click to select all players"><input checkbox-all="players.isChecked" ng-checked="findCheckedPlayers()" /> Nom</label></th>
        <th>Santé</th>
        <th>Or</th>
        <th>Expérience</th>
        <th>Niveau</th>
        <th>Actions / Commentaires</th>
      </tr>
      <tr ng-repeat="player in players">
        <td><label class="checkbox-inline"><input type="checkbox" name="player[{{player.id}}]" value="{{player.id}}" ng-model="player.isChecked" />{{player.title}}</label></td>
        <td><input class="form-control input-sm" type="text" ng-model="player.HP" ng-disabled="!player.isChecked" name="HP[{{player.id}}]" size="2" ng-change="manualEdit(player, 'HP', player.HP)" value="{{player.HP}}" /></td>
        <td><input class="form-control input-sm" type="text" ng-model="player.GC" ng-disabled="!player.isChecked" name="GC[{{player.id}}]" size="2" ng-change="manualEdit(player, 'GC', player.GC)" value="{{player.GC}}" /></td>
        <td><input class="form-control input-sm" type="text" ng-model="player.XP" ng-disabled="!player.isChecked" name="XP[{{player.id}}]" size="2" ng-change="manualEdit(player, 'XP', player.XP)" value="{{player.XP}}" />/{{player.level*10+90}}</td>
        <td><input class="form-control input-sm" type="text" ng-model="player.level" ng-disabled="!player.isChecked" name="level[{{player.id}}]" size="2" ng-change="manualEdit(player, 'level', player.level)" value="{{player.level}}" /></td>
        <td>
          <select ng-click="fillTitles($event)" class="taskSelect form-control input-sm" ng-model="player.task" id="task[{{$index}}]" name="task[{{player.id}}]" ng-options="task as task.title group by task.category.title for task in tasks" ng-disabled="!player.isChecked" ng-change="updateStats(player)" on-keyup="nextTab('task', $index+1)" keys="[17]">
            <option value="">Action pour {{player.title}}</option>
          </select>
          <input type="hidden" value="{{player.task.title}}" name="htask[{{player.id}}]" />
          <input type="hidden" value="{{player.taskType}}" name="taskType[{{player.id}}]" />
          <input class="form-control input-sm customTask" type="text" ng-model='player.customTask' ng-disabled="!player.isChecked" id="customTask[{{$index}}]" name="customTask[{{player.id}}]" ng-change="manualEdit(player, 'customTask', player.customTask)" placeholder="Commentaire" on-keyup="nextTab('customTask', $index+1)" keys="[17]" />
          <a href="players/{{sanitizedTeam}}/{{player.name}}" tooltip-html-unsafe="{{player.equipmentList}}" tooltip-placement="left" title="Click for player details"><span class="glyphicon glyphicon-user"></span></a>
        </td>
      </tr>
    </table>
    <input type="submit" name="submitTasks" value="Enregistrer" class="btn btn-block btn-primary" ng-disabled="!anyChecked" />
    </form>
  </div>

  <div ng-show="selected == 4" class="row" ng-controller="shopCtrl" ng-init="">
    <form id="shopForm" class="form-inline" role="form" name="shopForm" action="players/<?php echo $input->urlSegment1; ?>" method="post">
      <p class="alert alert-info"><span class="glyphicon glyphicon-info-sign" tooltip="Seuls les joueurs ayant suffisamment d'argent sont listés."></span> Seuls les joueurs ayant suffisamment d'or sont listés. <small ng-show="validOrders.length > 0"><span class="glyphicon glyphicon-warning-sign"></span> Don't forget to save!</small></p>
      <select ng-model="tempPlayer" class="form-control" name="" ng-options="player.title+' ('+player.GC+' GC)' for player in filteredPlayers()" ng-disabled="filteredPlayers().length == 0">
        <option value="">Joueurs concernés</option>
      </select>
      <select ng-model="tempEquipment" class="form-control" name="" ng-options="item as item.title+' ('+item.GC+' GC)' group by item.category.title for item in availableItems()" ng-disabled="!tempPlayer || filteredPlayers().length==0">
        <option value="">Objets possibles</option>
      </select>
      <button type="button" class="btn" ng-click="validOrder()" ng-disabled="filteredPlayers().length == 0 || availableItems().length == 0">Valider la commande</button>
      <table class="table table-condensed table-hover">
        <tr ng-repeat="order in validOrders">
          <td>{{order.player}}</td>
          <td>{{order.equipment}}</td>
          <td>{{order.GC}} or restant</td>
          <td><span ng-click="cancelOrder($index)" class="glyphicon glyphicon-trash" tooltip="Cancel"></span></td>
          <input type="hidden" name="playerId[]" value="{{order.playerId}}" />
          <input type="hidden" name="equipmentId[]" value="{{order.equipmentId}}" />
          <input type="hidden" name="GC[]" value="{{order.GC}}" />
        </tr>
      </table>
      <input type="submit" name="submitShop" value="Enregistrer" class="btn btn-block btn-primary" ng-disabled="validOrders.length == 0" />
    </form>
  </div> <!-- /shopCtrl -->

  <div ng-show="selected == 5" class="row" ng-controller="mapCtrl" ng-init="">
    <form id="mapForm" name="mapForm" class="form-inline" role="form" action="players/<?php echo $input->urlSegment1; ?>" method="post">
      <p class="alert alert-info"><span class="glyphicon glyphicon-info-sign" tooltip="Seuls les joueurs ayant suffisamment d'argent sont listés."></span> Seuls les joueurs ayant suffisamment d'or sont listés. <small ng-show="validOrders.length > 0"><span class="glyphicon glyphicon-warning-sign"></span> Don't forget to save!</small></p>
      <select ng-model="tempPlayer" name="" class="form-control" ng-options="player.title+' ('+player.GC+' GC)' for player in filteredPlayers()" ng-disabled="filteredPlayers().length == 0">
        <option value="">Joueurs concernés</option>
      </select>
      <select ng-model="tempPlace" name="" class="form-control" ng-options="item as item.title+' ('+item.GC+' GC)' group by item.country.title for item in availableItems()" ng-disabled="!tempPlayer || filteredPlayers().length==0 || availableItems().length == 0">
        <option value="">Lieux possibles</option>
      </select>
      <button type="button" class="btn" ng-click="validOrder()" ng-disabled="filteredPlayers().length == 0 || availableItems().length == 0">Valider la commande</button>
      <table class="table table-condensed table-hover">
        <tr ng-repeat="order in validOrders">
          <td>{{order.player}}</td>
          <td>{{order.place | filterHtmlChars}}</td>
          <td>{{order.GC}} or restant</td>
          <td><span ng-click="cancelOrder($index)" class="glyphicon glyphicon-trash" tooltip="Cancel"></span></td>
          <input type="hidden" name="playerId[]" value="{{order.playerId}}" />
          <input type="hidden" name="placeId[]" value="{{order.placeId}}" />
          <input type="hidden" name="GC[]" value="{{order.GC}}" />
          <input type="hidden" name="placeLevel[]" value="{{order.level}}" />
        </tr>
      </table>
      <input type="submit" name="submitMap" value="Enregistrer" class="btn btn-block btn-primary" ng-disabled="validOrders.length == 0" />
    </form>
  </div> <!-- /mapCtrl -->

  <div ng-show="selected == 6" class="row" ng-init="">
    <form id="adminTableForm" name="adminTableForm" action="players/<?php echo $input->urlSegment1; ?>" method="post" class="" role="form">

    <ul class="list-inline text-center">
      <li><a class="btn btn-info" href="" ng-click="setSearch('', search.type)">Toutes les actions</a></li>
      <li ng-repeat="cat in allCategories"><a class="btn btn-info" href="" ng-click="setSearch(cat.name, search.type)">{{cat.title}}</a></li>
    </ul>

    <input type="submit" name="adminTableSubmit" value="Enregistrer" class="btn btn-block btn-primary" ng-disabled="" />

    <table class="adminTable">
      <tr>
        <th ng-click="adminPredicate = 'group.title'; adminReverse=!adminReverse">Groups</th>
        <th ng-click="adminPredicate = 'title'; adminReverse=!adminReverse">Players</th>
        <th ng-repeat="task in tasks | orderBy: ['GC', 'HP', 'title'] | filter:search" title="{{task.summary}}">
          <div class="vertical-text">
            <div class="vertical-text__inner">
              {{task.title | filterHtmlChars}}
            </div>
          </div>
        </th>
      </tr>
      <tr>
        <td colspan="2">Commentaires</td>
        <td ng-repeat="task in tasks | orderBy: ['GC', 'HP', 'title'] | filter:search" title="Commentaire">
          <input type="checkbox" ng-model="task.showComment" ng-change="setCommonComment(task)" />
          <input ng-if="adminTableAnyChecked && task.showComment" type="text" name="commonComment[{{task.id}}]" value="" placeholder="Commentaire commun" ng-model="task.commonComment" ng-change="setCommonComment(task)" />
        </td>
      </tr>
      <tr>
        <td colspan="2">Tout cocher</td>
        <td ng-repeat="task in tasks | orderBy: ['GC', 'HP', 'title'] | filter:search" title="Tout cocher"><input type="checkbox" ng-model="task.checkedAll" ng-click="selectAll(task)" /></td>
      </tr>
      <tr ng-repeat="player in players | orderBy:adminPredicate:adminReverse">
        <td>{{player.group.title}}</td>
        <th>{{player.title}}</th>
        <!-- Hack because checklist-value doesn't work as expected... -->
        <input type="hidden" name="player[{{player.id}}]" value="{{player.checkedTasks}}">
        <input type="hidden" name="plyr[{{player.id}}]" value="{{player.checkedComments}}">
        <td ng-repeat="task in tasks | orderBy: ['GC', 'HP', 'title'] | filter:search" title="{{player.title}} ({{player.group.title}}) - {{task.title | filterHtmlChars}}">
          <input type="checkbox" name="pl_{{player.id}}[]" checklist-model="player.checkedTasks" checklist-value="task.id" ng-change="onCheck(player, task)" />
          <input ng-if="task.showComment && (player.checkedTasks.indexOf(task.id) != -1)" type="text" name="comment_{{player.id}}[{{task.id}}]" value="" ng-model="player.checkedComments[task.id]" placeholder="Commentaire" />
        </td>
      </tr>
    </table>
    <input type="submit" name="adminTableSubmit" value="Enregistrer" class="btn btn-block btn-primary" ng-disabled="" />
    </form>
  </div>

  <div ng-show="selected == 7" class="row" ng-init="">
    <ul>
      <li><a target="_blank" href="<?php echo $pages->get('/team-report')->url.$input->urlSegment1; ?>">Team report</a></li>
      <li ng-repeat="cat in allCategories"><a target="_blank" href="<?php echo $pages->get('/team-report')->url.$input->urlSegment1; ?>/{{cat.id}}">Team report : {{cat.title}}</a></li>
  </div>

  <?php } // Close admin front-end ?>

</div> <!-- /teamCtrl -->
