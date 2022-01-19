<html>
<?php
	include("/include/head.php");
?>
	<body>
		<?php
			include("/include/header.php");
		?>
<!-- 
	================================================== 
		Page globale
	================================================== -->
	<section class="global-page-header">
	</section>

<!-- 
	================================================== 
		Company Description Section Start
	================================================== -->
	<section class="company-description">
		<div class="container">
			<div class='details'>
				<div class="form-group col-md-12">
					<?php
						// Connexion à la BDD
						require_once("include/db_instance.inc.php");
						require_once("include/db_connect.inc.php");
						
						// Récupération des données de l'hôtel choisi
						// Utilisation du binding pour éviter toute tentative d'injection par l'URL
						$db->prepare("SELECT * FROM hotel WHERE idH = :hotel");
						$db->getActualRequest()->bindParam(':hotel', $_GET["hotel"], PDO::PARAM_INT);
						$db->executeBind();
						$db->read();
						
						// Création des variables 
						$nomH = $db->getData("nomH");
						$prix = $db->getData("prix");
						$deslong = $db->getData("deslong");
						$adr1 = $db->getData("adr1");
						$adr2 = $db->getData("adr2");
						$cp = $db->getData("cp");
						$ville = $db->getData("ville");
						$tel = $db->getData("tel");
								
						// Affichage du nom de l'hôtel
						echo("<h1 class='bal-color-red block'>".$nomH."</h1>");
						
						// Récupération des photos de l'hôtel choisi
						$db->execute("SELECT * FROM photo WHERE idH = $_GET[hotel]");
						echo("<div class=\"hotel-slider\">");
						while($db->read()) {
							echo("<div><img src=\"./image/hotel/".$db->getData('nomP')."\" ></div>");
						}
						echo("</div>");
						
						// Affichage des données
						echo("<h3>Prix de la nuitée</h3><h4>$prix €</h4>");
						echo("<h3>Description</h3><div class='text'>$deslong</div>");
						echo("<h3>Adresse</h3><div class='text'>$adr1</div>");
						if ($adr2 != "") {
							echo("<div class='text'>$adr2</div>");
						}
						echo("$cp $ville");
						echo("<h3>Téléphone</h3><div class='text'>$tel</div>");
						echo("<h3>Equipements(s) :</h3>");
						// Récupération et affichage des équipements de l'hôtel
						$db->execute("SELECT * FROM equipement JOIN equiper ON equipement.idE = equiper.idE AND idH = ".$_GET["hotel"]);
						while ($db->read()) {
							echo("<img class='logo-equip' src='image/logo/".$db->getData("logoE")."' alt='".$db->getData("libE")."'>");
						}
						require_once("include/db_disconnect.inc.php");
					?>
				</div>
				<!-- Création d'un formulaire ayant toutes les données nécessaires pour se diriger vers une réservation -->
				<div class="form-group col-md-12">
					<form action="reserv.php" method="POST">
						<input type="hidden" name="cboH" value="<?php if (isset($_GET["hotel"])) { echo($_GET["hotel"]); } ?>">
						<input class="btn btn-primary btn-block btn-lg" type="submit" name="btsRes" value="Réserver">
					</form>
				</div>
			</div>
		</div>
	</section>
	</body>
<?php
	include("/include/footer.php");
?>
</html>