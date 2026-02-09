@extends('backend.app')
@section('content')

<style>
  :root{
    --bc-gray:#f3f4f6; --bc-muted:#6b7280; --bc-dark:#111827;
    --bc-border:#e5e7eb; --bc-soft:#fafafa; --radius:12px;
    --primary:#111827; --accent:#2563eb; --danger:#ef4444; --success:#16a34a;
  }
  .page-title, .ps-2 { color:#0f172a !important; }

  .filters-wrap{ background:var(--bc-soft); border:1px solid var(--bc-border); border-radius:12px; padding:10px; }
  @media (max-width: 767.98px){
    .filters-wrap{ position:sticky; top:56px; z-index:9; box-shadow:0 6px 12px rgba(0,0,0,.06); }
  }

  .order-table{ width:100%; }
  .table thead th{ white-space:nowrap; }

  @media (max-width: 767.98px){
    .order-table thead{ display:none; }
    .order-table tbody tr{
      display:block; background:#fff; border:1px solid var(--bc-border);
      border-radius:var(--radius); box-shadow:0 1px 2px rgba(0,0,0,.05);
      padding:12px; margin-bottom:12px;
    }
    .order-table tbody tr td{
      display:grid; grid-template-columns:120px 1fr; gap:8px;
      border:none !important; padding:6px 0 !important; font-size:14px;
    }
    .order-table tbody tr td[data-label]::before{
      content:attr(data-label); font-weight:600; color:var(--bc-dark);
    }
    .order-table .action-icon{ font-size:18px; }
    .table-responsive{ overflow:visible; }
  }

  .badge-info-lighten{ background:#e0f2fe; color:#0369a1; padding:6px 10px; border-radius:999px; font-weight:600; }
  .badge-warning-lighten{ background:#fef3c7; color:#92400e; padding:6px 10px; border-radius:999px; font-weight:600; }
  .text-muted-2{ color:var(--bc-muted); }
  .soft{ background:var(--bc-soft); }
  .btn.btn-sm{ font-size:12px; }

  a.tel{ display:inline-flex; align-items:center; gap:6px; font-weight:700; text-decoration:none; color:#0f172a; }
  a.tel .dot{ width:8px; height:8px; border-radius:999px; background:var(--success); display:inline-block; }
</style>

<div class="row">
  <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
          <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
          <li class="breadcrumb-item active">Incomplete Orders</li>
        </ol>
      </div>
      <h4 class="page-title">Incomplete Orders</h4>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">

        <!-- FILTERS -->
        <div class="filters-wrap mb-2">
          <form id="filter_form">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <div class="ms-auto d-flex gap-2 w-100 w-md-auto">
                <input type="search" class="form-control" placeholder="Search by name, phone, address, session..." name="q" value="{{ $q ?? '' }}">
                <button type="button" class="btn btn-dark btn-sm" id="submit_search">Search</button>
              </div>
            </div>
          </form>
        </div>

        <!-- LIST -->
        <div class="col-sm-12 mt-2" id="rcvd_order">
          <div class="table-responsive">
            <table class="table table-centered mb-0 order-table">
              <thead class="table-light">
                <tr>
                  <th style="width:7%">Action</th>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Phone</th>
                  <th>Address</th>
                  <th>Shipping</th>
                  <th>Payment Method</th>
                  <th>Payment Details</th>
                  <th>Session/IP</th>
                  <th>Created At</th>
                </tr>
              </thead>
              <tbody>
                @forelse($items as $item)
                <tr>
                  <td data-label="Action">
                    <a href="{{ route('admin.incomplete_orders.show',[$item->id])}}" class="action-icon" title="View"><i class="mdi mdi-eye"></i></a>
                    @can('incomplete_order.delete')
                      <a href="{{ route('admin.incomplete_orders.destroy',[$item->id])}}" class="delete action-icon ms-2" title="Delete"><i class="mdi mdi-delete"></i></a>
                    @endcan
                  </td>

                  <td data-label="ID" style="color:#000;">#{{$item->id}}</td>

                  <td data-label="Name" style="color:#000;">
                    {{ $item->name ?? 'N/A' }}
                  </td>

                  <td data-label="Phone" style="color:#000;">
                    @if($item->phone)
                      <a class="tel" href="tel:{{ preg_replace('/\D/','',$item->phone) }}">
                        <span class="dot"></span>{{ $item->phone }}
                      </a>
                    @else
                      N/A
                    @endif
                  </td>

                  <td data-label="Address" style="color:#000;">
                    {{ Str::limit($item->address ?? 'N/A', 50) }}
                  </td>

                  <td data-label="Shipping">
                    @if($item->deliveryCharge)
                      <span class="badge badge-info-lighten">{{ $item->deliveryCharge->title }} - ৳{{ $item->deliveryCharge->amount }}</span>
                    @else
                      <span class="text-muted-2">Not selected</span>
                    @endif
                  </td>

                  <td data-label="Payment Method">
                    @if($item->payment_method)
                      @if($item->payment_method === 'cod')
                        <span class="badge badge-warning-lighten">Cash on Delivery</span>
                      @else
                        <span class="badge badge-info-lighten">{{ $item->payment_method }}</span>
                      @endif
                    @else
                      <span class="text-muted-2">Not selected</span>
                    @endif
                  </td>

                  <td data-label="Payment Details">
                    @if($item->payment_method && $item->payment_method !== 'cod')
                      @if($item->from_number)
                        <div><strong>From:</strong> {{ $item->from_number }}</div>
                      @endif
                      @if($item->transaction_id)
                        <div><strong>Txn ID:</strong> {{ Str::limit($item->transaction_id, 20) }}</div>
                      @endif
                      @if($item->screenshot_path)
                        <div><a href="{{ asset($item->screenshot_path) }}" target="_blank" class="btn btn-sm btn-info mt-1">View Screenshot</a></div>
                      @endif
                    @else
                      <span class="text-muted-2">N/A</span>
                    @endif
                  </td>

                  <td data-label="Session/IP" style="color:#000;">
                    <div><small><strong>Session:</strong> {{ Str::limit($item->session_id ?? 'N/A', 15) }}</small></div>
                    <div><small><strong>IP:</strong> {{ $item->ip_address ?? 'N/A' }}</small></div>
                  </td>

                  <td data-label="Created At" style="color:#000;">
                    {{ $item->created_at->format('Y-m-d H:i') }}<br>
                    <small class="text-muted-2">{{ $item->created_at->diffForHumans() }}</small>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="10" class="text-center py-4">
                    <p class="text-muted">No incomplete orders found.</p>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <p class="mt-2">{!! urldecode(str_replace("/?","?",$items->appends(Request::all())->render())) !!}</p>
        </div>

      </div>
    </div>
  </div>
</div>

@endsection

@push('js')
<script>
$(function(){
  // Search functionality
  $('#submit_search').on('click', function(){
    const q = $('input[name="q"]').val();
    const url = new URL(window.location.href);
    if(q){
      url.searchParams.set('q', q);
    } else {
      url.searchParams.delete('q');
    }
    window.location.href = url.toString();
  });

  // Enter key search
  $('input[name="q"]').on('keypress', function(e){
    if(e.which === 13){
      $('#submit_search').click();
    }
  });

  // Delete confirmation
  $(document).on('click', '.delete', function(e){
    e.preventDefault();
    const url = $(this).attr('href');
    if(confirm('Are you sure you want to delete this incomplete order?')){
      $.ajax({
        url: url,
        type: 'DELETE',
        data: {
          _token: '{{ csrf_token() }}'
        },
        success: function(response){
          if(response.status){
            alert(response.msg);
            location.reload();
          }
        }
      });
    }
  });
});
</script>
@endpush
