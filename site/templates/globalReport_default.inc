<table class="table table-condensed table-hover">
  <tr>
    <td></td>
    <th colspan="<?php echo $categories->count; ?>"><h3><?php echo $reportTitle; ?></th>
  </tr>
  <tr>
		<td></td>
		<th><i class="glyphicon glyphicon-pencil" data-toggle="tooltip" title="Compétence SACoche : Je peux présenter mon travail à la maison."></i></th>
		<th><i class="glyphicon glyphicon-file" data-toggle="tooltip" title="Compétence SACoche : J'ai mon matériel."></i></th>
		<th><i class="glyphicon glyphicon-comment" data-toggle="tooltip" title="Compétence SACoche : Je participe en classe."></i></th>
		<th>Abs.</th>
		<th><span data-toggle="tooltip" title="Compétence SACoche : J'adopte une attitude d'élève en classe.">Attitude</span></th>
		<th>Tests</th>
		<th><span data-toggle="tooltip" title="Solo missions" class="glyphicon glyphicon-user"></span></th>
		<th><span data-toggle="tooltip" title="Group missions"><span class="glyphicon glyphicon-user"></span><span class="glyphicon glyphicon-user"></span></span></th>
		<th>UT</th>
		<th>Planet Alert</th>
  </tr>
  <?php
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
			$death = $player->find("task.name=death, dateStart>=$period->dateStart, dateEnd<=$period->dateEnd");
			$utSessions = $player->find("template=event, task.name=ut-action-v|ut-action-vv, dateStart>=$period->dateStart, dateEnd<=$period->dateEnd");
			$posTests = $player->find("template=event, task.category.name=test, task.HP=0, dateStart>=$period->dateStart, dateEnd<=$period->dateEnd");
			$negTests = $player->find("template=event, task.category.name=test, task.HP<0, dateStart>=$period->dateStart, dateEnd<=$period->dateEnd");
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
			$teamPosTests += $posTests->count();
			$teamNegTests += $negTests->count();

      echo '<tr>';
      echo '<th>';
      if (!$input->get['pages2pdf']) {
        echo $player->title;
      } else {
        echo $player->title.' '.$player->lastName;
      }
      echo '</th>';
      echo '<td>';
			switch ($player->homework) {
				case 'NN' : $class='primary'; break;
				case 'VV' : $class='success'; break;
				case 'V' : $class='success'; break;
				case 'R' : $class='danger'; break;
				case 'RR' : $class='danger'; break;
				default: $class = '';
			}
			echo  '<span data-toggle="tooltip" title="Éléments évalués : '.$player->nbHk.'" class="label label-'.$class.'">'.$player->homework.'</span> ';
			if (!$input->get['pages2pdf']) {
				if ($nbNoHk > 0) {
					echo  '<span data-toggle="tooltip" data-html="true" title="'. $listNoHk .'"><i class="glyphicon glyphicon-remove-circle"></i>&nbsp;<span>'.$nbNoHk.'</span></span>';
					echo '&nbsp;&nbsp;&nbsp;';
				}
				if ($nbHalfHk > 0) {
					echo  '<span data-toggle="tooltip" data-html="true" title="'. $listHalfHk .'"><i class="glyphicon glyphicon-ban-circle"></i>&nbsp;<span>'.$nbHalfHk.'</span></span>';
					echo '&nbsp;&nbsp;&nbsp;';
				}
				if ($nbForgotSigned > 0) {
					echo  '<span data-toggle="tooltip" data-html="true" title="'. $listForgotSigned .'"><i class="glyphicon glyphicon-pencil"></i>&nbsp;<span class="">'.$nbForgotSigned.'</span></span> ';
				}
				if ($nbExtraHk > 0) {
					echo  '<span data-toggle="tooltip" data-html="true" title="'. $listExtraHk .'"><i class="glyphicon glyphicon-ok-circle"></i>&nbsp;<span>'.$nbExtraHk.'</span></span>';
				}
			} else {
				if ($nbNoHk > 0) {
					echo  '<span>'.$nbNoHk.' NoHk</span>';
					echo '&nbsp;&nbsp;';
				}
				if ($nbHalfHk > 0) {
					echo  '<span>'.$nbHalfHk.' HfHk</span>';
					echo '&nbsp;&nbsp;';
				}
				if ($nbForgotSigned > 0) {
					echo  '<span>'.$nbForgotSigned.' Sign.</span>';
					echo '&nbsp;&nbsp;&nbsp;';
				}
				echo '&nbsp;&nbsp;&nbsp;';
				if ($nbExtraHk > 0) {
					echo  '<span>'.$nbExtraHk.' XtHk</span>';
				}
			}
      echo '</td>';
			// Material
      echo '<td>';
			switch ($player->materialLabel) {
				case 'NN' : $class='primary'; break;
				case 'VV' : $class='success'; break;
				case 'V' : $class='success'; break;
				case 'R' : $class='danger'; break;
				case 'RR' : $class='danger'; break;
				default: $class = '';
			}
			echo  '<span data-toggle="tooltip" title="Nb d\'oublis : '.$nbForgotStuff.'" class="label label-'.$class.'">'.$player->materialLabel.'</span>';
      echo '</td>';
			// Participation
      echo '<td>';
			switch ($player->participation) {
				case 'NN' : $class='primary'; break;
				case 'VV' : $class='success'; break;
				case 'V' : $class='success'; break;
				case 'R' : $class='danger'; break;
				case 'RR' : $class='danger'; break;
				default: $class = '';
			}
			echo  '<span data-toggle="tooltip" data-html="true" title="Nb de cours évalués : '.$player->nbPart.'<br/>Qualité : '.$player->partRatio.'" class="label label-'.$class.'">'.$player->participation.'</span>';
			if ($player->nbPart <= 4) { echo ' <span class="label label-danger" data-toggle="tooltip" title="Peu de résultats !">!</span>'; }
			array_push($teamPart, $player->partRatio);
      echo '</td>';
			// Absent
      echo '<td>';
			if ($player->absent->count() > 0) {
				$listAbsent = '';
				foreach ($player->absent as $abs) {
					$listAbsent .= '- '.strftime("%d/%m", $abs->date).'<br />';
				}
				echo  ' <span data-toggle="tooltip" data-html="true" title="'.$listAbsent.'">['.$player->absent->count().' abs.]</span>';
			}
      echo '</td>';
			// Attitude
      echo '<td>';
			if ($player->posAttitude->count() > 0) {
				$listPosAttitude = '';
				foreach ($player->posAttitude as $e) {
					$listNegAttitude .= '- '.strftime("%d/%m", $e->date).': ['.$e->task->title.'] '.$e->summary.'<br />';
				}
			}
			if ($player->negAttitude->count() > 0) {
				$listNegAttitude = '';
				foreach ($player->negAttitude as $e) {
					$listNegAttitude .= '- '.strftime("%d/%m", $e->date).': ['.$e->task->title.'] '.$e->summary.'<br />';
				}
			}
			echo '<span data-toggle="tooltip" data-html="true" title="'.$listPosAttitude.'">'.$player->posAttitude->count().' <i class="glyphicon glyphicon-thumbs-up"></i></span> <span data-toggle="tooltip" data-html="true" title="'.$listNegAttitude.'">'.$player->negAttitude->count().' <i class="glyphicon glyphicon-thumbs-down"></i></span>';
			if ($player->nbAmbush > 0 ) { echo ' <span class="label label-danger" data-toggle="tooltip" title="\'Ambush\' dans l\'attitude !">!</span>'; }
      echo '</td>';
			// Tests
      echo '<td>';
			if ($posTests->count() > 0) {
				$listPosTests = '';
				foreach ($posTests as $e) {
					$listPosTests .= '- '.strftime("%d/%m", $e->date).': ['.$e->task->title.'] '.$e->summary.'<br />';
				}
			}
			if ($negTests->count() > 0) {
				$listNegTests = '';
				foreach ($negTests as $e) {
					$listNegTests .= '- '.strftime("%d/%m", $e->date).': ['.$e->task->title.'] '.$e->summary.'<br />';
				}
			}
			echo '<span data-toggle="tooltip" data-html="true" title="'.$listPosTests.'">'.$posTests->count().' <i class="glyphicon glyphicon-thumbs-up"></i></span>';
			echo '&nbsp;';
			echo '<span data-toggle="tooltip" data-html="true" title="'.$listNegTests.'">'.$negTests->count().' <i class="glyphicon glyphicon-thumbs-down"></i></span>';
      echo '</td>';
			// Solo missions
      echo '<td>';
			$posSolo = $player->find("template=event, task.category.name=individual-work, task.HP=0")->not('task.name=ut-action-v|ut-action-vv');
			$negSolo = $player->find("template=event, task.category.name=individual-work, task.HP<0");
			if ($posSolo->count() > 0) {
				$listPosSolo = '';
				foreach ($posSolo as $e) {
					$listPosSolo .= '- '.strftime("%d/%m", $e->date).': ['.$e->task->title.'] '.$e->summary.'<br />';
				}
			}
			if ($negSolo->count() > 0) {
				$listNegSolo = '';
				foreach ($negSolo as $e) {
					$listNegSolo .= '- '.strftime("%d/%m", $e->date).': ['.$e->task->title.'] '.$e->summary.'<br />';
				}
			}
			echo '<span data-toggle="tooltip" data-html="true" title="'.$listPosSolo.'">'.$posSolo->count().' <i class="glyphicon glyphicon-thumbs-up"></i></span>';
			echo '&nbsp;';
			echo '<span data-toggle="tooltip" data-html="true" title="'.$listNegSolo.'">'.$negSolo->count().' <i class="glyphicon glyphicon-thumbs-down"></i></span>';
      echo '</td>';
			// Group missions
      echo '<td>';
			$pos = $player->find("template=event, task.category.name=groupwork, task.HP=0")->not('task.name=ut-training-v|ut-training-vv');
			$neg = $player->find("template=event, task.category.name=groupwork, task.HP<0");
			if ($pos->count() > 0) {
				$listPos = '';
				foreach ($pos as $e) {
					$listPos .= '- '.strftime("%d/%m", $e->date).': ['.$e->task->title.'] '.$e->summary.'<br />';
				}
			}
			if ($neg->count() > 0) {
				$listNeg = '';
				foreach ($neg as $e) {
					$listNeg .= '- '.strftime("%d/%m", $e->date).': ['.$e->task->title.'] '.$e->summary.'<br />';
				}
			}
			echo '<span data-toggle="tooltip" data-html="true" title="'.$listPos.'">'.$pos->count().' <i class="glyphicon glyphicon-thumbs-up"></i></span>';
			echo '&nbsp;';
			echo '<span data-toggle="tooltip" data-html="true" title="'.$listNeg.'">'.$neg->count().' <i class="glyphicon glyphicon-thumbs-down"></i></span>';
      echo '</td>';
			// Underground Training
      echo '<td>';
			if ($utSessions->count() > 0) {
				$listUt = '';
				foreach ($utSessions as $e) {
					$listUt .= '- '.strftime("%d/%m", $e->date).': '.$e->summary.'<br />';
				}
			}
			echo '<span data-toggle="tooltip" data-html="true" title="'.$listUt.'">'.$utSessions->count().'</span>';
      echo '</td>';
			// Planet Alert
      echo '<td>';
			echo $player->places->count()+$player->people->count().' El.';
			echo ' - '.$player->equipment->count().' Eq.';
			echo ' - Lvl '.$player->level;
			if ($death->count() > 0) {
				foreach ($death as $d) {
					echo ' <span data-toggle="tooltip" title="'.strftime("%d/%m", $d->date).'" class="label label-danger">D</span>';
				}
			}
      echo '</td>';
      echo '</tr>';
    }
    
    // Team stats
    echo '<tr>';
    echo '<th>';
    echo 'Totaux et moyennes';
    echo '</th>';
		// Homework
    echo '<th>';
		if (!$input->get['pages2pdf']) {
			echo  '<span data-toggle="tooltip" title="No hk"><i class="glyphicon glyphicon-remove-circle"></i>&nbsp;<span>'.$teamNoHk.'</span></span>';
			echo '&nbsp;&nbsp;&nbsp;';
			echo  '<span data-toggle="tooltip" title="Half-hk"><i class="glyphicon glyphicon-ban-circle"></i>&nbsp;<span>'.$teamHalfHk.'</span></span>';
			echo '&nbsp;&nbsp;&nbsp;';
			echo  '<span data-toggle="tooltip" title="Extra-hk"><i class="glyphicon glyphicon-ok-circle"></i>&nbsp;<span>'.$teamExtraHk.'</span></span>';
			echo '&nbsp;&nbsp;&nbsp;';
			echo  '<span data-toggle="tooltip" title="Oubli signature"><i class="glyphicon glyphicon-pencil"></i>&nbsp;<span>'.$teamForgotSigned.'</span></span> ';
		} else {
			echo  '<span>'.$teamNoHk.' NoHk</span>';
			echo '&nbsp;&nbsp;&nbsp;';
			echo  '<span>'.$teamHalfHk.' HfHk</span>';
			echo '&nbsp;&nbsp;&nbsp;';
			echo  '<span>'.$teamExtraHk.' XtHk</span>';
			echo '&nbsp;&nbsp;&nbsp;';
			echo  '<span>'.$teamForgotSigned.' Sign.</span>';
		}
    echo '</th>';
		// Material
    echo '<th>';
		if (!$input->get['pages2pdf']) {
			echo '&nbsp;&nbsp;&nbsp;';
			echo  '<span data-toggle="tooltip" title="Oubli matériel"><i class="glyphicon glyphicon-file"></i>&nbsp;<span>'.$teamForgotStuff.'</span></span>';
		} else {
			echo '&nbsp;&nbsp;&nbsp;';
			echo  '<span>'.$teamForgotStuff.' Mat.</span>';
		}
    echo '</th>';
		// Participation
    echo '<th>';
		echo '<span data-toggle="tooltip" data-html="true" title="Qualité moyenne<br />(VV = 2 / V = 1 / R = -1 / RR = -2)">'.calculate_average($teamPart);
    echo '</th>';
		// Absent
    echo '<th>';
		echo '['.$teamAbsent.' abs.]</span>';
    echo '</th>';
		// Attitude
    echo '<th>';
		echo $teamPosAttitude.' <i class="glyphicon glyphicon-thumbs-up"></i></span>';
		echo '&nbsp;&nbsp;&nbsp;';
		echo $teamNegAttitude.' <i class="glyphicon glyphicon-thumbs-down"></i></span>';
    echo '</th>';
		// Tests
    echo '<th>';
		$teamTestRatio = round(($teamPosTests*100)/($teamPosTests+$teamNegTests));
		echo $teamTestRatio.'%';
    echo '</th>';
		// Solo missions
    echo '<th>';
    echo '</th>';
		// Groupwork
    echo '<th>';
    echo '</th>';
		// UT
    echo '<th>';
		echo $teamUt.' UT';
    echo '</th>';
		// Planet ALert
    echo '<th>';
		echo $teamEl.' El. - '. $teamEq.' Eq. - D:'.$teamDeath;
    echo '</th>';
    echo '</tr>';
?>
</table>

