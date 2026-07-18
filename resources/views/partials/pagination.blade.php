@if ($paginator->hasPages() || $paginator->total() > 0)
    <div style="padding: 12px 24px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: flex-end; background: #FFFFFF; font-size: 13px; font-weight: 500; color: #475569; gap: 24px;">
        
        <div style="display: flex; align-items: center; gap: 8px;">
            <span>Rows per page</span>
            <select onchange="window.location.href=this.value" style="padding: 4px 28px 4px 8px; border: none; background: transparent; outline: none; font-weight: 500; color: #1e293b; cursor: pointer; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg fill=%22none%22 stroke=%22%2364748b%22 stroke-width=%222%22 viewBox=%220 0 24 24%22 xmlns=%22http://www.w3.org/2000/svg%22><path d=%22M6 9l6 6 6-6%22/></svg>'); background-repeat: no-repeat; background-position: right 4px center; background-size: 16px; border-bottom: 1px solid transparent;">
                @foreach([25, 50, 75, 100] as $option)
                    <option value="{{ request()->fullUrlWithQuery(['per_page' => $option, 'page' => 1]) }}" {{ request('per_page', 25) == $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; align-items: center;">
            {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }}
        </div>

        <div style="display: flex; align-items: center; gap: 4px;">
            {{-- First Page --}}
            @if ($paginator->onFirstPage())
                <button disabled style="background:transparent; border:none; color:#cbd5e1; cursor:not-allowed; padding:6px; display:inline-flex; align-items:center; justify-content:center;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polygon points="19 20 9 12 19 4 19 20"></polygon><line x1="5" y1="19" x2="5" y2="5"></line></svg>
                </button>
            @else
                <a href="{{ request()->fullUrlWithQuery(['page' => 1]) }}" style="background:transparent; border:none; color:#475569; cursor:pointer; padding:6px; display:inline-flex; align-items:center; justify-content:center; text-decoration:none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polygon points="19 20 9 12 19 4 19 20"></polygon><line x1="5" y1="19" x2="5" y2="5"></line></svg>
                </a>
            @endif

            {{-- Previous Page --}}
            @if ($paginator->onFirstPage())
                <button disabled style="background:transparent; border:none; color:#cbd5e1; cursor:not-allowed; padding:6px; display:inline-flex; align-items:center; justify-content:center;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" style="background:transparent; border:none; color:#475569; cursor:pointer; padding:6px; display:inline-flex; align-items:center; justify-content:center; text-decoration:none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </a>
            @endif

            {{-- Next Page --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" style="background:transparent; border:none; color:#475569; cursor:pointer; padding:6px; display:inline-flex; align-items:center; justify-content:center; text-decoration:none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            @else
                <button disabled style="background:transparent; border:none; color:#cbd5e1; cursor:not-allowed; padding:6px; display:inline-flex; align-items:center; justify-content:center;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            @endif

            {{-- Last Page --}}
            @if ($paginator->hasMorePages())
                <a href="{{ request()->fullUrlWithQuery(['page' => $paginator->lastPage()]) }}" style="background:transparent; border:none; color:#475569; cursor:pointer; padding:6px; display:inline-flex; align-items:center; justify-content:center; text-decoration:none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polygon points="5 4 15 12 5 20 5 4"></polygon><line x1="19" y1="5" x2="19" y2="19"></line></svg>
                </a>
            @else
                <button disabled style="background:transparent; border:none; color:#cbd5e1; cursor:not-allowed; padding:6px; display:inline-flex; align-items:center; justify-content:center;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polygon points="5 4 15 12 5 20 5 4"></polygon><line x1="19" y1="5" x2="19" y2="19"></line></svg>
                </button>
            @endif
        </div>

    </div>
@endif
