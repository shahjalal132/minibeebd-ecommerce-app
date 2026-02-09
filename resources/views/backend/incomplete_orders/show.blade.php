@extends('backend.app')
@section('content')

<style>
  .info-card{ background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; margin-bottom:20px; }
  .info-label{ font-weight:600; color:#6b7280; font-size:13px; text-transform:uppercase; margin-bottom:5px; }
  .info-value{ color:#111827; font-size:16px; }
  .badge-custom{ padding:6px 12px; border-radius:6px; font-weight:600; }
</style>

<div class="row">
  <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
          <li class="breadcrumb-item"><a href="{{ route('admin.incomplete_orders.index') }}">Incomplete Orders</a></li>
          <li class="breadcrumb-item active">View Details</li>
        </ol>
      </div>
      <h4 class="page-title">Incomplete Order Details</h4>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-8">
    <!-- Customer Information -->
    <div class="info-card">
      <h5 class="mb-3">Customer Information</h5>
      <div class="row">
        <div class="col-md-6 mb-3">
          <div class="info-label">Name</div>
          <div class="info-value">{{ $item->name ?? 'N/A' }}</div>
        </div>
        <div class="col-md-6 mb-3">
          <div class="info-label">Phone</div>
          <div class="info-value">
            @if($item->phone)
              <a href="tel:{{ preg_replace('/\D/','',$item->phone) }}" class="text-primary">
                {{ $item->phone }}
              </a>
            @else
              N/A
            @endif
          </div>
        </div>
        <div class="col-md-12 mb-3">
          <div class="info-label">Address</div>
          <div class="info-value">{{ $item->address ?? 'N/A' }}</div>
        </div>
      </div>
    </div>

    <!-- Shipping Information -->
    <div class="info-card">
      <h5 class="mb-3">Shipping Information</h5>
      <div class="row">
        <div class="col-md-12 mb-3">
          <div class="info-label">Delivery Charge</div>
          <div class="info-value">
            @if($item->deliveryCharge)
              <span class="badge badge-custom" style="background:#e0f2fe;color:#0369a1;">
                {{ $item->deliveryCharge->title }} - ৳{{ $item->deliveryCharge->amount }}
              </span>
            @else
              <span class="text-muted">Not selected</span>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Information -->
    <div class="info-card">
      <h5 class="mb-3">Payment Information</h5>
      <div class="row">
        <div class="col-md-6 mb-3">
          <div class="info-label">Payment Method</div>
          <div class="info-value">
            @if($item->payment_method)
              @if($item->payment_method === 'cod')
                <span class="badge badge-custom" style="background:#fef3c7;color:#92400e;">Cash on Delivery</span>
              @else
                <span class="badge badge-custom" style="background:#e0f2fe;color:#0369a1;">{{ $item->payment_method }}</span>
              @endif
            @else
              <span class="text-muted">Not selected</span>
            @endif
          </div>
        </div>
        @if($item->payment_method && $item->payment_method !== 'cod')
          <div class="col-md-6 mb-3">
            <div class="info-label">From Number</div>
            <div class="info-value">{{ $item->from_number ?? 'N/A' }}</div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="info-label">Transaction ID</div>
            <div class="info-value">{{ $item->transaction_id ?? 'N/A' }}</div>
          </div>
          @if($item->screenshot_path)
            <div class="col-md-12 mb-3">
              <div class="info-label">Payment Screenshot</div>
              <div class="info-value">
                <a href="{{ asset($item->screenshot_path) }}" target="_blank" class="btn btn-info btn-sm">
                  <i class="mdi mdi-image"></i> View Screenshot
                </a>
              </div>
            </div>
          @endif
        @endif
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <!-- Session Information -->
    <div class="info-card">
      <h5 class="mb-3">Session Information</h5>
      <div class="mb-3">
        <div class="info-label">Session ID</div>
        <div class="info-value">
          <code style="font-size:12px;">{{ $item->session_id ?? 'N/A' }}</code>
        </div>
      </div>
      <div class="mb-3">
        <div class="info-label">IP Address</div>
        <div class="info-value">
          <code style="font-size:12px;">{{ $item->ip_address ?? 'N/A' }}</code>
        </div>
      </div>
    </div>

    <!-- Timestamps -->
    <div class="info-card">
      <h5 class="mb-3">Timestamps</h5>
      <div class="mb-3">
        <div class="info-label">Created At</div>
        <div class="info-value">
          {{ $item->created_at->format('Y-m-d H:i:s') }}<br>
          <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
        </div>
      </div>
      <div class="mb-3">
        <div class="info-label">Updated At</div>
        <div class="info-value">
          {{ $item->updated_at->format('Y-m-d H:i:s') }}<br>
          <small class="text-muted">{{ $item->updated_at->diffForHumans() }}</small>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="info-card">
      <h5 class="mb-3">Actions</h5>
      <div class="d-grid gap-2">
        <a href="{{ route('admin.incomplete_orders.index') }}" class="btn btn-secondary">
          <i class="mdi mdi-arrow-left"></i> Back to List
        </a>
        @can('incomplete_order.delete')
          <a href="{{ route('admin.incomplete_orders.destroy', $item->id) }}" 
             class="btn btn-danger delete-btn">
            <i class="mdi mdi-delete"></i> Delete
          </a>
        @endcan
      </div>
    </div>
  </div>
</div>

@endsection

@push('js')
<script>
$(function(){
  // Delete confirmation
  $('.delete-btn').on('click', function(e){
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
            window.location.href = '{{ route('admin.incomplete_orders.index') }}';
          }
        }
      });
    }
  });
});
</script>
@endpush
