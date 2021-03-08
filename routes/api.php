<?php

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

Route::group(['namespace' => 'V1', 'prefix' => 'v1'], function () {
    Route::group(['namespace' => 'Client', 'prefix' => 'client', 'as' => 'client.' /*, 'middleware' => ['auth:api']*/], function () {
        Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
            // Route::post('get_token','AuthController@getToken');
            Route::post('login', 'AuthController@login')->name('login');
            Route::post('register', 'AuthController@register')->name('register');
            Route::get('verification', 'AuthController@verification')->name('verification');
            //
            Route::get('logout', 'AuthController@logout')->name('logout')->middleware('auth:api');
            Route::get('profile', 'AuthController@profile')->name('profile')->middleware('auth:api');
            Route::post('profile/update', 'AuthController@profileUpdate')->name('profile_update')->middleware('auth:api');
            Route::post('profile/change_password', 'AuthController@changePassword')->name('change_password')->middleware('auth:api');
            Route::post('reset_password', 'AuthController@resetPassword')->name('reset_password');

            Route::get('social_login/{provider}', 'AuthController@redirectToProvider');
            Route::get('social_login/handler/{provider}', 'AuthController@social');
        });

        Route::group(['prefix' => 'category', 'as' => 'category.'], function () {
            Route::get('/', 'CategoryController@getList')->name('getList');
            // ->middleware(['scope:client.category.getList']);
            Route::get('{slug}', 'CategoryController@getDetail')->name('getDetail');
            // ->middleware(['scope:client.category.getDetail']);
        });

        Route::group(['prefix' => 'catalog', 'as' => 'catalog.'], function () {
            Route::get('/', 'CatalogController@getList')->name('getList');
            // ->middleware(['scope:client.catalog.getList']);
            Route::get('{slug}', 'CatalogController@getDetail')->name('getDetail');
            Route::get('{slug}/review', 'CatalogController@getReview')->name('getReview');
            // ->middleware(['scope:client.catalog.getDetail']);
        });

        Route::group(['prefix' => 'banner', 'as' => 'banner.'], function () {
            Route::get('/', 'BannerController@getList')->name('getList');
            Route::get('{slug}', 'BannerController@getDetail')->name('getDetail');
        });
        Route::group(['prefix' => 'promo', 'as' => 'promo.'], function () {
            Route::get('/', 'PromoController@getList')->name('getList');
            // ->middleware(['scope:client.promo.getList']);
            Route::get('{slug}', 'PromoController@getDetail')->name('getDetail');
            // ->middleware(['scope:client.promo.getDetail']);
        });

        Route::group(['prefix' => 'blog', 'as' => 'blog.'], function () {
            Route::get('/', 'BlogController@getList')->name('getList');
            // ->middleware(['scope:client.blog.getList']);
            Route::get('{slug}', 'BlogController@getDetail')->name('getDetail');
            // ->middleware(['scope:client.blog.getDetail']);
        });

        Route::group(['middleware' => ['auth:api']], function () {
            Route::group(['prefix' => 'cart', 'as' => 'cart.'], function () {
                Route::get('/', 'CartController@getData')->name('getData');
                Route::post('add', 'CartController@create')->name('create');
                Route::post('update', 'CartController@update')->name('update');
                Route::post('delete', 'CartController@delete')->name('delete');

                Route::post('shipment', 'CartController@shipment')->name('shipment');
                Route::post('checkout', 'CheckoutController@proced')->name('checkout');
            });

            Route::group(['prefix' => 'address', 'as' => 'address.'], function () {
                Route::get('/', 'AddressController@getData')->name('getData');
                Route::post('add', 'AddressController@create')->name('create');
                Route::post('{id}/update', 'AddressController@update')->name('update');
                Route::post('delete', 'AddressController@delete')->name('delete');

                Route::get('country', 'CountryController@getList')->name('getCountry');
                Route::get('province', 'ProvinceController@getList')->name('getProvince');
                Route::get('regency', 'RegencyController@getList')->name('getRegency');
                Route::get('district', 'DistrictController@getList')->name('getDistrict');
                Route::get('village', 'VillageController@getList')->name('getVillage');
            });

            Route::group(['prefix' => 'comment', 'as' => 'comment.'], function () {
                Route::get('/', 'CommentController@getComment')->name('getData');
                Route::post('/', 'CommentController@postComment')->name('create');
                Route::post('{id}', 'CommentController@editComment')->name('update');
                Route::delete('{id}', 'CommentController@deleteComment')->name('delete');
            });

            Route::group(['prefix' => 'wishlist', 'as' => 'wishlist.'], function () {
                Route::get('/', 'WishlistController@getList')->name('getData');
                Route::post('/add', 'WishlistController@create')->name('create');
                // Route::delete('delete','WishlistController@delete')->name('delete');
            });

            Route::group(['prefix' => 'report', 'as' => 'report.'], function () {
                Route::post('/', 'ReportController@report');
            });

            Route::group(['prefix' => 'transaction', 'as' => 'transaction.'], function () {
                Route::get('/', 'TransactionController@getList')->name('getList');;
                Route::get('status', 'TransactionController@getStatusList')->name('getStatus');
                Route::get('{transaction_code}', 'TransactionController@getDetail')->name('getDetail');
                Route::get('{transaction_code}/history', 'TransactionController@getHistory')->name('getHistory');
                // Route::post('{transaction_code}','TransactionController@changeStatus');
                Route::post('{transaction_code}/evidance', 'TransactionController@uploadEvidence')->name('uploadEvidence');
                Route::get('{transaction_code}/invoice', 'TransactionController@invoice')->name('invoice');
                Route::get('{transaction_code}/complete', 'TransactionController@complete')->name('complete');
            });

            Route::group(['prefix' => 'voucher', 'as' => 'voucher.'], function () {
                Route::post('/', 'VoucherController@getData')->name('getList');
            });

            Route::group(['prefix' => 'review', 'as' => 'review.'], function () {
                Route::get('/{id?}', 'ReviewController@getList');
                Route::post('create', 'ReviewController@create');
                Route::post('{id}/update', 'ReviewController@update');
                Route::delete('{id}/delete', 'ReviewController@delete');
            });

            Route::group(['prefix' => 'complaint', 'as' => 'complaint.'], function () {
                Route::post('/', 'ComplaintController@create');
                Route::get('status', 'ComplaintController@getStatusList')->name('getStatus');
                Route::get('/{id?}', 'ComplaintController@getList');

                Route::post('{id}/update', 'ComplaintController@update');
            });

            Route::group(['prefix' => 'courier', 'as' => 'courier.'], function () {
                Route::post('/', 'CourierController@getList')->name('getList');
            });
        });

        Route::group(['prefix' => 'bank', 'as' => 'bank.'], function () {
            Route::get('/', 'BankController@getList')->name('getList');
        });

        // TESTING HARUS DI DELETE PAS PROD
        // Route::get('test_payment/{action}', 'TransactionController@testing');
    });

    Route::group(['namespace' => 'Dashboard', 'prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
        Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
            Route::post('login', 'AuthController@login')->name('login')->middleware(['permission']);
            // Route::get('verification','AuthController@verification');
            //
            Route::get('logout', 'AuthController@logout')->name('logout')->middleware('auth:api');
            Route::get('profile', 'AuthController@profile')->name('profile')->middleware('auth:api');
        });

        Route::group(['middleware' => ['auth:api']], function () {

            Route::group(['prefix' => 'category', 'as' => 'category.'], function () {
                Route::get('/{id?}', 'CategoryController@getData')->name('getData')->middleware(['scope:dashboard.category.getData']);
                Route::post('create', 'CategoryController@create')->name('create')->middleware(['scopes:dashboard.category.create']);
                Route::post('{id}/update', 'CategoryController@create')->name('update')->middleware(['scopes:dashboard.category.update']);
                Route::delete('{id}/delete', 'CategoryController@delete')->name('delete')->middleware(['scopes:dashboard.category.delete']);
            });

            Route::group(['prefix' => 'tag', 'as' => 'tag.'], function () {
                Route::get('/{id?}', 'TagController@getData')->name('getData');
                // ->middleware(['scope:dashboard.tag.getData']);
                // Route::post('create', 'TagController@create')->name('create');
                // ->middleware(['scopes:dashboard.tag.create']);
                // Route::post('{id}/update', 'TagController@create')->name('update');
                // ->middleware(['scopes:dashboard.tag.update']);
                Route::delete('{id}/delete', 'TagController@delete')->name('delete');
                // ->middleware(['scopes:dashboard.tag.delete']);
            });

            Route::group(['prefix' => 'catalog', 'as' => 'catalog.'], function () {
                Route::get('/{id?}', 'CatalogController@getData')->name('getData')->middleware(['scope:dashboard.catalog.getData']);
                Route::get('/{id}/review', 'CatalogController@getReview')->name('getReview')->middleware(['scope:dashboard.catalog.getReview']);
                Route::post('create', 'CatalogController@create')->name('create')->middleware(['scope:dashboard.catalog.create']);
                Route::post('{id}/update', 'CatalogController@update')->name('update')->middleware(['scope:dashboard.catalog.update']);
                Route::delete('{id}/delete', 'CatalogController@delete')->name('delete')->middleware(['scope:dashboard.catalog.delete']);

                Route::post('change_status', 'CatalogController@changeStatusProduct')->name('changeStatus');
                // ->middleware(['scope:dashboard.catalog.changeStatus']);
                Route::post('type/change_status', 'CatalogController@changeStatusProductType')->name('changeStatusType');
                // ->middleware(['scope:dashboard.catalog.changeStatusType']);
            });

            Route::group(['prefix' => 'store', 'as' => 'store.'], function () {
                Route::get('/{id?}', 'StoreController@getData')->name('getData')->middleware(['scope:dashboard.store.getData']);
                Route::post('create', 'StoreController@create')->name('create')->middleware(['scope:dashboard.store.create']);
                Route::post('update', 'StoreController@update')->name('update')->middleware(['scope:dashboard.store.update']);
                Route::delete('{id}/delete', 'StoreController@delete')->name('delete')->middleware(['scope:dashboard.store.delete']);
            });

            Route::group(['prefix' => 'blog', 'as' => 'blog.'], function () {
                Route::group(['prefix' => 'post', 'as' => 'post.'], function () {
                    Route::get('/{id?}', 'BlogController@getData')->name('getData')->middleware(['scope:dashboard.blog.post.getData']);
                    Route::post('create', 'BlogController@create')->name('create')->middleware(['scope:dashboard.blog.post.create']);
                    Route::post('{id}/update', 'BlogController@update')->name('update')->middleware(['scope:dashboard.blog.post.update']);
                    Route::delete('{id}/delete', 'BlogController@delete')->name('delete')->middleware(['scope:dashboard.blog.post.delete']);
                    Route::post('change_status', 'BlogController@changeStatus')->name('changeStatus')->middleware(['scope:dashboard.blog.post.changeStatus']);
                });

                Route::group(['prefix' => '{attribute}', 'as' => 'attribute.'], function () {
                    Route::get('/{id?}', 'BlogAttributeController@index')->name('getData')->middleware(['scope:dashboard.blog.attribute.getData']);
                    Route::post('create', 'BlogAttributeController@create')->name('create')->middleware(['scope:dashboard.blog.attribute.create']);
                    Route::post('{id}/update', 'BlogAttributeController@create')->name('update')->middleware(['scope:dashboard.blog.attribute.update']);
                    Route::delete('{id}/delete', 'BlogAttributeController@delete')->name('delete')->middleware(['scope:dashboard.blog.attribute.delete']);
                });
            });

            Route::group(['prefix' => 'bank', 'as' => 'bank.'], function () {
                Route::get('/{id?}', 'BankController@index')->name('getData')->middleware(['scope:dashboard.bank.getData']);
                Route::post('create', 'BankController@create')->name('create')->middleware(['scope:dashboard.bank.create']);
                Route::post('{id}/update', 'BankController@update')->name('update')->middleware(['scope:dashboard.bank.update']);
                Route::delete('{id}/delete', 'BankController@delete')->name('delete')->middleware(['scope:dashboard.bank.delete']);
            });

            Route::group(['prefix' => 'courier', 'as' => 'courier.'], function () {
                Route::get('/{id?}', 'CourierController@index')->name('getData')->middleware(['scope:dashboard.courier.getData']);
                Route::post('create', 'CourierController@create')->name('create')->middleware(['scope:dashboard.courier.create']);
                Route::post('{id}/update', 'CourierController@update')->name('update')->middleware(['scope:dashboard.courier.update']);
                Route::delete('{id}/delete', 'CourierController@delete')->name('delete')->middleware(['scope:dashboard.courier.delete']);
            });

            Route::group(['prefix' => 'transaction', 'as' => 'transaction.'], function () {
                Route::get('/{id?}', 'TransactionController@getData')->name('getData');
                Route::post('{status}/{id}', 'TransactionController@changeStatus')->name('changeStatus');
                // ->middleware(['scope:dashboard.transaction.getData']);
                // Route::post('create', 'TransactionStatusController@create')->name('create');
                // ->middleware(['scope:dashboard.transaction.create']);
                // Route::post('{id}/update', 'TransactionStatusController@update')->name('update');
                // ->middleware(['scope:dashboard.transaction.update']);
                // Route::delete('{id}/delete', 'TransactionStatusController@delete')->name('delete');
                // ->middleware(['scope:dashboard.transaction.delete']);

                Route::group(['prefix' => 'status', 'as' => 'status.'], function () {
                    Route::get('/{id?}', 'TransactionStatusController@getData')->name('getData')->middleware(['scope:dashboard.transaction.status.getData']);
                    Route::post('create', 'TransactionStatusController@create')->name('create')->middleware(['scope:dashboard.transaction.status.create']);
                    Route::post('{id}/update', 'TransactionStatusController@update')->name('update')->middleware(['scope:dashboard.transaction.status.update']);
                    Route::delete('{id}/delete', 'TransactionStatusController@delete')->name('delete')->middleware(['scope:dashboard.transaction.status.delete']);
                });
            });

            Route::group(['prefix' => 'discount', 'as' => 'discount.'], function () {
                Route::get('{id?}', 'DiscountController@getData')->name('getData');
                // ->middleware(['scope:dashboard.discount.getData']);
                Route::post('create', 'DiscountController@create')->name('create');
                // ->middleware(['scope:dashboard.discount.create']);
                Route::post('{id}/update', 'DiscountController@update')->name('update');
                // ->middleware(['scope:dashboard.discount.update']);
                Route::delete('{id}/delete', 'DiscountController@delete')->name('delete');
                // ->middleware(['scope:dashboard.discount.delete']);
                // Route::post('{id}/copy', 'DiscountController@copy')->name('copy')->middleware(['scope:dashboard.discount.copy']);
                Route::post('change_status', 'DiscountController@changeStatus')->name('changeStatus');
                // ->middleware(['scope:dashboard.discount.changeStatus']);
            });

            // Route::group(['prefix' => 'administration','as' => 'administration.'], function () {
            Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
                Route::get('{id?}', 'AdministratorController@getData')->name('getData');
                // ->middleware(['scope:dashboard.admin.getData']);
                Route::post('create', 'AdministratorController@create')->name('create');
                // ->middleware(['scope:dashboard.admin.create']);
                Route::post('{id}/update', 'AdministratorController@update')->name('update');
                // ->middleware(['scope:dashboard.admin.update']);
                Route::delete('{id}/delete', 'AdministratorController@delete')->name('delete');
                // ->middleware(['scope:dashboard.admin.delete']);
                Route::post('reset_password', 'AdministratorController@resetPassword')->name('resetPassword');
                // ->middleware(['scope:dashboard.admin.resetPassword']);
            });

            Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
                Route::get('{id?}', 'CustomerController@getData')->name('getData');
                // ->middleware(['scope:dashboard.customer.getData']);
                Route::post('reset_password', 'CustomerController@resetPassword')->name('resetPassword');
                // ->middleware(['scope:dashboard.customer.resetPassword']);
                Route::get('verification/{id}', 'CustomerController@verification')->name('verification');
                Route::post('change_status', 'CustomerController@changeStatus')->name('changeStatus');
            });

            Route::group(['prefix' => 'role', 'as' => 'role.'], function () {
                Route::get('{id?}', 'RoleController@getData')->name('getData')->middleware(['scope:dashboard.role.getData']);
                Route::post('create', 'RoleController@create')->name('create')->middleware(['scope:dashboard.role.create']);
                Route::post('{id}/update', 'RoleController@update')->name('update')->middleware(['scope:dashboard.role.update']);
                Route::delete('{id}/delete', 'RoleController@delete')->name('delete')->middleware(['scope:dashboard.role.delete']);
            });

            Route::group(['prefix' => 'permission', 'as' => 'permission.'], function () {
                Route::get('{id?}', 'PermissionController@getData')->name('getData')->middleware(['scope:dashboard.permission.getData']);
                Route::post('create', 'PermissionController@create')->name('create')->middleware(['scope:dashboard.permission.create']);
                // Route::post('{id}/update', 'PermissionController@update')->name('update')->middleware(['scope:dashboard.permission.update']);
                // Route::delete('{id}/delete', 'PermissionController@delete')->name('delete')->middleware(['scope:dashboard.permission.delete']);
            });

            // });
            Route::group(['prefix' => 'voucher', 'as' => 'voucher.'], function () {
                Route::post('change_status', 'VoucherController@changeStatus')->name('changeStatus');
                Route::get('{id?}', 'VoucherController@getData')->name('getData');
                // ->middleware(['scope:dashboard.admin.getData']);
                Route::post('create', 'VoucherController@create')->name('create');
                // ->middleware(['scope:dashboard.admin.create']);
                Route::post('{id}/update', 'VoucherController@update')->name('update');
                // ->middleware(['scope:dashboard.admin.update']);
                Route::delete('{id}/delete', 'VoucherController@delete')->name('delete');
                // ->middleware(['scope:dashboard.admin.delete']);
            });

            Route::group(['prefix' => 'comment', 'as' => 'comment.'], function () {
                // Route::get('notification', 'CommentController@notification')->name('notification');
                Route::get('product/{id?}', 'CommentController@getProductCommentList')->name('getProductData');
                Route::get('/{id?}', 'CommentController@getComment')->name('getData');
                // Route::get('{id}/update','CommentController@getProductCommentList')->name('');
                Route::post('{id}/reply', 'CommentController@reply')->name('create');
                Route::delete('{id}/delete', 'CommentController@delete')->name('delete');
            });

            Route::group(['prefix' => 'banner', 'as' => 'banner.'], function () {
                Route::get('{id?}', 'BannerController@getData')->name('getData');
                // ->middleware(['scope:dashboard.admin.getData']);
                Route::post('create', 'BannerController@create')->name('create');
                // ->middleware(['scope:dashboard.admin.create']);
                Route::post('{id}/update', 'BannerController@update')->name('update');
                // ->middleware(['scope:dashboard.admin.update']);
                Route::delete('{id}/delete', 'BannerController@delete')->name('delete');
                // ->middleware(['scope:dashboard.admin.delete']);
            });

            Route::group(['prefix' => 'promo', 'as' => 'promo.'], function () {
                Route::get('{id?}', 'PromoController@getData')->name('getData');
                Route::post('create', 'PromoController@create')->name('create');
                Route::post('{id}/update', 'PromoController@update')->name('update');
                Route::delete('{id}/delete', 'PromoController@delete')->name('delete');
            });

            Route::group(['prefix' => 'notification', 'as' => 'notification.'], function () {
                Route::get('product', 'NotificationController@zeroStock');
                Route::get('comment', 'NotificationController@getNewComment');
                Route::get('review', 'NotificationController@getNewReview');
                Route::get('transaction', 'NotificationController@getNewTransaction');
            });

            Route::group(['prefix' => 'complaint', 'as' => 'complaint.'], function () {
                Route::get('/{id?}', 'ComplaintController@getList');
                Route::post('{id}/update', 'ComplaintController@update');
            });
        });
    });
});
