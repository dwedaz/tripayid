<?php

namespace Tripay\PPOB\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Tripay\PPOB\Models\Operator;

class OperatorCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Operator::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/tripay/operators');
        CRUD::setEntityNameStrings('operator', 'operators');
        
        $this->crud->setHeading('Tripay Operators', 'Manage PPOB Operators');
        $this->crud->setSubheading('Manage telecom operators and service providers');
    }

    protected function setupListOperation()
    {
        CRUD::column('operator_name')
            ->label('Operator Name')
            ->type('text');

        CRUD::column('operator_code')
            ->label('Code')
            ->type('text');

        CRUD::column('type')
            ->label('Type')
            ->type('select_from_array')
            ->options(['prepaid' => 'Prepaid', 'postpaid' => 'Postpaid']);

        CRUD::column('status')
            ->label('Status')
            ->type('boolean')
            ->options([0 => 'Inactive', 1 => 'Active'])
            ->wrapper([
                'element' => 'span',
                'class' => function ($crud, $column, $entry, $related_key) {
                    return $entry->status ? 'badge badge-success' : 'badge badge-danger';
                },
            ]);

        CRUD::column('logo_image')
            ->label('Logo')
            ->type('closure')
            ->function(function($entry) {
                if ($entry->logo_url) {
                    return '<img src="' . $entry->logo_url . '" alt="' . $entry->operator_name . '" style="max-width: 50px; max-height: 50px; border-radius: 4px;">';
                }
                return '<span class="text-muted">No logo</span>';
            });

        CRUD::column('products_count')
            ->label('Products')
            ->type('closure')
            ->function(function($entry) {
                return $entry->products()->count();
            });

        CRUD::column('sort_order')
            ->label('Sort Order')
            ->type('number');

        CRUD::column('synced_at')
            ->label('Last Synced')
            ->type('datetime')
            ->format('Y-m-d H:i:s');

        // Filters
        $this->crud->addFilter(
            [
                'name' => 'type',
                'type' => 'dropdown',
                'label' => 'Type'
            ],
            [
                'prepaid' => 'Prepaid',
                'postpaid' => 'Postpaid',
            ],
            function ($value) {
                $this->crud->addClause('where', 'type', $value);
            }
        );

        $this->crud->addFilter(
            [
                'name' => 'status',
                'type' => 'dropdown',
                'label' => 'Status'
            ],
            [
                1 => 'Active',
                0 => 'Inactive',
            ],
            function ($value) {
                $this->crud->addClause('where', 'status', $value);
            }
        );
    }

    protected function setupCreateOperation()
    {
        $this->setupFormFields();
    }

    protected function setupUpdateOperation()
    {
        $this->setupFormFields();
    }

    protected function setupShowOperation()
    {
        CRUD::column('operator_id')->label('Operator ID');
        CRUD::column('operator_name')->label('Operator Name');
        CRUD::column('operator_code')->label('Operator Code');
        CRUD::column('description')->label('Description');
        CRUD::column('type')->label('Type');
        CRUD::column('status')->label('Status')->type('boolean');
        CRUD::column('logo_url')->label('Logo URL');
        CRUD::column('sort_order')->label('Sort Order');
        CRUD::column('synced_at')->label('Last Synced')->type('datetime');
        CRUD::column('created_at')->label('Created At')->type('datetime');
        CRUD::column('updated_at')->label('Updated At')->type('datetime');
    }

    private function setupFormFields()
    {
        CRUD::field('operator_name')
            ->label('Operator Name')
            ->type('text')
            ->attributes(['required' => 'required']);

        CRUD::field('operator_id')
            ->label('Operator ID')
            ->type('text')
            ->hint('Unique identifier from Tripay API')
            ->attributes(['required' => 'required']);

        CRUD::field('operator_code')
            ->label('Operator Code')
            ->type('text')
            ->hint('Short code for the operator (e.g., TSEL, AXIS)')
            ->attributes(['maxlength' => 10]);

        CRUD::field('description')
            ->label('Description')
            ->type('textarea')
            ->attributes(['rows' => 3]);

        CRUD::field('type')
            ->label('Type')
            ->type('select_from_array')
            ->options(['prepaid' => 'Prepaid', 'postpaid' => 'Postpaid'])
            ->default('prepaid');

        CRUD::field('status')
            ->label('Active')
            ->type('boolean')
            ->default(true);

        CRUD::field('logo_url')
            ->label('Logo URL')
            ->type('url')
            ->hint('URL to the operator logo image');

        CRUD::field('sort_order')
            ->label('Sort Order')
            ->type('number')
            ->default(0)
            ->hint('Higher numbers appear first');
    }

    public function store()
    {
        // Validate unique operator_id
        $this->crud->getRequest()->validate([
            'operator_id' => 'required|unique:tripay_operators,operator_id',
            'operator_name' => 'required|string|max:255',
            'type' => 'required|in:prepaid,postpaid',
            'logo_url' => 'nullable|url',
        ]);

        return parent::store();
    }

    public function update()
    {
        // Validate unique operator_id except current
        $id = $this->crud->getCurrentEntryId();
        $this->crud->getRequest()->validate([
            'operator_id' => 'required|unique:tripay_operators,operator_id,' . $id,
            'operator_name' => 'required|string|max:255',
            'type' => 'required|in:prepaid,postpaid',
            'logo_url' => 'nullable|url',
        ]);

        return parent::update();
    }
}