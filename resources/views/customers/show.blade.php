@extends('layouts.main')

@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@section('title', 'Customer Profile')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Customers', 'url' => route('customers.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Customer', 'url' => '#', 'icon' => 'bx bx-user']
        ]" />
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">Customer Profile</h6>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal"
                        data-bs-target="#sendMessageModal">
                        <i class="bx bx-message me-1"></i> Send Message
                    </button>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                        data-bs-target="#addNextOfKinModal">
                        <i class="bx bx-user-plus me-1"></i> Add Next of Kin
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                        data-bs-target="#uploadDocumentsModal">
                        <i class="bx bx-upload me-1"></i> Upload Documents
                    </button>
                </div>
            </div>
            <div class="row">
                <!-- Total Loans -->
                <div class="col-md-3">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Total Loans</p>
                                    <h4 class="my-1">
                                        {{ number_format($customer->loans->whereNotIn('status', ['applied', 'approved'])->sum('amount'), 2) }}
                                    </h4>
                                    <p class="mb-0 font-13 text-success">
                                        <i class="bx bxs-up-arrow align-middle"></i> Up to date
                                    </p>
                                </div>
                                <div class="widgets-icons bg-light-success text-success ms-auto">
                                    <i class="bx bxs-wallet"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Default Loans -->
                <div class="col-md-3">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Default Loans</p>
                                    <h4 class="my-1">
                                        @php
                                            $dueScheduleIds = \App\Models\LoanSchedule::where('customer_id', $customer->id)
                                                ->whereDate('due_date', '<', now())
                                                ->pluck('id');

                                            $scheduled = \App\Models\LoanSchedule::whereIn('id', $dueScheduleIds)
                                                ->sum(\DB::raw('principal + interest'));

                                            $paid = \App\Models\Repayment::where('customer_id', $customer->id)
                                                ->whereIn('loan_schedule_id', $dueScheduleIds)
                                                ->sum(\DB::raw('principal + interest'));

                                            $diff = number_format($scheduled - $paid, 2);
                                            
                                            // Calculate actual days in arrears
                                            $oldestDueDate = \App\Models\LoanSchedule::where('customer_id', $customer->id)
                                                ->whereDate('due_date', '<', now())
                                                ->whereRaw('(principal + interest) > COALESCE((SELECT SUM(principal + interest) FROM repayments WHERE loan_schedule_id = loan_schedules.id), 0)')
                                                ->orderBy('due_date', 'asc')
                                                ->value('due_date');
                                            
                                            $daysInArrears = $oldestDueDate ? \Carbon\Carbon::parse($oldestDueDate)->diffInDays(now()) : 0;
                                        @endphp
                                        {{ $diff }}
                                    </h4>
                                    <p class="mb-0 font-13 text-danger">
                                        <i class="bx bxs-down-arrow align-middle"></i> 
                                        @if($daysInArrears > 0)
                                            In arrears ({{ $daysInArrears }} {{ $daysInArrears == 1 ? 'day' : 'days' }})
                                        @else
                                            Up to date
                                        @endif
                                    </p>
                                </div>
                                <div class="widgets-icons bg-light-danger text-danger ms-auto">
                                    <i class="bx bxs-error"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Arrears -->
                <div class="col-md-3">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Total Arrears</p>
                                    <h4 class="my-1">
                                        @php
                                            $dueScheduleIds = \App\Models\LoanSchedule::where('customer_id', $customer->id)
                                                ->whereDate('due_date', '<', now())
                                                ->pluck('id');

                                            $scheduled = \App\Models\LoanSchedule::whereIn('id', $dueScheduleIds)
                                                ->sum(\DB::raw('principal + interest'));

                                            $paid = \App\Models\Repayment::where('customer_id', $customer->id)
                                                ->whereIn('loan_schedule_id', $dueScheduleIds)
                                                ->sum(\DB::raw('principal + interest'));

                                            $diff = number_format($scheduled - $paid, 2);
                                        @endphp
                                        {{ $diff }}
                                    </h4>
                                    <p class="mb-0 font-13 text-warning">
                                        <i class="bx bxs-info-circle align-middle"></i>
                                        @php
                                            $today = now()->toDateString();
                                            $daysInArrears = \DB::table('loan_schedules as s')
                                                ->leftJoin('repayments as r', 's.id', '=', 'r.loan_schedule_id')
                                                ->where('s.customer_id', $customer->id)
                                                ->selectRaw('
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        s.id,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        s.due_date,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        (s.principal + s.interest) as amount_due,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        IFNULL(SUM(r.principal + r.interest), 0) as total_paid,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        CASE
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        WHEN SUM(r.principal + r.interest) < (s.principal + s.interest)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            AND ?> s.due_date
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            THEN DATEDIFF(?, s.due_date)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            WHEN SUM(r.principal + r.interest) >= (s.principal + s.interest)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            AND MAX(r.payment_date) > s.due_date
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            THEN DATEDIFF(MAX(r.payment_date), s.due_date)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ELSE 0
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            END as days_in_arrears', [$today, $today])
                                                ->groupBy('s.id', 's.due_date', 's.principal', 's.interest')
                                                ->orderBy('s.due_date')
                                                ->get();
                                            $maxDays = $daysInArrears->max('days_in_arrears');
                                        @endphp
                                        {{ $maxDays > 0 ? $maxDays . ' days in Arrears' : 'Up to date' }}
                                    </p>
                                </div>
                                <div class="widgets-icons bg-light-warning text-warning ms-auto">
                                    <i class="bx bxs-time-five"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Penalties -->
                <div class="col-md-3">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Pending Penalties</p>
                                    <h4 class="my-1">
                                        {{ number_format(\App\Models\LoanSchedule::where('customer_id', $customer->id)->sum('penalty_amount') - \App\Models\Repayment::where('customer_id', $customer->id)->sum('penalt_amount'), 2) }}
                                    </h4>
                                    <p class="mb-0 font-13 text-danger">
                                        <i class="bx bxs-error-circle align-middle"></i> Unpaid
                                    </p>
                                </div>
                                <div class="widgets-icons bg-light-danger text-danger ms-auto">
                                    <i class="bx bxs-wallet-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">

                <!-- Profile Card -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center">
                                <div class="avatar-lg mx-auto mb-4">
                                    <img src="{{ $customer->photo ? asset('storage/' . $customer->photo) : asset('assets/images/avatars/avatar-2.png') }}"
                                        alt="{{ $customer->name }}" class="rounded-circle p-1 bg-primary" width="110" />
                                </div>
                                <h5 class="font-size-16 mb-1 text-truncate">{{ $customer->name }}</h5>
                                <p class="text-muted text-truncate mb-3">{{ $customer->phone1 ?? 'No phone' }}</p>
                                
                                @php
                                    // Calculate days in arrears for loan status
                                    $oldestDueDate = \App\Models\LoanSchedule::where('customer_id', $customer->id)
                                        ->whereDate('due_date', '<', now())
                                        ->whereRaw('(principal + interest) > COALESCE((SELECT SUM(principal + interest) FROM repayments WHERE loan_schedule_id = loan_schedules.id), 0)')
                                        ->orderBy('due_date', 'asc')
                                        ->value('due_date');
                                    
                                    $daysInArrears = $oldestDueDate ? \Carbon\Carbon::parse($oldestDueDate)->diffInDays(now()) : 0;
                                    
                                    // Determine loan status based on days in arrears
                                    if ($daysInArrears == 0) {
                                        $statusClass = 'bg-success';
                                        $statusText = 'Current';
                                        $statusIcon = 'bx-check-circle';
                                    } elseif ($daysInArrears <= 30) {
                                        $statusClass = 'bg-warning';
                                        $statusText = 'Watch (' . $daysInArrears . ' days)';
                                        $statusIcon = 'bx-time-five';
                                    } elseif ($daysInArrears <= 90) {
                                        $statusClass = 'bg-orange';
                                        $statusText = 'Substandard (' . $daysInArrears . ' days)';
                                        $statusIcon = 'bx-error';
                                    } else {
                                        $statusClass = 'bg-danger';
                                        $statusText = 'Default (' . $daysInArrears . ' days)';
                                        $statusIcon = 'bx-x-circle';
                                    }
                                @endphp
                                
                                <div class="mb-3">
                                    <span class="badge {{ $statusClass }} text-white px-3 py-2">
                                        <i class="bx {{ $statusIcon }} me-1"></i>{{ $statusText }}
                                    </span>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="text-muted">
                                <div class="table-responsive">
                                    <!-- Personal Information Section -->
                                    <h6 class="mb-3 mt-3 text-primary border-bottom pb-2">
                                        <i class="bx bx-user me-2"></i>Personal Information
                                    </h6>
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Full Name :</th>
                                                <td>{{ $customer->name }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Date of Birth :</th>
                                                <td>
                                                    @if($customer->dob)
                                                        {{ \Carbon\Carbon::parse($customer->dob)->format('M d, Y') }}
                                                        ({{ \Carbon\Carbon::parse($customer->dob)->age }} years)
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Gender :</th>
                                                <td>{{ $customer->sex }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Marital Status :</th>
                                                <td>{{ $customer->marital_status ?? 'N/A'}}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Reference :</th>
                                                <td>{{ $customer->reference ?? 'N/A'}}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- Communication Section -->
                                    <h6 class="mb-3 mt-4 text-primary border-bottom pb-2">
                                        <i class="bx bx-phone me-2"></i>Communication
                                    </h6>
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Phone :</th>
                                                <td>{{ $customer->phone1 }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Alt Phone :</th>
                                                <td>{{ $customer->phone2 ?? 'N/A'}}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Email :</th>
                                                <td>{{ $customer->email ?? 'N/A'}}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- Work and Identification Section -->
                                    <h6 class="mb-3 mt-4 text-primary border-bottom pb-2">
                                        <i class="bx bx-briefcase me-2"></i>Work & Identification
                                    </h6>
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Employment Status :</th>
                                                <td>{{ $customer->employment_status ?? 'N/A'}}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Work/Business Name :</th>
                                                <td>{{ $customer->work ?? 'N/A'}}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Work/Business Address :</th>
                                                <td>{{ $customer->workAddress ?? 'N/A'}}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">ID Type :</th>
                                                <td>{{ $customer->idType ?? 'N/A'}}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">ID Number :</th>
                                                <td>{{ $customer->idNumber ?? 'N/A'}}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- Address Section -->
                                    <h6 class="mb-3 mt-4 text-primary border-bottom pb-2">
                                        <i class="bx bx-map me-2"></i>Address
                                    </h6>
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Region :</th>
                                                <td>{{ $customer->region->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">District :</th>
                                                <td>{{ $customer->district->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Street/Address :</th>
                                                <td>{{ $customer->street ?? 'N/A' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- Other Information Section -->
                                    <h6 class="mb-3 mt-4 text-primary border-bottom pb-2">
                                        <i class="bx bx-list-ul me-2"></i>Other Information
                                    </h6>
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Customer ID :</th>
                                                <td>{{ $customer->customerNo }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Category :</th>
                                                <td>{{ $customer->category }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Relation :</th>
                                                <td>{{ $customer->relation ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Number of Spouse :</th>
                                                <td>{{ $customer->number_of_spouse ?? '0' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Number of Children :</th>
                                                <td>{{ $customer->number_of_children ?? '0' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Description :</th>
                                                <td>{{ $customer->description ?? 'N/A' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- Financial Status Section -->
                                    <h6 class="mb-3 mt-4 text-primary border-bottom pb-2">
                                        <i class="bx bx-dollar-circle me-2"></i>Financial Status
                                    </h6>
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Monthly Income :</th>
                                                <td>{{ $customer->monthly_income ? 'TZS ' . number_format($customer->monthly_income, 2) : 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Monthly Expenses :</th>
                                                <td>{{ $customer->monthly_expenses ? 'TZS ' . number_format($customer->monthly_expenses, 2) : 'N/A' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- Bank Information Section -->
                                    <h6 class="mb-3 mt-4 text-primary border-bottom pb-2">
                                        <i class="bx bx-credit-card me-2"></i>Bank Information
                                    </h6>
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Bank Name :</th>
                                                <td>{{ $customer->bank_name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Bank Account :</th>
                                                <td>{{ $customer->bank_account ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Bank Account Name :</th>
                                                <td>{{ $customer->bank_account_name ?? 'N/A' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- System Information Section -->
                                    <h6 class="mb-3 mt-4 text-primary border-bottom pb-2">
                                        <i class="bx bx-info-circle me-2"></i>System Information
                                    </h6>
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Branch :</th>
                                                <td>{{ $customer->branch->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Company :</th>
                                                <td>{{ $customer->company->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Registrar :</th>
                                                <td>{{ $customer->user->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Joined :</th>
                                                <td>{{ $customer->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Last Updated :</th>
                                                <td>{{ $customer->updated_at->format('M d, Y') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- Loan Officers Section -->
                                    <h6 class="mb-3 mt-4 text-primary border-bottom pb-2">
                                        <i class="bx bx-user-check me-2"></i>Assigned Loan Officers
                                    </h6>
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                        @foreach($customer->loanOfficers as $officers)
                                            <tr>
                                                <td><i class="bx bx-user me-2"></i>{{ $officers->name }}</td>
                                            </tr>
                                        @endforeach
                                        @if($customer->loanOfficers->isEmpty())
                                            <tr>
                                                <td class="text-muted">No loan officers assigned</td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>



                        </div>
                    </div>
                </div>



                <!-- Profile Details -->
                <div class="col-xl-8">
                    <!-- Modern Document Management Card -->
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="bx bx-folder me-2"></i>Customer Documents
                                        <span class="badge bg-primary ms-2">{{ $customer->filetypes->count() }}</span>
                                    </h5>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#uploadDocumentsModal">
                                        <i class="bx bx-plus me-1"></i>Add Documents
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                @if ($customer->filetypes->count())
                                    <div class="row g-3" id="documentsGrid">
                                        @foreach ($customer->filetypes as $index => $file)
                                            <div class="col-md-6 col-lg-4">
                                                <div class="card border document-card" data-pivot-id="{{ $file->pivot->id }}">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-1 text-truncate fw-bold" title="{{ $file->name }}">
                                                                    {{ $file->name }}
                                                                </h6>
                                                                <div class="d-flex align-items-center gap-1">
                                                                    @if($file->pivot->document_path)
                                                                        <span
                                                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                                                            <i class="bx bx-check-circle me-1"></i>Uploaded
                                                                        </span>
                                                                    @else
                                                                        <span
                                                                            class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                                                            <i class="bx bx-x-circle me-1"></i>No file
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @if($file->pivot->document_path)
                                                            <div class="mt-3 d-flex gap-2">
                                                                <a href="{{ route('customers.documents.view', [\Vinkla\Hashids\Facades\Hashids::encode($customer->id), $file->pivot->id]) }}"
                                                                    class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                                                                    target="_blank" title="View Document">
                                                                    <span class="d-none d-sm-inline">View</span>
                                                                </a>
                                                                <a href="{{ route('customers.documents.download', [\Vinkla\Hashids\Facades\Hashids::encode($customer->id), $file->pivot->id]) }}"
                                                                    class="btn btn-sm btn-outline-success d-flex align-iteems-center gap-1"
                                                                    title="Download Document">
                                                                    <span class="d-none d-sm-inline">Download</span>
                                                                </a>
                                                                <a type="button"
                                                                    class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1"
                                                                    onclick="deleteDocument({{ $file->pivot->id }})"
                                                                    title="Delete Document">
                                                                    <span class="d-none d-sm-inline">Delete</span>
                                                                </a>
                                                            </div>
                                                        @else
                                                            <div class="mt-3">
                                                                <span class="text-danger small">
                                                                    <i class="bx bx-info-circle me-1"></i>No file uploaded for this
                                                                    document type
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="bx bx-folder-open display-1 text-muted"></i>
                                        <h5 class="mt-3 text-muted">No documents uploaded</h5>
                                        <p class="text-muted">Click "Add Documents" to upload customer files</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Next of Kin Card -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title mb-0">Next of Kin</h5>
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                    data-bs-target="#addNextOfKinModal">
                                    <i class="bx bx-user-plus me-1"></i> Add Next of Kin
                                </button>
                            </div>
                            <hr class="my-4">

                            <div class="table-responsive">
                                <table class="table table-bordered dt-responsive nowrap table-striped">
                                    <thead>
                                        <tr>
                                            <th>S/N</th>
                                            <th>Name</th>
                                            <th>Relationship</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Address</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="nextOfKinTableBody">
                                        @forelse($customer->nextOfKin as $kin)
                                            <tr data-kin-id="{{ Hashids::encode($kin->id) }}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $kin->name }}</td>
                                                <td>{{ $kin->relationship }}</td>
                                                <td>{{ $kin->phone ?? 'N/A' }}</td>
                                                <td>{{ $kin->email ?? 'N/A' }}</td>
                                                <td>{{ $kin->address ?? 'N/A' }}</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-warning edit-kin-btn" 
                                                        data-kin-id="{{ Hashids::encode($kin->id) }}"
                                                        data-kin-name="{{ $kin->name }}"
                                                        data-kin-relationship="{{ $kin->relationship }}"
                                                        data-kin-phone="{{ $kin->phone }}"
                                                        data-kin-email="{{ $kin->email }}"
                                                        data-kin-address="{{ $kin->address }}"
                                                        data-kin-id-type="{{ $kin->id_type }}"
                                                        data-kin-id-number="{{ $kin->id_number }}"
                                                        data-kin-dob="{{ $kin->date_of_birth ? $kin->date_of_birth->format('Y-m-d') : '' }}"
                                                        data-kin-gender="{{ $kin->gender }}"
                                                        data-kin-notes="{{ $kin->notes }}"
                                                        title="Edit">
                                                        <i class="bx bx-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger delete-kin-btn" 
                                                        data-kin-id="{{ Hashids::encode($kin->id) }}"
                                                        data-kin-name="{{ $kin->name }}"
                                                        title="Delete">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    <i class="bx bx-user fs-1 d-block mb-2"></i>
                                                    No next of kin records found for this customer.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Shares Accounts Card -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Shares Accounts</h5>
                            <hr class="my-4">

                            <div class="table-responsive">
                                <table class="table table-bordered dt-responsive nowrap table-striped" id="sharesTable">
                                    <thead>
                                        <tr>
                                            <th>S/N</th>
                                            <th>Account Number</th>
                                            <th>Share Product</th>
                                            <th>Share Balance</th>
                                            <th>Opening Date</th>
                                            <th>Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($customer->shareAccounts as $shareAccount)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $shareAccount->account_number }}</td>
                                                <td>{{ $shareAccount->shareProduct->share_name ?? 'N/A' }}</td>
                                                <td>{{ number_format($shareAccount->share_balance ?? 0, 2) }}</td>
                                                <td>{{ $shareAccount->opening_date ? $shareAccount->opening_date->format('M d, Y') : 'N/A' }}</td>
                                                <td>
                                                    @if($shareAccount->status === 'active')
                                                        <span class="badge bg-success">Active</span>
                                                    @elseif($shareAccount->status === 'inactive')
                                                        <span class="badge bg-warning">Inactive</span>
                                                    @elseif($shareAccount->status === 'closed')
                                                        <span class="badge bg-danger">Closed</span>
                                                    @else
                                                        <span class="badge bg-secondary">Unknown</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('shares.accounts.show', Hashids::encode($shareAccount->id)) }}"
                                                        class="btn btn-sm btn-info" title="View">
                                                        <i class="bx bx-show"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    <i class="bx bx-package fs-1 d-block mb-2"></i>
                                                    No share accounts found for this customer.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Contributions Accounts Card -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Contributions Accounts</h5>
                            <hr class="my-4">

                            <div class="table-responsive">
                                <table class="table table-bordered dt-responsive nowrap table-striped" id="contributionsTable">
                                    <thead>
                                        <tr>
                                            <th>S/N</th>
                                            <th>Account Number</th>
                                            <th>Contribution Product</th>
                                            <th>Balance</th>
                                            <th>Opening Date</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($customer->contributionAccounts as $contributionAccount)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $contributionAccount->account_number }}</td>
                                                <td>{{ $contributionAccount->contributionProduct->product_name ?? 'N/A' }}</td>
                                                <td>{{ number_format($contributionAccount->balance ?? 0, 2) }}</td>
                                                <td>{{ $contributionAccount->opening_date ? $contributionAccount->opening_date->format('M d, Y') : 'N/A' }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('contributions.accounts.show', Hashids::encode($contributionAccount->id)) }}"
                                                        class="btn btn-sm btn-info" title="View">
                                                        <i class="bx bx-show"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    <i class="bx bx-donate-heart fs-1 d-block mb-2"></i>
                                                    No contribution accounts found for this customer.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Loans Records Card -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Loans Records</h5>
                            <hr class="my-4">
                            <div class="table-responsive">
                                <table class="table table-bordered dt-responsive nowrap table-striped" id="loansTable">
                                    <thead>
                                        <tr>
                                            <th>S/N</th>
                                            <th>Amount</th>
                                            <th>Total Amount</th>
                                            <th>Paid Amount</th>
                                            <th>Balance</th>
                                            <th>Status</th>
                                            <th>Disbursed On</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($customer->loans as $loan)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ number_format($loan->amount, 2) }}</td>
                                                <td>{{ number_format($loan->amount_total, 2) }}</td>
                                                <td>
                                                    {{ number_format(\App\Models\Repayment::where('loan_id', $loan->id)->sum(\DB::raw('principal + interest')), 2) }}
                                                </td>
                                                <td>
                                                    {{ number_format($loan->amount_total - \App\Models\Repayment::where('loan_id', $loan->id)->sum(\DB::raw('principal + interest')), 2) }}
                                                </td>
                                                <td>
                                                    @if($loan->status === 'active')
                                                        <span class="badge bg-success">Active</span>
                                                    @elseif($loan->status === 'pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                    @elseif($loan->status === 'closed')
                                                        <span class="badge bg-secondary">Closed</span>
                                                    @elseif($loan->status === 'defaulted')
                                                        <span class="badge bg-danger">Defaulted</span>
                                                    @elseif($loan->status === 'applied')
                                                        <span class="badge bg-warning">Applied</span>
                                                    @elseif($loan->status === 'checked')
                                                        <span class="badge bg-info">Checked</span>
                                                    @elseif($loan->status === 'approved')
                                                        <span class="badge bg-primary">Approved</span>
                                                    @elseif($loan->status === 'authorized')
                                                        <span class="badge bg-success">Authorized</span>
                                                    @elseif($loan->status === 'rejected')
                                                        <span class="badge bg-danger">Rejected</span>
                                                    @elseif($loan->status === 'completed')
                                                        <span class="badge bg-success">Completed</span>
                                                    @else
                                                        <span class="badge bg-info">{{ ucfirst($loan->status) }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $loan->disbursed_on ? \Carbon\Carbon::parse($loan->disbursed_on)->format('M d, Y') : 'N/A' }}</td>
                                                <td class="text-center">
                                                    @can('view loan details')
                                                        <a href="{{ route('loans.show', Hashids::encode($loan->id)) }}"
                                                            class="btn btn-sm btn-info" title="View">
                                                            <i class="bx bx-show"></i> View
                                                        </a>
                                                    @endcan

                                                    @can('edit loan')
                                                        @if($loan->status == 'pending' || in_array($loan->status, ['applied', 'rejected']))
                                                            @php
                                                                $encodedId = Hashids::encode($loan->id);
                                                                $editRoute = in_array($loan->status, ['applied', 'rejected'])
                                                                    ? route('loans.application.edit', $encodedId)
                                                                    : route('loans.edit', $encodedId);
                                                            @endphp
                                                            <a href="{{ $editRoute }}" class="btn btn-sm btn-warning" title="Edit">
                                                                <i class="bx bx-edit"></i> Edit
                                                            </a>
                                                        @endif
                                                    @endcan
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    <i class="bx bx-money fs-1 d-block mb-2"></i>
                                                    No loans found for this customer.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

            </div>
            <!--end page wrapper -->
            <!--start overlay-->
            <div class="overlay toggle-icon"></div>
            <!--end overlay-->
            <!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i
                    class='bx bxs-up-arrow-alt'></i></a>
            <!--End Back To Top Button-->
            <footer class="page-footer">
                <p class="mb-0">Copyright © {{ date('Y') }}. All right reserved. -- By SAFCO FINTECH</p>
            </footer>

            <!-- Send Message Modal -->
            <!-- Send SMS Modal -->
            <div class="modal fade" id="sendMessageModal" tabindex="-1" aria-labelledby="sendMessageModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="sendMessageModalLabel">
                                <i class="bx bx-message me-2"></i>Send SMS to {{ $customer->name }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <!-- Important: action attribute points to the correct route -->
                        <form id="sendMessageForm"
                            action="{{ route('customers.send-message', \Vinkla\Hashids\Facades\Hashids::encode($customer->id)) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="phone_number" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number"
                                        value="{{ $customer->phone1 }}" readonly>
                                    <div class="form-text">Message will be sent to this number</div>
                                </div>

                                <div class="mb-3">
                                    <label for="message_template" id="message_template" class="form-label">Message
                                        Template</label>
                                    <select class="form-select" id="message_template" name="message_template">
                                        <option value="">Select a template...</option>
                                        <option value="payment_reminder">Payment Reminder</option>
                                        <option value="loan_approved">Loan Approved</option>
                                        <option value="loan_disbursed">Loan Disbursed</option>
                                        <option value="custom">Custom Message</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="message_content" class="form-label">Message Content</label>
                                    <textarea class="form-control" id="message_content" name="message_content" rows="4"
                                        placeholder="Type your message here..." required></textarea>
                                    <div class="form-text"><span id="character_count">0</span>/160 characters</div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <strong>Note:</strong> SMS charges may apply. Please ensure the message is appropriate
                                    and professional.
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bx bx-x me-1"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-primary" id="sendMessageBtn">
                                    <i class="bx bx-send me-1"></i>Send SMS Now
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modern Upload Documents Modal -->
            <div class="modal fade" id="uploadDocumentsModal" tabindex="-1" aria-labelledby="uploadDocumentsLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="uploadDocumentsLabel">
                                <i class="bx bx-cloud-upload me-2"></i>Upload Customer Documents
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- File Upload Area -->
                            <div class="upload-area" id="uploadArea">
                                <div class="text-center py-5">
                                    <div class="upload-icon-wrapper mb-4">
                                        <i class="bx bx-cloud-upload display-1 text-primary"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark">Drag & Drop files here or click to browse</h5>
                                    <p class="text-muted mb-4">Support: PDF, DOC, DOCX, JPG, PNG (Max 5MB each)</p>
                                    <input type="file" id="fileInput" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                        style="display: none;">
                                    <button type="button" class="btn btn-primary btn-lg px-4 py-2 rounded-pill"
                                        onclick="document.getElementById('fileInput').click()">
                                        <i class="bx bx-folder-open me-2"></i>Choose Files
                                    </button>
                                </div>
                            </div>

                            <!-- Selected Files Preview -->
                            <div id="selectedFiles" class="mt-4" style="display: none;">
                                <h6 class="mb-3">
                                    <i class="bx bx-file me-2"></i>Selected Files
                                    <span class="badge bg-primary ms-2" id="fileCount">0</span>
                                </h6>
                                <div id="filesList" class="row g-3"></div>
                            </div>

                            <!-- Upload Progress -->
                            <div id="uploadProgress" class="mt-4" style="display: none;">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bx bx-loader-alt bx-spin me-2"></i>
                                    <span>Uploading files...</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                        style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                <i class="bx bx-x me-2"></i>Cancel
                            </button>
                            <button type="button" class="btn btn-primary px-4" id="uploadBtn" disabled>
                                <i class="bx bx-upload me-2"></i>Upload Documents
                            </button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- TEST BUTTON FOR LOADING STATE AND SWEETALERT -->

            <!-- TEST MODAL -->

            <!-- Add/Edit Next of Kin Modal -->
            <div class="modal fade" id="addNextOfKinModal" tabindex="-1" aria-labelledby="addNextOfKinModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addNextOfKinModalLabel">Add Next of Kin</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="nextOfKinForm">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" id="nextOfKinId" name="next_of_kin_id">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="kinName" name="name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Relationship <span class="text-danger">*</span></label>
                                        <select class="form-select" id="kinRelationship" name="relationship" required>
                                            <option value="">Select Relationship</option>
                                            <option value="Spouse">Spouse</option>
                                            <option value="Parent">Parent</option>
                                            <option value="Sibling">Sibling</option>
                                            <option value="Child">Child</option>
                                            <option value="Guardian">Guardian</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" class="form-control" id="kinPhone" name="phone">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" id="kinEmail" name="email">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" id="kinAddress" name="address" rows="2"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ID Type</label>
                                        <select class="form-select" id="kinIdType" name="id_type">
                                            <option value="">Select ID Type</option>
                                            <option value="National ID">National ID</option>
                                            <option value="Passport">Passport</option>
                                            <option value="Driving License">Driving License</option>
                                            <option value="Voter ID">Voter ID</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ID Number</label>
                                        <input type="text" class="form-control" id="kinIdNumber" name="id_number">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" id="kinDob" name="date_of_birth">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Gender</label>
                                        <select class="form-select" id="kinGender" name="gender">
                                            <option value="">Select Gender</option>
                                            <option value="M">Male</option>
                                            <option value="F">Female</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" id="kinNotes" name="notes" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bx bx-x me-1"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bx bx-save me-1"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- End Add/Edit Next of Kin Modal -->

@endsection

        <!-- Modern Document Management Functions -->
        <script>
            // Modern delete document function
            function deleteDocument(pivotId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Delete Document',
                    text: 'Are you sure you want to delete this document? This action cannot be undone.',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Deleting...',
                            text: 'Please wait while we delete the document.',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        const deleteUrl = "{{ route('customers.documents.delete', [\Vinkla\Hashids\Facades\Hashids::encode($customer->id), 'PIVOT_ID']) }}".replace('PIVOT_ID', pivotId);

                        fetch(deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Show success toast
                                    const Toast = Swal.mixin({
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000,
                                        timerProgressBar: true
                                    });

                                    Toast.fire({
                                        icon: 'success',
                                        title: 'Document deleted successfully'
                                    });

                                    // Remove the document card from UI
                                    const documentCard = document.querySelector(`[data-pivot-id="${pivotId}"]`);
                                    if (documentCard) {
                                        documentCard.closest('.col-md-6, .col-lg-4').remove();
                                    }

                                    // Update document count
                                    const badge = document.querySelector('.badge.bg-primary');
                                    if (badge) {
                                        const currentCount = parseInt(badge.textContent);
                                        badge.textContent = currentCount - 1;
                                    }
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.message || 'Failed to delete document'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Delete error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Network error occurred while deleting document'
                                });
                            });
                    }
                });
            }

            // Legacy function for backward compatibility
            function deleteCustomerDocument(pivotId) {
                deleteDocument(pivotId);
            }
        </script>

        @push('styles')
            <style>
                .upload-area {
                    border: 2px dashed #dee2e6;
                    border-radius: 12px;
                    transition: all 0.3s ease;
                    cursor: pointer;
                    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
                }

                .upload-area:hover {
                    border-color: #0d6efd;
                    background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(13, 110, 253, 0.15);
                }

                .upload-area.border-primary {
                    border-color: #0d6efd !important;
                    background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%) !important;
                }

                .upload-icon-wrapper {
                    transition: all 0.3s ease;
                }

                .upload-area:hover .upload-icon-wrapper {
                    transform: scale(1.1);
                }

                .document-card {
                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                }

                .document-card:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }

                .file-icon {
                    font-size: 2rem;
                }

                .file-icon-wrapper {
                    transition: all 0.3s ease;
                    border: 1px solid transparent;
                }

                .file-icon-wrapper:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                }

                .document-card .btn {
                    transition: all 0.2s ease;
                    font-weight: 500;
                }

                .document-card .btn:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                }

                .document-card .btn-outline-primary:hover {
                    background-color: var(--bs-primary);
                    border-color: var(--bs-primary);
                    color: white;
                }

                .document-card .btn-outline-success:hover {
                    background-color: var(--bs-success);
                    border-color: var(--bs-success);
                    color: white;
                }

                .document-card .btn-outline-danger:hover {
                    background-color: var(--bs-danger);
                    border-color: var(--bs-danger);
                    color: white;
                }

                .badge {
                    font-size: 0.75rem;
                    font-weight: 500;
                    padding: 0.375rem 0.75rem;
                }
            </style>
        @endpush

        @push('scripts')
            <script>
                // Modern Document Upload System
                class DocumentUploader {
                    constructor() {
                        this.selectedFiles = [];
                        this.maxFileSize = 5 * 1024 * 1024; // 5MB
                        this.allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
                        this.init();
                    }

                    init() {
                        this.setupFileInput();
                        this.setupDragAndDrop();
                        this.setupUploadButton();
                        this.setupModalReset();
                    }

                    setupFileInput() {
                        const fileInput = document.getElementById('fileInput');
                        if (fileInput) {
                            fileInput.addEventListener('change', (e) => this.handleFileSelection(e.target.files));
                        }
                    }

                    setupDragAndDrop() {
                        const uploadArea = document.getElementById('uploadArea');
                        if (!uploadArea) return;

                        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                            uploadArea.addEventListener(eventName, this.preventDefaults, false);
                        });

                        ['dragenter', 'dragover'].forEach(eventName => {
                            uploadArea.addEventListener(eventName, () => this.highlight(uploadArea), false);
                        });

                        ['dragleave', 'drop'].forEach(eventName => {
                            uploadArea.addEventListener(eventName, () => this.unhighlight(uploadArea), false);
                        });

                        uploadArea.addEventListener('drop', (e) => this.handleDrop(e), false);
                    }

                    setupUploadButton() {
                        const uploadBtn = document.getElementById('uploadBtn');
                        if (uploadBtn) {
                            uploadBtn.addEventListener('click', () => this.uploadFiles());
                        }
                    }

                    setupModalReset() {
                        const modal = document.getElementById('uploadDocumentsModal');
                        if (modal) {
                            modal.addEventListener('hidden.bs.modal', () => this.resetModal());
                        }
                    }

                    preventDefaults(e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }

                    highlight(element) {
                        element.classList.add('border-primary', 'bg-light');
                    }

                    unhighlight(element) {
                        element.classList.remove('border-primary', 'bg-light');
                    }

                    handleDrop(e) {
                        const dt = e.dataTransfer;
                        const files = dt.files;
                        this.handleFileSelection(files);
                    }

                    handleFileSelection(files) {
                        const newFiles = Array.from(files);
                        const validFiles = newFiles.filter(file => this.validateFile(file));

                        if (validFiles.length !== newFiles.length) {
                            this.showToast('Some files were rejected due to size or type restrictions', 'warning');
                        }

                        this.selectedFiles = [...this.selectedFiles, ...validFiles];
                        this.updateFilePreview();
                        this.updateUploadButton();
                    }

                    validateFile(file) {
                        const extension = file.name.split('.').pop().toLowerCase();
                        const isValidType = this.allowedTypes.includes(extension);
                        const isValidSize = file.size <= this.maxFileSize;

                        if (!isValidType) {
                            console.warn(`File ${file.name} rejected: Invalid file type`);
                        }
                        if (!isValidSize) {
                            console.warn(`File ${file.name} rejected: File too large`);
                        }

                        return isValidType && isValidSize;
                    }

                    updateFilePreview() {
                        const selectedFilesDiv = document.getElementById('selectedFiles');
                        const filesListDiv = document.getElementById('filesList');
                        const fileCountSpan = document.getElementById('fileCount');

                        if (this.selectedFiles.length === 0) {
                            selectedFilesDiv.style.display = 'none';
                            return;
                        }

                        selectedFilesDiv.style.display = 'block';
                        fileCountSpan.textContent = this.selectedFiles.length;

                        filesListDiv.innerHTML = this.selectedFiles.map((file, index) => `
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="bx bxs-file fs-4 text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 text-truncate" title="${file.name}">${file.name}</h6>
                                            <small class="text-muted mb-2 d-block">${this.formatFileSize(file.size)}</small>
                                            <select class="form-select form-select-sm filetype-select" data-file-index="${index}" required>
                                                <option value="">Select document type...</option>
                                                <option value="1">Passport</option>
                                                <option value="2">National ID</option>
                                                <option value="3">Driver License</option>
                                                <option value="4">Proof of Residence</option>
                                                <option value="5">Proof of Income</option>
                                                <option value="6">Birth Certificate</option>
                                                <option value="7">Company Registration</option>
                                                <option value="8">Multiple Documents</option>
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="documentUploader.removeFile(${index})">
                                            <i class="bx bx-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');
                    }

                    removeFile(index) {
                        this.selectedFiles.splice(index, 1);
                        this.updateFilePreview();
                        this.updateUploadButton();
                    }

                    updateUploadButton() {
                        const uploadBtn = document.getElementById('uploadBtn');
                        if (uploadBtn) {
                            uploadBtn.disabled = this.selectedFiles.length === 0;
                        }
                    }

                    async uploadFiles() {
                        if (this.selectedFiles.length === 0) return;

                        // Validate that all files have filetypes selected
                        const filetypeSelects = document.querySelectorAll('.filetype-select');
                        let allValid = true;

                        filetypeSelects.forEach(select => {
                            if (!select.value) {
                                select.classList.add('is-invalid');
                                allValid = false;
                            } else {
                                select.classList.remove('is-invalid');
                            }
                        });

                        if (!allValid) {
                            this.showToast('Please select document types for all files', 'error');
                            return;
                        }

                        const uploadBtn = document.getElementById('uploadBtn');
                        const uploadProgress = document.getElementById('uploadProgress');
                        const progressBar = uploadProgress.querySelector('.progress-bar');

                        // Show progress
                        uploadProgress.style.display = 'block';
                        uploadBtn.disabled = true;
                        uploadBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Uploading...';

                        try {
                            const formData = new FormData();
                            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                            formData.append('_token', csrfToken);

                            // Add files with their selected filetypes
                            this.selectedFiles.forEach((file, index) => {
                                const filetypeSelect = document.querySelector(`.filetype-select[data-file-index="${index}"]`);
                                formData.append('documents[]', file);
                                formData.append('filetypes[]', filetypeSelect.value);
                            });

                            const response = await fetch('{{ route("customers.documents.upload", \Vinkla\Hashids\Facades\Hashids::encode($customer->id)) }}', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            });

                            const data = await response.json();

                            if (data.success) {
                                this.showToast(`Successfully uploaded ${data.uploaded_count || this.selectedFiles.length} document(s)`, 'success');

                                // Close modal and reload page
                                const modal = bootstrap.Modal.getInstance(document.getElementById('uploadDocumentsModal'));
                                modal.hide();

                                setTimeout(() => {
                                    location.reload();
                                }, 1000);
                            } else {
                                this.showToast(data.message || 'Upload failed', 'error');
                            }
                        } catch (error) {
                            console.error('Upload error:', error);
                            this.showToast('Network error occurred', 'error');
                        } finally {
                            uploadProgress.style.display = 'none';
                            uploadBtn.disabled = false;
                            uploadBtn.innerHTML = '<i class="bx bx-upload me-1"></i>Upload Documents';
                        }
                    }

                    resetModal() {
                        this.selectedFiles = [];
                        this.updateFilePreview();
                        this.updateUploadButton();

                        const fileInput = document.getElementById('fileInput');
                        if (fileInput) fileInput.value = '';
                    }

                    formatFileSize(bytes) {
                        if (bytes === 0) return '0 Bytes';
                        const k = 1024;
                        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                        const i = Math.floor(Math.log(bytes) / Math.log(k));
                        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                    }

                    showToast(message, type = 'info') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        });

                        Toast.fire({
                            icon: type,
                            title: message
                        });
                    }
                }

                // Initialize when DOM is loaded
                document.addEventListener('DOMContentLoaded', function () {
                    window.documentUploader = new DocumentUploader();
                });


                // Check if we need to print a receipt after deposit
                @if(session('print_receipt') && session('receipt_data'))
                    setTimeout(function () {
                        const receiptData = @json(session('receipt_data'));
                        printDepositReceipt(receiptData);
                    }, 1000);
                @endif

                function printDepositReceipt(receiptData) {
                    // Create a new window for thermal printer (narrow width)
                    const printWindow = window.open('', '_blank', 'width=320,height=600');

                    // Set the document title
                    const customerName = receiptData.customer_name.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_');
                    const fileName = `Deposit_Receipt_${customerName}_${receiptData.date}`;

                    const receiptHtml = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>${fileName}</title>
                            <style>
                                @page {
                                    size: 80mm 200mm;
                                    margin: 0;
                                    padding: 0;
                                }

                                @media print {
                                    body {
                                        font-family: 'Courier New', monospace;
                                        font-size: 10px;
                                        margin: 0;
                                        padding: 5px;
                                        width: 280px;
                                        max-width: 280px;
                                        min-width: 280px;
                                        page-break-after: avoid;
                                        page-break-before: avoid;
                                    }
                                }

                                body {
                                    font-family: 'Courier New', monospace;
                                    font-size: 10px;
                                    margin: 0;
                                    padding: 5px;
                                    width: 280px;
                                    max-width: 280px;
                                    min-width: 280px;
                                }
                                .header { text-align: center; margin-bottom: 8px; }
                                .title { font-size: 14px; font-weight: bold; margin-bottom: 3px; }
                                .subtitle { font-size: 10px; margin-bottom: 8px; }
                                .divider { border-top: 1px dashed #000; margin: 8px 0; }
                                .row { display: flex; justify-content: space-between; margin: 2px 0; }
                                .label { font-weight: bold; }
                                .value { text-align: right; }
                                .total { font-weight: bold; font-size: 12px; }
                                .footer { text-align: center; margin-top: 15px; font-size: 8px; }
                                .center { text-align: center; }
                                .bold { font-weight: bold; }
                                .notes { margin: 8px 0; font-size: 9px; }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <div class="title">SMARTFINANCE</div>
                                <div class="subtitle">Cash Deposit Receipt</div>
                            </div>

                            <div class="divider"></div>

                            <div class="row">
                                <span class="label">Customer:</span>
                                <span class="value">${receiptData.customer_name}</span>
                            </div>
                            <div class="row">
                                <span class="label">Deposit Type:</span>
                                <span class="value">${receiptData.deposit_type}</span>
                            </div>

                            <div class="divider"></div>

                            <div class="row">
                                <span class="label">Receipt No:</span>
                                <span class="value">${receiptData.receipt_number}</span>
                            </div>
                            <div class="row">
                                <span class="label">Date:</span>
                                <span class="value">${receiptData.date}</span>
                            </div>
                            <div class="row">
                                <span class="label">Time:</span>
                                <span class="value">${receiptData.time}</span>
                            </div>
                            <div class="row">
                                <span class="label">Bank Account:</span>
                                <span class="value">${receiptData.bank_account}</span>
                            </div>

                            <div class="divider"></div>

                            <div class="row total">
                                <span class="label">Amount Deposited:</span>
                                <span class="value">TSHS ${parseFloat(receiptData.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                            </div>

                            <div class="divider"></div>

                            <div class="notes">
                                <div class="label">Notes:</div>
                                <div style="margin-top: 2px;">${receiptData.notes}</div>
                            </div>

                            <div class="divider"></div>

                            <div class="footer">
                                <div>Received by: ${receiptData.received_by}</div>
                                <div>Branch: ${receiptData.branch}</div>
                                <div style="margin-top: 5px;">Thank you for your deposit!</div>
                            </div>
                        </body>
                        </html>
                    `;

                    printWindow.document.write(receiptHtml);
                    printWindow.document.close();

                    // Auto print after a short delay
                    setTimeout(() => {
                        printWindow.print();
                        // Auto close after printing (optional)
                        setTimeout(() => {
                            printWindow.close();
                        }, 2000);
                    }, 500);
                }

                // SMS Message functionality
                document.addEventListener('DOMContentLoaded', function () {
                    const messageTemplate = document.getElementById('message_template');
                    const messageContent = document.getElementById('message_content');
                    const characterCount = document.getElementById('character_count');
                    const form = document.getElementById('sendMessageForm');

                    // Character counter
                    function updateCharacterCount() {
                        if (messageContent && characterCount) {
                            const count = messageContent.value.length;
                            characterCount.textContent = count;
                            if (count > 160) {
                                characterCount.style.color = 'red';
                            } else if (count > 140) {
                                characterCount.style.color = 'orange';
                            } else {
                                characterCount.style.color = 'green';
                            }
                        }
                    }

                    // Template messages
                    const templateMessages = {
                        payment_reminder: "Dear {{ $customer->name }}, this is a friendly reminder that your loan payment is due. Please make your payment to avoid any late fees. Thank you.",
                        loan_approved: "Dear {{ $customer->name }}, congratulations! Your loan application has been approved. Please visit our office for the next steps. Thank you.",
                        loan_disbursed: "Dear {{ $customer->name }}, your loan has been successfully disbursed. Please check your account. Thank you for choosing SmartFinance."
                    };

                    // Template change handler
                    if (messageTemplate && messageContent) {
                        messageTemplate.addEventListener('change', function () {
                            const value = this.value;
                            if (value === 'custom') {
                                messageContent.value = '';
                            } else {
                                messageContent.value = templateMessages[value] || '';
                            }
                            updateCharacterCount();
                        });

                        messageContent.addEventListener('input', updateCharacterCount);
                    }

                    // Form submission
                    if (form) {
                        form.addEventListener('submit', function (e) {
                            e.preventDefault();

                            const sendBtn = document.getElementById('sendMessageBtn');
                            const originalText = sendBtn.innerHTML;
                            const phoneNumber = document.getElementById('phone_number').value.trim();
                            const msg = messageContent.value.trim();

                            if (!phoneNumber || !msg) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Required Fields',
                                    text: 'Please fill in phone number and message.',
                                    confirmButtonColor: '#3085d6'
                                });
                                return;
                            }

                            // Show confirmation
                            Swal.fire({
                                icon: 'question',
                                title: 'Confirm SMS Sending',
                                html: `<p><strong>To:</strong> ${phoneNumber}</p>
                                <p><strong>Message:</strong></p>
                                <div class="border p-2 rounded bg-light" style="max-height:100px; overflow-y:auto;">
                                ${msg}</div>
                                <small class="text-muted">SMS charges may apply</small>`,
                                showCancelButton: true,
                                confirmButtonColor: '#28a745',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Send SMS',
                                cancelButtonText: 'Cancel'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Show loading
                                    sendBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Sending...';
                                    sendBtn.disabled = true;

                                    fetch(form.action, {
                                        method: 'POST',
                                        body: new FormData(form),
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                        }
                                    })
                                        .then(res => res.json())
                                        .then(data => {
                                            if (data.success) {
                                                Swal.fire({
                                                    toast: true,
                                                    position: 'top-end',
                                                    icon: 'success',
                                                    title: 'SMS sent successfully!',
                                                    showConfirmButton: false,
                                                    timer: 3000,
                                                    timerProgressBar: true
                                                });

                                                form.reset();
                                                updateCharacterCount();

                                                // Hide modal
                                                const modalInstance = bootstrap.Modal.getInstance(document.getElementById('sendMessageModal'));
                                                modalInstance.hide();
                                            } else {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Failed to Send SMS',
                                                    text: data.message || 'Unknown error occurred'
                                                });
                                            }
                                        })
                                        .catch(err => {
                                            console.error(err);
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Connection Error',
                                                text: 'Could not send SMS. Check your internet connection.'
                                            });
                                        })
                                        .finally(() => {
                                            sendBtn.innerHTML = originalText;
                                            sendBtn.disabled = false;
                                        });
                                }
                            });
                        });
                    }

                    // Modal shown event
                    const sendMessageModal = document.getElementById('sendMessageModal');
                    if (sendMessageModal) {
                        sendMessageModal.addEventListener('shown.bs.modal', function () {
                            updateCharacterCount();
                        });
                    }
                });
            </script>

            <script>
                // Next of Kin Management
                $(document).ready(function() {
                    const customerId = "{{ Hashids::encode($customer->id) }}";
                    const addModal = new bootstrap.Modal(document.getElementById('addNextOfKinModal'));
                    let isEditMode = false;
                    let editingKinId = null;

                    // Reset form when modal is closed
                    $('#addNextOfKinModal').on('hidden.bs.modal', function() {
                        $('#nextOfKinForm')[0].reset();
                        $('#nextOfKinId').val('');
                        $('#addNextOfKinModalLabel').text('Add Next of Kin');
                        isEditMode = false;
                        editingKinId = null;
                    });

                    // Handle edit button click
                    $(document).on('click', '.edit-kin-btn', function() {
                        isEditMode = true;
                        editingKinId = $(this).data('kin-id');
                        $('#nextOfKinId').val(editingKinId);
                        $('#addNextOfKinModalLabel').text('Edit Next of Kin');
                        
                        $('#kinName').val($(this).data('kin-name'));
                        $('#kinRelationship').val($(this).data('kin-relationship'));
                        $('#kinPhone').val($(this).data('kin-phone'));
                        $('#kinEmail').val($(this).data('kin-email'));
                        $('#kinAddress').val($(this).data('kin-address'));
                        $('#kinIdType').val($(this).data('kin-id-type'));
                        $('#kinIdNumber').val($(this).data('kin-id-number'));
                        $('#kinDob').val($(this).data('kin-dob'));
                        $('#kinGender').val($(this).data('kin-gender'));
                        $('#kinNotes').val($(this).data('kin-notes'));
                        
                        addModal.show();
                    });

                    // Handle delete button click
                    $(document).on('click', '.delete-kin-btn', function() {
                        const kinId = $(this).data('kin-id');
                        const kinName = $(this).data('kin-name');
                        
                        Swal.fire({
                            title: 'Are you sure?',
                            text: `Do you want to delete "${kinName}" from next of kin?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: `/customers/${customerId}/next-of-kin/${kinId}`,
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function(response) {
                                        Swal.fire('Deleted!', response.message, 'success');
                                        location.reload();
                                    },
                                    error: function(xhr) {
                                        Swal.fire('Error!', xhr.responseJSON?.error || 'Failed to delete next of kin', 'error');
                                    }
                                });
                            }
                        });
                    });

                    // Handle form submission
                    $('#nextOfKinForm').on('submit', function(e) {
                        e.preventDefault();
                        
                        const formData = new FormData(this);
                        let url, method;
                        
                        if (isEditMode && editingKinId) {
                            url = `/customers/${customerId}/next-of-kin/${editingKinId}`;
                            method = 'PUT';
                            formData.append('_method', 'PUT');
                        } else {
                            url = `/customers/${customerId}/next-of-kin`;
                            method = 'POST';
                        }
                        
                        $.ajax({
                            url: url,
                            method: method,
                            data: formData,
                            processData: false,
                            contentType: false,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                Swal.fire('Success!', response.message, 'success');
                                addModal.hide();
                                location.reload();
                            },
                            error: function(xhr) {
                                let errorMessage = 'Failed to save next of kin';
                                if (xhr.responseJSON && xhr.responseJSON.errors) {
                                    const errors = Object.values(xhr.responseJSON.errors).flat();
                                    errorMessage = errors.join('<br>');
                                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                                    errorMessage = xhr.responseJSON.error;
                                }
                                Swal.fire('Error!', errorMessage, 'error');
                            }
                        });
                    });
                });
            </script>
        @endpush
