@extends('backend.app')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<style>
  :root{
    --bg:#f3f4f6;
    --card:#ffffff;
    --primary:#0ea5e9;
    --primary-soft:rgba(14,165,233,.08);
    --text:#0f172a;
    --muted:#6b7280;
    --border:#e5e7eb;
    --danger:#ef4444;
  }

  body{
    background:var(--bg);
  }

  .page-title-box{
    border:0;
    padding-bottom:0;
  }
  .page-title-box h4.page-title{
    font-weight:700;
    color:var(--text);
  }
  .page-title-right .breadcrumb{
    background:transparent;
  }

  /* Card look */
  .card{
    border:0;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 18px 45px rgba(15,23,42,.08);
    background:linear-gradient(135deg,#eff6ff 0,var(--card) 40%,var(--card) 100%);
  }
  .card-body{
    padding:24px 24px 28px;
  }

  /* Section header */
  .section-heading{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-bottom:10px;
    margin-top:8px;
  }
  .section-heading h5{
    font-size:1rem;
    font-weight:700;
    color:var(--text);
    margin:0;
  }
  .section-heading span.badge-tag{
    font-size:.75rem;
    text-transform:uppercase;
    letter-spacing:.06em;
    padding:.25rem .6rem;
    border-radius:999px;
    background:var(--primary-soft);
    color:var(--primary);
    font-weight:600;
  }

  /* Form controls */
  .form-label{
    font-weight:600;
    color:var(--text);
    font-size:.9rem;
    margin-bottom:.25rem;
  }
  .form-control,
  .form-select{
    border-radius:.8rem;
    border-color:var(--border);
    font-size:.9rem;
    padding:.55rem .75rem;
    box-shadow:none;
    background:#f9fafb;
  }
  .form-control:focus,
  .form-select:focus{
    border-color:var(--primary);
    box-shadow:0 0 0 2px var(--primary-soft);
    background:#ffffff;
  }

  /* Select2 skin */
  .select2-container--default .select2-selection--single{
    border-radius:.8rem !important;
    border:1px solid var(--border) !important;
    height:auto !important;
    padding:.25rem .5rem;
    background:#f9fafb;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered{
    padding-left:2px;
    padding-right:24px;
    line-height:1.5;
    font-size:.9rem;
    color:var(--text);
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow{
    height:100%;
  }

  /* Image preview */
  .img-box{
    position:relative;
    padding:5px;
  }
  .img-box a{
    position:absolute;
    top:2px;
    right:8px;
    font-size:18px;
    font-weight:700;
    color:var(--danger);
    line-height:1;
    text-decoration:none;
  }
  .preview-wrap img{
    width:54px;
    height:54px;
    object-fit:cover;
    border-radius:10px;
    border:1px solid #e5e7eb;
    margin-right:8px;
    margin-top:6px;
  }

  .media-box{
    border-radius:16px;
    border:1px dashed #d1d5db;
    padding:14px 14px 8px;
    background:#f9fafb;
  }

  /* Table → mobile cards */
  .table-responsive{
    border-radius:14px;
    border:1px solid #e5e7eb;
    background:#f9fafb;
    padding:6px;
  }
  table.responsive-table{
    margin-bottom:0;
  }
  table.responsive-table thead th{
    background:#eef2ff;
    border-color:#e5e7eb;
    font-size:.85rem;
    text-transform:uppercase;
    letter-spacing:.06em;
    color:#4b5563;
  }
  table.responsive-table tbody td{
    vertical-align:middle;
    font-size:.88rem;
  }

  @media (max-width: 576px){
    .card-body{
      padding:18px 14px 20px;
    }
    .page-title-right{
      margin-top:4px;
    }
    .page-title-right .breadcrumb{
      font-size:.78rem;
      flex-wrap:wrap;
    }

    .table-responsive{
      border:0;
      background:transparent;
      padding:0;
    }
    table.responsive-table thead{
      display:none;
    }
    table.responsive-table tbody tr{
      display:block;
      margin-bottom:12px;
      border-radius:14px;
      border:1px solid #e5e7eb;
      box-shadow:0 4px 14px rgba(15,23,42,.06);
      background:#ffffff;
      padding:10px 10px 6px;
    }
    table.responsive-table tbody td{
      display:flex;
      justify-content:space-between;
      gap:10px;
      border:0 !important;
      padding:.3rem 0 !important;
      font-size:13px !important;
    }
    table.responsive-table tbody td::before{
      content: attr(data-label);
      font-weight:600;
      color:#111827;
    }
  }

  /* Button */
  .btn-primary{
    background:linear-gradient(135deg,#0ea5e9,#2563eb);
    border:0;
    border-radius:999px;
    padding:.55rem 1.4rem;
    font-weight:600;
    box-shadow:0 12px 25px rgba(37,99,235,.35);
  }
  .btn-primary:hover{
    background:linear-gradient(135deg,#0284c7,#1d4ed8);
    box-shadow:0 16px 30px rgba(37,99,235,.45);
  }

  .btn-sm.btn-primary.add_moore{
    border-radius:999px;
  }
  .btn-sm.btn-danger.remove{
    border-radius:999px;
  }

  /* Tiny helper text */
  .text-muted{
    font-size:.8rem;
  }

</style>
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div>
        <h4 class="page-title mb-1">Product Update</h4>
        <small class="text-muted">Update product details, pricing, media & variations in one clean view.</small>
      </div>
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript:void(0)">SIS</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0)">CRM</a></li>
          <li class="breadcrumb-item active">Product Update</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="row mt-2">
  <div class="col-12">
    <div class="card">
      <div class="card-body">

        <form method="POST" action="{{ route('admin.products.update',[$item->id])}}" id="ajax_form" enctype="multipart/form-data">
          @csrf
          @method('PATCH')

          {{-- ===== Basic Info ===== --}}
          <div class="section-heading">
            <h5>Basic Information</h5>
            <span class="badge-tag">Step 1</span>
          </div>
          <div class="row g-3 mb-2">
            <div class="col-lg-4 col-md-6">
              <label class="form-label">Product Name</label>
              <input type="text" name="name" class="form-control" placeholder="Product Name" value="{{ $item->name }}">
            </div>

            <div class="col-lg-4 col-md-6">
              <label class="form-label">Product SKU</label>
              <input type="text" name="sku" class="form-control" placeholder="Product SKU" value="{{ $item->sku }}">
            </div>

            <div class="col-lg-4 col-md-6">
              <label class="form-label">Product Brand</label>
              <select class="form-select" name="type_id" id="type_id">
                <option value="">Select One</option>
                @foreach($types as $type)
                  <option value="{{$type->id}}" {{ $type->id == $item->type_id ? 'selected':'' }}>{{ $type->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-4 col-md-6">
              <label class="form-label">Product Category</label>
              <select class="form-select" name="category_id" id="category_id">
                <option value="">Select One</option>
                @foreach($cats as $cat)
                  <option value="{{$cat->id}}" {{$cat->id == $item->category_id ? 'selected':''}}>{{ $cat->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-4 col-md-6">
              <label class="form-label">Sub Category</label>
              <select class="form-select" name="sub_category_id" id="sub_category_id">
                <option value="">Select One</option>
                @foreach($subs as $sub)
                  <option value="{{$sub->id}}" {{ $sub->id == $item->sub_category_id ? 'selected':'' }}>{{ $sub->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-4 col-md-6">
              <label class="form-label">Video Embedded Code</label>
              <textarea name="video_link" class="form-control" rows="2" placeholder="<iframe>...">{{ $item->video_link }}</textarea>
            </div>
          </div>

          <hr class="my-3">

          {{-- ===== Media ===== --}}
          <div class="section-heading">
            <h5>Media & Gallery</h5>
            <span class="badge-tag">Step 2</span>
          </div>
          <div class="row g-3 mb-2">
            <div class="col-lg-4 col-md-6">
              <div class="media-box mb-2">
                <small class="d-block mb-1 text-muted">Current Image</small>
                <div class="d-flex align-items-center gap-2">
                  <img src="{{ getImage('products',$item->image)}}" width="60" height="60" class="rounded" style="object-fit:cover;border:1px solid #e5e7eb;">
                  <span class="text-muted" style="font-size:.8rem;">You can replace this image from below.</span>
                </div>
              </div>
              <label class="form-label">New Image (optional)</label>
              <input type="file" name="image" id="image_single" class="form-control" accept="image/*">
              <div id="preview_single" class="preview-wrap d-flex"></div>
            </div>

            <div class="col-lg-8 col-md-6">
              <div class="media-box mb-2">
                <small class="d-block mb-1 text-muted">Current Gallery</small>
                <div class="d-flex flex-wrap mb-1">
                  @foreach ($item->images as $image)
                    <div class="img-box">
                      <a href="{{ route('admin.deleteImage',[$image->id])}}" onclick="return confirm('Delete this image?')">&times;</a>
                      <img src="{{ getImage('products',$image->image)}}" width="54" height="54" style="object-fit:cover;border-radius:10px;border:1px solid #eee;">
                    </div>
                  @endforeach
                  @if($item->images->count() == 0)
                    <span class="text-muted" style="font-size:.8rem;">No gallery images added yet.</span>
                  @endif
                </div>
              </div>
              <label class="form-label">Add / Replace Gallery Images</label>
              <input type="file" name="images[]" id="images_multi" class="form-control" multiple accept="image/*">
              <div id="preview_multi" class="preview-wrap d-flex flex-wrap"></div>
            </div>
          </div>

          <hr class="my-3">

          {{-- ===== Pricing & Stock ===== --}}
          <div class="section-heading">
            <h5>Pricing & Stock</h5>
            <span class="badge-tag">Step 3</span>
          </div>
          <div class="row g-3 mb-2">
            <div class="col-lg-3 col-md-6">
              <label class="form-label">Purchase Price</label>
              <input type="number" step="any" name="purchase_prices" class="form-control" value="{{ $item->purchase_prices }}">
            </div>

            <div class="col-lg-3 col-md-6">
              <label class="form-label">Sell Price</label>
              <input type="number" step="any" id="sell_price" name="sell_price" class="form-control" value="{{ $item->sell_price }}">
            </div>

            <div class="col-lg-3 col-md-6">
              <label class="form-label">After Discount</label>
              <input type="number" step="any" id="after_discount" name="after_discount" class="form-control after_discount" value="{{ $item->after_discount }}">
            </div>

            <div class="col-lg-3 col-md-6">
              <label class="form-label">Product Type</label>
              <select name="type" id="prod_type" class="form-control">
                <option value="single" {{ $item->type=='single'?'selected':'' }}>Single</option>
                <option value="variable" {{ $item->type=='variable'?'selected':'' }}>Variable</option>
              </select>
            </div>

            <div class="col-lg-3 col-md-6">
              <label class="form-label">Manage Stock</label>
              <select name="is_stock" class="form-control" id="is_stock">
                <option value="0" {{ (string)($item->is_stock ?? 0) === '0' ? 'selected':'' }}>No</option>
                <option value="1" {{ (string)($item->is_stock ?? 0) === '1' ? 'selected':'' }}>Yes</option>
              </select>
            </div>

            <div id="stock_qty" class="col-lg-3 col-md-6">
              <label class="form-label">Stock Quantity</label>
              <input type="number" step="any" name="pro_quantity" class="form-control quantity" value="{{ $item->stock_quantity ?? 1 }}">
            </div>

            <div class="col-lg-3 col-md-6">
              <label class="form-label">Discount Type</label>
              <select class="form-select" name="discount_type" id="discount_type">
                <option value="">Select Discount Type</option>
                <option value="fixed" {{ $item->discount_type == 'fixed' ? 'selected' : '' }}>Fixed</option>
                <option value="percentage" {{ $item->discount_type == 'percentage' ? 'selected' : '' }}>Percentage</option>
              </select>
            </div>
            <div class="col-lg-3 col-md-6">
              <label class="form-label">Discount Amount</label>
              <input type="number" step="any" name="dicount_amount" id="dicount_amount" class="form-control dicount_amount" value="{{ $item->dicount_amount }}">
            </div>
          </div>

          <small class="text-muted d-block mb-2">
            
          </small>

          <hr class="my-3">

          {{-- ===== Variations ===== --}}
          <div id="variable_table_two" class="{{ $item->type=='variable' ? '' : 'd-none' }}">
            <div class="section-heading mb-2">
              <h5>Variations (Size / Color)</h5>
              <span class="badge-tag">Step 4</span>
            </div>

            <div class="table-responsive">
              <table class="table table-centered table-nowrap table-bordered text-center align-middle responsive-table">
                <thead class="table-light">
                  <tr>
                    <th>Size</th>
                    <th>Color</th>
                    <th style="width:18%;">Purchase</th>
                    <th style="width:18%;">Price</th>
                    <th style="width:18%;">Discount</th>
                    <th style="width:18%;">Stock Qty</th>
                    <th style="width:10%;">Action</th>
                  </tr>
                </thead>
                <tbody id="varBody">
                @forelse($item->variations as $v)
                  <tr>
                    <td data-label="Size">
                      <input type="hidden" name="variation_id[]" value="{{$v->id}}">
                      <input type="hidden" name="product_id[]" value="{{$item->id}}">
                      <select name="size_id[]" class="form-select">
                        @foreach($sizes as $size)
                          <option value="{{$size->id}}" {{ $size->id==$v->size_id ? 'selected':'' }}>{{ $size->title }}</option>
                        @endforeach
                      </select>
                    </td>
                    <td data-label="Color">
                      <select name="color_id[]" class="form-select">
                        @foreach($colors as $color)
                          <option value="{{$color->id}}" {{ $color->id==$v->color_id ? 'selected':'' }}>{{ $color->name }}</option>
                        @endforeach
                      </select>
                    </td>
                    <td data-label="Purchase">
                      <input class="variable_purchase_price form-control" type="number" step="any" value="{{ $v->purchase_price }}" name="purchase_price[]" placeholder="Purchase">
                    </td>
                    <td data-label="Price">
                      <input class="variable_sell_price form-control" type="number" step="any" value="{{ $v->price }}" name="price[]" placeholder="Price">
                    </td>
                    <td data-label="Discount">
                      <input class="variable_dis_price form-control" type="number" step="any" value="{{ $v->after_discount_price }}" name="after_discount_price[]" placeholder="Discount">
                    </td>
                    <td data-label="Qty">
                      <input class="quantity form-control" type="number" step="any" value="{{ $v->stock_quantity }}" name="quantity[]" placeholder="Qty">
                    </td>
                    <td data-label="Action">
                      <a class="action-icon btn btn-sm btn-primary add_moore"><i class="mdi mdi-plus"></i></a>
                      <a class="action-icon btn btn-sm btn-danger remove"><i class="mdi mdi-delete"></i></a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td data-label="Size">
                      <select name="size_id[]" class="form-select">
                        @foreach($sizes as $size)
                          <option value="{{ $size->id }}">{{ $size->title }}</option>
                        @endforeach
                      </select>
                    </td>
                    <td data-label="Color">
                      <select name="color_id[]" class="form-select">
                        @foreach($colors as $color)
                          <option value="{{ $color->id }}">{{ $color->name }}</option>
                        @endforeach
                      </select>
                    </td>
                    <td data-label="Purchase">
                      <input class="variable_purchase_price form-control" type="number" step="any" name="purchase_price[]" placeholder="Purchase">
                    </td>
                    <td data-label="Price">
                      <input class="variable_sell_price form-control" type="number" step="any" name="price[]" placeholder="Price">
                    </td>
                    <td data-label="Discount">
                      <input class="variable_dis_price form-control" type="number" step="any" name="after_discount_price[]" placeholder="Discount">
                    </td>
                    <td data-label="Qty">
                      <input class="quantity form-control" type="number" step="any" name="quantity[]" placeholder="Qty">
                    </td>
                    <td data-label="Action">
                      <a class="action-icon btn btn-sm btn-primary add_moore"><i class="mdi mdi-plus"></i></a>
                      <a class="action-icon btn btn-sm btn-danger remove"><i class="mdi mdi-delete"></i></a>
                    </td>
                  </tr>
                @endforelse
                </tbody>
              </table>
            </div>
            <small class="text-muted d-block mt-1">
              
            </small>
          </div>

          <hr class="my-3">

          {{-- ===== Content ===== --}}
          <div class="section-heading">
            <h5>Product Description</h5>
            <span class="badge-tag">Step 5</span>
          </div>
          <div class="row">
            <div class="col-12">
              <label class="form-label">Product Body</label>
              <textarea class="form-control" name="body" id="body" rows="6">{!! $item->body !!}</textarea>
            </div>

            <div class="col-12 mt-3 d-flex justify-content-end">
              <button type="submit" class="btn btn-primary px-4">Update Product</button>
            </div>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script>
$(function(){

  /* ===== Select2 ===== */
  $('#type_id,#category_id,#sub_category_id,#prod_type,#is_stock,#discount_type').select2({ width:'100%' });

  /* ===== Summernote ===== */
  $('#body').summernote({
    height: 300,
    toolbar: [
      ['style', ['style']],
      ['font', ['bold','italic','underline','clear']],
      ['fontsize', ['fontsize']],
      ['color', ['color']],
      ['para', ['ul','ol','paragraph']],
      ['insert', ['link','picture','video','hr']],
      ['view', ['fullscreen','codeview','help']]
    ],
    callbacks:{
      onImageUpload: function(files){
        let data = new FormData();
        data.append('upload', files[0]);
        data.append('_token', '{{ csrf_token() }}');
        $.ajax({
          url: "{{ route('admin.ckeditor.upload') }}",
          type: "POST",
          data,
          processData:false,
          contentType:false,
          success: function(resp){
            if(resp.url) $('#body').summernote('insertImage', resp.url);
            else alert('Image upload failed.');
          },
          error: function(){ alert('Upload error.'); }
        });
      }
    }
  });

  /* ===== Image previews ===== */
  $('#image_single').on('change', function(e){
    const f = e.target.files[0]; if(!f) return;
    $('#preview_single').html(`<img src="${URL.createObjectURL(f)}" alt="preview">`);
  });
  $('#images_multi').on('change', function(e){
    $('#preview_multi').empty();
    [...e.target.files].forEach(f=>{
      $('#preview_multi').append(`<img src="${URL.createObjectURL(f)}" alt="preview">`);
    });
  });

  /* ===== Category → Subcategory ===== */
  $('#category_id').on('change', function(){
    const cat_id = $(this).val();
    if(!cat_id){
      $('#sub_category_id').html('<option value="">Select One</option>').trigger('change');
      return;
    }
    $.getJSON('{{ route("admin.getSubcategory")}}', { cat_id }, function(data){
      let html = '<option value="">Select One</option>';
      $.each(data, function(key, value){ html += `<option value="${key}">${value}</option>`; });
      $('#sub_category_id').html(html).trigger('change');
    });
  });

  /* ===== Product type toggle ===== */
  function toggleVariantTable(){
    ($('#prod_type').val()==='variable') ? $('#variable_table_two').removeClass('d-none') : $('#variable_table_two').addClass('d-none');
  }
  $('#prod_type').on('change', toggleVariantTable);
  toggleVariantTable();

  /* ===== Stock toggle ===== */
  function toggleStock(){
    ($('#is_stock').val()==='1') ? $('#stock_qty').show() : $('#stock_qty').hide();
  }
  $('#is_stock').on('change', toggleStock);
  toggleStock();

  /* ===== Discount Live Calc ===== */
  function calcDiscount(base, dtype, damount){
    base = parseFloat(base||0); damount = parseFloat(damount||0);
    if(!dtype) return base;
    if(dtype==='percentage') return (base - (base*(damount/100)));
    return (base - damount);
  }
  function syncMainAfterDiscount(){
    const price = calcDiscount($('#sell_price').val(), $('#discount_type').val(), $('#dicount_amount').val());
    if(!isNaN(price)) $('#after_discount').val(price.toFixed(2));
  }
  $('#sell_price,#discount_type,#dicount_amount').on('input change', function(){
    syncMainAfterDiscount();
    $('.variable_sell_price').each(function(){
      const row = $(this).closest('tr');
      const vprice = calcDiscount($(this).val(), $('#discount_type').val(), $('#dicount_amount').val());
      if(!isNaN(vprice)) row.find('.variable_dis_price').val(vprice.toFixed(2));
    });
  });
  syncMainAfterDiscount();

  // Variation sell change -> its discount
  $(document).on('input','.variable_sell_price', function(){
    const row = $(this).closest('tr');
    const v = calcDiscount($(this).val(), $('#discount_type').val(), $('#dicount_amount').val());
    if(!isNaN(v)) row.find('.variable_dis_price').val(v.toFixed(2));
  });

  /* ===== Auto-fill helpers ===== */
  $('input[name="sell_price"]').on('blur', function(){ $('.variable_sell_price').val($(this).val()); });
  $('input[name="pro_quantity"]').on('blur', function(){ $('.quantity,.variant_qty').val($(this).val()); });
  $('input.after_discount').on('blur', function(){ $('.variable_dis_price').val($(this).val()); });

  /* ===== Variation Row Add/Remove ===== */
  $(document).on('click','.add_moore', function(e){
    e.preventDefault();
    const row = $(this).closest('tr');
    const p = row.find('.variable_purchase_price').val() || '';
    const s = row.find('.variable_sell_price').val() || '';
    const d = row.find('.variable_dis_price').val() || '';
    const q = row.find('.quantity').val() || '';

    const tpl = `
    <tr>
      <td data-label="Size">
        <select name="size_id[]" class="form-select">
          @foreach($sizes as $size)
            <option value="{{$size->id}}">{{ $size->title }}</option>
          @endforeach
        </select>
      </td>
      <td data-label="Color">
        <select name="color_id[]" class="form-select">
          @foreach($colors as $color)
            <option value="{{$color->id}}">{{ $color->name }}</option>
          @endforeach
        </select>
      </td>
      <td data-label="Purchase"><input class="variable_purchase_price form-control" type="number" step="any" value="${p}" name="purchase_price[]" placeholder="Purchase"></td>
      <td data-label="Price"><input class="variable_sell_price form-control" type="number" step="any" value="${s}" name="price[]" placeholder="Price"></td>
      <td data-label="Discount"><input class="variable_dis_price form-control" type="number" step="any" value="${d}" name="after_discount_price[]" placeholder="Discount"></td>
      <td data-label="Qty"><input class="quantity form-control" type="number" step="any" value="${q}" name="quantity[]" placeholder="Qty"></td>
      <td data-label="Action">
        <a class="action-icon btn btn-sm btn-primary add_moore"><i class="mdi mdi-plus"></i></a>
        <a class="action-icon btn btn-sm btn-danger remove"><i class="mdi mdi-delete"></i></a>
      </td>
    </tr>`;
    $('#varBody').append(tpl);
  });

  $(document).on('click','.remove', function(e){
    e.preventDefault();
    if($('#varBody tr').length <= 1) return;
    $(this).closest('tr').remove();
  });

});
</script>
@endpush
