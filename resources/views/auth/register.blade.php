@extends('frontend.app')

@section('content')
<main class="main-wrapper auth-page">
    <style>
        .auth-page {
            background: radial-gradient(circle at top, #e0f2fe 0, #f9fafb 30%, #ffffff 70%);
        }

        .axil-checkout-area {
            padding: 70px 0;
        }

        .auth-card {
            border-radius: 22px;
            overflow: hidden;
            border: 0;
            box-shadow: 0 22px 55px rgba(15, 23, 42, 0.14);
            background: #ffffff;
        }

        .auth-header {
            background: #041e3a;
            color: #ffffff;
            padding: 24px 22px;
            text-align: center;
        }

        .auth-header .auth-title {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .auth-header p {
            font-size: 13px;
            opacity: .9;
            margin: 0;
        }

        .auth-body {
            padding: 22px 22px 20px;
            background: #f9fafb;
        }

        .auth-inner {
            background: #ffffff;
            border-radius: 16px;
            padding: 18px 18px 16px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-group span {
            color: #ef4444;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: #9ca3af;
        }

        .input-wrapper input {
            width: 100%;
            border-radius: 999px;
            padding: 10px 14px 10px 38px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            font-size: 14px;
            transition: all .18s ease;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #041e3a;
            background: #ffffff;
            box-shadow: 0 0 0 .15rem rgba(4, 30, 58, .18);
        }

        .auth-btn {
            width: 100%;
            border-radius: 999px;
            padding: 12px 14px;
            font-weight: 600;
            border: none;
            font-size: 15px;
            background: #041e3a;
            color: white;
            margin-top: 4px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            transition: .18s ease;
        }

        .auth-btn:hover {
            filter: brightness(1.05);
            box-shadow: 0 16px 34px rgba(4, 30, 58, .40);
            transform: translateY(-1px);
        }

        .auth-footer-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 6px;
            font-size: 12px;
        }

        .auth-footer-links a {
            text-decoration: underline;
            color: #6b7280;
        }

        .auth-footer-links a:hover {
            color: #111827;
        }

        .auth-login-link {
            text-align: center;
            font-size: 12px;
            margin-top: 10px;
            color: #6b7280;
        }

        .auth-login-link a {
            color: #041e3a;
            font-weight: 600;
        }

        .invalid-feedback {
            display: block;
            font-size: 12px;
            margin-top: 3px;
            color: #dc2626;
        }

        @media (max-width: 767.98px){
            .axil-checkout-area {
                padding: 40px 0;
            }
            .auth-body {
                padding: 18px 14px 16px;
            }
            .auth-inner {
                padding: 14px 12px 12px;
            }
        }
    </style>

    <!-- Start Register Area  -->
    <div class="axil-checkout-area">
        <div class="container">
            <form action="{{ route('front.register') }}" method="POST" id="ajax_form">
                @csrf

                <div class="row justify-content-center">
                    <div class="col-lg-6 col-md-8">

                        <div class="card auth-card">
                            <div class="auth-header">
                                <div class="auth-title">Sign Up</div>
                                
                            </div>

                            <div class="auth-body">
                                <div class="auth-inner">

                                    {{-- Global error (for non-AJAX fallback) --}}
                                    @if ($errors->any())
                                        <div class="alert alert-danger py-2 px-3 mb-3" style="border-radius:10px;">
                                            <small>{{ $errors->first() }}</small>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>First Name <span>*</span></label>
                                                <div class="input-wrapper">
                                                    <i class="fas fa-user"></i>
                                                    <input type="text" id="first-name" placeholder="Adam"
                                                           name="first_name" value="{{ old('first_name') }}">
                                                </div>
                                                @error('first_name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Last Name <span>*</span></label>
                                                <div class="input-wrapper">
                                                    <i class="fas fa-user"></i>
                                                    <input type="text" id="last-name" placeholder="John"
                                                           name="last_name" value="{{ old('last_name') }}">
                                                </div>
                                                @error('last_name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Username <span>*</span></label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-id-badge"></i>
                                            <input type="text" name="username" value="{{ old('username') }}">
                                        </div>
                                        @error('username')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Phone <span>*</span></label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-phone-alt"></i>
                                            <input type="tel" id="phone" name="mobile"
                                                   value="{{ old('mobile') }}" placeholder="01XXXXXXXXX">
                                        </div>
                                        @error('mobile')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Email Address <span>*</span></label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-envelope"></i>
                                            <input type="email" id="email" name="email"
                                                   value="{{ old('email') }}" placeholder="example@mail.com">
                                        </div>
                                        @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Password <span>*</span></label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-lock"></i>
                                            <input type="password" id="password" name="password"
                                                   placeholder="Enter Password Here">
                                        </div>
                                        @error('password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Confirm Password <span>*</span></label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-lock"></i>
                                            <input type="password" name="password_confirmation"
                                                   placeholder="Re-Enter Password Here">
                                        </div>
                                    </div>

                               
                                        {{-- চাইলে এখানে Terms / Privacy link দিতে পারো --}}
                                    </div>

                                    <div class="form-group mb-1">
                                        <button type="submit" class="auth-btn">
                                            Create Account <i class="fas fa-arrow-right"></i>
                                        </button>
                                    </div>

                                    <div class="auth-login-link">
                                        Already have an account?
                                        <a href="{{ route('login') }}">Sign In</a>
                                    </div>

                                </div> {{-- /.auth-inner --}}
                            </div> {{-- /.auth-body --}}
                        </div> {{-- /.auth-card --}}

                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- End Register Area  -->

</main>
@endsection

@push('js')
<script src="{{ asset('frontend/js/checkout.js')}}"></script>
@endpush
