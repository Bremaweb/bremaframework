<?php

class messages {
    public static function message($message){
        if ( empty($_SESSION['messages']) ){
            $_SESSION['messages'] = array();
        }
        $_SESSION['messages'][] = $message;
    }
    public static function error($message){
        if ( empty($_SESSION['errors']) ){
            $_SESSION['errors'] = array();
        }
        $_SESSION['errors'][] = $message;
    }
    public static function display(){
        if ( !empty($_SESSION['messages']) ){
            foreach ( $_SESSION['messages'] as $message ){
                echo "<div class='alert alert-success text-center'>" . $message . "</div>";
            }
            unset($_SESSION['messages']);
        }
        if ( !empty($_SESSION['errors']) ){
            foreach ( $_SESSION['errors'] as $error ){
                echo "<div class='alert alert-danger text-center'>" . $error . "</div>";
            }
            unset($_SESSION['errors']);
        }
    }
}