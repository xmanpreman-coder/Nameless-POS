<?php

namespace Modules\Brand\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Modules\Brand\Entities\Brand;
use Modules\Brand\Http\Requests\StoreBrandRequest;
use Modules\Brand\Http\Requests\UpdateBrandRequest;

class BrandController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('access_brands'), 403);
        $brands = Brand::all();
        return view('brand::brands.index', compact('brands'));
    }

    public function create()
    {
        abort_if(Gate::denies('create_brands'), 403);
        return view('brand::brands.create');
    }

    public function store(StoreBrandRequest $request)
    {
        Brand::create($request->all());
        toast('Brand Created!', 'success');
        return redirect()->route('brands.index');
    }

    public function edit(Brand $brand)
    {
        abort_if(Gate::denies('edit_brands'), 403);
        return view('brand::brands.edit', compact('brand'));
    }

    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $brand->update($request->all());
        toast('Brand Updated!', 'info');
        return redirect()->route('brands.index');
    }

    public function destroy(Brand $brand)
    {
        abort_if(Gate::denies('delete_brands'), 403);
        $brand->delete();
        toast('Brand Deleted!', 'warning');
        return redirect()->route('brands.index');
    }
}