<?php
function imagePage()
{
  $template = new HTMLTemplate();
  $template->setTitle('Unserialize');
  $template->addCssFilePath('/themes/default/css/image.css');
  $template->addJsFilePath('/modules/unserialize/image.js');

  $template->setMenu(menu());

  $form = new Form('unserialize');

  $field = new FieldFile('base64-check', 'Base64 Decode');
  $field->setValue(TRUE);
  $form->addField($field);

  $field = new FieldCheckbox('unserialize-check', 'Unserialize');
  $field->setValue(TRUE);
  $form->addField($field);

  $field = new FieldTextarea('string-input', 'String Input');
  $form->addField($field);

  $field = new FieldSubmit('submit', 'Submit');
  $form->addField($field);

  $body = $form . htmlWrap('pre', '', array('class' => array('unserialize_output')));
  $template->setBody(htmlWrap('h1', 'Unserialize') . $body);
  return $template;
}
