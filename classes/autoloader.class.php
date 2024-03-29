<?php
	class ClassAutoloader {
        public function __construct() {
            spl_autoload_register(array($this, 'loader'));
        }
        private function loader($className) {
			if ( $className == "PHPMailer" ){
				$r = require_once(BREMA_DIR . "phpmailer/class.phpmailer.php");
				if ( $r === false ){
					die("Unable to load class " . $className . " in Autoloader");
				}
				require_once(BREMA_DIR . "phpmailer/class.smtp.php");
			} else {
				$parts = explode("\\",$className);

				if ( count($parts) > 0 )
					$className = implode("/", $parts);

					// try loading the class from the APP_DIR first
                    $classDir = defined('MODEL_DIR') ? MODEL_DIR : APP_DIR . 'includes/classes';
					$r = @include($classDir . '/' . $className . ".class.php");

					// try loading the class from brema frame work using brema framework filename format
					if ( $r === false ){
						$r = @include(BREMA_DIR . "classes/" . $className . ".class.php");
					}

					// try loading the class from brema framework using another filename format
					if ( $r === false ){
						$r = @include(BREMA_DIR . "classes/" . $className . ".php");
					}

				if ( $r === false )
					die("Unable to load class " . $className);
			}
        }
    }
?>