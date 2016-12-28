<div class="col col-sm-6">
    <div class="input-group input-group-file">
        <label class="input-group-btn ">
            <span class="btn btn-primary btn-file">
                Browse&hellip; {!! Form::file('csv') !!}
            </span>
        </label>
        <input type="text" class="form-control" readonly>
    </div>
    <div>
        <div class="radio">
            <label><input name="mode" type="radio" value="parsing" checked>Parsing mode</label>
        </div>
        <div class="radio">
            <label><input name="mode" type="radio" value="update">Update mode</label>
        </div>
        <div class="checkbox">
            <label><input name="auto-parse" type="checkbox" value="" checked>Automaticly start parsing</label>
        </div>
        <div class="checkbox">
            <label><input name="update-existing" type="checkbox" value="">Update existing rows</label>
        </div>
        <div class="select" hidden>
            <label for="field-select">Choose field for updating:</label>
            {!! Form::select(
                'field',
                $fields,
                null,
                ['class' => 'field-select form-control', 'id' => 'field-select']
            ) !!}
        </div>
        <div class="select">
            <label for="field-select">Choose category:</label>
            {!! Form::select(
                'category',
                $categories,
                null,
                ['class' => 'field-category form-control', 'id' => 'field-category']
            ) !!}
        </div>
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

