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
                                    $currentPageProductIds = $products->pluck('id')->toArray();
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
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($barcodeData as $index => $data)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3 barcode-item-container" style="border: 1px solid #ddd;border-style: dashed;background-color: #ffffff;padding: 15px;">
                            <p class="mt-2 mb-1" style="font-size: 14px;color: #000;font-weight: bold;">
                                {{ $data['name'] }}
                            </p>
                            <div class="text-center">
                                {!! $data['barcode'] !!}
                            </div>
                            <p class="mb-1" style="font-size: 11px;color: #000;">
                                {{ strtoupper($data['barcode_source']) }}: {{ $data['barcode_value'] }}
                            </p>
                            @if($data['gtin'])
                                <p class="mb-1" style="font-size: 11px;color: #666;">
                                    GTIN: {{ $data['gtin'] }}
                                </p>
                            @endif
                            @if($data['sku'])
                                <p class="mb-1" style="font-size: 11px;color: #666;">
                                    SKU: {{ $data['sku'] }}
                                </p>
                            @endif
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" onload="console.log('html2canvas loaded successfully from CDN')" onerror="console.error('Failed to load html2canvas from CDN')"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" onload="console.log('JSZip library loaded successfully')" onerror="console.error('Failed to load JSZip library')"></script>
    
    <script>
        // Debug: Check if html2canvas loads after DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                console.log('=== HTML2CANVAS DEBUG ===');
                console.log('typeof html2canvas:', typeof html2canvas);
                console.log('window.html2canvas:', window.html2canvas);
                
                if (typeof html2canvas === 'undefined') {
                    console.error('CRITICAL: html2canvas is still undefined after page load');
                    console.log('Available window properties:', Object.keys(window).filter(key => key.toLowerCase().includes('html') || key.toLowerCase().includes('canvas')));
                } else {
                    console.log('SUCCESS: html2canvas is available and ready');
                    console.log('html2canvas function:', html2canvas);
                }
            }, 2000);
        });
    </script>
    
    <script>
        // Support both Livewire v2 and v3
        if (typeof Livewire !== 'undefined') {
            if (typeof Livewire.on === 'function') {
                // Livewire v3
                document.addEventListener('livewire:init', () => {
                    Livewire.on('download-barcode-images', () => {
                        setTimeout(() => {
                            downloadBarcodesFromDOM();
                        }, 300);
                    });
                });
            } else {
                // Livewire v2
                window.livewire.on('download-barcode-images', () => {
                    setTimeout(() => {
                        downloadBarcodesFromDOM();
                    }, 300);
                });
            }
        }

        function downloadBarcodesFromDOM() {
            console.log('=== BARCODE DOWNLOAD DEBUG START ===');
            console.log('Download barcode images triggered...');
            
            // Debug: Check current page and barcode containers
            console.log('Current page URL:', window.location.href);
            console.log('Document title:', document.title);
            
            // Get all barcode containers from the page
            const barcodeContainers = document.querySelectorAll('.barcode-item-container');
            console.log('🔍 Found barcode containers:', barcodeContainers.length);
            
            // If none found, try alternative selectors
            if (barcodeContainers.length === 0) {
                console.log('⚠️ Primary selector failed, trying alternatives...');
                
                const alternatives = [
                    { selector: '.barcode-container', name: 'barcode-container' },
                    { selector: '.barcode-item', name: 'barcode-item' },
                    { selector: '[class*="barcode"]', name: 'any barcode class' },
                    { selector: '.print-barcode-item', name: 'print-barcode-item' },
                    { selector: '.row > .col', name: 'row columns' },
                    { selector: 'svg', name: 'SVG elements' },
                    { selector: '.card', name: 'card elements' }
                ];
                
                for (let alt of alternatives) {
                    const found = document.querySelectorAll(alt.selector);
                    if (found.length > 0) {
                        console.log(`✅ Found ${found.length} elements with selector "${alt.selector}" (${alt.name})`);
                        console.log('Sample element:', found[0]);
                        
                        // If we find SVG or cards, those might be our barcode containers
                        if (alt.selector === 'svg' || alt.selector === '.card' || alt.selector === '.row > .col') {
                            console.log(`🎯 Using ${alt.selector} as barcode containers`);
                            return Array.from(found); // Return alternative containers
                        }
                    }
                }
            }
            
            // Debug: List all containers that might be barcodes
            const allContainers = document.querySelectorAll('[class*="barcode"], [id*="barcode"]');
            console.log('All potential barcode elements:', allContainers.length);
            allContainers.forEach((el, i) => {
                console.log(`Element ${i}:`, el.className, el.id);
            });
            
            // Debug: Check specific selectors
            const altSelectors = [
                '.barcode-container',
                '.barcode-item',
                '[data-barcode]',
                '.product-barcode',
                '#barcode-display'
            ];
            
            altSelectors.forEach(selector => {
                const found = document.querySelectorAll(selector);
                if (found.length > 0) {
                    console.log(`Alternative selector "${selector}" found:`, found.length, 'elements');
                }
            });
            
            if (barcodeContainers.length === 0) {
                console.error('NO BARCODE CONTAINERS FOUND!');
                console.log('Available classes on page:', Array.from(document.querySelectorAll('[class]')).map(el => el.className).filter(c => c).slice(0, 20));
                alert('Tidak ada barcode untuk didownload! Pastikan Anda sudah generate barcode terlebih dahulu.');
                return;
            }

            // Check if html2canvas is loaded
            console.log('Checking html2canvas availability...');
            console.log('typeof html2canvas:', typeof html2canvas);
            console.log('window.html2canvas:', window.html2canvas);
            
            if (typeof html2canvas === "undefined") {
                console.error("CRITICAL ERROR: html2canvas library not loaded");
                console.log('Trying to load html2canvas manually...');
                
                // Try to load manually
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                script.onload = function() {
                    console.log('html2canvas loaded manually, retrying download...');
                    setTimeout(downloadBarcodesFromDOM, 1000);
                };
                script.onerror = function() {
                    console.error('Failed to load html2canvas manually');
                    alert("Error: html2canvas library tidak dapat dimuat. Coba gunakan browser yang berbeda atau periksa koneksi internet.");
                };
                document.head.appendChild(script);
                return;
            }

            console.log("SUCCESS: html2canvas library available, starting download process...");

            let downloadCount = 0;
            const totalBarcodes = barcodeContainers.length;
            let downloadPromises = [];

            // Function to download single barcode from DOM element
            function downloadSingleBarcode(container, index) {
                return new Promise((resolve) => {
                    console.log(`Processing barcode ${index + 1}/${totalBarcodes}`);
                    
                    try {
                        // Clone the container to avoid modifying the original
                        const clonedContainer = container.cloneNode(true);
                        clonedContainer.style.position = "absolute";
                        clonedContainer.style.left = "-9999px";
                        clonedContainer.style.top = "0";
                        clonedContainer.style.width = "300px"; // Fixed width for consistency
                        clonedContainer.style.minHeight = "150px";
                        clonedContainer.style.backgroundColor = "#ffffff";
                        clonedContainer.style.padding = "15px";
                        clonedContainer.style.border = "1px solid #ddd";
                        clonedContainer.style.fontFamily = "Arial, sans-serif";
                        document.body.appendChild(clonedContainer);
                        
                        // Wait for DOM to settle
                        setTimeout(() => {
                            html2canvas(clonedContainer, {
                                backgroundColor: "#ffffff",
                                width: 300,
                                height: 200,
                                scale: 2,
                                logging: false,
                                useCORS: true,
                                allowTaint: true,
                                imageTimeout: 15000,
                                onclone: function(clonedDoc) {
                                    // Ensure all styles are applied in cloned document
                                    const clonedElement = clonedDoc.body.querySelector('[style*="position: absolute"]');
                                    if (clonedElement) {
                                        clonedElement.style.position = "static";
                                        clonedElement.style.left = "auto";
                                    }
                                }
                            }).then(canvas => {
                                console.log(`Canvas created for barcode ${index + 1}`);
                                
                                // Get product name from container (more robust selector)
                                let name = 'barcode';
                                const nameElements = container.querySelectorAll('p');
                                for (let nameEl of nameElements) {
                                    if (nameEl.style.fontWeight === 'bold' || nameEl.textContent.trim().length > 3) {
                                        name = nameEl.textContent.trim();
                                        break;
                                    }
                                }
                                
                                // Get barcode value (more robust)
                                let barcodeValue = index + 1;
                                const barcodeInfos = container.querySelectorAll('p');
                                for (let info of barcodeInfos) {
                                    if (info.textContent.includes(':') && (info.textContent.includes('SKU') || info.textContent.includes('GTIN'))) {
                                        const parts = info.textContent.split(':');
                                        if (parts.length > 1) {
                                            barcodeValue = parts[1].trim();
                                            break;
                                        }
                                    }
                                }
                                
                                // Create download link
                                const link = document.createElement("a");
                                const cleanName = name.replace(/[^a-z0-9\s]/gi, "").replace(/\s+/g, "_");
                                const fileName = `${cleanName}_${barcodeValue}.png`;
                                
                                link.download = fileName;
                                link.href = canvas.toDataURL("image/png", 0.95);
                                
                                // Trigger download
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                                
                                console.log(`Downloaded: ${fileName}`);
                                
                                // Remove cloned container
                                if (document.body.contains(clonedContainer)) {
                                    document.body.removeChild(clonedContainer);
                                }
                                
                                downloadCount++;
                                
                                if (downloadCount === totalBarcodes) {
                                    console.log("All barcodes downloaded successfully!");
                                    alert(`Berhasil mendownload ${totalBarcodes} barcode!`);
                                }
                                
                                resolve();
                                
                            }).catch(err => {
                                console.error(`Error converting barcode ${index + 1} to image:`, err);
                                
                                // Remove cloned container
                                if (document.body.contains(clonedContainer)) {
                                    document.body.removeChild(clonedContainer);
                                }
                                
                                downloadCount++;
                                resolve();
                            });
                        }, 500 + (index * 300)); // Increased delay for stability
                        
                    } catch (error) {
                        console.error(`Error processing barcode ${index + 1}:`, error);
                        downloadCount++;
                        resolve();
                    }
                });
            }
            
            // Download all barcodes sequentially with error handling
            async function downloadAllBarcodes() {
                try {
                    console.log(`Starting download of ${totalBarcodes} barcodes...`);
                    
                    for (let i = 0; i < barcodeContainers.length; i++) {
                        await downloadSingleBarcode(barcodeContainers[i], i);
                        
                        // Small delay between downloads to prevent browser blocking
                        if (i < barcodeContainers.length - 1) {
                            await new Promise(resolve => setTimeout(resolve, 200));
                        }
                    }
                    
                    console.log("Download process completed");
                    
                } catch (error) {
                    console.error("Error in download process:", error);
                    alert("Terjadi kesalahan saat mendownload barcode. Silakan coba lagi.");
                }
            }
            
            downloadAllBarcodes();
        }

        // New ZIP download function
        async function downloadBarcodesAsZip() {
            console.log('=== ZIP DOWNLOAD START ===');
            
            // Check if JSZip library is loaded
            if (typeof JSZip === "undefined") {
                console.error("JSZip library not loaded");
                alert("Error: JSZip library tidak dimuat. Silakan refresh halaman dan coba lagi.");
                return;
            }

            // Get all barcode containers
            const barcodeContainers = document.querySelectorAll('.barcode-item-container');
            console.log('🔍 Found barcode containers for ZIP:', barcodeContainers.length);
            
            if (barcodeContainers.length === 0) {
                alert('Tidak ada barcode untuk didownload!');
                return;
            }

            console.log("✅ JSZip library available, starting ZIP generation...");

            // Create new ZIP instance
            const zip = new JSZip();
            const zipFolder = zip.folder("barcodes");
            
            let processedCount = 0;
            const totalBarcodes = barcodeContainers.length;

            // Show progress indicator
            const progressDiv = document.createElement('div');
            progressDiv.innerHTML = `
                <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); 
                           background: white; padding: 20px; border: 2px solid #007bff; border-radius: 8px; 
                           box-shadow: 0 4px 8px rgba(0,0,0,0.2); z-index: 9999; text-align: center;">
                    <div style="margin-bottom: 15px;">
                        <i class="bi bi-file-earmark-zip" style="font-size: 2rem; color: #007bff;"></i>
                    </div>
                    <h5>Creating ZIP file...</h5>
                    <div id="zip-progress">Processing barcode 1 of ${totalBarcodes}</div>
                    <div style="margin-top: 10px;">
                        <div style="width: 300px; height: 10px; background: #f0f0f0; border-radius: 5px; overflow: hidden;">
                            <div id="zip-progress-bar" style="width: 0%; height: 100%; background: #007bff; transition: width 0.3s;"></div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(progressDiv);

            // Function to convert single barcode to canvas and add to ZIP
            async function processSingleBarcodeToZip(container, index) {
                return new Promise((resolve) => {
                    try {
                        console.log(`📷 Processing barcode ${index + 1}/${totalBarcodes} for ZIP`);
                        
                        // Update progress
                        const progressText = document.getElementById('zip-progress');
                        const progressBar = document.getElementById('zip-progress-bar');
                        if (progressText) {
                            progressText.textContent = `Processing barcode ${index + 1} of ${totalBarcodes}`;
                        }
                        if (progressBar) {
                            progressBar.style.width = `${((index) / totalBarcodes) * 100}%`;
                        }

                        // Clone container for processing
                        const clonedContainer = container.cloneNode(true);
                        clonedContainer.style.position = "absolute";
                        clonedContainer.style.left = "-9999px";
                        clonedContainer.style.top = "0";
                        clonedContainer.style.width = "300px";
                        clonedContainer.style.minHeight = "150px";
                        clonedContainer.style.backgroundColor = "#ffffff";
                        clonedContainer.style.padding = "15px";
                        clonedContainer.style.border = "1px solid #ddd";
                        clonedContainer.style.fontFamily = "Arial, sans-serif";
                        document.body.appendChild(clonedContainer);
                        
                        setTimeout(() => {
                            html2canvas(clonedContainer, {
                                backgroundColor: "#ffffff",
                                width: 300,
                                height: 200,
                                scale: 2,
                                logging: false,
                                useCORS: true,
                                allowTaint: true
                            }).then(canvas => {
                                console.log(`✅ Canvas created for barcode ${index + 1}`);
                                
                                // Get filename
                                let name = 'barcode';
                                let barcodeValue = index + 1;
                                
                                // Extract product name and barcode value
                                const nameElements = container.querySelectorAll('p');
                                for (let nameEl of nameElements) {
                                    if (nameEl.style.fontWeight === 'bold' || nameEl.textContent.trim().length > 3) {
                                        name = nameEl.textContent.trim();
                                        break;
                                    }
                                }
                                
                                const barcodeInfos = container.querySelectorAll('p');
                                for (let info of barcodeInfos) {
                                    if (info.textContent.includes(':') && (info.textContent.includes('SKU') || info.textContent.includes('GTIN'))) {
                                        const parts = info.textContent.split(':');
                                        if (parts.length > 1) {
                                            barcodeValue = parts[1].trim();
                                            break;
                                        }
                                    }
                                }
                                
                                // Create filename
                                const cleanName = name.replace(/[^a-z0-9\s]/gi, "").replace(/\s+/g, "_");
                                const fileName = `${cleanName}_${barcodeValue}.png`;
                                
                                // Convert canvas to base64 and add to ZIP
                                const base64Data = canvas.toDataURL('image/png').split(',')[1];
                                zipFolder.file(fileName, base64Data, { base64: true });
                                
                                console.log(`📁 Added to ZIP: ${fileName}`);
                                
                                // Clean up
                                document.body.removeChild(clonedContainer);
                                
                                processedCount++;
                                resolve();
                                
                            }).catch(err => {
                                console.error(`❌ Error processing barcode ${index + 1}:`, err);
                                if (document.body.contains(clonedContainer)) {
                                    document.body.removeChild(clonedContainer);
                                }
                                processedCount++;
                                resolve();
                            });
                        }, 300 + (index * 100));
                        
                    } catch (error) {
                        console.error(`❌ Error processing barcode ${index + 1}:`, error);
                        processedCount++;
                        resolve();
                    }
                });
            }
            
            // Process all barcodes sequentially
            for (let i = 0; i < barcodeContainers.length; i++) {
                await processSingleBarcodeToZip(barcodeContainers[i], i);
            }

            // Update progress to 100%
            const progressBar = document.getElementById('zip-progress-bar');
            const progressText = document.getElementById('zip-progress');
            if (progressBar) progressBar.style.width = '100%';
            if (progressText) progressText.textContent = 'Generating ZIP file...';

            console.log('📦 All barcodes processed, generating ZIP file...');

            try {
                // Generate ZIP file
                const zipBlob = await zip.generateAsync({ 
                    type: "blob",
                    compression: "DEFLATE",
                    compressionOptions: { level: 6 }
                });
                
                console.log('✅ ZIP file generated successfully');
                
                // Create download link
                const zipFileName = `barcodes_${new Date().getTime()}.zip`;
                const link = document.createElement("a");
                link.href = URL.createObjectURL(zipBlob);
                link.download = zipFileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Clean up
                URL.revokeObjectURL(link.href);
                
                console.log(`🎉 ZIP download completed: ${zipFileName}`);
                alert(`Berhasil mendownload ZIP dengan ${processedCount} barcode!`);
                
            } catch (error) {
                console.error('❌ Error generating ZIP:', error);
                alert('Terjadi kesalahan saat membuat ZIP file. Silakan coba lagi.');
            } finally {
                // Remove progress indicator
                if (document.body.contains(progressDiv)) {
                    document.body.removeChild(progressDiv);
                }
            }
        }
    </script>
    @endpush
</div>
