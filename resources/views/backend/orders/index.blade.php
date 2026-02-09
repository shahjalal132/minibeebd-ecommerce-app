@extends('backend.app')
@section('content')

<style>
  :root{
    --bc-gray:#f3f4f6; --bc-muted:#6b7280; --bc-dark:#111827;
    --bc-border:#e5e7eb; --bc-soft:#fafafa; --radius:12px;
    --primary:#111827; --accent:#2563eb; --danger:#ef4444; --success:#16a34a;
  }
  .page-title, .ps-2 { color:#0f172a !important; }
  .disable-click{ pointer-events:none; }

  .action-bar{ display:flex; flex-wrap:wrap; gap:8px; }
  @media (max-width: 767.98px){
    .action-bar{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; }
    .action-bar .btn{ width:100%; justify-content:center; }
  }

  .filters-wrap{ background:var(--bc-soft); border:1px solid var(--bc-border); border-radius:12px; padding:10px; }
  @media (max-width: 767.98px){
    .filters-wrap{ position:sticky; top:56px; z-index:9; box-shadow:0 6px 12px rgba(0,0,0,.06); }
  }
  .status-chip-row{ display:flex; gap:8px; overflow-x:auto; -webkit-overflow-scrolling:touch; padding:6px 0; margin-bottom:6px; }
  .status-chip-row label{ white-space:nowrap; border:1px solid var(--bc-border); border-radius:999px; padding:6px 10px; background:#fff; display:flex; align-items:center; gap:6px; box-shadow:0 1px 0 rgba(0,0,0,.02); font-size:13px; }
  .status-chip-row input[type="radio"]{ accent-color:var(--primary); }
  .status-chip-row::-webkit-scrollbar{ height:6px; }
  .status-chip-row::-webkit-scrollbar-thumb{ background:#c7c7c7; border-radius:999px; }

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
    .touch-check input[type="checkbox"]{ width:20px; height:20px; transform:translateY(2px); }
  }

  .badge-info-lighten{ background:#e0f2fe; color:#0369a1; padding:6px 10px; border-radius:999px; font-weight:600; }
  .badge-danger-lighten{ background:#fee2e2; color:#991b1b; padding:6px 10px; border-radius:999px; font-weight:600; }
  .text-muted-2{ color:var(--bc-muted); }
  .soft{ background:var(--bc-soft); }
  .btn.btn-sm{ font-size:12px; }

  .bulk-bar{
    position:fixed; left:0; right:0; bottom:0; z-index:9999; display:none; gap:8px; align-items:center; padding:10px;
    background:#ffffff; border-top:1px solid var(--bc-border); box-shadow:0 -8px 20px rgba(0,0,0,.08);
  }
  .bulk-bar .count{ font-weight:700; color:#0f172a; padding:8px 12px; border-radius:10px; background:#f8fafc; }
  .bulk-bar .btn{ flex:1; font-weight:600; }
  @media (min-width: 768px){
    .bulk-bar{ left:50%; transform:translateX(-50%); width:720px; border-radius:14px 14px 0 0; }
  }

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
          <li class="breadcrumb-item active">Order List</li>
        </ol>
      </div>
      <h4 class="page-title">Order List</h4>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">

        <!-- ACTIONS -->
        <div class="row mb-3">
          <div class="col-12">
            <div class="action-bar">
              @can('product.create')
                <a href="{{ route('admin.orders.create')}}" class="btn btn-sm btn-danger"><i class="mdi mdi-basket me-1"></i> Add New Order</a>
              @endcan

              @can('product.edit')
                <a class="btn_modal btn btn-sm btn-info" href="{{ route('admin.assignUser')}}"><i class="mdi mdi-account-plus me-1"></i> Assign User</a>
              @endcan

              <a class="btn_modal btn btn-sm btn-info" href="{{ route('admin.orderStatusUpdateMulti')}}"><i class="mdi mdi-swap-horizontal me-1"></i> Status Change</a>

              @can('product.delete')
                <a class="multi_order_delete btn btn-sm btn-danger" href="{{ route('admin.deleteAllOrder')}}"><i class="mdi mdi-delete-outline me-1"></i> Delete All</a>
              @endcan

              <a class="multi_order_print btn btn-sm btn-success" href="{{ route('admin.orderList')}}"><i class="mdi mdi-printer me-1"></i> Print</a>

              <a class="send_to_redx btn btn-sm btn-dark" href="{{ route('admin.createRedxParcel')}}">Send to Redx</a>
              <a class="send_to_pathao btn btn-sm btn-primary" href="{{ route('admin.createPathaoParcel')}}">Send to Pathao</a>
              <a class="send_to_steadfast btn btn-sm btn-success" href="{{ route('admin.createSteadfastParcel')}}">Send to Steadfast</a>

              <button class="btn btn-sm btn-info" id="btn_courier_status" href="{{ route('admin.updateCourierStatus') }}">Update Courier Status</button>

              <div class="col-md-4 d-none">
                <select class="select2" name="redx_status">
                  <option value="" disabled selected>Choose Courier Status</option>
                  <option value="">All</option>
                  <option value="yes">Yes ({{$yes_count}})</option>
                  <option value="no">No ({{$no_count}})</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- FILTERS -->
        <div class="filters-wrap mb-2">
          <form id="filter_form">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <div class="status-chip-row">
                @foreach(getOrderStatus() as $key=>$value)
                  <label class="ps-2">
                    <input type="radio" class="order_sts" name="status" value="{{$key}}"/>
                    @if(Auth::user()->hasRole('worker'))
                      {{$value}} ({{\App\Models\Order::whereHas('details.product', function($q){ $q->whereNotNull('name'); })->where('status',$key)->where('assign_user_id',Auth::user()->id)->count()}})
                    @else
                      {{$value}} ({{\App\Models\Order::whereHas('details.product', function($q){ $q->whereNotNull('name'); })->where('status',$key)->count()}})
                    @endif
                  </label>
                @endforeach
              </div>

              <div class="ms-auto d-flex gap-2 w-100 w-md-auto">
                <input type="search" class="form-control" placeholder="Search by name, phone, invoice..." name="q">
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
                  <th style="width:40px">
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="checkbox" class="form-check-input check_all" value="">
                      </label>
                    </div>
                  </th>
                  <th style="width:7%">Action</th>
                  <th>Invoice ID</th>
                  <th>Order ID & Name</th>     {{-- ✅ নতুন আলাদা কলাম --}}
                  <th>Date Order</th>
                  <th>Order Time</th>
                  <th>Customers</th>
                  <th>Product Name</th>        {{-- ✅ নতুন --}}
                  <th>Product SKU</th>
                  <th>Variant</th>
                  <th>Status</th>
                  <th>Fraud Check</th>
                  <th>Payment Status</th>
                  <th>Assign User</th>
                  <th>Courier</th>
                  <th>Courier Status</th>
                  <th>Amount</th>
                </tr>
              </thead>
              <tbody>
                @foreach($items as $item)
                <tr>
                  <td class="touch-check" data-label="Select">
                    <input type="checkbox" class="order_checkbox" value="{{ $item->id}}">
                  </td>

                  <td data-label="Action">
                    <a href="{{ route('admin.orders.edit',[$item->id])}}" class="action-icon" title="Edit"><i class="mdi mdi-square-edit-outline"></i></a>
                    @can('order.delete')
                      <a href="{{ route('admin.orders.destroy',[$item->id])}}" class="delete action-icon ms-2" title="Delete"><i class="mdi mdi-delete"></i></a>
                    @endcan
                  </td>

                  <td data-label="Invoice ID" style="color:#000;">#{{$item->invoice_no}}</td>

                  {{-- ✅ Order ID & Name --}}
                  <td data-label="Order ID & Name" style="color:#000;">
                    <div><strong>ID:</strong> {{ $item->id }}</div>
                    <div class="text-muted-2">{{ trim(($item->first_name ?? '').' '.($item->last_name ?? '')) }}</div>
                  </td>

                  <td data-label="Date Order" style="color:#000;">{{ dateFormate($item->date)}}</td>
                  <td data-label="Order Time">{{ $item->created_at->diffForHumans() }}</td>

                  <td data-label="Customers" style="color:#000;">
                    {{$item->first_name.' '.$item->last_name}}<br>
                    <span class="text-muted-2">{{$item->shipping_address}}</span><br>
                    <a class="tel" href="tel:{{ preg_replace('/\D/','',$item->mobile) }}"><span class="dot"></span>{{ $item->mobile }}</a>
                  </td>

                  {{-- ✅ Product Name --}}
                  <td data-label="Product Name">
                    @foreach($item->details as $detail)
                      {{ $detail->product->name ?? 'Unknown Product' }}<br>
                    @endforeach
                  </td>

                  <td data-label="Product SKU">
                    <?php
                      foreach($item->details as $detail){
                        if(!isset($detail->product['sku']) || $detail->product['sku'] == ''){
                          echo '<span style="color:red;">Unavailable</span>';
                        } else {
                          echo e($detail->product['sku']);
                        }
                        echo "<br>";
                      }
                    ?>
                  </td>

                  <td data-label="Variant">
                    <?php
                      foreach($item->details as $detail){
                        if(!empty($detail->variant_name)){ echo e($detail->variant_name)."<br>"; continue; }
                        if(isset($detail->variation) && !empty($detail->variation->display_title)){ echo e($detail->variation->display_title)."<br>"; continue; }
                        $size = $detail->variation->size_label ?? $detail->variation->size ?? null;
                        $color = $detail->variation->color_label ?? $detail->variation->color ?? null;
                        if($size || $color){ echo e(trim(($size ?? '').($color ? (' - '.$color):'')))."<br>"; }
                        else{ echo "—<br>"; }
                      }
                    ?>
                  </td>

                  <td data-label="Status">
                    <a class="btn_modal" href="{{ route('admin.orderStatus', $item->id)}}">
                      <span class="badge badge-info-lighten">{{$item->status}}</span>
                    </a>
                  </td>

                  <td data-label="Fraud Check">
                    @php $percent =$item->getCourierPercent(); @endphp
                    @php $color = $percent>50 ? 'green' : ($percent>20?'#d97706':'#ef4444'); @endphp
                    <div style="color:{{$color}};font-weight:700;">{{$percent}}%</div>
                    <a href="javascript:void(0);" data-url="{{route('admin.fraudOrderCheck',$item->id)}}" class="btn btn-link btn-fraud p-0" style="color:#ef4444;"><i class="dripicons-search"></i></a>
                  </td>

                  <td data-label="Payment Status">
                    <a class="btn_modal" href="{{ route('admin.order_payments.edit', $item->id)}}">
                      <span class="badge badge-danger-lighten">{{$item->payment_status}}</span>
                    </a>
                  </td>

                  <td data-label="Assign User" style="color:#000;">{{ $item->assign?$item->assign->username:''}}</td>

                  <td data-label="Courier" style="color:#000;">
                    {{ $item->courier?$item->courier->name:''}} <br>
                    <span class="text-muted-2">{{$item->courier_tracking_id ?? ''}}</span><br>
                    @if($item->courier_id==1 && $item->courier_tracking_id)
                      <a href="https://redx.com.bd/track-global-parcel/?trackingId={{$item->courier_tracking_id}}" target="_blank">Track Link</a>
                    @elseif($item->courier_id==3 && $item->courier_tracking_code)
                      <a href="https://steadfast.com.bd/t/{{$item->courier_tracking_code}}" target="_blank">Track Link</a>
                    @elseif($item->courier_id==2 && $item->courier_tracking_id)
                      <a href="https://merchant.pathao.com/tracking?consignment_id={{$item->courier_tracking_id}}" target="_blank">Track Link</a>
                    @endif
                  </td>

                  <td data-label="Courier Status">
                    @if($item->courier_status) {{ $item->courier_status ?? '' }} @endif
                  </td>

                  <td data-label="Amount" style="color:#000;">{{ (int) $item->final_amount }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <p class="mt-2">{!! urldecode(str_replace("/?","?",$items->appends(Request::all())->render())) !!}</p>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Fraud Modal -->
<div tabindex="-1" role="dialog" class="orderFraudModal modal fade text-left">
  <div role="document" class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"
                style="font-size:14px;border:1px solid #24d164;padding:8px 15px;border-radius:5px;color:#24d164;font-weight:bold;">
          <span aria-hidden="true" style="font-size:18px;color:black;margin-right:5px;">×</span> Close
        </button>
        <div class="orderFraud"></div>
      </div>
    </div>
  </div>
</div>

{{-- Sticky Bulk Bar --}}
<div class="bulk-bar" id="bulkBar">
  <span class="count" id="bulkCount">0 selected</span>
  <button class="btn btn-sm btn-info"  id="bb-assign"><i class="mdi mdi-account-plus-outline me-1"></i>Assign</button>
  <button class="btn btn-sm btn-secondary" id="bb-status"><i class="mdi mdi-swap-horizontal me-1"></i>Status</button>
  <button class="btn btn-sm btn-success" id="bb-print"><i class="mdi mdi-printer me-1"></i>Print</button>
  @can('product.delete')
    <button class="btn btn-sm btn-danger"  id="bb-delete"><i class="mdi mdi-delete-outline me-1"></i>Delete</button>
  @endcan
</div>

@endsection

@push('js')
<script src="{{ asset('backend/js/order.js')}}"></script>
<script>
$(function(){

  // Fraud modal
  $(document).on('click', '.btn-fraud', function (e) {
    e.preventDefault();
    const url = $(this).data('url');
    $.get(url, function (response) {
      $('.orderFraudModal .modal-body .orderFraud').html(response);
      $('.orderFraudModal').modal('toggle');
    });
  });

  // Filters
  $("select[name='redx_status']").on('change', getOrderList);
  $("select[name='courier_type']").on('change', getOrderList);
  $('.order_sts').on('click', getOrderList);

  function getOrderList(){
    const statusValue = $("input[name='status']:checked").val();
    const redx_status = $("select[name='redx_status']").val();
    const courier_type = $("select[name='courier_type']").val();
    $.ajax({
      type: 'GET',
      url: "{{ route('admin.status_wise_order') }}",
      data: {statusValue, redx_status, courier_type},
      success: function(res){
        if(res.success === true){ $('#rcvd_order').html(res.view); afterRender(); }
      }
    });
  }

  // Search
  $('#submit_search').on('click', function(){
    const searchValue = $("input[name='q']").val();
    $.ajax({
      type: 'GET',
      url: "{{ route('admin.searchOrder') }}",
      data: {searchValue},
      success: function(res){
        if(res.success === true){ $('#rcvd_order').html(res.view); afterRender(); }
      }
    });
  });

  // Checkbox + bulk bar
  function refreshBulk(){
    const cnt = $('input.order_checkbox:checked').length;
    $('#bulkCount').text(cnt + ' selected');
    if(cnt > 0){ $('#bulkBar').fadeIn(120); } else { $('#bulkBar').fadeOut(120); }
  }
  function afterRender(){
    $(".check_all").off('change').on('change',function(){
      $(".order_checkbox").prop('checked',$(this).is(":checked"));
      refreshBulk();
    });
    $(document).off('change','.order_checkbox').on('change','.order_checkbox', refreshBulk);
  }
  afterRender();

  // Multi status
  $(document).on('submit', 'form#order_status_update_form', function(e){
    e.preventDefault();
    const url = $(this).attr('action');
    const status = $('#multi_status').val();
    const order_ids = $('input.order_checkbox:checked').map(function(){ return $(this).val(); }).get();
    if(!order_ids.length){ toastr.error('Please Select An Order First !'); return; }
    $.get(url, {status,order_ids}, function(res){
      if(res.status){ toastr.success(res.msg); location.reload(); }
      else{ toastr.error(res.msg); }
    });
  });

  // Assign user
  $(document).on('submit', 'form#order_assign_form', function(e){
    e.preventDefault();
    const url = $(this).attr('action');
    const assign_user_id = $('#assign_user_id').val();
    const order_ids = $('input.order_checkbox:checked').map(function(){ return $(this).val(); }).get();
    if(!order_ids.length){ toastr.error('Please Select An Order First !'); return; }
    $.get(url, {assign_user_id,order_ids}, function(res){
      if(res.status){ toastr.success(res.msg); location.reload(); }
      else{ toastr.error(res.msg); }
    });
  });

  // Delete
  $(document).on('click', 'a.multi_order_delete', function(e){
    e.preventDefault();
    const url = $(this).attr('href');
    const order_ids = $('input.order_checkbox:checked').map(function(){ return $(this).val(); }).get();
    if(!order_ids.length){ toastr.error('Please Select An Order First !'); return; }
    $.get(url, {order_ids}, function(res){
      if(res.status){ toastr.success(res.msg); location.reload(); }
      else{ toastr.error(res.msg); }
    });
  });

  // Print
  $(document).on('click', 'a.multi_order_print', function(e){
    e.preventDefault();
    const url = $(this).attr('href');
    const order_ids = $('input.order_checkbox:checked').map(function(){ return $(this).val(); }).get();
    if(!order_ids.length){ toastr.error('Please Select Atleast One Order!'); return; }
    $.get(url, {order_ids}, function(res){
      if(res.status){
        const w = window.open("", "_blank");
        w.document.write(res.view);
      }else{ toastr.error(res.msg); }
    });
  });

  // Courier status update
  $(document).on('click', '#btn_courier_status', function(e){
    e.preventDefault();
    const url = $(this).attr('href');
    const link = $(this);
    const order_ids = $('input.order_checkbox:checked').map(function(){ return $(this).val(); }).get();
    if(!order_ids.length){ toastr.error('Please Select Atleast One Order!'); return; }
    if(confirm('Are you sure?')){
      $.ajax({
        type:'GET', url, data:{order_ids},
        beforeSend: function(){ link.addClass('disable-click').text('Please wait...'); },
        success:function(res){
          link.removeClass('disable-click').text('Update Courier Status');
          if(res.status){ toastr.success(res.msg); }
          else{ toastr.error(`Invoice No. : ${res.invoice} something went wrong!`); }
        }
      });
    }
  });

  // Send to couriers
  function sendToCourier(link, label){
    const url = link.attr('href');
    const order_ids = $('input.order_checkbox:checked').map(function(){ return $(this).val(); }).get();
    if(!order_ids.length){ toastr.error('Please Select Atleast One Order!'); return; }
    $.ajax({
      type:'GET', url, data:{order_ids},
      beforeSend: function(){ link.addClass('disable-click').text('Please wait...'); },
      success:function(res){
        link.removeClass('disable-click').text(label);
        if(res.status){ toastr.success(res.msg); }
        else{ toastr.error(`Invoice :${res.invoice} something went wrong!`); }
      }
    });
  }
  $(document).on('click', 'a.send_to_redx', function(e){ e.preventDefault(); sendToCourier($(this),'Send to Redx'); });
  $(document).on('click', 'a.send_to_pathao', function(e){ e.preventDefault(); sendToCourier($(this),'Send to Pathao'); });
  $(document).on('click', 'a.send_to_steadfast', function(e){ e.preventDefault(); sendToCourier($(this),'Send to Steadfast'); });

  // Sticky Bulk Bar shortcuts
  $('#bb-assign').on('click', function(){ $('.btn_modal[href="{{ route('admin.assignUser') }}"]').trigger('click'); });
  $('#bb-status').on='click', function(){ $('.btn_modal[href="{{ route('admin.orderStatusUpdateMulti') }}"]').trigger('click'); };
  $('#bb-print').on('click', function(){ $('.multi_order_print').trigger('click'); });
  $('#bb-delete').on('click', function(){ $('.multi_order_delete').trigger('click'); });
});
</script>
@endpush
