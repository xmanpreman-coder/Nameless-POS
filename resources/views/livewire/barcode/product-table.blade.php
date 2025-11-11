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
                                $barcodeValue = $product->product_gtin ?? $product->product_sku ?? $product->product_code ?? '';
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
                                    {{ $product->product_sku ?? $product->product_code ?? 'N/A' }}
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
                        wire:loading.attr="disabled" 
                        type="button" 
                        class="btn btn-success btn-sm"
                    >
                        <span wire:loading.remove wire:target="downloadImage">
                            <i class="bi bi-download"></i> Download Image (PNG)
                        </span>
                        <span wire:loading wire:target="downloadImage">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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
            // Get all barcode containers from the page
            const barcodeContainers = document.querySelectorAll('.barcode-item-container');
            
            if (barcodeContainers.length === 0) {
                alert('Tidak ada barcode untuk didownload!');
                return;
            }

            // Check if html2canvas is loaded
            if (typeof html2canvas === "undefined") {
                alert("Error: html2canvas library tidak dimuat. Silakan refresh halaman dan coba lagi.");
                return;
            }

            let downloadCount = 0;
            const totalBarcodes = barcodeContainers.length;

            // Function to download single barcode from DOM element
            function downloadSingleBarcode(container, index) {
                return new Promise((resolve) => {
                    // Clone the container to avoid modifying the original
                    const clonedContainer = container.cloneNode(true);
                    clonedContainer.style.position = "absolute";
                    clonedContainer.style.left = "-9999px";
                    clonedContainer.style.width = container.offsetWidth + "px";
                    clonedContainer.style.backgroundColor = "#ffffff";
                    document.body.appendChild(clonedContainer);
                    
                    // Wait for rendering
                    setTimeout(() => {
                        html2canvas(clonedContainer, {
                            backgroundColor: "#ffffff",
                            scale: 2,
                            logging: false,
                            useCORS: true,
                            allowTaint: true
                        }).then(canvas => {
                            // Get product name from container
                            const nameElement = container.querySelector('p[style*="font-weight: bold"]');
                            const name = nameElement ? nameElement.textContent.trim() : 'barcode';
                            
                            // Get barcode value
                            const barcodeInfo = container.querySelector('p[style*="font-size: 11px"]');
                            const barcodeValue = barcodeInfo ? barcodeInfo.textContent.split(':')[1]?.trim() || index : index;
                            
                            // Download as PNG
                            const link = document.createElement("a");
                            const fileName = name.replace(/[^a-z0-9]/gi, "_") + "_" + barcodeValue + ".png";
                            link.download = fileName;
                            link.href = canvas.toDataURL("image/png");
                            link.click();
                            
                            // Remove cloned container
                            document.body.removeChild(clonedContainer);
                            
                            downloadCount++;
                            resolve();
                            
                            if (downloadCount === totalBarcodes) {
                                console.log("All barcodes downloaded successfully");
                            }
                        }).catch(err => {
                            console.error("Error converting to image:", err);
                            if (document.body.contains(clonedContainer)) {
                                document.body.removeChild(clonedContainer);
                            }
                            resolve();
                        });
                    }, 300 + (index * 200)); // Stagger downloads
                });
            }
            
            // Download all barcodes sequentially
            async function downloadAllBarcodes() {
                for (let i = 0; i < barcodeContainers.length; i++) {
                    await downloadSingleBarcode(barcodeContainers[i], i);
                }
            }
            
            downloadAllBarcodes();
        }
    </script>
    @endpush
</div>
