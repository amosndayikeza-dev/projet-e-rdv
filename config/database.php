<?php
class DataBase{
    private string $nomUtilisateur = "root";
    private string $password = "";
    private string $hote = "localhost";
    private string $nomBD = "projet-e-rdv";
    private PDO $pdo;
    public function __construct()
    {
        try{
            $this-> pdo = new PDO("mysql:hostname={$this->hote};dbname={$this->nomBD",$this->nomUtilisateur,$this->password);
            echo"connection reussie";
        }catch(PDOExeption $e){
            echo"connection echoue"
        }
    }
    public function getconnection(){
        return $this->pdo;
    }
}
$database = new database();
?>