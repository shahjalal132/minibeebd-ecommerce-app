<!-- Start Footer Area  -->
@php
    use App\Models\Information;
    $info = Information::first();
@endphp

<footer class="footer-modern main-bg text-light pt-5 pb-4 border-top border-secondary">
    <div class="container">
        {{-- Top row: Logo + Popular Categories + Contact/Social --}}
        <div class="row gy-4 footer-top-row">
            <!-- Logo + short info -->
            <div class="col-md-3 text-center text-md-start">
                <a href="{{ route('front.home') }}" class="footer-logo-link d-inline-flex align-items-center justify-content-center">
                    <img src="{{ asset('uploads/img/'.$info->site_logo)}}" alt="Logo" class="footer-logo-img img-fluid">
                </a>
                @if(!empty($info->address))
                    <p class="footer-tagline mt-3 mb-0 small opacity-75">
                        <i class="fa fa-map-marker-alt me-1"></i> {{ $info->address }}
                    </p>
                @endif
            </div>

            <!-- Popular Categories -->
            <div class="col-md-5 footer-col">
                <h5 class="footer-title mb-2 text-center text-md-start">
                    Popular Categories
                </h5>
                <p class="footer-subtitle small mb-3 text-center text-md-start">
                    Top picks from our best-selling sections
                </p>
                <nav class="footer-links d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                    @foreach(DB::table('categories')->where('is_popular', 1)->take(6)->get() as $cat)
                        <a href="{{ route('front.subCategories1',[$cat->url])}}"
                           class="footer-pill-link">
                            {{ $cat->name}}
                        </a>
                    @endforeach
                </nav>
            </div>

            <!-- Contact + Social -->
            <div class="col-md-4 text-center text-md-end footer-col">
                <h5 class="footer-title mb-2">
                    Contact & Social
                </h5>
                <p class="footer-subtitle small mb-3 text-center text-md-end">
                    Need help? We’re just one call away.
                </p>

                <ul class="list-unstyled small mb-3 footer-contact">
                    @if(!empty($info->owner_phone))
                        <li>
                            <span class="footer-contact-label"><i class="fa fa-phone me-1"></i> Call:</span>
                            <a href="tel:{{ $info->owner_phone }}">{{ $info->owner_phone }}</a>
                        </li>
                    @endif
                    @if(!empty($info->owner_email))
                        <li>
                            <span class="footer-contact-label"><i class="fa fa-envelope me-1"></i> Email:</span>
                            <a href="mailto:{{ $info->owner_email }}">{{ $info->owner_email }}</a>
                        </li>
                    @endif
                </ul>

                <div class="footer-social d-inline-flex flex-wrap justify-content-center justify-content-md-end gap-2">
                    @if(!empty($info->facebook))
                        <a href="{{ $info->facebook }}" target="_blank" class="footer-social-icon">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    @endif
                    @if(!empty($info->youtube))
                        <a href="{{ $info->youtube }}" target="_blank" class="footer-social-icon">
                            <i class="fab fa-youtube"></i>
                        </a>
                    @endif
                    @if(!empty($info->instagram))
                        <a href="{{ $info->instagram }}" target="_blank" class="footer-social-icon">
                            <i class="fab fa-instagram"></i>
                        </a>
                    @endif
                    @if(!empty($info->tiktok))
                        <a href="{{ $info->tiktok }}" target="_blank" class="footer-social-icon">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    @endif
                    @if(!empty($info->twitter))
                        <a href="{{ $info->twitter }}" target="_blank" class="footer-social-icon">
                            <span style="font-family: system-ui;">𝕏</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Second row: Legal + Copyright (desktop + mobile একসাথে সুন্দরভাবে) --}}
        <div class="row mt-4 pt-3 border-top border-secondary-subtle">
            <div class="col-md-8 mb-3 mb-md-0">
                <h6 class="footer-title-small text-center text-md-start mb-2">
                    Legal Pages
                </h6>
                <nav class="footer-links d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                    @foreach(DB::table('pages')->take(6)->get() as $page)
                        <a href="{{ route('front.page.name', $page->page)}}" class="footer-link-underline">
                            {{ $page->title }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="col-md-4 text-center text-md-end">
                <small class="footer-copy d-block mt-3 mt-md-0">
                    {!! $info->copyright !!} 
                    <a href="https://www.facebook.com/bizcareit" target="_blank" rel="noopener noreferrer">
                        Design & Development by Biz Care IT
                    </a>
                </small>
            </div>
        </div>
    </div>
</footer>

{{-- Mobile bottom nav (same) --}}
<style>
    .footer-modern{
        background: radial-gradient(circle at top left, #0f172a 0%, #020617 55%, #000000 100%);
        color:#e5e7eb;
        font-family: 'Hind Siliguri', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        border-top:1px solid transparent;
        border-image: linear-gradient(to right, #22d3ee, #2563eb);
        border-image-slice: 1;
    }
    .footer-modern a{
        color:#e5e7eb;
        text-decoration:none;
        transition: all .2s ease;
    }
    .footer-modern a:hover{
        color:#38bdf8;
        text-decoration:none;
    }

    /* 🔥 Top row alignment – সবকিছু same level */
    .footer-top-row{
        align-items:flex-start !important; /* Popular & Contact same level */
    }
    @media (min-width: 768px){
        .footer-col{
            display:flex;
            flex-direction:column;
            justify-content:flex-start;
        }
    }

    .footer-logo-img{
        max-height:60px;
        width:auto;
        object-fit:contain;
        filter: drop-shadow(0 8px 18px rgba(0,0,0,.45));
    }

    .footer-title{
        font-size:1rem;
        font-weight:600;
        letter-spacing:.04em;
        text-transform:uppercase;
    }

    .footer-subtitle{
        color:#9ca3af;
    }

    .footer-title-small{
        font-size:1rem;
        font-weight:600;
        letter-spacing:.10em;
        text-transform:uppercase;
        opacity:.9;
    }

    .footer-links{
        row-gap:.35rem;
    }

    .footer-pill-link{
        font-size:.9rem;
        padding:.2rem .75rem;
        border-radius:999px;
        background:rgba(15,23,42,.55);
        border:1px solid rgba(148,163,184,.35);
        white-space:nowrap;
        box-shadow:0 10px 25px rgba(15,23,42,.35);
        transform:translateY(0);
        transition: all .2s ease;
    }
    .footer-pill-link:hover{
        background:#0ea5e9;
        border-color:#0ea5e9;
        color:#0b1120;
        transform:translateY(-1px);
        box-shadow:0 16px 32px rgba(15,23,42,.6);
    }

    .footer-link-underline{
        font-size:1rem;
        position:relative;
        padding-bottom:3px;
    }
    .footer-link-underline::after{
        content:"";
        position:absolute;
        left:0;
        bottom:0;
        width:0;
        height:2px;
        background:#38bdf8;
        transition:width .2s;
    }
    .footer-link-underline:hover::after{
        width:100%;
    }

    .footer-contact li{
        margin-bottom:4px;
    }
    .footer-contact-label{
        opacity:.8;
        margin-right:4px;
    }
    .footer-contact a{
        color:#e5e7eb;
        font-weight:500;
    }
    .footer-contact a:hover{
        color:#38bdf8;
    }

    .footer-copy a{
        color:#e5e7eb;
        font-weight:500;
    }
    .footer-copy a:hover{
        color:#38bdf8;
    }

    /* Social icons – round & modern */
    .footer-social-icon{
        width:34px;
        height:34px;
        border-radius:999px;
        border:1px solid rgba(148,163,184,.5);
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:15px;
        background:rgba(15,23,42,.7);
        box-shadow:0 10px 24px rgba(15,23,42,.6);
        transition: all .2s ease;
    }
    .footer-social-icon:hover{
        background:#0ea5e9;
        border-color:#0ea5e9;
        color:#020617;
        transform:translateY(-1px) scale(1.02);
    }

    /* Mobile tweaks */
    @media (max-width: 767.98px){
        .footer-modern{
            text-align:center;
        }
        .footer-modern .footer-title,
        .footer-modern .footer-title-small{
            text-align:center;
        }
        .footer-modern .footer-copy{
            text-align:center;
        }
    }

    /* ===== Mobile Bottom Nav ===== */
    .footer-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background:#ffffff;
        border-top:1px solid #e5e7eb;
        box-shadow:0 -4px 18px rgba(15,23,42,.18);
        z-index: 99;
        display:none;
    }
    .m-nav-main {
        display:flex;
    }
    .button-shop {
        flex:1;
        padding:8px 4px 6px;
        text-align:center;
        font-size:12px;
        font-weight:600;
    }
    .button-shop .footerBtn{
        display:flex;
        flex-direction:column;
        align-items:center;
        gap:2px;
        text-decoration:none;
    }
    .button-shop .footerBtn i{
        font-size:20px;
        color:#1e65b2;
    }
    .button-shop .footerBtn span{
        color:#1e65b2;
    }

    @media (max-width: 575.98px){
        .footer-nav{
            display:block;
        }
        body{
            padding-bottom:60px; /* যাতে bottom nav এর নিচে content লুকায় না */
        }
    }
</style>

<div class="footer-nav d-sm-block d-md-none">
    <div class="m-nav-main">
        <div class="button-shop">
            <a href="{{ route('front.home')}}" class="text-light footerBtn">
                <i class="fa fa-home"></i>
                <span>Home</span>
            </a>
        </div>

        <div class="button-shop">
            <a href="#" class="text-light footerBtn mobile-nav-toggler">
                <i class="fa fa-bars"></i>
                <span>Categories</span>
            </a>
        </div>

        <div class="button-shop">
            <a href="https://wa.me/+88{{ $info->owner_phone }}" class="text-light footerBtn">
                <i class="fab fa-whatsapp"></i>
                <span>WhatsApp</span>
            </a>
        </div>

        <div class="button-shop">
            <a href="tel:{{ $info->owner_phone }}" class="text-light footerBtn">
                <i class="fa fa-phone-volume"></i>
                <span>Call</span>
            </a>
        </div>
    </div>
</div>

<div class="cart-dropdown" id="cart-dropdown"></div>

@include('frontend.partials.js')

</body>
</html>
