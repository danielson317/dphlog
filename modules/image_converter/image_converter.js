
/*******************
   *image preview
   *******************/
$(document).ready(function() {
    $('.choose-btn').change(function(e) {
        // Clear previous preview
        $('#preview-container').empty();

        // Display the selected image
        let image = URL.createObjectURL(e.target.files[0]);
        let imagediv = document.getElementById('preview-container');
        let newimg = document.createElement('img');
        newimg.src = image;
        imagediv.appendChild(newimg);
    });

    $('.convert-btn').click(function(e) {
        e.preventDefault();

        // Ensure file selected
        let fileInput = $('#choose-btn')[0];
        if (!fileInput.files.length) {
            displayError('Please choose a file first.');
            return;
        }

        // Get selected file type
        let fileType = $('input[name="file-type"]:checked').val();

        // Check if the selected file type is valid
        if (!fileType) {
            displayError('Please select a file type.');
            return;
        }

        // Create FormData object and append file and file type
        let formData = new FormData();
        formData.append('choose-btn', fileInput.files[0]);
        formData.append('file-type', fileType);

        // AJAX request to trigger conversion and download
        $.ajax({
            url: '/image-converter',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Create a Blob from the response data
                var blob = new Blob([response], { type: 'image/' + fileType });

                // Create a link element
                var link = document.createElement('a');

                // Set the href attribute with a data URL representing the Blob
                link.href = window.URL.createObjectURL(blob);

                // Set the download attribute with the desired file name
                link.download = 'webp_image.' + fileType;

                // Append the link to the document
                document.body.appendChild(link);

                // Simulate a click on the link to trigger the download
                link.click();

                
                document.body.removeChild(link);

                // clear field input for choose and preview after convert button is clicked
                $('#choose-btn').val('');
                $('#preview-container').empty();
            },
            error: function(xhr, status, error) {
                displayError('An error occurred during the conversion process. Please try again.');
                console.error(xhr, status, error);
            }
        });
    });
});