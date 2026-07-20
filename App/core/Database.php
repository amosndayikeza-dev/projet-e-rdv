# Connexion et exécution des requêtes

<?php
    namespace ap\core; 
    class database{
        private static $instance=null;

        private function __construct(){}

        // getter pour instance 

        public static function getInstance(){
            if (self::$instance==null){
                self::$instance=new PDO(
                    "mysql:host:localhost database_name= medi_map",
                    "",
                    "root"
                );
            }
        }
        return self::$instance
    }
?>