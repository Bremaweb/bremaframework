<?php
	class ClassAutoloader {
        public function __construct() {
            spl_autoload_register(array($this, 'loader'));
        }
        private function loader($className) {
			global $appDir;

            //echo 'Trying to load ', $className, ' via ', __METHOD__, "()\n";
			if ( $className == "PHPMailer" ){
				include(BREMA_DIR . "/includes/phpmailer/class.phpmailer.php");
			} else {
				$parts = explode("\\",$className);

				if ( count($parts) > 0 )
					$className = implode("/", $parts);

					// try loading the class from the APP_DIR first
					$r = @include(APP_DIR . "/includes/classes/" . $className . ".class.php");

					// try loading the class from brema frame work using brema framework filename format
					if ( $r === false ){
						$r = @include(BREMA_DIR . "/classes/" . $className . ".class.php");
					}

					// try loading the class from brema framework using another filename format
					if ( $r === false ){
						$r = @include(BREMA_DIR . "/classes/" . $className . ".php");
					}



				if ( $r === false )
					die("Unable to load class " . $className);
			}
        }
    }
?>