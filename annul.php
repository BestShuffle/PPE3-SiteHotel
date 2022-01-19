<html class="no-js">
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
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="block">
                            <h2>Annulation</h2>
                            <ol class="breadcrumb">
                                <li>
                                    <a href="index.php">
                                        <i class="ion-ios-home"></i>
                                        Accueil
                                    </a>
                                </li>
                                <li class="active">Annulation</li>
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
				<?php	
					require_once("/include/db_instance.inc.php");

					// Si l'utilisateur a validé l'annulation
					if (isset($_POST["txtNum"]) && $_POST["txtNum"] != "" &&
						isset($_POST["txtCode"]) && $_POST["txtCode"] != "" &&
						isset($_POST["btnAnnuler"])) {
						// Vérification que les données récupérées sont valides
						if(deleteReserv($db, $_POST["txtNum"], $_POST["txtCode"])) {
							// Définition du message à afficher
							$message = "<h3>Votre réservation a bien été annulée.</h3>";
							// Envoi d'un mail de confirmation
							// Suppression des avertissements lorsque le mail ne peut pas être envoyé
							// Cette précaution est dû à un manque de serveur SMTP pour envoyer le mail
							@mail($mail, 'Confirmation de réservation', "Bonjour M./Mme $nom,<br/>".$message);
							
							echo("<div class='alert alert-success'>$message<h3 class='block'>Un mail de confirmation vous sera envoyé dans les plus brefs délais.</h3></div>");
						} else {
							echo("<div class='alert alert-danger'><h3>Valeur(s) entrée(s) incorrecte(s).</h3></div>");
						}
					} else {
						// Affichage du formulaire d'annulation
						echo("<form method='POST'>");
							echo("<h3>Entrez vos informations de réservation</h3>");
							echo("<h5>Veillez à bien remplir tous les champs</h5>");
							echo("<div class='form-group col-md-6'>");
							echo("<label for='txtNum'>Réservation n°</label><input id='txtNum' name='txtNum' type='text' class='form-control' size='10' />");
							echo("</div>");
							echo("<div class='form-group col-md-6'>");
							echo("<label for='txtCode'>Code d'accès</label><input id='txtCode' name='txtCode' type='text' class='form-control' size='10' />");
							echo("</div>");
							echo("<div class='form-group col-md-12'>");
							echo("<input  class='form-control' type='submit' name='btnAnnuler'".
									"onclick=\"return confirm('Êtes-vous sur de vouloir annuler votre réservation ?')\" value='Annuler' />");
							echo("</div>");
						echo("</form>");
					}
					
					// Fonction de vérification du numéro de réservation et code d'accès
					function deleteReserv($db,$num,$code) {
						$result = true;
						
						require_once("/include/db_connect.inc.php");
						$db->execute("SELECT idR, codeR FROM reservation WHERE idR = $num");
						$db->read();
						// Si la requête retourne une ligne et que le code est le bon tout est valide
						// les données peuvent être supprimées
						if ($db->getData("idR") != NULL && $db->getData("codeR") == $code) {
							// Utilisation du binding pour éviter toute tentative d'injection SQL par les champs
							$db->prepare("DELETE FROM reserver WHERE idR = :num");
							$db->getActualRequest()->bindParam(':num', $num, PDO::PARAM_INT);
							$db->executeBind();
							$db->prepare("DELETE FROM reservation WHERE idR = :num");
							$db->getActualRequest()->bindParam(':num', $num, PDO::PARAM_INT);
							$db->executeBind();
						} else {
							// Sinon, la fonction retourne false pour indiquer qu'une valeur est invalide
							$result = false;
						}
						require_once("/include/db_disconnect.inc.php");
						
						return $result;
					}
				?>
            </div>
        </section>
		
        
    <?php
		include("/include/footer.php");
	?>
</html>