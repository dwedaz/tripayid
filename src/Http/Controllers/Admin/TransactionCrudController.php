<?php

namespace Tripay\PPOB\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Tripay\PPOB\Models\Transaction;

class TransactionCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Transaction::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/tripay/transactions');
        CRUD::setEntityNameStrings('transaction', 'transactions');
        
        $this->crud->setHeading('Tripay Transactions', 'Transaction Management');
        $this->crud->setSubheading('View and manage PPOB transactions');
        
        // Disable create/update/delete for transactions (read-only)
        $this->crud->denyAccess('create');
        $this->crud->denyAccess('update');
        $this->crud->denyAccess('delete');
    }

    protected function setupListOperation()
    {
        CRUD::column('api_trx_id')->label('Transaction ID');
        
        CRUD::column('product')->label('Product')
            ->type('relationship')
            ->attribute('product_name')
            ->model('Tripay\PPOB\Models\Product');

        CRUD::column('customer_number')->label('Customer');
        
        CRUD::column('total_amount')
            ->label('Amount')
            ->type('closure')
            ->function(function($entry) {
                return 'Rp ' . number_format($entry->total_amount, 0, ',', '.');
            });

        CRUD::column('status')
            ->label('Status')
            ->type('closure')
            ->function(function($entry) {
                $badges = [
                    'pending' => '<span class="badge badge-warning">Pending</span>',
                    'processing' => '<span class="badge badge-info">Processing</span>',
                    'success' => '<span class="badge badge-success">Success</span>',
                    'failed' => '<span class="badge badge-danger">Failed</span>',
                    'cancelled' => '<span class="badge badge-secondary">Cancelled</span>',
                ];
                return $badges[$entry->status] ?? '<span class="badge badge-light">Unknown</span>';
            });

        CRUD::column('type')->label('Type');
        CRUD::column('created_at')->label('Created')->type('datetime')->format('Y-m-d H:i:s');

        // Filters
        $this->crud->addFilter([
            'name' => 'status',
            'type' => 'dropdown',
            'label' => 'Status'
        ], [
            'pending' => 'Pending',
            'processing' => 'Processing', 
            'success' => 'Success',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
        ], function ($value) {
            $this->crud->addClause('where', 'status', $value);
        });

        $this->crud->addFilter([
            'name' => 'type',
            'type' => 'dropdown',
            'label' => 'Type'
        ], [
            'prepaid' => 'Prepaid',
            'postpaid' => 'Postpaid',
        ], function ($value) {
            $this->crud->addClause('where', 'type', $value);
        });

        $this->crud->addFilter([
            'name' => 'date',
            'type' => 'date_range',
            'label' => 'Date Range'
        ], false, function ($value) {
            $dates = json_decode($value);
            $this->crud->addClause('where', 'created_at', '>=', $dates->from);
            $this->crud->addClause('where', 'created_at', '<=', $dates->to . ' 23:59:59');
        });

        // Default ordering
        $this->crud->orderBy('created_at', 'desc');
    }

    protected function setupShowOperation()
    {
        CRUD::column('tripay_trx_id')->label('Tripay Transaction ID');
        CRUD::column('api_trx_id')->label('API Transaction ID');
        CRUD::column('product_id')->label('Product ID');
        CRUD::column('customer_number')->label('Customer Number');
        CRUD::column('customer_name')->label('Customer Name');
        CRUD::column('amount')->label('Amount')->type('number');
        CRUD::column('admin_fee')->label('Admin Fee')->type('number');
        CRUD::column('total_amount')->label('Total Amount')->type('number');
        CRUD::column('profit')->label('Profit')->type('number');
        CRUD::column('status')->label('Status');
        CRUD::column('type')->label('Type');
        CRUD::column('message')->label('Message');
        CRUD::column('sn')->label('Serial Number');
        CRUD::column('processed_at')->label('Processed At')->type('datetime');
        CRUD::column('completed_at')->label('Completed At')->type('datetime');
        CRUD::column('failed_at')->label('Failed At')->type('datetime');
        CRUD::column('created_at')->label('Created At')->type('datetime');
        CRUD::column('updated_at')->label('Updated At')->type('datetime');
    }
}