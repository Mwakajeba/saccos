@extends('layouts.auth')

@section('title', 'Change Password')

@section('content')
    <div class="authentication-header"></div>
    <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
        <div class="container">
            <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
                <div class="col mx-auto">
                    <div class="card rounded-4">
                        <div class="card-body">
                            <div class="p-4 rounded">
                                <div class="text-center mb-3">
                                    <img src="{{ asset('assets/images/icons/smartfinance.png') }}" width="260" style="max-width:100%;" alt="Logo" />
                                </div>
                                <div class="login-separater text-center mb-3">
                                    <span>FIRST LOGIN - CHANGE PASSWORD</span>
                                    <hr />
                                </div>

                                @if($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <p class="text-muted mb-4">
                                    For security reasons, you must set a new password before accessing the system.
                                </p>

                                <form method="POST" action="{{ route('password.first-login.update') }}" class="row g-3">
                                    @csrf

                                    <div class="col-12">
                                        <label class="form-label">New Password</label>
                                        <div class="input-group" id="first_login_show_hide_password">
                                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                            <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                                            @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="password_confirmation" class="form-control" required>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-lock-open"></i> Change Password &amp; Continue
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var container = document.querySelector('#first_login_show_hide_password');
        if (!container) return;
        var passwordInput = container.querySelector('input[name="password"]');
        var confirmInput = document.querySelector('input[name="password_confirmation"]');
        var toggle = container.querySelector('a');
        var icon = container.querySelector('i');
        if (!passwordInput || !toggle || !icon) return;
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            var newType = (passwordInput.type === 'password') ? 'text' : 'password';
            passwordInput.type = newType;
            if (confirmInput) {
                confirmInput.type = newType;
            }
            if (newType === 'text') {
                icon.classList.remove('bx-hide');
                icon.classList.add('bx-show');
            } else {
                icon.classList.remove('bx-show');
                icon.classList.add('bx-hide');
            }
        });
    });
</script>
@endpush


