<?php

namespace Tripay\PPOB\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Tripay\PPOB\Models\Category;

class CategoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Category::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/tripay/categories');
        CRUD::setEntityNameStrings('category', 'categories');
        
        // Custom styling and icons
        $this->crud->setHeading('Tripay Categories', 'Manage PPOB Categories');
        $this->crud->setSubheading('Manage prepaid and postpaid product categories');
    }

    protected function setupListOperation()
    {
        CRUD::column('name')
            ->label('Category Name')
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
        CRUD::column('category_id')->label('Category ID');
        CRUD::column('category_name')->label('Category Name');
        CRUD::column('description')->label('Description');
        CRUD::column('type')->label('Type');
        CRUD::column('status')->label('Status')->type('boolean');
        CRUD::column('sort_order')->label('Sort Order');
        CRUD::column('synced_at')->label('Last Synced')->type('datetime');
        CRUD::column('created_at')->label('Created At')->type('datetime');
        CRUD::column('updated_at')->label('Updated At')->type('datetime');
    }

    private function setupFormFields()
    {
        CRUD::field('category_name')
            ->label('Category Name')
            ->type('text')
            ->attributes(['required' => 'required']);

        CRUD::field('category_id')
            ->label('Category ID')
            ->type('text')
            ->hint('Unique identifier from Tripay API')
            ->attributes(['required' => 'required']);

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

        CRUD::field('sort_order')
            ->label('Sort Order')
            ->type('number')
            ->default(0)
            ->hint('Higher numbers appear first');
    }

    public function store()
    {
        // Validate unique category_id
        $this->crud->getRequest()->validate([
            'category_id' => 'required|unique:tripay_categories,category_id',
            'category_name' => 'required|string|max:255',
            'type' => 'required|in:prepaid,postpaid',
        ]);

        return parent::store();
    }

    public function update()
    {
        // Validate unique category_id except current
        $id = $this->crud->getCurrentEntryId();
        $this->crud->getRequest()->validate([
            'category_id' => 'required|unique:tripay_categories,category_id,' . $id,
            'category_name' => 'required|string|max:255',
            'type' => 'required|in:prepaid,postpaid',
        ]);

        return parent::update();
    }
}