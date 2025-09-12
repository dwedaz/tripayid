# Backpack Integration

Due to potential compatibility issues with the automatic sidebar menu registration, the Tripay PPOB package currently requires manual menu setup.

## Manual Menu Setup

To add the Tripay PPOB menu to your Backpack sidebar, add the following to your Backpack configuration or a custom view:

### Option 1: Using Backpack's Widget System

Add this to your Backpack layout or create a custom widget:

```php
// In your AppServiceProvider boot method or similar
if (class_exists('\Backpack\CRUD\app\Library\Widget')) {
    \Backpack\CRUD\app\Library\Widget::add([
        'type' => 'script',
        'content' => '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof window.backpack !== "undefined" && window.backpack.addSidebarItem) {
                window.backpack.addSidebarItem({
                    title: "Tripay PPOB",
                    icon: "la la-money-bill",
                    url: "' . backpack_url('tripay') . '",
                    children: [
                        { title: "Dashboard", url: "' . backpack_url('tripay') . '" },
                        { title: "Categories", url: "' . backpack_url('tripay/categories') . '" },
                        { title: "Operators", url: "' . backpack_url('tripay/operators') . '" },
                        { title: "Products", url: "' . backpack_url('tripay/products') . '" },
                        { title: "Transactions", url: "' . backpack_url('tripay/transactions') . '" }
                    ]
                });
            }
        });
        </script>'
    ]);
}
```

### Option 2: Manual Sidebar Configuration

If you prefer to add menu items directly to your Backpack sidebar configuration, you can add:

```php
// In your backpack/base.php config file or custom sidebar
'Tripay PPOB' => [
    'icon' => 'la la-money-bill',
    'children' => [
        [
            'title' => 'Dashboard',
            'url' => backpack_url('tripay'),
        ],
        [
            'title' => 'Categories', 
            'url' => backpack_url('tripay/categories'),
        ],
        [
            'title' => 'Operators',
            'url' => backpack_url('tripay/operators'), 
        ],
        [
            'title' => 'Products',
            'url' => backpack_url('tripay/products'),
        ],
        [
            'title' => 'Transactions',
            'url' => backpack_url('tripay/transactions'),
        ],
    ],
],
```

## Available Routes

The following admin routes are available:

- `GET /admin/tripay` - Dashboard
- `GET /admin/tripay/categories` - Categories CRUD
- `GET /admin/tripay/operators` - Operators CRUD  
- `GET /admin/tripay/products` - Products CRUD
- `GET /admin/tripay/transactions` - Transactions (read-only)
- `GET /admin/tripay/test` - Test endpoint

## Future Updates

The automatic menu registration will be improved in future versions to be more compatible with different Backpack configurations and avoid JavaScript conflicts.