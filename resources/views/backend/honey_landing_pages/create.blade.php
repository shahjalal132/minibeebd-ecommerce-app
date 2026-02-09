@extends('backend.app')

@push('css')
<style>
  .vertical-tabs {
    display: flex;
    gap: 20px;
  }
  .tab-nav {
    width: 250px;
    flex-shrink: 0;
  }
  .tab-content-area {
    flex: 1;
  }
  .nav-pills .nav-link {
    border-radius: 8px;
    margin-bottom: 8px;
    padding: 12px 16px;
    color: #495057;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
  }
  .nav-pills .nav-link.active {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
  }
  .tab-pane {
    display: none;
  }
  .tab-pane.active {
    display: block;
  }
  .form-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
  }
  .dynamic-item {
    border: 1px solid #dee2e6;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 8px;
    background: #f8f9fa;
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
          <li class="breadcrumb-item active">Honey Landing Page Create</li>
        </ol>
      </div>
      <h4 class="page-title">Create Honey Landing Page</h4>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <form method="POST" action="{{ route('admin.honey_landing_pages.store') }}" id="ajax_form">
          @csrf
          <div class="mb-3">
            <label class="form-label">Page Title</label>
            <input type="text" name="title" class="form-control" placeholder="Enter page title" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Create Page</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
