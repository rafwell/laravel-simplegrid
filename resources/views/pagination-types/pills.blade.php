<div class="nav-pagination pagination-pill">
    <ul class="pagination pull-right ">
        <li class="page-item">
            <a class="page-link" href="{!!$urlPreviousPage!!}" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        @if($currentPage > 3)
            @php
                $i = $currentPage - 2;
            @endphp
        @else
            @php
                $i = 1;
            @endphp
        @endif
        @php
            $diffToEnd = $totalPages-$i;

            for($j=0; $j<2; $j++){
                if($diffToEnd<=3){
                    $i--;
                    $diffToEnd = $totalPages-$i;
                }
            }
            
            if($i<1) $i = 1;

            $nextPages = $totalPages-$i;

            if($nextPages>2) $nextPages = 2;

            $initialPage = $i;
            $endPage = $initialPage+4;
            if($endPage>$totalPages)
                $endPage = $totalPages;
                
        @endphp
        @for($i; $i<=$endPage;$i++)
            <li class="page-item {!!$i==$currentPage ? 'active': ''!!}">
                <a class="page-link" href="{{$urlRowsPerPage}}&page={!!$i!!}">{!!$i!!}</a>
            </li>
        @endfor
        <li class="page-item">
            <a class="page-link" href="{!!$urlNextPage!!}" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</div>