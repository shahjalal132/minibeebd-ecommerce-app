@extends('frontend.app')
@section('content')

<style>
  .stripe-button-el { display: none; }

  input[type='text'], input[type='number'], #selectCourier {
    border: 1px solid #00276C;
  }
  .form-group label{ font-family: 'Hind Siliguri', sans-serif; }

  @media (max-width: 767px) {
    .table>:not(caption)>*>* { font-size: 12px; }
    .pro-qty input { font-size: 12px; width: 32px; margin: 0; }
    .checkout_details * { font-size: 13px; }
    .product-quanity{ width: 50px; }
  }
  .hind{ font-family: 'Hind Siliguri', sans-serif; }

  /* ===== Shipping radios - centered card layout ===== */
  .ship-radio-wrap{
    display:flex; flex-wrap:wrap; gap:12px; justify-content:center;
  }
  .ship-option{ position:relative; display:inline-block; }
  .ship-option input[type="radio"]{
    position:absolute; inset:0; opacity:0; cursor:pointer;
  }
  .ship-card{
    min-width: 220px; padding:12px 16px; border:1px solid #e5e7eb;
    border-radius:12px; background:#fff; text-align:center;
    transition: all .15s ease-in-out; box-shadow:0 6px 16px rgba(2,6,23,.05);
  }
  .ship-title{
    font-family:'Hind Siliguri', sans-serif; font-weight:700; color:#111827;
    margin-bottom:4px; font-size:16px;
  }
  .ship-amount{ font-weight:600; color:#0ea5e9; font-size:14px; }
  .ship-option input[type="radio"]:checked + .ship-card{
    border-color:#0ea5e9; box-shadow:0 0 0 3px rgba(14,165,233,.2); transform: translateY(-1px);
  }
  .ship-option:hover .ship-card{ border-color:#93c5fd; }
  @media (max-width: 576px){ .ship-card{ min-width: 46vw; } }
</style>

@php
use App\Models\Information;
use App\Models\BanglaText;
$info = Information::first();
$bangla_text = BanglaText::first();
$coupon_visibility = $info->coupon_visibility;
$cart = session()->get('cart', []);

$is_free = 0;
foreach($cart as $key => $item){
    if(!empty($item['is_free_shipping']) && $item['is_free_shipping'] == 1){
        $is_free = 1;
        break;
    }
}
@endphp

<main class="main-wrapper">
 <section class="section-content py-5" style="margin-top:60px; background: #f5f6fa;">
    <div class="container">
        <form action="{{ route('front.checkouts.store')}}" method="POST" id="checkout_form">
            @csrf
            <div class="row g-4">
                <!-- Checkout Form -->
                <div class="col-lg-6">
                    <aside class="card border-0 shadow-sm rounded-4 p-4">
                        <h4 class="mb-4 fw-bold" style="font-family: 'Hind Siliguri', sans-serif; text-align: justify;">
                            {{ $bangla_text->checkout_form_top_text }}
                        </h4>

                        <!-- Name -->
                        <div class="mb-3">
                            <label class="form-label fs-2 text-dark hind">{{ $bangla_text->name_text }}</label>
                            <input type="text" name="first_name" id="name" class="form-control rounded-3" placeholder="">
                        </div>

                        <!-- Mobile -->
                        <div class="mb-3">
                            <label class="form-label fs-2 text-dark hind">{{ $bangla_text->mobile_text }}</label>
                            <input type="text" maxlength="11" name="mobile" id="mobile" class="form-control rounded-3" placeholder="">
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label class="form-label fs-2 text-dark hind">{{ $bangla_text->address_text }}</label>
                            <input type="text" name="shipping_address" id="address" class="form-control rounded-3" placeholder="">
                        </div>

                        <input type="hidden" name="ip_address" id="ip_address" value="">

                        @php
                            $shipping_value = [];
                            foreach($cart as $citem) { $shipping_value[] = $citem['is_free_shipping']; }
                            $hasPaidShipping = (in_array(null, $shipping_value) || $is_free != 1);
                        @endphp

                        <!-- Delivery Charge (Radio UI + hidden select to keep old logic intact) -->
                        <div class="mb-3">
                          <label class="form-label fs-2 text-dark hind d-block text-center">{{ $bangla_text->delivery_text }}</label>

                          @if($hasPaidShipping)
                            {{-- Visible Radio Options --}}
                            <div class="ship-radio-wrap">
                              @foreach($charges as $charge)
                                <label class="ship-option">
                                  <input type="radio"
                                         name="delivery_charge_id_radio"
                                         class="charge_radio_radioui"
                                         value="{{ $charge->id }}"
                                         data-charge="{{ $charge->amount }}">
                                  <div class="ship-card">
                                    <div class="ship-title">{{ $charge->title }}</div>
                                    <div class="ship-amount">{{ number_format($charge->amount, 2) }} ৳</div>
                                  </div>
                                </label>
                              @endforeach
                            </div>

                            {{-- Hidden select (keeps old JS working: calculate_total() / #selectCourier handlers) --}}
                            <select required name="delivery_charge_id" id="selectCourier" class="form-select rounded-3 fs-2 d-none">
                              @foreach($charges as $charge)
                                <option value="{{ $charge->id }}" data-charge="{{ $charge->amount }}">{{ $charge->title }}</option>
                              @endforeach
                            </select>
                          @else
                            {{-- Free shipping --}}
                            <div class="ship-radio-wrap">
                              <label class="ship-option">
                                <input type="radio"
                                       name="delivery_charge_id_radio"
                                       class="charge_radio_radioui"
                                       value="0"
                                       data-charge="0"
                                       checked>
                                <div class="ship-card">
                                  <div class="ship-title">ফ্রী শিপিং</div>
                                  <div class="ship-amount">0.00 ৳</div>
                                </div>
                              </label>
                            </div>

                            {{-- Hidden select for old logic --}}
                            <select name="delivery_charge_id" id="selectCourier" class="form-select rounded-3 fs-2 d-none">
                              <option value="0" data-charge="0" selected>ফ্রী শিপিং</option>
                            </select>
                          @endif
                        </div>

                        <style>
                          input[type=checkbox], input[type=radio]{
                            opacity: 1; font-size: 18px; position: static; height: 30px; width: 20px;
                          }
                        </style>

                        <!-- Submit Button -->
                        <div class="d-grid mt-4">
                            <button type="submit" id="chk_btn" class="btn btn-lg fw-bold main-bg fs-2 text-white rounded-3 hind">
                                {{ $bangla_text->order_confirm_text }}
                            </button>
                        </div>
                    </aside>
                </div>

                <!-- Order Details -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 orderDetails bg-white sticky-top" style="top: 100px;">
                        @include('frontend.cart.details')
                    </div>
                </div>

                <!-- Hidden Cart Data (for analytics) -->
                @foreach($cart as $item)
                    <div class="cart-item-data" 
                         data-product-id="{{ $item['product_id'] }}" 
                         data-product-name="{{ $item['name'] }}" 
                         data-category-name="{{ $item['category_name'] }}"
                         data-price="{{ $item['price'] }}" 
                         data-quantity="{{ $item['quantity'] }}">
                    </div>
                @endforeach
                <input type="hidden" name="total_cart_price" value="{{ $totalPrice }}">
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // localStorage fields
    const inputFields = ['name', 'mobile', 'address'];

    // Load saved values for inputs
    inputFields.forEach(function(field) {
        const el = document.getElementById(field);
        if (el && localStorage.getItem(field)) {
            el.value = localStorage.getItem(field);
        }
    });

    // Save on input change
    inputFields.forEach(function(field) {
        const el = document.getElementById(field);
        if (el) {
            el.addEventListener('input', function() {
                localStorage.setItem(field, el.value);
            });
        }
    });

    // On form submit, clear localStorage
    document.getElementById('checkout_form').addEventListener('submit', function() {
        inputFields.forEach(function(field) {
            localStorage.removeItem(field);
        });
    });
});
</script>

</main>

@endsection

@push('js')
<script src="{{ asset('frontend/js/checkout.js')}}"></script>

<script>
    // Fetch user's IP address
    fetch('https://api.ipify.org?format=json', { headers: { 'Accept': 'application/json' }})
    .then(r => r.json()).then(data => { document.getElementById('ip_address').value = data.ip; })
    .catch(console.error);

    // Save incomplete on mobile change
    $(document).on('change','#mobile', function (e) {
        e.preventDefault();
        let mobile = $(this).val();
        let name = $('#name').val();
        let address = $('#address').val();
        let url = "{{ route('incompleteStore') }}";
        if (!mobile || mobile.length !== 11) return;

        $.ajax({
            url: url, type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data:{mobile,name,address},
            success: function (res) { console.log(res.message); },
            error: function (xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred';
                console.log(msg);
            }
        });
    });
</script>

<script type="text/javascript">
$(document).ready(function(){

    // ==== RADIO → HIDDEN SELECT SYNC + TOTAL RECALC (core) ====
    $(document).on('change', 'input[name="delivery_charge_id_radio"]', function(){
        var val = $(this).val();
        // sync to hidden select (old logic depends on #selectCourier)
        $('#selectCourier').val(val).trigger('change');

        // force total refresh immediately
        if (typeof calculate_total === 'function') {
            calculate_total();
        } else if (typeof getCharge === 'function') {
            getCharge();
        }

        // optional: visual highlight handled by CSS :checked + .ship-card
    });

    // First-time init (pick checked or highest)
    (function initShippingRadio(){
      var $radios = $('input[name="delivery_charge_id_radio"]');
      if(!$radios.length) return;

      var $checked = $radios.filter(':checked');
      if($checked.length){
        $checked.trigger('change');
        return;
      }
      var $pick = null, max = -1;
      $radios.each(function(){
        var c = parseFloat($(this).data('charge')) || 0;
        if(c > max){ max = c; $pick = $(this); }
      });
      ($pick || $radios.first()).prop('checked', true).trigger('change');
    })();

    // remove item ajax (unchanged)
    $(document).on('click', 'a.remove_item', function(e){
       e.preventDefault();
       let url = $(this).attr('data-href');
       let method = "DELETE";
       let segment = "checkouts";
       if(confirm('Are you sure?')){
        $.ajax({
          url, method, data: {segment},
          success: function(res){
            if(res.success){
              toastr.success(res.msg);
              $('div#cart-dropdown').html(res.html);
              $(document).find('div.orderDetails').empty().html(res.html2);
              $(document).find('div.cart_other_details').html(res.html3);
              if(res.item <= 0){ document.location.href = res.url; }
            }else{ toastr.error('Someting went wrong!'); }

            if (typeof calculate_total === 'function') calculate_total();
            else if (typeof getCharge === 'function') getCharge();
          }
        });
       }
    });

    // Analytics: begin_checkout
    (function generateDataLayers(){
      var items = [];
      $('.cart-item-data').each(function() {
          items.push({
              item_id: $(this).data('product-id'),
              item_name: $(this).data('product-name'),
              item_category: $(this).data('category-name'),
              price: $(this).data('price'),
              quantity: $(this).data('quantity')
          });
      });
      let total = $('input[name="total_cart_price"]').val();

      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
          event: 'begin_checkout',
          ecommerce: { currency: "BDT", value: total, items: items }
      });

      if (typeof fbq === 'function') {
          const eventID = "IC_{{ now()->format('Ymdhi') }}";
          fbq('track', 'InitiateCheckout', {
              value: total,
              currency: 'BDT',
              contents: items.map(i => ({ id: i.item_id, quantity: i.quantity, item_price: i.price })),
              content_type: 'product',
              content_ids: items.map(i => i.item_id)
          }, { eventID });
          console.log("InitiateCheckout EventID:", eventID);
      }
    })();
});
</script>

<script>
$(document).on('submit', 'form#checkout_form', function(e) {
  e.preventDefault();
  $('span.textdanger').text('');
  let ele = $('form#checkout_form');
  var url = ele.attr('action');
  var method = ele.attr('method');
  var formData = ele.serialize();

  var first_name = $('input[name="first_name"]').val();
  var mobile = $('input[name="mobile"]').val();
  var shipping_address = $('input[name="shipping_address"]').val();
  var ship_charge = (function(){
    var $sel = $('#selectCourier').find(':selected');
    return $sel.length ? ($sel.data('charge') || 0) : 0;
  })();

  function generateDataLayer(transaction_id){
    var items = [];
    $('.cart-item-data').each(function() {
        items.push({
            item_id: $(this).data('product-id'),
            item_name: $(this).data('product-name'),
            item_category: $(this).data('category-name'),
            price: $(this).data('price'),
            quantity: $(this).data('quantity')
        });
    });
    let total = $('input[name="total_cart_price"]').val();

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
        event: 'purchase',
        ecommerce: {
            currency: "BDT",
            value: total,
            shipping: ship_charge,
            transaction_id: transaction_id,
            items: items
        },
        customer: {
            first_name: first_name,
            phone: mobile,
            shipping_address: shipping_address
        }
    });
  }

  $.ajax({
      type: method, url: url, data: formData,
      success: function(res) {
          if (res.success == true) {
              var transaction_id = res.invoice_no;
              generateDataLayer(transaction_id);
              toastr.success(res.msg);
              if (res.url) { document.location.href = res.url; }
              else { window.location.reload(); }
          } else if (res.success == false) {
              toastr.error(res.msg);
          }
      },
      error: function(response) {
          $.each(response.responseJSON.errors, function(field_name, error) {
              $(document).find('[name=' + field_name + ']').after('<span class="textdanger" style="color:red">' + error + '</span>');
          })
      }
  });
});
  
function paymentInput(type){
  if(type == 'cash'){
    document.getElementById('payment_input').style.display = 'none';
    document.getElementById('paypal-button-container').style.display = 'none';
    document.getElementById('chk_btn').style.display = 'block';
    document.getElementsByClassName('stripe-button-el')[0].style.display = 'none';
    document.getElementsByClassName('stripe-button-el')[0].disabled = true;
  } else if(type == 'bkash' || type == 'rocket' || type == 'nogod'){
    document.getElementById('payment_input').style.display = 'block';
    document.getElementById('paypal-button-container').style.display = 'none';
    document.getElementById('chk_btn').style.display = 'block';
    document.getElementsByClassName('stripe-button-el')[0].style.display = 'none';
  } else if(type == 'stripe'){
    document.getElementById('payment_input').style.display = 'none';
    document.getElementById('paypal-button-container').style.display = 'none';
    document.getElementById('chk_btn').style.display = 'none';
    document.getElementsByClassName('stripe-button-el')[0].style.display = 'block';
    document.getElementsByClassName('stripe-button-el')[0].disabled = false;
  } else {
    document.getElementById('payment_input').style.display = 'none';
    document.getElementById('paypal-button-container').style.display = 'block';
    document.getElementById('chk_btn').style.display = 'none';
    document.getElementsByClassName('stripe-button-el')[0].style.display = 'none';
  }
}
</script>
@endpush
