@extends('backend.app')
@section('content')

<style>
    :root{
        --bg:#f3f4f6;
        --card:#ffffff;
        --border:#e5e7eb;
        --primary:#0ea5e9;
        --primary-soft:rgba(14,165,233,.15);
        --text:#111827;
        --muted:#6b7280;
        --shadow:0 10px 28px rgba(15,23,42,.08);
    }

    body{
        background: var(--bg);
    }

    .page-title-box h4{
        font-weight:700;
    }

    .card-modern{
        border:0;
        border-radius:20px;
        background:var(--card);
        box-shadow:var(--shadow);
        overflow:hidden;
    }
    .card-modern .card-header{
        background:linear-gradient(135deg,#eff6ff,#dbeafe);
        padding:12px 20px;
        border-bottom:1px solid rgba(15,23,42,.05);
    }
    .card-modern .card-header h4{
        margin:0;
        font-weight:700;
        color:#1e293b;
        font-size:1rem;
    }

    .form-label{
        font-size:.85rem;
        color:var(--muted);
        font-weight:600;
    }
    .form-control{
        border-radius:10px;
        border-color:var(--border);
    }
    .form-control:focus{
        border-color:var(--primary);
        box-shadow:0 0 0 .15rem var(--primary-soft);
    }

    .btn-primary{
        border:none;
        border-radius:10px;
        padding:.45rem 1.3rem;
        font-weight:600;
        background:linear-gradient(to right,#0ea5e9,#2563eb);
    }

    /* Modern table */
    .table-modern thead{
        background:#f8fafc;
    }
    .table-modern thead th{
        font-size:.8rem;
        color:var(--muted);
        text-transform:uppercase;
        letter-spacing:.05em;
        border-bottom:1px solid var(--border);
    }
    .table-modern tbody td{
        border-top:1px solid #f1f5f9;
        font-size:.9rem;
    }
    .status-badge{
        display:inline-block;
        padding:.25rem .7rem;
        border-radius:999px;
        font-size:.75rem;
        font-weight:600;
    }
    .status-active{
        background:rgba(16,185,129,.15);
        color:#047857;
    }
    .status-inactive{
        background:rgba(239,68,68,.15);
        color:#b91c1c;
    }

    /* Mobile view – table → card */
    @media(max-width:768px){
        .table-modern thead{ display:none; }
        .table-modern tbody tr{
            display:block;
            margin-bottom:12px;
            background:#fff;
            border-radius:14px;
            padding:10px;
            box-shadow:0 4px 10px rgba(0,0,0,.06);
        }
        .table-modern tbody td{
            display:grid;
            grid-template-columns:130px 1fr;
            border:none !important;
            padding:6px 4px !important;
        }
        .table-modern tbody td::before{
            content:attr(data-label);
            font-size:.75rem;
            color:var(--muted);
            text-transform:uppercase;
            font-weight:600;
        }
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Delivery Charge Manage</h4>
        </div>
    </div>
</div>

<div class="row">
    {{-- Left Form --}}
    <div class="col-lg-5 col-md-12 mb-3">
        <div class="card card-modern">
            <div class="card-header">
                <h4>Add Delivery Charge</h4>
            </div>
            <div class="card-body">

                @can('size.create')
                <form method="POST" action="{{ route('admin.delivery_charge.store') }}" id="ajax_form">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Delivery Charge Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Title">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Charge Amount</label>
                        <input type="text" name="amount" class="form-control" value="0">
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="status" class="form-check-input" value="1">
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Save</button>

                </form>
                @endcan
            </div>
        </div>
    </div>

    {{-- Right Table --}}
    <div class="col-lg-7 col-md-12 mb-3">
        <div class="card card-modern">
            <div class="card-header">
                <h4>Delivery Charge List</h4>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-modern table-centered mb-0">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Title</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th style="width:100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $key=>$item)
                            <tr>
                                <td data-label="SL">{{ $key+1 }}</td>

                                <td data-label="Title">{{ $item->title }}</td>

                                <td data-label="Amount">{{ $item->amount }}</td>

                                <td data-label="Status">
                                    @if($item->status == '1')
                                        <span class="status-badge status-active">Active</span>
                                    @else
                                        <span class="status-badge status-inactive">Inactive</span>
                                    @endif
                                </td>

                                <td data-label="Action">
                                    @can('size.edit')
                                    <a href="{{ route('admin.delivery_charge.edit',$item->id) }}" class="btn_modal action-icon me-1">
                                        <i class="mdi mdi-square-edit-outline"></i>
                                    </a>
                                    @endcan

                                    @can('size.delete')
                                    <a href="{{ route('admin.delivery_charge.destroy',$item->id) }}" class="delete action-icon">
                                        <i class="mdi mdi-delete"></i>
                                    </a>
                                    @endcan
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</div>

@endsection
