<?php
function logView()
{
  $file_handle = fopen(LOG_LOCATION, 'r');

  $entry = '';
  $count = 0;
  $timestamp = '';
  $matches = array();
  $filtered_entries = array();
  while (($line = fgets($file_handle)) !== FALSE)
  {
    // If this is the first line of an entry (identified by the timestamp).
    if (preg_match('/^\[(\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2}) (.*)\](.*)/', $line, $matches))
    {
      // First item in the log.
      if ($count === 0)
      {
        $timestamp = $matches[1];
        $entry = $matches[3];
        $count++;
        continue;
      }

      // Identify type.
      $type = 'debug';
      if (strpos($entry, 'PHP Parse error:') !== FALSE)
      {
        $type = 'php_parse_error';
      }
      if (strpos($entry, 'PHP Fatal error:') !== FALSE)
      {
        $type = 'php_fatal_error';
      }
      if (strpos($entry, 'PHP Warning:') !== FALSE)
      {
        $type = 'php_warning';
      }
      elseif (strpos($entry, 'assert') !== FALSE)
      {
        $type = 'assert';
      }

      // Record the previous entry.
      $row = array(
        $type,
        $timestamp,
        '<pre>' . $entry . '</pre>',
      );

      // Limit results to the last 100 entries.
      array_unshift($filtered_entries, $row);
//      array_push($last_100, $row);
      if ($count > 100)
      {
//        break;
        array_pop($filtered_entries);
      }
      else
      {
        $count++;
      }

      // Start a new entry.
      $timestamp = $matches[1];
      $entry = $matches[3];
      continue;
    }
    $entry .= $line;
  }

  $table = new TableTemplate('log_table');
  foreach($filtered_entries as $row)
  {
    $table->addRow($row, array('class' => array($row[0])));
  }

  return $table;
}