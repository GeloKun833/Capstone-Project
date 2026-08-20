@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $maxTabs = 10;
        $blockStart = (int) (floor(($currentPage - 1) / $maxTabs) * $maxTabs) + 1;
        $blockEnd = min($blockStart + $maxTabs - 1, $lastPage);
    @endphp

    <nav aria-label="Pagination">
        <ul class="pagination pagination-limited mb-0">
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                @if ($paginator->onFirstPage())
                    <span class="page-link">&laquo; Previous</span>
                @else
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Previous</a>
                @endif
            </li>

            @if ($blockStart > 1)
                <li class="page-item disabled" aria-hidden="true">
                    <span class="page-link">…</span>
                </li>
            @endif

            @for ($page = $blockStart; $page <= $blockEnd; $page++)
                @if ($page == $currentPage)
                    <li class="page-item active" aria-current="page">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                    </li>
                @endif
            @endfor

            @if ($blockEnd < $lastPage)
                <li class="page-item disabled" aria-hidden="true">
                    <span class="page-link">…</span>
                </li>
            @endif

            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                @if ($paginator->hasMorePages())
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next &raquo;</a>
                @else
                    <span class="page-link">Next &raquo;</span>
                @endif
            </li>
        </ul>
    </nav>
@endif
