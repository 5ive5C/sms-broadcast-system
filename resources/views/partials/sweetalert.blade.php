{{-- Flash-message toasts and a delegated confirm() replacement, both on
     SweetAlert2. Delegation matters here: action buttons for a DataTable row
     are injected by the ajax redraw, long after this listener registers, so
     binding to individual elements at page-load would miss them. --}}

@push('js')
<script>
    window._AdminLTE_Ready(() => {
        if (typeof window.Swal === 'undefined') {
            return;
        }

        @if(session('status'))
            Swal.fire({
                icon: 'success',
                title: @json(session('status')),
                toast: true,
                position: 'top-end',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: @json(session('error')),
                toast: true,
                position: 'top-end',
                timer: 4000,
                timerProgressBar: true,
                showConfirmButton: false,
            });
        @endif
    });

    document.addEventListener('submit', (e) => {
        const form = e.target.closest('form[data-confirm]');

        if (! form) {
            return;
        }

        e.preventDefault();

        if (typeof window.Swal === 'undefined') {
            if (confirm(form.dataset.confirm)) {
                form.submit();
            }

            return;
        }

        Swal.fire({
            title: form.dataset.confirm,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
            confirmButtonColor: 'var(--bs-danger)',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
@endpush
