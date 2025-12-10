<div>
    @if (session()->has('message'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <div class="alert-body">
                <span>{{ session('message') }}</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="alert-body">
                <span>{{ session('success') }}</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Pilih Produk untuk Cetak Barcode</h4>
        </div>
        <div class="card-body">
            <!-- Search and Filter -->
            <div class="row mb-3">
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="search">Cari Produk</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="search"
                            wire:model.live.debounce.300ms="search" 
                            placeholder="Cari berdasarkan nama, SKU, atau GTIN..."
                        >
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="category_id">Filter Kategori</label>
                        <select 
                            class="form-control" 
                            id="category_id"
                            wire:model.live="category_id"
                        >
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="barcodeSource">Sumber Barcode</label>
                        <select 
                            class="form-control" 
                            id="barcodeSource"
                            wire:model.live="barcodeSource"
                        >
                            <option value="gtin">GTIN</option>
                            <option value="sku">SKU</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="barcodeType">Jenis Barcode</label>
                        <select 
                            class="form-control" 
                            id="barcodeType"
                            wire:model="barcodeType"
                        >
                            <option value="C128">Code 128</option>
                            <option value="C39">Code 39</option>
                            <option value="EAN13">EAN-13</option>
                            <option value="EAN8">EAN-8</option>
                            <option value="UPCA">UPC-A</option>
                            <option value="UPCE">UPC-E</option>
                            <option value="MSI">MSI</option>
                            <option value="CODABAR">Codabar</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button 
                                type="button" 
                                class="btn btn-sm btn-primary btn-block" 
                                wire:click="selectAll"
                            >
                                Pilih Semua
                            </button>
                            <button 
                                type="button" 
                                class="btn btn-sm btn-secondary btn-block mt-1" 
                                wire:click="deselectAll"
                            >
                                Batal Semua
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product List -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">
                                @php
                                    $currentPageProductIds = $products->getCollection()->pluck('id')->toArray();
                                    $selectedOnCurrentPage = array_intersect($selectedProducts, $currentPageProductIds);
                                    $allCurrentPageSelected = count($selectedOnCurrentPage) == count($currentPageProductIds) && count($currentPageProductIds) > 0;
                                @endphp
                                <input 
                                    type="checkbox" 
                                    onclick="if(this.checked) { @this.call('selectAll') } else { @this.call('deselectAll') }"
                                    @if($allCurrentPageSelected) checked @endif
                                >
                            </th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>SKU</th>
                            <th>GTIN</th>
                            <th style="width: 150px;">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                                @php
                                $isSelected = in_array($product->id, $selectedProducts);
                                $barcodeValue = $product->product_gtin ?? $product->product_sku ?? '';
                                $hasValidBarcode = !empty($barcodeValue) && is_numeric($barcodeValue);
                            @endphp
                            <tr class="{{ $isSelected ? 'table-active' : '' }}">
                                <td class="text-center align-middle">
                                    <input 
                                        type="checkbox" 
                                        wire:click="toggleProduct({{ $product->id }})"
                                        @if($isSelected) checked @endif
                                    >
                                </td>
                                <td class="align-middle">
                                    {{ $product->product_name }}
                                    @if(!$hasValidBarcode)
                                        <br><small class="text-danger">
                                            <i class="bi bi-exclamation-triangle"></i> Tidak ada SKU/GTIN numerik
                                        </small>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    {{ $product->category->category_name ?? 'N/A' }}
                                </td>
                                <td class="align-middle">
                                    {{ $product->product_sku ?? 'N/A' }}
                                </td>
                                <td class="align-middle">
                                    {{ $product->product_gtin ?? 'N/A' }}
                                </td>
                                <td class="align-middle">
                                    @if($isSelected)
                                        <input 
                                            type="number" 
                                            class="form-control form-control-sm" 
                                            wire:model.live="quantities.{{ $product->id }}"
                                            min="1" 
                                            max="100" 
                                            value="{{ $quantities[$product->id] ?? 1 }}"
                                        >
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    <span class="text-muted">Tidak ada produk ditemukan</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $products->links() }}
            </div>

            <!-- Action Buttons -->
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted">
                        <strong>{{ count($selectedProducts) }}</strong> produk dipilih
                    </span>
                </div>
                <div>
                    <button 
                        type="button" 
                        class="btn btn-primary" 
                        wire:click="generateBarcodes"
                        wire:loading.attr="disabled"
                        @if(empty($selectedProducts)) disabled @endif
                    >
                        <span wire:loading.remove wire:target="generateBarcodes">
                            <i class="bi bi-upc-scan"></i> Generate Barcodes
                        </span>
                        <span wire:loading wire:target="generateBarcodes">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            Generating...
                        </span>
                    </button>
                            <!-- Print button removed per request -->
                    @if(!empty($barcodes))
                        <button 
                            type="button" 
                            class="btn btn-secondary ml-2" 
                            wire:click="clearBarcodes"
                        >
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Generated Barcodes -->
    @if(!empty($barcodes))
        <style>
            /* Make barcode SVGs responsive inside the card */
            .barcode-item-container svg,
            .barcode-item svg {
                max-width: 100%;
                height: auto;
                display: block;
                margin: 0 auto;
            }
        </style>
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Barcode yang Dihasilkan ({{ count($barcodes) }})</h4>
                <div class="btn-group">
                    <button 
                        wire:click="downloadImage" 
                        onclick="downloadBarcodesAsZip()" 
                        wire:loading.attr="disabled" 
                        type="button" 
                        class="btn btn-success btn-sm"
                    >
                        <span wire:loading.remove wire:target="downloadImage">
                            <i class="bi bi-file-earmark-zip"></i> Download ZIP
                        </span>
                        <span wire:loading wire:target="downloadImage">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </button>
                    
                    <button 
                        onclick="downloadBarcodesFromDOM()" 
                        type="button" 
                        class="btn btn-outline-success btn-sm ms-2"
                        title="Download individual PNG files"
                    >
                        <i class="bi bi-download"></i> Individual PNG
                    </button>
                    <button
                        onclick="downloadSVGsAsZip()"
                        type="button"
                        class="btn btn-outline-primary btn-sm ms-2"
                        title="Download SVG files (recommended)"
                    >
                        <i class="bi bi-file-earmark-code"></i> Download SVG
                    </button>
                    @if (class_exists('Imagick'))
                        <button
                            onclick="downloadPngServerZip()"
                            type="button"
                            class="btn btn-outline-secondary btn-sm ms-2"
                            title="Download PNG (server-side conversion)"
                        >
                            <i class="bi bi-cloud-download"></i> Download PNG (Server)
                        </button>
                    @endif
                    <button
                        onclick="downloadPngClientZip()"
                        type="button"
                        class="btn btn-outline-dark btn-sm ms-2"
                        title="Download PNG (client-side conversion, simple)"
                    >
                        <i class="bi bi-phone"></i> Download PNG (Client)
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($barcodeData as $index => $data)
                            @php
                                // Determine which label to show based on Livewire property or data
                                $selectedSource = isset($barcodeSource) ? strtolower($barcodeSource) : (strtolower($data['barcode_source'] ?? '') ?: 'gtin');
                                $label = $selectedSource === 'sku' ? 'sku' : 'gtin';
                                if ($label === 'sku') {
                                    $value = $data['sku'] ?? $data['barcode_value'] ?? '';
                                } else {
                                    $value = $data['gtin'] ?? $data['barcode_value'] ?? $data['sku'] ?? '';
                                }
                            @endphp

                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3 barcode-item-container" data-encoded="{{ $data['encoded_value'] ?? '' }}" style="border: 1px solid #ddd;border-style: dashed;background-color: #ffffff;padding: 15px;">
                                <p class="mt-2 mb-1" style="font-size: 14px;color: #000;font-weight: bold;">
                                    {{ $data['name'] }}
                                </p>
                                <div class="text-center">
                                    {!! $data['barcode'] !!}
                                </div>
                                <p class="mb-1" style="font-size: 11px;color: #000;">
                                    {{ strtoupper($label) }}: {{ $value }}
                                </p>
                                <p class="mb-1 text-muted" style="font-size:11px;">Encoded value: <strong>{{ $data['encoded_value'] ?? $data['barcode_value'] ?? '' }}</strong></p>
                                <p style="font-size: 13px;color: #000;font-weight: bold;">
                                    Price: {{ format_currency($data['price']) }}
                                </p>
                            </div>
                        @endforeach
                </div>
            </div>
        </div>
    @endif

    @push('page_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" onload="console.log('JSZip library loaded successfully')" onerror="console.error('Failed to load JSZip library')"></script>

    <script>
        // Minimal, stable download scripts: keep only SVG zip and simple client PNG zip.
        window._barcodeDataForServerDownload = @json($barcodeData ?? []);

        // Livewire listener only for barcode-data-ready (no automatic download triggers)
        if (typeof Livewire !== 'undefined') {
            if (typeof Livewire.on === 'function') {
                document.addEventListener('livewire:init', () => {
                    Livewire.on('barcode-data-ready', (payload) => {
                        try { window._barcodeDataForServerDownload = payload.data || []; } catch (e) { console.error(e); }
                    });
                });
            } else if (window.livewire && typeof window.livewire.on === 'function') {
                window.livewire.on('barcode-data-ready', (payload) => {
                    try { window._barcodeDataForServerDownload = payload.data || []; } catch (e) { console.error(e); }
                });
            }
        }

        // Helper to escape XML text in SVG
        function escapeXml(unsafe) {
            return (unsafe || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&apos;');
        }

        // Download raw SVGs wrapped with labels
        async function downloadSVGsAsZip() {
            if (typeof JSZip === 'undefined') { alert('JSZip belum dimuat.'); return; }
            const containers = document.querySelectorAll('.barcode-item-container');
            if (!containers.length) { alert('Tidak ada barcode untuk didownload'); return; }

            const zip = new JSZip();
            const folder = zip.folder('barcodes_svg');

            containers.forEach((container, i) => {
                const svgElem = container.querySelector('svg');
                if (!svgElem) return;
                const svgString = new XMLSerializer().serializeToString(svgElem);
                const encodedSvg = encodeURIComponent(svgString);

                // gather meta
                const productName = container.querySelector('p')?.textContent.trim() || 'barcode';
                let sku = '';
                let price = '';
                container.querySelectorAll('p').forEach(p => {
                    const t = p.textContent.trim();
                    if (/^sku[:\s]/i.test(t) || t.toLowerCase().includes('sku')) sku = t.split(':').slice(1).join(':').trim();
                    if (/price|rp/i.test(t)) price = t;
                });

                // Embed the original SVG as an <image> so we control the output size
                const imgWidth = 560; const imgHeight = 120;
                const imageTag = `<image href="data:image/svg+xml;utf8,${encodedSvg}" width="${imgWidth}" height="${imgHeight}" preserveAspectRatio="xMidYMid meet" />`;

                // Note: XML declaration removed to avoid Blade/PHP parsing issues.
                const wrapped = `<svg xmlns="http://www.w3.org/2000/svg" width="600" height="220" viewBox="0 0 600 220">`+
                                `<style>.title{font-family:Arial,Helvetica,sans-serif;font-size:16px;font-weight:700;fill:#000}.info{font-family:Arial,Helvetica,sans-serif;font-size:12px;fill:#000}</style>`+
                                `<text x="20" y="24" class="title">${escapeXml(productName)}</text>`+
                                `<g transform="translate(20,36)">`+imageTag+`</g>`+
                                `<text x="20" y="186" class="info">SKU/GTIN: ${escapeXml(sku)}</text>`+
                                `<text x="20" y="204" class="info">${escapeXml(price)}</text>`+
                                `</svg>`;

                const clean = (productName||'barcode').replace(/[^a-z0-9\s]/gi,'').replace(/\s+/g,'_');
                const name = `${clean}_${(sku||i+1) || i+1}.svg`;
                folder.file(name, wrapped);
            });

            const blob = await zip.generateAsync({type:'blob'});
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `barcodes_svg_${Date.now()}.zip`;
            document.body.appendChild(link); link.click(); document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        }

        // Simple client-side SVG -> PNG zip (keeps labels via wrapper canvas)
        async function downloadPngClientZip() {
            if (typeof JSZip === 'undefined') { alert('JSZip belum dimuat.'); return; }
            const containers = document.querySelectorAll('.barcode-item-container');
            if (!containers.length) { alert('Tidak ada barcode untuk didownload'); return; }

            const zip = new JSZip();
            const folder = zip.folder('barcodes_png');

            for (let i = 0; i < containers.length; i++) {
                const c = containers[i];
                const svg = c.querySelector('svg'); if (!svg) continue;
                    const svgString = new XMLSerializer().serializeToString(svg);
                    const blob = new Blob([svgString], {type:'image/svg+xml'});
                    const url = URL.createObjectURL(blob);
                    // create image
                    /* eslint-disable no-await-in-loop */
                    const img = await new Promise((res, rej) => { const im=new Image(); im.onload=()=>res(im); im.onerror=rej; im.src=url; });

                    // build canvas with title and footer
                    const title = c.querySelector('p')?.textContent.trim() || 'barcode';
                    let sku=''; let price=''; c.querySelectorAll('p').forEach(p=>{const t=p.textContent.trim(); if(/sku/i.test(t)) sku=t.split(':').slice(1).join(':').trim(); if(/price|rp/i.test(t)) price=t});
                    const padding = 20;
                    // Use fixed inner dimensions to avoid very large renders when original SVG lacks explicit dimensions
                    const innerW = 560; const innerH = 120;
                    const W = innerW + padding*2; const H = 28 + innerH + 40;
                    const scale=2; const canvas=document.createElement('canvas'); canvas.width=W*scale; canvas.height=H*scale; const ctx=canvas.getContext('2d'); ctx.fillStyle='#fff'; ctx.fillRect(0,0,canvas.width,canvas.height);
                    ctx.fillStyle='#000'; ctx.font=`${14*scale}px Arial`; ctx.fillText(title, padding*scale, 18*scale);
                    ctx.drawImage(img, padding*scale, 28*scale, innerW*scale, innerH*scale);
                    ctx.font=`${12*scale}px Arial`; ctx.fillText(sku, padding*scale, (28+innerH+18)*scale); if(price) ctx.fillText(price, padding*scale, (28+innerH+34)*scale);
                    const dataUrl = canvas.toDataURL('image/png'); const base64 = dataUrl.split(',')[1];
                    const clean = title.replace(/[^a-z0-9\s]/gi,'').replace(/\s+/g,'_'); const name = `${clean}_${(sku||i+1)}.png`;
                    folder.file(name, base64, {base64:true}); URL.revokeObjectURL(url);
            }

            const zipBlob = await zip.generateAsync({type:'blob'});
            const link = document.createElement('a'); link.href = URL.createObjectURL(zipBlob); link.download = `barcodes_png_${Date.now()}.zip`; document.body.appendChild(link); link.click(); document.body.removeChild(link); URL.revokeObjectURL(link.href);
        }

        // Backwards-compatible global aliases for older inline onclick handlers
        function _assignBarcodeAliases() {
            try {
                window.downloadBarcodesAsZip = function() { return downloadSVGsAsZip(); };
                window.downloadBarcodesFromDOM = function() { return downloadPngClientZip(); };
            } catch (e) { console.debug('assign aliases failed', e); }
        }
        _assignBarcodeAliases();

        // Re-assign after Livewire updates (Livewire may re-render DOM)
        if (typeof Livewire !== 'undefined') {
            try {
                document.addEventListener('livewire:update', _assignBarcodeAliases);
                document.addEventListener('livewire:load', _assignBarcodeAliases);
            } catch (e) { /* ignore */ }
        }
    </script>
    @endpush
</div>
