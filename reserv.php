<html>
<?php
	// Ajout du head
	include("/include/head.php");
?>
	<body>
		<?php
			// Ajout du header
			include("/include/header.php");
		?>
	
        <!-- 
        ================================================== 
            Page globale
        ================================================== -->
        <section class="global-page-header">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="block">
                            <h2>Réservation</h2>
                            <ol class="breadcrumb">
                                <li>
                                    <a href="index.php">
                                        <i class="ion-ios-home"></i>
                                        Accueil
                                    </a>
                                </li>
                                <li class="active">Réservation</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </section>


	<!-- 
		================================================== 
			Company Description Section Start
		================================================== -->
		<section class="company-description">
			<div class="container">
				<form method="POST">
 					<?php
						// Récupération de l'instance et connexion à la base de données
						require_once("include/db_instance.inc.php");
						require_once("include/db_connect.inc.php");
						
						// Fonction de génération d'un code aléatoire
						function getRandomCode() {
							// Définition des caractères utilisables pour la génération d'un code
							$caracts = "0123456789";
							// Création de l'array contenant le futur mot de passe
							$pass = array();
							// Définition de la taille de la liste des caractères pour la futur récupération aléatoire
							$caractsLength = strlen($caracts) - 1;
							// Taille du code devant être généré
							$codeLength = 4;
							for ($i = 0; $i < $codeLength; $i++) {
								// Récupération d'une lettre aléatoirement
								$n = rand(0, $caractsLength);
								$pass[] = $caracts[$n];
							}
							// Transformation de l'array en string
							return implode($pass);
						}
						
						// Vérification que tous les champs sont bien présents
						// Si c'est le cas c'est que le client souhaite réserver
						if (isset($_POST["cboH"]) && $_POST["cboH"] != "" &&
							isset($_POST["datePickerDebut"]) && $_POST["datePickerDebut"] != "" &&
							isset($_POST["datePickerFin"]) && $_POST["datePickerFin"] != "" &&
							isset($_POST["txtNom"]) && $_POST["txtNom"] != "" &&
							isset($_POST["txtTel"]) && $_POST["txtTel"] != "" &&
							isset($_POST["txtMail"]) && $_POST["txtMail"] != "" &&
							isset($_POST["cboNbChambres"]) && $_POST["cboNbChambres"] != "" &&
							isset($_POST["btnReserver"])) {
							// Génération du code aléatoire
							$code = getRandomCode();
							// Récupération des données de la page précédente
							$idH = $_POST["cboH"];
							$nom = $_POST["txtNom"];
							$telephone = $_POST["txtTel"];
							$mail = $_POST["txtMail"];
							$datePickerDebut = $_POST["datePickerDebut"];
							$datePickerFin = $_POST["datePickerFin"];
							$nbChambres = $_POST["cboNbChambres"];
							
							// Vérification de l'existence d'une réservation à ce nom et à cette date
							// Utilisation de bind pour éviter toute injection par les champs
							$db->prepare("SELECT idR FROM reservation WHERE nomR = :nom AND telR = :telephone ".
												"AND datedebut = '$datePickerDebut' AND datefin = '$datePickerFin' ".
												"AND idH = :idH");
							$db->getActualRequest()->bindParam(':nom', $nom, PDO::PARAM_STR);
							$db->getActualRequest()->bindParam(':telephone', $telephone, PDO::PARAM_STR);
							$db->getActualRequest()->bindParam(':idH', $idH, PDO::PARAM_INT);
							$db->executeBind();
							
							$db->read();
							// Si la réservation n'existe pas l'enregistrement est effectué
							if ($db->getData("idR") == NULL) {
								
								// Récupération des chambres à réserver
								// Utilisation de bind pour éviter toute injection par les champs
								$chambres = array();
								$db->prepare("SELECT TOP $nbChambres idC FROM chambre ".
											 "WHERE idH = :idH AND ".
											 "idC NOT IN (SELECT idC FROM reservation R INNER JOIN reserver Res ".
											 "ON R.idR = Res.idR ".
											 "WHERE R.idH = $idH ".
											 "AND ('$datePickerDebut' >= R.datedebut AND '$datePickerDebut'< R.datefin) ".
											 "OR ('$datePickerFin' >= R.datedebut AND '$datePickerFin' < R.datefin))");
								$db->getActualRequest()->bindParam(':idH', $idH, PDO::PARAM_INT);
								$db->executeBind();
								// Remplissage de la liste de chambres
								while($db->read()) {
									array_push($chambres, $db->getData("idC"));
								}
								
								// Insertion de la réservation
								// Utilisation de bind pour éviter toute injection par les champs
								$db->prepare("INSERT INTO reservation (nomR, telR, codeR, datedebut, datefin, idH) ".
											 "VALUES (:nom, :telephone, '$code', '$datePickerDebut', '$datePickerFin', :idH)");
								$db->getActualRequest()->bindParam(':nom', $nom, PDO::PARAM_STR);
								$db->getActualRequest()->bindParam(':telephone', $telephone, PDO::PARAM_STR);
								$db->getActualRequest()->bindParam(':idH', $idH, PDO::PARAM_INT);
								$db->executeBind();
								// Récupération du numéro de réservation
								// Utilisation de bind pour éviter toute injection par les champs
								$db->prepare("SELECT idR FROM reservation WHERE nomR = :nom AND codeR = '$code'");
								$db->getActualRequest()->bindParam(':nom', $nom, PDO::PARAM_STR);
								$db->executeBind();
								$db->read();
								
								// Si le numéro de réservation est null c'est que la requête a échouée
								// Dans ce cas il s'agit très probablement d'une tentative d'injection XSS en base de données
								if ($db->getData("idR") != NULL) {
									$idR = $db->getData("idR");
								
									// Insertion de la liste des chambres
									// Utilisation de bind pour éviter toute injection par modification de valeur
									foreach($chambres as $idC) {
										$db->prepare("INSERT INTO reserver VALUES ($idR, $idC, :idH)");
										$db->getActualRequest()->bindParam(':idH', $idH, PDO::PARAM_INT);
										$db->executeBind();
									}
									// Destruction du dernier objet de la boucle
									unset($idC);
									
									// Définition du message à afficher, utilisation du <br/> pour que le retour à la ligne fonctionne bien dans le mail
									// La fonction htmlspecialchars est utilisée pour éviter qu'une injection XSS soit executée
									$message = "<h3>Votre réservation de <b>$nbChambres</b> chambre(s) au nom de <b>".htmlspecialchars($nom)."</b> a bien été effectuée.<br/>".
											   "Votre réservation est la n°<b>$idR</b>. Votre code d'accès est : <b>$code</b>.<br/>".
											   "Veillez à ne pas communiquer ces informations.</h3>";
											   
									// Suppression des avertissements lorsque le mail ne peut pas être envoyé
									// Cette précaution est dû à un manque de serveur SMTP pour envoyer le mail
									@mail($mail, 'Confirmation de réservation', "Bonjour M./Mme ".htmlspecialchars($nom).",<br/>".$message);
									
									// Affichage des informations de réservation
									echo("<div class='alert alert-success'>$message<h3 class='block'>Un mail de confirmation vous sera envoyé dans les plus brefs délais.</h3></div>");
								} else {
									// Avertissement qu'il y a eu une erreur lors de l'inscription de la réservation en base de données
									echo("<div class='alert alert-danger'><h3>Erreur lors de l'enregistrement de votre réservation.</h3></div>");
								}
							} else {
								// Avertissement que la réservation est déjà présente en base de données
								echo("<div class='alert alert-danger'><h3>Votre réservation a déjà été enregistrée.</h3s></div>");
							}
						} else {
					?>
					<div class="form-group col-md-12">
						<label for="cboH">Choisissez votre hôtel</h4>
						<!-- Chargement de la liste déroulante d'hôtel -->
						<select id="cboH" name="cboH" class='form-control col-md-6' onchange="this.form.submit();">
						
						<!-- Ajout d'une ligne blanche -->
						<option value=""></option>
						<?php						
							// Récupération de tous les hôtels
							$db->execute("SELECT * FROM hotel");
							
							while($db->read()) {
								// Ajout de la ligne dans le cbo, s'il était sélectionné on le resélectionne
								echo("<option value='".$db->getData("idH")."'");
								if (isset($_POST["cboH"]) && $_POST["cboH"] == $db->getData("idH"))
									echo(" selected='selected'");
								echo("'>".$db->getData("nomH")."</option>");
							}
						?>
						</select>
					</div>
				</form>
				<form method="POST">
					<!-- Chargement de la liste déroulante de projections -->
					<?php
					// Vérification qu'un hôtel est sélectionné
						if (isset($_POST["cboH"]) && $_POST["cboH"] != "") {
							// Récupération des valeurs précédentes
							echo("<input type='hidden' name='cboH' value='$_POST[cboH]' />");
							// Affichage du choix des dates
							// Si une date est sélectionnée la page est rechargée pour vérifier si les deux champs
							// de date sont remplis ou non							
							echo("<div class='form-group col-md-6'>");
							echo("<label for='datePickerDebut'>Date d'arrivée</label><input id='datePickerDebut' name='datePickerDebut' class='form-control' ".
								 "type='text' value='");
							if (isset($_POST['datePickerDebut']))
								echo($_POST['datePickerDebut']);
							echo("' onchange='form.submit();'>");
							echo("</div>");
							echo("<div class='form-group col-md-6'>");
							echo("<label for='datePickerFin'>Date de départ</label><input id='datePickerFin' name='datePickerFin' class='form-control' ".
								 "type='text' value='");
							if (isset($_POST['datePickerFin']))
								echo($_POST['datePickerFin']);
							echo("' onchange='form.submit();'>");
							echo("</div>");
						}
					?>
				</form>
				<form method="POST">
					<?php
					// Vérification des champs s'il y a bien des dates de sélectionnées
						if (isset($_POST['datePickerDebut']) && $_POST['datePickerDebut'] != ""
							&& isset($_POST["datePickerFin"]) && $_POST["datePickerFin"] != "") {
							// Récupération des valeurs précédentes
							echo("<input type='hidden' name='cboH' value='$_POST[cboH]' />");
							echo("<input type='hidden' name='datePickerDebut' value='$_POST[datePickerDebut]' />");
							echo("<input type='hidden' name='datePickerFin' value='$_POST[datePickerFin]' />");
							// Demande à l'utilisateur de bien remplir tous les champs
							echo("<div class='form-group col-md-12'>");
							echo("<h5>Veillez à bien remplir tous les champs</h5>");
							echo("</div>");
							// Champ de nom
							echo("<div class='form-group col-md-6'>");
							echo("<label for='txtNom'>Nom de la réservation</label><input id='txtNom' name='txtNom' type='text' class='form-control' value='");
							if (isset($_POST['txtNom']))
								echo($_POST['txtNom']);
							echo("' />");
							echo("</div>");
							// Champ de téléphone
							echo("<div class='form-group col-md-6'>");
							echo("<label for='txtTel'>Numéro de téléphone</label><input id='txtTel' name='txtTel' type='tel' class='form-control' value='");
							if (isset($_POST['txtTel']))
								echo($_POST['txtTel']);
							echo("' />");
							echo("</div>");
							// Champ d'adresse mail
							echo("<div class='form-group col-md-12'>");
							echo("<label for='txtMail'>Adresse mail</label><input id='txtMail' name='txtMail' type='email' class='form-control' value='");
							if (isset($_POST['txtMail']))
								echo($_POST['txtMail']);
							echo("' />");
							echo("</div>");
							
							// Début du champ contenant le nombre de chambres restantes
							echo("<div class='form-group col-md-6'>");
							echo("<label for='txtChambresRest'>Nombre de chambres restantes</label>");
							echo("<input id='txtChambresRest' name='txtChambresRest' type='text' class='form-control' disabled='true' value='");
							
							// Récupération du nombre de chambres disponibles dans l'hôtel
							// Utilisation du bind pour éviter toute injection en cas de modification de données
							$datePickerDebut = $_POST['datePickerDebut'];
							$datePickerFin = $_POST["datePickerFin"];
							$db->prepare("SELECT COUNT(idC) nbChambres FROM chambre ".
										 "WHERE idH = :idH AND ".
										 "idC NOT IN (SELECT idC FROM reservation R INNER JOIN reserver Res ".
										 "ON R.idR = Res.idR ".
										 "WHERE R.idH = ".$_POST["cboH"]." ".
										 "AND ('".$datePickerDebut."' >= R.datedebut AND '".
												    $datePickerDebut."'< R.datefin) ".
										 "OR ('".$datePickerFin."' >= R.datedebut AND '".
												   $datePickerFin."' < R.datefin))");
							$db->getActualRequest()->bindParam(':idH', $_POST["cboH"], PDO::PARAM_INT);
							$db->executeBind();
							$db->read();
							// Affichage du nombre de chambres disponibles
							$nbChambres = $db->getData("nbChambres");
							echo($nbChambres);
							// Fin du champ contenant le nombre de chambres restantes
							echo("' />");
							echo("</div>");
							
							// Affichage du cbo proposant le nombre de chambres à réserver, il y a de 1 à n places
							echo("<div class='form-group col-md-6'>");
							echo("<label for='cboNbChambres'>Nombre de chambres à réserver</label><select id='cboNbChambres' name='cboNbChambres' class='form-control'>");
							for ($i = 1; $i <= $nbChambres; $i++) {
								// Ajout de la ligne dans le cbo, s'il était sélectionné on le resélectionne
								echo("<option value='$i'");
								if (isset($_POST["cboNbChambres"]) && $_POST["cboNbChambres"] == $i)
									echo(" selected='selected'");
								echo(">$i</option>");
							}
							echo("</select>");
							echo("</div>");
							// Demande de confirmation à l'utilisateur
							echo("<div class='form-group col-md-12'>");
							echo("<input class='btn btn-primary btn-block btn-lg' type='submit' name='btnReserver'".
									"onclick=\"return confirm('Êtes vous sûr de vouloir réserver ?')\" value='Réserver' />");
							echo("</div>");
						}
					?>
				</form>
				<?php
					// Fin du if
						require_once("include/db_disconnect.inc.php");
					}
				?>
			</div>
		</section>
	</body>
<?php
	// Déconnexion de la BDD
	require_once("include/db_disconnect.inc.php");
	include("/include/footer.php");
?>
</html>