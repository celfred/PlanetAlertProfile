<?php 
/* Shop template */

include("./head.inc"); 
?>

<div ng-controller="shopCtrl" ng-init="loadItems()">
<?php

  echo '<a class="pdfLink btn btn-info" href="'. $page->url.'?pages2pdf=1">Get PDF</a>';

  $categories = $pages->get("/shop")->children;
  echo '<ul class="list-inline text-center">';
  echo "<li><a class='btn btn-info' href='' ng-click='setFilter(\"\")'>Tout lister</a></li>";
  foreach($categories as $category) {
    echo "<li><a class='btn btn-info' href='' ng-click='setFilter(\"{$category->name}\")' tooltip-html-unsafe='{$category->summary}' tooltip-placement='bottom'>{$category->title}</a></li>";
  }
  ?>
  </ul>

  <table class="table table-hover table-condensed">
    <tr>
      <th ng-click="predicate = 'name'; reverse=!reverse">Nom</th>
      <th ng-click="predicate = 'level'; reverse=!reverse"><span class="glyphicon glyphicon-signal"></span> Niveau minimum</th>
      <th ng-click="predicate = 'HP'; reverse=!reverse"><img ng-src="<?php  echo $config->urls->templates?>img/heart.png" alt="" /> Santé</th>
      <th ng-click="predicate = 'XP'; reverse=!reverse"><img ng-src="<?php  echo $config->urls->templates?>img/star.png" alt="" /> Expérience</th>
      <th ng-click="predicate = 'GC'; reverse=!reverse"><img ng-src="<?php  echo $config->urls->templates?>img/gold_mini.png" alt="" /> Or</th>
      <th ng-click="predicate = 'category.title'; reverse=!reverse;">Catégorie</th>
    </tr>
    <tr ng-repeat="item in items | orderBy:predicate:reverse | filter:search">
      <td>
        <img ng-mouseover="getPos($event); showImg = !showImg" ng-mouseOut="showImg = !showImg" ng-src="site/assets/files/{{item.id}}/mini_{{item.image.basename}}" alt="" />
        <span>{{item.title | filterHtmlChars}}</span>
        <span tooltip-html-unsafe="{{item.summary}}" tooltip-placement="right" class="glyphicon glyphicon-question-sign"></span>
        <ul class="list-unstyled tipList-light" ng-style="{left: posLeft+'px'}" ng-show="showImg"><li><img ng-src='site/assets/files/{{item.id}}/{{item.image.basename}}' /></li></ul>
      </td>
      <td>{{item.level}}</td>
      <td>{{item.HP}}</td>
      <td>{{item.XP}}</td>
      <td>{{item.GC}}</td>
      <td>{{item.category.title}}</td>
    </tr>
  </table>
</div>

<?php
  include("./foot.inc"); 
?>
