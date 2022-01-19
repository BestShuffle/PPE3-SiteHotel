<?php	
class Database
{
	private static $_instance = null;
	
	private $_cnn;
	private $_actualRequest;
	private $_reader;

	/**
	  * Constructeur de la classe
	  */
	private function __construct() {
		$this->_cnn = null;
		$this->_actualRequest = null;
		$this->_reader = null;
	}
 
    /**
      * Méthode qui crée l'unique instance de la classe
      * si elle n'existe pas encore puis la retourne.
      *
      * @param void
      * @return Database
      */
	public static function getInstance() {
		if(is_null(self::$_instance)) {
			self::$_instance = new Database();
		}
		return self::$_instance;
	}
	
	/**
	  * Fonction de connexion à la base de données
	  */
	public function connect() {
		require_once("/config/config.inc.php");
		self::$_instance->_cnn = new PDO("odbc:Driver={SQL Server};Server=$dbhost;Database=$dbname;Uid=$dbuser;Pwd=$dbpass;");
	}
	
	/**
	  * Fonction de déconnexion à la base de données
	  */
	public function disconnect() {
		// Fermeture du curseur de la requête avant déconnexion
		$this->closeCursor();
		self::$_instance->_cnn = null;
	}
	
	/**
	  * Fonction de préparation de requête
	  */
	public function prepare($request) {
		$this->_actualRequest = $this->_cnn->prepare($request);
	}
	
	/**
	  * Fonction d'envoi d'exécution de requête
	  */
	public function executeBind() {
		$this->_actualRequest->execute();
	}
	
	/**
	  * Fonction d'envoi de requête avec récupération de données
	  */
	public function execute($request) {
		$this->_actualRequest = $this->_cnn->prepare($request);
		$this->_actualRequest->execute();
	}
	
	/**
	  * Fonction de récupération d'une donnée dans le reader
	  */
	public function getData($data) {
		return utf8_encode($this->_reader[$data]);
	}
	
	/**
	  * Fonction abstraite de récupération d'une donnée dans le reader
	  */
	public function getDataReader($data, $reader) {
		return utf8_encode($reader[$data]);
	}
	
	/**
	  * Fonction de récupération de la requête actuelle
	  */
	public function getActualRequest() {
		return $this->_actualRequest;
	}
	
	/**
	  * Fonction de récupération de la requête 
	  */
	public function getReader() {
		return $this->_reader;
	}
	
	/**
	  * Fonction de lecture des données retournées par la
	  * requête actuelle
	  */
	public function read() {
		return $this->_reader = $this->_actualRequest->fetch();
	}
	
	/**
	  * Fonction de fermeture du curseur de la requête actuelle
	  */
	public function closeCursor() {
		$this->_actualRequest->closeCursor();
	}
}
?>