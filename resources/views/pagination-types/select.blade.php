<div class="nav-pagination pagination-select">
    <div class="input-group">
        <a href="{!!$urlPreviousPage!!}" class="direction input-group-addon" title="@lang('Simplegrid::grid.Previous Page')">
            <span class="glyphicon glyphicon-chevron-left"></span>
        </a>
        <select class="form-control select-page" data-url="{!!$urlPagination!!}">
            @for($i=1; $i<=$totalPages;$i++)
                <option value="{!!$i!!}" {{$i==$currentPage ? 'selected' : ''}} >@lang('Simplegrid::grid.Page') {!!$i!!}</option>
            @endfor
        </select>
        <a href="{!!$urlNextPage!!}" class="direction input-group-addon" title="@lang('Simplegrid::grid.Next Page')">
            <span class="glyphicon glyphicon-chevron-right"></span>
        </a>	                
    </div>
</div>