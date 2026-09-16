@extends('adminlte::page')

@section('title', 'Quick Send')

@section('content_header')
    <h1>Quick Send</h1>
@stop

@section('content')

    @include('partials.sweetalert')

    <form action="{{ route('quick-send.store') }}" method="post" enctype="multipart/form-data" id="quick-send-form">
        @csrf

        <div class="row">
            {{-- Recipients --}}
            <div class="col-lg-5 mb-4">
                <x-adminlte-card icon="bi bi-people">
                    <x-slot name="titleSlot">Recipients</x-slot>

                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-paste-btn" data-bs-toggle="tab" data-bs-target="#tab-paste" type="button" role="tab">
                                <i class="bi bi-clipboard me-1"></i> Paste Numbers
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-upload-btn" data-bs-toggle="tab" data-bs-target="#tab-upload" type="button" role="tab">
                                <i class="bi bi-upload me-1"></i> Upload File
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-paste" role="tabpanel">
                            <textarea name="recipients_text" id="recipients_text" rows="6"
                                class="form-control @error('recipients_text') is-invalid @enderror"
                                placeholder="One number per line, or comma-separated&#10;e.g.&#10;+60123456781&#10;+60198765404">{{ old('recipients_text') }}</textarea>
                            @error('recipients_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="tab-pane fade" id="tab-upload" role="tabpanel">
                            <label for="recipients_file"
                                class="d-flex flex-column align-items-center justify-content-center text-center border border-2 border-dashed rounded-3 p-4"
                                style="cursor: pointer; min-height: 150px;" id="dropzone">
                                <i class="bi bi-file-earmark-arrow-up fs-1 text-primary mb-2"></i>
                                <span class="fw-semibold">Click to choose a file</span>
                                <span class="small text-body-secondary">.txt or .csv &middot; max 5&nbsp;MB</span>
                                <span class="small mt-2 fw-semibold text-primary d-none" id="file-chosen-name"></span>
                            </label>
                            <input type="file" name="recipients_file" id="recipients_file" accept=".txt,.csv" class="d-none @error('recipients_file') is-invalid @enderror">
                            @error('recipients_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-3 p-3 rounded-3 bg-body-tertiary">
                        <span class="fw-semibold"><i class="bi bi-person-check me-1"></i> Valid numbers detected</span>
                        <span class="badge text-bg-primary fs-6" id="recipient-count">0</span>
                    </div>
                </x-adminlte-card>
            </div>

            {{-- Message content --}}
            <div class="col-lg-7 mb-4">
                <x-adminlte-card icon="bi bi-chat-left-text">
                    <x-slot name="titleSlot">Message</x-slot>

                    <div class="mb-2">
                        <label for="content" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea name="content" id="content" rows="5" maxlength="1600" required
                            class="form-control @error('content') is-invalid @enderror"
                            placeholder="Type your message here...">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between small text-body-secondary mb-3">
                        <span id="content-encoding">GSM 7-bit</span>
                        <span><span id="content-chars">0</span> characters &middot; <span id="content-segments">0</span> part(s)</span>
                    </div>

                    <div class="border rounded-3 p-3 bg-body-tertiary">
                        <div class="small fw-semibold text-body-secondary mb-2">
                            <i class="bi bi-phone me-1"></i> Preview
                        </div>
                        <div class="d-inline-block px-3 py-2 rounded-4 bg-primary text-white" style="max-width: 100%; word-break: break-word; border-bottom-left-radius: 0.25rem !important;">
                            <span id="content-preview" class="small">Your message will appear here&hellip;</span>
                        </div>
                    </div>
                </x-adminlte-card>
            </div>
        </div>

        <x-adminlte-card>
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex gap-4">
                    <div>
                        <div class="text-body-secondary small">Recipients</div>
                        <div class="fs-4 fw-bold" id="summary-recipients">0</div>
                    </div>
                    <div>
                        <div class="text-body-secondary small">Credits required</div>
                        <div class="fs-4 fw-bold text-primary" id="summary-credits">0</div>
                    </div>
                    <div>
                        <div class="text-body-secondary small">Priority lane</div>
                        <div class="fs-4 fw-bold">transactional</div>
                    </div>
                    <div>
                        <div class="text-body-secondary small">Balance after send</div>
                        <div class="fs-4 fw-bold" id="summary-balance">{{ number_format($wallet->balance) }}</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg d-inline-flex align-items-center gap-2">
                    <i class="bi bi-send"></i> Send Now
                </button>
            </div>
        </x-adminlte-card>
    </form>

@stop

@push('css')
<style>
    .border-dashed { border-style: dashed !important; }
    #dropzone:hover { background-color: var(--bs-tertiary-bg); }
</style>
@endpush

@push('js')
<script>
    window._AdminLTE_Ready(() => {
        const contentEl = document.getElementById('content');
        const pasteEl = document.getElementById('recipients_text');
        const fileEl = document.getElementById('recipients_file');
        const fileNameEl = document.getElementById('file-chosen-name');
        const balance = {{ (float) $wallet->balance }};

        const GSM7 = "@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";

        function isGsm7(text) {
            for (const ch of text) {
                if (! GSM7.includes(ch)) return false;
            }
            return true;
        }

        function updateContentStats() {
            const text = contentEl.value;
            const gsm7 = isGsm7(text);
            const len = text.length;

            const single = gsm7 ? 160 : 70;
            const multi = gsm7 ? 153 : 67;
            const parts = len === 0 ? 0 : (len <= single ? 1 : Math.ceil(len / multi));

            document.getElementById('content-encoding').textContent = gsm7 ? 'GSM 7-bit' : 'Unicode';
            document.getElementById('content-chars').textContent = len;
            document.getElementById('content-segments').textContent = parts;
            document.getElementById('content-preview').textContent = text || 'Your message will appear here…';

            updateCredits();
        }

        function countPastedNumbers(text) {
            const tokens = text.split(/[\r\n,;]+/).map((t) => t.replace(/[^\d+]/g, '')).filter((t) => /^\+?\d{8,15}$/.test(t));
            return new Set(tokens).size;
        }

        function updateCredits() {
            const recipients = parseInt(document.getElementById('recipient-count').textContent, 10) || 0;
            const parts = parseInt(document.getElementById('content-segments').textContent, 10) || 0;
            const credits = recipients * Math.max(parts, 1);

            document.getElementById('summary-recipients').textContent = recipients.toLocaleString();
            document.getElementById('summary-credits').textContent = credits.toLocaleString();
            document.getElementById('summary-balance').textContent = Math.max(balance - credits, 0).toLocaleString();
        }

        function updateRecipientCount() {
            let count = 0;

            if (fileEl.files.length > 0) {
                count = fileEl.dataset.count ? parseInt(fileEl.dataset.count, 10) : 0;
            } else {
                count = countPastedNumbers(pasteEl.value);
            }

            document.getElementById('recipient-count').textContent = count.toLocaleString();
            updateCredits();
        }

        contentEl.addEventListener('input', updateContentStats);
        pasteEl.addEventListener('input', updateRecipientCount);

        fileEl.addEventListener('change', () => {
            const file = fileEl.files[0];

            if (! file) {
                fileNameEl.classList.add('d-none');
                fileEl.dataset.count = 0;
                updateRecipientCount();
                return;
            }

            fileNameEl.textContent = file.name;
            fileNameEl.classList.remove('d-none');

            const reader = new FileReader();
            reader.onload = () => {
                fileEl.dataset.count = countPastedNumbers(String(reader.result));
                updateRecipientCount();
            };
            reader.readAsText(file);
        });

        updateContentStats();
        updateRecipientCount();
    });
</script>
@endpush
