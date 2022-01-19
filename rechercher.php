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
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="block">
                            <h2>Recherche</h2>
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
			<!-- Affichage du formulaire de recherche -->
				<form method="POST">
					<div class="form-group col-md-9">
					<input type="text" name="txtRech" class="form-control" size="30" value="<?php if (isset($_POST["txtRech"])) echo($_POST["txtRech"]); ?>" >
					</div>
					<div class="form-group col-md-3">
					<input type="submit" name="btnRechercher" class="form-control" value="Rechercher" />
					</div>
				</form>
				<?php	
					require_once("/include/db_instance.inc.php");
					require_once("/include/db_connect.inc.php");

					$foundHotels = array();

					// Vérification que l'entrée utilisateur fait au moins 3 caractères
					if (isset($_POST["txtRech"]) && strlen($_POST["txtRech"]) >= 3) {
						$txtRech = $_POST["txtRech"];
						research($db, $foundHotels, $txtRech);
					} else {
						showHotels($db);
					}

					// Recherche par nom, descriptions et adresse (complète)
					function research($db, &$foundHotels, $txt) {
						// Recherche par nom
						researchBy($db, $foundHotels, $txt, "nomH");
						// Recherche par description
						researchBy($db, $foundHotels, $txt, "descourt");
						researchBy($db, $foundHotels, $txt, "deslong");
						// Recherche par adresse
						researchBy($db, $foundHotels, $txt, "cp");
						researchBy($db, $foundHotels, $txt, "adr1");
						researchBy($db, $foundHotels, $txt, "adr2");
						researchBy($db, $foundHotels, $txt, "ville");
						
						// Si aucun hôtel n'est trouvé l'utilisateur est prévenu
						if (count($foundHotels) == 0) {
							echo("<div class='form-group col-md-12'>");
							echo("<div class='alert alert-danger'><h3>Aucun résultat ne correspond à votre recherche.</h3></div>");
							echo("</div>");
						}
					}

					// Fonction de recherche par champ voulu
					function researchBy($db, &$foundHotels, $txt, $field) {
						// Ajout de pourcents au début et à la fin de la chaîne de caractères
						// Ces pourcents correspondents à ceux présents dans le like de la requête
						$txt = "%$txt%";
						// Utilisation du binding de paramètres pour éviter toute injection par le champ de recherche
						$db->prepare("SELECT * FROM hotel JOIN photo ON hotel.idH = photo.idH WHERE $field LIKE :txt");
						$db->getActualRequest()->bindParam(':txt', $txt, PDO::PARAM_STR);
						$db->executeBind();
						while ($db->read()) {
							// Utilisation de la liste des hôtels trouvés pour éviter d'afficher deux fois le même hôtel
							if (!in_array($db->getData("idH"), $foundHotels)) {
								array_push($foundHotels, $db->getData("idH"));
								showActualHotel($db);
							}
						}
					}

					// Fonction de récupération de tous les hôtels
					function showHotels($db) {
						$db->execute("SELECT * FROM hotel JOIN photo ON hotel.idH = photo.idH");

						while ($db->read())
						{
							showActualHotel($db);
						}
					}
					
					// Fonction d'affichage de l'hôtel actuel
					function showActualHotel($db) {
						// Utilisation de la liste des hôtels trouvés pour éviter d'afficher deux fois un hôtel
						$idH = $db->getData("idH");
						// Affichage de l'hôtel
						echo("<div class='row'>");
							echo("<form action='details.php' method='GET'>");
								// Affichage du lien vers la page de détails avec animation d'apparition
								echo("<a href='details.php?hotel=".$idH."'><h2>".$db->getData("nomH")."</h2></a><br/>");
								echo("<div class='col-md-2 wow fadeInLeft' data-wow-delay='.3s' >");
									echo("<img src='image/hotel/".$db->getData("nomP")."' width='200' />");
								echo("</div>");
								// Affichage des informations de l'hôtel
								echo("<div class='col-md-10'>");
									echo("<div class='block'>");
										echo("<div>");
											echo("<ul class='lis2'>");
													echo("<section class='pres-rech wow fadeInUp animated cd-headline' data-wow-delay='.3s'>");
													echo("<h4 class='bold'>Description</h4><span>".$db->getData("descourt"));
													echo("<h4 class='bold'>Adresse</h4>".$db->getData("adr1")."<br/>");
													if ($db->getData("adr2") != "") {
														echo($db->getData("adr2")."<br/>");
													}
													echo($db->getData("cp")." ".$db->getData("ville"));
													echo("<h4 class='bold'>Prix de la nuitée</h4><span>".$db->getData("prix")." €");
													// Ajout d'une donnée cachée contenant le numéro de l'hôtel et d'un bouton d'accès
													// aux détails de l'hôtel
													echo("<input type='hidden' name='hotel' value='".$idH."'>");
													echo("<input class='details-button' type='submit' value='Détails'>");
												echo("</section>");
											echo("</ul>");
										echo("</div>");
									echo("</div>");
								echo("</div>");
							echo("</form>");
						echo("</div>");
						echo("<hr style='clear:both' />");
					}
					require_once("/include/db_disconnect.inc.php");
				?>
			</div>
		</section>
	</body>
	<?php
		include("/include/footer.php");
	?>
</html>