<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Livewire\Component;
use Modules\Product\Entities\Product;

class SearchProduct extends Component
{

    public $query;
    public $search_results;
    public $how_many;

    public function mount() {
        $this->query = '';
        $this->how_many = 5;
        $this->search_results = Collection::empty();
    }

    public function render() {
        return view('livewire.search-product');
    }

    public function updatedQuery() {
        $this->search_results = Product::where('product_name', 'like', '%' . $this->query . '%')
            ->orWhere('product_sku', 'like', '%' . $this->query . '%')
            ->orWhere('product_gtin', 'like', '%' . $this->query . '%')
            ->take($this->how_many)->get();
    }

    public function loadMore() {
        $this->how_many += 5;
        $this->updatedQuery();
    }

    public function resetQuery() {
        $this->query = '';
        $this->how_many = 5;
        $this->search_results = Collection::empty();
    }

    public function selectProduct($product) {
        // Ensure product is passed as array for consistent event handling
        if (is_object($product)) {
            $productArray = $product->toArray();
        } else {
            $productArray = $product;
        }
        
        $this->dispatch('productSelected', $productArray);
    }

    public function searchByBarcode($barcode) {
        // Search for product by barcode with reconstruction support
        $searchResult = $this->searchProductWithDetails($barcode);
        
        if ($searchResult['product']) {
            $product = $searchResult['product'];
            
            // Convert to array for proper event dispatching
            $productArray = $product->toArray();
            
            // Dispatch event to add product to cart (specifically for POS)
            $this->dispatch('productSelected', $productArray);
            $this->resetQuery();
            
            $message = $searchResult['reconstructed'] ? 
                'Product found (barcode reconstructed: ' . $searchResult['actual_barcode'] . ')' : 
                'Product found and added: ' . $product->product_name;
                
            session()->flash('scanner_success', $message);
            
            // Log reconstruction for debugging
            if ($searchResult['reconstructed']) {
                \Log::info("Livewire Scanner: Barcode reconstructed", [
                    'original' => $barcode,
                    'reconstructed' => $searchResult['actual_barcode'],
                    'product' => $product->product_name
                ]);
            }
        } else {
            session()->flash('scanner_error', 'Product not found for barcode: ' . $barcode);
        }
        
        $this->dispatch('scannerResult', [
            'success' => $searchResult['product'] ? true : false,
            'message' => $searchResult['product'] ? 'Product found and added!' : 'Product not found for barcode: ' . $barcode,
            'product' => $searchResult['product'] ? $searchResult['product']->toArray() : null,
            'reconstructed' => $searchResult['reconstructed'],
            'actual_barcode' => $searchResult['actual_barcode']
        ]);
    }

    /**
     * Search for product with barcode reconstruction support
     */
    private function searchProductWithDetails($barcode) {
        // First try exact match
        $product = Product::where('product_barcode_symbology', $barcode)
                         ->orWhere('product_sku', $barcode)
                         ->orWhere('product_gtin', $barcode)
                         ->first();

        if ($product) {
            return [
                'product' => $product,
                'actual_barcode' => $barcode,
                'reconstructed' => false
            ];
        }

        // If not found and barcode looks like it might be missing first digit
        if ($this->mightBeMissingFirstDigit($barcode)) {
            $result = $this->searchWithPossibleMissingDigitDetails($barcode);
            if ($result) {
                return $result;
            }
        }

        return [
            'product' => null,
            'actual_barcode' => $barcode,
            'reconstructed' => false
        ];
    }

    /**
     * Check if barcode might be missing first digit
     */
    private function mightBeMissingFirstDigit($barcode) {
        // Check for common patterns where first digit might be missing
        // EAN-13 becomes 12 digits, EAN-8 becomes 7 digits, UPC-A becomes 11 digits
        $length = strlen($barcode);
        
        return in_array($length, [7, 11, 12]) && is_numeric($barcode);
    }

    /**
     * Search for product by trying common first digits with details
     */
    private function searchWithPossibleMissingDigitDetails($barcode) {
        // Common first digits for Indonesian products (8 is most common for EAN-13)
        $commonFirstDigits = ['8', '9', '0', '1', '2', '3', '4', '5', '6', '7'];
        
        foreach ($commonFirstDigits as $digit) {
            $fullBarcode = $digit . $barcode;
            
            $product = Product::where('product_barcode_symbology', $fullBarcode)
                             ->orWhere('product_sku', $fullBarcode)
                             ->orWhere('product_gtin', $fullBarcode)
                             ->first();
            
            if ($product) {
                return [
                    'product' => $product,
                    'actual_barcode' => $fullBarcode,
                    'reconstructed' => true
                ];
            }
        }
        
        return null;
    }
}
