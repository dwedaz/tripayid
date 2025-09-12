{{-- Tripay PPOB Menu Script - Injected automatically when package is installed --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Only add menu if not already present
    if (document.querySelector('[data-tripay-menu]')) return;
    
    // Find the sidebar nav (multiple selectors for different themes)
    const nav = document.querySelector('.sidebar nav ul, .sidebar-menu, .nav.nav-pills.flex-column, .main-sidebar .nav, .navbar-nav, .sidebar .nav, .nav-pills');
    if (!nav) {
        console.log('Tripay: Could not find sidebar navigation');
        return;
    }
    
    // Create menu HTML
    const menuItem = document.createElement('li');
    menuItem.className = 'nav-item nav-dropdown';
    menuItem.setAttribute('data-tripay-menu', 'true');
    
    menuItem.innerHTML = `
        <a class="nav-link nav-dropdown-toggle" href="#" onclick="event.preventDefault(); this.parentElement.classList.toggle('open');">
            <i class="nav-icon {{ config('tripay.backpack.menu.icon', 'la la-money-bill') }}"></i>
            {{ config('tripay.backpack.menu.title', 'Tripay PPOB') }}
            <i class="nav-arrow fas fa-angle-left" style="float: right; transition: transform 0.3s;"></i>
        </a>
        <ul class="nav-dropdown-items" style="display: none; padding-left: 1rem;">
            <li class="nav-item">
                <a class="nav-link" href="{{ backpack_url('tripay') }}">
                    <i class="nav-icon la la-tachometer"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ backpack_url('tripay/categories') }}">
                    <i class="nav-icon la la-tags"></i>
                    Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ backpack_url('tripay/operators') }}">
                    <i class="nav-icon la la-signal"></i>
                    Operators
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ backpack_url('tripay/products') }}">
                    <i class="nav-icon la la-box"></i>
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ backpack_url('tripay/transactions') }}">
                    <i class="nav-icon la la-exchange-alt"></i>
                    Transactions
                </a>
            </li>
        </ul>
    `;
    
    // Add click handler for dropdown
    const toggle = menuItem.querySelector('.nav-dropdown-toggle');
    toggle.addEventListener('click', function(e) {
        e.preventDefault();
        const parent = this.parentElement;
        const items = parent.querySelector('.nav-dropdown-items');
        const arrow = this.querySelector('.nav-arrow');
        
        if (parent.classList.contains('open')) {
            parent.classList.remove('open');
            items.style.display = 'none';
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        } else {
            parent.classList.add('open');
            items.style.display = 'block';
            if (arrow) arrow.style.transform = 'rotate(-90deg)';
        }
    });
    
    // Insert the menu item
    nav.appendChild(menuItem);
});
</script>

<style>
[data-tripay-menu] .nav-dropdown-items {
    background: rgba(0,0,0,0.1);
    margin: 0.25rem 0;
    border-radius: 0.25rem;
}
[data-tripay-menu] .nav-dropdown-items .nav-link {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}
</style>