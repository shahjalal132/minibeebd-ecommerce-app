{{-- resources/views/backend/dashboard.blade.php --}}
@extends('backend.app')
@section('content')

@push('css')
<style>
  :root{
    --card-radius:16px; --card-shadow:0 8px 24px rgba(0,0,0,.06);
  }
  .page-title-box { margin-bottom:.75rem; }

  /* KPI card */
  .mini-card{
    position:relative; border:0!important; border-radius:var(--card-radius)!important;
    box-shadow:var(--card-shadow);
    transition:transform .15s ease, box-shadow .15s ease;
    overflow:hidden;
  }
  .mini-card:hover{ transform:translateY(-2px); box-shadow:0 10px 28px rgba(0,0,0,.10); }

  .mini-card .card-body{ padding:1rem 1rem; }
  .mini-card .label{ font-size:.9rem; opacity:.95; letter-spacing:.2px;}
  .mini-card .value{ font-weight:700; line-height:1; font-size:clamp(1.5rem,2.8vw,2.25rem); padding-top:.15rem; }
  .mini-card .right{ display:flex; align-items:center; justify-content:flex-end; height:100%; opacity:.35; }
  .mini-card .icon-wrap{ width:44px; height:44px; border-radius:12px; background:rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center; }
  .mini-card .sub{ font-size:.85rem; opacity:.9; margin-top:.15rem; }

  /* Gradient backgrounds */
  .bg-total{background:linear-gradient(135deg,#0EA3AC 0%,#23C9B0 100%);}
  .bg-pending{background:linear-gradient(135deg,#2DAC37 0%,#56D15F 100%);}
  .bg-complete{background:linear-gradient(135deg,#F5BD08 0%,#FFD24D 100%);}
  .bg-cancel{background:linear-gradient(135deg,#C9213B 0%,#E0435B 100%);}
  .bg-sell{background:linear-gradient(135deg,#006AE6 0%,#3A8BFF 100%);}
  .bg-expense{background:linear-gradient(135deg,#6B7280 0%,#94A3B8 100%);}
  .bg-profit{background:linear-gradient(135deg,#10B981 0%,#34D399 100%);}
  .bg-stocks{background:linear-gradient(135deg,#7C3AED 0%,#A78BFA 100%);}

  .mini-card a{ color:#fff; text-decoration:none; display:block; }
  .mini-card h5,.mini-card h3,.mini-card p{ color:#fff; margin:0; }

  /* shimmer skeleton */
  .skeleton{ width:100%; height:28px; border-radius:8px; background:linear-gradient(90deg,#ffffff30 25%,#ffffff55 37%,#ffffff30 63%); background-size:400% 100%; animation:shimmer 1.1s infinite; }
  @keyframes shimmer{ 0%{background-position:100% 0} 100%{background-position:-100% 0} }

  /* glow on hover */
  .mini-card::after{
    content:""; position:absolute; inset:0; pointer-events:none;
    background: radial-gradient(600px circle at var(--x,50%) var(--y,0%), rgba(255,255,255,.18), transparent 40%);
    opacity:0; transition:opacity .25s ease;
  }
  .mini-card:hover::after{ opacity:1; }

  /* trend badge */
  .badge-trend{
    position:absolute; top:10px; right:10px;
    background:rgba(255,255,255,.2); backdrop-filter: blur(4px);
    font-weight:600; border:1px solid rgba(255,255,255,.25);
  }

  /* quick pills */
  .quick-range .btn{
    border-radius:999px; padding:.35rem .75rem; font-weight:600; font-size:.875rem;
  }
  .quick-range .btn.active{ box-shadow: inset 0 0 0 2px rgba(255,255,255,.7); }
.bg-report {
  background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
  color: #111827; /* black tone */
  border: none;
  box-shadow: 0 10px 24px rgba(59, 130, 246, 0.25);
  transition: all 0.3s ease;
}
.bg-report:hover {
  transform: translateY(-4px);
  box-shadow: 0 15px 32px rgba(59, 130, 246, 0.35);
}

  /* layout gaps */
  .gy-12{ row-gap:12px; }
  @media (min-width:768px){ .gy-md-16{ row-gap:16px; } }

  /* empty state (partial) */
  .empty-state{ border:1px dashed #e5e7eb; border-radius:14px; padding:28px; text-align:center; color:#6b7280; }
</style>
@endpush

@php
  $today      = date('Y-m-d');
  $userStart  = optional(auth()->user()->created_at)->format('Y-m-d') ?? $today; // ইউজারের অ্যাকাউন্ট তৈরির দিন
  $endDateUi  = request('endDate', $today);
@endphp

<!-- Title -->
<div class="row">
  <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right d-none d-md-block">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript:void(0)">Hyper</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
          <li class="breadcrumb-item active">Overview</li>
        </ol>
      </div>
      <h4 class="page-title">Hello {{ auth()->user()->first_name.' '.auth()->user()->last_name }}</h4>
    </div>
  </div>
</div>

<!-- Filters -->
<div class="row mb-2 align-items-end">
  <div class="col-12 col-md-10">
    <form action="{{ route('admin.dashboard') }}" method="GET" class="row g-2">
      <div class="col-6 col-md-4">
        <label class="form-label mb-1">Start Date</label>
        <input type="date" class="form-control" name="startDate"
               value="{{ request('startDate', $userStart) }}"
               min="{{ $userStart }}">
      </div>
      <div class="col-6 col-md-4">
        <label class="form-label mb-1">End Date</label>
        <input type="date" class="form-control" name="endDate"
               value="{{ $endDateUi }}"
               min="{{ $userStart }}">
      </div>
    </form>
    <div class="col-12 mt-2">
      <div class="quick-range btn-group" role="group" aria-label="Quick ranges">
        <button class="btn btn-outline-secondary btn-sm" data-range="today">Today</button>
        <button class="btn btn-outline-secondary btn-sm" data-range="7d">Last 7D</button>
        <button class="btn btn-outline-secondary btn-sm" data-range="mtd">MTD</button>
        <button class="btn btn-outline-secondary btn-sm" data-range="ytd">YTD</button>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-2 mt-2 mt-md-0">
    <button id="refreshBtn" class="btn btn-primary w-100">
      <i class="uil uil-refresh me-1"></i> Refresh
    </button>
  </div>
</div>

<!-- loader -->
<div class="row d-none" id="loader">
  <div class="col-12 text-center">
    <div class="spinner-border" role="status" aria-label="Loading"></div>
  </div>
</div>

<!-- KPI cards -->
<div id="dashboard_data_top">
  <div class="row gy-12 gy-md-16">

    <!-- Total -->
    <div class="col-6 col-lg-3">
      <div class="card mini-card bg-total" data-card="total">
        <span class="badge badge-trend text-white"><span class="trend-icon">↗</span> <span class="trend-val">0%</span></span>
        <a href="{{ url('admin/orders') }}" class="p-0">
          <div class="card-body" onmousemove="hoverGlow(event, this.parentElement)">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="label">Total Order</div>
                <div class="kpi-value"><span class="value total_orders" data-kpi="total_orders">0</span></div>
                <div class="sub">vs previous period</div>
              </div>
              <div class="right"><div class="icon-wrap"><i class="uil uil-shopping-bag-alt fs-4 text-white"></i></div></div>
            </div>
          </div>
        </a>
      </div>
    </div>

    <!-- Pending -->
    <div class="col-6 col-lg-3">
      <div class="card mini-card bg-pending" data-card="pending">
        <span class="badge badge-trend text-white"><span class="trend-icon">↗</span> <span class="trend-val">0%</span></span>
        <a href="{{ url('admin/orders') }}">
          <div class="card-body" onmousemove="hoverGlow(event, this.parentElement)">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="label">Pending Order</div>
                <div class="kpi-value"><span class="value pending_orders">0</span></div>
                <div class="sub">vs previous period</div>
              </div>
              <div class="right"><div class="icon-wrap"><i class="uil uil-clock fs-4 text-white"></i></div></div>
            </div>
          </div>
        </a>
      </div>
    </div>

    <!-- Complete -->
    <div class="col-6 col-lg-3">
      <div class="card mini-card bg-complete" data-card="complete">
        <span class="badge badge-trend text-white"><span class="trend-icon">↗</span> <span class="trend-val">0%</span></span>
        <a href="{{ url('admin/orders') }}">
          <div class="card-body" onmousemove="hoverGlow(event, this.parentElement)">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="label">Complete Order</div>
                <div class="kpi-value"><span class="value complete_orders">0</span></div>
                <div class="sub">vs previous period</div>
              </div>
              <div class="right"><div class="icon-wrap"><i class="uil uil-check-circle fs-4 text-white"></i></div></div>
            </div>
          </div>
        </a>
      </div>
    </div>

    <!-- Cancel -->
    <div class="col-6 col-lg-3">
      <div class="card mini-card bg-cancel" data-card="cancel">
        <span class="badge badge-trend text-white"><span class="trend-icon">↘</span> <span class="trend-val">0%</span></span>
        <a href="{{ url('admin/orders') }}">
          <div class="card-body" onmousemove="hoverGlow(event, this.parentElement)">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="label">Cancel Order</div>
                <div class="kpi-value"><span class="value cancell_orders">0</span></div>
                <div class="sub">vs previous period</div>
              </div>
              <div class="right"><div class="icon-wrap"><i class="uil uil-times-circle fs-4 text-white"></i></div></div>
            </div>
          </div>
        </a>
      </div>
    </div>

    {{-- Worker লুকাবে --}}
    @unlessrole('worker')
      <!-- Sell -->
      <div class="col-6 col-lg-3">
        <div class="card mini-card bg-sell" data-card="sell">
          <span class="badge badge-trend text-white"><span class="trend-icon">↗</span> <span class="trend-val">0%</span></span>
          <a href="{{ url('admin/products') }}">
            <div class="card-body" onmousemove="hoverGlow(event, this.parentElement)">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="label">Sell Amount</div>
                  <div class="kpi-value"><span class="value sell_amount">৳0</span></div>
                  <div class="sub">vs previous period</div>
                </div>
                <div class="right"><div class="icon-wrap"><i class="uil uil-chart-line fs-4 text-white"></i></div></div>
              </div>
            </div>
          </a>
        </div>
      </div>

      <!-- Expense -->
      <div class="col-6 col-lg-3">
        <div class="card mini-card bg-expense" data-card="expense">
          <span class="badge badge-trend text-white"><span class="trend-icon">↘</span> <span class="trend-val">0%</span></span>
          <a href="{{ url('admin/products') }}">
            <div class="card-body" onmousemove="hoverGlow(event, this.parentElement)">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="label">Expense</div>
                  <div class="kpi-value"><span class="value total_expense">৳0</span></div>
                  <div class="sub">vs previous period</div>
                </div>
                <div class="right"><div class="icon-wrap"><i class="uil uil-wallet fs-4 text-white"></i></div></div>
              </div>
            </div>
          </a>
        </div>
      </div>

      <!-- Profit -->
      <div class="col-6 col-lg-3">
        <div class="card mini-card bg-profit" data-card="profit">
          <span class="badge badge-trend text-white"><span class="trend-icon">↗</span> <span class="trend-val">0%</span></span>
          <a href="{{ url('admin/orders') }}">
            <div class="card-body" onmousemove="hoverGlow(event, this.parentElement)">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="label">Net Profit</div>
                  <div class="kpi-value"><span class="value total_net_profit">৳0</span></div>
                  <div class="sub">vs previous period</div>
                </div>
                <div class="right"><div class="icon-wrap"><i class="uil uil-money-bill fs-4 text-white"></i></div></div>
              </div>
            </div>
          </a>
        </div>
      </div>

      <!-- Stocks -->
      <div class="col-6 col-lg-3">
        <div class="card mini-card bg-stocks" data-card="stocks">
          <div class="card-body" onmousemove="hoverGlow(event, this.parentElement)">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="label">Current Stocks</div>
                <div class="kpi-value"><span class="value">{{ number_format($total_stocks ?? 0) }}</span></div>
                <div class="sub">as of today</div>
              </div>
              <div class="right"><div class="icon-wrap"><i class="uil uil-box fs-4 text-white"></i></div></div>
            </div>
          </div>
        </div>
      </div>
    @endunlessrole
  </div>
</div>
@if($isWorker)
  <!-- View Report -->
  <div class="col-6 col-lg-3">
    <div class="card mini-card bg-report" data-card="report">
      <span class="badge badge-trend text-dark">
        <span class="trend-icon">📊</span> 
        <span class="trend-val">View</span>
      </span>
      <a href="{{ url('admin/user-report') }}">
        <div class="card-body" onmousemove="hoverGlow(event, this.parentElement)">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="label text-dark fw-bold">My Report</div>
              <div class="kpi-value">
                <span class="value text-dark fw-semibold">Go to Report</span>
              </div>
              <div class="sub text-muted">Your performance details</div>
            </div>
            <div class="right">
              <div class="icon-wrap">
                <i class="uil uil-file-alt fs-4 text-dark"></i>
              </div>
            </div>
          </div>
        </div>
      </a>
    </div>
  </div>
@endif


<!-- নিচের পার্শিয়াল -->
<div class="mt-3" id="dashboard_data"></div>
@endsection

@push('js')
<script>
  window.isWorker = @json(auth()->user()->hasRole('worker'));

  /* ===== Hover glow ===== */
  function hoverGlow(e, card){
    const r = card.getBoundingClientRect();
    card.style.setProperty('--x', (e.clientX - r.left) + 'px');
    card.style.setProperty('--y', (e.clientY - r.top)  + 'px');
  }

  /* ===== debounce ===== */
  function debounce(fn, delay=350){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a),delay); }; }

  /* ===== Number formatters ===== */
  const nf = new Intl.NumberFormat('en-BD', { maximumFractionDigits: 0 });
  const nfMoney = new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT', maximumFractionDigits:0 });

  /* ===== Count-up animation ===== */
  function animateCount(el, to, isMoney=false, duration=900){
    const start = 0;
    const startTime = performance.now();
    function frame(now){
      const p = Math.min((now - startTime) / duration, 1);
      const val = Math.floor(start + (to - start)*p);
      el.textContent = isMoney ? nfMoney.format(val) : nf.format(val);
      if(p < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  /* ===== skeleton reset ===== */
  function skeletonAll(){
    ['.total_orders','.pending_orders','.complete_orders','.cancell_orders',
     '.sell_amount','.total_expense','.total_net_profit'
    ].forEach(cls=>{
      const el = document.querySelector(cls);
      if(el){ el.classList.add('skeleton'); el.textContent = ' '; }
    });
  }

  /* ===== Trend badge ===== */
  function setTrend(cardSel, current, previous, positiveGood=true){
    const card = document.querySelector(cardSel);
    if(!card) return;
    const badge = card.querySelector('.badge-trend');
    const icon  = badge.querySelector('.trend-icon');
    const valEl = badge.querySelector('.trend-val');

    let pct = 0;
    if(previous > 0){
      pct = ((current - previous) / previous) * 100;
    }else if(current > 0){
      pct = 100;
    }
    const rounded = Math.round(pct);

    valEl.textContent = (rounded>0? '+' : '') + rounded + '%';
    const upIsGood = positiveGood ? rounded >= 0 : rounded < 0;

    if(upIsGood){
      icon.textContent = '↗';
      badge.classList.remove('bg-danger'); badge.classList.add('bg-success','text-white');
    }else{
      icon.textContent = '↘';
      badge.classList.remove('bg-success'); badge.classList.add('bg-danger','text-white');
    }
  }

  /* ===== Date helpers for quick ranges ===== */
  function formatYMD(d){ return d.toISOString().slice(0,10); }
  function dateDiffDays(a,b){ const A=new Date(a), B=new Date(b); return Math.floor((B-A)/(1000*60*60*24))+1; }
  function shiftBack(sd, ed){
    const len = dateDiffDays(sd, ed);
    const s = new Date(sd); s.setDate(s.getDate()-len);
    const e = new Date(ed); e.setDate(e.getDate()-len);
    return {psd: formatYMD(s), ped: formatYMD(e)};
  }
  function applyRange(range){
    const sd = document.querySelector('input[name="startDate"]');
    const ed = document.querySelector('input[name="endDate"]');
    const today = new Date();
    let s, e = new Date(); // e = today

    if(range==='today'){
      s = new Date();
    }else if(range==='7d'){
      s = new Date(); s.setDate(s.getDate()-6);
    }else if(range==='mtd'){
      s = new Date(today.getFullYear(), today.getMonth(), 1);
    }else if(range==='ytd'){
      s = new Date(today.getFullYear(), 0, 1);
    }else{
      return;
    }
    // min clamp (userStart)
    const minStr = sd.getAttribute('min'); const minDate = new Date(minStr);
    if (s < minDate) s = minDate;

    sd.value = formatYMD(s);
    ed.value = formatYMD(e);

    document.querySelectorAll('.quick-range .btn').forEach(b=>b.classList.remove('active'));
    const btn = document.querySelector(`.quick-range .btn[data-range="${range}"]`);
    if(btn) btn.classList.add('active');

    fetchBoth();
  }

  /* ===== API wrappers ===== */
  function fetchJSON(url){ return fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()); }
  function getRangeDates(){
    const sd = document.querySelector('input[name="startDate"]').value;
    const ed = document.querySelector('input[name="endDate"]').value;
    return {sd, ed};
  }

  function fetchDashboardData(){
    const url = @json(route('admin.getDashboardData'));
    const loader = document.getElementById('loader');
    loader.classList.remove('d-none');
    fetch(url, { headers:{'X-Requested-With':'XMLHttpRequest'} })
      .then(r=>r.text())
      .then(html=>{ document.getElementById('dashboard_data').innerHTML = html; })
      .finally(()=> loader.classList.add('d-none'));
  }

  // current + previous (same-length) fetch
  function fetchBoth(){
    const url = @json(route('admin.getDashboardData2'));
    const {sd, ed} = getRangeDates();
    const {psd, ped} = shiftBack(sd, ed);

    const curUrl = `${url}?startDate=${encodeURIComponent(sd)}&endDate=${encodeURIComponent(ed)}`;
    const prvUrl = `${url}?startDate=${encodeURIComponent(psd)}&endDate=${encodeURIComponent(ped)}`;

    skeletonAll();

    Promise.all([fetchJSON(curUrl), fetchJSON(prvUrl)]).then(([cur, prv])=>{
      // numbers (count-up)
      const sel = (s)=>document.querySelector(s);
      animateCount(sel('.total_orders'),   cur.total_orders||0);
      animateCount(sel('.pending_orders'), cur.pending_orders||0);
      animateCount(sel('.complete_orders'),cur.complete_orders||0);
      animateCount(sel('.cancell_orders'), cur.cancell_orders||0);

      if(!window.isWorker){
        animateCount(sel('.sell_amount'),      cur.sell_amount||0, true);
        animateCount(sel('.total_expense'),    cur.totalExpense||0, true);
        animateCount(sel('.total_net_profit'), cur.profit||0, true);
      }

      // trends
      setTrend('[data-card="total"]',    cur.total_orders||0,    prv.total_orders||0, true);
      setTrend('[data-card="pending"]',  cur.pending_orders||0,  prv.pending_orders||0, false); // কমলে ভালো
      setTrend('[data-card="complete"]', cur.complete_orders||0, prv.complete_orders||0, true);
      setTrend('[data-card="cancel"]',   cur.cancell_orders||0,  prv.cancell_orders||0, false); // কমলে ভালো

      if(!window.isWorker){
        setTrend('[data-card="sell"]',    cur.sell_amount||0,     prv.sell_amount||0, true);
        setTrend('[data-card="expense"]', cur.totalExpense||0,    prv.totalExpense||0, false);
        setTrend('[data-card="profit"]',  cur.profit||0,          prv.profit||0, true);
      }
    });
  }

  /* ===== Bootstrap tooltips in partial ===== */
  function initTooltips(){
    const tEls = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tEls.map(el => new bootstrap.Tooltip(el));
  }

  document.addEventListener('DOMContentLoaded', function(){
    const sd = document.querySelector('input[name="startDate"]');
    const ed = document.querySelector('input[name="endDate"]');

    // min sync
    ed.min = sd.min;

    // clamp end >= start
    function clamp(){ if (ed.value < sd.value) ed.value = sd.value; }

    // init fetches
    fetchBoth();
    if(!window.isWorker){ fetchDashboardData(); }

    // listen date changes
    sd.addEventListener('change', ()=>{ clamp(); fetchBoth(); if(!window.isWorker){ fetchDashboardData(); } });
    ed.addEventListener('change', ()=>{ clamp(); fetchBoth(); if(!window.isWorker){ fetchDashboardData(); } });

    // refresh
    document.getElementById('refreshBtn').addEventListener('click', ()=>{ fetchBoth(); if(!window.isWorker){ fetchDashboardData(); } });

    // quick range
    document.querySelectorAll('.quick-range .btn').forEach(btn=>{
      btn.addEventListener('click', ()=> applyRange(btn.dataset.range));
    });

    // tooltip init for later-loaded partial
    document.addEventListener('shown.bs.tab', initTooltips);
  });
</script>
@endpush
