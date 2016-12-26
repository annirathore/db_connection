<?php

/**
 * Class DbConnection
 *
 * @author Aniruddh Rathore
 */
class DbConnection {

    /**
     * Configuration file of database connection
     *
     * @const string
     */
    const DATABASE_CONFIG_FILE = '../conf/config.ini';

    /**
     * Check connection to database
     *
     * @var    string
     * @access public
     */
    public $connection;

    /**
     * Generate random key
     *
     * @var    string
     * @access public
     */
    public $rand_key;

    /**
     * Error message
     *
     * @var    string
     * @access public
     */
    public $error_message;

    /**
     * MySQL Host
     *
     * @var    string
     * @access public
     */
    public $database_host;

    /**
     * MySQL Username
     *
     * @var    string
     * @access public
     */
    public $database_user;

    /**
     * MySQL Password
     *
     * @var    string
     * @access public
     */
    public $database_password;

    /**
     * Array of login access credentials
     *
     * @var    array
     * @access private
     */
    private $accessCredential = array();

    /**
     * To get action on same page
     *
     * @access public
     * @return string
     */
    public function getSelfScript() {
        return htmlentities($_SERVER['PHP_SELF']);
    }

    /**
     * Returns post data
     *
     * @access public
     * @param  string $value_name
     * @return string
     */
    public function safeDisplay($value_name) {
        if (empty($_POST[$value_name])) {
            return '';
        }
        return htmlentities($_POST[$value_name]);
    }

    /**
     * To return error msg(it will display error msg)
     *
     * @access public
     * @return string $errormsg
     */
    public function getErrorMessage() {
        if (empty($this->error_message)) {
            return '';
        }
        $errormsg = nl2br(htmlentities($this->error_message));
        return $errormsg;
    }

    /**
     * To display error message
     *
     * @access public
     * @param  string $err
     */
    public function handleError($err) {
        $this->error_message .= $err . "\r\n";
    }

    /**
     * To display error message
     * if we are not connected with mysql
     *
     * @access public
     * @param  string $err
     */
    public function handleDBError($err) {
        $this->handleError($err . "\r\n mysqlerror:" . mysql_error());
    }

    /**
     * To redirect on required page through url
     *
     * @access public
     * @param  string $url
     */
    public function redirectToUrl($url) {
        header("Location: $url");
        exit;
    }

    /**
     * To initiate database connection
     *
     * @access  public
     * @param   string $host hostname of database
     * @param   string $uname username of database
     * @param   string $pwd password of database
     */
    public function initializeDatabase($host, $uname, $pwd) {
        $this->database_host = $host;
        $this->database_user = $uname;
        $this->database_password = $pwd;
    }

    /**
     * Connect with mysql database,
     * in case connection failure then show db error message
     * and return flag if successfull connection with mysql
     *
     * @access  public
     * @return  boolean
     */
    public function databaseLogin() {
        $this->connection = mysql_connect($this->database_host, $this->database_user, $this->database_password);
        if (!$this->connection) {
            $this->handleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
            return false;
        }

        if (!mysql_query("SET NAMES 'UTF8'", $this->connection)) {
            $this->handleDBError('Error setting utf8 encoding');
            return false;
        }
        return true;
    }

    /**
     * To prevent sql injection and
     * Escapes special characters in a string for use in an SQL statement
     *
     * @access  public
     * @param   string $str
     * @return  string $ret_str
     */
    public function sanitizeForSql($str) {
        if (function_exists("mysql_real_escape_string")) {
            $ret_str = mysql_real_escape_string($str);
        } else {
            $ret_str = addslashes($str);
        }
        return $ret_str;
    }

    /**
     * Check login data is in session or not
     *
     * @access public
     * @return boolean
     */
    public function userFullName() {
        return isset($_SESSION['username']) ? $_SESSION['username'] : '';
    }

    /**
     * Return a session variable
     *
     * @access  public
     * @return  string
     */
    public function getLoginSessionVar() {
        $retvar = md5($this->rand_key);
        $retvar = 'usr_' . substr($retvar, 0, 10);
        return $retvar;
    }

    /**
     * Parse a ini file to get a Key Value Pair out of it.
     *
     * @access  public
     * @return  array $configValue
     */
    public static function getConfigValues($fileName) {
        $configValues = parse_ini_file($fileName, true);
        return $configValues;
    }

}
$result = false;
$dbConnection   = new DbConnection();
$config         = DbConnection::getConfigValues(DbConnection::DATABASE_CONFIG_FILE);
$configDatabase = $config['database'];
$dbConnection->initializeDatabase($configDatabase['mysql.host'],
        $configDatabase['mysql.username'],
        $configDatabase['mysql.password']);
if ($dbConnection->databaseLogin()) {
    mysql_select_db($configDatabase['mysql.database']);
    $result = true;
} else {
    exit('Database not connected !!!');
}
