<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Modules\Product\Entities\Brand;
use Modules\Product\DataTables\ProductBrandsDataTable;

class BrandsController extends Controller
{

    public function index(ProductBrandsDataTable $dataTable) {
        abort_if(Gate::denies('access_product_brands'), 403);

        return $dataTable->render('product::brands.index');
    }

    public function store(Request $request) {
        abort_if(Gate::denies('access_product_brands'), 403);

        $request->validate([
            'brand_code' => 'required|unique:brands,brand_code',
            'brand_name' => 'required'
        ]);

        Brand::create([
            'brand_code' => $request->brand_code,
            'brand_name' => $request->brand_name,
        ]);

        toast('Product Brand Created!', 'success');

        return redirect()->back();
    }


    public function edit($id) {
        abort_if(Gate::denies('access_product_brands'), 403);

        $brand = Brand::findOrFail($id);

        return view('product::brands.edit', compact('brand'));
    }


    public function update(Request $request, $id) {
        abort_if(Gate::denies('access_product_brands'), 403);

        $request->validate([
            'brand_code' => 'required|unique:brands,brand_code,' . $id,
            'brand_name' => 'required'
        ]);

        Brand::findOrFail($id)->update([
            'brand_code' => $request->brand_code,
            'brand_name' => $request->brand_name,
        ]);

        toast('Product Brand Updated!', 'info');

        return redirect()->route('product-brands.index');
    }


    public function destroy($id) {
        abort_if(Gate::denies('access_product_brands'), 403);

        $brand = Brand::findOrFail($id);

        if ($brand->products()->exists()) {
            return back()->withErrors('Can\'t delete because there are products associated with this brand.');
        }

        $brand->delete();

        toast('Product Brand Deleted!', 'warning');

        return redirect()->route('product-brands.index');
    }
}
