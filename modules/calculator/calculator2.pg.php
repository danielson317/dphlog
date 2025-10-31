<?php

function calculator2Page()
{
  $output = '';
  $output .= htmlWrap('script', '', array('src' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js'));
  $output .= htmlWrap('script', file_get_contents(ROOT_PATH . '/modules/calculator/calculator2.js'));
  $output .= htmlWrap('style', file_get_contents(ROOT_PATH . '/modules/calculator/calculator2.css'));
  $output .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';

  /*****************************
   *   Header
   *****************************/
  $header = htmlWrap('span', htmlWrap('span', '', array('class' => array('logo'))), array('class' => array('label', 'qemr'))); //'QuickEMR'
  $header .= htmlWrap('div', '', array('class' => array('operation')));
  $header .= htmlWrap('span', 'WebPT', array('class' => array('label', 'other')));

  $header = htmlWrap('div', $header, array('class' => array('header')));

  /*****************************
   *   Body
   *****************************/
  $body = '';

  // Full time providers
  $left = htmlWrap('div', 'Full Time Providers', array('class' => array('service')));
//  $left = htmlWrap('div', $left, array('class' => array('left')));

  $right = htmlWrap('div', '$0.00', array('id' => 'qemr-provider-value', 'class' => array('qemr', 'amount')));
  $right .= htmlWrap('div', fieldNumber('full_time_provider', 1), array('class' => array('operation')));
  $right .= htmlWrap('div', '$0.00', array('id' => 'other-provider-value', 'class' => array('other', 'amount')));
  $right = htmlWrap('div', $right, array('class' => array('right')));
  $body .= htmlWrap('div', $left . $right, array('class' => array('provider-info', 'price-line')));

  // Part time providers
  $left = htmlWrap('div', 'Part Time Providers', array('class' => array('service')));
//  $left = htmlWrap('div', $left, array('class' => array('left')));

  $right = htmlWrap('div', '$0.00', array('id' => 'qemr-pt-provider-value', 'class' => array('qemr', 'amount')));
  $right .= htmlWrap('div', fieldNumber('part_time_provider'), array('class' => array('operation')));
  $right .= htmlWrap('div', '$0.00', array('id' => 'other-pt-provider-value', 'class' => array('other', 'amount')));
  $right = htmlWrap('div', $right, array('class' => array('right')));
  $body .= htmlWrap('div', $left . $right, array('class' => array('pt-provider-info', 'price-line')));

  // Support personnel.
  $left = htmlWrap('div', 'Non-Provider Users', array('class' => array('service')));
//  $left = htmlWrap('div', $left, array('class' => array('left')));

  $right = htmlWrap('div', '$0.00', array('id' => 'qemr-support-value', 'class' => array('qemr', 'amount')));
  $right .= htmlWrap('div', fieldNumber('support'), array('class' => array('operation')));
  $right .= htmlWrap('div', '$0.00', array('id' => 'other-support-value', 'class' => array('other', 'amount')));
  $right = htmlWrap('div', $right, array('class' => array('right')));
  $body .= htmlWrap('div', $left . $right, array('class' => array('support-info', 'price-line')));

  // Connect.
  $left = htmlWrap('div', 'Connect/Reach Patients', array('class' => array('service')));
  $attr = array(
    'type' => 'checkbox',
    'id' => 'connect-input',
    'class' => array('conversion-inputs'),
  );
  $connect_input = htmlSolo('input', $attr);
  $connect_input .= htmlWrap('span', '', array('class' => array('slider', 'round')));
  $connect_input = htmlWrap('label', $connect_input, array('class' => array('switch')));
//  $left = htmlWrap('div', $left, array('class' => array('left')));

  $right = htmlWrap('div', '$0.00', array('id' => 'qemr-connect-value', 'class' => array('qemr', 'amount')));
  $right .= htmlWrap('div', $connect_input, array('class' => array('operation')));
  $right .= htmlWrap('div', '$0.00', array('id' => 'other-connect-value', 'class' => array('other', 'amount')));
  $right = htmlWrap('div', $right, array('class' => array('right')));
  $body .= htmlWrap('div', $left . $right, array('class' => array('connect-info', 'price-line')));

  // Reminder calls.
  $left = htmlWrap('div', 'Reminder Calls', array('class' => array('service')));
//  $left = htmlWrap('div', $left, array('class' => array('left')));

  $right = htmlWrap('div', '$0.00', array('id' => 'qemr-calls-value', 'class' => array('qemr', 'amount')));
  $right .= htmlWrap('div', fieldNumber('reminder_calls', 0, 50), array('class' => array('operation')));
  $right .= htmlWrap('div', '$0.00', array('id' => 'other-calls-value', 'class' => array('other', 'amount')));
  $right = htmlWrap('div', $right, array('class' => array('right')));
  $body .= htmlWrap('div', $left . $right, array('class' => array('calls-info', 'price-line')));

  // Total.
//  $left = htmlWrap('div', $left, array('class' => array('left')));

  $right = htmlWrap('div', '$69.00', array('class' => array('qemr', 'amount')));
  $right .= htmlWrap('div', '', array('class' => array('operation')));
//  $right .= htmlWrap('div', '', array('class' => array('operation')));
  $right .= htmlWrap('div', '$180.00', array('class' => array('other', 'amount')));
  $right = htmlWrap('div', $right, array('class' => array('right')));
  $body .= htmlWrap('div', $right, array('class' => array('totals', 'price-line')));

  // Savings.
  $footer = htmlWrap('div', 'Save ' . htmlWrap('span', '$111.00', array('class' => array('saved'))) . ' per month!', array('class' => array('savings', 'price-line')));

  // Combine items.
  $output .= htmlWrap('div', $header . $body . $footer, array('id' => 'price_calculator'));

  die($output);
}

//function fieldNumber($id, $min = 0, $increment = 1)
//{
//  $field = '';
//
//  // 1
//  $attr = array(
//    'type' => 'button',
//    'value' => '',
//    'class' => array('minus', 'operator'),
//  );
//  $field .= htmlSolo('input', $attr);
//
//  // Value.
//  $attr = array(
//    'type' => 'textfield',
//    'min' => $min,
//    'value' => $min,
//    'increment' => $increment,
////    'id' => $id,
//    'class' => array('value'),
//  );
//  $field .= htmlSolo('input', $attr);
//
//  // +
//  $attr = array(
//    'type' => 'button',
//    'value' => '',
//    'class' => array('plus', 'operator'),
//  );
//  $field .= htmlSolo('input', $attr);
//
//  return htmlWrap('div', $field, array('id' => $id, 'class' => array('number-field-wrapper')));
//}
