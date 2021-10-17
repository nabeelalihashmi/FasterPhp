    <?php

    namespace Core\Classes;

    use PDO;
    use PDOException;


    class DB {
        private static $_instance = null;

        public function __construct() {
            try {
                self::$_instance = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
                    DB_USER,
                    DB_PASS
                );

                self::$_instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
        }

        public static function instance() {
            if (self::$_instance == null) {
                new self();
            }
            return self::$_instance;
        }
    }
