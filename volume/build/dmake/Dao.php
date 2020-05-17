<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 *  DAO
 *
 */

namespace Dmake;

use \PDO;

class Dao
{
    /**
     * A dao instance
     * @var PDO
     */
    protected static $instance = null;

    protected function __construct() {}
    protected function __clone() {}

    /**
     *
     * @return PDO
     */
    public static function getInstance($failOnExit = true) : ?PDO
    {
        if (self::$instance === null)
        {
            $opt  = array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => FALSE,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            );
            $db = Config::getConfig('db');
            $dsn = 'mysql:host='.$db->host.';dbname='.$db->dbname.';charset='.$db->charset;
            try {
                self::$instance = new PDO($dsn, $db->username, $db->password, $opt);
	        } catch (\Exception $e) {
		        if ($failOnExit) {
		            die("Database running? If docker-compose is starting up, please come back in a few seconds.\n");
		        } else {
                    return null;
                }
            }
        }
        
        return self::$instance;
    }

    /**
     * drops the instance
     */
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

    /**
     * for static calls
     * @param $method
     * @param $args
     * @return mixed
     */
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

