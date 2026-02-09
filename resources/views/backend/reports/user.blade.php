@extends('backend.app')
@section('content')

@php
  $isWorker = auth()->user()->hasRole('worker');
  $selfId   = auth()->id();
@endphp

<style>
  /* Print tweaks */
  @media print{
    nav, .no-print, .pagination { display:none !important; }
    .card { border:none !important; box-shadow:none !important; }
    .print-header{ display:block !important; margin-bottom:8px; }
    table tbody td{ font-size:12px !important; }
  }
  .toolbar-sticky{ position:sticky; top:0; z-index:6; background:#fff; padding:.75rem 0; border-bottom:1px solid #f0f0f0; }
  .userReport-wrap{ position:relative; }
  .userReport .table-responsive{ overflow-x:auto; }
  .loading-overlay{ position:absolute; inset:0; background:rgba(255,255,255,.6); display:none; align-items:center; justify-content:center; z-index:5; backdrop-filter: blur(1px); }
  .spinner{ width:26px;height:26px;border:3px solid #333;border-top-color:transparent;border-radius:50%; animation:spin .7s linear infinite; }
  @keyframes spin{ to{ transform:rotate(360deg); } }
  .toast-mini{ position:fixed; right:12px; bottom:12px; background:#212529; color:#fff; padding:10px 12px; border-radius:8px; z-index:9999; font-size:13px; box-shadow:0 8px 20px rgba(0,0,0,.2); }
</style>

<div class="row">
  <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript:void(0)">SIS</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0)">CRM</a></li>
          <li class="breadcrumb-item active">User report</li>
        </ol>
      </div>
      <h4 class="page-title">User Report</h4>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">

        {{-- PRINT HEADER (visible only on print) --}}
        <div class="print-header" style="display:none">
          <h5 class="mb-0">User Report</h5>
          <small>
            @php
              $fromTxt = request('from') ?: '—';
              $toTxt   = request('to') ?: '—';
              $assignName = '';
              if(!$isWorker && request('assign')){
                $u = $users->firstWhere('id', (int)request('assign'));
                $assignName = $u ? full_name($u) : '';
              } elseif($isWorker) {
                $assignName = full_name(auth()->user());
              }
            @endphp
            Date Range: {{ $fromTxt }} to {{ $toTxt }} {{ $assignName ? ' | Assign: '.$assignName : '' }}
          </small>
        </div>

        {{-- Filters --}}
        <div class="row no-print">
          <div class="col-12 toolbar-sticky">
            <form class="row gy-2 gx-2 align-items-end" id="user_report_filters" onsubmit="return false">

              {{-- Assign By: Admin only --}}
              @if(!$isWorker)
                <div class="col-md-4">
                  <label class="form-label">Assign By</label>
                  <select class="form-select" id="assign" name="assign">
                    <option value="">Choose...</option>
                    @foreach($users as $user)
                      <option value="{{ $user->id }}" {{ (string)request('assign')===(string)$user->id ? 'selected':'' }}>
                        {{ full_name($user) }}
                      </option>
                    @endforeach
                  </select>
                </div>
              @else
                {{-- Worker: hide field, force own id --}}
                <input type="hidden" id="assign" name="assign" value="{{ $selfId }}">
              @endif

              <div class="col-md-4">
                <label class="form-label">From</label>
                <input type="date" name="from" id="from" class="form-control" value="{{ request('from') }}">
              </div>

              <div class="col-md-4">
                <label class="form-label">To</label>
                <input type="date" name="to" id="to" class="form-control" value="{{ request('to') }}">
              </div>

              @php
                $exportBase = \Illuminate\Support\Facades\Route::has('admin.report.user.export')
                    ? route('admin.report.user.export')
                    : null;
              @endphp

              <div class="col-12 d-flex gap-2 mt-2">
                <button type="button" class="btn btn-primary btn-sm" id="btnApply">Apply</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnReset">Reset</button>
                <div class="ms-auto">
                  @if($exportBase)
                    <a href="{{ $exportBase }}" class="btn btn-dark btn-sm" id="btnExport">Export</a>
                  @endif
                  <button type="button" class="btn btn-info btn-sm" id="btnPrint">Print</button>
                </div>
              </div>

              <div class="col-12 mt-2">
                <div class="d-flex flex-wrap gap-2">
                  <button type="button" class="btn btn-outline-dark btn-xs" data-preset="today">Today</button>
                  <button type="button" class="btn btn-outline-dark btn-xs" data-preset="week">This Week</button>
                  <button type="button" class="btn btn-outline-dark btn-xs" data-preset="month">This Month</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        {{-- Report Container --}}
        <div class="row">
          <div class="col-12">
            <div class="userReport-wrap">
              <div class="loading-overlay" id="loadingOverlay"><span class="spinner"></span></div>
              <div class="userReport">
                {{-- AJAX content goes here --}}
              </div>
            </div>
          </div>
        </div>

        <div id="toast" class="toast-mini" style="display:none"></div>

      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script>
(function(){
  const IS_WORKER = {{ $isWorker ? 'true' : 'false' }};
  const SELF_ID   = {{ (int)$selfId }};

  const $from   = document.querySelector('input[name="from"]');
  const $to     = document.querySelector('input[name="to"]');
  const $assign = document.querySelector('#assign'); // may be hidden for worker
  const $overlay= document.getElementById('loadingOverlay');
  const $wrap   = document.querySelector('div.userReport');
  const $toast  = document.getElementById('toast');
  const exportBtn = document.getElementById('btnExport');

  // If worker, hard-lock assign value & (if select somehow rendered) disable it
  if (IS_WORKER && $assign) {
    $assign.value = SELF_ID;
    if ($assign.tagName === 'SELECT') {
      $assign.setAttribute('disabled','disabled');
    }
  }

  const fmt = (d)=> d.toISOString().slice(0,10);

  function showToast(msg, type='dark'){
    if(!$toast) return;
    $toast.textContent = msg;
    $toast.style.background = (type==='danger' ? '#dc3545' : type==='success' ? '#198754' : '#212529');
    $toast.style.display = 'block';
    setTimeout(()=> $toast.style.display='none', 2200);
  }

  function setPreset(preset){
    const today = new Date();
    let start, end;
    if(preset==='today'){
      start = end = today;
    }else if(preset==='week'){
      const day = today.getDay(); // 0 Sun
      const diffToMon = (day===0 ? 6 : day-1);
      start = new Date(today); start.setDate(today.getDate() - diffToMon);
      end   = new Date(start); end.setDate(start.getDate() + 6);
      if(end > today) end = today;
    }else if(preset==='month'){
      start = new Date(today.getFullYear(), today.getMonth(), 1);
      end   = new Date(today.getFullYear(), today.getMonth()+1, 0);
      if(end > today) end = today;
    }
    if($from && $to){
      $from.value = fmt(start);
      $to.value   = fmt(end);
      fetchReport();
    }
  }

  function validRange(){
    if(!$from || !$to) return true;
    if(!$from.value || !$to.value) return true;
    return (new Date($from.value) <= new Date($to.value));
  }

  let debounceTimer;
  function debounceFetch(){
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fetchReport, 300);
  }

  function toggleLoading(show=true){
    if(!$overlay) return;
    $overlay.style.display = show ? 'flex' : 'none';
  }

  function syncUrl(){
    const params = new URLSearchParams(window.location.search);
    // force own id if worker
    const assignVal = IS_WORKER ? SELF_ID : ($assign?.value || '');
    assignVal ? params.set('assign', assignVal) : params.delete('assign');
    ($from.value) ? params.set('from', $from.value) : params.delete('from');
    ($to.value)   ? params.set('to',   $to.value)   : params.delete('to');
    const newUrl = `${window.location.pathname}?${params.toString()}`;
    window.history.replaceState(null,'', newUrl);
  }

  function fetchReport(){
    if(!validRange()){
      showToast('From date cannot be after To date', 'danger');
      return;
    }
    syncUrl();
    toggleLoading(true);

    const startDate = $from?.value || '';
    const endDate   = $to?.value   || '';
    // Force assign for worker on the request too
    const assignUser= IS_WORKER ? SELF_ID : ($assign?.value || '');

    fetch("{{ route('admin.report.user') }}" + `?startDate=${encodeURIComponent(startDate)}&endDate=${encodeURIComponent(endDate)}&assignUser=${encodeURIComponent(assignUser)}`, {
      method: "GET",
      headers: { "X-Requested-With": "XMLHttpRequest" }
    })
    .then(r => r.json())
    .then(res => {
      if(res?.success){
        $wrap.innerHTML = res.html || '<div class="text-center text-muted py-4">No data</div>';
      }else{
        $wrap.innerHTML = '<div class="text-center text-danger py-4">Failed to load report.</div>';
      }
    })
    .catch(()=> {
      $wrap.innerHTML = '<div class="text-center text-danger py-4">Network error.</div>';
    })
    .finally(()=> toggleLoading(false));
  }

  document.getElementById('btnApply')?.addEventListener('click', fetchReport);
  document.getElementById('btnReset')?.addEventListener('click', ()=>{
    if(!$assign) return;
    if(!IS_WORKER){ $assign.value = ''; }
    if($from) $from.value = '';
    if($to)   $to.value   = '';
    fetchReport();
  });

  document.querySelectorAll('[data-preset]').forEach(btn=>{
    btn.addEventListener('click', ()=> setPreset(btn.dataset.preset));
  });

  $from?.addEventListener('change', debounceFetch);
  $to?.addEventListener('change', debounceFetch);
  if(!IS_WORKER){ $assign?.addEventListener('change', debounceFetch); }

  document.getElementById('btnPrint')?.addEventListener('click', ()=> window.print());

  if (exportBtn) {
    exportBtn.addEventListener('click', (e)=>{
      e.preventDefault();
      const base = exportBtn.getAttribute('href');
      const params = new URLSearchParams({
        startDate: $from?.value || '',
        endDate:   $to?.value || '',
        assignUser: IS_WORKER ? SELF_ID : ($assign?.value || '')
      });
      window.location.href = base + '?' + params.toString();
    });
  }

  document.addEventListener('DOMContentLoaded', fetchReport);
  fetchReport();
})();
</script>
@endpush
