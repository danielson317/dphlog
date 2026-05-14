<?php

/******************************************************************************
 *
 * Constants
 *
 ******************************************************************************/
define('DEFAULT_PAGER_SIZE', 100);

define('SEC_MIN', 60);
define('SEC_HOUR', 3600);
define('SEC_DAY', 86400);
define('SEC_WEEK', 604800);
define('SEC_MONTH', 2419200);
define('SEC_YEAR', 31449600);

define('EXCEPTION_NOT_FOUND', 1);
define('EXCEPTION_PERMISSION_DENIED', 2);
define('EXCEPTION_EXPIRED', 3);
define('EXCEPTION_REQUIRED_FIELD_MISSING', 4);
define('EXCEPTION_FIELD_INVALID', 5);

define('DATE_FORM', 'm/d/Y');


/******************************************************************************
 *
 * DEBUG Helpers
 *
 ******************************************************************************/
function debugPrint($variable, $name = '', $die = TRUE)
{
  echo '<h1 class="debug">' . $name . '</h1>';
  echo '<pre>';
  print_r($variable);
  echo '</pre>';
  if ($die)
  {
    die();
  }
}

function getCallingFunction()
{
  $callers = debug_backtrace();
  return $callers[2]['function'];
}

function getCallingFunctionStack()
{
  $callers = debug_backtrace();
  $stack = array();
  foreach ($callers as $caller)
  {
    $stack[] = $caller['function'];
  }

  return implode('->', $stack);
}

function getCallingMethodName()
{
  $e = new Exception();
  $trace = $e->getTrace();
  //position 0 would be the line that called this function so we ignore it
  $last_call = $trace[1];
  return $last_call;
}

function assertFailure($file, $line, $code, $message = '')
{
  echo '<h1>ASSERT FAILURE: </h1>';
  echo '<span class="assert-message">' . '<strong>' . 'Location' . ':</strong> ' . $file . ' line ' . $line . '</span><br />';
  echo '<span class="assert-message">' . '<strong>' . 'Message' . ':</strong> ' . $message . '</span><br />';
  echo '<pre>';
  error_log('assert: ' . $file . ' ' . $line . ' ' . $message);
  $backtrace = debug_backtrace();
  array_shift($backtrace);
  array_shift($backtrace);
  foreach($backtrace AS &$stack_item)
  {
    if (isset($stack_item['object']))
    {
      unset($stack_item['object']);
    }
  }
  $stack = print_r($backtrace, TRUE);
  error_log($stack);
  echo $stack;
  die('</pre>');
}

/******************************************************************************
 *
 * URL Helpers
 *
 ******************************************************************************/
class URL
{
  protected $path = '';
  protected $query = array();
  protected $fragment = '';

  function __construct($url = FALSE)
  {
    if (!$url)
    {
      $url = $_SERVER['REQUEST_URI'];
    }

    // Home path.
    if ($url == '/')
    {
      $this->path = '/';
      return;
    }

    $start = strpos($url, '/') + 1;
    $end = strpos($url, '?');

    // No query string. Only the path is defined.
    if ($end === FALSE)
    {
      $this->path = substr($url, $start);
      return;
    }
    $this->path = substr($url, $start, $end - 1);

    // Build the query string.
    $query = substr($url, $end + 1);
    $query = explode('&', $query);
    foreach ($query as $parameter)
    {
      $parts = explode('=', $parameter);
      $this->query[$parts[0]] = isset($parts[1]) ? urldecode($parts[1]) : FALSE;
    }
  }

  function getPath()
  {
    return $this->path;
  }

  function getQuery()
  {
    return $this->query;
  }
}

function getUrlID($name, $default = FALSE)
{
  if (!isset($_GET[$name]) || !is_numeric($_GET[$name]))
  {
    return $default;
  }

  return abs($_GET[$name]);
}

function redirect($path = FALSE, $statusCode = '303')
{
  if ($path === FALSE)
  {
    GLOBAL $url;
    $path = $url->getPath();
  }
  header('Location: ' . $path, TRUE, $statusCode);
  die();
}

function u($target = '', $options = array())
{
  GLOBAL $url;

  $link = '';
  if (isset($options['external']))
  {
    $link .= $target;
  }
  else
  {
    // If the url should be absolute include the protocol and domain.
    if (isset($options['absolute']))
    {
      $s = '';
      if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")
      {
        $s = 's';
      }      $protocol = strLeft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/") . $s;
      $link .= $protocol . "://" . $_SERVER['SERVER_NAME'];
    }

    // Build the internal link.
    $path = $url->getPath();
    $relative = strpos($target, '/') !== 0;
    if ($relative && !empty($path))
    {
      $link .= '/' . $path;
    }
    if (!empty($target))
    {
      if ($relative)
      {
        $link .= '/';
      }
      $link .= $target;
    }
  }

  // Add query string.
  $preserve_query = array();
  if (isset($options['preserve_query']))
  {
    $preserve_query = $url->getQuery();
  }

  $new_query = array();
  if (isset($options['query']))
  {
    $new_query = $options['query'];
  }

  $query = array_merge($preserve_query, $new_query);
  if (!empty($query))
  {
    $link .= '?';
    $first = TRUE;
    foreach($query as $key => $value)
    {
      if (!$first)
      {
        $link .= '&';
      }
      if ($value !== FALSE)
      {
        $link .= urlencode($key) . '=' . urlencode($value);
        $first = FALSE;
      }
    }
  }

  // Add fragment.
  if (isset($options['fragment']))
  {
    $link .= $options['fragment'];
  }

  return $link;
}

/******************************************************************************
 *
 * HTML Helpers
 *
 ******************************************************************************/
function buildAttr($attr)
{
  assert(is_array($attr), 'Attributes passed to buildAttr should be an array().');
  $attr_string = '';
  foreach ($attr as $name => $value)
  {
    if (is_numeric($name))
    {
      continue;
    }
    if (is_array($value))
    {
      $value = implode(' ', $value);
    }
    $attr_string .= ' ' . $name . '="' . $value . '"';
  }

  return $attr_string;
}

function htmlWrap($tag, $content, $attr = array())
{
  return '<' . $tag . buildAttr($attr) . '>' . $content . '</' . $tag . '>';
}

function htmlSolo($tag, $attr = array())
{
  return '<' . $tag . buildAttr($attr) . '/>';
}

function stringToAttr($string)
{
  $replace = array(' ', '_');
  return strtolower(str_replace($replace, '-', $string));
}

function optionList($list, $selected = FALSE)
{
  $output = '';
  foreach ($list as $key => $value)
  {
    $attr = array(
      'value' => $key,
    );
    if ($key == $selected)
    {
      $attr['selected'] = 'selected';
    }
    $output .= htmlWrap('option', $value, $attr);
  }
  return $output;
}

function strLeft($s1, $s2)
{
  return substr($s1, 0, strpos($s1, $s2));
}

/*****************************************************************************
 *
 * Other Helpers
 *
 ******************************************************************************/

/**
 * @param int $length
 *
 * @return string
 */
function generateRandomString($length = 32)
{
  return base64_encode(openssl_random_pseudo_bytes($length));
}


/******************************************************************************
 *
 *     Sanitation and string processing functions.
 *
 ******************************************************************************/

/**
 * Converts the given string to a machine name.
 *
 * @param string $string
 * @return string
 */
function toMachine($string)
{
  $string = str_replace(' ', '_', $string);
  $string = strtolower($string);
  $string = preg_replace('/[^A-Za-z0-9_-]/', '_',$string);
  return $string;
}

function newLineToHtml($string)
{
  return str_replace("\n", '<br>', $string);
}

/**
 * Sanitizes a string for html output.
 *
 * @param string $data
 * @return string
 */
function sanitize($data)
{
  if (is_null($data))
  {
    return '';
  }
  assert(is_string($data) || is_numeric($data) || is_bool($data), 'Non-string passed to sanitize!');

  $data = dewordify($data);
  $data = trim($data);
  $data = htmlspecialchars($data, ENT_IGNORE | ENT_QUOTES);
  return $data;
}

function dewordify($string)
{
  $search = [                 // www.fileformat.info/info/unicode/<NUM>/ <NUM> = 2018
    "\xC2\xAB",     // « (U+00AB) in UTF-8
    "\xC2\xBB",     // » (U+00BB) in UTF-8
    "\xE2\x80\x98", // ‘ (U+2018) in UTF-8
    "\xE2\x80\x99", // ’ (U+2019) in UTF-8
    "\xE2\x80\x9A", // ‚ (U+201A) in UTF-8
    "\xE2\x80\x9B", // ? (U+201B) in UTF-8
    "\xE2\x80\x9C", // “ (U+201C) in UTF-8
    "\xE2\x80\x9D", // ” (U+201D) in UTF-8
    "\xE2\x80\x9E", // „ (U+201E) in UTF-8
    "\xE2\x80\x9F", // ? (U+201F) in UTF-8
    "\xE2\x80\xB9", // ‹ (U+2039) in UTF-8
    "\xE2\x80\xBA", // › (U+203A) in UTF-8
    "\xE2\x80\x93", // – (U+2013) in UTF-8
    "\xE2\x80\x94", // — (U+2014) in UTF-8
    "\xE2\x80\xA6", // … (U+2026) in UTF-8
  ];

  $replacements = [
    '<<',
    '>>',
    "'",
    "'",
    "'",
    "'",
    '"',
    '"',
    '"',
    '"',
    '<',
    '>',
    '-',
    '-',
    '...'
  ];

  return str_replace($search, $replacements, $string);
}

function sanitizeFileName($name)
{
  $new_name = strtolower($name);
  return preg_replace("/[^a-z-_0-9]+/i", "_", $new_name);
}

// Heavily borrowed from Drupal 7.
function sanitizeXss($string, $allowed_tags = array('a', 'em', 'strong', 'cite', 'blockquote', 'code', 'ul', 'ol', 'li', 'dl', 'dt', 'dd', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'br'))
{
  // Store the text format.
  _sanitizeXssHelper($allowed_tags, TRUE);
  // Remove NULL characters (ignored by some browsers).
  $string = str_replace(chr(0), '', $string);
  // Remove Netscape 4 JS entities.
  $string = preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $string);

  // Defuse all HTML entities.
  $string = str_replace('&', '&amp;', $string);
  // Change back only well-formed entities in our whitelist:
  // Decimal numeric entities.
  $string = preg_replace('/&amp;#([0-9]+;)/', '&#\1', $string);
  // Hexadecimal numeric entities.
  $string = preg_replace('/&amp;#[Xx]0*((?:[0-9A-Fa-f]{2})+;)/', '&#x\1', $string);
  // Named entities.
  $string = preg_replace('/&amp;([A-Za-z][A-Za-z0-9]*;)/', '&\1', $string);

  return preg_replace_callback('%
    (
    <(?=[^a-zA-Z!/])  # a lone <
    |                 # or
    <!--.*?-->        # a comment
    |                 # or
    <[^>]*(>|$)       # a string that starts with a <, up until the > or the end of the string
    |                 # or
    >                 # just a >
    )%x', '_sanitizeXssHelper', $string);
}

function _sanitizeXssHelper($m, $store = FALSE) {
  static $allowed_html;

  if ($store) {
    $allowed_html = array_flip($m);
    return;
  }

  $string = $m [1];

  if (substr($string, 0, 1) != '<') {
    // We matched a lone ">" character.
    return '&gt;';
  }
  elseif (strlen($string) == 1) {
    // We matched a lone "<" character.
    return '&lt;';
  }

  if (!preg_match('%^<\s*(/\s*)?([a-zA-Z0-9\-]+)([^>]*)>?|(<!--.*?-->)$%', $string, $matches)) {
    // Seriously malformed.
    return '';
  }

  $slash = trim($matches [1]);
  $elem = &$matches [2];
  $attrlist = &$matches [3];
  $comment = &$matches [4];

  if ($comment) {
    $elem = '!--';
  }

  if (!isset($allowed_html [strtolower($elem)])) {
    // Disallowed HTML element.
    return '';
  }

  if ($comment) {
    return $comment;
  }

  if ($slash != '') {
    return "</$elem>";
  }

  // Is there a closing XHTML slash at the end of the attributes?
  $attrlist = preg_replace('%(\s?)/\s*$%', '\1', $attrlist, -1, $count);
  $xhtml_slash = $count ? ' /' : '';

  // Clean up attributes.
//  $attr2 = implode(' ', $attrlist); //_filter_xss_attributes($attrlist));
  $attr2 = preg_replace('/[<>]/', '', $attrlist);
  $attr2 = strlen($attr2) ? ' ' . $attr2 : '';

  return "<$elem$attr2$xhtml_slash>";
}

/**
 * @param array $list
 * @param bool|string|int $key
 * @return mixed
 */
function getListItem($list, $key, $default = FALSE)
{
  assert(is_array($list) || empty($list), 'Non-array passed to getListItem function.');
  if ($key === FALSE)
  {
    return $list;
  }
  elseif (!isset($list[$key]))
  {
    return $default;
  }
  return $list[$key];
}

function iis($array, $key, $default = FALSE)
{
  if (is_array($array) && array_key_exists($key, $array))
  {
    return $array[$key];
  }
  return $default;
}