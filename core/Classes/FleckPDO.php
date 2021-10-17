    <?php

    namespace Core\Classes;

    use PDO;
    use PDOException;


    class FleckPDO {
        private static $_instance = null;

        public function __construct() {
            try {
                self::$_instance = new PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}",
                $_ENV['DB_USER'],
                $_ENV['DB_PASS']);
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
