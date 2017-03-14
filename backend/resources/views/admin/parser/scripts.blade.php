<script>
$(function(){
    
    var $console         = $('.spots-parser-info-body');
    var $consoleScroll   = $('.spots-parser-info');
    var $form            = $('.export-form');
    var $inputs          = $form.find('input');
    var $checks          = $form.find('.checkbox');
    var fileForParse     = false;
    var step             = 1;
    var totalRows        = 0;
    var rowsParsed       = 0;
    var fileOffset       = 0;
    var rowsAdded        = 0;
    var rowsUpdated      = 0;
    var headers          = [];
    var token            = $('input[name="_token"]').val();
    var timerInterval    = null;
    var timerSeconds     = 0;
    
    var $progressRow     = $('.spots-parser-progress');
    var $progress        = $progressRow.find('.progress-bar');
    
    var $uploadBtn       = $form.find('button[type="submit"]');
    var $uploadPreloader = $uploadBtn.find('.prldr');
    var $uploadDone      = $uploadBtn.find('.btn-loaded');
    var $uploadText      = $uploadBtn.find('.btn-export');
    var $parseBtn        = $form.find('button.btn-parse');
    var $parsePreloader  = $parseBtn.find('.prldr');
    var $parseDone       = $parseBtn.find('.btn-loaded');
    var $parseText       = $parseBtn.find('.btn-export');
    var $fieldCategory   = $form.find('select.field-category');
    var $fullRemoteId    = $form.find('input[name="full-remote-id"]');
    var $mode            = $form.find('input[name="mode"]');
    var $refreshBtn       = $form.find('button.btn-refresh');
    var $refreshPreloader = $refreshBtn.find('.prldr');
    var $refreshDone      = $refreshBtn.find('.btn-loaded');
    var $refreshText      = $refreshBtn.find('.btn-export');
    
    var mode             = $mode.filter(':checked').val();
    // File inputs handle
    $(document).on('change', 'input[type="file"]', function() {
        var input = $(this),
            numFiles = input.get(0).files ? input.get(0).files.length : 1,
            label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
        input.trigger('fileselect', [numFiles, label]);
    });
    
    $(document).ready( function() {
        $('input[type="file"]').on('fileselect', function(event, numFiles, label) {

            var input = $(this).parents('.input-group').find(':text'),
                log = numFiles > 1 ? numFiles + ' files selected' : label;

            if( input.length ) {
                input.val(label);
            } else {
                if( log ) alert(log);
            }

        });
    });
    
    $fullRemoteId.on('change', function(){
        var useFullRID = $fullRemoteId.is(':checked');
        if(useFullRID)
        {
            $mode.filter('[value="any"]').addClass('disabled').prop('checked', false).prop('disabled', true);
            $mode.filter('[value="insert"]').addClass('disabled').prop('checked', false).prop('disabled', true);
            $mode.filter('[value="update"]').addClass('disabled').prop('checked', true);
        }
        else
        {
            $mode.filter('[value="any"]').removeClass('disabled').prop('disabled', false);
            $mode.filter('[value="insert"]').removeClass('disabled').prop('disabled', false);
        }
    });
    
    // File upload handle
    $form.on('submit', function(e){
        e.preventDefault();
        if( !$form.is('.loading'))
        {
            var formData = new FormData(this);
            
            startTimer();
            
            $inputs.attr('disabled', true);
            $checks.addClass('disabled');
            $uploadBtn.addClass('disabled');
            $uploadPreloader.show();
            $uploadText.hide();
            $refreshBtn.addClass('disabled');
            message('Upload started, please wait...');
            
            $form.addClass('loading');
            $.ajax({
                url: $form.attr('action'),
                data: formData,
                type: 'POST',
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function(response){
                    if(response.success)
                    {
                        $parseBtn.removeClass('disabled');
                        $uploadDone.show();
                        
                        fileForParse = response.data.path;
                        totalRows = response.data.count - 1;
                        message('File uploaded: ' + response.data.filename + ';<br />' + 'Total rows: ' + totalRows);
                        $form.removeClass('loading');
                        stopTimer();
                        if($('input[name="auto-parse"]').is(':checked'))
                        {
                            $parseBtn.click();
                        }
                    }
                    else
                    {
                        message(response.data[0]);
                        sendUploadError();
                    }
                    $refreshBtn.addClass('disabled');
                    $uploadPreloader.hide();
                },
                error: function(response){
                    message('File not loaded with error: ' + response.status + ' ' + response.statusText);
                    sendUploadError();
                    $uploadPreloader.hide();
                    $refreshBtn.addClass('disabled');
                }
                
            });
        }  
    });
    
    $parseBtn.on('click', function(e) {
        e.preventDefault();
        startTimer();
        if( !$form.is('.loading') )
        {
            if( !fileForParse )
            {
                message('File for parse not loaded!');
                stopTimer();
                $refreshBtn.removeClass('disabled');
            }
            else
            {
                $parseBtn.addClass('disabled');
                $parsePreloader.show();
                $parseText.hide();
                $refreshBtn.addClass('disabled');
                $form.addClass('loading');
                $progressRow.slideDown();
                parseHandler();
            }
        }
    });
    
    $refreshBtn.on('click', function(e) {
        e.preventDefault();
        if( !$refreshBtn.is('.disabled'))
        {
            $refreshBtn.addClass('disabled');
            $form.addClass('loading');
            $inputs.attr('disabled', true);
            $refreshText.hide();
            $refreshPreloader.show();
            message('Materialized view refresh started');
            $.ajax({
                url: '{{ route("admin.spots.refresh-view") }}',
                success: function(response){
                    $refreshPreloader.hide();
                    $refreshDone.show();
                    $form.removeClass('loading');
                    $inputs.attr('disabled', false);
                    message('Materialized view refreshed successfully');
                },
                error: function() {
                    message('<span class="text-danger">Something went wrong!</span>');
                    $form.removeClass('loading');
                    $inputs.attr('disabled', false);
                }
            });
        }
    });
    
    var parseHandler = function() {
        $form.find('select').attr('disabled', 'disabled');
        message('Parsing step ' + (step) + ' started');
        var url = '{{ route($uploadRoute) }}';
        $.ajax({
            url: url,
            data: {
                path: fileForParse,
                total_rows: totalRows,
                rows_parsed: rowsParsed,
                file_offset: fileOffset,
                headers: headers,
                mode: $mode.filter(':checked').val(),
                category: $fieldCategory.val(),
                use_prefix: $fullRemoteId.is(':checked')?0:1,
                _token: token
            },
            type: 'POST',
            dataType: 'json',
            success: function(response){
                step++;
                if(response.success)
                {
                    rowsParsed = response.rows_parsed;
                    fileOffset = response.file_offset;
                    rowsAdded   += response.rows_added;
                    rowsUpdated +=  response.rows_updated;
                    if(response.headers)
                    {
                        headers = response.headers;
                    }
                    
                    if( rowsParsed >= totalRows || response.end_of_parse == true )
                    {
                        message('Parsing complete!');
                        message('Total rows in file: ' + totalRows);
                        message('Total rows parsed: ' + rowsParsed);
                        $progressRow.slideUp();
                        $parseDone.show();
                        $parsePreloader.hide();
                        stopTimer();
                        $refreshBtn.removeClass('disabled');
                        //$parsePreloader.hide();
                    }
                    else if(rowsParsed < totalRows)
                    {
                        var percent = Math.round(rowsParsed * 100 / totalRows);
                        $progress.attr('aria-valuenow', percent).css('width', percent + '%');
                        message('Rows parsed that step: ' + response.rows_parsed_now);
                        message('Total rows added to base: ' + rowsAdded);
                        message('Total rows updated: ' + rowsUpdated);
                        message('Total parsed rows: ' + rowsParsed);
                        parseHandler();
                    }
                }
                else
                {
                    sendParseError();
                }
                if(response.messages && response.messages.length > 0)
                {
                    $.each(response.messages, function(i, value){
                        message('<span class="text-danger">' + value + '</span>');
                    });
                }
            },
            error: function() {
                step++;
                sendParseError();
            }
        });
    }
    
    function message(mes) {
        $console.html($console.html() + '<br />' + mes);
        var height = $consoleScroll[0].scrollHeight;
        $consoleScroll.scrollTop(height);
    }
    
    function startTimer() {
        timerInterval = setInterval(timerIntervalHandler, 1000);
        $progress.addClass('active');
    }
    
    function stopTimer() {
        clearInterval(timerInterval);
        $progress.removeClass('active');
    }
    
    function timerIntervalHandler() {
        var sec = timerSeconds++;
        var hour = parseInt(sec / 3600);
        sec -= hour * 3600;
        var min = parseInt(sec / 60);
        sec -= min * 60;
        
        $('.timer').html(hour + ':' + min + ':' + sec);
    }
    
    function sendUploadError() {
        $uploadBtn.removeClass('disabled');
        $inputs.attr('disabled', false);
        $checks.removeClass('disabled');
        $uploadText.show();
        $form.removeClass('loading');
        stopTimer();
    }
    
    function sendParseError() {
    
        $parseBtn.removeClass('disabled');
        $parsePreloader.hide();
        $parseText.show();
        $form.removeClass('loading');
        message('<span class="text-danger">PARSE ERROR!</span>');
        stopTimer();
    }
    
});
</script>