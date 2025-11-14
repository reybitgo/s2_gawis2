@props(['perPage' => 15, 'options' => [10, 15, 25, 50, 100]])

<div class="d-flex align-items-center gap-2">
    <label for="per-page-select" class="text-body-secondary small mb-0">Show</label>
    <select
        id="per-page-select"
        name="per_page"
        class="form-select form-select-sm"
        style="width: auto;"
        onchange="updatePerPage(this.value)"
    >
        @foreach($options as $option)
            <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>
                {{ $option }}
            </option>
        @endforeach
    </select>
    <span class="text-body-secondary small">entries</span>
</div>

@push('scripts')
<script>
function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', '1'); // Reset to first page when changing per_page
    window.location.href = url.toString();
}
</script>
@endpush
