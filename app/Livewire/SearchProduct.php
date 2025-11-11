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
            ->orWhere('product_code', 'like', '%' . $this->query . '%') // Backward compatibility
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
        $this->dispatch('productSelected', $product);
    }

    public function searchByBarcode($barcode) {
        // Search for product by barcode
        $product = Product::where('product_barcode_symbology', $barcode)
                         ->orWhere('product_code', $barcode)
                         ->orWhere('product_gtin', $barcode)
                         ->first();

        if ($product) {
            $this->selectProduct($product);
            $this->resetQuery();
            session()->flash('scanner_success', 'Product found and added: ' . $product->product_name);
        } else {
            session()->flash('scanner_error', 'Product not found for barcode: ' . $barcode);
        }
        
        $this->dispatch('scannerResult', [
            'success' => $product ? true : false,
            'message' => $product ? 'Product found and added!' : 'Product not found for barcode: ' . $barcode,
            'product' => $product ? $product->toArray() : null
        ]);
    }
}
