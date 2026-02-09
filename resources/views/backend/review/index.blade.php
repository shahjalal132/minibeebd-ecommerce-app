@extends('backend.app')

@push('css')
<style>
    :root{
        --bg:#f9fafb;
        --card:#ffffff;
        --muted:#6b7280;
        --text:#0f172a;
        --primary:#0ea5e9;
        --danger:#ef4444;
        --success:#22c55e;
        --ring:0 0 0 .18rem rgba(14,165,233,.25);
    }

    body{
        background:var(--bg);
    }

    .page-title-box{
        border:0;
        margin-bottom: 1rem;
    }
    .page-title-box h4{
        font-weight:700;
        color:var(--text);
    }

    .review-page-card{
        border:0;
        border-radius:18px;
        box-shadow:0 18px 45px rgba(15,23,42,.08);
        overflow:hidden;
        background:var(--card);
    }

    .review-toolbar{
        display:flex;
        flex-wrap:wrap;
        gap:.75rem;
        align-items:center;
        justify-content:space-between;
        margin-bottom:1rem;
    }

    .review-toolbar-left{
        flex:1 1 220px;
        max-width:380px;
    }

    .search-input-wrap{
        position:relative;
    }
    .search-input-wrap .form-control{
        border-radius:999px;
        padding-left:2.2rem;
        padding-right:.9rem;
        border-color:#e5e7eb;
        box-shadow:none;
    }
    .search-input-wrap .form-control:focus{
        box-shadow:var(--ring);
        border-color:var(--primary);
    }
    .search-input-wrap .search-icon{
        position:absolute;
        left:.75rem;
        top:50%;
        transform:translateY(-50%);
        font-size:.9rem;
        color:var(--muted);
    }

    .review-toolbar-right{
        display:flex;
        flex-wrap:wrap;
        gap:.5rem;
        justify-content:flex-end;
    }

    .review-toolbar-right .btn{
        border-radius:999px;
        font-weight:600;
        font-size:.8rem;
        padding:.4rem .9rem;
        display:inline-flex;
        align-items:center;
        gap:.25rem;
    }

    .review-toolbar-right .btn i{
        font-size:1rem;
    }

    /* Table styling */
    .review-table-wrap{
        border-radius:16px;
        border:1px solid #e5e7eb;
        overflow:hidden;
        background:#ffffff;
    }

    .review-table thead{
        background:#f9fafb;
    }

    .review-table thead th{
        border-bottom:1px solid #e5e7eb !important;
        font-size:.78rem;
        text-transform:uppercase;
        letter-spacing:.04em;
        color:#6b7280;
        font-weight:600;
        padding:.7rem .9rem;
        white-space:nowrap;
    }

    .review-table tbody td{
        vertical-align:middle;
        font-size:.85rem;
        color:#111827;
        padding:.65rem .9rem;
    }

    .review-image{
        width:52px;
        height:52px;
        border-radius:12px;
        object-fit:cover;
        border:1px solid #e5e7eb;
    }

    .status-badge{
        border-radius:999px;
        font-size:.7rem;
        padding:.25rem .65rem;
    }

    .status-badge.approved{
        background:rgba(34,197,94,.12);
        color:#15803d;
    }

    .status-badge.pending{
        background:rgba(249,115,22,.12);
        color:#c2410c;
    }

    .action-icon{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:30px;
        height:30px;
        border-radius:999px;
        border:0;
        background:rgba(248,113,113,.09);
        color:#b91c1c;
        transition:all .16s ease;
    }
    .action-icon:hover{
        background:#b91c1c;
        color:#fff;
    }

    .alert{
        border-radius:999px;
        padding:.45rem .9rem;
        font-size:.82rem;
    }

    /* Checkbox column */
    .review-table input[type="checkbox"]{
        width:16px;
        height:16px;
        cursor:pointer;
    }

    /* Responsive: Table -> Card layout on mobile */
    @media (max-width: 767.98px){
        .page-title-box{
            margin-bottom:.7rem;
        }
        .page-title-box .page-title-right{
            margin-bottom:.25rem;
        }

        .review-toolbar{
            align-items:stretch;
        }

        .review-toolbar-right{
            width:100%;
            justify-content:flex-start;
        }

        .review-table-wrap{
            border-radius:0;
            border:0;
        }

        .review-table thead{
            display:none;
        }

        .review-table tbody tr{
            display:block;
            margin-bottom:.85rem;
            border-radius:14px;
            border:1px solid #e5e7eb;
            box-shadow:0 4px 16px rgba(15,23,42,.06);
            padding:.65rem .8rem;
            background:#fff;
        }

        .review-table tbody td{
            display:grid;
            grid-template-columns:110px 1fr;
            gap:.25rem .5rem;
            border:none !important;
            padding:.2rem 0 !important;
            font-size:.82rem;
        }

        .review-table tbody td:first-child{
            grid-template-columns:70px 1fr;
        }

        .review-table tbody td::before{
            content:attr(data-label);
            font-weight:600;
            color:var(--muted);
            text-transform:none;
            font-size:.78rem;
        }

        .review-image{
            width:60px;
            height:60px;
        }

        td[data-label="Actions"]{
            margin-top:.3rem;
        }
    }

    @media (max-width: 575.98px){
        .review-toolbar-left{
            max-width:100%;
        }
    }

</style>
@endpush

@section('content')
<div class="row review-page">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item active">Review List</li>
                </ol>
            </div>
            <h4 class="page-title">Review List</h4>
        </div>
    </div>
</div>   

<div class="row">
    <div class="col-12">
        <div class="card review-page-card">
            <div class="card-body">
                @if(Session::has('success'))
                    <div class="alert alert-success mb-3">
                        <strong>{{ Session::get('success') }}</strong>
                    </div>
                @endif

                {{-- Top toolbar --}}
                <div class="review-toolbar">
                    <div class="review-toolbar-left">
                        <form>
                            <div class="search-input-wrap">
                                <span class="search-icon">
                                    <i class="mdi mdi-magnify"></i>
                                </span>
                                <input type="text"
                                       name="q"
                                       class="form-control"
                                       placeholder="Search reviews..."
                                       value="{{ request('q') ?? '' }}">
                            </div>
                        </form>
                    </div>

                    <div class="review-toolbar-right">
                        <a href="{{ route('admin.reviews.action', ['status' => 1]) }}" 
                           class="btn btn-success action_btn">
                            <i class="mdi mdi-check-decagram"></i>
                            Approve
                        </a>
                        <a href="{{ route('admin.reviews.action', ['status' => 0]) }}" 
                           class="btn btn-warning action_btn">
                            <i class="mdi mdi-timer-sand"></i>
                            Pending
                        </a>
                        <a href="{{ route('admin.reviews.action', ['delete' => 1]) }}" 
                           class="btn btn-danger action_btn">
                            <i class="mdi mdi-trash-can-outline"></i>
                            Delete
                        </a>
                    </div>
                </div>

                {{-- Table --}}
                <div class="table-responsive review-table-wrap">
                    <table class="table table-centered table-nowrap mb-0 review-table">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <input type="checkbox" id="parent_item">
                                </th>
                                <th>Product</th>
                                <th>Name</th>
                                <th>Message</th>
                                <th>Image</th>
                                <th>Status</th>
                                <th style="width: 125px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $review)
                                <tr>
                                    <td data-label="Select">
                                        <input type="checkbox"
                                               value="{{ $review->id }}"
                                               class="user_status">
                                    </td>
                                    <td data-label="Product">
                                        {{ $review->product ? $review->product->name : '' }}
                                    </td>
                                    <td data-label="Name">
                                        {{ $review->name }}
                                    </td>
                                    <td data-label="Message">
                                        {!! \Illuminate\Support\Str::limit(strip_tags($review->message), 120) !!}
                                    </td>
                                    <td data-label="Image">
                                        @if($review->image)
                                            <img src="{{ asset($review->image) }}" 
                                                 alt="{{ $review->name }}"
                                                 class="review-image">
                                        @else
                                            <span class="text-muted small">No image</span>
                                        @endif
                                    </td>
                                    <td data-label="Status">
                                        @if($review->status == 1)
                                            <span class="status-badge approved">Approved</span>
                                        @else
                                            <span class="status-badge pending">Pending</span>
                                        @endif
                                    </td>
                                    <td data-label="Actions">
                                        <a href="{{ route('admin.reviews.destroy',[$review->id])}}"
                                           class="delete action-icon"
                                           title="Delete">
                                            <i class="mdi mdi-delete"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $data->links() }}
                    </div>
                </div>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection 

@push('js')
<script>
$(document).ready(function(){

    // Select/Deselect all checkboxes
    $('#parent_item').on('change', function(){
        $('.user_status').prop('checked', this.checked);
    });

    $(document).on('change', '.user_status', function(){
        $('#parent_item').prop(
            'checked', 
            $('.user_status:checked').length === $('.user_status').length
        );
    });

    // Bulk Actions (Approve / Pending / Delete)
    $(document).on('click', '.action_btn', function(e){
        e.preventDefault();

        var url = $(this).attr('href');
        var ids = $('input.user_status:checked').map(function(){
            return $(this).val();
        }).get();

        if (ids.length === 0) {
            toastr.error('Please select at least one review!');
            return;
        }

        // Confirm only for delete
        if (url.includes('delete=1')) {
            if (!confirm('Are you sure you want to delete the selected reviews? This action cannot be undone.')) {
                return;
            }
        }

        $.ajax({
            type: 'GET',
            url: url,
            data: { ids: ids },
            success: function(res){
                if(res.status){
                    toastr.success(res.msg || 'Action completed successfully!');
                    setTimeout(function(){
                        window.location.reload();
                    }, 800);
                } else {
                    toastr.error(res.msg || 'Something went wrong!');
                }
            },
            error: function(){
                toastr.error('Server error! Please try again.');
            }
        });
    });

});
</script>
@endpush
