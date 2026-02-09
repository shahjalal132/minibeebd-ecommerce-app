@extends('backend.app')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />

<style>
    :root{
        --ink:#0f172a;
        --muted:#6b7280;
        --soft:#f9fafb;
        --card:#ffffff;
        --border:#e5e7eb;
        --primary:#0ea5e9;
        --primary-soft:rgba(14,165,233,.08);
        --danger:#ef4444;
    }

    body{
        background:var(--soft);
    }

    .page-title-box{
        margin-bottom: 10px;
    }

    .page-title-box h4{
        font-weight: 700;
        color: var(--ink);
    }

    .order-layout{
        max-width: 1180px;
        margin: 0 auto;
    }

    .order-card{
        border: 0;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15,23,42,.08);
        overflow: hidden;
    }

    .order-card .card-body{
        background: linear-gradient(to bottom, #f9fafb, #ffffff);
        padding: 20px 18px 22px;
    }

    @media (min-width: 768px){
        .order-card .card-body{
            padding: 28px 26px 26px;
        }
    }

    .section-header{
        display:flex;
        align-items:center;
        justify-content:space-between;
        margin-bottom: 10px;
        margin-top: 8px;
    }

    .section-title{
        font-size: 15px;
        font-weight: 600;
        letter-spacing:.02em;
        color: var(--ink);
        display:flex;
        align-items:center;
        gap:8px;
    }

    .section-title span.badge-dot{
        width:6px;height:6px;
        border-radius:999px;
        background:var(--primary);
        box-shadow:0 0 0 4px var(--primary-soft);
    }

    .section-sub{
        font-size: 12px;
        color: var(--muted);
    }

    .section-box{
        background:var(--card);
        border-radius:16px;
        border:1px solid var(--border);
        padding:14px 12px;
        margin-bottom: 14px;
    }

    @media (min-width: 768px){
        .section-box{
            padding:16px 16px;
        }
    }

    .form-label{
        font-size: 12px;
        font-weight: 500;
        color: var(--muted);
        margin-bottom: 4px;
    }

    .form-control, .form-select, select.form-control{
        border-radius: 10px !important;
        border:1px solid var(--border);
        font-size: 13px;
        padding: .4rem .65rem;
    }

    .form-control:focus, .form-select:focus, select.form-control:focus{
        border-color: var(--primary);
        box-shadow: 0 0 0 .18rem var(--primary-soft);
    }

    textarea.form-control{
        min-height:80px;
        resize: vertical;
    }

    .search-wrapper{
        margin-top: 6px;
    }

    .search-card{
        background: var(--card);
        border-radius: 16px;
        border:1px solid var(--border);
        padding: 10px 12px 12px;
    }

    .search-input{
        position: relative;
    }

    .search-input input{
        border-radius:999px !important;
        padding-left: 40px;
        font-size: 14px;
    }

    .search-input::before{
        content:'🔍';
        position:absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
        opacity:.65;
        pointer-events:none;
    }

    .table-wrapper{
        margin-top: 10px;
    }

    .table-card{
        background: var(--card);
        border-radius: 16px;
        border:1px solid var(--border);
        padding: 10px 10px 4px;
    }

    .table-responsive{
        border-radius: 12px;
    }

    #product_table thead{
        font-size: 12px;
    }

    #product_table tbody td{
        vertical-align: middle;
        font-size: 13px;
    }

    #product_table img{
        max-width:48px;
        border-radius:10px;
    }

    .quantity, .unit_price, .unit_discount{
        max-width: 90px;
        padding-inline: 6px;
        font-size: 12px;
    }

    .row_total{
        font-weight: 600;
    }

    .btn-remove-row{
        border-radius: 999px;
        padding: 3px 7px;
        font-size: 11px;
    }

    /* Mobile tweaks */
    @media (max-width: 767.98px){
        .section-box .row > [class^="col-"]{
            margin-bottom: 8px;
        }

        .table-card{
            padding: 8px;
        }

        .table-responsive{
            border:0;
        }

        #product_table{
            min-width:650px; /* horizontal scroll for mobile */
        }

        .btn.btn-success{
            width: 100%;
        }
    }

    .redx-title,
    .pathao-title{
        font-size: 13px;
        font-weight:600;
        color: var(--danger);
        margin-bottom: 8px;
    }

    .note-area textarea{
        border-radius: 12px !important;
    }

    .footer-actions{
        display:flex;
        justify-content:flex-end;
        margin-top: 6px;
    }

    @media (max-width: 575.98px){
        .footer-actions{
            justify-content:stretch;
        }
    }

    .btn-save-order{
        border-radius: 999px;
        padding-inline: 26px;
        font-weight: 600;
        letter-spacing:.03em;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">SIS</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">CRM</a></li>
                    <li class="breadcrumb-item active">Order Create</li>
                </ol>
            </div>
            <h4 class="page-title">Order Create</h4>
        </div>
    </div>
</div>

<div class="row order-layout">
    <div class="col-12">
        <div class="card order-card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.orders.store')}}" id="ajax_form">
                    @csrf

                    {{-- Order Info --}}
                    <div class="section-header">
                        <div class="section-title">
                            <span class="badge-dot"></span>
                            Order Information
                        </div>
                        <div class="section-sub">
                            Date, status & basic order details
                        </div>
                    </div>
                    <div class="section-box">
                        <div class="row g-2">
                            <div class="col-md-4 col-12">
                                <label class="form-label">Pick a Date</label>
                                <input type="date" class="form-control" required name="date" />
                            </div>
                            <div class="col-md-4 d-none">
                                <label for="validationDefault02" class="form-label">Invoice Number</label>
                                <input type="text" class="form-control" id="validationDefault02"  name="invoice_no" />
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="form-label">Order Status</label>
                                <select class="form-control" name="status">
                                    @foreach($status as $key=>$s)
                                        <option value="{{$key}}">{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Product Search --}}
                    <div class="search-wrapper">
                        <div class="section-header">
                            <div class="section-title">
                                <span class="badge-dot"></span>
                                Products
                            </div>
                            <div class="section-sub">
                                Search & add products into this order
                            </div>
                        </div>
                        <div class="search-card">
                            <div class="row justify-content-center">
                                <div class="col-md-8 col-12">
                                    <div class="search-input">
                                        <input type="text" id="search" class="form-control" placeholder="Search product by name or SKU...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Product Table --}}
                    <div class="table-wrapper">
                        <div class="table-card">
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap mb-0" id="product_table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Image</th>
                                            <th>Product</th>
                                            <th>Size</th>
                                            <th>Color</th>
                                            <th style="width: 120px;">Quantity</th>
                                            <th style="width: 150px;">Sell Price</th>
                                            <th style="width: 150px;">Discount</th>
                                            <th>Subtotal</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="data">
                                        {{-- JS will append rows here --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Customer & Courier --}}
                    <div class="section-header mt-3">
                        <div class="section-title">
                            <span class="badge-dot"></span>
                            Customer & Courier
                        </div>
                        <div class="section-sub">
                            Customer info & delivery method
                        </div>
                    </div>
                    <div class="section-box">
                        <div class="row g-2">
                            <div class="col-md-3 col-12">
                                <label class="form-label">Courier</label>
                                <select class="form-control" name="courier_id">
                                    <option value="" data-charge="0">Select One</option>
                                    @foreach($couriers as $courier)
                                        <option value="{{ $courier->id }}"> {{ $courier->name }} </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 col-12">
                                <label class="form-label">Customer First Name</label>
                                <input type="text" class="form-control" name="first_name" />
                            </div>

                            {{-- Last Name removed --}}

                            <div class="col-md-3 col-12">
                                <label class="form-label">Customer Mobile</label>
                                <input type="text" class="form-control" name="mobile" />
                            </div>

                            <div class="col-md-6 col-12">
                                <label class="form-label">Customer Address</label>
                                <textarea rows="3" name="shipping_address" class="form-control"></textarea>
                            </div>
                        </div>

                        {{-- Redx --}}
                        <div class="row for_redx d-none mt-2">
                            <div class="col-12 redx-title">
                                These fields only for Redx Courier Service
                            </div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Choose Area</label>
                                <select class="form-control select2" id="area_select">
                                    <option value="">Select One</option>
                                    @foreach($areas as $key=>$area)
                                        <option value="{{ $area['id'] }}">{{ $area['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Area ID</label>
                                <input type="text" readonly class="form-control" id="area_id" name="area_id" value=""/>
                            </div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Area Name</label>
                                <input type="text" readonly class="form-control" id="area_name" name="area_name" value=""/>
                            </div>
                        </div>

                        {{-- Pathao --}}
                        <div class="row for_pathao d-none mt-2">
                            <div class="col-12 pathao-title">
                                These fields only for Pathao Courier Service
                            </div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Choose City</label>
                                <select class="form-control select2" id="city_select" name="city">
                                    <option value="">Select One</option>
                                    @foreach($cities as $key=>$city)
                                        <option value="{{ $city['city_id'] }}">{{ $city['city_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Choose Zone</label>
                                <select class="form-control select2" id="zone_select" name="state">
                                    <option value="">Select One</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Choose Area</label>
                                <select class="form-control select2" name="area_id">
                                    <option value="">Select One</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-12">
                                <label class="form-label">Item Weight</label>
                                <input type="number" class="form-control" id="weight" step="0.5" min="0.5" max="10" name="weight" value="0.5"/>
                            </div>
                        </div>
                    </div>

                    {{-- Payment & Total --}}
                    <div class="section-header mt-2">
                        <div class="section-title">
                            <span class="badge-dot"></span>
                            Payment Summary
                        </div>
                        <div class="section-sub">
                            Delivery charge & grand total
                        </div>
                    </div>
                    <div class="section-box">
                        <div class="row g-2">
                            <div class="col-md-3 col-12">
                                <label class="form-label">Delivery Charge</label>
                                <select class="form-control" name="delivery_charge_id" id="delevery_charge">
                                    <option value="" data-charge="0">Select One</option>
                                    @foreach($charges as $charge)
                                        <option value="{{ $charge->id }}" data-charge="{{ $charge->amount }}">{{ $charge->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 col-12">
                                <label class="form-label">Total</label>
                                <input type="text" class="form-control" name="final_amount" id="purchase_total" />

                                <input type="hidden" value="0" name="shipping_charge" id="shipping_charge" />
                                {{-- Discount hidden field (backend এর জন্য) --}}
                                <input type="hidden" name="discount" id="discount_amount" value="0" />
                            </div>
                        </div>
                    </div>

                    {{-- Note --}}
                    <div class="section-header mt-2">
                        <div class="section-title">
                            <span class="badge-dot"></span>
                            Note
                        </div>
                        <div class="section-sub">
                            Any special instruction about this order
                        </div>
                    </div>
                    <div class="section-box note-area">
                        <textarea class="form-control" name="note" placeholder="Write any special instruction or note here..."></textarea>
                    </div>

                    {{-- Actions --}}
                    <div class="footer-actions">
                        <button class="btn btn-success btn-save-order" type="submit">
                            Save Order
                        </button>
                    </div>

                </form>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
    var path = "{{ route('admin.getOrderProduct') }}";
    const products = [];

    // সব discount ইনপুটকে disable করার ফাংশন
    function disableDiscountFields(){
        $('input.unit_discount').prop('disabled', true);
    }

    $("#search").autocomplete({
        selectFirst: true,
        minLength: 2,
        source: function(request, response){
            $.ajax({
                url: path,
                type: 'GET',
                dataType: "json",
                data: { search: request.term },
                success: function(data){
                    if(data.length == 0){
                        toastr.error('Product Or Stock Not Found');
                    }else if(data.length == 1){
                        if(products.indexOf(data[0].id) == -1){
                            productEntry(data[0]);
                            products.push(data[0].id);
                        }
                        $('#search').val('');
                    }else if(data.length > 1){
                        response(data);
                    }
                }
            });
        },
        select: function(event, ui){
            if(products.indexOf(ui.item.id) == -1){
                productEntry(ui.item);
                products.push(ui.item.id);
            }
            $('#search').val('');
            return false;
        }
    });

    function productEntry(item){
        $.ajax({
            url: '{{ route("admin.orderProductEntry")}}',
            type: 'GET',
            dataType: "json",
            data: {id:item.id},
            success: function(res){
                if(res.html){
                    $('tbody#data').append(res.html);
                    disableDiscountFields();   // নতুন row এও discount disable
                    calculateSum();
                }
            }
        });
    }

    // পুরোনো row থাকলেও (edit mode হলে) লোডের পর disable করে দিবে
    $(document).ready(function(){
        disableDiscountFields();
    });

    $(document).on('click', ".remove", function(e){
        var whichtr = $(this).closest("tr");
        whichtr.remove();
        calculateSum();
    });

    $(document).on('blur change', ".quantity", function(e){
        let current_stock = Number($(this).val());
        let stock = Number($(this).data('qty'));

        if(current_stock > stock){
            toastr.error('Product Stock Not Available');
            $(this).val(stock);
            calculateSum();
            return false;
        }
    });

    $(document).on('blur', ".quantity, .unit_price, .unit_discount", function(e){
        calculateSum();
    });

    $(document).on('change', "#delevery_charge", function(e){
        calculateSum();
    });

    $(document).on('change', 'select[name="courier_id"]', function(e){
        let courier_id = $(this).val();
        if(courier_id == 1){
            $(document).find('div.for_redx').removeClass('d-none');
            $(document).find('div.for_pathao').addClass('d-none');
        }else if(courier_id == 2){
            $(document).find('div.for_pathao').removeClass('d-none');
            $(document).find('div.for_redx').addClass('d-none');
        }else{
            $(document).find('div.for_pathao').addClass('d-none');
            $(document).find('div.for_redx').addClass('d-none');
        }
    });

    $(document).on('change', '#city_select', function(e){
        let city = $(this).val();
        var url = "{{ route('admin.zonesByCity', ":city") }}";
        url = url.replace(':city', city);
        $.ajax({
            url,
            type: 'GET',
            dataType: "json",
            success: function(res){
                if(res.success){
                    let html = "<option value=''>Select One</option>";
                    for(let i = 0; i < res.zones.length; i++){
                        html += "<option value='"+res.zones[i].zone_id+"' >"+res.zones[i].zone_name+"</option>";
                    }
                    $('#zone_select').html(html);
                }
            }
        });
    });

    $(document).on('change', '#zone_select', function(e){
        let zone = $(this).val();
        var url = "{{ route('admin.areasByZone', ":zone") }}";
        url = url.replace(':zone', zone);
        $.ajax({
            url,
            type: 'GET',
            dataType: "json",
            success: function(res){
                if(res.success){
                    let html = "<option value=''>Select One</option>";
                    for(let j = 0; j < res.areas.length; j++){
                        html += "<option value='"+res.areas[j].area_id+"' >"+res.areas[j].area_name+"</option>";
                    }
                    $('select[name="area_id"]').html(html);
                }
            }
        });
    });

    function calculateSum(){
        let tblrows = $("#product_table tbody tr");
        let sub_total = 0;
        let row_discount = 0;
        let charge = Number($("#delevery_charge option:selected").data('charge')) || 0;

        tblrows.each(function(index){
            let tblrow = $(this);
            let qty = Number(tblrow.find('td input.quantity').val()) || 0;
            let amount = Number(tblrow.find('td input.unit_price').val()) || 0;
            let discount = Number(tblrow.find('td input.unit_discount').val()) || 0; // যদিও disabled, তবু value read হবে

            let row_total = Number(qty * amount);
            row_discount += Number(qty * discount);

            tblrow.find('td.row_total').text(row_total.toFixed(2));
            sub_total += row_total;
        });

        sub_total += charge;

        $('input#purchase_total').val(sub_total.toFixed(2));
        // hidden discount ফিল্ডে মোট discount পাঠাচ্ছি
        $('input#discount_amount').val(row_discount.toFixed(2));
        $('input#shipping_charge').val(charge.toFixed(2));
    }
</script>
@endpush
