@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="pagination-shell">
        <div class="flex flex-col gap-3 sm:hidden">
            <div class="pagination-summary">
                {{ __('Menampilkan') }}
                <span class="font-semibold text-slate-700">{{ $paginator->firstItem() ?? 0 }}</span>
                -
                <span class="font-semibold text-slate-700">{{ $paginator->lastItem() ?? 0 }}</span>
                {{ __('dari') }}
                <span class="font-semibold text-slate-700">{{ $paginator->total() }}</span>
            </div>
            <div class="grid grid-cols-2 gap-2">
                @if ($paginator->onFirstPage())
                    <span class="pagination-link pagination-link-disabled">{{ __('Sebelumnya') }}</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-link">{{ __('Sebelumnya') }}</a>
                @endif

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-link">{{ __('Berikutnya') }}</a>
                @else
                    <span class="pagination-link pagination-link-disabled">{{ __('Berikutnya') }}</span>
                @endif
            </div>
        </div>

        <div class="hidden sm:flex sm:items-center sm:justify-between sm:gap-4">
            <p class="pagination-summary">
                {{ __('Menampilkan') }}
                <span class="font-semibold text-slate-700">{{ $paginator->firstItem() ?? 0 }}</span>
                -
                <span class="font-semibold text-slate-700">{{ $paginator->lastItem() ?? 0 }}</span>
                {{ __('dari') }}
                <span class="font-semibold text-slate-700">{{ $paginator->total() }}</span>
                {{ __('hasil') }}
            </p>

            <div class="pagination-controls">
                @if ($paginator->onFirstPage())
                    <span class="pagination-link pagination-link-disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                        <span aria-hidden="true">&lsaquo;</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-link" aria-label="{{ __('pagination.previous') }}">
                        <span aria-hidden="true">&lsaquo;</span>
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="pagination-link pagination-link-disabled" aria-disabled="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="pagination-link pagination-link-active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="pagination-link" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-link" aria-label="{{ __('pagination.next') }}">
                        <span aria-hidden="true">&rsaquo;</span>
                    </a>
                @else
                    <span class="pagination-link pagination-link-disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                        <span aria-hidden="true">&rsaquo;</span>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
