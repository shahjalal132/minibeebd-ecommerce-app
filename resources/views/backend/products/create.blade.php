@extends('backend.app')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<style>
  /* Page polish */
  .section-title{
    font-weight:700; font-size:1.05rem; color:#111; margin:2px 0 10px;
    display:flex; align-items:center; gap:8px;
  }
  .section-title:before{
    content:""; width:6px; height:18px; border-radius:4px; background:#0d6efd; display:inline-block;
  }
  .soft-card{ border:1px solid #eef0f2; border-radius:12px; padding:14px; background:#fff; }

  /* Image previews */
  .preview-wrap img{ width:54px; height:54px; object-fit:cover; border-radius:8px; border:1px solid #eee; margin-right:8px; margin-top:6px; }

  /* Mobile friendly table -> cards */
  @media (max-width: 576px){
    .table-responsive{ border:0; }
    table.responsive-table thead{ display:none; }
    table.responsive-table tbody tr{
      display:block; margin-bottom:12px; border:1px solid #eee; border-radius:12px; padding:12px;
    }
    table.responsive-table tbody td{
      display:flex; justify-content:space-between; gap:10px; border:0 !important; padding:.3rem 0 !important;
      font-size:13px !important;
    }
    table.responsive-table tbody td::before{
      content: attr(data-label);
      font-weight:600; color:#333;
    }
  }

  /* Form spacing (mobile-first) */
  .form-label{ font-weight:600; color:#222; }
  .select2-container--default .select2-selection--single{ height:38px; }
  .select2-container--default .select2-selection--single .select2-selection__rendered{ line-height:38px; }
  .select2-container--default .select2-selection--single .select2-selection__arrow{ height:36px; }
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
          <li class="breadcrumb-item active">Product Create</li>
        </ol>
      </div>
      <h4 class="page-title">Product Create</h4>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">

        <form method="POST" action="{{ route('admin.products.store') }}" id="ajax_form" enctype="multipart/form-data">
          @csrf

          {{-- Basic Info --}}
          <div class="soft-card mb-3">
            <div class="section-title">Basic Information</div>
            <div class="row g-3">
              <div class="col-lg-4 col-md-6">
                <label class="form-label">Product Name</label>
                <input type="text" name="name" class="form-control" placeholder="Product Name">
              </div>

              <div class="col-lg-4 col-md-6">
                <label class="form-label">Product SKU</label>
                <input type="text" name="sku" class="form-control" placeholder="Product SKU">
              </div>

              <div class="col-lg-4 col-md-6">
                <label class="form-label">Product Brand</label>
                <select class="form-select" name="type_id" id="type_id">
                  <option value="">Select One</option>
                  @foreach($types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-lg-4 col-md-6">
                <label class="form-label">Product Category</label>
                <select class="form-select" name="category_id" id="category_id">
                  <option value="">Select One</option>
                  @foreach($cats as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-lg-4 col-md-6">
                <label class="form-label">Sub Category</label>
                <select class="form-select" name="sub_category_id" id="sub_category_id">
                  <option value="">Select One</option>
                </select>
              </div>

              <div class="col-lg-4 col-md-6">
                <label class="form-label">Video Embedded Code</label>
                <textarea name="video_link" class="form-control" rows="1" placeholder="<iframe>...</iframe>"></textarea>
              </div>
            </div>
          </div>

          {{-- Media --}}
          <div class="soft-card mb-3">
            <div class="section-title">Media</div>
            <div class="row g-3">
              <div class="col-lg-4 col-md-6">
                <label class="form-label">Image</label>
                <input type="file" name="image" id="image_single" class="form-control" accept="image/*">
                <div id="preview_single" class="preview-wrap d-flex"></div>
              </div>

              <div class="col-lg-8 col-md-6">
                <label class="form-label">Multi Image</label>
                <input type="file" name="images[]" id="images_multi" class="form-control" multiple accept="image/*">
                <div id="preview_multi" class="preview-wrap d-flex flex-wrap"></div>
              </div>
            </div>
          </div>

          {{-- Pricing & Stock --}}
          <div class="soft-card mb-3">
            <div class="section-title">Pricing & Stock</div>
            <div class="row g-3">
              <div class="col-lg-3 col-md-6">
                <label class="form-label">Purchase Price</label>
                <input type="number" step="any" name="purchase_prices" class="form-control" placeholder="Purchase Price">
              </div>

              <div class="col-lg-3 col-md-6">
                <label class="form-label">Sell Price</label>
                <input type="number" step="any" name="sell_price" id="sell_price" class="form-control" placeholder="Sell Price">
              </div>

              <div class="col-lg-3 col-md-6">
                <label class="form-label">After Discount</label>
                <input type="number" step="any" name="after_discount" id="after_discount" class="form-control after_discount" placeholder="After Discount">
              </div>

              <div class="col-lg-3 col-md-6">
                <label class="form-label">Product Type</label>
                <select name="type" id="prod_type" class="form-control">
                  <option value="single">Single</option>
                  <option value="variable" selected>Variable</option>
                </select>
              </div>

              <div class="col-lg-3 col-md-6">
                <label class="form-label">Manage Stock</label>
                <select name="is_stock" class="form-control" id="is_stock">
                  <option value="0" selected>No</option>
                  <option value="1">Yes</option>
                </select>
              </div>

              <div id="stock_qty" class="col-lg-3 col-md-6">
                <label class="form-label">Stock Quantity</label>
                <input type="number" step="any" name="pro_quantity" class="form-control quantity" value="1">
              </div>
            </div>
          </div>

          {{-- Variations --}}
          <div id="variable_table_two" class="soft-card mb-3">
            <div class="section-title">Variations</div>
            <div class="table-responsive">
              <table class="table table-centered table-nowrap table-bordered text-center align-middle responsive-table">
                <thead class="table-light">
                  <tr>
                    <th>Size</th>
                    <th>Color</th>
                    <th style="width:18%;">Purchase Price</th>
                    <th style="width:18%;">Price</th>
                    <th style="width:18%;">Discount Price</th>
                    <th style="width:18%;">Stock Quantity</th>
                    <th style="width:10%;">Action</th>
                  </tr>
                </thead>
                <tbody id="variant_tbody">
                  <tr>
                    <td data-label="Size">
                      <select name="size_id[]" class="form-control">
                        @foreach($sizes as $size)
                          <option {{ $size->is_default==1 ? 'selected' : '' }} value="{{ $size->id }}">{{ $size->title }}</option>
                        @endforeach
                      </select>
                    </td>
                    <td data-label="Color">
                      <select name="color_id[]" class="form-control">
                        @foreach($colors as $color)
                          <option {{ $color->is_default==1 ? 'selected' : '' }} value="{{ $color->id }}">{{ $color->name }}</option>
                        @endforeach
                      </select>
                    </td>
                    <td data-label="Purchase"><input class="variable_purchase_price form-control" type="number" step="any" name="purchase_price[]" placeholder="Purchase Price"></td>
                    <td data-label="Price"><input class="variable_sell_price form-control" type="number" step="any" name="price[]" placeholder="Price"></td>
                    <td data-label="Discount"><input class="variable_dis_price form-control" type="number" step="any" name="after_discount_price[]" placeholder="Discount Price"></td>
                    <td data-label="Qty"><input class="variant_qty form-control" type="number" step="any" name="quantity[]" placeholder="Stock Quantity"></td>
                    <td data-label="Action">
                      <a class="btn btn-sm btn-primary add_row"><i class="mdi mdi-plus"></i></a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <small class="text-muted">Tip: Main “Sell / After Discount / Quantity” ফিল্ডে দিলে নিচের রো গুলো auto-fill হবে।</small>
          </div>

          {{-- Content --}}
          <div class="soft-card mb-3">
            <div class="section-title">Content</div>
            <div class="row g-3">
              <div class="col-12 d-none">
                <label class="form-label">Feature</label>
                <textarea id="feature" class="form-control" name="feature" rows="5"></textarea>
              </div>
              <div class="col-12">
                <label class="form-label">Product Body</label>
                <textarea id="body" class="form-control" name="body" rows="6"></textarea>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success px-4">Save</button>
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
$(function () {
  /* ===== Select2 ===== */
  $('#type_id, #category_id, #sub_category_id, #prod_type, #is_stock').select2({ width: '100%' });

  /* ===== Summernote ===== */
  initSummernote('#feature', 200);
  initSummernote('#body', 300);
  function initSummernote(selector, height){
    $(selector).summernote({
      height,
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
          let editor = $(this), data = new FormData();
          data.append('upload', files[0]);
          data.append('_token','{{ csrf_token() }}');
          $.ajax({
            url: "{{ route('admin.ckeditor.upload') }}",
            type: "POST", data, processData:false, contentType:false,
            success: function(resp){ resp.url ? editor.summernote('insertImage', resp.url) : alert('Image upload failed'); },
            error: function(){ alert('Upload error'); }
          });
        }
      }
    });
  }

  /* ===== Category → Subcategory ===== */
  $('#category_id').on('change', function(){
    let cat_id = $(this).val();
    if(!cat_id){ $('#sub_category_id').html('<option value="">Select One</option>').trigger('change'); return; }
    $.get('{{ route("admin.getSubcategory") }}', {cat_id}, function(data){
      let html = '<option value="">Select One</option>';
      $.each(data, function(k,v){ html += `<option value="${k}">${v}</option>`; });
      $('#sub_category_id').html(html).trigger('change');
    }, 'json');
  });

  /* ===== Product Type toggle ===== */
  function toggleVariantTable(){
    const type = $('#prod_type').val();
    (type === 'variable') ? $('#variable_table_two').slideDown(150) : $('#variable_table_two').slideUp(150);
  }
  $('#prod_type').on('change', toggleVariantTable); toggleVariantTable();

  /* ===== Stock toggle ===== */
  function toggleStock(){
    ($('#is_stock').val() === '1') ? $('#stock_qty').show() : $('#stock_qty').hide();
  }
  $('#is_stock').on('change', toggleStock); toggleStock();

  /* ===== Image previews ===== */
  $('#image_single').on('change', function(e){
    const f = e.target.files[0]; if(!f) return;
    const url = URL.createObjectURL(f);
    $('#preview_single').html(`<img src="${url}" alt="preview">`);
  });
  $('#images_multi').on('change', function(e){
    $('#preview_multi').empty();
    [...e.target.files].forEach(f=>{
      const url = URL.createObjectURL(f);
      $('#preview_multi').append(`<img src="${url}" alt="preview">`);
    });
  });

  /* ===== Auto-fill helpers ===== */
  $('input[name="sell_price"]').on('blur', function(){ $('.variable_sell_price').val($(this).val()); });
  $('input[name="pro_quantity"]').on('blur', function(){ $('.variant_qty').val($(this).val()); });
  $('input.after_discount').on('blur', function(){ $('.variable_dis_price').val($(this).val()); });

  /* ===== Variant Row Add / Remove ===== */
  $(document).on('click','.add_row', function(){
    const row = $(this).closest('tr');
    const p = row.find('.variable_purchase_price').val() || '';
    const s = row.find('.variable_sell_price').val() || '';
    const d = row.find('.variable_dis_price').val() || '';
    const q = row.find('.variant_qty').val() || '';
    const tpl = `
      <tr>
        <td data-label="Size">
          <select name="size_id[]" class="form-control">
            @foreach($sizes as $size)
              <option value="{{ $size->id }}">{{ $size->title }}</option>
            @endforeach
          </select>
        </td>
        <td data-label="Color">
          <select name="color_id[]" class="form-control">
            @foreach($colors as $color)
              <option value="{{ $color->id }}">{{ $color->name }}</option>
            @endforeach
          </select>
        </td>
        <td data-label="Purchase"><input class="variable_purchase_price form-control" type="number" step="any" name="purchase_price[]" value="${p}" placeholder="Purchase Price"></td>
        <td data-label="Price"><input class="variable_sell_price form-control" type="number" step="any" name="price[]" value="${s}" placeholder="Price"></td>
        <td data-label="Discount"><input class="variable_dis_price form-control" type="number" step="any" name="after_discount_price[]" value="${d}" placeholder="Discount Price"></td>
        <td data-label="Qty"><input class="variant_qty form-control" type="number" step="any" name="quantity[]" value="${q}" placeholder="Stock Quantity"></td>
        <td data-label="Action">
          <a class="btn btn-sm btn-primary add_row"><i class="mdi mdi-plus"></i></a>
          <a class="btn btn-sm btn-danger remove_row"><i class="mdi mdi-delete"></i></a>
        </td>
      </tr>`;
    $('#variant_tbody').append(tpl);
  });
  $(document).on('click','.remove_row', function(){
    if($('#variant_tbody tr').length <= 1) return;
    $(this).closest('tr').remove();
  });

});
</script>
@endpush
