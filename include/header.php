		<!--
        ==================================================
						Header
        ================================================== -->
        <header id="top-bar" class="navbar-fixed-top animated-header">
            <div class="container">
                <div class="navbar-header">
                    <!-- responsive nav button -->
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    </button>
                    <!-- /responsive nav button -->
                    
                    <!-- logo -->
                    <div class="navbar-brand">
						<a href="index.php" >
							<img height="35" src="image/logo.png" alt="">
						</a>
					</div> <!-- /logo -->
                </div>
                <!-- main menu -->
                <nav class="collapse navbar-collapse navbar-right" role="navigation">
					<form action="rechercher.php" method="POST">
						<div class="main-menu">
						<!-- Barre de navigation -->
							<ul class="nav navbar-nav navbar-right">
								<li><a href="index.php">Accueil</a></li>
								<li><a href="reserv.php">Réservation</a></li>
								<li><a href="annul.php">Annulation</a></li>
								<li>
									<div class="search-bar">
										<a href="rechercher.php">Rechercher un hôtel</a>
										<input type="text" name="txtRech">
										<input class="top-bar-search-button" type="submit" value="Rechercher">
									</div>
								</li>
							</ul>
						</div>
					</form>
                </nav> <!-- /main nav -->
            </div>
        </header>