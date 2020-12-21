<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

/**
 * Database
 * @var StdClass $config
 */
$config->db = new StdClass();
$config->db->charset = 'utf8';
$config->db->perPage = 50;

// See errortext below.
$config->db->username = getenv('MYSQL_USER');
$config->db->password = getenv('MYSQL_PASSWORD');
$config->db->host = getenv('MYSQL_HOST');
$config->db->dbname	= getenv('MYSQL_DATABASE');

if (empty($config->db->username)
    || empty($config->db->password)
    || empty($config->db->host)
    || empty($config->db->dbname)
) {
    echo "
<h3>Error setting up database connection.</h3>
You need to set the environment variables<br />
<blockquote>MYSQL_USER<br />
MYSQL_PASSWORD<br />
MYSQL_HOST<br />
MYSQL_DATABASE<br />
</blockquote>
by using
<blockquote>SetEnv MYSQL_HOST localhost</blockquote>
<blockquote>...</blockquote>
in <b>apache</b> server configurations<br />
or for <b>nginx</b> by using
<blockquote>env[MYSQL_HOST] = localhost</blockquote> 
<blockquote>...</blockquote> 
for <b>php-cgi</b>
or
<blockquote>fastcgi_param MYSQL_HOST localhost;</blockquote>
<blockquote>...</blockquote>
for <b>php-fpm</b>.    
";
    exit;
}
