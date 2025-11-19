<?php

namespace App\Livewire\Barcode;

use Livewire\Component;
use Livewire\WithPagination;
use Milon\Barcode\Facades\DNS1DFacade;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Category;

class ProductTable extends Component
{
    use WithPagination;

    public $search = '';
    public $category_id = '';
    public $selectedProducts = [];
    public $quantities = [];
    public $barcodes = [];
    public $barcodeData = [];
    public $barcodeType = 'C128'; // Default barcode type
    public $barcodeSource = 'gtin'; // Default: gtin or sku

    protected $paginationTheme = 'bootstrap';

    public function updatedBarcodeSource() {
        // Clear barcodes when source changes
        $this->barcodes = [];
        $this->barcodeData = [];
    }

    public function mount() {
        $this->selectedProducts = [];
        $this->quantities = [];
        $this->barcodes = [];
        $this->barcodeData = [];
    }

    public function render() {
        $products = Product::query()
            ->with('category')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('product_name', 'like', '%' . $this->search . '%')
                            ->orWhere('product_sku', 'like', '%' . $this->search . '%')
                            ->orWhere('product_gtin', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->category_id, function ($query) {
                $query->where('category_id', $this->category_id);
            })
            ->orderBy('product_name')
            ->paginate(20);

        $categories = Category::orderBy('category_name')->get();

        return view('livewire.barcode.product-table', [
            'products' => $products,
            'categories' => $categories
        ]);
    }

    public function updatedSearch() {
        $this->resetPage();
    }

    public function updatedCategoryId() {
        $this->resetPage();
    }

    public function toggleProduct($productId) {
        if (in_array($productId, $this->selectedProducts)) {
            $this->selectedProducts = array_diff($this->selectedProducts, [$productId]);
            unset($this->quantities[$productId]);
        } else {
            $this->selectedProducts[] = $productId;
            $this->quantities[$productId] = 1;
        }
        $this->barcodes = [];
        $this->barcodeData = [];
    }

    public function selectAll() {
        // Get products from current page only
        $products = Product::query()
            ->with('category')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('product_name', 'like', '%' . $this->search . '%')
                      ->orWhere('product_sku', 'like', '%' . $this->search . '%')
                      ->orWhere('product_gtin', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->category_id, function ($query) {
                $query->where('category_id', $this->category_id);
            })
            ->orderBy('product_name')
            ->paginate(20);

        foreach ($products->items() as $product) {
            if (!in_array($product->id, $this->selectedProducts)) {
                $this->selectedProducts[] = $product->id;
                $this->quantities[$product->id] = 1;
            }
        }
    }

    public function deselectAll() {
        // Get products from current page only
        $products = Product::query()
            ->with('category')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('product_name', 'like', '%' . $this->search . '%')
                      ->orWhere('product_sku', 'like', '%' . $this->search . '%')
                      ->orWhere('product_gtin', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->category_id, function ($query) {
                $query->where('category_id', $this->category_id);
            })
            ->orderBy('product_name')
            ->paginate(20);

        // Remove only products from current page
        $currentPageProductIds = $products->pluck('id')->toArray();
        $this->selectedProducts = array_diff($this->selectedProducts, $currentPageProductIds);
        
        foreach ($currentPageProductIds as $productId) {
            unset($this->quantities[$productId]);
        }
        
        $this->barcodes = [];
        $this->barcodeData = [];
    }

    public function generateBarcodes() {
        if (empty($this->selectedProducts)) {
            return session()->flash('message', 'Silakan pilih minimal satu produk!');
        }

        $this->barcodes = [];
        $this->barcodeData = [];
        $errors = [];
        $successCount = 0;

        foreach ($this->selectedProducts as $productId) {
            $product = Product::find($productId);
            if (!$product) continue;

            $quantity = isset($this->quantities[$productId]) ? (int)$this->quantities[$productId] : 1;
            
            if ($quantity > 100) {
                $errors[] = "Produk '{$product->product_name}': Maksimal quantity adalah 100 per produk!";
                continue;
            }

            if ($quantity < 1) {
                $errors[] = "Produk '{$product->product_name}': Quantity harus minimal 1!";
                continue;
            }

            // Use selected source (GTIN or SKU)
            if ($this->barcodeSource === 'gtin') {
                $barcodeValue = $product->product_gtin ?? '';
                if (empty($barcodeValue)) {
                    $errors[] = "Produk '{$product->product_name}': Tidak memiliki GTIN!";
                    continue;
                }
            } else {
                // Use SKU
                $barcodeValue = $product->product_sku ?? '';
                if (empty($barcodeValue)) {
                    $errors[] = "Produk '{$product->product_name}': Tidak memiliki SKU!";
                    continue;
                }
            }

            if (!is_numeric($barcodeValue)) {
                $errors[] = "Produk '{$product->product_name}': " . ($this->barcodeSource === 'gtin' ? 'GTIN' : 'SKU') . " harus berupa angka numerik!";
                continue;
            }

            // Use selected barcode type or product's default
            $barcodeSymbology = $this->barcodeType ?: ($product->product_barcode_symbology ?? 'C128');
            
            // Generate barcodes for this product
            for ($i = 1; $i <= $quantity; $i++) {
                try {
                    $barcode = DNS1DFacade::getBarCodeSVG($barcodeValue, $barcodeSymbology, 2, 60, 'black', false);
                    $this->barcodes[] = $barcode;
                    $this->barcodeData[] = [
                        'barcode' => $barcode,
                        'name' => $product->product_name,
                        'price' => $product->product_price,
                        'sku' => $product->product_sku ?? '',
                        'gtin' => $product->product_gtin ?? '',
                        'barcode_value' => $barcodeValue,
                        'barcode_source' => $this->barcodeSource,
                    ];
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Produk '{$product->product_name}': Gagal membuat barcode - " . $e->getMessage();
                }
            }
        }

        if (!empty($errors)) {
            session()->flash('message', implode(' ', $errors));
        }

        if (empty($this->barcodes)) {
            session()->flash('message', 'Tidak ada barcode yang berhasil dibuat. Pastikan produk memiliki SKU atau GTIN yang berupa angka numerik.');
        } elseif ($successCount > 0) {
            session()->flash('success', "Berhasil membuat {$successCount} barcode!");
        }
    }

    public function downloadImage() {
        if (empty($this->barcodeData)) {
            session()->flash('message', 'Tidak ada barcode untuk didownload!');
            return;
        }

        // Dispatch browser event to trigger download
        $this->dispatch('download-barcode-images');
    }

    public function clearBarcodes() {
        $this->barcodes = [];
        $this->barcodeData = [];
    }

    public function getBarcodeDataForDownload() {
        return $this->barcodeData;
    }
}
