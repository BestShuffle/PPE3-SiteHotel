Planning du
<?php
	// Définition de la zone de temps locale et de la langue à utiliser
	date_default_timezone_set('Europe/Paris');
	setlocale(LC_TIME, 'fr_FR.utf8','fra');
	// Récupération de la date du début et de la fin de la semaine
	list($start_date, $end_date) = x_week_range(date("Y-m-d"));
	echo(strftime("%A %d %B", strtotime($start_date))
		." au "
		.strftime("%A %d %B %Y", strtotime($end_date)));
	// Création d'une liste de jours comprenant les dates du lundi au dimanche
	$days = [];
	for ($i = 0; $i < 7; $i++) {
		$tmp_date = new DateTime($start_date);
		// On avance de jour en jour
		$tmp_date->add(new DateInterval('P'.$i.'D'));
		array_push($days, $tmp_date);
	}
	
	// Fonction récupérant la date du début et de la fin de la semaine
	function x_week_range($date) {
		$ts = strtotime($date);
		$start = (date('w', $ts) == 0) ? $ts : strtotime('last monday', $ts);
		return array(date('Y-m-d', $start),
					 date('Y-m-d', strtotime('next sunday', $start)));
	}
	
	// Connexion à la BDD
	require_once("/includes/db_instance.inc.php");
	require_once("/includes/db_connect.inc.php");
	
	// Récupération du nombre de projections le plus haut pour obtenir le nombre de lignes du tableau à générer
	$db->execute("SELECT dateproj, COUNT(*) nb
				  FROM projection
				  WHERE dateproj BETWEEN '$start_date' AND '$end_date'
				  GROUP BY dateproj
				  HAVING COUNT(*) >= ALL (SELECT COUNT(*) nb
										  FROM projection
										  WHERE dateproj BETWEEN '$start_date' AND '$end_date'
										  GROUP BY dateproj)");
				  
					
	$db->read();
	$nbRow = $db->getData("nb");
	
	// Définition des jours d'une semaine pour une utilisation ultérieur
	$week = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
	
	// Création du tableau de planning qui servira de modèle pour la génération du tableau HTML
	$planning = [];
	
	// Création des lignes dans le tableau
	for ($i = 0; $i < $nbRow; $i++) {
		array_push($planning, ["Lundi"=>"", "Mardi"=>"" , "Mercredi"=>"", "Jeudi"=>"", "Vendredi"=>"", "Samedi"=>"", "Dimanche"=>""]);
	}
	// Récupération des films pour chaque jour de la semaine, un for est plus rapide qu'un foreach dans ce cas (variable $i présente par défaut) 
	for ($day = 0; $day < count($days); $day++)
	{
		$db->execute("SELECT * FROM projection, film WHERE projection.nofilm = film.nofilm AND dateproj = '".$days[$day]->format('Y-m-d')."' ORDER BY heureproj");
		$nbProj = 0;
		while ($db->read())
		{
			// Récupération du jour actuel de la semaine avec majuscule en début du mot
			$dayLib = ucfirst(strftime("%A", $days[$day]->getTimestamp()));
			// Récupération de l'heure de la projection
			$heureproj = strtotime($db->getData("heureproj"));
			// Ecriture des données dans la cellule correspondant à la projection
			$planning[$nbProj][$dayLib] = "<a href='affiche.php?film=".$db->getData("nofilm")."'>".$db->getData("titre")
			."</a><br/>Salle ".$db->getData("nosalle")." - ".strftime("%Hh%M", $heureproj);
			$nbProj++;
		}
	}
	
	require_once("/includes/db_disconnect.inc.php");
?>

<!-- Génération du tableau de planning HTML à partir du planning PHP -->
<?php if (count($planning) > 0): ?>
<table>
	<thead>
		<tr>
			<th width="14.35%"><?php echo implode('</th><th width="14.35%">', array_keys(current($planning))); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($planning as $row): array_map('htmlentities', $row); ?>
		<tr>
			<td><?php echo implode('</td><td>', $row); ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<?php
	echo("<a href='pdf.php?start_date=$start_date&end_date=$end_date'>Générer le planning sous PDF</a>");
	else:
		$html= "Aucune projection prevue.";
	endif;
?>                     