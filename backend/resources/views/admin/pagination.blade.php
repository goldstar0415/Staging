<div class="col-xs-12 pagination">
    @if (!empty(Request::query()))
        {!! $paginatable->appends(Request::query())->render() !!}
    @else
        {!! $paginatable->render() !!}
    @endif
</div>