<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|   ['dsn']      The full DSN string describe a connection to the database.
|   ['hostname'] The hostname of your database server.
|   ['username'] The username used to connect to the database
|   ['password'] The password used to connect to the database
|   ['database'] The name of the database you want to connect to
|   ['dbdriver'] The database driver. e.g.: mysqli.
|           Currently supported:
|                cubrid, ibase, mssql, mysql, mysqli, oci8,
|                odbc, pdo, postgre, sqlite, sqlite3, sqlsrv
|   ['dbprefix'] You can add an optional prefix, which will be added
|                to the table name when using the  Query Builder class
|   ['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|   ['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|   ['cache_on'] TRUE/FALSE - Enables/disables query caching
|   ['cachedir'] The path to the folder where cache files should be stored
|   ['char_set'] The character set used in communicating with the database
|   ['dbcollat'] The character collation used in communicating with the database
|                NOTE: For MySQL and MySQLi databases, this setting is only used
|                as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|                (and in table creation queries made with DB Forge).
|                There is an incompatibility in PHP with mysql_real_escape_string() which
|                can make your site vulnerable to SQL injection if you are using a
|                multi-byte character set and are running versions lower than these.
|                Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|   ['swap_pre'] A default table prefix that should be swapped with the dbprefix
|   ['encrypt']  Whether or not to use an encrypted connection.
|
|           'mysql' (deprecated), 'sqlsrv' and 'pdo/sqlsrv' drivers accept TRUE/FALSE
|           'mysqli' and 'pdo/mysql' drivers accept an array with the following options:
|
|               'ssl_key'    - Path to the private key file
|               'ssl_cert'   - Path to the public key certificate file
|               'ssl_ca'     - Path to the certificate authority file
|               'ssl_capath' - Path to a directory containing trusted CA certificates in PEM format
|               'ssl_cipher' - List of *allowed* ciphers to be used for the encryption, separated by colons (':')
|               'ssl_verify' - TRUE/FALSE; Whether verify the server certificate or not
|
|   ['compress'] Whether or not to use client compression (MySQL only)
|   ['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|                           - good for ensuring strict SQL while developing
|   ['ssl_options'] Used to set various SSL options that can be used when making SSL connections.
|   ['failover'] array - A array with 0 or more data for connections if the main should fail.
|   ['save_queries'] TRUE/FALSE - Whether to "save" all executed queries.
|               NOTE: Disabling this will also effectively disable both
|               $this->db->last_query() and profiling of DB queries.
|               When you run a query, with this setting set to TRUE (default),
|               CodeIgniter will store the SQL statement for debugging purposes.
|               However, this may cause high memory usage, especially if you run
|               a lot of SQL queries ... disable this to avoid that problem.
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $query_builder variables lets you determine whether or not to load
| the query builder class.
*/
$active_group = 'default';
$query_builder = TRUE;

$dotenv = Dotenv\Dotenv::createImmutable(APPPATH);
$dotenv->load();

// --------------------------------------------------------------------------------------
// Uncomment the line follows to generate the private key using database credentials.
// Private key is stored in the file "./private_key.txt".
// makePrivateKey("host", "username", "password", "db_name", a, b, c, d);
// --------------------------------------------------------------------------------------

$db['default'] = array(
    'dsn'   => '',
    'hostname' => getDatabaseCredential()[0],
    'username' => getDatabaseCredential()[1],
    'password' => getDatabaseCredential()[2],
    'database' => getDatabaseCredential()[3],
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
);
if (getenv("ERROR_REPORTING") === "false") {
    ini_set('display_errors', false);
    error_reporting(0);
    $db['default']['db_debug'] = FALSE;
}

// Secret obfuscation, encryption and decryption functions
function getDatabaseCredential() {
    $lines = getFirstLines(2);

    $key = getenv('PRIVATE_KEY');
    $cipher = substr($key, 8);

    $a = (int)substr($key, 0, 2);
    $b = (int)substr($key, 2, 2);
    $c = (int)substr($key, 4, 2);
    $d = (int)substr($key, 6, 2);
    $pass = getPassphrase($lines, $a, $b, $c, $d);

    return explode(":", AES256_decrypt(hex2bin($cipher), $pass));
}

function getPassphrase($lines, $a, $b, $c, $d) {
    return substr($lines[0], $a, $c - $a) . substr($lines[1], $b, $d - $b);
}

function getFirstLines($number) {
    $file = fopen("./application/.env", "r") or die("Unable to open environment file!");

    $cnt = 0;
    $lines = array("", "");
    while ($cnt < $number && ($line = fgets($file)) !== false) {
        // process the line read.
        $lines[$cnt] = $line;
        $cnt = $cnt + 1;
    }

    fclose($file);
    return $lines;
}

// Source of AES-256 encryption and decryption code:
// https://stackoverflow.com/a/46872528
function AES256_encrypt($plaintext, $password) {
    $method = "AES-256-CBC";
    $key = hash('sha256', $password, true);
    $iv = openssl_random_pseudo_bytes(16);

    $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
    $hash = hash_hmac('sha256', $ciphertext . $iv, $key, true);

    return $iv . $hash . $ciphertext;
}
function AES256_decrypt($ivHashCiphertext, $password) {
    $method = "AES-256-CBC";
    $iv = substr($ivHashCiphertext, 0, 16);
    $hash = substr($ivHashCiphertext, 16, 32);
    $ciphertext = substr($ivHashCiphertext, 48);
    $key = hash('sha256', $password, true);

    if (!hash_equals(hash_hmac('sha256', $ciphertext . $iv, $key, true), $hash)) return null;

    return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
}

// Function to make private key.
function makePrivateKey($host, $username, $password, $database, $a, $b, $c, $d) {
    $lines = getFirstLines(2);
    $pass = getPassphrase($lines, $a, $b, $c, $d);

    $plaintext = join(":", array($host, $username, $password, $database));
    $pk = $a . $b . $c . $d . bin2hex(AES256_encrypt($plaintext, $pass));

    file_put_contents("private_key.txt", $pk);
}
