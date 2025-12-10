@php
    $brand_max_id = \Modules\Product\Entities\Brand::max('id') + 1;
    $brand_code = "BR_" . str_pad($brand_max_id, 2, '0', STR_PAD_LEFT)
@endphp
<div class="modal fade" id="brandCreateModal" tabindex="-1" role="dialog" aria-labelledby="brandCreateModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="brandCreateModalLabel">Create Brand</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('product-brands.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="form-group">
                        <label for="brand_code">Brand Code <span class="text-danger">*</span></label>
                        <input class="form-control @error('brand_code') is-invalid @enderror" type="text" id="brand_code" name="brand_code" required value="{{ old('brand_code', $brand_code) }}">
                        @error('brand_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="brand_name">Brand Name <span class="text-danger">*</span></label>
                        <input class="form-control @error('brand_name') is-invalid @enderror" type="text" id="brand_name" name="brand_name" required value="{{ old('brand_name') }}">
                        @error('brand_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Create <i class="bi bi-check"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>
