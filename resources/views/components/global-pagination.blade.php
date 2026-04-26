<!-- VISIBLE PAGINATION - ULTIMATE BEAUTIFUL DESIGN -->
@if (isset($paginator) && $paginator->hasPages())
    @php
        // Helper function to show limited page numbers with dots
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $window = 2; // number of pages to show on each side of current page
        
        $pages = [];
        for ($i = 1; $i <= $lastPage; $i++) {
            if ($i == 1 || $i == $lastPage || abs($i - $currentPage) <= $window) {
                $pages[] = $i;
            } elseif (end($pages) != '...') {
                $pages[] = '...';
            }
        }
        
        $isRtl = (app()->getLocale() === 'ar');
        $prevIcon = $isRtl ? '→' : '←';
        $nextIcon = $isRtl ? '←' : '→';
    @endphp

    <div style="
        display: block !important;
        visibility: visible !important;
        width: 100% !important;
        margin: 32px 0 !important;
        font-family: system-ui, -apple-system, 'Segoe UI', 'Inter', 'Roboto', sans-serif !important;
        direction: {{ $isRtl ? 'rtl' : 'ltr' }} !important;
    ">
        <!-- Main Container -->
        <div style="
            background: linear-gradient(135deg, #ffffff 0%, #fefefe 100%) !important;
            border-radius: 28px !important;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.02) !important;
            padding: 16px 24px !important;
            border: 1px solid rgba(203, 213, 225, 0.4) !important;
            transition: all 0.2s ease !important;
        ">
            <!-- Progress Bar & Summary -->
            <div style="
                display: flex !important;
                flex-wrap: wrap !important;
                justify-content: space-between !important;
                align-items: center !important;
                gap: 12px !important;
                margin-bottom: 20px !important;
                padding-bottom: 12px !important;
                border-bottom: 1px solid #eef2f6 !important;
            ">
                <!-- Results Summary -->
                <div style="
                    font-size: 14px !important;
                    color: #475569 !important;
                    background: #f8fafc !important;
                    padding: 6px 14px !important;
                    border-radius: 40px !important;
                    display: inline-flex !important;
                    align-items: center !important;
                    gap: 6px !important;
                ">
                    <span style="font-weight: 500;">📄</span>
                    @if($isRtl)
                        عرض <strong style="color: #3b82f6; margin: 0 4px;">{{ $paginator->firstItem() }}</strong> إلى <strong style="color: #3b82f6; margin: 0 4px;">{{ $paginator->lastItem() }}</strong> من <strong style="color: #1e293b; margin: 0 4px;">{{ $paginator->total() }}</strong> نتيجة
                    @else
                        Showing <strong style="color: #3b82f6; margin: 0 4px;">{{ $paginator->firstItem() }}</strong> to <strong style="color: #3b82f6; margin: 0 4px;">{{ $paginator->lastItem() }}</strong> of <strong style="color: #1e293b; margin: 0 4px;">{{ $paginator->total() }}</strong> results
                    @endif
                </div>

                <!-- Progress Indicator -->
                <div style="
                    font-size: 13px !important;
                    color: #475569 !important;
                    background: #f1f5f9 !important;
                    padding: 4px 12px !important;
                    border-radius: 30px !important;
                    display: inline-flex !important;
                    align-items: center !important;
                    gap: 8px !important;
                ">
                    <span style="font-weight: 500;">📊</span>
                    <span>{{ $currentPage }} / {{ $lastPage }}</span>
                    <div style="width: 60px; height: 4px; background: #e2e8f0; border-radius: 10px; overflow: hidden;">
                        <div style="width: {{ ($currentPage / $lastPage) * 100 }}%; height: 100%; background: #3b82f6; border-radius: 10px;"></div>
                    </div>
                </div>
            </div>

            <!-- Pagination Buttons -->
            <div style="
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                flex-wrap: wrap !important;
                gap: 8px !important;
            ">
                <!-- Previous Button -->
                @if ($paginator->onFirstPage())
                    <span style="
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        min-width: 40px !important;
                        height: 40px !important;
                        padding: 0 12px !important;
                        background: #f1f5f9 !important;
                        color: #94a3b8 !important;
                        border-radius: 14px !important;
                        font-size: 16px !important;
                        font-weight: 500 !important;
                        cursor: default !important;
                        opacity: 0.6 !important;
                        transition: all 0.2s !important;
                    ">{{ $prevIcon }} {{ $isRtl ? 'السابق' : 'Prev' }}</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" wire:click.prevent="previousPage" style="
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        min-width: 40px !important;
                        height: 40px !important;
                        padding: 0 14px !important;
                        background: #ffffff !important;
                        color: #1e293b !important;
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 14px !important;
                        font-size: 14px !important;
                        font-weight: 500 !important;
                        text-decoration: none !important;
                        transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1) !important;
                        box-shadow: 0 1px 2px rgba(0,0,0,0.02) !important;
                    " onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.03)';" onmouseout="this.style.background='#ffffff'; this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.02)';">
                        {{ $prevIcon }} {{ $isRtl ? 'السابق' : 'Prev' }}
                    </a>
                @endif

                <!-- Page Numbers with Dots -->
                @foreach ($pages as $page)
                    @if ($page == '...')
                        <span style="
                            display: inline-flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            min-width: 40px !important;
                            height: 40px !important;
                            color: #94a3b8 !important;
                            font-size: 18px !important;
                            font-weight: 500 !important;
                            letter-spacing: 2px !important;
                        ">⋯</span>
                    @elseif ($page == $currentPage)
                        <span style="
                            display: inline-flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            min-width: 44px !important;
                            height: 44px !important;
                            background: #3b82f6 !important;
                            color: white !important;
                            border-radius: 16px !important;
                            font-size: 15px !important;
                            font-weight: 600 !important;
                            box-shadow: 0 4px 8px rgba(59,130,246,0.25) !important;
                            transform: scale(1.02) !important;
                            cursor: default !important;
                        ">{{ $page }}</span>
                    @else
                        <a href="{{ $paginator->url($page) }}" wire:click.prevent="gotoPage({{ $page }})" style="
                            display: inline-flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            min-width: 40px !important;
                            height: 40px !important;
                            background: #ffffff !important;
                            color: #334155 !important;
                            border: 1px solid #e2e8f0 !important;
                            border-radius: 14px !important;
                            font-size: 14px !important;
                            font-weight: 500 !important;
                            text-decoration: none !important;
                            transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1) !important;
                        " onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#cbd5e1'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#ffffff'; this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)';">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                <!-- Next Button -->
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" wire:click.prevent="nextPage" style="
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        min-width: 40px !important;
                        height: 40px !important;
                        padding: 0 14px !important;
                        background: #ffffff !important;
                        color: #1e293b !important;
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 14px !important;
                        font-size: 14px !important;
                        font-weight: 500 !important;
                        text-decoration: none !important;
                        transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1) !important;
                    " onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#ffffff'; this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)';">
                        {{ $isRtl ? 'التالي' : 'Next' }} {{ $nextIcon }}
                    </a>
                @else
                    <span style="
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        min-width: 40px !important;
                        height: 40px !important;
                        padding: 0 12px !important;
                        background: #f1f5f9 !important;
                        color: #94a3b8 !important;
                        border-radius: 14px !important;
                        font-size: 14px !important;
                        font-weight: 500 !important;
                        cursor: default !important;
                        opacity: 0.6 !important;
                    ">{{ $isRtl ? 'السابق' : 'Next' }} {{ $nextIcon }}</span>
                @endif
            </div>
        </div>
    </div>
@endif