@php
$isEdit = isset($customer);
@endphp

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bx bx-error-circle me-2"></i>
    Please fix the following errors:
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<form action="{{ $isEdit ? route('customers.update', $customer) : route('customers.store') }}"
      method="POST" enctype="multipart/form-data" id="customerForm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <!-- Personal Information Section -->
    <div class="row">
        <div class="col-12">
            <h6 class="mb-3 text-primary border-bottom pb-2">
                <i class="bx bx-user me-2"></i>Personal Information
            </h6>
        </div>

        <!-- Full Name -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $customer->name ?? '') }}" placeholder="Enter full name" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Date of Birth -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
            <input type="date" name="dob" id="dob" class="form-control @error('dob') is-invalid @enderror"
                value="{{ old('dob', isset($customer) && $customer->dob ? \Carbon\Carbon::parse($customer->dob)->format('Y-m-d') : '') }}"
                max="{{ \Carbon\Carbon::now()->subYears(18)->format('Y-m-d') }}" required>
            <small class="form-text text-muted">Must be 18 years or older</small>
            @error('dob') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Sex -->
        <div class="col-md-6 mb-3">
            <label for="sex" class="form-label">Sex <span class="text-danger">*</span></label>
            <select name="sex" id="sex" class="form-select @error('sex') is-invalid @enderror" required>
                <option value="">-- Select Sex --</option>
                <option value="M" {{ old('sex', $customer->sex ?? '') == 'M' ? 'selected' : '' }}>Male</option>
                <option value="F" {{ old('sex', $customer->sex ?? '') == 'F' ? 'selected' : '' }}>Female</option>
            </select>
            @error('sex') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Marital Status -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Marital Status</label>
            <select name="marital_status" class="form-select @error('marital_status') is-invalid @enderror">
                <option value="">-- Select Status --</option>
                <option value="Single" {{ old('marital_status', $customer->marital_status ?? '') == 'Single' ? 'selected' : '' }}>Single</option>
                <option value="Married" {{ old('marital_status', $customer->marital_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
                <option value="Divorced" {{ old('marital_status', $customer->marital_status ?? '') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                <option value="Widowed" {{ old('marital_status', $customer->marital_status ?? '') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
            </select>
            @error('marital_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Reference -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Reference</label>
            <input type="text" name="reference" class="form-control @error('reference') is-invalid @enderror"
                value="{{ old('reference', $customer->reference ?? '') }}" placeholder="Enter reference (optional)">
            @error('reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Communication Section -->
    <div class="row mt-3">
        <div class="col-12">
            <h6 class="mb-3 text-primary border-bottom pb-2">
                <i class="bx bx-phone me-2"></i>Communication
            </h6>
        </div>

        <!-- Phone 1 -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">255</span>
                <input type="text" name="phone1" id="phone1" class="form-control @error('phone1') is-invalid @enderror"
                    value="{{ old('phone1', isset($customer->phone1) ? preg_replace('/^(\+?255)/', '', $customer->phone1) : '') }}" 
                    placeholder="712345678" maxlength="9" pattern="[0-9]{9}" required>
            </div>
            <small class="form-text text-muted">Enter 9 digits (e.g., 712345678)</small>
            @error('phone1') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Phone 2 -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Alternative Phone Number</label>
            <div class="input-group">
                <span class="input-group-text">255</span>
                <input type="text" name="phone2" id="phone2" class="form-control @error('phone2') is-invalid @enderror"
                    value="{{ old('phone2', isset($customer->phone2) ? preg_replace('/^(\+?255)/', '', $customer->phone2) : '') }}" 
                    placeholder="712345678" maxlength="9" pattern="[0-9]{9}">
            </div>
            <small class="form-text text-muted">Optional - Enter 9 digits</small>
            @error('phone2') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Email -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $customer->email ?? '') }}" placeholder="example@email.com">
            <small class="form-text text-muted">Optional</small>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Work and Identification Section -->
    <div class="row mt-3">
        <div class="col-12">
            <h6 class="mb-3 text-primary border-bottom pb-2">
                <i class="bx bx-briefcase me-2"></i>Work and Identification
            </h6>
        </div>

        <!-- Employment Status -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Employment Status</label>
            <select name="employment_status" class="form-select @error('employment_status') is-invalid @enderror">
                <option value="">-- Select Status --</option>
                <option value="Employed" {{ old('employment_status', $customer->employment_status ?? '') == 'Employed' ? 'selected' : '' }}>Employed</option>
                <option value="Self Employed" {{ old('employment_status', $customer->employment_status ?? '') == 'Self Employed' ? 'selected' : '' }}>Self Employed</option>
                <option value="Unemployed" {{ old('employment_status', $customer->employment_status ?? '') == 'Unemployed' ? 'selected' : '' }}>Unemployed</option>
                <option value="Student" {{ old('employment_status', $customer->employment_status ?? '') == 'Student' ? 'selected' : '' }}>Student</option>
                <option value="Retired" {{ old('employment_status', $customer->employment_status ?? '') == 'Retired' ? 'selected' : '' }}>Retired</option>
            </select>
            @error('employment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Work/Business Name -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Work/Business Name</label>
            <input type="text" name="work" class="form-control @error('work') is-invalid @enderror"
                value="{{ old('work', $customer->work ?? '') }}" placeholder="e.g. ABC Company, Own Business">
            @error('work') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Work/Business Address -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Work/Business Address</label>
            <input type="text" name="workAddress" class="form-control @error('workAddress') is-invalid @enderror"
                value="{{ old('workAddress', $customer->workAddress ?? '') }}" placeholder="e.g. Kariakoo, Dar es Salaam">
            @error('workAddress') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- ID Type -->
        <div class="col-md-6 mb-3">
            <label class="form-label">ID Type</label>
            <select name="idType" class="form-select @error('idType') is-invalid @enderror">
                <option value="">-- Select ID Type --</option>
                @foreach(['National ID', 'License', 'Voter Registration', 'Passport', 'Other'] as $type)
                <option value="{{ $type }}" {{ old('idType', $customer->idType ?? '') == $type ? 'selected' : '' }}>
                    {{ $type }}
                </option>
                @endforeach
            </select>
            @error('idType') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- ID Number -->
        <div class="col-md-6 mb-3">
            <label class="form-label">ID Number</label>
            <input type="text" name="idNumber" class="form-control @error('idNumber') is-invalid @enderror"
                value="{{ old('idNumber', $customer->idNumber ?? '') }}" placeholder="Enter ID number">
            @error('idNumber') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Address Section -->
    <div class="row mt-3">
        <div class="col-12">
            <h6 class="mb-3 text-primary border-bottom pb-2">
                <i class="bx bx-map me-2"></i>Address
            </h6>
        </div>

        <!-- Region -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Region <span class="text-danger">*</span></label>
            <select name="region_id" id="region" class="form-select select2-single @error('region_id') is-invalid @enderror" required>
                <option value="">-- Select Region --</option>
                @foreach($regions as $region)
                <option value="{{ $region->id }}" {{ old('region_id', $customer->region_id ?? '') == $region->id ? 'selected' : '' }}>
                    {{ $region->name }}
                </option>
                @endforeach
            </select>
            @error('region_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- District -->
        <div class="col-md-6 mb-3">
            <label class="form-label">District <span class="text-danger">*</span></label>
            <select name="district_id" id="district" class="form-select @error('district_id') is-invalid @enderror" required>
                <option value="">-- Select District --</option>
                @if($isEdit && $customer->district_id)
                <option value="{{ $customer->district_id }}" selected>
                    {{ $customer->district->name ?? 'Selected District' }}
                </option>
                @elseif(old('district_id'))
                <option value="{{ old('district_id') }}" selected>
                    {{ \App\Models\District::find(old('district_id'))->name ?? 'Selected District' }}
                </option>
                @endif
            </select>
            @error('district_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Street/Address -->
        <div class="col-md-12 mb-3">
            <label class="form-label">Street/Address</label>
            <input type="text" name="street" class="form-control @error('street') is-invalid @enderror"
                value="{{ old('street', $customer->street ?? '') }}" placeholder="e.g. Mkwawa Street, Block A, House No. 123">
            @error('street') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Others Section -->
    <div class="row mt-3">
        <div class="col-12">
            <h6 class="mb-3 text-primary border-bottom pb-2">
                <i class="bx bx-list-ul me-2"></i>Other Information
            </h6>
        </div>

        <!-- Category -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Category <span class="text-danger">*</span></label>
            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                <option value="">-- Select Category --</option>
                <option value="Member" {{ old('category', $customer->category ?? 'Member') == 'Member' ? 'selected' : '' }}>Member</option>
                <option value="Borrower" {{ old('category', $customer->category ?? '') == 'Borrower' ? 'selected' : '' }}>Borrower</option>
                <option value="Guarantor" {{ old('category', $customer->category ?? '') == 'Guarantor' ? 'selected' : '' }}>Guarantor</option>
            </select>
            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Relation -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Relation</label>
            <input type="text" name="relation" class="form-control @error('relation') is-invalid @enderror"
                value="{{ old('relation', $customer->relation ?? '') }}" placeholder="e.g. Spouse, Parent, Friend">
            @error('relation') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Number of Spouse -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Number of Spouse</label>
            <input type="number" name="number_of_spouse" class="form-control @error('number_of_spouse') is-invalid @enderror"
                value="{{ old('number_of_spouse', $customer->number_of_spouse ?? '') }}" placeholder="0" min="0" max="10">
            @error('number_of_spouse') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Number of Children -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Number of Children</label>
            <input type="number" name="number_of_children" class="form-control @error('number_of_children') is-invalid @enderror"
                value="{{ old('number_of_children', $customer->number_of_children ?? '') }}" placeholder="0" min="0" max="50">
            @error('number_of_children') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Description -->
        <div class="col-md-12 mb-3">
            <label class="form-label">Description/Notes</label>
            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                      rows="3" placeholder="Any additional notes about the customer">{{ old('description', $customer->description ?? '') }}</textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Photo Upload -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Photo</label>
            <input type="file" name="photo" accept="image/*" class="form-control @error('photo') is-invalid @enderror" onchange="previewImage(event)">
            @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div id="preview" class="mt-2">
                @if($isEdit && $customer->photo)
                <img src="{{ asset('storage/'.$customer->photo) }}" width="100" class="rounded">
                @endif
            </div>
        </div>

        @if($isEdit)
        <!-- Password (only for edit) -->
        <div class="col-md-6 mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                placeholder="Leave blank to keep current password">
            <small class="form-text text-muted">Only fill if you want to change password</small>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        @endif
    </div>

    <!-- Financial Status Section -->
    <div class="row mt-3">
        <div class="col-12">
            <h6 class="mb-3 text-primary border-bottom pb-2">
                <i class="bx bx-dollar-circle me-2"></i>Financial Status
            </h6>
        </div>

        <!-- Monthly Income -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Monthly Income (TZS)</label>
            <input type="number" name="monthly_income" class="form-control @error('monthly_income') is-invalid @enderror"
                value="{{ old('monthly_income', $customer->monthly_income ?? '') }}" placeholder="0.00" step="0.01" min="0">
            <small class="form-text text-muted">Optional</small>
            @error('monthly_income') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Monthly Expenses -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Monthly Expenses (TZS)</label>
            <input type="number" name="monthly_expenses" class="form-control @error('monthly_expenses') is-invalid @enderror"
                value="{{ old('monthly_expenses', $customer->monthly_expenses ?? '') }}" placeholder="0.00" step="0.01" min="0">
            <small class="form-text text-muted">Optional</small>
            @error('monthly_expenses') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Bank Information Section -->
    <div class="row mt-3">
        <div class="col-12">
            <h6 class="mb-3 text-primary border-bottom pb-2">
                <i class="bx bx-credit-card me-2"></i>Bank Information
            </h6>
        </div>

        <!-- Bank Name -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Bank Name</label>
            <select name="bank_name" class="form-select select2-single @error('bank_name') is-invalid @enderror">
                <option value="">-- Select Bank --</option>
                <option value="CRDB Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'CRDB Bank' ? 'selected' : '' }}>CRDB Bank</option>
                <option value="NMB Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'NMB Bank' ? 'selected' : '' }}>NMB Bank</option>
                <option value="NBC Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'NBC Bank' ? 'selected' : '' }}>NBC Bank</option>
                <option value="TPB Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'TPB Bank' ? 'selected' : '' }}>TPB Bank (Tanzania Postal Bank)</option>
                <option value="Equity Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'Equity Bank' ? 'selected' : '' }}>Equity Bank</option>
                <option value="Exim Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'Exim Bank' ? 'selected' : '' }}>Exim Bank</option>
                <option value="Stanbic Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'Stanbic Bank' ? 'selected' : '' }}>Stanbic Bank</option>
                <option value="Standard Chartered Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'Standard Chartered Bank' ? 'selected' : '' }}>Standard Chartered Bank</option>
                <option value="Bank of Africa" {{ old('bank_name', $customer->bank_name ?? '') == 'Bank of Africa' ? 'selected' : '' }}>Bank of Africa (BOA)</option>
                <option value="DTB Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'DTB Bank' ? 'selected' : '' }}>DTB Bank (Diamond Trust Bank)</option>
                <option value="Access Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'Access Bank' ? 'selected' : '' }}>Access Bank</option>
                <option value="Azania Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'Azania Bank' ? 'selected' : '' }}>Azania Bank</option>
                <option value="Bank of Baroda" {{ old('bank_name', $customer->bank_name ?? '') == 'Bank of Baroda' ? 'selected' : '' }}>Bank of Baroda</option>
                <option value="Bank of India" {{ old('bank_name', $customer->bank_name ?? '') == 'Bank of India' ? 'selected' : '' }}>Bank of India</option>
                <option value="Citibank" {{ old('bank_name', $customer->bank_name ?? '') == 'Citibank' ? 'selected' : '' }}>Citibank</option>
                <option value="Ecobank" {{ old('bank_name', $customer->bank_name ?? '') == 'Ecobank' ? 'selected' : '' }}>Ecobank</option>
                <option value="GTBank" {{ old('bank_name', $customer->bank_name ?? '') == 'GTBank' ? 'selected' : '' }}>GTBank</option>
                <option value="I&M Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'I&M Bank' ? 'selected' : '' }}>I&M Bank</option>
                <option value="KCB Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'KCB Bank' ? 'selected' : '' }}>KCB Bank</option>
                <option value="Mwalimu Commercial Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'Mwalimu Commercial Bank' ? 'selected' : '' }}>Mwalimu Commercial Bank (MCB)</option>
                <option value="PBZ Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'PBZ Bank' ? 'selected' : '' }}>PBZ Bank (People's Bank of Zanzibar)</option>
                <option value="UBA Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'UBA Bank' ? 'selected' : '' }}>UBA Bank (United Bank for Africa)</option>
                <option value="Absa Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'Absa Bank' ? 'selected' : '' }}>Absa Bank</option>
                <option value="Amana Bank" {{ old('bank_name', $customer->bank_name ?? '') == 'Amana Bank' ? 'selected' : '' }}>Amana Bank</option>
                <option value="Other" {{ old('bank_name', $customer->bank_name ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
            </select>
            <small class="form-text text-muted">Optional</small>
            @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Bank Account Number -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Bank Account Number</label>
            <input type="text" name="bank_account" class="form-control @error('bank_account') is-invalid @enderror"
                value="{{ old('bank_account', $customer->bank_account ?? '') }}" placeholder="e.g. 01234567890">
            <small class="form-text text-muted">Optional</small>
            @error('bank_account') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Bank Account Name -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Bank Account Name</label>
            <input type="text" name="bank_account_name" class="form-control @error('bank_account_name') is-invalid @enderror"
                value="{{ old('bank_account_name', $customer->bank_account_name ?? '') }}" placeholder="Account holder name">
            <small class="form-text text-muted">Optional - Name as appears on bank account</small>
            @error('bank_account_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Share & Contribution Accounts Section -->
    <div class="row mt-4">
        <div class="col-12">
            <h6 class="mb-3 text-primary border-bottom pb-2">
                <i class="bx bx-wallet me-2"></i>Share & Contribution Account
            </h6>
        </div>

        <div class="col-md-12 mb-3">
            <div class="card border-light">
                <div class="card-body">
                    <div class="row">
                        <!-- Shares Option -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Share Account</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" value="1" name="has_shares"
                                    id="has_shares" {{ old('has_shares', false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_shares">Create Share Account</label>
                            </div>
                        </div>

                        <!-- Share Product -->
                        <div class="col-md-6 mb-3" id="share-product-container" style="display: none;">
                            <label class="form-label">Share Product <span class="text-danger">*</span></label>
                            <select name="share_product_id" id="share_product_id" class="form-select @error('share_product_id') is-invalid @enderror">
                                <option value="">-- Select Share Product --</option>
                                @foreach($shareProducts ?? [] as $product)
                                    <option value="{{ $product->id }}" {{ old('share_product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->share_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('share_product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Contributions Option -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Contribution Account</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" value="1" name="has_contributions"
                                    id="has_contributions" {{ old('has_contributions', false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_contributions">Create Contribution Account</label>
                            </div>
                        </div>

                        <!-- Contribution Product -->
                        <div class="col-md-6 mb-3" id="contribution-product-container" style="display: none;">
                            <label class="form-label">Contribution Product <span class="text-danger">*</span></label>
                            <select name="contribution_product_id" id="contribution_product_id" class="form-select @error('contribution_product_id') is-invalid @enderror">
                                <option value="">-- Select Contribution Product --</option>
                                @foreach($contributionProducts ?? [] as $product)
                                    <option value="{{ $product->id }}" {{ old('contribution_product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->product_name }} ({{ $product->category }})
                                    </option>
                                @endforeach
                            </select>
                            @error('contribution_product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-between">
            @can('view borrower')
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Customers
            </a>
            @endcan
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-save me-1"></i> {{ $isEdit ? 'Update Customer' : 'Create Customer' }}
            </button>
        </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sharesCheckbox = document.querySelector('#has_shares');
        const shareProductContainer = document.querySelector('#share-product-container');
        const contributionsCheckbox = document.querySelector('#has_contributions');
        const contributionProductContainer = document.querySelector('#contribution-product-container');
        const regionSelect = document.querySelector('#region');
        const districtSelect = document.querySelector('#district');

        // Show/hide share product
        function toggleShareProductField() {
            if (sharesCheckbox && shareProductContainer) {
                if (sharesCheckbox.checked) {
                    shareProductContainer.style.display = 'block';
                    const select = shareProductContainer.querySelector('select');
                    if (select) select.required = true;
                } else {
                    shareProductContainer.style.display = 'none';
                    const select = shareProductContainer.querySelector('select');
                    if (select) {
                        select.required = false;
                        select.value = '';
                    }
                }
            }
        }

        // Show/hide contribution product
        function toggleContributionProductField() {
            if (contributionsCheckbox && contributionProductContainer) {
                if (contributionsCheckbox.checked) {
                    contributionProductContainer.style.display = 'block';
                    const select = contributionProductContainer.querySelector('select');
                    if (select) select.required = true;
                } else {
                    contributionProductContainer.style.display = 'none';
                    const select = contributionProductContainer.querySelector('select');
                    if (select) {
                        select.required = false;
                        select.value = '';
                    }
                }
            }
        }

        if (sharesCheckbox) {
            sharesCheckbox.addEventListener('change', toggleShareProductField);
            toggleShareProductField(); // On load
        }

        if (contributionsCheckbox) {
            contributionsCheckbox.addEventListener('change', toggleContributionProductField);
            toggleContributionProductField(); // On load
        }

        // Load districts on region change
        regionSelect.addEventListener('change', function() {
            const regionId = this.value;

            if (!regionId) {
                districtSelect.innerHTML = '<option value="">Select District</option>';
                return;
            }

            fetch(`/get-districts/${regionId}`)
                .then(response => response.json())
                .then(data => {
                    districtSelect.innerHTML = '<option value="">Select District</option>';
                    Object.entries(data).forEach(([id, name]) => {
                        const option = document.createElement('option');
                        option.value = id;
                        option.textContent = name;
                        districtSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading districts:', error));
        });

        // Initialize Select2 for region only (not district)
        if (window.jQuery) {
            $('#region').select2({
                placeholder: 'Select Region',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });
            // Use jQuery event for region change
            $('#region').on('change', function() {
                const regionId = this.value;
                const districtSelect = document.getElementById('district');
                if (!regionId) {
                    districtSelect.innerHTML = '<option value="">Select District</option>';
                    return;
                }
                fetch(`/get-districts/${regionId}`)
                    .then(response => response.json())
                    .then(data => {
                        districtSelect.innerHTML = '<option value="">Select District</option>';
                        Object.entries(data).forEach(([id, name]) => {
                            const option = document.createElement('option');
                            option.value = id;
                            option.textContent = name;
                            districtSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error loading districts:', error));
            });
        } else {
            // Fallback for non-jQuery environments
            regionSelect.addEventListener('change', function() {
                const regionId = this.value;
                if (!regionId) {
                    districtSelect.innerHTML = '<option value="">Select District</option>';
                    return;
                }
                fetch(`/get-districts/${regionId}`)
                    .then(response => response.json())
                    .then(data => {
                        districtSelect.innerHTML = '<option value="">Select District</option>';
                        Object.entries(data).forEach(([id, name]) => {
                            const option = document.createElement('option');
                            option.value = id;
                            option.textContent = name;
                            districtSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error loading districts:', error));
            });
        }
    });

    // Image preview function
    function previewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('preview');

        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = `<img src="${e.target.result}" width="100" class="mt-2">`;
            }
            reader.readAsDataURL(file);
        }
    }


    // Add/remove filetype-document upload rows
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('file-type-upload-container');
        const addBtn = document.getElementById('add-filetype-row');

        // Ensure there's always at least one row for new customers
        if (!container.querySelector('.file-type-upload-row')) {
            addBtn.click(); // This will add the first row
        }

        addBtn.addEventListener('click', function() {
            const row = document.querySelector('.file-type-upload-row');
            const newRow = row.cloneNode(true);

            // Clear values
            newRow.querySelector('select').selectedIndex = 0;
            newRow.querySelector('input[type="file"]').value = '';

            container.appendChild(newRow);
        });

        container.addEventListener('click', function(e) {
            if (e.target.closest('.remove-filetype-row')) {
                const rows = container.querySelectorAll('.file-type-upload-row');
                if (rows.length > 1) {
                    e.target.closest('.file-type-upload-row').remove();
                }
            }
        });
    });

    // Format phone inputs to add +255 prefix
    document.addEventListener('DOMContentLoaded', function() {
        const phone1Input = document.getElementById('phone1');
        const phone2Input = document.getElementById('phone2');
        const form = document.getElementById('customerForm');

        if (phone1Input) {
            phone1Input.addEventListener('input', function(e) {
                // Only allow digits
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }

        if (phone2Input) {
            phone2Input.addEventListener('input', function(e) {
                // Only allow digits
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }

        // Form submission - add 255 prefix to phone numbers
        if (form) {
            form.addEventListener('submit', function(e) {
                if (phone1Input && phone1Input.value && phone1Input.value.length === 9) {
                    // Create a hidden input with 255 prefix
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'phone1';
                    hiddenInput.value = '255' + phone1Input.value;
                    form.appendChild(hiddenInput);
                    phone1Input.disabled = true; // Disable original input
                }

                if (phone2Input && phone2Input.value && phone2Input.value.length === 9) {
                    // Create a hidden input with 255 prefix
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'phone2';
                    hiddenInput.value = '255' + phone2Input.value;
                    form.appendChild(hiddenInput);
                    phone2Input.disabled = true; // Disable original input
                }
            });
        }
    });
</script>

