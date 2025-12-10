<a href="{{ route('brands.edit', $data->id) }}" class="btn btn-info btn-sm">
    <i class="bi bi-pencil"></i>
</a>
<button id="delete-{{ $data->id }}" class="btn btn-danger btn-sm" onclick="if(confirm('Are you sure you want to delete this brand?')) { document.getElementById('destroy{{ $data->id }}').submit(); }">
    <i class="bi bi-trash"></i>
</button>
<form id="destroy{{ $data->id }}" class="d-none" action="{{ route('brands.destroy', $data->id) }}" method="POST">
    @csrf
    @method('delete')
</form>