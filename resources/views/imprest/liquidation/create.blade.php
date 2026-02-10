@extends('layouts.main')

@section('title', 'Create Liquidation')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Imprest Management', 'url' => route('imprest.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Request Details', 'url' => route('imprest.requests.show', $imprestRequest->id), 'icon' => 'bx bx-file'],
            ['label' => 'Create Liquidation', 'url' => '#', 'icon' => 'bx bx-receipt']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 text-primary">Create Liquidation for {{ $imprestRequest->request_number }}</h5>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Imprest Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Request Number:</strong><br>
                        {{ $imprestRequest->request_number }}
                    </div>
                    <div class="col-md-3">
                        <strong>Employee:</strong><br>
                        {{ $imprestRequest->employee->name ?? 'N/A' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Disbursed Amount:</strong><br>
                        {{ number_format($imprestRequest->disbursement->amount_issued ?? 0, 2) }}
                    </div>
                    <div class="col-md-3">
                        <strong>Purpose:</strong><br>
                        {{ $imprestRequest->purpose }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Liquidation Form</h6>
            </div>
            <div class="card-body">
                <form id="liquidationForm">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="liquidation_date" class="form-label">Liquidation Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="liquidation_date" name="liquidation_date"
                                   value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-8">
                            <label for="liquidation_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="liquidation_notes" name="liquidation_notes" rows="2"></textarea>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Expense Items</h6>
                        <button type="button" class="btn btn-primary btn-sm" id="addItemBtn">
                            <i class="bx bx-plus me-1"></i> Add Expense Item
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Chart Account</th>
                                    <th>Date</th>
                                    <th>Receipt #</th>
                                    <th>Supplier</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Total:</strong></td>
                                    <td><strong id="totalAmount">0.00</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Disbursed Amount:</strong></td>
                                    <td><strong>{{ number_format($imprestRequest->disbursement->amount_issued ?? 0, 2) }}</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Balance to Return:</strong></td>
                                    <td><strong id="balanceReturn">{{ number_format($imprestRequest->disbursement->amount_issued ?? 0, 2) }}</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('imprest.requests.show', $imprestRequest->id) }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-check me-1"></i> Submit Liquidation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Expense Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Expense Category <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_category" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Chart Account <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_chart_account" required>
                            <option value="">-- Select Account --</option>
                            @foreach($expenseAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="modal_description" rows="2" required></textarea>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Expense Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="modal_expense_date" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Receipt Number</label>
                        <input type="text" class="form-control" id="modal_receipt">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" class="form-control" id="modal_supplier">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="modal_amount" step="0.01" min="0.01" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveItemBtn">Add Item</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let itemIndex = 0;
    const disbursedAmount = {{ $imprestRequest->disbursement->amount_issued ?? 0 }};
    const modal = new bootstrap.Modal(document.getElementById('itemModal'));

    $('#addItemBtn').on('click', function() {
        clearModal();
        modal.show();
    });

    $('#saveItemBtn').on('click', function() {
        const category = $('#modal_category').val();
        const description = $('#modal_description').val();
        const chartAccountId = $('#modal_chart_account').val();
        const chartAccountText = $('#modal_chart_account option:selected').text();
        const expenseDate = $('#modal_expense_date').val();
        const receipt = $('#modal_receipt').val();
        const supplier = $('#modal_supplier').val();
        const amount = parseFloat($('#modal_amount').val()) || 0;

        if (!category || !description || !chartAccountId || !expenseDate || amount <= 0) {
            alert('Please fill all required fields');
            return;
        }

        const row = `
            <tr data-index="${itemIndex}">
                <td>${category}<input type="hidden" name="items[${itemIndex}][expense_category]" value="${category}"></td>
                <td>${description}<input type="hidden" name="items[${itemIndex}][description]" value="${description}"></td>
                <td>${chartAccountText}<input type="hidden" name="items[${itemIndex}][chart_account_id]" value="${chartAccountId}"></td>
                <td>${expenseDate}<input type="hidden" name="items[${itemIndex}][expense_date]" value="${expenseDate}"></td>
                <td>${receipt || '-'}<input type="hidden" name="items[${itemIndex}][receipt_number]" value="${receipt}"></td>
                <td>${supplier || '-'}<input type="hidden" name="items[${itemIndex}][supplier_name]" value="${supplier}"></td>
                <td class="item-amount">${formatCurrency(amount)}<input type="hidden" name="items[${itemIndex}][amount]" value="${amount}"></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="bx bx-trash"></i></button></td>
            </tr>
        `;

        $('#itemsBody').append(row);
        itemIndex++;
        updateTotals();
        modal.hide();
    });

    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        updateTotals();
    });

    function clearModal() {
        $('#modal_category').val('');
        $('#modal_description').val('');
        $('#modal_chart_account').val('');
        $('#modal_expense_date').val('{{ date("Y-m-d") }}');
        $('#modal_receipt').val('');
        $('#modal_supplier').val('');
        $('#modal_amount').val('');
    }

    function updateTotals() {
        let total = 0;
        $('#itemsBody tr').each(function() {
            const amountInput = $(this).find('input[name$="[amount]"]').val();
            total += parseFloat(amountInput) || 0;
        });

        $('#totalAmount').text(formatCurrency(total));
        $('#balanceReturn').text(formatCurrency(disbursedAmount - total));
    }

    function formatCurrency(value) {
        return parseFloat(value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    $('#liquidationForm').on('submit', function(e) {
        e.preventDefault();

        if ($('#itemsBody tr').length === 0) {
            alert('Please add at least one expense item');
            return;
        }

        const formData = $(this).serialize();

        $.ajax({
            url: '{{ route("imprest.liquidation.store", $imprestRequest->id) }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.success,
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'An error occurred';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error,
                });
            }
        });
    });
});
</script>
@endpush
