@php($template = $template ?? null)

<div class="mb-3">
    <label for="name" class="form-label">Template name <span class="text-danger">*</span></label>
    <input type="text" name="name" id="name"
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $template?->name) }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-2">
    <label for="body" class="form-label">Message body <span class="text-danger">*</span></label>
    <textarea name="body" id="body" rows="5" maxlength="1600" required
        class="form-control @error('body') is-invalid @enderror"
        placeholder="Hi {name}, your payment of RM{amount} is due on {due_date}.">{{ old('body', $template?->body) }}</textarea>
    <div class="form-text">Wrap merge fields in curly braces, e.g. <code>{name}</code>, <code>{amount}</code>.</div>
    @error('body')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="d-flex justify-content-between small text-body-secondary mb-3">
    <span id="body-encoding">GSM 7-bit</span>
    <span><span id="body-chars">0</span> characters &middot; <span id="body-parts">1</span> part(s)</span>
</div>

<div class="mb-3">
    <span class="small fw-semibold text-body-secondary">Placeholders detected:</span>
    <span id="body-placeholders" class="small"></span>
</div>

@push('js')
<script>
    window._AdminLTE_Ready(() => {
        const bodyEl = document.getElementById('body');
        if (! bodyEl) return;

        const GSM7 = "@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";

        function isGsm7(text) {
            for (const ch of text) {
                if (! GSM7.includes(ch)) return false;
            }
            return true;
        }

        function refresh() {
            const text = bodyEl.value;
            const gsm7 = isGsm7(text);
            const len = text.length;
            const single = gsm7 ? 160 : 70;
            const multi = gsm7 ? 153 : 67;
            const parts = len === 0 ? 0 : (len <= single ? 1 : Math.ceil(len / multi));

            document.getElementById('body-encoding').textContent = gsm7 ? 'GSM 7-bit' : 'Unicode';
            document.getElementById('body-chars').textContent = len;
            document.getElementById('body-parts').textContent = parts;

            const placeholders = [...new Set((text.match(/\{(\w+)\}/g) || []))];
            document.getElementById('body-placeholders').textContent = placeholders.length ? placeholders.join(' ') : 'none';
        }

        bodyEl.addEventListener('input', refresh);
        refresh();
    });
</script>
@endpush
