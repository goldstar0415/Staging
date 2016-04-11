@extends('admin.main')

@section('content')
    <h2>Spot reports</h2>
    <hr>
    {!! Form::open(['method' => 'GET', 'route' => 'admin.spot-requests.search','class' => 'search-form']) !!}
    {!! Form::text('search_text', null, ['placeholder' => 'Search by name']) !!}
    {!! Form::submit('Search') !!}
    {!! Form::close() !!}
    <table class="col-xs-12 requests">
        <thead>
        <tr>
            <th class="col-sm-3">Title</th>
            <th class="col-sm-5">Reason</th>
            <th class="col-sm-2">Text</th>
            <th class="col-sm-2"></th>
        </tr>
        </thead>
        <tbody>
        @foreach($reports as $report)
            <tr>
                <td>
                    <a href="{!! frontend_url(
                    $report->spot->user ? $report->spot->user->id : Auth::id(),
                    'spot', $report->spot_id
                    ) !!}">{{ $report->spot->title }}</a>
                </td>
                <td>
                    @if ($report->reason === App\SpotReport::WRONG)
                        Wrong Information
                    @elseif($report->reason === App\SpotReport::INAPPROPRIATE)
                        Inappropriate Content
                    @elseif($report->reason === App\SpotReport::DUPLICATE)
                        Duplicate Data
                    @elseif($report->reason === App\SpotReport::SPAM)
                        Spam
                    @elseif($report->reason === App\SpotReport::OTHER)
                        Other
                    @endif
                </td>
                <td>
                    {{ $report->text }}
                </td>
                <td>
                    {!! link_delete(route('admin.spot-reports.destroy', [$report->id]), '', ['class' => 'delete']) !!}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @include('admin.pagination', ['paginatable' => $reports])
@endsection