// Lorsque la page est charg�e
window.onload = function() {
	// Options du datepicker
	let options = {
		altField: ".datepicker",
		closeText: 'Fermer',
		prevText: 'Pr�c�dent',
		nextText: 'Suivant',
		currentText: 'Aujourd\'hui',
		monthNames: ['Janvier', 'F&eacute;vrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Ao&ucirc;t', 'Septembre', 'Octobre', 'Novembre', 'D&eacute;cembre'],
		monthNamesShort: ['Janv.', 'F&eacute;vr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Ao&ucirc;t', 'Sept.', 'Oct.', 'Nov.', 'D&eacute;c.'],
		dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
		dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
		dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
		weekHeader: 'Sem.',
		dateFormat: 'dd/mm/yy',
		beforeShow:function(input) {
			$(input).css({
				"position": "relative",
				"z-index": 999999
			});
		}
	};
	
	// Affichage des datepickers
	$("#datePickerDebut").datepicker(options);
	$("#datePickerFin").datepicker(options);

	let adaptHeight = false;
	// S'il s'agit d'un appareil ayant une largeur ou hauteur d'�cran inf�rieur � 600 pixels
	if ($(window).width() < 600 || $(window).height() < 600) {
		adaptHeight = true;
	}
	
	// Param�trage du slider affichant les images d'un h�tel
	$('.hotel-slider').bxSlider({
		auto: true,
		autoControls: true,
		infiniteLoop: true,
		responsive: true,
		stopAutoOnClick: true,
		touchEnabled: true,
		slideWidth: 600,
		adaptiveHeight: adaptHeight
	});
}