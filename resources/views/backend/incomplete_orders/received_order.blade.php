@foreach($items as $item)
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
@endforeach
