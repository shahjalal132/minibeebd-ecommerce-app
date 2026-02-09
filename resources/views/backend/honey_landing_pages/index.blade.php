@extends('backend.app')
@section('content')
    <style>
        th,
        td,
        h4,
        .pg_manage,
        .form-label {
            color: black !important;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">SIS</a></li>
                        <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                        <li class="breadcrumb-item active pg_manage">Honey CMS</li>
                    </ol>
                </div>
                <h4 class="page-title">Honey Landing Page Manage</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-xl-8">
                        </div>
                        <div class="col-xl-4">
                            <div class="text-xl-end mt-xl-0 mt-2">
                                @can('honey_landing_page.create')
                                    <a href="{{ route('admin.honey_landing_pages.create') }}" class="btn btn-danger mb-2 me-2">
                                        <i class="mdi mdi-plus me-1"></i> Add Honey Landing Page
                                    </a>
                                @endcan
                            </div>
                        </div><!-- end col-->
                    </div>

                    <div class="table-responsive">
                        <table class="table table-centered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>SL</th>
                                    <th>Page Title</th>
                                    <th>Status</th>
                                    <th style="width: 125px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $key => $item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item->title }}</td>
                                        <td>
                                            @if($item->status)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(auth()->user()->can('honey_landing_page.edit'))
                                            <a href="{{ route('admin.honey_landing_pages.edit', [$item->id]) }}"
                                                class="action-icon">
                                                <i class="mdi mdi-square-edit-outline"></i>
                                            </a>
                                            @endif
                                            @if(auth()->user()->can('honey_landing_page.delete'))
                                            <a href="{{ route('admin.honey_landing_pages.destroy', [$item->id]) }}"
                                                class="delete action-icon">
                                                <i class="mdi mdi-delete"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col -->
    </div> <!-- end row -->
@endsection
