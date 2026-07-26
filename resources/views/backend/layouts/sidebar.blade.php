@php
$route = request()->route()->getName();
@endphp
<div class="sidebar">
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            {{-- Dashboard --}}
            @can('dashboard_view')
            <li class="nav-item">
                <a href="{{ route('backend.admin.dashboard') }}"
                    class="nav-link {{ $route === 'backend.admin.dashboard' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>Dashboard</p>
                </a>
            </li>
            @endcan

            {{-- POS --}}
            @can('sale_create')
            <li class="nav-item">
                <a href="{{ route('backend.admin.cart.index') }}"
                    class="nav-link {{ $route === 'backend.admin.cart.index' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-cart-plus"></i>
                    <p>POS</p>
                </a>
            </li>
            @endcan

            {{-- ── Customers ── --}}
            @if (auth()->user()->hasAnyPermission([
                'customer_create','customer_view','customer_update','customer_delete',
            ]))
            <li class="nav-item {{ request()->routeIs(['backend.admin.customers.*']) ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs(['backend.admin.customers.*']) ? 'active' : '' }}">
                    <i class="fas fa-users nav-icon"></i>
                    <p>
                        Customers
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @if (auth()->user()->hasAnyPermission(['customer_view','customer_update','customer_delete']))
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.customers.index') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.customers.index','backend.admin.customers.edit']) ? 'active' : '' }}">
                            <i class="fas fa-list nav-icon"></i>
                            <p>All Customers</p>
                        </a>
                    </li>
                    @endif
                    @can('customer_create')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.customers.create') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.customers.create']) ? 'active' : '' }}">
                            <i class="fas fa-plus nav-icon"></i>
                            <p>Add Customer</p>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endif

            {{-- ── Products ── --}}
            @if (auth()->user()->hasAnyPermission([
                'product_create','product_view','product_update','product_delete','product_import','product_purchase',
            ]))
            <li class="nav-item {{ request()->routeIs(['backend.admin.products.*','backend.admin.brands.*','backend.admin.categories.*','backend.admin.units.*']) ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs(['backend.admin.products.*','backend.admin.brands.*','backend.admin.categories.*','backend.admin.units.*']) ? 'active' : '' }}">
                    <i class="fas fa-box nav-icon"></i>
                    <p>
                        Products
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @if (auth()->user()->hasAnyPermission(['product_view','product_update','product_delete']))
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.products.index') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.products.index','backend.admin.products.edit']) ? 'active' : '' }}">
                            <i class="fas fa-list nav-icon"></i>
                            <p>Product List</p>
                        </a>
                    </li>
                    @endif
                    @can('product_create')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.products.create') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.products.create']) ? 'active' : '' }}">
                            <i class="fas fa-plus nav-icon"></i>
                            <p>Add Product</p>
                        </a>
                    </li>
                    @endcan
                    @can('product_import')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.products.import') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.products.import']) ? 'active' : '' }}">
                            <i class="fas fa-file-import nav-icon"></i>
                            <p>Product Import</p>
                        </a>
                    </li>
                    @endcan
                    @if (auth()->user()->hasAnyPermission(['brand_create','brand_view','brand_update','brand_delete']))
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.brands.index') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.brands.*']) ? 'active' : '' }}">
                            <i class="fas fa-tag nav-icon"></i>
                            <p>Brands</p>
                        </a>
                    </li>
                    @endif
                    @if (auth()->user()->hasAnyPermission(['category_create','category_view','category_update','category_delete']))
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.categories.index') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.categories.*']) ? 'active' : '' }}">
                            <i class="fas fa-folder nav-icon"></i>
                            <p>Categories</p>
                        </a>
                    </li>
                    @endif
                    @if (auth()->user()->hasAnyPermission(['unit_create','unit_view','unit_update','unit_delete']))
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.units.index') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.units.*']) ? 'active' : '' }}">
                            <i class="fas fa-ruler nav-icon"></i>
                            <p>Units</p>
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- ── Inventory ── --}}
            @if (auth()->user()->hasAnyPermission([
                'reports_inventory','lpo_view','grn_receive',
            ]))
            <li class="nav-item {{ request()->routeIs(['backend.admin.inventory.*']) ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs(['backend.admin.inventory.*']) ? 'active' : '' }}">
                    <i class="fas fa-warehouse nav-icon"></i>
                    <p>
                        Inventory
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @can('grn_receive')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.inventory.goods-received.index') }}"
                            class="nav-link {{ request()->routeIs('backend.admin.inventory.goods-received.*') ? 'active' : '' }}">
                            <i class="fas fa-truck-loading nav-icon"></i>
                            <p>Goods Received</p>
                        </a>
                    </li>
                    @endcan
                    @can('reports_inventory')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.inventory.report') }}#adjust"
                            class="nav-link">
                            <i class="fas fa-balance-scale nav-icon"></i>
                            <p>Stock Adjustment</p>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endif

            {{-- ── Suppliers (collapsed by default) ── --}}
            @if (auth()->user()->hasAnyPermission([
                'supplier_create','supplier_view','supplier_update','supplier_delete',
            ]))
            <li class="nav-item {{ request()->routeIs(['backend.admin.suppliers.*','backend.admin.supplier-invoices.*']) ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs(['backend.admin.suppliers.*','backend.admin.supplier-invoices.*']) ? 'active' : '' }}">
                    <i class="fas fa-truck nav-icon"></i>
                    <p>
                        Suppliers
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @if (auth()->user()->hasAnyPermission(['supplier_view','supplier_update','supplier_delete']))
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.suppliers.index') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.suppliers.index','backend.admin.suppliers.edit']) ? 'active' : '' }}">
                            <i class="fas fa-list nav-icon"></i>
                            <p>All Suppliers</p>
                        </a>
                    </li>
                    @endif
                    @can('supplier_create')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.suppliers.create') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.suppliers.create']) ? 'active' : '' }}">
                            <i class="fas fa-plus nav-icon"></i>
                            <p>Add Supplier</p>
                        </a>
                    </li>
                    @endcan
                    @can('lpo_view')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.supplier-invoices.index') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.supplier-invoices.*']) ? 'active' : '' }}">
                            <i class="fas fa-file-invoice-dollar nav-icon"></i>
                            <p>Supplier Invoices</p>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endif

            {{-- ── Sale ── --}}
            @if (auth()->user()->hasAnyPermission(['sale_view']))
            <li class="nav-item {{ request()->routeIs(['backend.admin.orders.*']) ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs(['backend.admin.orders.*']) ? 'active' : '' }}">
                    <i class="fas fa-tags nav-icon"></i>
                    <p>
                        Sales
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @can('sale_view')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.orders.index') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.orders.index']) ? 'active' : '' }}">
                            <i class="fas fa-list nav-icon"></i>
                            <p>Sale List</p>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endif

            {{-- ── Purchase ── --}}
            @if (auth()->user()->hasAnyPermission([
                'purchase_create','purchase_view','purchase_update','purchase_delete',
            ]))
            <li class="nav-item {{ request()->routeIs(['backend.admin.purchase.*']) ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs(['backend.admin.purchase.*']) ? 'active' : '' }}">
                    <i class="fas fa-shopping-bag nav-icon"></i>
                    <p>
                        Purchases
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @can('purchase_view')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.purchase.index') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.purchase.index']) ? 'active' : '' }}">
                            <i class="fas fa-list nav-icon"></i>
                            <p>Purchase List</p>
                        </a>
                    </li>
                    @endcan
                    @can('purchase_create')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.purchase.create') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.purchase.create']) ? 'active' : '' }}">
                            <i class="fas fa-plus nav-icon"></i>
                            <p>New Purchase</p>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endif

            {{-- ── LPO Procurement ── --}}
            @if (auth()->user()->hasAnyPermission([
                'lpo_view','lpo_create','grn_receive','lpo_invoice_match',
            ]))
            <li class="nav-item {{ request()->routeIs(['backend.admin.lpo.*']) ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs(['backend.admin.lpo.*']) ? 'active' : '' }}">
                    <i class="fas fa-file-contract nav-icon"></i>
                    <p>
                        LPO Procurement
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @can('lpo_view')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.lpo.index') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.lpo.index']) ? 'active' : '' }}">
                            <i class="fas fa-list nav-icon"></i>
                            <p>LPO List</p>
                        </a>
                    </li>
                    @endcan
                    @can('lpo_create')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.lpo.create') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.lpo.create']) ? 'active' : '' }}">
                            <i class="fas fa-plus nav-icon"></i>
                            <p>New LPO Requisition</p>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endif

            {{-- ── Accounting & Ledger ── --}}
            @if (auth()->user()->hasAnyPermission([
                'chart_of_accounts_view','general_ledger_view','trial_balance_view',
                'debtors_view','debtors_receipt_create','creditors_view','creditors_payment_create',
                'expense_view','expense_create','expense_update','expense_delete',
            ]))
            <li class="nav-item {{ request()->routeIs(['backend.admin.expenses.*','backend.admin.accounting.*','backend.admin.debtors.*','backend.admin.creditors.*']) ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs(['backend.admin.expenses.*','backend.admin.accounting.*','backend.admin.debtors.*','backend.admin.creditors.*']) ? 'active' : '' }}">
                    <i class="fas fa-calculator nav-icon"></i>
                    <p>
                        Accounting & Ledger
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @can('chart_of_accounts_view')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.accounting.ledger.chart') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.accounting.ledger.chart']) ? 'active' : '' }}">
                            <i class="fas fa-sitemap nav-icon"></i>
                            <p>Chart of Accounts</p>
                        </a>
                    </li>
                    @endcan
                    @can('general_ledger_view')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.accounting.ledger.entries') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.accounting.ledger.entries']) ? 'active' : '' }}">
                            <i class="fas fa-book nav-icon"></i>
                            <p>General Ledger</p>
                        </a>
                    </li>
                    @endcan
                    @can('trial_balance_view')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.accounting.ledger.trial-balance') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.accounting.ledger.trial-balance']) ? 'active' : '' }}">
                            <i class="fas fa-balance-scale nav-icon"></i>
                            <p>Trial Balance</p>
                        </a>
                    </li>
                    @endcan
                    @can('debtors_view')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.debtors.index') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.debtors.*']) ? 'active' : '' }}">
                            <i class="fas fa-hand-holding-usd nav-icon"></i>
                            <p>Debtors (AR)</p>
                        </a>
                    </li>
                    @endcan
                    @can('creditors_view')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.creditors.index') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.creditors.*']) ? 'active' : '' }}">
                            <i class="fas fa-file-invoice-dollar nav-icon"></i>
                            <p>Creditors (AP)</p>
                        </a>
                    </li>
                    @endcan
                    @can('expense_view')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.expenses.index') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.expenses.*']) ? 'active' : '' }}">
                            <i class="fas fa-receipt nav-icon"></i>
                            <p>Expenses</p>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endif

            {{-- ── Reports ── --}}
            @if (auth()->user()->hasAnyPermission([
                'reports_summary','reports_sales','reports_inventory','profit_loss_view',
            ]))
            <li class="nav-item {{ request()->routeIs(['backend.admin.sale.report','backend.admin.sale.summery','backend.admin.profit-loss.*','backend.admin.inventory.overview']) ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs(['backend.admin.sale.report','backend.admin.sale.summery','backend.admin.profit-loss.*','backend.admin.inventory.overview']) ? 'active' : '' }}">
                    <i class="fas fa-chart-bar nav-icon"></i>
                    <p>
                        Reports
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @can('reports_summary')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.sale.summery') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.sale.summery']) ? 'active' : '' }}">
                            <i class="fas fa-chart-line nav-icon"></i>
                            <p>Sales Summary</p>
                        </a>
                    </li>
                    @endcan
                    @can('reports_sales')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.sale.report') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.sale.report']) ? 'active' : '' }}">
                            <i class="fas fa-file-invoice nav-icon"></i>
                            <p>Sales Report</p>
                        </a>
                    </li>
                    @endcan
                    @can('profit_loss_view')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.profit-loss.index') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.profit-loss.*']) ? 'active' : '' }}">
                            <i class="fas fa-file-invoice-dollar nav-icon"></i>
                            <p>Profit & Loss</p>
                        </a>
                    </li>
                    @endcan
                    @can('reports_inventory')
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.inventory.overview') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.inventory.overview']) ? 'active' : '' }}">
                            <i class="fas fa-warehouse nav-icon"></i>
                            <p>Inventory Overview</p>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endif

            @if (auth()->user()->hasAnyPermission([
                'currency_create','currency_view','currency_update','currency_delete','currency_set_default',
                'role_create','role_view','role_update','role_delete','permission_view',
                'user_create','user_view','user_update','user_delete','user_suspend',
                'website_settings','contact_settings','socials_settings','style_settings',
                'custom_settings','notification_settings','website_status_settings',
                'invoice_settings','system_update_settings',
            ]))
            <li class="nav-header">SETTINGS</li>
            <li class="nav-item {{ request()->routeIs(['backend.admin.settings.*','backend.admin.currencies.*','backend.admin.roles*','backend.admin.permissions*','backend.admin.users*']) ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs(['backend.admin.settings.*','backend.admin.currencies.*','backend.admin.roles*','backend.admin.permissions*','backend.admin.users*']) ? 'active' : '' }}">
                    <i class="fas fa-cog nav-icon"></i>
                    <p>
                        Settings
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @if (auth()->user()->hasAnyPermission([
                        'website_settings','contact_settings','socials_settings','style_settings',
                        'custom_settings','notification_settings','website_status_settings',
                        'invoice_settings','system_update_settings',
                    ]))
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.settings.website.general') }}?active-tab=website-info"
                            class="nav-link {{ $route === 'backend.admin.settings.website.general' ? 'active' : '' }}">
                            <i class="fas fa-sliders-h nav-icon"></i>
                            <p>General Settings</p>
                        </a>
                    </li>
                    @endif
                    @if (auth()->user()->hasAnyPermission(['currency_create','currency_view','currency_update','currency_delete']))
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.currencies.index') }}"
                            class="nav-link {{ request()->routeIs(['backend.admin.currencies.*']) ? 'active' : '' }}">
                            <i class="fas fa-coins nav-icon"></i>
                            <p>Currency</p>
                        </a>
                    </li>
                    @endif
                    @if (auth()->user()->hasAnyPermission([
                        'role_create','role_view','role_update','role_delete','permission_view',
                    ]))
                    <li class="nav-item">
                        <a href="#" class="nav-link d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-user-shield nav-icon"></i>
                                Roles & Permissions
                            </span>
                            <span>
                                <i class="fas fa-angle-left right"></i>
                            </span>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('role_view')
                            <li class="nav-item">
                                <a href="{{ route('backend.admin.roles') }}"
                                    class="nav-link {{ $route === 'backend.admin.roles' ? 'active' : '' }}">
                                    <i class="fas fa-user-tag nav-icon"></i>
                                    <p>Roles</p>
                                </a>
                            </li>
                            @endcan
                            @can('permission_view')
                            <li class="nav-item">
                                <a href="{{ route('backend.admin.permissions') }}"
                                    class="nav-link {{ $route === 'backend.admin.permissions' ? 'active' : '' }}">
                                    <i class="fas fa-key nav-icon"></i>
                                    <p>Permissions</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif
                    @if (auth()->user()->hasAnyPermission([
                        'user_create','user_view','user_update','user_delete','user_suspend',
                    ]))
                    <li class="nav-item">
                        <a href="{{ route('backend.admin.users') }}"
                            class="nav-link {{ $route === 'backend.admin.users' ? 'active' : '' }}">
                            <i class="fas fa-users-cog nav-icon"></i>
                            <p>User Management</p>
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif

        </ul>
    </nav>
</div>

<script>
    const treeviewElements = document.querySelectorAll('.nav-treeview');
    treeviewElements.forEach(treeviewElement => {
        const navLinkElements = treeviewElement.querySelectorAll('.nav-link.active');
        if (navLinkElements.length > 0) {
            const parentNavItem = treeviewElement.closest('.nav-item');
            if (parentNavItem) {
                parentNavItem.classList.add('menu-open');
            }
            const childNavLink = parentNavItem.querySelector('.nav-link');
            if (childNavLink) {
                childNavLink.classList.add('active');
            }
        }
    });
</script>
