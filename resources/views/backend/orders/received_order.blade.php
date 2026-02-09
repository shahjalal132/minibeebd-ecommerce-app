{{-- resources/views/backend/orders/received_order.blade.php --}}

@php
  /** $items can come as $received_order from controller or directly as $items (paginator/collection) */
  $items = isset($received_order) ? $received_order : ($items ?? collect());
@endphp

<style>
  /* Mobile-friendly rows (fallback যদি index এর main CSS load না থাকে) */
  @media (max-width: 767.98px){
    .order-table thead{ display:none; }
    .order-table tbody tr{
      display:block; background:#fff; border:1px solid #e5e7eb; border-radius:12px;
      box-shadow:0 1px 2px rgba(0,0,0,.05); padding:12px; margin-bottom:12px;
    }
    .order-table tbody tr td{
      display:grid; grid-template-columns:120px 1fr; gap:8px;
      border:none !important; padding:6px 0 !important; font-size:14px;
    }
    .order-table tbody tr td[data-label]::before{
      content:attr(data-label); font-weight:600; color:#111827;
    }
  }
  .order-table td{ word-break: break-word; }
</style>

{{-- ✅ Wrapper রাখলাম যাতে AJAX এ কেবল এই অংশটাই replace করা যায় --}}
<div id="ajax_order_wrapper">
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
          <th>Order ID &amp; Name</th>
          <th>Date Order</th>
          <th>Order Time</th>
          <th>Customers</th>
          <th>Product Name</th>
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
        @forelse($items as $item)
        <tr>
          <td class="touch-check" data-label="Select">
            <input type="checkbox" class="order_checkbox" value="{{ $item->id }}">
          </td>

          <td data-label="Action">
            <a href="{{ route('admin.orders.edit',[$item->id])}}" class="action-icon" title="Edit">
              <i class="mdi mdi-square-edit-outline"></i>
            </a>
            @can('order.delete')
            <a href="{{ route('admin.orders.destroy',[$item->id])}}" class="delete action-icon ms-2" title="Delete">
              <i class="mdi mdi-delete"></i>
            </a>
            @endcan
          </td>

          <td data-label="Invoice ID" style="color:#000;">#{{ $item->invoice_no }}</td>

          <td data-label="Order ID & Name" style="color:#000;">
            <div><strong>ID:</strong> {{ $item->id }}</div>
            <div class="text-muted-2">{{ trim(($item->first_name ?? '').' '.($item->last_name ?? '')) }}</div>
          </td>

          <td data-label="Date Order" style="color:#000;">{{ dateFormate($item->date) }}</td>
          <td data-label="Order Time">{{ $item->created_at->diffForHumans() }}</td>

          <td data-label="Customers" style="color:#000;">
            {{ $item->first_name.' '.$item->last_name }}<br>
            <span class="text-muted-2">{{ $item->shipping_address }}</span><br>
            <a class="tel" href="tel:{{ preg_replace('/\D/','',$item->mobile) }}">
              <span class="dot"></span>{{ $item->mobile }}
            </a>
          </td>

          <td data-label="Product Name">
            @foreach($item->details as $detail)
              {{ $detail->product->name ?? 'Unknown Product' }}<br>
            @endforeach
          </td>

          <td data-label="Product SKU">
            @foreach($item->details as $detail)
              @if(!isset($detail->product['sku']) || $detail->product['sku']=='')
                <span style="color:red;">Unavailable</span>
              @else
                {{ $detail->product['sku'] }}
              @endif
              <br>
            @endforeach
          </td>

          <td data-label="Variant">
            @foreach($item->details as $detail)
              @php
                $label = null;
                if(!empty($detail->variant_name)) { $label = $detail->variant_name; }
                elseif(isset($detail->variation) && !empty($detail->variation->display_title)) { $label = $detail->variation->display_title; }
                else {
                  $size = $detail->variation->size_label ?? $detail->variation->size ?? null;
                  $color= $detail->variation->color_label ?? $detail->variation->color ?? null;
                  $label = trim(($size ?? '').($color ? (' - '.$color):''));
                }
              @endphp
              {{ $label ?: '—' }}<br>
            @endforeach
          </td>

          <td data-label="Status">
            <a class="btn_modal" href="{{ route('admin.orderStatus', $item->id)}}">
              <span class="badge badge-info-lighten">{{ $item->status }}</span>
            </a>
          </td>

          <td data-label="Fraud Check">
            @php $percent=$item->getCourierPercent(); $color=$percent>50?'green':($percent>20?'#d97706':'#ef4444'); @endphp
            <div style="color:{{$color}};font-weight:700;">{{ $percent }}%</div>
            <a href="javascript:void(0);" data-url="{{route('admin.fraudOrderCheck',$item->id)}}" class="btn btn-link btn-fraud p-0" style="color:#ef4444;">
              <i class="dripicons-search"></i>
            </a>
          </td>

          <td data-label="Payment Status">
            <a class="btn_modal" href="{{ route('admin.order_payments.edit', $item->id)}}">
              <span class="badge badge-danger-lighten">{{ $item->payment_status }}</span>
            </a>
          </td>

          <td data-label="Assign User" style="color:#000;">{{ $item->assign? $item->assign->username:'' }}</td>

          <td data-label="Courier" style="color:#000;">
            {{ $item->courier ? $item->courier->name : '' }} <br>
            <span class="text-muted-2">{{ $item->courier_tracking_id ?? '' }}</span><br>
            @if($item->courier_id==1 && $item->courier_tracking_id)
              <a href="https://redx.com.bd/track-global-parcel/?trackingId={{$item->courier_tracking_id}}" target="_blank">Track Link</a>
            @elseif($item->courier_id==3 && $item->courier_tracking_code)
              <a href="https://steadfast.com.bd/t/{{$item->courier_tracking_code}}" target="_blank">Track Link</a>
            @elseif($item->courier_id==2 && $item->courier_tracking_id)
              <a href="https://merchant.pathao.com/tracking?consignment_id={{$item->courier_tracking_id}}" target="_blank">Track Link</a>
            @endif
          </td>

          <td data-label="Courier Status">{{ $item->courier_status ?? '' }}</td>

          <td data-label="Amount" style="color:#000;">{{ (int)$item->final_amount }}</td>
        </tr>
        @empty
        <tr><td colspan="17" class="text-center text-muted-2">No orders found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if(method_exists($items,'links'))
    {{-- ✅ pagination container আলাদা div এ রাখলাম যাতে AJAX এ কেবল এটুকুই পাল্টানো যায় --}}
    <div class="mt-2 ajax-pagination">
      {!! urldecode(str_replace("/?","?",$items->appends(Request::all())->render())) !!}
    </div>
  @endif
</div>

<script>
(function(){
  // Master checkbox (event delegation so that AJAX replace এর পরেও কাজ করে)
  $(document).off('change', '.check_all').on('change', '.check_all', function(){
    $(".order_checkbox").prop('checked', $(this).is(":checked"));
  });

  // ✅ Pagination: prevent full reload, only replace wrapper
  $(document).off('click', '.ajax-pagination a').on('click', '.ajax-pagination a', function(e){
    e.preventDefault();
    const pageUrl = $(this).attr('href');
    if(!pageUrl) return;
    $.ajax({
      url: pageUrl,
      type: 'GET',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      beforeSend: function(){ $('#ajax_order_wrapper').css('opacity', .55); },
      success: function(res){
        replaceWrapperFromResponse(res);
      },
      complete: function(){ $('#ajax_order_wrapper').css('opacity', 1); window.scrollTo({top:0,behavior:'smooth'}); }
    });
  });

  // ✅ Helper to safely replace wrapper from JSON({view}) or full HTML
  function replaceWrapperFromResponse(res){
    if (res && typeof res === 'object' && 'view' in res){
      $('#ajax_order_wrapper').html(res.view);
      return;
    }
    try{
      const $dom = $('<div/>').html(res);
      const inner = $dom.find('#ajax_order_wrapper').html();
      $('#ajax_order_wrapper').html(inner || res);
    }catch(e){
      $('#ajax_order_wrapper').html(res);
    }
  }

  // ✅ Global 5s-delayed refresh (index বা অন্য যেকোনো জায়গা থেকে call করা যাবে)
  window.reloadAjaxOrderWrapper = function(url){
    const targetUrl = url || window.location.href;
    // cache-buster avoid cached HTML
    const fetchUrl = targetUrl + (targetUrl.includes('?') ? '&' : '?') + '_=' + Date.now();
    $.ajax({
      url: fetchUrl,
      type: 'GET',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      beforeSend: function(){ $('#ajax_order_wrapper').css('opacity', .55); },
      success: function(res){
        // ⏱️ 5 সেকেন্ড delay দিয়ে replace
        setTimeout(function(){ replaceWrapperFromResponse(res); }, 5000);
      },
      complete: function(){ setTimeout(function(){ $('#ajax_order_wrapper').css('opacity', 1); }, 5000); }
    });
  };

  // ✅ Custom event: $(document).trigger('orders:refresh', [optionalUrl])
  $(document).off('orders:refresh').on('orders:refresh', function(e, url){
    window.reloadAjaxOrderWrapper(url);
  });

})();
</script>
