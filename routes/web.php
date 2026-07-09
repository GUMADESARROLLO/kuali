<?php

use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\AssetMaintenanceController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\PersonController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SlaRuleController;
use App\Http\Controllers\Admin\SoftwareLicenseController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\SubcategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('home'))->name('dashboard');
    Route::get('/mis-tickets', [DashboardController::class, 'user'])->name('user.dashboard');
    Route::get('/mis-tickets/crear', [TicketController::class, 'create'])->name('user.tickets.create');
    Route::post('/mis-tickets', [TicketController::class, 'store'])->name('user.tickets.store');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'admin'])->name('dashboard');
        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/crear', [AdminTicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [AdminTicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/assign', [AdminTicketController::class, 'assign'])
            ->middleware('auth')
            ->name('tickets.assign');
        Route::post('/tickets/{ticket}/comment', [AdminTicketController::class, 'comment'])
            ->middleware('auth')
            ->name('tickets.comment');
        Route::post('/tickets/{ticket}/status', [AdminTicketController::class, 'updateStatus'])
            ->middleware('auth')
            ->name('tickets.status');
        Route::post('/tickets/{ticket}/priority', [AdminTicketController::class, 'updatePriority'])
            ->middleware('auth')
            ->name('tickets.priority');
        Route::post('/tickets/{ticket}/reclassify', [AdminTicketController::class, 'reclassify'])
            ->middleware('auth')
            ->name('tickets.reclassify');
        Route::post('/tickets/{ticket}/resolve', [AdminTicketController::class, 'resolve'])
            ->middleware('auth')
            ->name('tickets.resolve');

        Route::get('/departamentos', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('/departamentos/crear', [DepartmentController::class, 'create'])->name('departments.create');
        Route::post('/departamentos', [DepartmentController::class, 'store'])->name('departments.store');
        Route::get('/departamentos/{department}/editar', [DepartmentController::class, 'edit'])->name('departments.edit');
        Route::put('/departamentos/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departamentos/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        Route::get('/usuarios', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/usuarios/crear', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/usuarios', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/usuarios/{user}/editar', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::post('/usuarios/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/usuarios/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        Route::get('/categorias', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::get('/categorias/crear', [AdminCategoryController::class, 'create'])->name('categories.create');
        Route::post('/categorias', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('/categorias/{category}/editar', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categorias/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categorias/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('/categorias/{category}/subcategorias', [AdminCategoryController::class, 'storeSubcategory'])->name('categories.subcategories.store');
        Route::put('/subcategorias/{subcategory}', [AdminCategoryController::class, 'updateSubcategory'])->name('subcategories.update');
        Route::delete('/subcategorias/{subcategory}', [AdminCategoryController::class, 'destroySubcategory'])->name('subcategories.destroy');
        Route::post('/categorias/reordenar', [AdminCategoryController::class, 'reorderCategories'])->name('categories.reorder');
        Route::post('/subcategorias/reordenar', [AdminCategoryController::class, 'reorderSubcategories'])->name('subcategories.reorder');

        Route::get('/reportes', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reportes/exportar', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/reportes/activos-por-persona', [ReportController::class, 'assetsByPerson'])->name('reports.assets-by-person');
        Route::get('/reportes/activos-por-persona/pdf', [ReportController::class, 'exportAssetsPdf'])->name('reports.assets-by-person.pdf');

        Route::get('/calendarios', [CalendarController::class, 'index'])->name('calendars.index');
        Route::post('/calendarios', [CalendarController::class, 'store'])->name('calendars.store');
        Route::put('/calendarios/{calendar}', [CalendarController::class, 'update'])->name('calendars.update');
        Route::delete('/calendarios/{calendar}', [CalendarController::class, 'destroy'])->name('calendars.destroy');

        Route::get('/sla', [SlaRuleController::class, 'index'])->name('sla-rules.index');
        Route::post('/sla', [SlaRuleController::class, 'store'])->name('sla-rules.store');
        Route::put('/sla/{slaRule}', [SlaRuleController::class, 'update'])->name('sla-rules.update');
        Route::delete('/sla/{slaRule}', [SlaRuleController::class, 'destroy'])->name('sla-rules.destroy');

        // Inventario
        Route::get('/empresas', [CompanyController::class, 'index'])->name('companies.index');
        Route::post('/empresas', [CompanyController::class, 'store'])->name('companies.store');
        Route::put('/empresas/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::delete('/empresas/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');

        Route::get('/activos', [AssetController::class, 'index'])->name('assets.index');
        Route::get('/activos/crear', [AssetController::class, 'create'])->name('assets.create');
        Route::post('/activos', [AssetController::class, 'store'])->name('assets.store');
        Route::get('/activos/{asset}', [AssetController::class, 'show'])->name('assets.show');
        Route::get('/activos/{asset}/editar', [AssetController::class, 'edit'])->name('assets.edit');
        Route::put('/activos/{asset}', [AssetController::class, 'update'])->name('assets.update');
        Route::delete('/activos/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');
        Route::post('/activos/{asset}/asignar', [AssetController::class, 'assign'])->name('assets.assign');

        Route::get('/mantenimiento', [AssetMaintenanceController::class, 'index'])->name('maintenance.index');
        Route::post('/mantenimiento', [AssetMaintenanceController::class, 'store'])->name('maintenance.store');
        Route::put('/mantenimiento/{asset_maintenance}', [AssetMaintenanceController::class, 'update'])->name('maintenance.update');
        Route::delete('/mantenimiento/{asset_maintenance}', [AssetMaintenanceController::class, 'destroy'])->name('maintenance.destroy');

        Route::get('/licencias', [SoftwareLicenseController::class, 'index'])->name('licenses.index');
        Route::post('/licencias', [SoftwareLicenseController::class, 'store'])->name('licenses.store');
        Route::put('/licencias/{software_license}', [SoftwareLicenseController::class, 'update'])->name('licenses.update');
        Route::delete('/licencias/{software_license}', [SoftwareLicenseController::class, 'destroy'])->name('licenses.destroy');
        Route::post('/licencias/{software_license}/asignar', [SoftwareLicenseController::class, 'assign'])->name('licenses.assign');

        Route::get('/personas', [PersonController::class, 'index'])->name('persons.index');
        Route::post('/personas', [PersonController::class, 'store'])->name('persons.store');
        Route::put('/personas/{person}', [PersonController::class, 'update'])->name('persons.update');
        Route::delete('/personas/{person}', [PersonController::class, 'destroy'])->name('persons.destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// API interna
Route::middleware('auth')->get('/api/subcategorias/{category}', [SubcategoryController::class, 'byCategory'])->name('api.subcategories');

require __DIR__.'/auth.php';
