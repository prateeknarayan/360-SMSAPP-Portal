<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AppsController;
use App\Http\Controllers\UserInterfaceController;
use App\Http\Controllers\CardsController;
use App\Http\Controllers\ComponentsController;
use App\Http\Controllers\ExtensionController;
use App\Http\Controllers\PageLayoutController;
use App\Http\Controllers\FormsController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\MiscellaneousController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\superuser\ChannelController;
use App\Http\Controllers\superuser\AuthenticationTypeController;
use App\Http\Controllers\superuser\SupportedPlatformController;
use App\Http\Controllers\ProcessRecordController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\NumberController;
use App\Http\Controllers\EmailController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Main Page Route

// Route::get('test', function () {
//     dd('Welcome to webhook user routes.',);
// });


Route::group(['prefix' => 'su', 'middleware' =>'auth','namespace' =>'App\Http\Controllers\superuser'], function () {
    Route::resource('authentication-types', AuthenticationTypeController::class);
    Route::get('data/authentication-type-list',  [AuthenticationTypeController::class, 'authList']);
     Route::get('getdata/authentication/{id}',  [AuthenticationTypeController::class, 'getList']);
    Route::resource('channels', ChannelController::class);
    Route::get('data/channels-list',  [ChannelController::class, 'channelList']);
    Route::resource('supported-platforms', SupportedPlatformController::class);
    Route::get('data/platforms-list',  [SupportedPlatformController::class, 'platformsList']);
    Route::post('authentication-types/delete/all',  [AuthenticationTypeController::class, 'deleteAll']);
    Route::post('channels/delete/all',  [ChannelController::class, 'deleteAll']);
    Route::post('supported-platforms/delete/all',  [SupportedPlatformController::class, 'deleteAll']);

});


Route::group(['prefix' => 'su', 'middleware' =>'auth'], function () {
    Route::resource('users', UserController::class);
    Route::get('data/users-list',  [UserController::class, 'usersList']);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::get('data/permissions-list',  [PermissionController::class, 'permissionList']);   

   // 
});

// Route::group(['middleware' =>'auth'], function () {
    Route::get('process-records',  [ProcessRecordController::class, 'index']);
    Route::get('process-records/{record}',  [ProcessRecordController::class, 'singleRecord']);

    Route::get('data/records-list',  [ProcessRecordController::class, 'recordslList']);
    Route::resource('clients', ClientController::class);
    Route::get('data/clients-list',  [ClientController::class, 'clientsList']);
    Route::get('clients/{id}/numbers',  [ClientController::class, 'clientNumbers']);
    Route::get('clients/{id}/numbers-list',  [ClientController::class, 'clientNumbersList']);
    Route::get('clients/{id}/numbers/create',  [ClientController::class, 'numberCreate']);
    Route::post('clients/numbers/store',  [ClientController::class, 'numberStore']);
    
    //import routes for clients
    Route::get('import-clients', [ClientController::class, 'fileImportExport']);
    Route::post('file-import', [ClientController::class, 'fileImport'])->name('file-import');
    Route::get('file-export', [ClientController::class, 'fileExport'])->name('file-export');



    Route::resource('numbers', NumberController::class);
    Route::get('data/numbers-list',  [NumberController::class, 'numbersList']);
    Route::get('search',  [NumberController::class, 'search']);
    Route::post('numbers/delete/all',  [NumberController::class, 'deleteAll']);
    Route::post('clients/delete/all',  [ClientController::class, 'deleteAll']);
    //Route::post('search',  [NumberController::class, 'searchResults']);



// });


/* Route Dashboards */
// Route::group(['prefix' => 'dashboard','middleware' =>'auth'], function () {
//     Route::get('/', [DashboardController::class, 'dashboardEcommerce'])->name('dashboard-ecommerce');
//     Route::get('analytics', [DashboardController::class, 'dashboardAnalytics'])->name('dashboard-analytics');
//     Route::get('ecommerce', [DashboardController::class, 'dashboardEcommerce'])->name('dashboard-ecommerce');
// });
/* Route Dashboards */

/* Route Apps */
Route::group(['prefix' => 'app', 'middleware' =>'auth'], function () {
    Route::get('email', [AppsController::class, 'emailApp'])->name('app-email');
    Route::get('chat', [AppsController::class, 'chatApp'])->name('app-chat');
    Route::get('todo', [AppsController::class, 'todoApp'])->name('app-todo');
    Route::get('calendar', [AppsController::class, 'calendarApp'])->name('app-calendar');
    Route::get('kanban', [AppsController::class, 'kanbanApp'])->name('app-kanban');
    Route::get('invoice/list', [AppsController::class, 'invoice_list'])->name('app-invoice-list');
    Route::get('invoice/preview', [AppsController::class, 'invoice_preview'])->name('app-invoice-preview');
    Route::get('invoice/edit', [AppsController::class, 'invoice_edit'])->name('app-invoice-edit');
    Route::get('invoice/add', [AppsController::class, 'invoice_add'])->name('app-invoice-add');
    Route::get('invoice/print', [AppsController::class, 'invoice_print'])->name('app-invoice-print');
    Route::get('ecommerce/shop', [AppsController::class, 'ecommerce_shop'])->name('app-ecommerce-shop');
    Route::get('ecommerce/details', [AppsController::class, 'ecommerce_details'])->name('app-ecommerce-details');
    Route::get('ecommerce/wishlist', [AppsController::class, 'ecommerce_wishlist'])->name('app-ecommerce-wishlist');
    Route::get('ecommerce/checkout', [AppsController::class, 'ecommerce_checkout'])->name('app-ecommerce-checkout');
    Route::get('file-manager', [AppsController::class, 'file_manager'])->name('app-file-manager');
    Route::get('access-roles', [AppsController::class, 'access_roles'])->name('app-access-roles');
    Route::get('access-permission', [AppsController::class, 'access_permission'])->name('app-access-permission');
    Route::get('user/list', [AppsController::class, 'user_list'])->name('app-user-list');
    Route::get('user/view/account', [AppsController::class, 'user_view_account'])->name('app-user-view-account');
    Route::get('user/view/security', [AppsController::class, 'user_view_security'])->name('app-user-view-security');
    Route::get('user/view/billing', [AppsController::class, 'user_view_billing'])->name('app-user-view-billing');
    Route::get('user/view/notifications', [AppsController::class, 'user_view_notifications'])->name('app-user-view-notifications');
    Route::get('user/view/connections', [AppsController::class, 'user_view_connections'])->name('app-user-view-connections');
});
/* Route Apps */

/* Route UI */
Route::group(['prefix' => 'ui','middleware' =>'auth'], function () {
    Route::get('typography', [UserInterfaceController::class, 'typography'])->name('ui-typography');
});
/* Route UI */

/* Route Icons */
Route::group(['prefix' => 'icons','middleware' =>'auth'], function () {
    Route::get('feather', [UserInterfaceController::class, 'icons_feather'])->name('icons-feather');
});
/* Route Icons */

/* Route Cards */
Route::group(['prefix' => 'card','middleware' =>'auth'], function () {
    Route::get('basic', [CardsController::class, 'card_basic'])->name('card-basic');
    Route::get('advance', [CardsController::class, 'card_advance'])->name('card-advance');
    Route::get('statistics', [CardsController::class, 'card_statistics'])->name('card-statistics');
    Route::get('analytics', [CardsController::class, 'card_analytics'])->name('card-analytics');
    Route::get('actions', [CardsController::class, 'card_actions'])->name('card-actions');
});
/* Route Cards */

/* Route Components */
Route::group(['prefix' => 'component/popovers','middleware' =>'auth'], function () {
    Route::get('accordion', [ComponentsController::class, 'accordion'])->name('component-accordion');
    Route::get('alert', [ComponentsController::class, 'alert'])->name('component-alert');
    Route::get('avatar', [ComponentsController::class, 'avatar'])->name('component-avatar');
    Route::get('badges', [ComponentsController::class, 'badges'])->name('component-badges');
    Route::get('breadcrumbs', [ComponentsController::class, 'breadcrumbs'])->name('component-breadcrumbs');
    Route::get('buttons', [ComponentsController::class, 'buttons'])->name('component-buttons');
    Route::get('carousel', [ComponentsController::class, 'carousel'])->name('component-carousel');
    Route::get('collapse', [ComponentsController::class, 'collapse'])->name('component-collapse');
    Route::get('divider', [ComponentsController::class, 'divider'])->name('component-divider');
    Route::get('dropdowns', [ComponentsController::class, 'dropdowns'])->name('component-dropdowns');
    Route::get('list-group', [ComponentsController::class, 'list_group'])->name('component-list-group');
    Route::get('modals', [ComponentsController::class, 'modals'])->name('component-modals');
    Route::get('pagination', [ComponentsController::class, 'pagination'])->name('component-pagination');
    Route::get('navs', [ComponentsController::class, 'navs'])->name('component-navs');
    Route::get('offcanvas', [ComponentsController::class, 'offcanvas'])->name('component-offcanvas');
    Route::get('tabs', [ComponentsController::class, 'tabs'])->name('component-tabs');
    Route::get('timeline', [ComponentsController::class, 'timeline'])->name('component-timeline');
    Route::get('pills', [ComponentsController::class, 'pills'])->name('component-pills');
    Route::get('tooltips', [ComponentsController::class, 'tooltips'])->name('component-tooltips');
    Route::get('popovers', [ComponentsController::class, 'popovers'])->name('component-popovers');
    Route::get('pill-badges', [ComponentsController::class, 'pill_badges'])->name('component-pill-badges');
    Route::get('progress', [ComponentsController::class, 'progress'])->name('component-progress');
    Route::get('spinner', [ComponentsController::class, 'spinner'])->name('component-spinner');
    Route::get('toast', [ComponentsController::class, 'toast'])->name('component-bs-toast');
});
/* Route Components */

/* Route Extensions */
Route::group(['prefix' => 'ext-component','middleware' =>'auth'], function () {
    Route::get('sweet-alerts', [ExtensionController::class, 'sweet_alert'])->name('ext-component-sweet-alerts');
    Route::get('block-ui', [ExtensionController::class, 'block_ui'])->name('ext-component-block-ui');
    Route::get('toastr', [ExtensionController::class, 'toastr'])->name('ext-component-toastr');
    Route::get('sliders', [ExtensionController::class, 'sliders'])->name('ext-component-sliders');
    Route::get('drag-drop', [ExtensionController::class, 'drag_drop'])->name('ext-component-drag-drop');
    Route::get('tour', [ExtensionController::class, 'tour'])->name('ext-component-tour');
    Route::get('clipboard', [ExtensionController::class, 'clipboard'])->name('ext-component-clipboard');
    Route::get('plyr', [ExtensionController::class, 'plyr'])->name('ext-component-plyr');
    Route::get('context-menu', [ExtensionController::class, 'context_menu'])->name('ext-component-context-menu');
    Route::get('swiper', [ExtensionController::class, 'swiper'])->name('ext-component-swiper');
    Route::get('tree', [ExtensionController::class, 'tree'])->name('ext-component-tree');
    Route::get('ratings', [ExtensionController::class, 'ratings'])->name('ext-component-ratings');
    Route::get('locale', [ExtensionController::class, 'locale'])->name('ext-component-locale');
});
/* Route Extensions */

/* Route Page Layouts */
Route::group(['prefix' => 'page-layouts','middleware' =>'auth'], function () {
    Route::get('collapsed-menu', [PageLayoutController::class, 'layout_collapsed_menu'])->name('layout-collapsed-menu');
    Route::get('full', [PageLayoutController::class, 'layout_full'])->name('layout-full');
    Route::get('without-menu', [PageLayoutController::class, 'layout_without_menu'])->name('layout-without-menu');
    Route::get('empty', [PageLayoutController::class, 'layout_empty'])->name('layout-empty');
    Route::get('blank', [PageLayoutController::class, 'layout_blank'])->name('layout-blank');
});
/* Route Page Layouts */

/* Route Forms */
Route::group(['prefix' => 'form','middleware' =>'auth'], function () {
    Route::get('input', [FormsController::class, 'input'])->name('form-input');
    Route::get('input-groups', [FormsController::class, 'input_groups'])->name('form-input-groups');
    Route::get('input-mask', [FormsController::class, 'input_mask'])->name('form-input-mask');
    Route::get('textarea', [FormsController::class, 'textarea'])->name('form-textarea');
    Route::get('checkbox', [FormsController::class, 'checkbox'])->name('form-checkbox');
    Route::get('radio', [FormsController::class, 'radio'])->name('form-radio');
    Route::get('custom-options', [FormsController::class, 'custom_options'])->name('form-custom-options');
    Route::get('switch', [FormsController::class, 'switch'])->name('form-switch');
    Route::get('select', [FormsController::class, 'select'])->name('form-select');
    Route::get('number-input', [FormsController::class, 'number_input'])->name('form-number-input');
    Route::get('file-uploader', [FormsController::class, 'file_uploader'])->name('form-file-uploader');
    Route::get('quill-editor', [FormsController::class, 'quill_editor'])->name('form-quill-editor');
    Route::get('date-time-picker', [FormsController::class, 'date_time_picker'])->name('form-date-time-picker');
    Route::get('layout', [FormsController::class, 'layouts'])->name('form-layout');
    Route::get('wizard', [FormsController::class, 'wizard'])->name('form-wizard');
    Route::get('validation', [FormsController::class, 'validation'])->name('form-validation');
    Route::get('repeater', [FormsController::class, 'form_repeater'])->name('form-repeater');
});
/* Route Forms */

/* Route Tables */
Route::group(['prefix' => 'table','middleware' =>'auth'], function () {
    Route::get('', [TableController::class, 'table'])->name('table');
    Route::get('datatable/basic', [TableController::class, 'datatable_basic'])->name('datatable-basic');
    Route::get('datatable/advance', [TableController::class, 'datatable_advance'])->name('datatable-advance');
});
/* Route Tables */

/* Route Pages */
// Route::group(['prefix' => 'page','middleware' =>'auth'], function () {
//     Route::get('account-settings-account', [PagesController::class, 'account_settings_account'])->name('page-account-settings-account');
//     Route::get('account-settings-security', [PagesController::class, 'account_settings_security'])->name('page-account-settings-security');
//     Route::get('account-settings-billing', [PagesController::class, 'account_settings_billing'])->name('page-account-settings-billing');
//     Route::get('account-settings-notifications', [PagesController::class, 'account_settings_notifications'])->name('page-account-settings-notifications');
//     Route::get('account-settings-connections', [PagesController::class, 'account_settings_connections'])->name('page-account-settings-connections');
//     Route::get('profile', [PagesController::class, 'profile'])->name('page-profile');
//     Route::get('faq', [PagesController::class, 'faq'])->name('page-faq');
//     Route::get('knowledge-base', [PagesController::class, 'knowledge_base'])->name('page-knowledge-base');
//     Route::get('knowledge-base/category', [PagesController::class, 'kb_category'])->name('page-knowledge-base');
//     Route::get('knowledge-base/category/question', [PagesController::class, 'kb_question'])->name('page-knowledge-base');
//     Route::get('pricing', [PagesController::class, 'pricing'])->name('page-pricing');
//     Route::get('api-key', [PagesController::class, 'api_key'])->name('page-api-key');
//     Route::get('blog/list', [PagesController::class, 'blog_list'])->name('page-blog-list');
//     Route::get('blog/detail', [PagesController::class, 'blog_detail'])->name('page-blog-detail');
//     Route::get('blog/edit', [PagesController::class, 'blog_edit'])->name('page-blog-edit');

//     // Miscellaneous Pages With Page Prefix
//     Route::get('coming-soon', [MiscellaneousController::class, 'coming_soon'])->name('misc-coming-soon');
//     Route::get('not-authorized', [MiscellaneousController::class, 'not_authorized'])->name('misc-not-authorized');
//     Route::get('maintenance', [MiscellaneousController::class, 'maintenance'])->name('misc-maintenance');
//     Route::get('license', [PagesController::class, 'license'])->name('page-license');
// });

/* Modal Examples */
Route::get('/modal-examples', [PagesController::class, 'modal_examples'])->name('modal-examples');

/* Route Pages */
Route::get('/error', [MiscellaneousController::class, 'error'])->name('error');

/* Route Authentication Pages */
Route::group(['prefix' => 'auth','middleware' =>'auth'], function () {
    Route::get('login-basic', [AuthenticationController::class, 'login_basic'])->name('auth-login-basic');
    Route::get('login-cover', [AuthenticationController::class, 'login_cover'])->name('auth-login-cover');
    Route::get('register-basic', [AuthenticationController::class, 'register_basic'])->name('auth-register-basic');
    Route::get('register-cover', [AuthenticationController::class, 'register_cover'])->name('auth-register-cover');
    Route::get('forgot-password-basic', [AuthenticationController::class, 'forgot_password_basic'])->name('auth-forgot-password-basic');
    Route::get('forgot-password-cover', [AuthenticationController::class, 'forgot_password_cover'])->name('auth-forgot-password-cover');
    Route::get('reset-password-basic', [AuthenticationController::class, 'reset_password_basic'])->name('auth-reset-password-basic');
    Route::get('reset-password-cover', [AuthenticationController::class, 'reset_password_cover'])->name('auth-reset-password-cover');
    Route::get('verify-email-basic', [AuthenticationController::class, 'verify_email_basic'])->name('auth-verify-email-basic');
    Route::get('verify-email-cover', [AuthenticationController::class, 'verify_email_cover'])->name('auth-verify-email-cover');
    Route::get('two-steps-basic', [AuthenticationController::class, 'two_steps_basic'])->name('auth-two-steps-basic');
    Route::get('two-steps-cover', [AuthenticationController::class, 'two_steps_cover'])->name('auth-two-steps-cover');
    Route::get('register-multisteps', [AuthenticationController::class, 'register_multi_steps'])->name('auth-register-multisteps');
    Route::get('lock-screen', [AuthenticationController::class, 'lock_screen'])->name('auth-lock_screen');
});
/* Route Authentication Pages */

/* Route Charts */
Route::group(['prefix' => 'chart','middleware' =>'auth'], function () {
    Route::get('apex', [ChartsController::class, 'apex'])->name('chart-apex');
    Route::get('chartjs', [ChartsController::class, 'chartjs'])->name('chart-chartjs');
    Route::get('echarts', [ChartsController::class, 'echarts'])->name('chart-echarts');
});
/* Route Charts */

// map leaflet
Route::get('/maps/leaflet', [ChartsController::class, 'maps_leaflet'])->name('map-leaflet');

// locale Route
Route::get('lang/{locale}', [LanguageController::class, 'swap']);

// Route::group(['namespace' => 'App\Http\Controllers\Auth'], function () {
    Auth::routes();
// });
/*------------------------------------------
--------------------------------------------
All Normal Users Routes List
--------------------------------------------
--------------------------------------------*/
Route::group(['namespace' => 'App\Http\Controllers','middleware' => ['auth', 'user-access:executive']], function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});

/*------------------------------------------
--------------------------------------------
All Super Admin Routes List
--------------------------------------------
--------------------------------------------*/
// Route::middleware(['auth', 'user-access:superadmin'])->group(function () {
Route::group(['prefix'=> 'superadmin','namespace' => 'App\Http\Controllers','middleware' =>['auth', 'user-access:superadmin']], function () {
    Route::get('home', [HomeController::class, 'superadminHome'])->name('superadmin.home');
   // Route::resource('users', UserController::class);
 });
  
/*------------------------------------------
--------------------------------------------
All Admin Routes List
--------------------------------------------
--------------------------------------------*/
//Route::middleware(['auth', 'user-access:admin'])->group(function () {
Route::group(['namespace' => 'App\Http\Controllers','middleware' =>['auth', 'user-access:admin']], function () { 
    Route::get('/admin/home', [HomeController::class, 'adminHome'])->name('admin.home');
});
  
/*------------------------------------------
--------------------------------------------
All Admin Routes List
--------------------------------------------
--------------------------------------------*/
// Route::middleware(['auth', 'user-access:manager', 'namespace' => 'App\Http\Controllers'])->group(function () {
  Route::group(['namespace' => 'App\Http\Controllers','middleware' =>['auth', 'user-access:manager']], function () {
    Route::get('/manager/home', [HomeController::class, 'managerHome'])->name('manager.home');
});
//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::group(['namespace' => 'App\Http\Controllers'], function () {
    Route::get('/sendemail', [EmailController::class, 'index']);
});

