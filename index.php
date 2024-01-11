<?php
include 'libraries/bootstrap.inc.php';

// Database.
if (!file_exists(DB_PATH))
{
  die('Database file does not exist. Visit /install.php to create a new one.');
}
if (!is_writable(dirname(DB_PATH)))
{
  die('Database file is not writable. Edit the file permission to give apache read/write access.');
}
GLOBAL $db;
$db = new SQLite(DB_PATH);

// Path.
GLOBAL $url;
$url = new URL();
$path = $url->getPath();

// If the path is not defined it is probably a link. Attempt to redirect.
$registry = getRegistry();
if (!array_key_exists($path, $registry))
{
  $path = 'unknown';
}

// A non-redirect path is found. Open the session and serve.
session_name('dphlog');
session_start();

GLOBAL $logged_in_user;
if (!$logged_in_user && $path !== 'unknown')
{
  $path = 'login';
}

// Retrieve body.
echo $registry[$path]();

/******************************************************************************
 *
 * Pages.
 *
 ******************************************************************************/
/**
 * @return array
 */
function getRegistry()
{
  $registry = array(
    '/' => 'home', /** @uses home() */
    'home' => 'home', /** @uses home() */
    'user' => 'userUpsertForm', /** @uses userUpsertForm() */
    'users' => 'userListPage', /** @uses userListPage() */
    'login' => 'userLoginForm', /** @uses userLoginForm() */
    'unknown' => 'unknown', /** @uses unknown() */
    'logout' => 'userLogout', /** @uses userLogout */

    'json-formatter' => 'jsonFormatterPage', /** @uses jsonFormatterPage() */
    'time' => 'timePage', /** @uses timePage() */
    'unserialize' => 'phpUnserializePage', /** @uses phpUnserializePage() */
    'ajax/unserialize' => 'phpUnserializeAjax', /** @uses phpUnserializeAjax() */
    'calculator' => 'calculatorPage', /** @uses calculatorPage() */
    'calculator2' => 'calculator2Page', /** @uses calculator2Page() */
    'image-converter'=> 'imageConverter', /** @uses imageConverter() */
  );

  return $registry;
}

function home()
{
  $template = new HTMLTemplate();
  $template->setTitle('DPH Log');
  $template->addCssFilePath('/themes/default/css/log.css');
  $template->addJsFilePath('/modules/log/log.js');

  $template->setMenu(menu());
  $template->setBody(htmlWrap('h1', 'Welcome to Daniel P Henry\'s PHP Error Log Reader') . logView());

  return $template;
}

function unknown()
{
  header('HTTP/1.1 404 Not Found');

  $template = new HTMLTemplate();
  $template->setTitle('Unknown');
  $template->setBody(htmlWrap('h1', 'Page Not Found'));

  return $template;
}

function menu()
{
  $output = '';

  // Home/Log
  $output .= htmlWrap('a', 'Home', array('href' => '/home'));

  // Users
  $output .= htmlWrap('a', 'Users', array('href' => '/users'));

  // JSON formatter.
  $output .= htmlWrap('a', 'JSON Formatter', array('href' => '/json-formatter'));

  //epoch converter
  $output .= htmlWrap('a', 'Time', array('href' => '/time'));

  $output .= htmlWrap('a', 'Unserialize', array('href' => '/unserialize'));
  $output .= htmlWrap('a', 'Calculator', array('href' => '/calculator'));
  $output .= htmlWrap('a', 'Image Converter', array('href' =>'/image-converter'));

  // Username.
  GLOBAL $logged_in_user;
  $output .=htmlWrap('a', $logged_in_user['username'], array('href' => '/user?user_id=' . $logged_in_user['id']));
  $submenu = new ListTemplate('ul');
  $submenu->addListItem(htmlWrap('a', 'Logout', array('href' => '/logout')));
  $output .= $submenu;

  $attr = array('id' => 'menu', 'class' => array('menu'));
  $output = htmlWrap('div', $output, $attr);
  return $output;
}
