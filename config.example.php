<?php
/**
 * Copy to config.php on your server (cPanel File Manager or FTP).
 * Do NOT commit config.php to GitHub — it is listed in .gitignore.
 */
return [
    'db_host' => 'localhost',              // Hostinger/cPanel: often localhost; InfinityFree: sql###.host.com
    'db_user' => 'your_database_username',
    'db_pass' => 'your_database_password',
    'db_name' => 'your_database_name',
    'debug' => false,                      // true only while fixing errors; false on live site
];
