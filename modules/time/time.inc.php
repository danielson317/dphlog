<?php

function timeForm()
{
  $form = new Form('time_converter');

  // Timestamp
  $field = new FieldText('timestamp', 'Timestamp');
  $form->addField($field);

  // Date
  $field = new FieldDate('date', 'Date');
  $field->setValue('02/01/2023');
  $form->addField($field);

  // Time
  $field = new FieldTime('time', 'Time');
  //$field->setValue('2:00 PM');
  $form->addField($field);

  // Timezone
  $user = getActiveUser();
  $list = getTimeTimezoneList();
  $field = new FieldSelect('timezone', 'Timezone', $list);
  $field->setValue($user['timezone']);
  $form->addField($field);

  // Convert button
  $field = new FieldSubmit('convert-button', 'Convert');
  $form->addField($field);

  $field = new FieldSubmit('now-button', 'Now');
  $form->addField($field);
  return $form;
}
  

/**
 * @param $timezone: String containing iana time zone in this format 'America/Chicago'.
 * @param $users_in_zone: A list of the users in the timezone, previously sorted and gathered from the db.
 * @return: Wrapped html string containing a completed clock based on the timezone given.
 */
function timeClock($timezone, $users_in_zone)
{
  $output = '';
  $output .= htmlWrap('h2', getTimeTimezoneList($timezone), array('class' => array('header')));

  $face = htmlWrap('div', null, array('class' => array('marking marking-one')));
  $face .= htmlWrap('div', null, array('class' => array('marking marking-two')));
  $face .= htmlWrap('div', null, array('class' => array('marking marking-three')));
  $face .= htmlWrap('div', null, array('class' => array('marking marking-four')));

  $face = htmlWrap('div', $face, array('class' => array('outer-clock-face')));

  // Create clock hands.
  $inner_face = htmlWrap('div', null, array('class' => array('hand hour-hand')));
  $inner_face .= htmlWrap('div', null, array('class' => array('hand min-hand')));
  $inner_face .= htmlWrap('div', null, array('class' => array('hand second-hand')));
  $inner_face = htmlWrap('div', $inner_face, array('class' => array('inner-clock-face')));

  $output .=  htmlWrap('div', $face . $inner_face, array('class' => array('clock')));

  $output .= htmlWrap('div', '01:00:00 PM', array('class' => array('time')));
  $output .= htmlWrap('div', $timezone, array('class' => array('timezone')));

  $user_list = '';

  // If the user list for that timezone is empty, add a None to the list.
  if ($users_in_zone)
  {
    foreach ($users_in_zone as $user)
    {
      $user_list .= htmlWrap('li', $user, array('class' => array('user')));
    }
    $output .= htmlWrap('ul', $user_list, array('class' => array('user-list')));
  }

  $attr = array (
    'id' => array(toMachine($timezone)),
    'class' => array('clock-wrap')
  );

  return htmlWrap('div', $output, $attr);
}

function getTimeTimezoneList($key = FALSE)
{
  $list = array(
    'UTC' => 'UTC',
    'America/New_York' => 'Eastern',
    'America/Chicago' => 'Central',
    'America/Denver' => 'Mountain',
    //'America/Phoenix' => 'Mountain (Arizona)',
    'America/Los_Angeles' => 'Pacific',
    'America/Anchorage' => 'Alaska',
    'America/Adak' => 'Hawaii',
    //'Pacific/Honolulu' => 'Hawaii (No Daylight Savings)',
  );
  return getListItem($list, $key);
}

/**
 * @return $user_hash_map: A map of users sorted by timezone.
 * Gets the user list from the database and sorts each user into their timezones.
 */
function sortUsers()
{
  // Get user list from database.
  $users = getUserList();


  // Initialize map with full timezone list.
  $user_hash_map = getTimeTimezoneList();
  foreach($user_hash_map as $key => $value)
  {
    $user_hash_map[$key] = array();
  }

  foreach ($users as $user)
  {
    $curr_user = $user['username'];
    $curr_timezone = $user['timezone'];

    // If the current timezone key does not exist in the hashmap yet, add it.
    if (!array_key_exists($curr_timezone, $user_hash_map)){
      $user_hash_map[$curr_timezone] = array();
    }
    // Then add the current user to that timezone map.
    $user_hash_map[$curr_timezone][] = $curr_user;
  }

  return $user_hash_map;
}