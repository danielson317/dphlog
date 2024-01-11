<?php
DEFINE('ROOT_PATH', __DIR__ . '/..');

// Include the platform file.
$platform_path = ROOT_PATH . '/platform.inc.php';
if (!file_exists($platform_path))
{
  die('platform.inc.php is missing.');
}
include $platform_path;

// Debug Flag. Set to FALSE for production. When true errorsPrint will display
// in the messages and debug statements will actually execute.
if (DEBUG)
{
  assert_options(ASSERT_ACTIVE,   TRUE);
  assert_options(ASSERT_BAIL,     TRUE);
  assert_options(ASSERT_WARNING,  TRUE);
  assert_options(ASSERT_CALLBACK, 'assertFailure');
  function errorHandler($severity, $message, $file, $line)
  {
    assert(FALSE, $message);
  }

  set_error_handler('errorHandler');
}
else
{
  assert_options(ASSERT_ACTIVE,   FALSE);
}

// Timezone must be set initially or any error prints will produce warnings.
// Set to something until the proper time zone can be pulled form the db.
date_default_timezone_set('America/Denver');


include ROOT_PATH . '/libraries/global.inc.php';
include ROOT_PATH . '/libraries/database/database.inc.php';
include ROOT_PATH . '/libraries/database/sqlite.inc.php';
include ROOT_PATH . '/libraries/form.inc.php';
include ROOT_PATH . '/libraries/templates/template.inc.php';
include ROOT_PATH . '/libraries/session/session.db.php';
include ROOT_PATH . '/libraries/session/session.inc.php';
include ROOT_PATH . '/libraries/user/user.db.php';
include ROOT_PATH . '/libraries/user/user.inc.php';
include ROOT_PATH . '/libraries/user/user.pg.php';

include ROOT_PATH . '/modules/log/log.inc.php';
include ROOT_PATH . '/modules/log/log.pg.php';

include ROOT_PATH . '/modules/json_formatter/json_formatter.pg.php';

include ROOT_PATH . '/modules/unserialize/unserialize.pg.php';

include ROOT_PATH . '/modules/time/time.pg.php';
include ROOT_PATH . '/modules/time/time.inc.php';

include ROOT_PATH . '/modules/calculator/calculator.pg.php';
include ROOT_PATH . '/modules/calculator/calculator2.pg.php';

include ROOT_PATH . '/modules/image_converter/image_converter.pg.php';
