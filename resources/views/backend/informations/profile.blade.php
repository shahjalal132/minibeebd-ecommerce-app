@extends('backend.app')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<style>
  .card { border-radius: 14px; }
  .page-title-box { padding-bottom: 0; }
  .section-title{font-weight:700;font-size:1.05rem;margin-bottom:.75rem}
  .preview-wrap img{border:1px solid #e9ecef;border-radius:10px;max-width:110px;height:auto}
  .sticky-actions{position:sticky;bottom:0;background:#fff;z-index:5;padding:.75rem 1rem;border-top:1px solid #eee;display:flex;gap:.5rem;justify-content:flex-end}
  .help-text{font-size:.85rem;opacity:.85}
  .is-invalid{border-color:#dc3545}
  .spinner{
    width: 16px;height: 16px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;display:inline-block;vertical-align:middle;animation:spin .7s linear infinite;margin-right:6px
  }
  @keyframes spin{to{transform:rotate(360deg)}}
  @media(max-width:576px){
    .breadcrumb{display:none}
  }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript:void(0)">SIS</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0)">CRM</a></li>
          <li class="breadcrumb-item active">My Account</li>
        </ol>
      </div>
      <h4 class="page-title">My Account</h4>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <a href="{{route('admin.dashboard')}}" class="btn btn-outline-secondary btn-sm">← Back</a>

        <div id="message" class="mt-3"></div>

        <form action="{{route('admin.profile.update')}}" method="POST" enctype="multipart/form-data" id="profile_update_form" class="mt-2">
          @csrf

          <div class="section-title">Basic Info</div>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="first_name" class="form-label fw-semibold">First Name</label>
              <input type="text" id="first_name" class="form-control" name="first_name" placeholder="First name..." value="{{ $data->first_name }}">
              <small class="text-danger d-block" id="first_name_error"></small>
            </div>
            <div class="col-md-6">
              <label for="last_name" class="form-label fw-semibold">Last Name</label>
              <input type="text" id="last_name" class="form-control" name="last_name" placeholder="Last name..." value="{{ $data->last_name }}">
              <small class="text-danger d-block" id="last_name_error"></small>
            </div>

            <div class="col-md-6">
              <label for="email" class="form-label fw-semibold">Email Address</label>
              <input type="email" id="email" class="form-control" name="email" placeholder="Email address..." value="{{ $data->email }}" disabled>
              <small class="help-text">Email পরিবর্তন করতে চাইলে সাপোর্টে যোগাযোগ করুন।</small>
              <small class="text-danger d-block" id="email_error"></small>
            </div>

            <div class="col-md-6">
              <label for="username" class="form-label fw-semibold">Username</label>
              <input type="text" id="username" class="form-control" name="username" placeholder="Username..." value="{{ $data->username }}" pattern="^[a-zA-Z0-9._-]{3,}$" maxlength="30">
              <small class="help-text">Only letters, numbers, dot, underscore, hyphen (min 3 chars)</small>
              <small class="text-danger d-block" id="username_error"></small>
            </div>

            <div class="col-md-6">
              <label for="mobile" class="form-label fw-semibold">Phone</label>
              <input type="tel" inputmode="tel" id="mobile" class="form-control" name="mobile" placeholder="+8801XXXXXXXXX" value="{{ $data->mobile }}">
              <small class="text-danger d-block" id="mobile_error"></small>
            </div>

            <div class="col-md-6">
              <label for="business_name" class="form-label fw-semibold">Business Name</label>
              <input type="text" id="business_name" class="form-control" name="business_name" placeholder="Business name..." value="{{ $data->business_name }}">
              <small class="text-danger d-block" id="business_name_error"></small>
            </div>
          </div>

          <hr class="my-4">

          <div class="section-title">Profile Photo</div>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="image" class="form-label fw-semibold">Upload Image</label>
              <input type="file" id="image" class="form-control" name="image" accept="image/*">
              <small class="help-text d-block">Max 1MB, JPG/PNG/WebP</small>
              <small class="text-danger d-block" id="image_error"></small>
            </div>
            <div class="col-md-6">
              <div class="preview-wrap mt-2">
                <img src="{{ asset('uploads/img/'.$data->image) }}" alt="Profile" id="preview_img">
              </div>
            </div>
          </div>

          <div class="sticky-actions mt-4">
            <button type="submit" class="btn btn-success" id="updateBtn">
              Update
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script>
  // --- helpers ---
  function setError(field, msg){
    const input = document.getElementById(field);
    const target = document.getElementById(field + '_error');
    if (input) input.classList.add('is-invalid');
    if (target) target.textContent = msg || '';
  }
  function clearError(field){
    const input = document.getElementById(field);
    const target = document.getElementById(field + '_error');
    if (input) input.classList.remove('is-invalid');
    if (target) target.textContent = '';
  }
  function showSuccess(msg){
    const box = document.getElementById('message');
    box.innerHTML = `<div class="alert alert-success mb-0"><strong>${msg}</strong></div>`;
    setTimeout(()=> box.innerHTML = '', 3000);
  }
  function showErrors(errors){
    const keys = ['first_name','last_name','username','mobile','business_name','image'];
    keys.forEach(k=>{
      if(errors[k]) setError(k, errors[k][0]);
      else clearError(k);
    });
  }

  // image preview + validation
  const imageInput = document.getElementById('image');
  const previewImg = document.getElementById('preview_img');
  if(imageInput && previewImg){
    imageInput.addEventListener('change', (e)=>{
      const file = e.target.files?.[0];
      if(!file) return;
      const validTypes = ['image/jpeg','image/png','image/webp'];
      if(!validTypes.includes(file.type)){
        setError('image', 'Only JPG, PNG or WEBP allowed.');
        imageInput.value=''; return;
      }
      if(file.size > 1024*1024){ // 1MB
        setError('image', 'Max size 1MB.');
        imageInput.value=''; return;
      }
      clearError('image');
      previewImg.src = URL.createObjectURL(file);
    });
  }

  // clear errors while typing
  ['first_name','last_name','username','mobile','business_name'].forEach(id=>{
    const el = document.getElementById(id);
    if(el){
      el.addEventListener('input', ()=> clearError(id));
      el.addEventListener('change', ()=> clearError(id));
    }
  });

  // submit via AJAX
  $(document).ready(function(){
    $("#profile_update_form").on('submit', function(e){
      e.preventDefault();
      const $btn = $("#updateBtn");
      const original = $btn.html();
      const data = new FormData(this);

      $.ajax({
        url: $(this).attr("action"),
        method: $(this).attr("method"),
        data,
        contentType:false,
        processData:false,
        beforeSend: function(){
          $btn.prop('disabled', true).html(`<span class="spinner"></span>Saving...`);
        },
        success: function(res){
          $btn.prop('disabled', false).html(original);

          if(res?.errors){
            showErrors(res.errors);
          } else if(res?.success){
            // clear all error states
            ['first_name','last_name','username','mobile','business_name','image'].forEach(clearError);
            showSuccess(res.success);

            // optional: update preview if backend returned new image path
            if(res.image_url){
              $("#preview_img").attr('src', res.image_url);
            }
          } else {
            // unexpected response
            $("#message").html(`<div class="alert alert-warning mb-0">Unexpected response. Please try again.</div>`);
          }
        },
        error: function(xhr){
          $btn.prop('disabled', false).html(original);
          let msg = 'Something went wrong.';
          if(xhr?.responseJSON?.message) msg = xhr.responseJSON.message;
          $("#message").html(`<div class="alert alert-danger mb-0">${msg}</div>`);
        }
      });
    });
  });
</script>
@endpush
