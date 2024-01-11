<?php
function imageConverter()
{
  $template = new HTMLTemplate();
  $template->setTitle('Image Converter');
  $template->addJsFilePath('/modules/image_converter/image_converter.js');
  $template->addCssFilePath('/modules/image_converter/image_converter.css');
  $template->setMenu(menu());

  $form = imageConverterForm();

  $body = $form . htmlWrap('body', '', ['class' => ['image_converter_body']]);
  $template->setBody(htmlWrap('h1', 'Image Converter') . $body);

  return $template;
}

/*******************
 * Button to choose a file and convert the file.
 *******************/
function imageConverterForm()
{
  // Check if the form has been submitted
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['choose-btn']))
  {
    // Set validations for file
    $file = $_FILES['choose-btn'];
    $max_size = 32 * 1024 * 1024; // 32 megabytes
    $allowed_types = ['webp' => 'image/webp'];
    $result = formFileUploadError($file, $max_size, $allowed_types);

    // Check for errors
    if (!$result['valid'])
    {
      // Accumulate error messages
      $error_messages = array_merge($messages, $result['errors']);
    }
    else
    {
      // Check if file-type has been selected
      $output_format = isset($_POST['file-type']) ? strtolower($_POST['file-type']) : 'png';

      // Get the converted image data
      $convertedImageData = convertWebpToFormat($file['tmp_name'], $output_format);

      if (!$convertedImageData)
      {
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'error' => 'File conversion failed.']);
      }
      else
      {
        // Set the format for the image file
        header('Content-Type: image/' . $output_format);
        header('Content-Disposition: attachment; filename="webp_image.' . $output_format . '"');
        header('Content-Length: ' . strlen($convertedImageData));
        echo $convertedImageData;
      }
      die();
    }
  }
  $output = '';
  $form = new Form('image_converter');

  $field = new FieldFile('choose-btn', 'Choose a file');
  $field->setID(array('id' => 'choose-btn'));
  $form->addField($field);

//  $output .= htmlWrap('div', $form, ['class' => ['image-converter-form']]);

  $image_preview = htmlWrap('div', '', array('id' => 'preview-container'));
  $output .= htmlWrap('div', $image_preview, array('class' => array('image-preview')));

  $options = array(
    'png' => 'PNG',
    'jpeg' => 'JPEG',
  );
  $file_type_field = new FieldRadio('file-type', 'Select File Type');
  $file_type_field->setOptions($options);
  $file_type_field->setValue('png');
  $form->addField($file_type_field);

  $convert_btn = new FieldSubmit('convert-btn', 'Convert File');
  $form->addField($convert_btn);

  $output .= htmlWrap('div', $form, ['class' => ['image-converter-button']]);

  // Initialize an array to store error messages
  $errorMessages = [];

  // Display error messages
  foreach ($errorMessages as $error)
  {
    $output .= displayError($messages);
  }

  return $output;
}

/*******************
 * Function to convert the file type from webp to png or jpeg.
 *******************/
function convertWebpToFormat($webpFile, $outputFormat)
{
  // Load the WebP file
  $im = imagecreatefromwebp($webpFile);
  // Start output buffering
  ob_start();

  // Output the image in the selected format
  switch ($outputFormat)
  {
    case 'png':
      imagepng($im);
      break;
    case 'jpeg':
      imagejpeg($im);
      break;
  }
  // Get the buffered output and clean the buffer
  $convertedImageData = ob_get_clean();

  // free up memory
  imagedestroy($im);

  return $convertedImageData;
}

/*******************
 * Validation rules to check for size and type
 *******************/
function formFileUploadError($file, $max_size, $allowed_types)
{
  $valid = true;
  $messages = [];

  // Check if file is set and not empty
  if (isset($file) && !empty($file))
  {
    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it as invalid.
    if (!isset($file['error']) || is_array($file['error']))
    {
      $messages[] = 'Invalid parameters.';
      $valid = false;
    }
    // Check error values.
    switch ($file['error'])
    {
      case UPLOAD_ERR_OK:
        break;
      case UPLOAD_ERR_NO_FILE:
        $messages[] = 'No file sent.';
        return false;
        break;
      // If file size exceeds php settings.
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        $messages[] = 'File is too large.';
        return false;
        break;
      case UPLOAD_ERR_NO_TMP_DIR:
        $messages[] = 'Missing temporary directory.';
        return false;
        break;
      case UPLOAD_ERR_CANT_WRITE:
        $messages[] = 'Unable to write to the temporary directory.';
        return false;
        break;
      default:
        $messages[] = 'Unknown file error.';
        return false;
    }

    // Check file size does not exceed our settings.
    if ($file['size'] > $max_size)
    {
      $messages[] = 'File is too large.';
      $valid = false;
    }

    // Check file type.
    $image_info = getimagesize($file['tmp_name']);
    if (!$image_info || !isset($image_info['mime']) || !in_array($image_info['mime'], $allowed_types, true))
    {
      $messages[] = 'Incorrect file type for this field.';
      $valid = false;
    }
  }
  else
  {
    // If file is not set, return an error
    $messages[] = 'No file sent.';
    $valid = false;
  }

  return ['valid' => $valid, 'errors' => $messages];
}