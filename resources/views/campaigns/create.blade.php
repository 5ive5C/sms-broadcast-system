@extends('adminlte::page')

@section('title', 'New Campaign')

@section('content_header')
    <h1>New Campaign</h1>
@stop

@section('content')

    @include('partials.sweetalert')
    @include('campaigns.partials.steps', ['current' => 1])

    <form action="{{ route('campaigns.store') }}" method="post">
        @csrf

        <div class="row">
            <div class="col-lg-7 mb-4">
                <x-adminlte-card>
                    <x-slot name="titleSlot">Compose</x-slot>

                    <div class="mb-3">
                        <label for="name" class="form-label">Campaign name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name"
                            class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="template_id" class="form-label">Template</label>
                        <select id="template_id" name="template_id" class="form-select">
                            <option value="">None &mdash; write from scratch</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" data-body="{{ $template->body }}">
                                    {{ $template->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label for="content" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea name="content" id="content" rows="6" maxlength="1600" required
                            class="form-control @error('content') is-invalid @enderror">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between small text-body-secondary mb-3">
                        <span id="content-encoding">GSM 7-bit</span>
                        <span><span id="content-chars">0</span> characters &middot; <span id="content-parts">0</span> part(s)</span>
                    </div>

                    <div class="mb-3">
                        <label for="lane" class="form-label">Priority lane</label>
                        <select name="lane" id="lane" class="form-select">
                            <option value="bulk">bulk</option>
                            <option value="transactional">transactional</option>
                        </select>
                    </div>
                </x-adminlte-card>
            </div>

            <div class="col-lg-5 mb-4">
                <x-adminlte-card>
                    <x-slot name="titleSlot">Preview</x-slot>

                    <div class="border rounded-3 p-3 bg-body-tertiary">
                        <div class="d-inline-block px-3 py-2 rounded-3 bg-white border" style="max-width: 100%; word-break: break-word;">
                            <span id="content-preview" class="small">Your message will appear here&hellip;</span>
                        </div>
                    </div>
                </x-adminlte-card>
            </div>
        </div>

        <x-adminlte-card>
            <button type="submit" class="btn btn-primary">Continue to recipients</button>
            <a href="{{ route('campaigns.index') }}" class="btn btn-secondary">Cancel</a>
        </x-adminlte-card>
    </form>

@stop

@push('js')
<script>
    window._AdminLTE_Ready(() => {
        const contentEl = document.getElementById('content');
        const templateEl = document.getElementById('template_id');

        const GSM7 = "@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";

        function isGsm7(text) {
            for (const ch of text) {
                if (! GSM7.includes(ch)) return false;
            }
            return true;
        }

        function refresh() {
            const text = contentEl.value;
            const gsm7 = isGsm7(text);
            const len = text.length;
            const single = gsm7 ? 160 : 70;
            const multi = gsm7 ? 153 : 67;
            const parts = len === 0 ? 0 : (len <= single ? 1 : Math.ceil(len / multi));

            document.getElementById('content-encoding').textContent = gsm7 ? 'GSM 7-bit' : 'Unicode';
            document.getElementById('content-chars').textContent = len;
            document.getElementById('content-parts').textContent = parts;
            document.getElementById('content-preview').textContent = text || 'Your message will appear here…';
        }

        templateEl.addEventListener('change', () => {
            const option = templateEl.selectedOptions[0];
            if (! option || ! option.dataset.body) return;

            contentEl.value = option.dataset.body;
            refresh();
        });

        contentEl.addEventListener('input', refresh);
        refresh();
    });
</script>
@endpush
