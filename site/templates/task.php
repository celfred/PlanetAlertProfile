<?php 
/** Task template */

include("./head.inc"); 
?>

<div ng-controller="taskCtrl" ng-init="loadTasks()">
  
  <a class="pdfLink btn btn-info" href="<?php echo $page->url; ?>?pages2pdf=1">Get PDF</a>

  <ul class="list-inline text-center">
    <li><a class="btn btn-info" href="" ng-click="setSearch('', search.type)">Toutes les actions</a></li>
    <li ng-repeat="cat in allCategories"><a class="btn btn-info" href="" ng-click="setSearch(cat.name, search.type)">{{cat.title}}</a></li>
  </ul>
  <div class="col-sm-12 text-center">
    <label><input ng-model="search.type" value='' type="radio">Toutes les actions</input></label>
    <label><input ng-model="search.type" value='positive' type="radio">Actions positives seulement</input></label>
    <label><input ng-model="search.type" value='négative' type="radio">Actions négatives seulement</input></label></li>
  </div>
  <table class="table table-hover table-condensed">
    <tr>
      <th ng-click="predicate = 'name'; reverse=!reverse">Nom</th>
      <th ng-click="predicate = 'HP'; reverse=!reverse"><img src="<?php  echo $config->urls->templates?>img/heart.png" alt="" /> Santé</th>
      <th ng-click="predicate = 'XP'; reverse=!reverse"><img src="<?php  echo $config->urls->templates?>img/star.png" alt="" /> Expérience</th>
      <th ng-click="predicate = 'GC'; reverse=!reverse"><img src="<?php  echo $config->urls->templates?>img/gold_mini.png" alt="Gold Coins (GC)" width="20" height="20" /> Or</th>
      <th ng-click="predicate = 'category.title'; reverse=!reverse">Catégorie</th>
      <th ng-click="predicate = 'type'; reverse=!reverse"><span class="glyphicon glyphicon-plus"></span> / <span class="glyphicon glyphicon-minus"></span></th>
    </tr>
    <tr ng-repeat="task in tasks | orderBy:predicate:reverse | filter:search" ng-class="{'negative' : task.type == 'negative', 'positive' : task.type == 'positive' }">
      <td><span>{{task.title | filterHtmlChars}}</span> <span tooltip-html-unsafe="{{task.summary}}" tooltip-placement="right" class="glyphicon glyphicon-info-sign"></span></td>
      <td>{{task.HP}}</td>
      <td>{{task.XP}}</td>
      <td>{{task.GC}}</td>
      <td>{{task.category.title}}</td>
      <td>{{task.type}}</td>
    </tr>
  </table>

</div>

<?php
  include("./foot.inc"); 
?>
