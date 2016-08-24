@extends('admin.main')

@section('content')
    <h3>
        TicketMaster parser
    </h3>
    <p class="text-info">
        Last imported row: {{ isset($ticketMasterSpot->id) ? $ticketMasterSpot->id : null }}
    </p>
    <a href="{{ route('admin.ticket-master.run') }}" type="button" class="btn btn-success">Run</a>
    <h2>
        Parser settings
    </h2>
    <hr>
    {!! Form::open(['method' => 'PUT']) !!}
    <div class="form-group">
        {!! Form::label('aid', 'Aid') !!}
        {!! Form::text(
                'aid',
                isset($settings->parser->aid) ? $settings->parser->aid : null,
                ['placeholder' => 'identifier', 'class' => 'form-control']
            ) !!}
        {!! Form::submit('Save', ['class' => 'btn btn-default']) !!}
    </div>
    <p class="text-info">
        Last imported id: {{ isset($settings->parser->last_imported_id) ? $settings->parser->last_imported_id : null }}
    </p>
    {!! Form::close() !!}
    <a href="{{ route('admin.parser.run') }}" type="button" class="btn btn-success">Run</a>
    <h3>
        Crawler parser
    </h3>
    <p class="text-info">
        Last imported row: {{ isset($settings->crawler->last_imported_row) ? $settings->crawler->last_imported_row : null }}
    </p>
    <a href="{{ route('admin.crawler.run') }}" type="button" class="btn btn-success">Run</a>
    
    @if (session('run'))
        <p class="text-info">Event parser goes to the queue</p>
    @endif
@endsection