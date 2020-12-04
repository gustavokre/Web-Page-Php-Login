<?php
    class Login extends User{
        private $errors = [];
        private $online = false;
        private $valid = false;
        private $failedAttempts;
        private $password;
        
        public function __construct($login, $password)
        {
            if(Validate::generic($login, 'login')){
                $this->setLogin($login);
                $this->password = $password;
                $this->valid = true;
            }
            else
            {
                array_push($this->errors, "Usuário ou senha não forma preenchidos corretamente.");
            }
        }

        public function getErrors(){
            return $this->errors;
        }

        public function getIsOnline(){
            return $this->online;
        }

        public function goOnline(PDO $pdo){
            if(!$this->valid){
                return false;
            }
            $table = DatabaseConection::TABLENAME;
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE userLogin=:userLogin");
            $stmt->bindValue(':userLogin', $this->getLogin(), PDO::PARAM_STR);
            
            if(!$stmt->execute()) {
                array_push($this->errors, $stmt->errorInfo());
                return false;
            }

            while($user = $stmt->fetch(PDO::FETCH_ASSOC)){
                if($this->verifyPassword($this->password, $user['password_hash'])){
                    $this->setEmail($user['email']);
                    $this->setFullName($user['fullName']);
                    $this->online = true;
                    return true;
                }

                $this->failedAttempts++;
                array_push($this->errors, "Senha incorreta.");
                return false;
            }
            
            $this->failedAttempts++;
            array_push($this->errors, "Usuário não existe");
            return false;
        }

        public function verifyPassword($password, $dbPassword){
            return password_verify($password, $dbPassword);
        }

        public function goOffline(){
            $this->setEmail("");
            $this->setFullName("");
            $this->setLogin("");
            $this->setPasswordHash("");
            $this->online = false;
        }
    }
?>