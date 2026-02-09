{{-- resources/views/backend/partials/navbar.blade.php --}}
{{-- Always-visible Sidebar (no backdrop/toggle) + Desktop push + Centered Logo (hidden on mobile) --}}

@php
    use App\Models\Information;

    // Site info + logo
    $info = Information::first();
    $logoUrl = $info && !empty($info->site_logo) ? asset('uploads/img/' . $info->site_logo) : null;

    // Role flag (Spatie)
    $isWorker = auth()->check() && method_exists(auth()->user(), 'hasRole') ? auth()->user()->hasRole('worker') : false;

    // Active route helper
    if (!function_exists('nav_active')) {
        function nav_active($patterns = [])
        {
            foreach ((array) $patterns as $p) {
                if (request()->routeIs($p) || request()->is($p)) {
                    return 'is-active';
                }
            }
            return '';
        }
    }
@endphp

<aside id="appSidebar" class="sidebar">
    {{-- ===== Brand / Logo (desktop only) ===== --}}
    <div class="sidebar__brand">
        <a href="{{ route('admin.dashboard') }}" class="brand-link" aria-label="Dashboard">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $info->site_name ?? 'Admin' }} Logo" class="brand-logo">
            @else
                <i class="uil uil-estate"></i>
                <span class="brand-text">{{ $info->site_name ?? 'Admin Panel' }}</span>
            @endif
        </a>
    </div>

    {{-- ===== Sidebar Menu ===== --}}
    <nav class="sidebar__nav">
        <ul class="side-nav">

            {{-- Dashboard (সবাই) --}}
            <li class="side-nav-item">
                <a href="{{ route('admin.dashboard') }}" class="side-nav-link {{ nav_active(['admin.dashboard']) }}">
                    <i class="uil uil-home-alt"></i>
                    <span> Dashboard </span>
                </a>
            </li>

            {{-- Orders Manage (Worker পুরো দেখবে) --}}
            @php $ordersOpen = request()->is('admin/orders*') ? 'show' : ''; @endphp
            @if ($isWorker || auth()->user()->can('order.view') || auth()->user()->can('permission.view'))
                <li class="side-nav-item">
                    <button class="side-nav-link has-arrow" data-bs-toggle="collapse" data-bs-target="#orders"
                        aria-expanded="{{ $ordersOpen ? 'true' : 'false' }}">
                        <i class="uil uil-folder-plus"></i>
                        <span> Orders Manage</span>
                        <i class="uil uil-angle-right menu-arrow"></i>
                    </button>
                    <div class="collapse {{ $ordersOpen }}" id="orders">
                        <ul class="side-nav-second-level">
                            @if ($isWorker || auth()->user()->can('order.view'))
                                @foreach (getOrderStatus(1) as $key => $item)
                                    <li>
                                        <a href="{{ url('admin/orders?q=&status=' . $key) }}"
                                            class="{{ request('status') === (string) $key ? 'is-active' : '' }}">
                                            <i class="uil uil-list-ul"></i>
                                            <span>{{ $item }} Order</span>
                                        </a>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </li>
            @endif

            @if (auth()->user()->can('incomplete_order.view'))
                <li class="side-nav-item">
                    <a href="{{ route('admin.incomplete_orders.index') }}" class="side-nav-link {{ nav_active(['admin.incomplete_orders.*']) }}">
                        <i class="uil uil-folder-plus"></i>
                        <span> Incomplete Orders </span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->can('product.view') ||
                    auth()->user()->can('type.view') ||
                    auth()->user()->can('size.view') ||
                    auth()->user()->can('category.view') ||
                    auth()->user()->can('discount.view') ||
                    auth()->user()->can('color.view'))
                @php
                    $productsOpen =
                        request()->is('admin/types*') ||
                        request()->is('admin/categories*') ||
                        request()->is('admin/sizes*') ||
                        request()->is('admin/colors*') ||
                        request()->is('admin/products*') ||
                        request()->is('admin/free-shipping*') ||
                        request()->is('admin/landing-pages-two*')
                            ? 'show'
                            : '';
                @endphp
                <li class="side-nav-item">
                    <button class="side-nav-link has-arrow" data-bs-toggle="collapse" data-bs-target="#products"
                        aria-expanded="{{ $productsOpen ? 'true' : 'false' }}">
                        <i class="uil uil-table"></i>
                        <span> Products </span>
                        <i class="uil uil-angle-right menu-arrow"></i>
                    </button>
                    <div class="collapse {{ $productsOpen }}" id="products">
                        <ul class="side-nav-second-level">
                            @if (auth()->user()->can('type.view'))
                                <li>
                                    <a href="{{ route('admin.types.index') }}"
                                        class="{{ nav_active(['admin.types.*']) }}">
                                        <i class="uil uil-bright"></i>
                                        <span> Brand Manage </span>
                                    </a>
                                </li>
                            @endif

                            @if (auth()->user()->can('category.view'))
                                <li>
                                    <a href="{{ route('admin.categories.index') }}"
                                        class="{{ nav_active(['admin.categories.*']) }}">
                                        <i class="uil uil-books"></i>
                                        <span> Category Manage </span>
                                    </a>
                                </li>
                            @endif

                            {{-- <li>
                                <a href="{{ route('admin.homecat') }}" class="{{ nav_active(['admin.homecat']) }}">
                                    <i class="uil uil-home"></i>
                                    <span> Home Category Manage </span>
                                </a>
                            </li> --}}

                            {{-- @if (auth()->user()->can('size.view'))
                                <li>
                                    <a href="{{ route('admin.sizes.index') }}"
                                        class="{{ nav_active(['admin.sizes.*']) }}">
                                        <i class="uil uil-ruler"></i>
                                        <span> Size Manage </span>
                                    </a>
                                </li>
                            @endif --}}

                            {{-- @if (auth()->user()->can('color.view'))
                                <li>
                                    <a href="{{ route('admin.colors.index') }}"
                                        class="{{ nav_active(['admin.colors.*']) }}">
                                        <i class="uil uil-palette"></i>
                                        <span> Color Manage </span>
                                    </a>
                                </li>
                            @endif --}}

                            @if (auth()->user()->can('product.view'))
                                <li>
                                    <a href="{{ route('admin.products.index') }}"
                                        class="{{ nav_active(['admin.products.*']) }}">
                                        <i class="uil uil-box"></i>
                                        <span> Products Manage</span>
                                    </a>
                                </li>
                            @endif

                            {{-- <li>
                                <a href="{{ route('admin.free_shipping') }}"
                                    class="{{ nav_active(['admin.free_shipping']) }}">
                                    <i class="uil uil-truck"></i>
                                    <span> Free Shipping Manage</span>
                                </a>
                            </li> --}}

                            {{-- <li>
                                <a href="{{ route('admin.landing_pages_two') }}"
                                    class="{{ nav_active(['admin.landing_pages_two']) }}">
                                    <i class="uil uil-apps"></i>
                                    <span>Manage Landing Page</span>
                                </a>
                            </li> --}}
                        </ul>
                    </div>
                </li>
            @endif

            {{-- Reviews (Worker হলে hidden) --}}
            {{-- @unless ($isWorker)
      <li class="side-nav-item">
        <a href="{{ route('admin.reviews.index')}}" class="side-nav-link {{ nav_active(['admin.reviews.*']) }}">
          <i class="uil uil-comment-alt-dots"></i>
          <span> Reviews</span>
        </a>
      </li>
      @endunless --}}

            {{-- Front Page (আগের মত) --}}
            @if (auth()->user()->can('page.view') ||
                    auth()->user()->can('image.view') ||
                    auth()->user()->can('slider.view') ||
                    auth()->user()->can('honey_landing_page.view'))
                @php $frontOpen = request()->is('admin/pages*') || request()->is('admin/sliders*') || request()->is('admin/honey_landing_pages*') ? 'show' : ''; @endphp
                <li class="side-nav-item">
                    <button class="side-nav-link has-arrow" data-bs-toggle="collapse" data-bs-target="#front_page"
                        aria-expanded="{{ $frontOpen ? 'true' : 'false' }}">
                        <i class="uil uil-monitor"></i>
                        <span> Front Page </span>
                        <i class="uil uil-angle-right menu-arrow"></i>
                    </button>
                    <div class="collapse {{ $frontOpen }}" id="front_page">
                        <ul class="side-nav-second-level">
                            @if (auth()->user()->can('page.view'))
                                {{-- <li>
                                    <a href="{{ route('admin.pages.index') }}"
                                        class="{{ nav_active(['admin.pages.*']) }}">
                                        <i class="uil uil-notes"></i>
                                        <span>Manage Page Data</span>
                                    </a>
                                </li> --}}
                            @endif

                            @if (auth()->user()->can('slider.view'))
                                {{-- <li>
                                    <a href="{{ route('admin.sliders.index') }}"
                                        class="{{ nav_active(['admin.sliders.*']) }}">
                                        <i class="uil uil-sliders-v"></i>
                                        <span> Slider Manage </span>
                                    </a>
                                </li> --}}
                            @endif

                            @if (auth()->user()->can('honey_landing_page.view'))
                                <li>
                                    <a href="{{ route('admin.honey_landing_pages.index') }}"
                                        class="{{ nav_active(['admin.honey_landing_pages.*']) }}">
                                        <i class="uil uil-honey"></i>
                                        <span> Honey CMS </span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif

            {{-- Delivery Charge / Courier Manage (আগের মত) --}}
            @if (auth()->user()->can('delivery_charge.view'))
                <li class="side-nav-item">
                    <a href="{{ route('admin.delivery_charge.index') }}"
                        class="side-nav-link {{ nav_active(['admin.delivery_charge.*']) }}">
                        <i class="uil uil-bill"></i>
                        <span> Delivery Charge </span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->can('payment_method.view') || true)
                {{-- Temporary permission bypass or add permission later --}}
                <li class="side-nav-item">
                    <a href="{{ route('admin.payment-methods.index') }}"
                        class="side-nav-link {{ nav_active(['admin.payment-methods.*']) }}">
                        <i class="uil uil-money-bill"></i>
                        <span> Payment Methods </span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->can('couriers.view'))
                <li class="side-nav-item">
                    <a href="{{ route('admin.couriers.index') }}"
                        class="side-nav-link {{ nav_active(['admin.couriers.*']) }}">
                        <i class="uil uil-truck-loading"></i>
                        <span> Courier Manage </span>
                    </a>
                </li>
            @endif

            {{-- Users (আগের মত) --}}
            @if (auth()->user()->can('combo.view') || auth()->user()->can('permission.view') || auth()->user()->can('role.view'))
                @php $usersOpen = request()->is('admin/users*') ? 'show' : ''; @endphp
                <li class="side-nav-item">
                    <button class="side-nav-link has-arrow" data-bs-toggle="collapse" data-bs-target="#user"
                        aria-expanded="{{ $usersOpen ? 'true' : 'false' }}">
                        <i class="uil uil-users-alt"></i>
                        <span> Users </span>
                        <i class="uil uil-angle-right menu-arrow"></i>
                    </button>
                    <div class="collapse {{ $usersOpen }}" id="user">
                        <ul class="side-nav-second-level">
                            @if (auth()->user()->can('user.view'))
                                <li>
                                    <a href="{{ route('admin.users.index') }}"
                                        class="{{ nav_active(['admin.users.*']) }}">
                                        <i class="uil uil-user"></i>
                                        <span>Manage User</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif

            {{-- Reports: worker → শুধুমাত্র User Report --}}
            @php $reportsOpen = request()->is('admin/report/*') || request()->is('admin/expenses*') || request()->is('admin/ipblock*') ? 'show' : ''; @endphp
            @if ($isWorker || auth()->user()->can('order.view') || auth()->user()->can('permission.view'))
                <li class="side-nav-item">
                    <button class="side-nav-link has-arrow" data-bs-toggle="collapse" data-bs-target="#reports"
                        aria-expanded="{{ $reportsOpen ? 'true' : 'false' }}">
                        <i class="uil uil-chart-line"></i>
                        <span> Reports </span>
                        <i class="uil uil-angle-right menu-arrow"></i>
                    </button>
                    <div class="collapse {{ $reportsOpen }}" id="reports">
                        <ul class="side-nav-second-level">
                            @if ($isWorker)
                                <li>
                                    <a href="{{ route('admin.report.user') }}"
                                        class="{{ nav_active(['admin.report.user']) }}">
                                        <i class="uil uil-file-graph"></i>
                                        <span>User Report</span>
                                    </a>
                                </li>
                            @else
                                @if (auth()->user()->can('user.view'))
                                    <li>
                                        <a href="{{ route('admin.report.order') }}"
                                            class="{{ nav_active(['admin.report.order']) }}">
                                            <i class="uil uil-clipboard-notes"></i>
                                            <span>Order Report</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.expenses.index') }}"
                                            class="{{ nav_active(['admin.expenses.*']) }}">
                                            <i class="uil uil-money-withdrawal"></i>
                                            <span>Expense</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.report.user') }}"
                                            class="{{ nav_active(['admin.report.user']) }}">
                                            <i class="uil uil-file-graph"></i>
                                            <span>User Report</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.ipblock') }}"
                                            class="{{ nav_active(['admin.ipblock']) }}">
                                            <i class="uil uil-shield-slash"></i>
                                            <span>Block Ip</span>
                                        </a>
                                    </li>
                                @endif
                            @endif
                        </ul>
                    </div>
                </li>
            @endif

            {{-- Settings (admin only) --}}
            @if (auth()->user()->can('product.delete'))
                <li class="side-nav-item">
                    <a href="{{ route('admin.settings.index') }}"
                        class="side-nav-link {{ nav_active(['admin.settings.*']) }}">
                        <i class="dripicons-gear noti-icon"></i>
                        <span> Settings Manage </span>
                    </a>
                </li>
            @endif

        </ul>
    </nav>
</aside>

{{-- ===== Styles specific to sidebar ===== --}}
<style>
    :root {
        --sb-w: 240px;
        /* Desktop sidebar width */
        --sb-bg: #111827;
        --sb-card: #131a2a;
        --sb-text: #e6e8ee;
        --sb-muted: #a8b3cf;
        --sb-accent: #6aa9ff;
        --sb-border: #1f2937;
    }

    /* Sidebar base (always visible) */
    .sidebar {
        background: var(--sb-bg);
        color: var(--sb-text);
        border-right: 1px solid var(--sb-border);
        width: var(--sb-w);
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        z-index: 1045;
        display: flex;
        flex-direction: column;
    }

    /* Desktop: push content so nothing gets cut */
    @media (min-width: 992px) {
        body.with-sidebar {
            padding-left: var(--sb-w);
        }
    }

    /* Mobile responsive behavior is handled in app.blade.php */


    /* ===== Brand area (desktop only) ===== */
    .sidebar__brand {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        /* নিচে নামাতে constraint ফ্রি */
        flex-direction: column;
        padding: 60px 10px 10px;
        /* top 60px → আরও নিচে */
        border-bottom: 1px solid var(--sb-border);
    }

    .brand-link {
        text-decoration: none;
        color: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    /* ===== Perfect Centered Logo (Desktop + Mobile) ===== */
    .sidebar__brand {
        display: flex;
        align-items: center;
        justify-content: center;
        /* ⭐ Logo perfect center */
        padding: 100px 10px 10px;
        width: 100%;
    }

    /* Logo scaling override – no distortion, always centered */
    .brand-logo {
        display: block;
        height: 55px !important;
        /* Logo size control */
        width: auto !important;
        object-fit: contain;
        margin: 0 auto;
        /* Extra guarantee for centering */
    }


    .brand-text {
        font-weight: 700;
        letter-spacing: .3px;
    }

    /* ===== Menu ===== */
    .sidebar__nav {
        padding: 12px 10px 24px;
        overflow-y: auto;
    }

    @media (max-width: 991.98px) {
        .sidebar__nav {
            overflow: visible;
        }
    }

    .side-nav {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .side-nav-item {
        margin: 6px 0;
    }

    .side-nav-link {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 10px;
        border: 0;
        background: transparent;
        color: var(--sb-text);
        text-decoration: none;
        padding: 12px 12px;
        border-radius: 12px;
        transition: background .2s ease, color .2s ease;
        text-align: left;
    }

    .side-nav-link .uil {
        font-size: 20px;
        color: var(--sb-muted);
    }

    .side-nav-link:hover {
        background: var(--sb-card);
        color: #fff;
    }

    .side-nav-link:hover .uil {
        color: var(--sb-accent);
    }

    .side-nav-link.is-active,
    .side-nav-second-level a.is-active {
        background: linear-gradient(180deg, rgba(106, 169, 255, .18), rgba(106, 169, 255, .06));
        border: 1px solid rgba(106, 169, 255, .25);
        color: #fff;
    }

    .side-nav-link.is-active .uil {
        color: var(--sb-accent);
    }

    .has-arrow {
        cursor: pointer;
    }

    .menu-arrow {
        margin-left: auto;
        transition: transform .25s ease;
        color: var(--sb-muted);
    }

    button[aria-expanded="true"] .menu-arrow {
        transform: rotate(90deg);
    }

    .side-nav-second-level {
        list-style: none;
        padding: 6px 0 6px 14px;
    }

    .side-nav-second-level li a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        margin: 4px 0;
        color: var(--sb-muted);
        text-decoration: none;
        border-radius: 10px;
    }

    .side-nav-second-level li a:hover {
        background: #11182a;
        color: #fff;
    }

    .side-nav-second-level .uil {
        font-size: 18px;
    }
</style>
