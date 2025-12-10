<a href="{{ route('product-brands.edit', $data->id) }}" class="btn btn-info btn-sm">
    <i class="bi bi-pencil"></i>
</a>
<form id="destroy{{ $data->id }}" class="d-inline" action="{{ route('product-brands.destroy', $data->id) }}" method="POST">
    @csrf
    @method('delete')
    <button type="button" class="btn btn-danger btn-sm" onclick="
        event.preventDefault();
        if (confirm('Are you sure? It will delete the data permanently!')) {
            this.closest('form').submit();
        }
        ">
        <i class="bi bi-trash"></i>
    </button>
</form>
