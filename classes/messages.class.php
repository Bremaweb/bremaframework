<?php

class messages {
    /**
     * @param $message
     */
    public static function message($message){
        if ( empty($_SESSION['messages']) ){
            $_SESSION['messages'] = array();
        }
        $_SESSION['messages'][] = $message;
    }

    /**
     * @param $message
     */
    public static function info($message){
        if ( empty($_SESSION['info']) ){
            $_SESSION['info'] = array();
        }
        $_SESSION['info'][] = $message;
    }

    /**
     * @param $message
     */
    public static function error($message){
        if ( empty($_SESSION['errors']) ){
            $_SESSION['errors'] = array();
        }
        $_SESSION['errors'][] = $message;
    }

    /**
     * @param string $pre
     * @param string $post
     */
    public static function display($pre = "", $post = ""){
        if ( !empty($_SESSION['messages']) ){
            foreach ( $_SESSION['messages'] as $message ){
                echo $pre . "<div class='alert alert-success text-center'>" . $message . "</div>" . $post;
            }
            $_SESSION['messages'] = array();
        }

        if ( !empty($_SESSION['info']) ){
            foreach ( $_SESSION['info'] as $info ){
                echo $pre . "<div class='alert alert-info text-center'>" . $info . "</div>" . $post;
            }
            $_SESSION['info'] = array();
        }

        if ( !empty($_SESSION['errors']) ){
            foreach ( $_SESSION['errors'] as $error ){
                echo $pre . "<div class='alert alert-danger text-center'>" . $error . "</div>" . $post;
            }
            $_SESSION['errors'] = array();
        }
    }
}