<?php

use Cake\Core\Configure\Engine\IniConfig;
use Cake\Datasource\ConnectionManager;
use Cake\Error\ErrorHandler;
use Cake\Utility\Security;
use Cake\Filesystem\File;
use Cake\Routing\Router;
use Cake\Core\Configure;
use Cake\Cache\Cache;
use Cake\Database\Type;
use Cake\I18n\Time;
use Cake\Log\Log;

Configure::config( 'settings', new IniConfig() );
Configure::load( 'config', 'settings' );
(new ErrorHandler(Configure::read('Error')))->register();
Configure::write('DebugKit.safeTld', Configure::read('DebugKit.tld'));
Configure::write('Session.timeout', Configure::read('Session.lifetime')/60);

Cache::setConfig('mysql_conf', [
    'className' => 'File',
    'duration' => '+1 hours',
    'path' => CACHE,
    'prefix' => 'cake_mysql_conf_'
]);

Time::setDefaultLocale('de-DE');

define('FIXTURE', array(
    array(
        'id' => 15,
        'name' => 'Sample User',
        'email' => 'sampleuser@webpremiere.dev',
        'group_id' => '3'
    ),
    array(
        'id' => 24,
        'name' => 'Sample Admin',
        'email' => 'test@webpremiere.de',
        'group_id' => '1'
    ),
));

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $protocol = 'https://';
} else {
    $protocol = 'http://';
}
define('DIR_REL_HOST', str_replace('/index.php?', '', Configure::read('App.fullBaseUrl')));
define('DIR_HOST', $protocol . preg_replace('/:80$/', '', env('HTTP_HOST')));
define('BASE_URL', Configure::read('App.fullBaseUrl'));
define('UPLOADS', ROOT . DS . 'uploads');
define('IMAGES', UPLOADS . DS . 'images');
define('VIDEOS', UPLOADS . DS . 'videos');
define('AVATARS', UPLOADS . DS . 'avatars');
if(!defined('MYSQL_CONFIG_DIR')) {
    define('MYSQL_CONFIG_DIR', ROOT . DS . 'mysqlconf' . DS);
    if (!is_dir(MYSQL_CONFIG_DIR)) {
        $parent_perms = substr(sprintf('%o', fileperms(dirname(dirname( MYSQL_CONFIG_DIR )))), -4);
        $old = umask(0);
        mkdir(MYSQL_CONFIG_DIR, octdec($parent_perms));
        umask($old);
    }
}
if (!defined('MAGICK_PATH')) {
	define('MAGICK_PATH_FINAL', 'convert');
} else if (strpos(strtolower(MAGICK_PATH), 'c:\\') !== false) {
	define('MAGICK_PATH_FINAL', '"' . MAGICK_PATH . '"');	
} else {
	define('MAGICK_PATH_FINAL', MAGICK_PATH);	
}

if (!defined('FFMPEG_PATH')) {
	define('FFMPEG_PATH_FINAL', 'ffmpeg');	
} else {
	define('FFMPEG_PATH_FINAL', FFMPEG_PATH);	
}

define('MYSQL_CONFIG_FILENAME', 'my');
define('MYSQL_CONFIG', MYSQL_CONFIG_DIR . MYSQL_CONFIG_FILENAME . '.ini');

if( !Cache::read('Client', 'mysql_conf') || !file_exists( MYSQL_CONFIG ) ) {
    $config = ConnectionManager::getConfig('target_db');
    $db_host = $config['host'];
    $db_db   = $config['database'];
    $db_pass = $config['password'];
    $db_user = $config['username'];
    Configure::write('Client', [
        'host'      => $db_host,
        'user'      => $db_user,
        'password'  => $db_pass
        ]);
    Configure::config( 'mysql_conf', new IniConfig( MYSQL_CONFIG_DIR ));
    Configure::dump( MYSQL_CONFIG_FILENAME, 'mysql_conf', ['Client'] );
    Configure::load( MYSQL_CONFIG_FILENAME, 'mysql_conf' );
    Cache::write('Client', Configure::consume('Client'), 'mysql_conf');
}

if(!defined('MYSQLUPLOAD')) {
    define('MYSQLUPLOAD', ROOT . DS . 'mysql');
    if (!is_dir(MYSQLUPLOAD)) {
        $parent_perms = substr(sprintf('%o', fileperms(dirname(dirname(MYSQLUPLOAD)))), -4);
        $old = umask(0);
        mkdir(MYSQLUPLOAD, octdec($parent_perms));
        umask($old);
    }
}
if (!defined('MYSQL_CMD_PATH')) {
    $a = explode('.', DIR_HOST);
    $last = count($a) - 1;
    $local_tld = array( 'dev', 'mbp', 'local' );
    $tl = $a[$last];
    $path = '';
    if( in_array( $tl, $local_tld ) ) {
        $path = '/usr/local/mysql/bin/';
    }
    define('MYSQL_CMD_PATH', $path);
}
if (!defined('TOPLEVEL')) {
    $a = explode('.', DIR_HOST);
    $last = count($a) - 1;
    define('TOPLEVEL', $a[$last]);
}
if (!defined('SIMPLE_JSON')) {
    define('SIMPLE_JSON', '/Element/simple_json');
}
if (!defined('FLASH_JSON')) {
    define('FLASH_JSON', '/Element/flash_json');
}
if (!defined('MAX_DUMPS')) {
    define('MAX_DUMPS', Configure::check('Mysql.max_dumps') ? (int) Configure::read( 'Mysql.max_dumps' ) : 5 );
}

function rm_file( $fn ) {
    $path = MYSQLUPLOAD . DS . $fn;
    $files = glob($path);
    // Log::write('debug', 'rm_file:');
    // Log::write('debug', $files);
    if(!empty($files)) {
        $file = new File($files[0]);
        $deleted = $file->delete();
        $file->close();
        return $deleted;
    }
    return true;
}

function c($max = 5) {
    $files = l(SORT_ASC, TRUE);
    reset($files);
    $c = count($files);
    if($c > $max) {
        $file = new File(key($files));
        $file->delete();
        $file->close();
        c($max);
    }
}

function l( $sort = SORT_DESC, $fullpath = '' ) {
    $times = array();
    $path = MYSQLUPLOAD . DS . '*.*';
    $files = glob($path);
    if(!is_array($files)) $files = [$files];
    foreach ($files as $key => $val) {
        $timestamp = filemtime($val);
        if(!$fullpath) {
            $parts = explode(DS, $val);
            $val = array_pop($parts);
        }
        $times[$val] = $timestamp;
    }
    array_multisort($times, $sort);
    return $times;
}

function l2( $sort = SORT_DESC, $fullpath = false ) {

    $time = array();
    $path = MYSQLUPLOAD . DS . '*.*';
    $files = glob($path);
    if(!is_array($files)) $files = [];
    foreach ($files as $key => $val) {
        $timestamp = filemtime($val);
        if(!$fullpath) {
            $parts = explode(DS, $val);
            $val = array_pop($parts);
        }
        $t = Time::createFromTimestamp($timestamp);
        $ago = $t->timeAgoInWords(
            [
                'accuracy' => 'minutes',
                'format' => 'd. MMM, YYY',
                'end' => '+1 year'
            ]
        );
        $time[$val] = [
            'human' => $t->i18nFormat('d. MMM yyy HH:mm B', 'Europe/Berlin', 'de-DE'),
            'unix' => $timestamp,
            'ago' => $ago
        ];
    }
    array_multisort($time, $sort);
    // debug($time);

    return $time;
}

/*
 * Get Site specific Images, Logos & Favicons from config.ini
 */
function logo_url( $output = FALSE ) {
    $ret = BASE_URL . DS . Configure::read( 'App.imageBaseUrl' ) . 'config' . DS . Configure::read( 'Site.logo' );
    if ($output) {
        echo $ret;
    } else {
        return $ret;
    }
}
function icon_url( $output = FALSE ) {
    $ret = BASE_URL . DS . Configure::read( 'App.imageBaseUrl' ) . 'config' . DS . Configure::read( 'Site.icon' );
    if ($output) {
        echo $ret;
    } else {
        return $ret;
    }
}

/*
 * get the age in days of Backup
 *
 */
function get_date_diff( $time, $time_unit = "d" ) {

	$now = date_create();

	if ( !isset( $time ) )
		$time = $now;

	$lst = date_create( date( "Y-m-d H:i:s", $time ) );
	$diff = date_diff( $lst, $now );
	switch( $time_unit ) {
		case "y":
            $total = $diff->y + $diff->m / 12 + $diff->d / 365.25;
            $total = round($total, 0, PHP_ROUND_HALF_DOWN);
			$unit_name = sprintf( __('Year%s'), 1 !== (int) $total ? 'en' : '' );
			break;
		case "m":
            $total= $diff->y * 12 + $diff->m + $diff->d/30 + $diff->h / 24;
            $total = round($total, 0, PHP_ROUND_HALF_DOWN);
			$unit_name = sprintf( __('Month%s'), 1 !== (int) $total ? 'en' : '' );
			break;
		case "d":
            $total = $diff->y * 365.25 + $diff->m * 30 + $diff->d + $diff->h/24 + $diff->i/60;
            $total = round($total, 0, PHP_ROUND_HALF_DOWN);
			$unit_name = sprintf( __('Day%s'), 1 < (int) $total ? 'en' : '');
			break;
		case "h":
            $total = ($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h + $diff->i/60;
            $total = round($total, 0, PHP_ROUND_HALF_DOWN);
			$unit_name = sprintf( __('Hour%s'), 1 !== (int) $total ? 'n' : '' );
			break;
		case "i":
            $total = (($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s/60;
            $total = round($total, 0, PHP_ROUND_HALF_DOWN);
			$unit_name = sprintf( __('Minute%s'), 1 !== (int) $total ? 'n' : '');
			break;
    }
	return array( 'total' => $total, 'name' => $unit_name, 'timestamp' => $time );
}

function express_date_diff( $time ) {
    if( ( $diff = get_date_diff( $time, 'i' ) ) && ( $diff['total'] > 59 ) ) { // express in minutes
        if( ( $diff = get_date_diff( $time, 'h' ) ) && ( $diff['total'] > 23 ) ) { // express in hours
            if( ( $diff = get_date_diff( $time, 'd' ) ) && ( $diff['total'] > 29 ) ) { // express in days
                if( ( $diff = get_date_diff( $time, 'm' ) ) && ( $diff['total'] > 11 ) ) { // express in months
                    $diff = get_date_diff( $time, 'y' ); // express in years
                }
            }
        }
    }
    return $diff;
}

function array_extract($symbol, $array = null) {
    if (!$array) return;
    $return = [];
    foreach( $array as $key => $val ) {
        if(is_array($val) && array_key_exists($symbol, $val)) {
            $return[$key] = $val[$symbol];
        }
    }
    return $return;
}