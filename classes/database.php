<?php

    class DatabaseConection{

        const INIFILE = 'database.ini';
        private $pdo;
        private $errors = [];

        public function __construct($cfg = false){
            if(!$cfg){
                $cfg = $this->loadIniConfig();
            }
            $this->doConnection($cfg);
        }

        public function doConnection($cfg){
            $dsn = "mysql:host=". $cfg['host'] .";dbname=". $cfg['dbname'] .";charset=utf8;";
            try{
                $this->pdo = new PDO($dsn, $cfg['user'], $cfg['password']);
            } catch(PDOException $erro){
                throw new Exception($erro);
            }
        }

        public function getConnection(){
            return $this->pdo;
        }

        public function loadIniConfig(){
            if(!file_exists(__DIR__ . "\\" . self::INIFILE)){
                throw new Exception("Arquivo " . self::INIFILE . " não encontrado.");
                return false;
            }
            return parse_ini_file(self::INIFILE);
        }

        public function getErrors(){
            return $this->errors;
        }

        public function createTable(){
            //password_hash size 255 explained https://www.php.net/manual/en/function.password-hash.php
            //email size 254 https://web.archive.org/web/20120222213813/http://www.eph.co.uk/resources/email-address-length-faq/ and https://tools.ietf.org/html/rfc5321
            $sql = "CREATE TABLE IF NOT EXISTS user(
                userID int PRIMARY KEY AUTO_INCREMENT NOT NULL,
                userLogin varchar(:login) NOT NULL,
                password_hash varchar(255) NOT NULL,
                email varchar(254) NOT NULL,
                fullName varchar(:name) NOT NULL
                );";
            $stmt = $this->pdo->prepare($sql);
            //you can change these values but caution with validate.php class
            //voce pode alterar esse valores mas cuidado com a classe validate.php
			$stmt->bindValue(':login', 32, PDO::PARAM_INT);
			$stmt->bindValue(':name', 96, PDO::PARAM_INT);
			if(!$stmt->execute()) {
                array_push($this->errors, $stmt->errorInfo());
                return false;
            }
            return true;
        }
    }

?>