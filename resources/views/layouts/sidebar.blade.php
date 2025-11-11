<div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show {{ request()->routeIs('app.pos.*') ? 'c-sidebar-minimized' : '' }}" id="sidebar">
    <div class="c-sidebar-brand d-md-down-none">
        <a href="{{ route('home') }}">
            @php
                $settings = \Modules\Setting\Entities\Setting::first();
                $companyName = $settings ? $settings->company_name : 'Nameless.POS';
                $showLogo = false;
                
                if ($settings && $settings->site_logo) {
                    $logoPath = storage_path('app/public/' . $settings->site_logo);
                    if (file_exists($logoPath)) {
                        $logoUrl = asset('storage/' . $settings->site_logo) . '?v=' . filemtime($logoPath);
                        $showLogo = true;
                    }
                }
            @endphp
            
            @if($showLogo)
                <img class="c-sidebar-brand-full" src="{{ $logoUrl }}" alt="{{ $companyName }}" width="110" 
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <img class="c-sidebar-brand-minimized" src="{{ $logoUrl }}" alt="{{ $companyName }}" width="40" 
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div class="c-sidebar-brand-full" style="display:none; color: white; font-weight: bold; font-size: 16px; padding: 15px; text-align: center;">
                    {{ $companyName }}
                </div>
                <div class="c-sidebar-brand-minimized" style="display:none; color: white; font-weight: bold; font-size: 12px; padding: 8px; text-align: center;">
                    {{ substr($companyName, 0, 3) }}
                </div>
            @else
                <div class="c-sidebar-brand-full" style="color: white; font-weight: bold; font-size: 16px; padding: 15px; text-align: center;">
                    {{ $companyName }}
                </div>
                <div class="c-sidebar-brand-minimized" style="color: white; font-weight: bold; font-size: 12px; padding: 8px; text-align: center;">
                    {{ substr($companyName, 0, 3) }}
                </div>
            @endif
        </a>
    </div>
    <ul class="c-sidebar-nav">
        @include('layouts.menu')
        <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
            <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
        </div>
        <div class="ps__rail-y" style="top: 0px; height: 692px; right: 0px;">
            <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 369px;"></div>
        </div>
    </ul>
    <button class="c-sidebar-minimizer c-class-toggler" type="button" data-target="_parent" data-class="c-sidebar-minimized"></button>
</div>
