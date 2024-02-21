
/**
 * Created by Austin 2/6/2023
 */
var state = 'now';
var main_interval;

$(document).ready(function()
{
let $date_and_epoch = $('.date-and-epoch');
let $form = $('#time_converter');

  // Time conversions from milliseconds.
  const MILI_SEC = 1000;

  // Initialize clocks.
  clockStep();
  /*******************
   * Button to stop time and convert to inputted time.
   *******************/
  $form.find('.convert-button input').click(function(e)
  {
    e.preventDefault();
    clearInterval(main_interval);

    state = 'then';
    clockStep();

    // Add text to show clocks are paused.
    $date_and_epoch.find('#clock-state').text('Clocks Paused');

    let timezone_value = $form.find('.field.timezone select').val();

    // Remove red border from everything, then add it to desired conversion clock.
    $('.current-timezone').removeClass('current-timezone');
    $('.clock-wrap#' + machineName(timezone_value)).addClass('current-timezone');
  });

  /**********************************************************
   * Now/reset button, resets the clocks to the current time
   **********************************************************/
  $form.find('.now-button input').click(function(e)
    {
      e.preventDefault();
      state = 'now';

      // Get local timezone.
      let user_time_zone = new Intl.DateTimeFormat().resolvedOptions().timeZone;

      // Remove Clocks Paused text after click.
      $date_and_epoch.find('#clock-state').text('');

      // // Clear left form.
      $form.find('.field.timestamp input').val('');
      $form.find('.field.time input').val('');
      $form.find('.field.date input').val('');


      //Add red border to the local timezone clock.
      $('.current-timezone').removeClass('current-timezone');
      $('.clock-wrap#' + machineName(user_time_zone)).addClass('current-timezone');
      main_interval = setInterval(clockStep, MILI_SEC);
    }).click();
 /*******************
   * Button to copy unix time to clipboard.
   *******************/

  // if copy button is clicked copy unix time to clipboard
  $date_and_epoch.find('.copy-button').click(function(e) 
  {
    
    let unix_time_value = $date_and_epoch.find('.unix-time').text();

    let temp_value = $('<textarea>');
    
    $('body').append(temp_value);

    temp_value.val(unix_time_value).select();

    document.execCommand('copy');

    temp_value.remove();
  });
});

/**
 * Main loop for the clocks. Updates every second in the setInterval loop
 */
function clockStep()
{
  let datetime;
  let date_time;
  let epoch;

  // Set up options for date time format.
  let options =
    {
      day: 'numeric',
      month: 'numeric',
      year: 'numeric'
    };

  // "Now" means get current time from client side.
  // If the button is pressed (state is now), then set date to current time.
  if (state === 'now')
  {
    datetime = new Date();
    date_time = new Intl.DateTimeFormat('en-US', options).format(datetime);
    epoch = Math.floor(Date.now() / 1000);

  }
  // Otherwise set the date to the inputted time.
  else
  {
    let $form = $('#time_converter');

    let timestamp = $form.find('.field.timestamp input').val();
    let time_value = $form.find('.field.time input').val();
    let date_value = $form.find('.field.date input').val();
    let timezone = $form.find('.field.timezone select').val();

    // If timestamp is empty, use input from date/time section.
    if (timestamp === "")
    {
      //default timezone to user setting
      datetime = parseDateTimeInputData(time_value, date_value, timezone);
      date_time = new Intl.DateTimeFormat('en-US', options).format(datetime);

      epoch = Math.floor(datetime.getTime() / 1000);
    }
    // Otherwise use the input from the timestamp field.
    else
    {
      datetime = new Date(timestamp * 1000);
      date_time = new Intl.DateTimeFormat('en-US', options).format(datetime);
      epoch = Math.floor(datetime.getTime() / 1000);
    }
  }

  // Full dictionary of iana timezones => general zone. matches with dict in php file.
  const zone_dict =
    {
      'UTC': 'UTC',
      'America/New_York': 'Eastern',
      'America/Chicago': 'Central',
      'America/Denver': 'Mountain',
      //'America/Phoenix': 'Mountain (Arizona)',
      'America/Los_Angeles': 'Pacific',
      'America/Anchorage': 'Alaska',
      'America/Adak': 'Hawaii',
      //'Pacific/Honolulu': 'Hawaii (No Daylight Savings)'
    };

  // Create datetimeformat object based on user's machine's current time.
  $('.current-date').html(date_time);

  // Get # of miliseconds since jan 1, 1970 and divide by 1000 to get seconds.
  $('.unix-time').html(epoch);

  // Update display time and clock hands for each timezone.
  for (let key in zone_dict)
  {
    startClockHands(datetime, key);
  }
}

/**
 * @param datetime: js Date object
 * @param iana_timezone: iana timezone string in "America/New_York" format
 * Updates the clock hands based on the time of the desired timezone.
 */
function startClockHands(datetime, iana_timezone)
{
  let options =
    {
      hour: 'numeric',
      minute: 'numeric',
      second: 'numeric',
      timeZone: iana_timezone
    };

  let full = new Intl.DateTimeFormat('en-US', options).format(datetime);

  //Get hr, mins, sec from the desired time zone, outputs as string type.
  let seconds = new Intl.DateTimeFormat('en-US', {second: 'numeric', timeZone: iana_timezone, hour12: false}).format(datetime);
  let mins = new Intl.DateTimeFormat('en-US', {minute: 'numeric', timeZone: iana_timezone, hour12: false}).format(datetime);
  let hour = new Intl.DateTimeFormat('en-US', {hour: 'numeric', timeZone: iana_timezone, hour12: false}).format(datetime);

  //Create jquery selector and parse the id based on the ianaTimeZone (same as in php).
  let $clock = $('#' + machineName(iana_timezone));

  //Calculate sec,min,hr and transform each hand based on the value.
  //Finds the given id for each timezone, and updates the hands on each clock.
  const seconds_degrees = ((seconds / 60) * 360) + 90;
  $clock.find('.second-hand').css('transform', 'rotate(' + seconds_degrees + 'deg');

  const mins_degrees = ((mins / 60) * 360) + ((seconds/60)*6) + 90;
  $clock.find('.min-hand').css('transform', 'rotate(' + mins_degrees + 'deg');

  const hour_degrees = ((hour / 12) * 360) + ((mins/60)*30) + 90;
  $clock.find('.hour-hand').css('transform', 'rotate(' + hour_degrees + 'deg');

  //Pass data into .php file for web interface display.
  $clock.find('.time').text(full);
}

/**
 * @param time: time string in "12:00 am" format
 * @param date: date in "m/d/y" format
 * @param timezone: timezone from form
 * @returns {Date}: Date() object for intl use
 * Takes user's input from form and parses input into intl library readable format.
 */
function parseDateTimeInputData(time, date, timezone)
{
  //Parse time string.
  let parsed_time = time.match(/(\d{2}):(\d{2}) (am|pm)/);
  let hours = parsed_time[1];
  let minutes = parsed_time[2];
  let ampm = parsed_time[3];

  //Distinguish between am/pm.
  if (ampm === 'pm' && hours !== '12')
  {
    hours = (parseInt(hours, 10) + 12).toString();
  } else if (ampm === 'am' && hours === '12')
  {
    hours = '00';
  }

  //Parse date string.
  let parsed_date = date.match(/(\d{2})\/(\d{2})\/(\d{4})/);
  let month = parseInt(parsed_date[1]) - 1;
  let day = parsed_date[2];
  let year = parsed_date[3];

  //Convert to Date() object.
  let temp = new Date(year, month, day, hours, minutes);

  let options =
  {
    timeZone: timezone,
    hour: 'numeric',
    timeZoneName: 'shortOffset'
  }

  // +4 hardcoded to offset UTC time to EST.
  let selected_offset = new Intl.DateTimeFormat('en-US', options).format(temp);
  selected_offset = parseInt(selected_offset.substring(selected_offset.indexOf('GMT-') + 4));

  let datetime = new Date(Date.UTC(year, month, day, hours, minutes));
  datetime.setHours(datetime.getHours() + selected_offset);

  return datetime;
}