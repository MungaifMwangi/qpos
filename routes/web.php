<?php

use App\Http\Controllers\Backend\CurrencyController;
use App\Http\Controllers\Backend\Pos\CartController;
use App\Http\Controllers\Backend\Product\ProductController;
use App\Http\Controllers\Backend\Report\ReportController;
use App\Http\Controllers\Backend\SupplierController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Backend\Product\CategoryController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\RolePermission\PermissionController;
use App\Http\Controllers\Backend\Pos\OrderController;
use App\Http\Controllers\Backend\Product\BrandController;
use App\Http\Controllers\Backend\Product\PurchaseController;
use App\Http\Controllers\Backend\RolePermission\RoleController;
use App\Http\Controllers\Backend\Product\UnitController;
use App\Http\Controllers\Backend\UserManagementController;
use App\Http\Controllers\Backend\WebsiteSettingController;
use App\Http\Controllers\Backend\Accounting\ExpenseController;
use App\Http\Controllers\Backend\Accounting\ProfitLossController;
use App\Models\Supplier;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ====================== FRONTEND ======================

// homepage
Route::get('/', function () {
    return to_route('login');
})->name('frontend.home');

//authentication
Route::match(['get', 'post'], 'login', [AuthController::class, 'login'])->name('login');

Route::get('logout', [AuthController::class, 'logout'])->name('logout');
Route::match(['get', 'post'], 'sign-up', [AuthController::class, 'register'])->name('signup');
Route::match(['get', 'post'], 'forget-password', [AuthController::class, 'forgetPassword'])->name('forget.password');
Route::match(['get', 'post'], 'new-password', [AuthController::class, 'newPassword'])->name('new.password');
Route::match(['get', 'post'], 'password-reset', [AuthController::class, 'resetPassword'])->name('password.reset');
Route::get('resend-otp', [AuthController::class, 'resendOtp'])->name('resend.otp');

// google auth
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.handle.callback');

// ====================== /FRONTEND =====================

// ====================== BACKEND =======================

Route::prefix('admin')->as('backend.admin.')->middleware(['admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', ProductController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('purchase', PurchaseController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('units', UnitController::class);
    Route::resource('currencies', CurrencyController::class);
    Route::match(['get', 'post'], 'import/products', [ProductController::class,'import'])->name('products.import');
    Route::get('currencies/default/{id}', [CurrencyController::class, 'setDefault'])->name('currencies.setDefault');
    Route::get('customers/orders/{id}', [CustomerController::class, 'orders'])->name('customers.orders');
    Route::get('purchase/products/{id}', [PurchaseController::class, 'purchaseProducts'])->name('purchase.products');
    // Specific order sub-routes MUST come before Route::resource('orders') to avoid wildcard collision
    Route::get('orders/invoice/{id}', [OrderController::class,'invoice'])->name('orders.invoice');
    Route::get('orders/pos-invoice/{id}', [OrderController::class, 'posInvoice'])->name('orders.pos-invoice');
    Route::get('orders/transactions/{id}', [OrderController::class, 'transactions'])->name('orders.transactions');
    Route::match(['get', 'post'], 'orders/due/collection/{id}', [OrderController::class, 'collection'])->name('due.collection');
    Route::get('collection/invoice/{id}', [OrderController::class, 'collectionInvoice'])->name('collectionInvoice');
    // void uses POST to avoid being swallowed by resource DELETE admin/orders/{order}
    Route::post('orders/void/{id}', [OrderController::class, 'void'])->name('orders.void');
    // Resource route AFTER all specific order routes
    Route::resource('orders', OrderController::class);
    Route::resource('categories', CategoryController::class);
    //start report

    Route::get('/sale/summery', [ReportController::class, 'saleSummery'])->name('sale.summery');
    Route::get('/sale/report', [ReportController::class, 'saleReport'])->name('sale.report');
    Route::get('/inventory/report', [ReportController::class, 'inventoryReport'])->name('inventory.report');
    Route::post('/inventory/adjust', [ReportController::class, 'adjustStock'])->name('inventory.adjust');
    //end report

    //start accounting
    Route::resource('expenses', ExpenseController::class);
    Route::get('profit-loss', [ProfitLossController::class, 'index'])->name('profit-loss.index');

    // Double-Entry General Ledger
    Route::get('accounting/ledger/chart', [\App\Http\Controllers\Backend\Accounting\GeneralLedgerController::class, 'chartOfAccounts'])->name('accounting.ledger.chart');
    Route::get('accounting/ledger/entries', [\App\Http\Controllers\Backend\Accounting\GeneralLedgerController::class, 'journalEntries'])->name('accounting.ledger.entries');
    Route::get('accounting/ledger/entry-lines/{id}', [\App\Http\Controllers\Backend\Accounting\GeneralLedgerController::class, 'entryLines'])->name('accounting.ledger.entry-lines');
    Route::get('accounting/ledger/trial-balance', [\App\Http\Controllers\Backend\Accounting\GeneralLedgerController::class, 'trialBalance'])->name('accounting.ledger.trial-balance');

    // Debtors AR
    Route::get('accounting/debtors', [\App\Http\Controllers\Backend\Accounting\DebtorController::class, 'index'])->name('debtors.index');
    Route::get('accounting/debtors/statement/{id}', [\App\Http\Controllers\Backend\Accounting\DebtorController::class, 'statement'])->name('debtors.statement');
    Route::post('accounting/debtors/receipt', [\App\Http\Controllers\Backend\Accounting\DebtorController::class, 'storeReceipt'])->name('debtors.receipt');

    // Creditors AP
    Route::get('accounting/creditors', [\App\Http\Controllers\Backend\Accounting\CreditorController::class, 'index'])->name('creditors.index');
    Route::post('accounting/creditors/payment', [\App\Http\Controllers\Backend\Accounting\CreditorController::class, 'storePayment'])->name('creditors.payment');
    //end accounting

    //start LPO procurement
    Route::prefix('lpo')->name('lpo.')->group(function () {
        $c = \App\Http\Controllers\Backend\Procurement\LpoController::class;
        Route::get('/',              [$c, 'index'])       ->name('index');
        Route::get('/create',        [$c, 'create'])      ->name('create');
        Route::post('/store',        [$c, 'store'])       ->name('store');
        Route::get('/show/{id}',     [$c, 'show'])        ->name('show');
        Route::get('/print/{id}',    [$c, 'printLpo'])    ->name('print');
        Route::get('/{id}/items',    [$c, 'getItems'])    ->name('items');   // AJAX — GRN modal data
        Route::post('/{id}/grn',     [$c, 'storeGrn'])   ->name('store-grn');
        Route::post('/{id}/invoice', [$c, 'storeInvoice'])->name('store-invoice');
    });
    //end LPO procurement

   // start pos
    Route::get('/get/products', [CartController::class, 'getProducts'])->name('getProducts');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::put('/cart/increment', [CartController::class, 'increment']);
    Route::put('/cart/decrement', [CartController::class, 'decrement']);
    Route::put('/cart/delete', [CartController::class, 'delete']);
    Route::put('/cart/empty', [CartController::class, 'empty']);
    Route::put('/order/create', [OrderController::class, 'store']);
    Route::get('/get/customers',[CustomerController::class,'getCustomers']);
    Route::post('/create/customers', [CustomerController::class, 'store']);
    //end pos
    Route::get('profile', [DashboardController::class, 'profile'])->name('profile');
    Route::post('profile/update', [AuthController::class, 'update'])->name('profile.update');

    // user management
    Route::prefix('users')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('users');
        Route::get('suspend/{id}/{status}', [UserManagementController::class, 'suspend'])->name('user.suspend');
        Route::match(['get', 'post'], 'create', [UserManagementController::class, 'create'])->name('user.create');
        Route::match(['get', 'post'], 'edit/{id}', [UserManagementController::class, 'edit'])->name('user.edit');
        Route::get('delete/{id}', [UserManagementController::class, 'delete'])->name('user.delete');
    });

    // settings
    Route::prefix('settings')->group(function () {
        // website settings
        Route::prefix('website')->group(function () {
            Route::controller(WebsiteSettingController::class)->prefix('general')->group(function () {
                Route::get('/', 'websiteGeneral')->name('settings.website.general');
                Route::post('update-info', 'websiteInfoUpdate')->name('settings.website.info.update');
                Route::post('update-contacts', 'websiteContactsUpdate')->name('settings.website.contacts.update');
                Route::post('update-social-links', 'websiteSocialLinkUpdate')->name('settings.website.social.link.update');
                Route::post('update-style-settings', 'websiteStyleSettingsUpdate')->name('settings.website.style.settings.update');
                Route::post('update-custom-css', 'websiteCustomCssUpdate')->name('settings.website.custom.css.update');
                Route::post('update-notification-settings', 'websiteNotificationSettingsUpdate')->name('settings.website.notification.settings.update');
                Route::post('update-website-status', 'websiteStatusUpdate')->name('settings.website.status.update');

                Route::post('update-invoice-settings', 'websiteInvoiceUpdate')->name('settings.website.invoice.update');

                Route::get('check-update', 'checkForUpdate')->name('settings.website.check.update');
                Route::post('apply-update', 'applyUpdate')->name('settings.website.apply.update');
            });

            Route::controller(RoleController::class)->prefix('roles')->group(function () {
                Route::get('/', 'index')->name('roles');
                Route::post('create', 'store')->name('roles.create');
                Route::get('show/{id}', 'show')->name('roles.show');
                Route::put('update/{id}', 'update')->name('roles.update');
                Route::get('delete/{id}', 'destroy')->name('roles.delete');
                Route::post('role-permission/{id}', 'updatePermission')->name('update.role-permissions');
                Route::get('role-wise-permissions/{id?}', 'roleWisePermissions')->name('role-wise-permissions');
            });

            Route::controller(PermissionController::class)->prefix('permissions')->group(function () {
                Route::get('/', 'index')->name('permissions');
                Route::post('create', 'store')->name('permissions.store');
                // Route::get('show/{id}', 'show')->name('roles.show');
                Route::put('update/{id}', 'update')->name('permissions.update');
                Route::get('delete/{id}', 'destroy')->name('permissions.delete');
            });
        });
    });
});

// ====================== /BACKEND ======================

Route::get('clear-all', function () {
    Artisan::call('optimize:clear');
    return redirect()->back();
});

Route::get('storage-link', function () {
    Artisan::call('storage:link');
    return redirect()->back();
});

Route::get('test', [TestController::class, 'test'])->name('test');
