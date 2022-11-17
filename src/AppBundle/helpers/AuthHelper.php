<?php

class AuthHelper {
    function __construct() {
        //abre la sessión siempre para usar el helper
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function isLogedIn(){
        if (empty($_SESSION['MODERATOR_ID']) && empty($_SESSION['ADMIN_ID'])) {
            header("Location: /login");
            die();
        }
    }

    public function adminIsLogedIn(){
        if (empty($_SESSION['ADMIN_ID'])) {
            header("Location: /login");
            die();
        }
    }


    public function login($userExists)
    {
        if ($userExists->getRole()->getName() == 'moderator') {
            $_SESSION['MODERATOR_ID'] = $userExists->getId();
            $_SESSION['MODERATOR_NAME'] = $userExists->getUsername();
        } else{
            $_SESSION['ADMIN_ID'] = $userExists->getId();
            $_SESSION['ADMIN_NAME'] = $userExists->getUsername();
        }
    }



    

    function logout() {
        session_destroy();
        header("Location:/login");
    } 
}