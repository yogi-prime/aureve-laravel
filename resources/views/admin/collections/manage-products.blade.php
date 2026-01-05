@extends('layouts.admin')

@section('title', 'Manage Products - ' . $collection->name)
@section('page-title', 'Manage Products: ' . $collection->name)

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <form action="{{ route('admin.collections.update-products', $collection) }}" method="POST">
        @csrf

        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Select Products for this Collection</h3>
                    <p class="text-sm text-gray-500 mt-1">Currently {{ count($selectedProducts) }} products selected</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="button" onclick="selectAll()" class="text-sm text-indigo-600 hover:text-indigo-800">
                        Select All
                    </button>
                    <span class="text-gray-300">|</span>
                    <button type="button" onclick="deselectAll()" class="text-sm text-gray-600 hover:text-gray-800">
                        Deselect All
                    </button>
                </div>
            </div>

            <!-- Search Filter -->
            <div class="mb-4">
                <input type="text" id="productSearch" placeholder="Search products..."
                       class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                       onkeyup="filterProducts()">
            </div>

            <!-- Products Grid -->
            <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-md p-4" id="productsList">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($products as $product)
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer product-item"
                               data-name="{{ strtolower($product->name) }}">
                            <input type="checkbox" name="products[]" value="{{ $product->id }}"
                                   class="product-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                   {{ in_array($product->id, $selectedProducts) ? 'checked' : '' }}>
                            <div class="ml-3 flex-1">
                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                <div class="text-xs text-gray-500">SKU: {{ $product->sku }} | Rs. {{ number_format($product->base_price, 2) }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 text-sm text-gray-500">
                <span id="selectedCount">{{ count($selectedProducts) }}</span> of {{ count($products) }} products selected
            </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.collections.index') }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">
                Update Products
            </button>
        </div>
    </form>
</div>

<script>
function selectAll() {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = true);
    updateCount();
}

function deselectAll() {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
    updateCount();
}

function filterProducts() {
    const search = document.getElementById('productSearch').value.toLowerCase();
    document.querySelectorAll('.product-item').forEach(item => {
        const name = item.getAttribute('data-name');
        item.style.display = name.includes(search) ? 'flex' : 'none';
    });
}

function updateCount() {
    const count = document.querySelectorAll('.product-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count;
}

// Update count on checkbox change
document.querySelectorAll('.product-checkbox').forEach(cb => {
    cb.addEventListener('change', updateCount);
});
</script>
@endsection
