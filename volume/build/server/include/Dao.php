<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 * DAO
 *
 */
use Server\Config;
use \PDO;

class DAO
{
    protected static $instance = null;

    protected function __construct() {}
    protected function __clone() {}

    /**
     *
     * @return PDO
     */
    public static function getInstance()
    {
        if (self::$instance === null)
        {
            $opt  = array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => FALSE,
            );
			$db = Config::getConfig('db');
            $dsn = 'mysql:host='.$db->host.';dbname='.$db->dbname.';charset='.$db->charset;
			try {
	            self::$instance = new PDO($dsn, $db->username, $db->password, $opt);
			} catch(Exception $e) {
				echo "Sorry, connection to DB Server failed.";
				exit;
			}
        }
        return self::$instance;
    }

    public static function dropInstance()
    {
        self::$instance = null;
    }

    /**
     * @return PDO
     */
    public static function renewInstance()
    {
        self::dropInstance();
        return self::getInstance();
    }

    /**
     * For long running jobs, the wait_timeout (default 28800 s) might be
     * exceeded and therefore the query fails with 'mysql server has gone away'.
     * For specific methods, that might be called after long period of time, one can use
     * this method, which will automatically reconnect, if the simple query fails.
     *
     * @return PDO
     */
    public static function checkAndGetInstance() : PDO
    {
        try {
            self::query("SELECT 1;", null, false, true);
        } catch(\PDOException $e) {
            if ($e->getCode() != 'HY000'
                || !stristr($e->getMessage(), 'server has gone away')) {
                throw $e;
            }
            echo 'Wait timeout exceeded, renewing instance...' . PHP_EOL;
            self::renewInstance();
        }
        return self::$instance;
    }


    public static function __callStatic($method, $args)
    {
        return call_user_func_array(array(self::getInstance(), $method), $args);
    }

    /**
     * provides a static query
     * @param $sql
     * @param null $args
     * @param $silent for silent checkAndGetInstance
     * @return bool|PDOStatement
     */
    public static function query($sql, $args = null, $retryQuery = true, $silent = false)
    {
        try {
            /* test query might fail, avoid a warning in output */
            if ($silent) {
                $stmt = @self::getInstance()->prepare($sql);
                @$stmt->execute($args);
            } else {
                $stmt = self::getInstance()->prepare($sql);
                $stmt->execute($args);
            }
            return $stmt;
        } catch(\PDOException $e) {
            if ($e->getCode() != 'HY000'
                || !stristr($e->getMessage(), 'server has gone away')) {
                throw $e;
            }
            echo 'Wait timeout exceeded, renewing instance...' . PHP_EOL;
            self::renewInstance();
            self::query($sql, $args, false);
        }
    }
}

