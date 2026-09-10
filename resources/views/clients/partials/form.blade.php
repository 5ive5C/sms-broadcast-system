@php($client = $client ?? null)

<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text" name="name" id="name"
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $client?->name) }}" autofocus>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="slug" class="form-label">
        Slug <span class="text-body-secondary">(leave blank to auto-generate from name)</span>
    </label>
    <input type="text" name="slug" id="slug"
        class="form-control @error('slug') is-invalid @enderror"
        value="{{ old('slug', $client?->slug) }}">
    @error('slug')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="sender_id" class="form-label">Sender ID</label>
    <input type="text" name="sender_id" id="sender_id"
        class="form-control @error('sender_id') is-invalid @enderror"
        value="{{ old('sender_id', $client?->sender_id) }}">
    @error('sender_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
            @foreach(['active' => 'Active', 'suspended' => 'Suspended', 'inactive' => 'Inactive'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $client?->status ?? 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="pricing_tier_id" class="form-label">Pricing Tier</label>
        <select name="pricing_tier_id" id="pricing_tier_id" class="form-select @error('pricing_tier_id') is-invalid @enderror">
            <option value="">None</option>
            @foreach($pricingTiers as $id => $name)
                <option value="{{ $id }}" @selected((int) old('pricing_tier_id', $client?->pricing_tier_id) === $id)>{{ $name }}</option>
            @endforeach
        </select>
        @error('pricing_tier_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
