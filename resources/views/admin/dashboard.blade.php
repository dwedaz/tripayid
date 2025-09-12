@extends(backpack_view('blank'))

@php
  $title = 'Tripay PPOB Dashboard';
  $breadcrumbs = [
    'Admin' => backpack_url('dashboard'),
    'Tripay PPOB' => false,
  ];
@endphp

@section('header')
    <section class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ $title }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    @foreach($breadcrumbs as $label => $link)
                        @if($link)
                            <li class="breadcrumb-item"><a href="{{ $link }}">{{ $label }}</a></li>
                        @else
                            <li class="breadcrumb-item active">{{ $label }}</li>
                        @endif
                    @endforeach
                </ol>
            </div>
        </div>
    </section>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="balance-amount">Loading...</h3>
                    <p>Current Balance</p>
                </div>
                <div class="icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <a href="#" class="small-box-footer" onclick="refreshBalance()">
                    Refresh <i class="fas fa-sync-alt"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="products-count">{{ \Tripay\PPOB\Models\Product::count() }}</h3>
                    <p>Active Products</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
                <a href="{{ backpack_url('tripay/products') }}" class="small-box-footer">
                    View Products <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="transactions-today">{{ \Tripay\PPOB\Models\Transaction::today()->count() }}</h3>
                    <p>Today's Transactions</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <a href="{{ backpack_url('tripay/transactions') }}" class="small-box-footer">
                    View Transactions <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="categories-count">{{ \Tripay\PPOB\Models\Category::count() }}</h3>
                    <p>Product Categories</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
                <a href="{{ backpack_url('tripay/categories') }}" class="small-box-footer">
                    View Categories <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Data Synchronization</h5>
                            <p class="text-muted">Sync data from Tripay API to keep your database up to date.</p>
                            <button class="btn btn-primary mr-2" onclick="syncData('categories')">
                                <i class="fas fa-sync"></i> Sync Categories
                            </button>
                            <button class="btn btn-primary mr-2" onclick="syncData('operators')">
                                <i class="fas fa-sync"></i> Sync Operators
                            </button>
                            <button class="btn btn-primary mr-2" onclick="syncData('products')">
                                <i class="fas fa-sync"></i> Sync Products
                            </button>
                            <button class="btn btn-success" onclick="syncData('all')">
                                <i class="fas fa-sync-alt"></i> Sync All
                            </button>
                        </div>
                        <div class="col-md-6">
                            <h5>System Management</h5>
                            <p class="text-muted">Manage system cache and check health status.</p>
                            <button class="btn btn-warning mr-2" onclick="clearCache()">
                                <i class="fas fa-broom"></i> Clear Cache
                            </button>
                            <button class="btn btn-info mr-2" onclick="checkHealth()">
                                <i class="fas fa-heartbeat"></i> Health Check
                            </button>
                            <a href="{{ backpack_url('tripay/reports') }}" class="btn btn-secondary">
                                <i class="fas fa-chart-bar"></i> View Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Transactions</h3>
                    <div class="card-tools">
                        <a href="{{ backpack_url('tripay/transactions') }}" class="btn btn-tool">
                            <i class="fas fa-expand-arrows-alt"></i> View All
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="recent-transactions">
                            @foreach(\Tripay\PPOB\Models\Transaction::latest()->take(10)->get() as $transaction)
                            <tr>
                                <td>{{ $transaction->api_trx_id }}</td>
                                <td>{{ $transaction->customer_number }}</td>
                                <td>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                <td>
                                    @if($transaction->status === 'success')
                                        <span class="badge badge-success">Success</span>
                                    @elseif($transaction->status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($transaction->status === 'failed')
                                        <span class="badge badge-danger">Failed</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($transaction->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
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

@section('after_scripts')
<script>
// Refresh balance
function refreshBalance() {
    $('#balance-amount').text('Loading...');
    fetch('{{ backpack_url('tripay/balance') }}')
        .then(response => response.json())
        .then(data => {
            $('#balance-amount').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.balance));
        })
        .catch(error => {
            console.error('Error:', error);
            $('#balance-amount').text('Error');
        });
}

// Sync data
function syncData(type) {
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
    
    fetch(`{{ backpack_url('tripay/sync') }}/${type}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            new Noty({
                text: data.message,
                type: 'success'
            }).show();
            // Refresh page after successful sync
            setTimeout(() => window.location.reload(), 2000);
        } else {
            new Noty({
                text: data.message || 'Sync failed',
                type: 'error'
            }).show();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        new Noty({
            text: 'An error occurred during sync',
            type: 'error'
        }).show();
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Clear cache
function clearCache() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Clearing...';
    
    fetch('{{ backpack_url('tripay/cache/clear') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
    })
    .then(response => response.json())
    .then(data => {
        new Noty({
            text: data.message,
            type: data.success ? 'success' : 'error'
        }).show();
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Check health
function checkHealth() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
    
    fetch('{{ backpack_url('tripay/health') }}')
        .then(response => response.json())
        .then(data => {
            new Noty({
                text: data.message,
                type: data.healthy ? 'success' : 'error'
            }).show();
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
}

// Load balance on page load
document.addEventListener('DOMContentLoaded', function() {
    refreshBalance();
});
</script>
@endsection