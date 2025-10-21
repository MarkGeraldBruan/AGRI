<?php

use App\Http\Controllers\Client\UserController;
use App\Http\Controllers\Client\SuppliesController;
use App\Http\Controllers\Client\EquipmentController;
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\ReportController;
use App\Http\Controllers\Client\StockCardController;
use App\Http\Controllers\Client\PropertyCardController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Client\AnnouncementController;
use App\Http\Controllers\Client\HelpController;
use App\Http\Controllers\Client\NotificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Client\RsmiController;
use App\Http\Controllers\Client\RpciController;
use App\Http\Controllers\Client\RpcPpeController;
use App\Http\Controllers\Client\PpesController;
use Illuminate\Support\Facades\Route;

// Root route - redirect to dashboard after login
Route::get('/', function () {
    return auth()->check() ? redirect('/client/dashboard') : redirect('/home');
});

// Authentication routes
Auth::routes();

// Home route (landing page before login)
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Protected client routes
Route::prefix('client')->middleware('auth:web')->group(function(){
    
    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('client.dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('client.dashboard.stats');
    Route::get('/dashboard/recent-items', [DashboardController::class, 'getRecentItems'])->name('client.dashboard.recent');
    Route::get('/dashboard/low-stock', [DashboardController::class, 'getLowStockItems'])->name('client.dashboard.lowstock');
    
    // User management routes
    Route::resource('users', UserController::class);
    
    // Profile settings routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('client.profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('client.profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('client.profile.password');
    Route::delete('/profile/remove-avatar', [ProfileController::class, 'removeAvatar'])->name('client.profile.remove-avatar');
    
    // Supplies routes with additional functionality
    Route::resource('supplies', SuppliesController::class);
    Route::get('supplies-export', [SuppliesController::class, 'export'])->name('supplies.export');

    // Equipment routes with export functionality
    Route::get('equipment/api/classifications', [EquipmentController::class, 'getClassifications'])->name('equipment.classifications');
    Route::resource('equipment', EquipmentController::class)->names([
        'index' => 'client.equipment.index'
    ]);
    Route::get('equipment-export', [EquipmentController::class, 'export'])->name('equipment.export');

    // Report routes
    Route::resource('reports', ReportController::class)->names([
        'index' => 'client.reports.index'
    ]);
        
    // Individual report routes
    Route::get('report/rsmi', [ReportController::class, 'rsmi'])->name('client.report.rsmi');
    Route::get('report/rpci', [ReportController::class, 'rpci'])->name('client.report.rpci');
    Route::get('report/rpc-ppe', [RpcPpeController::class, 'index'])->name('client.report.rpc-ppe');

    // Stock Card routes (Fixed naming convention)
    Route::prefix('stockcard')->name('client.stockcard.')->group(function () {
        Route::get('/', [StockCardController::class, 'index'])->name('index');
        Route::get('/show/{id}', [StockCardController::class, 'show'])->name('show');
        
        // Stock In routes
        Route::get('/stock-in', [StockCardController::class, 'stockIn'])->name('stock-in');
        Route::post('/stock-in', [StockCardController::class, 'processStockIn'])->name('stock-in.process');
        
        // Stock Out routes
        Route::get('/stock-out', [StockCardController::class, 'stockOut'])->name('stock-out');
        Route::post('/stock-out', [StockCardController::class, 'processStockOut'])->name('stock-out.process');
    });
    
    Route::resource('propertycard', PropertyCardController::class)->names([
        'index' => 'client.propertycard.index'
    ]);
    
    
    Route::post('announcement/{id}/reserve', [AnnouncementController::class, 'reserveSupplies'])
        ->name('client.announcement.reserve');

    Route::post('announcement/{id}/stock-out', [AnnouncementController::class, 'stockOutSupplies'])
        ->name('client.announcement.stock-out');

    Route::post('announcement/bulk-publish', [AnnouncementController::class, 'bulkPublish'])
        ->name('client.announcement.bulk-publish');

    Route::post('announcement/bulk-delete', [AnnouncementController::class, 'bulkDelete'])
        ->name('client.announcement.bulk-delete');

    Route::post('announcement/{id}/toggle-status', [AnnouncementController::class, 'toggleStatus'])
        ->name('client.announcement.toggle-status');

    Route::resource('announcement', AnnouncementController::class)->names([
        'index' => 'client.announcement.index',
        'create' => 'client.announcement.create',
        'store' => 'client.announcement.store',
        'show' => 'client.announcement.show',
        'edit' => 'client.announcement.edit',
        'update' => 'client.announcement.update',
        'destroy' => 'client.announcement.destroy'
    ]);
    // Help routes 
    Route::resource('help', HelpController::class)->names([
        'index' => 'client.help.index',
        'create' => 'client.help.create',
        'store' => 'client.help.store',
        'show' => 'client.help.show',
        'edit' => 'client.help.edit',
        'update' => 'client.help.update',
        'destroy' => 'client.help.destroy'
    ]);
    
    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::get('/notifications/recent', [NotificationController::class, 'getRecent']);

    // Alternative route names for backward compatibility
    Route::get('inventory', [SuppliesController::class, 'index'])->name('client.inventory');
    Route::get('inventory/{supply}', [SuppliesController::class, 'show'])->name('client.inventory.show');
});

// Route para sa RSMI report page
Route::prefix('client/report')->group(function () {
    Route::get('/rsmi', [RsmiController::class, 'index'])->name('client.report.rsmi');
    Route::get('/rsmi/export/pdf', [RsmiController::class, 'exportPDF'])->name('client.report.rsmi.export.pdf');
    Route::get('/rsmi/export/excel', [RsmiController::class, 'exportExcel'])->name('client.report.rsmi.export.excel');

    // RPCI report routes
    Route::get('/rpci', [RpciController::class, 'index'])->name('client.report.rpci');
    // Use ReportController export handlers for RPCl quick-export endpoints
    Route::get('/rpci/export/pdf', [App\Http\Controllers\Client\ReportController::class, 'exportRpciPdf'])->name('client.report.rpci.export.pdf');
    Route::get('/rpci/export/excel', [App\Http\Controllers\Client\ReportController::class, 'exportRpciExcel'])->name('client.report.rpci.export.excel');

    // PPES report routes
    Route::get('/ppes', [PpesController::class, 'index'])->name('client.report.ppes');
    Route::get('/ppes/export/pdf', [PpesController::class, 'exportPDF'])->name('client.report.ppes.export.pdf');
    Route::get('/ppes/export/excel', [PpesController::class, 'exportExcel'])->name('client.report.ppes.export.excel');

    // RPC-PPE report routes
    Route::get('/rpc-ppe', [RpcPpeController::class, 'index'])->name('client.report.rpc-ppe');
    Route::get('/rpc-ppe/export/pdf', [RpcPpeController::class, 'exportPDF'])->name('client.report.rpc-ppe.export.pdf');
    Route::get('/rpc-ppe/export/excel', [RpcPpeController::class, 'exportExcel'])->name('client.report.rpc-ppe.export.excel');
});

// Fallback route for authenticated users
Route::middleware('auth:web')->group(function(){
    // Redirect authenticated users to dashboard if they hit /home
    Route::get('/home', function() {
        return redirect('/client/dashboard');
    });
});