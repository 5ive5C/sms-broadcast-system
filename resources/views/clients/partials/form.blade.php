@php($client = $client ?? null)
@php($pricingTiers = $pricingTiers ?? \App\Models\PricingTier::query()->active()->orderBy('min_credits')->get())

<div class="mb-3">
    <label for="name" class="form-label">Legal Name <span class="text-danger">*</span></label>
    <input type="text" name="name" id="name"
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $client?->name) }}" autofocus required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="company_reg_no" class="form-label">Registration No.</label>
        <input type="text" name="company_reg_no" id="company_reg_no"
            class="form-control @error('company_reg_no') is-invalid @enderror"
            value="{{ old('company_reg_no', $client?->company_reg_no) }}">
        @error('company_reg_no')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="industry" class="form-label">Industry</label>
        <input type="text" name="industry" id="industry"
            class="form-control @error('industry') is-invalid @enderror"
            value="{{ old('industry', $client?->industry) }}" placeholder="e.g. Banking, Healthcare">
        @error('industry')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="address" class="form-label">Address</label>
    <textarea name="address" id="address" rows="3"
        class="form-control @error('address') is-invalid @enderror">{{ old('address', $client?->address) }}</textarea>
    @error('address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<hr class="my-4">
<h6 class="fw-bold mb-3">Messaging &amp; Access</h6>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="pricing_tier_id" class="form-label">Pricing Tier</label>
        <select name="pricing_tier_id" id="pricing_tier_id" class="form-select @error('pricing_tier_id') is-invalid @enderror">
            <option value="">Select a tier&hellip;</option>
            @foreach($pricingTiers as $tier)
                <option value="{{ $tier->id }}" @selected((int) old('pricing_tier_id', $client?->pricing_tier_id) === $tier->id)>
                    {{ number_format($tier->min_credits) }} credits &mdash; RM{{ number_format($tier->price_per_credit, 3) }}
                </option>
            @endforeach
        </select>
        @error('pricing_tier_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label d-block">Message Types</label>
        @php($selectedTypes = old('message_types', $client?->message_types ?? []))
        @foreach(['tac' => 'TAC / OTP', 'transactional' => 'Transactional', 'bulk' => 'Bulk'] as $value => $label)
            <div class="form-check form-check-inline">
                <input type="checkbox" name="message_types[]" value="{{ $value }}" id="message_type_{{ $value }}"
                    class="form-check-input" @checked(in_array($value, $selectedTypes, true))>
                <label for="message_type_{{ $value }}" class="form-check-label">{{ $label }}</label>
            </div>
        @endforeach
        @error('message_types')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="two_factor_required" class="form-label">Two-Factor</label>
        <select name="two_factor_required" id="two_factor_required" class="form-select @error('two_factor_required') is-invalid @enderror">
            <option value="1" @selected((string) old('two_factor_required', $client?->two_factor_required ? '1' : '0') === '1')>Required</option>
            <option value="0" @selected((string) old('two_factor_required', $client?->two_factor_required ? '1' : '0') === '0')>Optional</option>
        </select>
        @error('two_factor_required')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@unless($client)
    <hr class="my-4">
    <h6 class="fw-bold mb-3">First Admin</h6>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="admin_name" class="form-label">First Admin &mdash; Name <span class="text-danger">*</span></label>
            <input type="text" name="admin_name" id="admin_name"
                class="form-control @error('admin_name') is-invalid @enderror"
                value="{{ old('admin_name') }}" required>
            @error('admin_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="admin_email" class="form-label">First Admin &mdash; Email <span class="text-danger">*</span></label>
            <input type="email" name="admin_email" id="admin_email"
                class="form-control @error('admin_email') is-invalid @enderror"
                value="{{ old('admin_email') }}" required>
            @error('admin_email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@endunless

<hr class="my-4">
<h6 class="fw-bold mb-3">Person In Charge (PIC)</h6>

<div class="mb-3">
    <label for="pic_name" class="form-label">PIC Name <span class="text-danger">*</span></label>
    <input type="text" name="pic_name" id="pic_name"
        class="form-control @error('pic_name') is-invalid @enderror"
        value="{{ old('pic_name', $client?->pic_name) }}" required>
    @error('pic_name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="pic_phone" class="form-label">PIC Phone No. <span class="text-danger">*</span></label>
        <input type="text" name="pic_phone" id="pic_phone"
            class="form-control @error('pic_phone') is-invalid @enderror"
            value="{{ old('pic_phone', $client?->pic_phone) }}" required>
        @error('pic_phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="pic_email" class="form-label">PIC Email <span class="text-danger">*</span></label>
        <input type="email" name="pic_email" id="pic_email"
            class="form-control @error('pic_email') is-invalid @enderror"
            value="{{ old('pic_email', $client?->pic_email) }}" required>
        @error('pic_email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
