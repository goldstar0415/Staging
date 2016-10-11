@extends('admin.main')

@section('content')
<h2 class="col-xs-12">Hotels Parser</h2>
<hr class="col-xs-12" />
<div class="clearfix"></div>
<div class="row actions">
    {!! Form::open(['method' => 'POST', 'route' => 'admin.hotels.export-upload', 'class' => 'export-form', 'files' => true]) !!}
    <div class="col col-sm-6">
        <div class="input-group input-group-file">
            <label class="input-group-btn ">
                <span class="btn btn-primary btn-file">
                    Browse&hellip; {!! Form::file('csv') !!}
                </span>
            </label>
            <input type="text" class="form-control" readonly>
        </div>
    </div>
    <div class="col col-sm-6">
        <button class="btn btn-submit" type="submit">
            <span class="btn-export">Upload</span>
            <span class="btn-loaded">Loaded!</span>
            <div class="prldr">
                <div class="prldr-i prldr-1"></div>
                <div class="prldr-i prldr-2"></div>
                <div class="prldr-i prldr-3"></div>
            </div>
        </button>
        
        <button class="btn btn-submit disabled" type="button">
            <span class="btn-export">Start parse</span>
            <span class="btn-loaded">Done!</span>
            <div class="prldr">
                <div class="prldr-i prldr-1"></div>
                <div class="prldr-i prldr-2"></div>
                <div class="prldr-i prldr-3"></div>
            </div>
        </button>
    </div>
    {!! Form::close() !!}
    <div class="clearfix"></div>
    <div class="col-xs-12">
        <div class="progress hotels-parser-progress">
            <div class="progress-bar progress-bar-striped active" role="progressbar"
            aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
            
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="hotels-parser-info">
        <div class="hotels-parser-info-console">Console:</div>
        <div class="hotels-parser-info-body"></div>
    </div>    
</div>

@endsection
@section('scripts')
<script>
$(function(){
    
    var $console         = $('.hotels-parser-info-body');
    var $consoleScroll   = $('.hotels-parser-info');
    var $form            = $('.export-form');
    var $inputs          = $form.find('input');
    var fileForParse     = false;
    var step             = 0;
    var totalRows        = 0;
    var rowsParsed       = 0;
    var fileOffset       = 0;
    var headers          = [];
    var token            = $('input[name="_token"]').val();
    
    var $progressRow     = $('.hotels-parser-progress');
    var $progress        = $progressRow.find('.progress-bar');
    
    var $uploadBtn       = $form.find('button[type="submit"]');
    var $uploadPreloader = $uploadBtn.find('.prldr');
    var $uploadDone      = $uploadBtn.find('.btn-loaded');
    var $uploadText      = $uploadBtn.find('.btn-export');
    var $parseBtn        = $form.find('button[type="button"]');
    var $parsePreloader  = $parseBtn.find('.prldr');
    var $parseDone       = $parseBtn.find('.btn-loaded');
    var $parseText       = $parseBtn.find('.btn-export');
    
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
    
    // File upload handle
    $form.on('submit', function(e){
        e.preventDefault();
        
        if( !$form.is('.loading'))
        {
            var formData = new FormData(this);
            
            $inputs.attr('disabled', true);
            $uploadBtn.addClass('disabled');
            $uploadPreloader.show();
            $uploadText.hide();
            
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
                        
                    }
                    else
                    {
                        message(response.data[0]);
                        $uploadBtn.removeClass('disabled');
                        $inputs.attr('disabled', false);
                        $uploadText.show();
                    }
                    $uploadPreloader.hide();
                    $form.removeClass('loading');
                },
                error: function(response){
                    message('File not loaded with error: ' + response.status + ' ' + response.statusText);
                    $uploadBtn.removeClass('disabled');
                    $inputs.attr('disabled', false);
                    $uploadText.show();
                    $uploadPreloader.hide();
                    $form.removeClass('loading');
                }
                
            });
        }  
    });
    
    $parseBtn.on('click', function(e) {
        e.preventDefault();
        
        if( !$form.is('.loading') )
        {
            if( !fileForParse )
            {
                message('File for parse not loaded!');
            }
            else
            {
                $parseBtn.addClass('disabled');
                $parsePreloader.show();
                $parseText.hide();

                $form.addClass('loading');
                $progressRow.slideDown();
                parseHandler();
            }
        }
        
        
    });
    
    var parseHandler = function() {
        message('Parsing step ' + (step++) + ' started');
        $.ajax({
            url: '{{ route("admin.hotels.export") }}',
            data: {
                path: fileForParse,
                total_rows: totalRows,
                rows_parsed: rowsParsed,
                file_offset: fileOffset,
                headers: headers,
                _token: token
            },
            type: 'POST',
            dataType: 'json',
            success: function(response){
                console.log(response);
                if(response.success)
                {
                    rowsParsed = response.rows_parsed;
                    fileOffset = response.file_offset;
                    if(response.headers)
                    {
                        headers = response.headers;
                    }
                    
                    if( rowsParsed >= totalRows || response.endOfParse == true )
                    {
                        message('Parsing complete!');
                        message('Total rows in file: ' + totalRows);
                        message('Total rows parsed: ' + rowsParsed);
                        $progressRow.slideUp();
                        $parseDone.show();
                        $parsePreloader.hide();
                    }
                    else if(rowsParsed < totalRows)
                    {
                        var percent = Math.round(rowsParsed * 100 / totalRows);
                        $progress.attr('aria-valuenow', percent).css('width', percent + '%');
                        message('Rows parsed now: ' + rowsParsed);
                        parseHandler();
                    }
                    if(response.messages && response.messages.length > 0)
                    {
                        $.each(response.messages, function(i, value){
                            message('<span class="text-danger">' + value + '</span>');
                        });
                    }
                }
                else
                {
                    $parseBtn.removeClass('disabled');
                    $parsePreloader.hide();
                    $parseText.show();
                    $form.removeClass('loading');
                    
                    message('PARSE ERROR!');
                }
            }
        });
    }
    
    function message(mes) {
        $console.html($console.html() + '<br />' + mes);
        var height = $consoleScroll[0].scrollHeight;
        $consoleScroll.scrollTop(height);
    }
    
});
</script>
@endsection