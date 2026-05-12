{{--
    Reusable Truncated Ellipsis Pagination Component
    ─────────────────────────────────────────────────
    Usage:
        @include('components.pagination', ['paginator' => $yourPaginatedVariable])

    Optional:
        $navClass  — extra CSS classes on the <nav> (default: '')
--}}
@php
    $currentPage = $paginator->currentPage();
    $lastPage    = max(1, $paginator->lastPage());
    $navClass    = $navClass ?? '';

    /**
     * Build the compact token list.
     * Each token is either an int (page number) or the string 'ellipsis'.
     * Rule: suppress an ellipsis that covers only a single page — show the
     * number instead (e.g. "1 ... 3" → "1 2 3").
     */
    $buildTokens = function (int $current, int $last, int $window) use (&$buildTokens): array {
        if ($last <= 1) {
            return [1];
        }

        $tokens     = [];
        $rangeStart = max(2, $current - $window);
        $rangeEnd   = min($last - 1, $current + $window);

        // First page
        $tokens[] = 1;

        // Left gap: use ellipsis only when the gap is > 1; else show the number
        if ($rangeStart === 3) {
            $tokens[] = 2;               // gap of exactly 1 → show the page
        } elseif ($rangeStart > 3) {
            $tokens[] = 'ellipsis';
        }
        // (rangeStart === 2 means no gap at all)

        // Window pages (never includes page 1 or page $last)
        for ($p = $rangeStart; $p <= $rangeEnd; $p++) {
            $tokens[] = $p;
        }

        // Right gap
        if ($rangeEnd === $last - 2) {
            $tokens[] = $last - 1;       // gap of exactly 1 → show the page
        } elseif ($rangeEnd < $last - 2) {
            $tokens[] = 'ellipsis';
        }
        // (rangeEnd === $last - 1 means no gap at all)

        // Last page
        $tokens[] = $last;

        return $tokens;
    };

    // Desktop: window ±2; mobile JS collapses to ±1
    $pageTokens = $buildTokens($currentPage, $lastPage, 2);

    // Unique ID so multiple paginations on the same page don't clash
    $navId = 'pagination-' . Str::random(6);
@endphp

<div class="px-4 sm:px-6 py-3 border-t border-gray-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-sm text-gray-600">
    {{-- "Showing X to Y of Z results" --}}
    <div class="text-center sm:text-left">
        Showing
        <span class="font-medium">{{ $paginator->firstItem() ?? ($paginator->count() ? 1 : 0) }}</span>
        to
        <span class="font-medium">{{ $paginator->lastItem() ?? $paginator->count() }}</span>
        of
        <span class="font-medium">{{ $paginator->total() }}</span>
        results
    </div>

    <nav id="{{ $navId }}"
         class="table-pagination inline-flex items-center gap-1 flex-wrap justify-center sm:justify-end {{ $navClass }}"
         role="navigation"
         aria-label="Pagination">

        {{-- ← Previous --}}
        @php $prevDisabled = $paginator->onFirstPage(); @endphp
        <a href="{{ $prevDisabled ? '#' : $paginator->previousPageUrl() }}"
           class="pagination-btn {{ $prevDisabled ? 'disabled' : '' }}"
           aria-disabled="{{ $prevDisabled ? 'true' : 'false' }}"
           aria-label="Previous page">
            <i class="fas fa-chevron-left"></i>
        </a>

        {{-- Page tokens --}}
        @foreach ($pageTokens as $token)
            @if ($token === 'ellipsis')
                <span class="pagination-ellipsis" aria-hidden="true">&hellip;</span>
            @else
                <a href="{{ $paginator->url($token) }}"
                   class="pagination-btn {{ $token === $currentPage ? 'active' : '' }}"
                   aria-current="{{ $token === $currentPage ? 'page' : 'false' }}"
                   aria-label="Page {{ $token }}"
                   data-page="{{ $token }}"
                   data-dist="{{ abs($token - $currentPage) }}">
                    {{ $token }}
                </a>
            @endif
        @endforeach

        {{-- → Next --}}
        @php $nextDisabled = !$paginator->hasMorePages(); @endphp
        <a href="{{ $nextDisabled ? '#' : $paginator->nextPageUrl() }}"
           class="pagination-btn {{ $nextDisabled ? 'disabled' : '' }}"
           aria-disabled="{{ $nextDisabled ? 'true' : 'false' }}"
           aria-label="Next page">
            <i class="fas fa-chevron-right"></i>
        </a>
    </nav>
</div>

{{-- Responsive collapse: ≥640 px keeps ±2 window; <640 px hides distance-2 buttons --}}
<script>
(function () {
    var nav = document.getElementById('{{ $navId }}');
    if (!nav) return;
    function adjust() {
        var small = window.innerWidth < 640;
        nav.querySelectorAll('a[data-dist]').forEach(function (btn) {
            btn.style.display = (small && parseInt(btn.getAttribute('data-dist'), 10) === 2) ? 'none' : '';
        });
    }
    adjust();
    window.addEventListener('resize', adjust);
})();
</script>
