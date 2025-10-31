$(document).ready(function()
{
  // CONSTANTS - Prices.
  const QEMR_FULL_TIME_PRICE = 69;
  const OTHER_FULL_TIME_PRICE = 180;

  const QEMR_PART_TIME_PRICE = 35;
  const OTHER_PART_TIME_PRICE = 180;

  const QEMR_SUPPORT_PRICE = 0;
  const OTHER_SUPPORT_PRICE = 59;

  const QEMR_CONNECT_PRICE = 0;
  const OTHER_CONNECT_PRICE = 399;

  const QEMR_CALL_PRICE = 0.12;
  const OTHER_CALL_PRICE = 0.21;

  let $price_calculator = $('#price_calculator');

  // Input IDs.
  let $full_time_provider_input = $price_calculator.find("#full_time_provider input.value");
  let $qemr_provider_value = $price_calculator.find("#qemr-provider-value");
  let $other_provider_value = $price_calculator.find("#other-provider-value");

  let $part_time_provider_input = $price_calculator.find("#part_time_provider input.value");
  let $qemr_pt_provider_value = $price_calculator.find("#qemr-pt-provider-value");
  let $other_pt_provider_value = $price_calculator.find("#other-pt-provider-value");

  let $support_input = $price_calculator.find("#support input.value");
  let $qemr_support_value = $price_calculator.find("#qemr-support-value");
  let $other_support_value = $price_calculator.find("#other-support-value");

  let $connect_input = $price_calculator.find("#connect-input");
  let $qemr_connect_value = $price_calculator.find("#qemr-connect-value");
  let $other_connect_value = $price_calculator.find("#other-connect-value");

  let $reminder_calls_input = $price_calculator.find("#reminder_calls input.value");
  let $qemr_reminder_calls_value = $price_calculator.find("#qemr-calls-value");
  let $other_reminder_calls_value = $price_calculator.find("#other-calls-value");

  let $totals_row = $price_calculator.find(".totals");
  let $savings_row = $price_calculator.find(".savings");

  // Prices * input values.
  let qemr_ft_amount = 0;
  let qemr_pt_amount = 0;
  let qemr_support_amount = 0;
  let qemr_connect_amount = 0;
  let qemr_calls_amount = 0;

  let other_ft_amount = 0;
  let other_pt_amount = 0;
  let other_support_amount = 0;
  let other_connect_amount = 0
  let other_calls_amount = 0;

  // On totals refresh.
  $totals_row.on("refresh", function()
  {
    let total = (qemr_ft_amount + qemr_pt_amount + qemr_calls_amount + qemr_support_amount + qemr_connect_amount);
    $totals_row.find(".qemr").text("$" + total.toFixed(2));
    let other_total = other_ft_amount + other_pt_amount + other_calls_amount + other_support_amount + other_connect_amount;
    $totals_row.find(".other").text("$" + other_total.toFixed(2));
    let savings = other_total - total;
    $savings_row.find(".saved").text("$" + savings.toFixed(2));
  });

  // Full time provider cells.
  $full_time_provider_input.change(function()
  {
    qemr_ft_amount = calculatePrices($full_time_provider_input, QEMR_FULL_TIME_PRICE, $qemr_provider_value);
    other_ft_amount = calculatePrices($full_time_provider_input, OTHER_FULL_TIME_PRICE, $other_provider_value);
    $totals_row.trigger("refresh");
  }).change();

  // Part time provicer cells.
  $part_time_provider_input.change(function()
  {
    qemr_pt_amount = calculatePrices($part_time_provider_input, QEMR_PART_TIME_PRICE, $qemr_pt_provider_value);
    other_pt_amount = calculatePrices($part_time_provider_input, OTHER_PART_TIME_PRICE, $other_pt_provider_value);
    $totals_row.trigger("refresh");
  }).change();


  // Support Personnel cells.
  $support_input.change(function()
  {
    qemr_support_amount = calculatePrices($support_input, QEMR_SUPPORT_PRICE, $qemr_support_value);
    other_support_amount = calculatePrices($support_input, OTHER_SUPPORT_PRICE, $other_support_value);
    $totals_row.trigger("refresh");
  }).change();

  // Connect cells.
  // $qemr_connect_value.text("$" + QEMR_CONNECT_PRICE.toFixed(2));
  // $other_connect_value.text("$" + OTHER_CONNECT_PRICE.toFixed(2));

  $connect_input.click(function()
  {
    if ($connect_input.is(":checked"))
    {
      qemr_connect_amount = QEMR_CONNECT_PRICE;
      other_connect_amount = OTHER_CONNECT_PRICE;
    }
    else
    {
      qemr_connect_amount = 0;
      other_connect_amount = 0;
    }

    $qemr_connect_value.text("$" + qemr_connect_amount.toFixed(2));
    $other_connect_value.text("$" + other_connect_amount.toFixed(2));

    $totals_row.trigger("refresh");
  });

  // Reminder calls cells.
  $reminder_calls_input.change(function()
  {
    qemr_calls_amount = calculatePrices($reminder_calls_input, QEMR_CALL_PRICE, $qemr_reminder_calls_value);
    other_calls_amount = calculatePrices($reminder_calls_input, OTHER_CALL_PRICE, $other_reminder_calls_value);
    $totals_row.trigger("refresh");
  }).change();

  $('.number-field-wrapper').each(function()
  {
    let $this = $(this);
    let $value = $this.find('input.value');
    let $increment = parseInt($value.attr('increment'));
    $this.find('.minus').click(function()
    {
      if ($value.val() <= parseInt($value.attr('min')))
      {
        $this.val(0);
        return;
      }
      $value.val(parseInt($value.val()) - $increment);
      $value.change();
    });

    $this.find('.plus').click(function()
    {
      $value.val(parseInt($value.val()) + $increment);
      $value.change();
    });
  });
});

function calculatePrices(input_cell, price, output_cell)
{
  // Get user input.
  let cell_value = parseFloat(input_cell.val());

  // Calculate feature price.
  let amount = cell_value * (price.toFixed(2));

  // Update calculated price on screen.
  output_cell.text("$" + amount.toFixed(2));

  return amount;
}
