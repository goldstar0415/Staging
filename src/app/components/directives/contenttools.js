(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('contentTools', contenttools);

  /* @ngInject */
  function contenttools($timeout, API_URL) {
    var directive = {
      link: link,
      restrict: 'EA',
      scope: {
        model: '=ngModel'
      }
    };
    return directive;

    function link(scope, element, attrs) {

      if (!window.ContentTools) {
        $.getScript('/assets/libs/contenttools/content-tools.min.js', function () {
          $.get('/assets/libs/contenttools/content-tools.min.css', function (css) {
            $('head').append('<style>' + css + '</style>');

            ContentTools.IMAGE_UPLOADER = imageUploader;

            $timeout(initEditor);
          });
        });
      } else {
        $timeout(initEditor);
      }
    }

    function initEditor() {
      // Initialise the editor

      var editor = new ContentTools.EditorApp.get();
      editor.init('[content-tools]', 'article-body');
      editor.start();
      window.onbeforeunload = null;
    }

    function imageUploader(dialog) {
      var image, xhr, xhrComplete, xhrProgress;

      // Set up the event handlers


      dialog.bind('imageUploader.cancelUpload', function () {
        // Cancel the current upload

        // Stop the upload
        if (xhr) {
          xhr.upload.removeEventListener('progress', xhrProgress);
          xhr.removeEventListener('readystatechange', xhrComplete);
          xhr.abort();
        }

        // Set the dialog to empty
        dialog.state('empty');
      });

      dialog.bind('imageUploader.clear', function () {
        // Clear the current image
        dialog.clear();
        image = null;
      });

      //IMAGE UPLOADER FILEREADY START ----------------------------------------------------
      dialog.bind('imageUploader.fileReady', function (file) {

        // Upload a file to the server
        var formData;

        // Set the dialog state to uploading and reset the progress bar to 0
        dialog.state('uploading');
        dialog.progress(0);

        // Build the form data to post  to the server
        formData = new FormData();
        formData.append('image', file);

        //AJAX CALL START -------------------------------------------------------------------
        $.ajax({
          xhr: function () {
            //Instantiate XHR
            var xhr = new window.XMLHttpRequest();

            //Add Progress Event Listener
            xhr.upload.addEventListener("progress", function (evt) {
              if (evt.lengthComputable) {
                var percentComplete = evt.loaded / evt.total;
                percentComplete = parseInt(percentComplete * 100);
                console.log(percentComplete);
                dialog.progress(percentComplete);

                if (percentComplete === 100) {
                  //Upload Is Complete
                }
              }
            }, false);
            return xhr;
          },
          url: API_URL + '/posts/upload',
          data: formData,
          cache: false,
          type: 'POST',
          contentType: false,
          processData: false,
          success: function (result) {

            // Unpack the response (from JSON)
            var response = JSON.parse(result);

            // Store the image details
            image = {
              size: response.image_size,
              url: response.image_url
            };

            // Populate the dialog
            dialog.populate(image.url, image.size);

            dialog.save(
              image.url,
              image.size,
              {
                "data-ce-max-width": response.image_size.width
              })
            ;

            // Clear the request
            xhr = null;
            xhrProgress = null;
            xhrComplete = null;

          }
        });
        //AJAX CALL END ---------------------------------------------------------------------
      });
      //IMAGE UPLOADER FILEREADY END --------------------------------------------------------

      //dialog.bind('imageUploader.save', function () {
      //  var crop, cropRegion, formData;
      //
      //  // Set the dialog to busy while the rotate is performed
      //  dialog.busy(true);
      //
      //  // Build the form data to post to the server
      //  formData = new FormData();
      //  formData.append('url', image.url);
      //
      //  // Set the width of the image when it's inserted, this is a default
      //  // the user will be able to resize the image afterwards.
      //  formData.append('width', 600);
      //
      //  // Check if a crop region has been defined by the user
      //  if (dialog.cropRegion()) {
      //    formData.append('crop', dialog.cropRegion());
      //  }
      //
      //  //AJAX CALL START -------------------------------------------------------------------
      //  $.ajax({
      //    url: 'assets/imageSave.php',
      //    data: formData,
      //    cache: false,
      //    type: 'POST',
      //    contentType: false,
      //    processData: false,
      //    success: function (result) {
      //
      //      // Free the dialog from its busy state
      //      dialog.busy(false);
      //
      //      // Unpack the response (from JSON)
      //      var response = JSON.parse(result);
      //
      //      // Trigger the save event against the dialog with details of the
      //      // image to be inserted.
      //      dialog.save(
      //        response.url,
      //        response.size,
      //        {
      //          'alt': response.alt,
      //          'data-ce-max-width': response.size['width']
      //        });
      //
      //      // Clear the request
      //      xhr = null
      //      xhrComplete = null
      //    }
      //  });
      //  //AJAX CALL END ---------------------------------------------------------------------
      //});

    }

  }

})();
