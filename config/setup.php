<?php

use Cake\Core\Configure;
use Cake\Core\Configure\Engine\IniConfig;
use Cake\I18n\DateTime;
use Firebase\JWT\JWT;

Configure::config('settings', new IniConfig());
Configure::load('config', 'settings');
Configure::write('DebugKit.safeTld', Configure::read('DebugKit.tld'));
Configure::write('Session.timeout', Configure::read('Session.lifetime') / 60);
Configure::write('Site.salutation', Configure::read('Site.salutation'));

// Cache::setConfig('mysql_conf', [
//     'className' => 'File',
//     'duration' => '+1 hours',
//     'path' => CACHE,
//     'prefix' => 'cake_mysql_conf_'
// ]);

DateTime::setDefaultLocale('de-DE');

define('APP_NAME', 'VOD_APP');
define('API_PATH', '/api/v1');
define('AUTH_HEADER', 'Authorization');
define('AUTH_PREFIX', 'Bearer');
define('SUPERUSER', 'Superuser');
define('ADMIN', 'Administrator');
define('MANAGER', 'Manager');
define('USER', 'User');
define('GUEST', 'Guest');


if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
  $protocol = 'https://';
} else {
  $protocol = 'http://';
}
define('DIR_REL_HOST', str_replace('/index.php?', '', Configure::read('App.fullBaseUrl')));
define('DIR_HOST', $protocol . preg_replace('/:\d{2,4}$/', '', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''));
define('BASE_URL', Configure::read('App.fullBaseUrl'));
define('UPLOADS', ROOT . DS . 'uploads');
define('IMAGES', UPLOADS . DS . 'images');
define('VIDEOS', UPLOADS . DS . 'videos');
define('AVATARS', UPLOADS . DS . 'avatars');
define('SCREENSHOTS', UPLOADS . DS . 'screenshots');
define('TEMPLATES', ROOT . DS . 'templates');
define('EMAIL_TEMPLATES', TEMPLATES . DS . 'email');
define('MYSQLUPLOAD', ROOT . DS . 'mysql');
if (!defined('MYSQL_CONFIG_DIR')) {
  define('MYSQL_CONFIG_DIR', ROOT . DS . 'mysqlconf' . DS);
  if (!is_dir(MYSQL_CONFIG_DIR)) {
    $parent_perms = substr(sprintf('%o', fileperms(dirname(dirname(MYSQL_CONFIG_DIR)))), -4);
    $old = umask(0);
    mkdir(MYSQL_CONFIG_DIR, octdec($parent_perms));
    umask($old);
  }
}
if (!defined('MAGICK_PATH')) {
  define('MAGICK_PATH', 'convert');
} else if (strpos(strtolower(MAGICK_PATH), 'c:\\') !== false) {
  define('MAGICK_PATH_FINAL', '"' . MAGICK_PATH . '"');
} else {
  define('MAGICK_PATH_FINAL', MAGICK_PATH);
}

if (!defined('FFMPEG_PATH')) {
  define('FFMPEG_PATH', 'ffmpeg');
}
if (!defined('FFMPEG_PATH_FINAL')) {
  define('FFMPEG_PATH_FINAL', FFMPEG_PATH);
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
  define('MAX_DUMPS', Configure::check('Mysql.max_dumps') ? (int) Configure::read('Mysql.max_dumps') : 5);
}

/*
 * Get Site specific Images, Logos & Favicons from config.ini
 */
function logo_url($output = false)
{
  $ret = BASE_URL . DS . Configure::read('App.imageBaseUrl') . 'config' . DS . Configure::read('Site.logo');
  if ($output) {
    echo $ret;
  } else {
    return $ret;
  }
}
function icon_url($output = false)
{
  $ret = BASE_URL . DS . Configure::read('App.imageBaseUrl') . 'config' . DS . Configure::read('Site.icon');
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
function get_date_diff($time, $time_unit = "d")
{

  $now = date_create();

  if (!isset($time)) {
    $time = $now;
  }

  $lst = date_create(date("Y-m-d H:i:s", $time));
  $diff = date_diff($lst, $now);
  switch ($time_unit) {
    case "y":
      $total = $diff->y + $diff->m / 12 + $diff->d / 365.25;
      $total = round($total, 0, PHP_ROUND_HALF_DOWN);
      $unit_name = sprintf(__('Year%s'), 1 !== (int) $total ? 'en' : '');
      break;
    case "m":
      $total = $diff->y * 12 + $diff->m + $diff->d / 30 + $diff->h / 24;
      $total = round($total, 0, PHP_ROUND_HALF_DOWN);
      $unit_name = sprintf(__('Month%s'), 1 !== (int) $total ? 'en' : '');
      break;
    case "d":
      $total = $diff->y * 365.25 + $diff->m * 30 + $diff->d + $diff->h / 24 + $diff->i / 60;
      $total = round($total, 0, PHP_ROUND_HALF_DOWN);
      $unit_name = sprintf(__('Day%s'), 1 < (int) $total ? 'en' : '');
      break;
    case "h":
      $total = ($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h + $diff->i / 60;
      $total = round($total, 0, PHP_ROUND_HALF_DOWN);
      $unit_name = sprintf(__('Hour%s'), 1 !== (int) $total ? 'n' : '');
      break;
    case "i":
      $total = (($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s / 60;
      $total = round($total, 0, PHP_ROUND_HALF_DOWN);
      $unit_name = sprintf(__('Minute%s'), 1 !== (int) $total ? 'n' : '');
      break;
  }
  return array('total' => $total, 'name' => $unit_name, 'timestamp' => $time);
}

function express_date_diff($time)
{
  if (($diff = get_date_diff($time, 'i')) && ($diff['total'] > 59)) { // express in minutes
    if (($diff = get_date_diff($time, 'h')) && ($diff['total'] > 23)) { // express in hours
      if (($diff = get_date_diff($time, 'd')) && ($diff['total'] > 29)) { // express in days
        if (($diff = get_date_diff($time, 'm')) && ($diff['total'] > 11)) { // express in months
          $diff = get_date_diff($time, 'y'); // express in years
        }
      }
    }
  }
  return $diff;
}

function array_extract($symbol, $array = null)
{
  if (!$array) {
    return;
  }

  $return = [];
  foreach ($array as $key => $val) {
    if (is_array($val) && array_key_exists($symbol, $val)) {
      $return[$key] = $val[$symbol];
    }
  }
  return $return;
}

/**
 * Appends a trailing slash.
 *
 * Will remove trailing forward and backslashes if it exists already before adding
 * a trailing forward slash. This prevents double slashing a string or path.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @since 1.2.0
 *
 * @param string $value Value to which trailing slash will be added.
 * @return string String with trailing slash added.
 */
function trailingslashit($value)
{
  return untrailingslashit($value) . '/';
}

/**
 * Removes trailing forward slashes and backslashes if they exist.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @since 2.2.0
 *
 * @param string $text Value from which trailing slashes will be removed.
 * @return string String without the trailing slashes.
 */
function untrailingslashit($value)
{
  return rtrim($value, '/\\');
}

function randomString($length = 8)
{
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}

function getPublicKey()
{
  return file_get_contents(CONFIG . '/jwt.pem');
}

function createJWT($uid, $exp = null)
{
  return JWT::encode(
    [
      'sub' => $uid,
      'exp' => $exp ?? time() + 604800,
    ],
    getPublicKey(),
    'HS256'
  );
}
