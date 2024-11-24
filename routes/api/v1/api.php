<?php

use App\Http\Controllers\Api\v1\Auth\AuthController;
use App\Http\Controllers\Api\v1\Dashboard\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'auth:api'], function () {

    Route::get('/user', [AuthController::class, 'get_user']);
    Route::apiResources([
        'products' => \App\Http\Controllers\Api\v1\Dashboard\ProductController::class,
        'categories' => \App\Http\Controllers\Api\v1\Dashboard\CategoryController::class,
        'clients' => \App\Http\Controllers\Api\v1\Dashboard\ClientController::class,
        'bills' => \App\Http\Controllers\Api\v1\Dashboard\BillController::class,
        'users' => \App\Http\Controllers\Api\v1\Dashboard\UserController::class,
        'tags' => \App\Http\Controllers\Api\v1\Dashboard\TagController::class,
        'elements' => \App\Http\Controllers\Api\v1\Dashboard\ElementController::class,
        'product_tags' => \App\Http\Controllers\Api\v1\Dashboard\ProductTagController::class,
        'orders'=>\App\Http\Controllers\Api\v1\Dashboard\OrderController::class,
    ]);

    Route::get('bills_by_client', [\App\Http\Controllers\Api\v1\Dashboard\BillController::class, 'billsByClient']);
    Route::get('final_consumer', [\App\Http\Controllers\Api\v1\Dashboard\ClientController::class, 'get_final_consumer']);
    Route::get('select-clients-with-email', [\App\Http\Controllers\Api\v1\Dashboard\ClientController::class, 'selectWithEmail']);


    Route::get('tags/select/tags', [\App\Http\Controllers\Api\v1\Dashboard\TagController::class, 'select_tags']);
    Route::get('tags/select/short-tags', [\App\Http\Controllers\Api\v1\Dashboard\TagController::class, 'select_short_tags']);


    Route::get('products/select/products', [\App\Http\Controllers\Api\v1\Dashboard\ProductController::class, 'select']);
    Route::get('products/select/all_products', [\App\Http\Controllers\Api\v1\Dashboard\ProductController::class, 'selectAll']);
    Route::get('clients/select/clients', [\App\Http\Controllers\Api\v1\Dashboard\ClientController::class, 'select']);
    Route::get('categories/select/categories', [\App\Http\Controllers\Api\v1\Dashboard\CategoryController::class, 'getAllCategories']);
    Route::get('roles/select/roles', [\App\Http\Controllers\Api\v1\Dashboard\RoleController::class, 'roles_for_select']);

    Route::put('products/restore/{id}', [\App\Http\Controllers\Api\v1\Dashboard\ProductController::class, 'restore']);
    Route::put('clients/restore/{id}', [\App\Http\Controllers\Api\v1\Dashboard\ClientController::class, 'restore']);
    Route::put('categories/restore/{id}', [\App\Http\Controllers\Api\v1\Dashboard\CategoryController::class, 'restore']);
    Route::put('bills/restore/{id}', [\App\Http\Controllers\Api\v1\Dashboard\BillController::class, 'restore']);
    Route::put('users/restore/{id}', [\App\Http\Controllers\Api\v1\Dashboard\UserController::class, 'restore']);

    Route::post('categories/store_image/{category}', [\App\Http\Controllers\Api\v1\Dashboard\CategoryController::class, 'store_image']);

    Route::get('statistics/lastSales', [\App\Http\Controllers\Api\v1\Dashboard\BillController::class, 'lastSales']);

    Route::get('statistics/most_sold', [\App\Http\Controllers\Api\v1\Dashboard\DashboardController::class, 'most_sold_products']);

    Route::post('products/store_tag/{product}', [\App\Http\Controllers\Api\v1\Dashboard\ProductController::class, 'store_tag']);
    Route::delete('products/delete_product_tag/{product}', [\App\Http\Controllers\Api\v1\Dashboard\ProductController::class, 'delete_product_tag']);

    Route::delete('tags/delete_tag_of_article/{tag}', [\App\Http\Controllers\Api\v1\Dashboard\TagController::class, 'delete_tag_of_article']);

    Route::get('dashboard/total/products', [\App\Http\Controllers\Api\v1\Dashboard\DashboardController::class, 'total_products']);
    Route::get('dashboard/total/clients', [\App\Http\Controllers\Api\v1\Dashboard\DashboardController::class, 'total_clients']);
    Route::get('dashboard/utility', [\App\Http\Controllers\Api\v1\Dashboard\DashboardController::class, 'utilityFor']);

    Route::post('products/store_element/{product}', [\App\Http\Controllers\Api\v1\Dashboard\ProductController::class, 'store_element']);

    Route::get('elements/all/elements', [\App\Http\Controllers\Api\v1\Dashboard\ElementController::class, 'all']);

    Route::get('elements/show/byElement/{element}/{product}', [\App\Http\Controllers\Api\v1\Dashboard\ElementController::class, 'showProductByElement']);

    Route::get('products/show/elements/{product}', [\App\Http\Controllers\Api\v1\Dashboard\ProductController::class, 'showElementsByProduct']);

    Route::get('products/download/all', [\App\Http\Controllers\Api\v1\Dashboard\ProductController::class, 'download']);
    Route::get('orders/download/{order}', [\App\Http\Controllers\Api\v1\Dashboard\OrderController::class, 'download']);
    Route::post('send-order-by-email/{order}', [\App\Http\Controllers\Api\v1\Dashboard\OrderController::class, 'sendByEmail']);

    Route::post('settings',[SettingController::class, 'storeOrUpdate']);
    Route::get('settings',[SettingController::class, 'index']);

    Route::post('/upload-template-certificate', [\App\Http\Controllers\Api\v1\Dashboard\TemplateController::class, 'uploadDocx']);
    Route::get('/info-template', [\App\Http\Controllers\Api\v1\Dashboard\TemplateController::class, 'getInfoTemplate']);
    Route::delete('/delete-template', [\App\Http\Controllers\Api\v1\Dashboard\TemplateController::class, 'deleteTemplate']);

    Route::get('/variables-template', [\App\Http\Controllers\Api\v1\Dashboard\TemplateController::class, 'getVariablesOfTemplate']);

    Route::post('/generate-certificate/{id}', [\App\Http\Controllers\Api\v1\Dashboard\TemplateController::class, 'generateCertificate']);

    Route::post('/generate-recipe/{id}', [\App\Http\Controllers\Api\v1\Dashboard\BillController::class, 'download']);
    Route::get('/sales-per-day-by-user', [\App\Http\Controllers\Api\v1\Dashboard\BillController::class, 'totalPerDayByUser']);
    Route::put('/try-check-invoice/{id}', [\App\Http\Controllers\Api\v1\Dashboard\BillController::class, 'tryCheckInvoice']);

    Route::get('/can-create-invoice', [SettingController::class, 'canCreateInvoice']);

    Route::post('/upload-signature', [SettingController::class, 'uploadSignature']);
    Route::get('/check-signature', [SettingController::class, 'checkSign']);
    Route::delete('/delete-signature', [SettingController::class, 'deleteSignature']);



    Route::get('/taxes', [\App\Http\Controllers\Api\v1\Dashboard\TaxeController::class, 'index']);
    Route::post('/taxes', [\App\Http\Controllers\Api\v1\Dashboard\TaxeController::class, 'store']);
    Route::put('/taxes/{id}', [\App\Http\Controllers\Api\v1\Dashboard\TaxeController::class, 'activeSelection']);




});


Route::group(['namespace' => 'App\Http\Controllers\Api\v1\Auth'], function () {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

});

Route::get('/signature/download/{filename}', [\App\Http\Controllers\Api\v1\Dashboard\BillController::class, 'downloadSignature'])
    ->name('download.signature')
    ->middleware('signed');
