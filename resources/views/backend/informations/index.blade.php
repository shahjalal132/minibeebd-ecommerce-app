@extends('backend.app')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<style>
  /* Mobile-friendly tweaks */
  .page-title-box { padding-bottom: 0; }
  .card-section-title {
    font-size: 1.1rem; font-weight: 700; margin-bottom: .75rem;
  }
  .help-text { font-size: .85rem; opacity:.85 }
  .sticky-actions {
    position: sticky; bottom: 0; background:#fff; z-index: 5;
    padding: .75rem 1rem; border-top: 1px solid #eee; display:flex; gap:.5rem; justify-content:flex-end;
  }
  /* Better file input preview */
  .preview-wrap img { border:1px solid #e9ecef; border-radius:8px; max-width:100px; height:auto }
  .form-grid .form-group { margin-bottom: 1rem; }
  .color-sample { font-weight:600; font-size:14px }
  @media (max-width: 575.98px) {
    .breadcrumb { display:none; }
    .card { border-radius: 12px; }
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
          <li class="breadcrumb-item active">Settings</li>
        </ol>
      </div>
      <h4 class="page-title">Settings</h4>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        

        @if(Session::has('msg'))
          <div class="alert alert-success mt-3">
            <strong>{{ Session::get('msg') }}</strong>
          </div>
        @endif

        <form action="{{ route('admin.settings.update', [$information->id]) }}" method="POST" enctype="multipart/form-data" class="mt-3">
          @csrf
          @method('PUT')

          {{-- GENERAL --}}
          <div class="card mb-3">
            <div class="card-body">
              <div class="card-section-title">General</div>
              <div class="row form-grid">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="site_name" class="fw-semibold">Site Name</label>
                    <input type="text" id="site_name" class="form-control" name="site_name" placeholder="Site name..." value="{{ $information->site_name }}">
                    @error('site_name') <small class="text-danger">{{ $message }}</small> @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="row g-2 align-items-end">
                    <div class="col-12">
                      <label for="site_logo" class="fw-semibold">Site Logo</label>
                      <input type="file" id="site_logo" class="form-control" name="site_logo" accept="image/*">
                      @error('site_logo') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-12 preview-wrap">
                      <img src="{{ asset('uploads/img/'.$information->site_logo) }}" alt="Logo" id="preview_logo">
                    </div>
                  </div>
                </div>

                <div class="col-md-6 mt-3">
                  <div class="row g-2 align-items-end">
                    <div class="col-12">
                      <label for="fav_icon" class="fw-semibold">Favicon</label>
                      <input type="file" id="fav_icon" class="form-control" name="fav_icon" accept="image/*">
                      @error('fav_icon') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-12 preview-wrap">
                      <img src="{{ asset('uploads/img/'.$information->fav_icon) }}" alt="Favicon" id="preview_favicon">
                    </div>
                  </div>
                </div>

                <div class="col-md-6 mt-3">
                  <label class="fw-semibold">Topbar Notice</label>
                  <textarea class="form-control" name="topbar_notice" rows="2" placeholder="Short notice...">{{ $information->topbar_notice }}</textarea>
                </div>
              </div>
            </div>
          </div>

          {{-- CONTACT & ADDRESS --}}
          <div class="card mb-3">
            <div class="card-body">
              <div class="card-section-title">Contact & Address</div>
              <div class="row form-grid">
                <div class="col-md-6">
                  <label class="fw-semibold" for="owner_phone">Phone</label>
                  <input type="tel" inputmode="tel" id="owner_phone" class="form-control" name="owner_phone" placeholder="+8801XXXXXXXXX" value="{{ $information->owner_phone }}">
                  @error('owner_phone') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6">
                  <label class="fw-semibold" for="owner_email">Email</label>
                  <input type="email" id="owner_email" class="form-control" name="owner_email" placeholder="you@domain.com" value="{{ $information->owner_email }}">
                  @error('owner_email') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6">
                  <label class="fw-semibold" for="address">Address</label>
                  <textarea name="address" id="address" rows="3" class="form-control" placeholder="Full address">{{ $information->address }}</textarea>
                  @error('address') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6">
                  <label class="fw-semibold" for="tracking_code">Header Code (Analytics/Pixel)</label>
                  <textarea name="tracking_code" id="tracking_code" rows="3" class="form-control" placeholder="Script tags allowed">{{ $information->tracking_code }}</textarea>
                </div>
                <div class="col-12">
                  <label class="fw-semibold" for="copyright">Copyright Text</label>
                  <textarea name="copyright" id="copyright" rows="2" class="form-control" placeholder="© Your Company">{{ $information->copyright }}</textarea>
                  @error('copyright') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>
            </div>
          </div>

          {{-- SOCIAL LINKS --}}
          <div class="card mb-3">
            <div class="card-body">
              <div class="card-section-title">Social Links</div>
              <div class="row form-grid">
                <div class="col-md-6">
                  <label class="fw-semibold" for="facebook">Facebook</label>
                  <input type="url" class="form-control" id="facebook" name="facebook" placeholder="https://facebook.com/yourpage" value="{{ $information->facebook }}">
                  @error('facebook') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6">
                  <label class="fw-semibold" for="instagram">Instagram</label>
                  <input type="url" class="form-control" id="instagram" name="instagram" placeholder="https://instagram.com/yourhandle" value="{{ $information->instagram }}">
                  @error('instagram') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6">
                  <label class="fw-semibold" for="youtube">YouTube</label>
                  <input type="url" class="form-control" id="youtube" name="youtube" placeholder="https://youtube.com/@yourchannel" value="{{ $information->youtube }}">
                  @error('youtube') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6">
                  <label class="fw-semibold" for="twitter">Twitter/X</label>
                  <input type="url" class="form-control" id="twitter" name="twitter" placeholder="https://x.com/yourhandle" value="{{ $information->twitter }}">
                  @error('twitter') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6">
                  <label class="fw-semibold" for="tiktok">TikTok</label>
                  <input type="url" class="form-control" id="tiktok" name="tiktok" placeholder="https://tiktok.com/@yourhandle" value="{{ $information->tiktok }}">
                  @error('tiktok') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>
            </div>
          </div>

          {{-- SUPPORT & FRAUD --}}
          <div class="card mb-3">
            <div class="card-body">
              <div class="card-section-title">Support & Security</div>
              <div class="row form-grid">
                <div class="col-md-4">
                  <label class="fw-semibold" for="whats_num">WhatsApp Number</label>
                  <input type="tel" id="whats_num" class="form-control" name="whats_num" placeholder="+8801XXXXXXXXX" value="{{ $information->whats_num }}">
                  @error('whats_num') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-4">
                  <label class="fw-semibold" for="whats_active">WhatsApp Status</label>
                  <select class="form-select" name="whats_active" id="whats_active">
                    <option value="1" {{ $information->whats_active == '1' ? 'selected':'' }}>Active</option>
                    <option value="0" {{ $information->whats_active == '0' ? 'selected':'' }}>DeActive</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="fw-semibold" for="supp_num1">Support Number</label>
                  <input type="tel" id="supp_num1" class="form-control" name="supp_num1" placeholder="+8801XXXXXXXXX" value="{{ $information->supp_num1 }}">
                </div>
                <div class="col-md-6 mt-2">
                  <label class="fw-semibold" for="fraudApi">Fraud API</label>
                  <input type="text" id="fraudApi" class="form-control" name="fraudApi" placeholder="Fraud API Endpoint/Key" value="{{ $information->fraudApi }}">
                </div>
              </div>
            </div>
          </div>

          {{-- COURIER INTEGRATIONS --}}
          <div class="card mb-3">
            <div class="card-body">
              <div class="card-section-title">Courier Integrations</div>

              <h6 class="text-danger mb-2">Redx</h6>
              <div class="row form-grid">
                <div class="col-md-6">
                  <label class="fw-semibold">API Base URL</label>
                  <input type="text" class="form-control" name="redx_api_base_url" id="redx_api_base_url" value="{{ $information->redx_api_base_url }}" placeholder="https://sandbox.redx.com.bd/v1.0.0-beta">
                </div>
                <div class="col-md-6">
                  <label class="fw-semibold">Access Token</label>
                  <textarea class="form-control" name="redx_api_access_token" id="redx_api_access_token" rows="2">{{ $information->redx_api_access_token }}</textarea>
                </div>
              </div>

              <hr>

              <h6 class="text-danger mb-2">Pathao</h6>
              <div class="row form-grid">
                <div class="col-md-4">
                  <label class="fw-semibold">Status</label>
                  <select name="pathao_status" class="form-select">
                    <option value="1" {{ $information->pathao_status == 1 ?'selected':'' }}>Active</option>
                    <option value="0" {{ $information->pathao_status == 0 ?'selected':'' }}>De Active</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="fw-semibold" for="pathao_api_base_url">API Base URL</label>
                  <input type="text" name="pathao_api_base_url" id="pathao_api_base_url" value="{{ $information->pathao_api_base_url }}" class="form-control" placeholder="https://api-hermes.pathao.com/">
                </div>
                <div class="col-md-4">
                  <label class="fw-semibold" for="pathao_store_id">Store ID</label>
                  <input type="text" name="pathao_store_id" id="pathao_store_id" value="{{ $information->pathao_store_id }}" class="form-control" placeholder="Store ID">
                </div>
                <div class="col-12">
                  <label class="fw-semibold" for="pathao_api_access_token">Access Token</label>
                  <textarea name="pathao_api_access_token" id="pathao_api_access_token" rows="2" class="form-control">{{ $information->pathao_api_access_token }}</textarea>
                  <a href="{{ route('admin.viewAccessToken') }}" class="btn btn-success btn-sm mt-2">Generate Token</a>
                </div>
              </div>

              <hr>

              <h6 class="text-danger mb-2">Steadfast</h6>
              <div class="row form-grid">
                <div class="col-md-4">
                  <label class="fw-semibold">API Base URL</label>
                  <input type="text" class="form-control" name="steadfast_api_base_url" id="steadfast_api_base_url" value="{{ $information->steadfast_api_base_url }}" placeholder="https://portal.steadfast.com.bd/api/">
                </div>
                <div class="col-md-4">
                  <label class="fw-semibold">API Key</label>
                  <input type="text" class="form-control" name="steadfast_api_key" id="steadfast_api_key" value="{{ $information->steadfast_api_key }}">
                </div>
                <div class="col-md-4">
                  <label class="fw-semibold">Secret Key</label>
                  <input type="text" class="form-control" name="steadfast_secret_key" id="steadfast_secret_key" value="{{ $information->steadfast_secret_key }}">
                </div>
              </div>
            </div>
          </div>

          {{-- META PIXEL / CAPI --}}
          <div class="card mb-3">
            <div class="card-body">
              <div class="card-section-title">Meta Pixel (Server-Side)</div>
              <div class="row form-grid">
                <div class="col-md-6">
                  <label class="fw-semibold" for="fb_pixel_id">Meta Pixel ID</label>
                  <input type="text" name="fb_pixel_id" id="fb_pixel_id" value="{{ $information->fb_pixel_id }}" class="form-control" placeholder="e.g., 1234567890">
                </div>
                <div class="col-md-6">
                  <label class="fw-semibold" for="fb_pixel_test_code">Event Test Code</label>
                  <input type="text" name="fb_pixel_test_code" id="fb_pixel_test_code" value="{{ $information->fb_pixel_test_code }}" class="form-control" placeholder="Test code">
                </div>
                <div class="col-12">
                  <label class="fw-semibold" for="fb_access_token">Access Token</label>
                  <textarea name="fb_access_token" id="fb_access_token" rows="2" class="form-control">{{ $information->fb_access_token }}</textarea>
                </div>
              </div>
            </div>
          </div>

          {{-- CHECKOUT & STYLE --}}
          <div class="card mb-3">
            <div class="card-body">
              <div class="card-section-title">Checkout & Style</div>

              <div class="row form-grid">
                <div class="col-md-6">
                  <label class="fw-semibold d-block">Is IP Restriction</label>
                  <div class="d-flex gap-3">
                    <label class="d-flex gap-2 align-items-center">
                      <input type="radio" value="1" class="form-check" name="is_ip_check" {{ $information->is_ip_check == 1 ? 'checked' : '' }}> Yes
                    </label>
                    <label class="d-flex gap-2 align-items-center">
                      <input type="radio" value="0" class="form-check" name="is_ip_check" {{ $information->is_ip_check == 0 ? 'checked' : '' }}> No
                    </label>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="fw-semibold d-block">Is Mobile Restriction</label>
                  <div class="d-flex gap-3">
                    <label class="d-flex gap-2 align-items-center">
                      <input type="radio" value="1" class="form-check" name="is_mobile_check" {{ $information->is_mobile_check == 1 ? 'checked' : '' }}> Yes
                    </label>
                    <label class="d-flex gap-2 align-items-center">
                      <input type="radio" value="0" class="form-check" name="is_mobile_check" {{ $information->is_mobile_check == 0 ? 'checked' : '' }}> No
                    </label>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="fw-semibold">Time Limit Per Order (Minutes)</label>
                  <input type="number" min="0" class="form-control" name="time_limit" value="{{ $information->time_limit }}">
                </div>

                <div class="col-md-6">
                  <label class="fw-semibold">Primary Text Color</label>
                  <input type="color" class="form-control color-input" name="primary_color" value="{{ $information->primary_color ?? '#ffffff' }}">
                  <input type="text" readonly class="form-control mt-1 color-code" value="{{ $information->primary_color ?? '#ffffff' }}">
                </div>

                <div class="col-12">
                  <div class="row">
                    <div class="col-md-4">
                      <label class="fw-semibold">Primary Background</label>
                      <input type="color" class="form-control color-input" name="primary_background" value="{{ $information->primary_background ?? '#5ca3da' }}">
                      <input type="text" readonly class="form-control mt-1 color-code" value="{{ $information->primary_background ?? '#5ca3da' }}">
                    </div>
                    <div class="col-md-4">
                      <label class="fw-semibold">Primary Background 2</label>
                      <input type="color" class="form-control color-input" name="primary_background2" value="{{ $information->primary_background2 ?? '#207cca' }}">
                      <input type="text" readonly class="form-control mt-1 color-code" value="{{ $information->primary_background2 ?? '#207cca' }}">
                      <span class="help-text">For Gradient</span>
                    </div>
                    <div class="col-md-4">
                      <label class="fw-semibold">Primary Background 3</label>
                      <input type="color" class="form-control color-input" name="primary_background3" value="{{ $information->primary_background3 ?? '#1d5fab' }}">
                      <input type="text" readonly class="form-control mt-1 color-code" value="{{ $information->primary_background3 ?? '#1d5fab' }}">
                      <span class="help-text">For Gradient</span>
                    </div>
                  </div>
                </div>

                <div class="col-12 mt-2">
                  <input type="hidden" name="gradient_code" id="gradient_code">
                  <label class="fw-bold text-dark">Gradient Preview</label>
                  <div id="gradientPreview" class="rounded" style="width:100%;height:110px;border:1px solid #e9ecef;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:16px;">
                    Gradient Text Preview
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="sticky-actions">
            <input type="submit" value="Update Settings" class="btn btn-success">
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script>
  // --- Color → RGBA helper ---
  function hexToRgba(hex) {
    if (!hex) return 'rgba(0,0,0,1)';
    hex = hex.replace('#','');
    if (hex.length === 3) {
      hex = hex.split('').map(c => c + c).join('');
    }
    const r = parseInt(hex.substring(0,2),16) || 0;
    const g = parseInt(hex.substring(2,4),16) || 0;
    const b = parseInt(hex.substring(4,6),16) || 0;
    return `rgba(${r}, ${g}, ${b}, 1)`;
  }

  function updateGradient() {
    const bg1 = document.querySelector('[name="primary_background"]')?.value || '#5ca3da';
    const bg2 = document.querySelector('[name="primary_background2"]')?.value || '#207cca';
    const bg3 = document.querySelector('[name="primary_background3"]')?.value || '#1d5fab';
    const tcol= document.querySelector('[name="primary_color"]')?.value || '#ffffff';

    const codes = document.querySelectorAll('.color-code');
    if (codes.length >= 4) {
      // Order: text color is the 4th code in this layout
      codes[0].value = tcol;        // text color (above section)
      codes[1].value = bg1;
      codes[2].value = bg2;
      codes[3].value = bg3;
    } else {
      // fallback: just ignore
    }

    const gradient = `linear-gradient(90deg, ${hexToRgba(bg1)} 0%, ${hexToRgba(bg2)} 35%, ${hexToRgba(bg3)} 100%)`;
    const preview  = document.getElementById('gradientPreview');
    if (preview) {
      preview.style.background = gradient;
      preview.style.color = tcol;
    }
    const hidden = document.getElementById('gradient_code');
    if (hidden) hidden.value = gradient;
  }

  // Image previews
  function bindImagePreview(inputId, imgId) {
    const input = document.getElementById(inputId);
    const img   = document.getElementById(imgId);
    if (!input || !img) return;
    input.addEventListener('change', (e) => {
      const file = e.target.files?.[0];
      if (!file) return;
      if (!file.type.startsWith('image/')) {
        alert('Please select a valid image file.');
        input.value = '';
        return;
      }
      const temp = URL.createObjectURL(file);
      img.src = temp;
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
    // Attach color listeners
    document.querySelectorAll('.color-input').forEach(el => {
      el.addEventListener('input', updateGradient);
      el.addEventListener('change', updateGradient);
    });
    updateGradient();

    // Bind image previews (fixed duplicate id issue)
    bindImagePreview('site_logo', 'preview_logo');
    bindImagePreview('fav_icon',  'preview_favicon');
  });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
@endpush
