@php($client = $client ?? null)

<div class="mb-3">
    <label for="name" class="form-label">Company Name <span class="text-danger">*</span></label>
    <input type="text" name="name" id="name"
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $client?->name) }}" autofocus required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="company_reg_no" class="form-label">Company Registration No.</label>
    <input type="text" name="company_reg_no" id="company_reg_no"
        class="form-control @error('company_reg_no') is-invalid @enderror"
        value="{{ old('company_reg_no', $client?->company_reg_no) }}">
    @error('company_reg_no')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
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
