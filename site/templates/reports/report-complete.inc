<?php
  $out = '';
  $out .= '<section class="well">';
  if ($user->isGuest()) {
    $out .= $noAuthMessage;
  } else {
    include("report-title.inc"); 
    $out .= '<table class="table table-condensed table-hover">';
      $out .= '<tr>';
      $out .= '<td>'.$allPlayers->count().' pupils</td>';
      if (!$input->get['pages2pdf']) {
        $out .= '<th><span class="glyphicon glyphicon-pencil" data-toggle="tooltip" title="Compétence SACoche : Je peux présenter mon travail à la maison."></span></th>';
        $out .= '<th><span class="glyphicon glyphicon-file" data-toggle="tooltip" title="Compétence SACoche : J\'ai mon matériel."></span></th>';
        $out .= '<th><span class="glyphicon glyphicon-comment" data-toggle="tooltip" title="Compétence SACoche : Je participe en classe."></span></th>';
        $out .= '<th>Abs.</th>';
        $out .= '<th><span data-toggle="tooltip" title="Compétence SACoche : J\'adopte une attitude d\'élève en classe.">Attitude</span></th>';
        $out .= '<th>Tests</th>';
        $out .= '<th><span data-toggle="tooltip" title="Solo missions" class="glyphicon glyphicon-user"></span></th>';
        $out .= '<th><span data-toggle="tooltip" title="Group missions"><span class="glyphicon glyphicon-user"></span><span class="glyphicon glyphicon-user"></span></span></th>';
        $out .= '<th><span data-toggle="tooltip" title="Underground Training">UT</span></th>';
        $out .= '<th><span data-toggle="tooltip" title="Compétence SACoche : Je prends une initiative particulière (Motivation).">Mot.</span> <span data-toggle="tooltip" data-html="true" title="<span class=\'label label-success\'>VV</span> 9 xtHk/init. ET 47→49 UT/FP<br /><span class=\'label label-success\'>VV</span> 10 xtHk/init. OU 50→+ UT/FP<br /><span class=\'label label-success\'>V</span> 4 xtHk/init. ET 18→19 UT/FP<br /><span class=\'label label-success\'>V</span> 5 xtHK/init. OU 20→49 UT/FP" class="glyphicon glyphicon-question-sign"></span></th>';
        $out .= '<th>Planet Alert</th>';
      } else {
        $out .= '<th>Hk</th>';
        $out .= '<th>Mat.</th>';
        $out .= '<th>Part.</th>';
        $out .= '<th>Abs.</th>';
        $out .= '<th>Attitude</th>';
        $out .= '<th>Tests</th>';
        $out .= '<th>Solo</th>';
        $out .= '<th>Group</th>';
        $out .= '<th>UT</th>';
        $out .= '<th>Mot.</th>';
        $out .= '<th>Planet Alert</th>';
      }
      $out .= '</tr>';

      $teamPlaces = 0;
      $teamEquipment = 0;
      $teamHealth = array();
      $teamForgotStuff = 0;
      $teamForgotSigned = 0;
      $teamNoHk = 0;
      $teamHalfHk = 0;
      $teamExtraHk = 0;
      $teamAbsent = 0;
      $teamPart = array();
      $teamNegAttitude = 0;
      $teamPosAttitude = 0;
      $teamEl = 0;
      $teamEq = 0;
      $teamDeath = 0;
      $teamUt = 0;
      $teamMotivation = 0;
      $teamPosTests = 0;
      $teamNegTests = 0;
      $listNoHk = '';
      $listHalfHk = '';
      $listExtraHk = '';
      $listForgotStuff = '';
      $listForgotSigned = '';

      foreach($allPlayers as $player) {
        setHomework($player,$period->dateStart, $period->dateEnd);
        setParticipation($player, $period->dateStart, $period->dateEnd);
        setAttitude($player,$period->dateStart, $period->dateEnd);
        $nbForgotSigned = $player->notSigned->count();
        $nbForgotStuff = $player->noMaterial->count();
        $nbNoHk = $player->noHk->count();
        $nbHalfHk = $player->halfHk->count();
        $nbHk = $player->nbHk;
        $nbExtraHk = $player->extraHk->count() + $player->veryExtraHk->count();
        $death = $player->find("task.name=death, date>=$period->dateStart, date<=$period->dateEnd");
        $utSessions = $player->find("template=event, task.name=ut-action-v|ut-action-vv, date>=$period->dateStart, date<=$period->dateEnd");
        $inClassUtCount = $utSessions->find("inClass=1")->count();
        $fpSessions = $player->find("template=event, task.category.name=test, date>=$period->dateStart, date<=$period->dateEnd");
        $inClassFpCount = $fpSessions->find("inClass=1")->count();
        $posTests = $fpSessions->find("task.HP=0");
        $negTests = $fpSessions->find("task.HP<0");
        // Team stats
        $teamNoHk += $nbNoHk;
        $teamHalfHk += $nbHalfHk;
        $teamExtraHk += $nbExtraHk;
        $teamAbsent += $player->absent->count();
        $teamForgotSigned += $nbForgotSigned;
        $teamForgotStuff += $nbForgotStuff;
        $teamNegAttitude += $player->negAttitude->count();
        $teamPosAttitude += $player->posAttitude->count();
        $teamEl += $player->places->count()+$player->people->count();
        $teamEq += $player->equipment->count();
        $teamDeath += $death->count();
        $teamUt += $utSessions->count();
        if ($player->motivation == 'V' || $player->motivation == 'VV') {
          $teamMotivation++;
        }
        $teamPosTests += $posTests->count();
        $teamNegTests += $negTests->count();

        $out .= '<tr>';
        $out .= '<th>';
        if (!$input->get['pages2pdf']) {
          $out .= $player->title;
        } else {
          $out .= $player->title.' '.$player->lastName;
        }
        $out .= '</th>';
        $out .= '<td>';
        switch ($player->homework) {
          case 'NN' : $class='primary'; break;
          case 'VV' : $class='success'; break;
          case 'V' : $class='success'; break;
          case 'R' : $class='danger'; break;
          case 'RR' : $class='danger'; break;
          default: $class = '';
        }
        $out .=  '<span data-toggle="tooltip" title="Éléments évalués : '.$player->nbHk.'" class="label label-'.$class.'">'.$player->homework.'</span> ';
        if (!$input->get['pages2pdf']) {
          if ($nbNoHk > 0) {
            $listNoHk = '';
            foreach ($player->noHk as $e) {
              $listNoHk .= '- '.strftime("%d/%m", $e->date).' - '.$e->summary.'<br />';
            }
            $out .=  '<span data-toggle="tooltip" data-html="true" title="'.$listNoHk.'"><span class="glyphicon glyphicon-remove-circle"></span>&nbsp;<span>'.$nbNoHk.'</span></span>';
            $out .= '&nbsp;&nbsp;&nbsp;';
          }
          if ($nbHalfHk > 0) {
            $listHalfHk = '';
            foreach ($player->halfHk as $e) {
              $listHalfHk .= '- '.strftime("%d/%m", $e->date).' - '.$e->summary.'<br />';
            }
            $out .=  '<span data-toggle="tooltip" data-html="true" title="'.$listHalfHk.'"><span class="glyphicon glyphicon-ban-circle"></span>&nbsp;<span>'.$nbHalfHk.'</span></span>';
            $out .= '&nbsp;&nbsp;&nbsp;';
          }
          if ($nbForgotSigned > 0) {
            $listForgotSigned = '';
            foreach ($player->notSigned as $e) {
              $listForgotSigned .= '- '.strftime("%d/%m", $e->date).' - '.$e->summary.'<br />';
            }
            $out .=  '<span data-toggle="tooltip" data-html="true" title="'.$listForgotSigned.'"><span class="glyphicon glyphicon-pencil"></span>&nbsp;<span class="">'.$nbForgotSigned.'</span></span> ';
          }
          if ($nbExtraHk > 0) {
            $listExtraHk = '';
            foreach ($player->extraHk as $e) {
              $listExtraHk .= '- '.strftime("%d/%m", $e->date).' - '.$e->summary.'<br />';
            }
            $out .=  '<span data-toggle="tooltip" data-html="true" title="'.$listExtraHk.'"><span class="glyphicon glyphicon-ok-circle"></span>&nbsp;<span>'.$nbExtraHk.'</span></span>';
          }
        } else {
          if ($nbNoHk > 0) {
            $out .=  '<span>'.$nbNoHk.' No</span>';
            $out .= '&nbsp;&nbsp;';
          }
          if ($nbHalfHk > 0) {
            $out .=  '<span>'.$nbHalfHk.' Mid</span>';
            $out .= '&nbsp;&nbsp;';
          }
          if ($nbForgotSigned > 0) {
            $out .=  '<span>'.$nbForgotSigned.' Sign.</span>';
            $out .= '&nbsp;&nbsp;&nbsp;';
          }
          $out .= '&nbsp;&nbsp;&nbsp;';
          if ($nbExtraHk > 0) {
            $out .=  '<span>'.$nbExtraHk.' Xt</span>';
          }
        }
        $out .= '</td>';
        // Material
        $out .= '<td>';
        switch ($player->materialLabel) {
          case 'NN' : $class='primary'; break;
          case 'VV' : $class='success'; break;
          case 'V' : $class='success'; break;
          case 'R' : $class='danger'; break;
          case 'RR' : $class='danger'; break;
          default: $class = '';
        }
        if (!$input->get['pages2pdf']) {
          $out .=  '<span data-toggle="tooltip" title="Nb d\'oublis : '.$nbForgotStuff.'" class="label label-'.$class.'">'.$player->materialLabel.'</span>';
        } else {
          $out .= '<span class="label label-'.$class.'">'.$player->materialLabel.'</span>';
          $out .= ' ('.$nbForgotStuff.')';
        }
        $out .= '</td>';
        // Participation
        $out .= '<td>';
        switch ($player->participation) {
          case 'NN' : $class='primary'; break;
          case 'VV' : $class='success'; break;
          case 'V' : $class='success'; break;
          case 'R' : $class='danger'; break;
          case 'RR' : $class='danger'; break;
          default: $class = '';
        }
        if (!$input->get['pages2pdf']) {
          $out .=  '<span data-toggle="tooltip" data-html="true" title="Nb de cours évalués : '.$player->nbPart.'<br/>Qualité : '.$player->partRatio.'" class="label label-'.$class.'">'.$player->participation.'</span>';
          if ($player->nbPart <= 4) { $out .= ' <span class="label label-danger" data-toggle="tooltip" title="Peu de résultats !">!</span>'; }
        } else {
          $out .= '<span class="label label-'.$class.'">'.$player->participation.'</span>';
          if ($player->nbPart <= 4) { $out .= ' <span class="label label-danger"> ! </span>'; }
        }
        array_push($teamPart, $player->partRatio);
        if ($input->get['pages2pdf']) {
          $out .= ' ('.$player->nbPart.' c., '.$player->partRatio.' q.';
        }
        $out .= '</td>';
        // Absent
        $out .= '<td>';
        $listAbsent = '';
        if ($player->absent->count() > 0) {
          if (!$input->get['pages2pdf']) {
            foreach ($player->absent as $abs) {
              $listAbsent .= '- '.strftime("%d/%m", $abs->date).'<br />';
            }
            $out .=  ' <span data-toggle="tooltip" data-html="true" title="'.$listAbsent.'">['.$player->absent->count().' abs.]</span>';
          } else {
            $out .= $player->absent->count();
          }
        }
        $out .= '</td>';
        // Attitude
        $out .= '<td>';
        if (!$input->get['pages2pdf']) {
          $listPosAttitude = '';
          if ($player->posAttitude->count() > 0) {
            foreach ($player->posAttitude as $e) {
              $listPosAttitude .= '- '.strftime("%d/%m", $e->date).': ['.$e->task->title.'] '.$e->summary.'<br />';
            }
          }
          $listNegAttitude = '';
          if ($player->negAttitude->count() > 0) {
            foreach ($player->negAttitude as $e) {
              $listNegAttitude .= '- '.strftime("%d/%m", $e->date).': ['.$e->task->title.'] '.$e->summary.'<br />';
            }
          }
          $out .= '<span data-toggle="tooltip" data-html="true" title="'.$listPosAttitude.'">'.$player->posAttitude->count().' <span class="glyphicon glyphicon-thumbs-up"></span></span> <span data-toggle="tooltip" data-html="true" title="'.$listNegAttitude.'">'.$player->negAttitude->count().' <span class="glyphicon glyphicon-thumbs-down"></span></span>';
        } else {
          $out .= $player->posAttitude->count().'+ / '.$player->negAttitude->count().'-';
        } 
        if ($player->nbAmbush > 0 ) { 
            $out .= ' <span class="label label-danger" data-toggle="tooltip" title="\'Ambush\' dans l\'attitude !">!</span>';
        }
        $out .= '</td>';
        // Tests
        $out .= '<td>';
        if (!$input->get['pages2pdf']) {
          $listPosTests = '';
          if ($posTests->count() > 0) {
            foreach ($posTests as $e) {
              $listPosTests .= '- '.strftime("%d/%m", $e->date).': ['.$e->task->title.'] '.$e->summary.'<br />';
            }
          }
          $listNegTests = '';
          if ($negTests->count() > 0) {
            foreach ($negTests as $e) {
              $listNegTests .= '- '.strftime("%d/%m", $e->date).': ['.$e->task->title.'] '.$e->summary.'<br />';
            }
          }
          $out .= '<span data-toggle="tooltip" data-html="true" title="'.$listPosTests.'">'.$posTests->count().' <span class="glyphicon glyphicon-thumbs-up"></span></span>';
          $out .= '&nbsp;';
          $out .= '<span data-toggle="tooltip" data-html="true" title="'.$listNegTests.'">'.$negTests->count().' <span class="glyphicon glyphicon-thumbs-down"></span></span>';
          if ($inClassFpCount > 0) {
            $out .= '<span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="'.$inClassFpCount.' in class fights"></span>';
          }
        } else {
          $out .= $posTests->count().'+ / '.$negTests->count().'-';
          if ($inClassFpCount > 0) {
            $out .= $inClassFpCount.' in class fights';
          }
        }
        $out .= '</td>';
        // Solo missions
        $out .= '<td>';
        $posSolo = $player->find("template=event, task.category.name=individual-work, task.HP=0, date>=$period->dateStart, date<=$period->dateEnd")->not('task.name=ut-action-v|ut-action-vv');
        $negSolo = $player->find("template=event, task.category.name=individual-work, task.HP<0, date>=$period->dateStart, date<=$period->dateEnd");
        if (!$input->get['pages2pdf']) {
          $listPosSolo = '';
          if ($posSolo->count() > 0) {
            foreach ($posSolo as $e) {
              $listPosSolo .= '- '.strftime("%d/%m", $e->date).': ['.$e->task->title.'] '.$e->summary.'<br />';
            }
          }
          $listNegSolo = '';
          if ($negSolo->count() > 0) {
            foreach ($negSolo as $e) {
              $listNegSolo .= '- '.strftime("%d/%m", $e->date).': ['.$e->task->title.'] '.$e->summary.'<br />';
            }
          }
          $out .= '<span data-toggle="tooltip" data-html="true" title="'.$listPosSolo.'">'.$posSolo->count().' <span class="glyphicon glyphicon-thumbs-up"></span></span>';
          $out .= '&nbsp;';
          $out .= '<span data-toggle="tooltip" data-html="true" title="'.$listNegSolo.'">'.$negSolo->count().' <span class="glyphicon glyphicon-thumbs-down"></span></span>';
        } else {
          $out .= $posSolo->count().'+ / '.$negSolo->count().'-';
        }
        $out .= '</td>';
        // Group missions
        $out .= '<td>';
        $pos = $player->find("template=event, task.category.name=groupwork, task.HP=0, date>=$period->dateStart, date<=$period->dateEnd")->not('task.name=ut-training-v|ut-training-vv');
        $neg = $player->find("template=event, task.category.name=groupwork, task.HP<0, date>=$period->dateStart, date<=$period->dateEnd");
        if (!$input->get['pages2pdf']) {
          $listPos = '';
          if ($pos->count() > 0) {
            foreach ($pos as $e) {
              $listPos .= '- '.strftime("%d/%m", $e->date).': ['.$e->task->title.'] '.$e->summary.'<br />';
            }
          }
          $listNeg = '';
          if ($neg->count() > 0) {
            foreach ($neg as $e) {
              $listNeg .= '- '.strftime("%d/%m", $e->date).': ['.$e->task->title.'] '.$e->summary.'<br />';
            }
          }
          $out .= '<span data-toggle="tooltip" data-html="true" title="'.$listPos.'">'.$pos->count().' <span class="glyphicon glyphicon-thumbs-up"></span></span>';
          $out .= '&nbsp;';
          $out .= '<span data-toggle="tooltip" data-html="true" title="'.$listNeg.'">'.$neg->count().' <span class="glyphicon glyphicon-thumbs-down"></span></span>';
        } else {
          $out .= $pos->count().'+ / '.$neg->count().'-';
        }
        $out .= '</td>';
        // Underground Training
        $out .= '<td>';
        if (!$input->get['pages2pdf']) {
          $listUt = '';
          if ($utSessions->count() > 0) {
            foreach ($utSessions as $e) {
              if ($e->inClass == 1) {
                $inClass = '*';
              } else {
                $inClass = '';
              }
              $listUt .= '- '.strftime("%d/%m", $e->date).': '.$e->summary.$inClass.'<br />';
            }
          }
          $out .= '<span data-toggle="tooltip" data-html="true" title="'.$listUt.'">'.$utSessions->count().'</span>';
          if ($inClassUtCount > 0) {
            $out .= '<span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="'.$inClassUtCount.' in class UT"></span>';
          }
        } else {
          $out .= $utSessions->count();
          if ($inClassUtCount > 0) {
            $out .= $inClassUtCount.' in cl.';
          }
        }
        $out .= '</td>';
        $out .= '<td>';
          $nbBonus = $player->initiative->count() + $player->extraHk->count() + $player->veryExtraHk->count();
          if ($player->motivation == 'V' || $player->motivation == 'VV') {
            $out .= '<span class="label label-success" data-toggle="tooltip" title="'.$nbBonus.' xtHk/init. - '.$player->outClassActivity.' out of class activity">'.$player->motivation.'</span>';
          } else {
            $out .= '<span class="label label-default" data-toggle="tooltip" title="'.$nbBonus.' xtHk/init. - '.$player->outClassActivity.' out of class activity">NN</span>';
          }
        $out .= '</td>';
        // Planet Alert
        $out .= '<td>';
        $out .= $player->places->count()+$player->people->count().' El.';
        $out .= ' - '.$player->equipment->count().' Eq.';
        $out .= ' - Lvl '.$player->level;
        if ($death->count() > 0) {
          if (!$input->get['pages2pdf']) {
            foreach ($death as $d) {
                $out .= ' <span data-toggle="tooltip" title="'.strftime("%d/%m", $d->date).'" class="label label-danger">D</span>';
            }
          } else {
            $out .= $death->count().'D';
          }
        }
        $out .= '</td>';
        $out .= '</tr>';
      }

      // Team stats
      $out .= '<tr>';
      $out .= '<th>';
      $out .= 'Totaux et moyennes';
      $out .= '</th>';
      // Homework
      $out .= '<th>';
      $out .= '<span>'.$teamNoHk.' No</span>';
      $out .= '&nbsp;&nbsp;&nbsp;';
      $out .= '<span>'.$teamHalfHk.' Mid</span>';
      $out .= '&nbsp;&nbsp;&nbsp;';
      $out .= '<span>'.$teamExtraHk.' Xt</span>';
      $out .= '&nbsp;&nbsp;&nbsp;';
      $out .= '<span>'.$teamForgotSigned.' Sign.</span>';
      $out .= '</th>';
      // Material
      $out .= '<th>';
      $out .= '&nbsp;&nbsp;&nbsp;';
      $out .=  '<span>'.$teamForgotStuff.' Mat.</span>';
      $out .= '</th>';
      // Participation
      $out .= '<th>';
      $out .= '<span data-toggle="tooltip" data-html="true" title="Qualité moyenne<br />(VV = 2 / V = 1 / R = -1 / RR = -2)">'.calculate_average($teamPart);
      $out .= '</th>';
      // Absent
      $out .= '<th>';
      $out .= '['.$teamAbsent.' abs.]</span>';
      $out .= '</th>';
      // Attitude
      $out .= '<th>';
      $out .= $teamPosAttitude.' <span class="glyphicon glyphicon-thumbs-up"></span></span>';
      $out .= '&nbsp;&nbsp;&nbsp;';
      $out .= $teamNegAttitude.' <span class="glyphicon glyphicon-thumbs-down"></span></span>';
      $out .= '</th>';
      // Tests
      $out .= '<th>';
      if ($teamPosTests > 0 || $teamNegTests > 0) {
        $teamTestRatio = round(($teamPosTests*100)/($teamPosTests+$teamNegTests));
      } else {
        $teamTestRatio = "-";
      }
      $out .= $teamTestRatio.'%';
      $out .= '</th>';
      // Solo missions
      $out .= '<th>';
      $out .= '</th>';
      // Groupwork
      $out .= '<th>';
      $out .= '</th>';
      // UT
      $out .= '<th>';
      $out .= $teamUt.' UT';
      $out .= '</th>';
      // Motivation
      $out .= '<th>';
      $out .= $teamMotivation.' Bonus';
      $out .= '</th>';
      // Planet ALert
      $out .= '<th>';
      $out .= $teamEl.' El. - '. $teamEq.' Eq. - D:'.$teamDeath;
      $out .= '</th>';
      $out .= '</tr>';
    $out .= '</table>';
  }
  $out .= '</section>';

  echo $out;
?>
