<?php

namespace Tripay\PPOB\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Tripay\PPOB\Models\Product;
use Tripay\PPOB\Models\Category;
use Tripay\PPOB\Models\Operator;

class ProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Product::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/tripay/products');
        CRUD::setEntityNameStrings('product', 'products');
        
        $this->crud->setHeading('Tripay Products', 'Manage PPOB Products');
        $this->crud->setSubheading('Manage prepaid and postpaid products');
    }

    protected function setupListOperation()
    {
        CRUD::column('product_name')->label('Product Name')->limit(50);
        
        CRUD::column('category')->label('Category')
            ->type('relationship')
            ->attribute('category_name')
            ->model('Tripay\PPOB\Models\Category');
            
        CRUD::column('operator')->label('Operator')
            ->type('relationship')  
            ->attribute('operator_name')
            ->model('Tripay\PPOB\Models\Operator');

        CRUD::column('product_price')
            ->label('Price')
            ->type('closure')
            ->function(function($entry) {
                return 'Rp ' . number_format($entry->product_price, 0, ',', '.');
            });

        CRUD::column('selling_price')
            ->label('Selling Price')
            ->type('closure')
            ->function(function($entry) {
                return 'Rp ' . number_format($entry->selling_price, 0, ',', '.');
            });

        CRUD::column('type')->label('Type');
        CRUD::column('status')->label('Status')->type('boolean');
        CRUD::column('is_featured')->label('Featured')->type('boolean');

        // Filters
        $this->crud->addFilter([
            'name' => 'category_id',
            'type' => 'select2',
            'label' => 'Category'
        ], function () {
            return Category::pluck('category_name', 'category_id')->toArray();
        }, function ($value) {
            $this->crud->addClause('where', 'category_id', $value);
        });

        $this->crud->addFilter([
            'name' => 'operator_id',
            'type' => 'select2',
            'label' => 'Operator'
        ], function () {
            return Operator::pluck('operator_name', 'operator_id')->toArray();
        }, function ($value) {
            $this->crud->addClause('where', 'operator_id', $value);
        });
    }

    protected function setupCreateOperation()
    {
        $this->setupFormFields();
    }

    protected function setupUpdateOperation()
    {
        $this->setupFormFields();
    }

    private function setupFormFields()
    {
        CRUD::field('product_id')->label('Product ID')->type('text');
        CRUD::field('product_name')->label('Product Name')->type('text');
        
        CRUD::field('category_id')->label('Category')
            ->type('select2')
            ->model('Tripay\PPOB\Models\Category')
            ->attribute('category_name');
            
        CRUD::field('operator_id')->label('Operator')
            ->type('select2')
            ->model('Tripay\PPOB\Models\Operator')
            ->attribute('operator_name');

        CRUD::field('product_price')->label('Product Price')->type('number')->attributes(['step' => '0.01']);
        CRUD::field('selling_price')->label('Selling Price')->type('number')->attributes(['step' => '0.01']);
        CRUD::field('description')->label('Description')->type('textarea');
        CRUD::field('type')->label('Type')->type('select_from_array')
            ->options(['prepaid' => 'Prepaid', 'postpaid' => 'Postpaid']);
        CRUD::field('status')->label('Active')->type('boolean')->default(true);
        CRUD::field('is_featured')->label('Featured')->type('boolean')->default(false);
        CRUD::field('sort_order')->label('Sort Order')->type('number')->default(0);
    }
}