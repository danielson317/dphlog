<?php

/******************************************************************************
 *
 * Form
 *
 ******************************************************************************/

class Form
{
  protected $id;
  protected $attr = array();
  protected $fields = array();
  protected $values = array();
  protected $groups = array();

  protected $title = '';

  function __construct($id)
  {
    $this->id = $id;
  }

  function __toString()
  {
    $output = '';

    // Title
    if ($this->title)
    {
      $output .= htmlWrap('h1', $this->title);
    }

    // Fields.
    $fields_output = $this->buildGroup();

    // Form.
    $attr = $this->attr;
    $attr['id'] = $this->id;
    $attr['method'] = 'post';
    $output .= htmlWrap('form', $fields_output, $attr);
    return $output;
  }

  function setAttr($name, $value)
  {
    $this->attr[$name] = $value;
  }

  function setValues($values)
  {
    $this->values = $values;
  }

  function setTitle($title)
  {
    $this->title = $title;
  }

  function addGroup($group, $parent_group = 'default')
  {
    $this->groups[$group] = $parent_group;
    return $this;
  }

  function addField(Field $field)
  {
    if ($field instanceof FieldFile)
    {
      $this->attr['enctype'] = 'multipart/form-data';
    }
    if (array_key_exists($field->getId(), $this->values))
    {
      $field->setValue($this->values[$field->getId()]);
    }
    $this->fields[] = $field;
  }

  function getAttr($name)
  {
    return isset($this->attr[$name]) ? $this->attr[$name] : FALSE;
  }

  function getTitle()
  {
    return $this->title;
  }

  function buildGroup($group_name = 'default')
  {
    $fields = array();
    foreach($this->fields as $field)
    {
      if ($field->getGroup() == $group_name)
      {
        $fields[] = $field->__toString();
      }
    }

    foreach($this->groups as $group => $group_parent)
    {
      if ($group_parent == $group_name)
      {
        $attr = array(
          'class' => array('field_group', $group),
        );
        $fields[] = htmlWrap('div', $this->buildGroup($group), $attr);
      }
    }

    return implode('', $fields);
  }
}

/******************************************************************************
 *
 * Fields.
 *
 ******************************************************************************/

abstract class Field
{
  protected $id;
  protected $attr = array();
  protected $label = '';
  protected $value = '';
  protected $group = '';

  function __construct($id, $label = '')
  {
    $this->id = $id;
    $this->label = $label;
  }
  abstract function __toString();

  function setLabel($label)
  {
    $this->label = $label;
    return $this;
  }

  function setValue($value)
  {
    $this->value = $value;
    return $this;
  }
  function setGroup($group)
  {
    $this->group = $group;
    return $this;
  }

  function getId()
  {
    return $this->id;
  }

  function getValue()
  {
    return $this->value;
  }

  function setAttr($name, $value)
  {
    $this->attr[$name] = $value;
    return $this;
  }
  function getGroup()
  {
    if (!$this->group)
    {
      return 'default';
    }
    return $this->group;
  }

  function setRequired($required = TRUE)
  {
    if ($required)
    {
      $this->attr['required'] = $required;
    }
    elseif ($this->attr['required'])
    {
      unset($this->attr['required']);
    }
  }
}

class FieldHidden extends Field
{
  function __construct($id, $value = '', $label = '')
  {
    parent::__construct($id, $label);
    $this->value = $value;
  }

  function __toString()
  {
    $attr = $this->attr;
//    $attr['id'] = $this->id;
    $attr['name'] = $this->id;
    $attr['type'] = 'hidden';
    if ($this->value)
    {
      $attr['value'] = $this->value;
    }

    $output = htmlSolo('input', $attr);
    return $output;
  }
}

class FieldText extends Field
{
  function __toString()
  {
    $output = '';

    // Label.
    $attr = array('class' => array('label'), 'for' => $this->id);
    $output .= htmlWrap('label', $this->label, $attr);

    // Input.
    $attr = $this->attr;
//    $attr['id'] = $this->id;
    $attr['name'] = $this->id;
    $attr['type'] = 'text';
    if ($this->value)
    {
      $attr['value'] = $this->value;
    }
    $output .= htmlSolo('input', $attr);

    // Wrapper.
    $attr = array('class' => array('field', 'text', $this->id));
    $output = htmlWrap('div', $output, $attr);
    return $output;
  }
}

class FieldPassword extends Field
{
  function __toString()
  {
    $output = '';

    // Label.
    $attr = array('class' => array('label'), 'for' => $this->id);
    $output .= htmlWrap('label', $this->label, $attr);

    // Input.
    $attr = $this->attr;
//    $attr['id'] = $this->id;
    $attr['name'] = $this->id;
    $attr['type'] = 'password';
    if ($this->value)
    {
      $attr['value'] = $this->value;
    }
    $output .= htmlSolo('input', $attr);

    // Wrapper.
    $attr = array('class' => array('field', 'text', $this->id));
    $output = htmlWrap('div', $output, $attr);
    return $output;
  }
}

class FieldNumber extends Field
{
  function __toString()
  {
    $output = '';

    // Label.
    $attr = array('class' => array('label'), 'for' => $this->id);
    $output .= htmlWrap('label', $this->label, $attr);

    // Input.
    $attr = $this->attr;
//    $attr['id'] = $this->id;
    $attr['name'] = $this->id;
    $attr['type'] = 'number';
    if ($this->value)
    {
      $attr['value'] = $this->value;
    }
    $output .= htmlSolo('input', $attr);

    // Wrapper.
    $attr = array('class' => array('field', 'text', $this->id));
    $output = htmlWrap('div', $output, $attr);
    return $output;
  }
}

class FieldSelect extends Field
{
  protected $options;

  function __construct($id, $name = '', $options = array())
  {
    parent::__construct($id, $name);
    $this->setOptions($options);
  }

  function __toString()
  {
    $output = '';

    // Label.
    $attr = array('class' => array('label'), 'for' => $this->id);
    $output .= htmlWrap('label', $this->label, $attr);

    // Options.
    $select_options = '';
    foreach($this->options as $id => $label)
    {
      $attr = array('value' => $id);
      if ($id == $this->value)
      {
        $attr['selected'] = 'selected';
      }
      $select_options .= htmlWrap('option', $label, $attr);
    }

    // Select.
    $attr = $this->attr;
//    $attr['id'] = $this->id;
    $attr['name'] = $this->id;
    $attr['type'] = 'text';
    $output .= htmlWrap('select', $select_options, $attr);

    // Wrapper.
    $attr = array('class' => array('field', 'select', $this->id));
    $output = htmlWrap('div', $output, $attr);
    return $output;
  }

  function setOptions($options)
  {
    $this->options = $options;
    return $this;
  }
}

class FieldCheckbox extends Field
{
  function __toString()
  {
    $output = '';

    // Input.
    $attr = $this->attr;
//    $attr['id'] = $this->id;
    $attr['name'] = $this->id;
    $attr['type'] = 'checkbox';
    if ($this->value)
    {
      $attr['checked'] = 'checked';
    }
    $output .= htmlSolo('input', $attr);

    // Label.
    $attr = array('id' => array('label'), 'for' => $this->id);
    $output .= htmlWrap('label', $this->label, $attr);

    // Wrapper.
    $attr = array('class' => array('field', 'select', $this->id));
    $output = htmlWrap('div', $output, $attr);
    return $output;
  }
}
class FieldRadio extends Field
{
    protected $options;

     function __construct($id, $label = '')
    {
        parent::__construct($id, $label);
    }

     function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

     function __toString()
    {
        $output = '';

        $attr = array('class' => array('label'), 'for' => $this->id);
        $output .= htmlWrap('label', $this->label, $attr);

        foreach ($this->options as $value => $label) {
            $inputId = $this->id . '_' . $value;

            $inputAttr = array(
                'type' => 'radio',
                'id' => $inputId, 
                'name' => $this->id,
                'value' => $value,
            );

            if ($value == $this->value) 
            {
                $inputAttr['checked'] = 'checked';
            }

            $output .= htmlSolo('input', $inputAttr);
            $output .= htmlWrap('label', $label, array('for' => $inputId));
        }

        // Wrapper.
        $attr = array('class' => array('field', 'radio', $this->id));
        $output = htmlWrap('div', $output, $attr);

        return $output;
    }
}

class FieldTextarea extends Field
{
  protected $rows = 5;
  protected $cols = 100;

  function __toString()
  {
    $output = '';

    // Label.
    $attr = array('class' => array('label'), 'for' => $this->id);
    $output .= htmlWrap('label', $this->label, $attr);

    // Input.
    $attr = $this->attr;
//    $attr['id'] = $this->id;
    $attr['name'] = $this->id;
    $attr['rows'] = $this->rows;
    $attr['cols'] = $this->cols;
    $output .= htmlWrap('textarea', $this->value, $attr);

    // Wrapper.
    $attr = array('class' => array('field', 'textarea', $this->id));
    $output = htmlWrap('div', $output, $attr);
    return $output;
  }

  function setRows($rows)
  {
    $this->rows = $rows;
    return $this;
  }

  function setCols($cols)
  {
    $this->cols = $cols;
  }
}

class FieldSubmit extends Field
{
  function __construct($id, $value = '')
  {
    parent::__construct($id);
    $this->value = $value;
  }

  function __toString()
  {
    // Input.
    $attr = $this->attr;
//    $attr['id'] = $this->id;
    $attr['name'] = $this->id;
    $attr['type'] = 'submit';
    if ($this->value)
    {
      $attr['value'] = $this->value;
    }

    $output = htmlSolo('input', $attr);

    // Wrapper.
    $attr = array('class' => array('field', 'textarea', $this->id));
    $output = htmlWrap('div', $output, $attr);
    return $output;
  }
}
class FieldFile extends Field
{
    function __construct($id, $value = '')
    {
        parent::__construct($id);
        $this->value = $value;
    }

    function setID($id)
    {
        $this->attr = array_merge($this->attr, $id);
        return $this;
    }

    function __toString()
    {
        // Input.
        $attr = $this->attr;
        $attr['name'] = $this->id;
        $attr['type'] = 'file';
        if ($this->value)
        {
            $attr['value'] = $this->value;
        }

        $output = htmlSolo('input', $attr);

        // Wrapper.
        $attr = array('class' => array('field', 'textarea', $this->id));
        $output = htmlWrap('div', $output, $attr);
        return $output;
    }
}

class FieldMarkup extends Field
{
  function __construct($id, $label = '', $value = '')
  {
    parent::__construct($id, $label);
    $this->value = $value;
  }

  function __toString()
  {
    $output = '';

    // Label.
    if ($this->label)
    {
      $attr = array('class' => array('label'), 'for' => $this->id);
      $output .= htmlWrap('label', $this->label, $attr);
    }

    // Output.
    $output .= htmlWrap('div', $this->value, array('class' => 'markup-value'));

    // Wrapper.
    $attr = array('class' => array('field', 'text', $this->id));
    $output = htmlWrap('div', $output, $attr);
    return $output;
  }
}

define('TIME_FORM', 'h:i a');
define('DATE_FORM_INPUT', 'n/j/Y');
define('DATE_SQL', 'Y-m-d');
define('DATETIME_SQL', 'Y-m-d H:i:s');
define('DATE_YEAR', 'Y'); // 2018
define('TIME_SQL', 'H:i:s');
define('TIME_FORM_CAPS', 'h:i A');

class FieldDate extends Field
{
  // Form item info.
  protected $change_month;
  protected $change_year;
  protected $min_year;
  protected $max_year;
  protected $min_date;
  protected $max_date;

  /***************
   * Operators.  *
   ***************/
  /**
   * A standard constructor.
   *
   * Sets up the basic form item. All values are set to default. Using the name.
   * Override default values individually with setters. Something like:
   *    $form_item->setLabel('hello')->setPlaceholder('world');
   * Callbacks (validation and submit) are RegistryEntry items.
   *
   * @param String $name - The machine name of the field. Label is also set to this
   *   value by default.
   * @param String $label -
   */
  public function __construct($name, $label = '')
  {
    parent::__construct($name, $label);
    // For construct assume date of birth for now.
    $this->setDateRangeRelative('1970', '2037');
    $this->setMonthSelect();
    $this->setYearSelect();
  }

  public function __toString()
  {
    $output = '';

    // Label.
    $attr = array('class' => array('label'), 'for' => $this->id);
    $output .= htmlWrap('label', $this->label, $attr);

    // Input.
    $attr = $this->attr;
//    $attr['id'] = $this->id;
    $attr['name'] = $this->id;
    $attr['type'] = 'text';
    $attr['change_month'] = $this->change_month;
    $attr['change_year'] = $this->change_year;
    $attr['min_year'] = $this->min_year;
    $attr['max_year'] = $this->max_year;

    if ($this->value)
    {
      $attr['value'] = $this->value;
    }
    $output .= htmlSolo('input', $attr);

    // Wrapper.
    $attr = array('class' => array('field', 'date', $this->id));
    $output = htmlWrap('div', $output, $attr);
    return $output;
  }

  /**
   * Checks the given value against all validators.
   *
   * @param $value
   * @return $this
   */


  public function setValue($value)
  {
    if ($this->isEmpty($value))
    {
      return $this;
    }
    if (is_object($value) && get_class($value) == 'DateTime')
    {
      $date = $value;
    }
    else
    {
      $date = DateTime::createFromFormat(DATE_FORM_INPUT, $value);
    }

    $this->value = $date->format(DATE_FORM);
    return $this;
  }
  public function getDateObject($value)
  {
    if ($this->isEmpty($value))
    {
      return NULL;
    }

    $date = DateTime::createFromFormat(DATE_FORM_INPUT, $value);
    return $date;
  }

  public function setValueFromObject($value)
  {
    if ($this->isEmpty($value))
    {
      return FALSE;
    }
    assert(is_object($value), 'Value not in expected format.');

    $this->value = $value->format(DATE_FORM);
    return $this;
  }

  /**
   * @param mixed $value
   * @return bool
   */
  public static function isEmpty($value)
  {
    return ($value === '' || $value === NULL || $value === FALSE);
  }

  public function setMinDate($date = FALSE)
  {
    if (!$date)
    {
      $date = new DateTime();
    }
    $this->min_date = $date->format(DATETIME_SQL);
    $this->min_year = $date->format(DATE_YEAR);
    return $this;
  }
  public function setMaxDate($date = FALSE)
  {
    if (!$date)
    {
      $date = new DateTime();
    }
    $this->max_date = $date->format(DATETIME_SQL);
    $this->max_year = $date->format(DATE_YEAR);
    return $this;
  }
  public function setDateRange($min_date = FALSE, $max_date = FALSE)
  {
    if (!$min_date)
    {
      $min_date = new DateTime('-120years');
    }
    else
    {
      $min_date = new DateTime($min_date);
    }
    $this->min_date = $min_date->format(DATETIME_SQL);
    $this->min_year = $min_date->format(DATE_YEAR);

    if (!$max_date)
    {
      $max_date = new DateTime();
    }
    else
    {
      $max_date = new DateTime($max_date);
    }
    $this->max_date = $max_date->format(DATETIME_SQL);
    $this->max_year = $max_date->format(DATE_YEAR);
    return $this;
  }
  public function setDateRangeRelative($min_date, $max_date)
  {
    $min = new DateTime($min_date);
    $max = new DateTime($max_date);
    $this->min_date = $min->format(DATETIME_SQL);
    $this->max_date = $max->format(DATETIME_SQL);
    $this->min_year = $min->format(DATE_YEAR);
    $this->max_year = $max->format(DATE_YEAR);
    return $this;
  }

  public function setYearRange($min, $max)
  {
    $this->min_year = $min;
    $this->min_date = (new DateTime('January 1 ' . $min . 'years'))->format(DATETIME_SQL);
    $this->max_year = $max;
    $this->max_date = (new DateTime('January 1 ' . $max . 'years'))->format(DATETIME_SQL);
    return $this;
  }

  public function getMonthSelect()
  {
    return $this->change_month;
  }
  public function setMonthSelect($select = TRUE)
  {
    $this->change_month = $select;
    return $this;
  }
  public function getYearSelect()
  {
    return $this->change_year;
  }
  public function setYearSelect($select = TRUE)
  {
    $this->change_year = $select;
    return $this;
  }
  private function _getOffsetYear($year_offset)
  {
    $current_year = date('Y');
    $operator = substr($year_offset, 0, 1);
    $offset = substr($year_offset, 1);
    switch ($operator)
    {
      case '-':
        $year = (int)$current_year - (int)$offset;
        break;
      case '+':
        $year = (int)$current_year + (int)$offset;
        break;
      default:
        $year = $this->min_year;
    }
    return $year;
  }
}

class FieldTime extends FieldDate
{
  // Form item info.
  protected $step;
  protected $min_time;
  protected $max_time;
  protected $duration = FALSE;

  /***************
   * Operators.  *
   ***************/
  /**
   * A standard constructor.
   *
   * Sets up the basic form item. All values are set to default. Using the name.
   * Override default values individually with setters. Something like:
   *    $form_item->setLabel('hello')->setPlaceholder('world');
   * Callbacks (validation and submit) are RegistryEntry items.
   *
   * @param String $name - The machine name of the field. Label is also set to this
   *   value by default.
   * @param String $label -
   */
  public function __construct($name, $label = '')
  {
    parent::__construct($name, $label);
    $this->type = 'time';

    // Settings specific to this form item.
    $this->setTimeRange('12:00 AM', '11:59 PM');
    $this->setStep('15');
  }

  /***************
   * Processors. *
   ***************/
  public function __toString()
  {
    $output = '';

    // Label.
    $attr = array('class' => array('label'), 'for' => $this->id);
    $output .= htmlWrap('label', $this->label, $attr);

    // Input.
    $attr = $this->attr;
//    $attr['id'] = $this->id;
    $attr['name'] = $this->id;
    $attr['type'] = 'text';
    $attr['step'] = $this->step;
//    $attr['change_year'] = $this->change_year;
//    $attr['min_year'] = $this->min_year;
//    $attr['max_year'] = $this->max_year;

    if ($this->value)
    {
      $attr['value'] = $this->value;
    }
    $output .= htmlSolo('input', $attr);

    // Wrapper.
    $attr = array('class' => array('field', 'time', $this->id));
    $output = htmlWrap('div', $output, $attr);
    return $output;
  }

  public function getMinTime()
  {
    return $this->min_time;
  }
  public function getMaxTime()
  {
    return $this->max_time;
  }
  public function setTimeRange($min, $max)
  {
    $this->min_time = $min;
    $this->max_time = $max;
    return $this;
  }

  public function getStep()
  {
    return $this->step;
  }
  public function setStep($step)
  {
    $this->step = $step;
    return $this;
  }

  public static function formatDbToForm($value)
  {
    $date = DateTime::createFromFormat(DATETIME_SQL, $value);
    if (!$date)
    {
      $date = DateTime::createFromFormat(TIME_SQL, $value);
    }
    assert(is_object($date), 'could not create time from value');
    return $date->format(TIME_FORM_CAPS);
  }
  public static function formatDateTimeToHour($value)
  {
    $date = DateTime::createFromFormat(DATETIME_SQL, $value);
    assert(is_object($date), 'Could not create datetime object.');
    if (!is_object($date))
    {
      return '12:00';
    }
    return $date->format('H');
  }
  public static function formatSqlTimeToForm($value)
  {
    $date = DateTime::createFromFormat(TIME_SQL, $value);
    return $date->format(TIME_FORM_CAPS);
  }
  public static function isEmpty($value)
  {
    return ($value === '' || $value === NULL);
  }
}
