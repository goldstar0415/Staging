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
        <div class="select">
            <label for="field-select">Choose category:</label>
            {!! Form::select(
                'category',
                $categories,
                null,
                ['class' => 'field-category form-control', 'id' => 'field-category']
            ) !!}
        </div>
        <div class="radio">
            <label><input name="mode" type="radio" value="any" checked>Insert new and update old spots</label>
        </div>
        <div class="radio">
            <label><input name="mode" type="radio" value="insert">Only insert new spots</label>
        </div>
        <div class="radio">
            <label><input name="mode" type="radio" value="update">Only update old spots</label>
        </div>
        <div class="checkbox">
            <label><input name="auto-parse" type="checkbox" value="" checked>Automaticly start parsing</label>
        </div>
        <div class="checkbox">
            <label><input name="full-remote-id" type="checkbox" value="">Use full remote ID (with prefix) in CSV</label>
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

    <button class="btn btn-submit btn-parse disabled" type="button">
        <span class="btn-export">Start parse</span>
        <span class="btn-loaded">Done!</span>
        <div class="prldr">
            <div class="prldr-i prldr-1"></div>
            <div class="prldr-i prldr-2"></div>
            <div class="prldr-i prldr-3"></div>
        </div>
    </button>
    
    <button class="btn btn-submit btn-refresh" type="button">
        <span class="btn-export">Refresh materialized view</span>
        <span class="btn-loaded">Refresh complite!</span>
        <div class="prldr">
            <div class="prldr-i prldr-1"></div>
            <div class="prldr-i prldr-2"></div>
            <div class="prldr-i prldr-3"></div>
        </div>
    </button>
</div>

