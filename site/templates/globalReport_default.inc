<table class="table table-condensed table-hover">
  <tr>
    <td></td>
    <th colspan="11"><h3><?php echo $reportTitle; ?></th>
  </tr>
  <tr>
		<td><?php echo $allPlayers->count().' pupils'; ?></td>
		<?php
			if (!$input->get['pages2pdf']) {
				echo '<th><i class="glyphicon glyphicon-pencil" data-toggle="tooltip" title="Compétence SACoche : Je peux présenter mon travail à la maison."></i></th>';
				echo '<th><i class="glyphicon glyphicon-file" data-toggle="tooltip" title="Compétence SACoche : J\'ai mon matériel."></i></th>';
				echo '<th><i class="glyphicon glyphicon-comment" data-toggle="tooltip" title="Compétence SACoche : Je participe en classe."></i></th>';
			} else {
				echo '<th>Hk</th>';
				echo '<th>Mat.</th>';
				echo '<th>Part.</th>';
			}
		?>
		<th>Abs.</th>
		<th><span data-toggle="tooltip" title="Compétence SACoche : J'adopte une attitude d'élève en classe.">Attitude</span></th>
		<th>Tests</th>
		<th><span data-toggle="tooltip" title="Solo missions" class="glyphicon glyphicon-user"></span></th>
		<th><span data-toggle="tooltip" title="Group missions"><span class="glyphicon glyphicon-user"></span><span class="glyphicon glyphicon-user"></span></span></th>
		<th><span data-toggle="tooltip" title="Compétence SACoche : Je prends une initiative particulière.">UT</span> <i data-toggle="tooltip" data-html="true" title="<span class='label label-success'>VV</span> 9xtHk ET 47→49UT/FP<br /><span class='label label-success'>VV</span> 10xtHk OU 50→+UT/FP<br /><span class='label label-success'>V</span> 4xtHk ET 18→19UT/FP<br /><span class='label label-success'>V</span> 5xtHK OU 20→49UT/FP" class="glyphicon glyphicon-question-sign"></i></th>
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
		$teamNegAttitude = 0;
		$teamPosAttitude = 0;
		$teamEl = 0;
		$teamEq = 0;
		$teamDeath = 0;
		$teamUt = 0;
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
			// Get rid of in-class activities
			$utSessions = $player->find("template=event, task.name=ut-action-v|ut-action-vv, inClass=0, date>=$period->dateStart, date<=$period->dateEnd");
			$posTests = $player->find("template=event, task.category.name=test, task.HP=0, inClass=0, date>=$period->dateStart, date<=$period->dateEnd");
			$negTests = $player->find("template=event, task.category.name=test, task.HP<0, inClass=0, date>=$period->dateStart, date<=$period->dateEnd");
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
					$listNoHk = '';
					foreach ($player->noHk as $e) {
						$listNoHk .= '- '.strftime("%d/%m", $e->date).' - '.$e->summary.'<br />';
					}
					echo  '<span data-toggle="tooltip" data-html="true" title="'.$listNoHk.'"><i class="glyphicon glyphicon-remove-circle"></i>&nbsp;<span>'.$nbNoHk.'</span></span>';
					echo '&nbsp;&nbsp;&nbsp;';
				}
				if ($nbHalfHk > 0) {
					$listHalfHk = '';
					foreach ($player->halfHk as $e) {
						$listHalfHk .= '- '.strftime("%d/%m", $e->date).' - '.$e->summary.'<br />';
					}
					echo  '<span data-toggle="tooltip" data-html="true" title="'.$listHalfHk.'"><i class="glyphicon glyphicon-ban-circle"></i>&nbsp;<span>'.$nbHalfHk.'</span></span>';
					echo '&nbsp;&nbsp;&nbsp;';
				}
				if ($nbForgotSigned > 0) {
					$listForgotSigned = '';
					foreach ($player->notSigned as $e) {
						$listForgotSigned .= '- '.strftime("%d/%m", $e->date).' - '.$e->summary.'<br />';
					}
					echo  '<span data-toggle="tooltip" data-html="true" title="'.$listForgotSigned.'"><i class="glyphicon glyphicon-pencil"></i>&nbsp;<span class="">'.$nbForgotSigned.'</span></span> ';
				}
				if ($nbExtraHk > 0) {
					$listExtraHk = '';
					foreach ($player->extraHk as $e) {
						$listExtraHk .= '- '.strftime("%d/%m", $e->date).' - '.$e->summary.'<br />';
					}
					echo  '<span data-toggle="tooltip" data-html="true" title="'.$listExtraHk.'"><i class="glyphicon glyphicon-ok-circle"></i>&nbsp;<span>'.$nbExtraHk.'</span></span>';
				}
			} else {
				if ($nbNoHk > 0) {
					echo  '<span>'.$nbNoHk.' No</span>';
					echo '&nbsp;&nbsp;';
				}
				if ($nbHalfHk > 0) {
					echo  '<span>'.$nbHalfHk.' Mid</span>';
					echo '&nbsp;&nbsp;';
				}
				if ($nbForgotSigned > 0) {
					echo  '<span>'.$nbForgotSigned.' Sign.</span>';
					echo '&nbsp;&nbsp;&nbsp;';
				}
				echo '&nbsp;&nbsp;&nbsp;';
				if ($nbExtraHk > 0) {
					echo  '<span>'.$nbExtraHk.' Xt</span>';
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
      if ($input->get['pages2pdf']) {
				echo ' ('.$nbForgotStuff.')';
			}
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
      if ($input->get['pages2pdf']) {
				echo ' ('.$player->nbPart.' c., '.$player->partRatio.' q.';
			}
      echo '</td>';
			// Absent
      echo '<td>';
			$listAbsent = '';
			if ($player->absent->count() > 0) {
				foreach ($player->absent as $abs) {
					$listAbsent .= '- '.strftime("%d/%m", $abs->date).'<br />';
				}
				echo  ' <span data-toggle="tooltip" data-html="true" title="'.$listAbsent.'">['.$player->absent->count().' abs.]</span>';
			}
      echo '</td>';
			// Attitude
      echo '<td>';
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
			echo '<span data-toggle="tooltip" data-html="true" title="'.$listPosAttitude.'">'.$player->posAttitude->count().' <i class="glyphicon glyphicon-thumbs-up"></i></span> <span data-toggle="tooltip" data-html="true" title="'.$listNegAttitude.'">'.$player->negAttitude->count().' <i class="glyphicon glyphicon-thumbs-down"></i></span>';
			if ($player->nbAmbush > 0 ) { 
				if (!$input->get['pages2pdf']) {
					echo ' <span class="label label-danger" data-toggle="tooltip" title="\'Ambush\' dans l\'attitude !">!</span>';
				} else {
					echo ' <span class="label label-danger">A!</span>';
				}
			}
      echo '</td>';
			// Tests
      echo '<td>';
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
			echo '<span data-toggle="tooltip" data-html="true" title="'.$listPosTests.'">'.$posTests->count().' <i class="glyphicon glyphicon-thumbs-up"></i></span>';
			echo '&nbsp;';
			echo '<span data-toggle="tooltip" data-html="true" title="'.$listNegTests.'">'.$negTests->count().' <i class="glyphicon glyphicon-thumbs-down"></i></span>';
      echo '</td>';
			// Solo missions
      echo '<td>';
			$posSolo = $player->find("template=event, task.category.name=individual-work, task.HP=0, date>=$period->dateStart, date<=$period->dateEnd")->not('task.name=ut-action-v|ut-action-vv');
			$negSolo = $player->find("template=event, task.category.name=individual-work, task.HP<0, date>=$period->dateStart, date<=$period->dateEnd");
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
			echo '<span data-toggle="tooltip" data-html="true" title="'.$listPosSolo.'">'.$posSolo->count().' <i class="glyphicon glyphicon-thumbs-up"></i></span>';
			echo '&nbsp;';
			echo '<span data-toggle="tooltip" data-html="true" title="'.$listNegSolo.'">'.$negSolo->count().' <i class="glyphicon glyphicon-thumbs-down"></i></span>';
      echo '</td>';
			// Group missions
      echo '<td>';
			$pos = $player->find("template=event, task.category.name=groupwork, task.HP=0, date>=$period->dateStart, date<=$period->dateEnd")->not('task.name=ut-training-v|ut-training-vv');
			$neg = $player->find("template=event, task.category.name=groupwork, task.HP<0, date>=$period->dateStart, date<=$period->dateEnd");
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
			echo '<span data-toggle="tooltip" data-html="true" title="'.$listPos.'">'.$pos->count().' <i class="glyphicon glyphicon-thumbs-up"></i></span>';
			echo '&nbsp;';
			echo '<span data-toggle="tooltip" data-html="true" title="'.$listNeg.'">'.$neg->count().' <i class="glyphicon glyphicon-thumbs-down"></i></span>';
      echo '</td>';
			// Underground Training
      echo '<td>';
			$listUt = '';
			if ($utSessions->count() > 0) {
				foreach ($utSessions as $e) {
					$listUt .= '- '.strftime("%d/%m", $e->date).': '.$e->summary.'<br />';
				}
			}
			echo '<span data-toggle="tooltip" data-html="true" title="'.$listUt.'">'.$utSessions->count().'</span>';
			if ($player->motivation == 'V' || $player->motivation == 'VV') {
				echo  ' <span class="label label-success" data-toggle="tooltip" title="'.$player->ut->count().'UT/'.$player->fp->count().'FP">'.$player->motivation.'</span> ';
			}
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
			echo  '<span>'.$teamNoHk.' No</span>';
			echo '&nbsp;&nbsp;&nbsp;';
			echo  '<span>'.$teamHalfHk.' Mid</span>';
			echo '&nbsp;&nbsp;&nbsp;';
			echo  '<span>'.$teamExtraHk.' Xt</span>';
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
		if ($teamPosTests > 0 || $teamNegTests > 0) {
			$teamTestRatio = round(($teamPosTests*100)/($teamPosTests+$teamNegTests));
		} else {
			$teamTestRatio = "-";
		}
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

