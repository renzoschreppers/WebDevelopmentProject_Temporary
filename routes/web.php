<?php

use App\Http\Middleware\ActiveUser;
use App\Http\Middleware\Admin;
use App\Livewire\Admin\Categories;
use App\Livewire\Admin\DietaryTags;
use App\Livewire\Admin\Dishes;
use App\Livewire\Admin\MenuPlanner;
use App\Livewire\Admin\Menus;
use App\Livewire\DishBrowser;
use App\Livewire\DishDetail;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\WeekMenu;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::view('/', 'home')->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', ActiveUser::class])
    ->name('dashboard');

Route::get('menu', WeekMenu::class)->name('menu');
Route::get('dishes', DishBrowser::class)->name('dishes');
Route::get('dishes/{dish}', DishDetail::class)->name('dishes.show');

Route::middleware(['auth', ActiveUser::class])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

Route::middleware(['auth', ActiveUser::class, Admin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/', 'admin.dashboard')->name('dashboard');
        Route::get('categories', Categories::class)->name('categories');
        Route::get('dietary-tags', DietaryTags::class)->name('dietary-tags');
        Route::get('dishes', Dishes::class)->name('dishes');
        Route::get('menus', Menus::class)->name('menus');
        Route::get('menus/{menu}/edit', MenuPlanner::class)->name('menus.edit');
    });

require __DIR__.'/auth.php';
