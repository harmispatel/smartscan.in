<?php

use App\Http\Controllers\{AdminSettingsController,AuthController,BillingInfoController,CategoryController,DashboardController,ContactController, CustomerVisits, DesignController,EveryPayController, FoodJunctionController, FrontendController, ImportExportController,IngredientController,ItemsController, ItemsReviewsController, LanguageController,LanguagesController,OptionController,OrderController,PaymentController,PaypalController,PreviewController, RoomsController, ShopBannerController,ShopController,ShopQrController, ShopTablesController, StatisticsController,SubscriptionsController,TagsController,ThemeController,TutorialController,UserController};
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Magarrent\LaravelCurrencyFormatter\Facades\Currency;

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

Route::get('config-clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    dd("Cache is cleared");
});



// Route::get('/', function ()
// {
//     dd(1);
// })->name('home');
// Frontend
Route::get('/', [FrontendController::class,'index'])->name('home');
Route::get('/contact-us', [FrontendController::class,'contactUS'])->name('contact.us');
Route::post('/contact-us-mail', [FrontendController::class,'contactUSMail'])->name('contact.us.mail');
Route::get('/qr-guide', [FrontendController::class,'QrGuide'])->name('qr.guide');
Route::get('/qr-menu', [FrontendController::class,'QrMenu'])->name('qr.menu');


// Auth Routes
Route::get('/login', [AuthController::class,'showLogin'])->name('login');
Route::post('/login', [AuthController::class,'login'])->name('doLogin');

// Logout
Route::get('/logout', [AuthController::class,'logout'])->name('logout');


Route::group(['prefix' => 'admin'], function ()
{
    // If Auth Login
    Route::group(['middleware' => ['auth','is_admin']], function ()
    {
        // Admin Dashboard
        Route::get('dashboard', [DashboardController::class,'index'])->name('admin.dashboard');

        // Admins
        Route::get('/admins',[UserController::class,'AdminUsers'])->name('admins');
        Route::get('/new-admins',[UserController::class,'NewAdminUser'])->name('admins.add');
        Route::post('/store-admins',[UserController::class,'storeNewAdmin'])->name('admins.store');
        Route::get('/delete-admins/{id}',[UserController::class,'destroyAdminUser'])->name('admins.destroy');
        Route::get('/edit-admins/{id}',[UserController::class,'editAdmin'])->name('admins.edit');
        Route::post('/update-admins',[UserController::class,'updateAdmin'])->name('admins.update');

        // Clients
        Route::get('/clients',[UserController::class,'index'])->name('clients');
        Route::get('/list-clients/{id?}',[UserController::class,'clientsList'])->name('clients.list');
        Route::get('/new-clients',[UserController::class,'insert'])->name('clients.add');
        Route::post('/store-clients',[UserController::class,'store'])->name('clients.store');
        Route::post('/status-clients',[UserController::class,'changeStatus'])->name('clients.status');
        Route::post('/status-fav-clients',[UserController::class,'addToFavClients'])->name('clients.addtofav');
        Route::post('/delete-clients',[UserController::class,'destroy'])->name('clients.destroy');
        Route::get('/edit-clients/{id}',[UserController::class,'edit'])->name('clients.edit');
        Route::get('/access-clients/{id}',[UserController::class,'clientAccess'])->name('clients.access');
        Route::post('/update-clients',[UserController::class,'update'])->name('clients.update');
        Route::post('/delete-clients-data',[UserController::class,'deleteClientsData'])->name('client.delete.data');
        Route::post('/delete-clients-orders',[UserController::class,'deleteClientsOrders'])->name('client.delete.orders');

        // Subscription
        Route::get('/subscriptions',[SubscriptionsController::class,'index'])->name('subscriptions');
        Route::get('/new-subscription',[SubscriptionsController::class,'insert'])->name('subscriptions.add');
        Route::post('/store-subscription',[SubscriptionsController::class,'store'])->name('subscriptions.store');
        Route::post('/delete-subscription',[SubscriptionsController::class,'destroy'])->name('subscriptions.destroy');
        Route::get('/edit-subscription/{id}',[SubscriptionsController::class,'edit'])->name('subscriptions.edit');
        Route::post('/update-subscription',[SubscriptionsController::class,'update'])->name('subscriptions.update');

        // Tutorial
        Route::get('/tutorial',[TutorialController::class,'index'])->name('tutorial');
        Route::get('/new-tutorial',[TutorialController::class,'insert'])->name('tutorial.add');
        Route::post('/store-tutorial',[TutorialController::class,'store'])->name('tutorial.store');
        Route::get('/delete-tutorial/{id}',[TutorialController::class,'destroy'])->name('tutorial.destroy');
        Route::get('/edit-tutorial/{id}',[TutorialController::class,'edit'])->name('tutorial.edit');
        Route::post('/update-tutorial',[TutorialController::class,'update'])->name('tutorial.update');

        // Ingredients
        Route::get('/ingredients',[IngredientController::class,'index'])->name('ingredients');
        Route::get('/new-ingredients',[IngredientController::class,'insert'])->name('ingredients.add');
        Route::post('/store-ingredients',[IngredientController::class,'store'])->name('ingredients.store');
        Route::get('/delete-ingredients/{id}',[IngredientController::class,'destroy'])->name('ingredients.destroy');
        Route::get('/edit-ingredients/{id}',[IngredientController::class,'edit'])->name('ingredients.edit');
        Route::post('/update-ingredients',[IngredientController::class,'update'])->name('ingredients.update');
        Route::post('/status-ingredients',[IngredientController::class,'changeStatus'])->name('ingredients.status');

        // AdminProfile
        Route::get('/my-profile/{id}',[UserController::class,'myProfile'])->name('admin.profile.view');
        Route::get('/edit-profile/{id}',[UserController::class,'editProfile'])->name('admin.profile.edit');
        Route::post('/update-profile',[UserController::class,'updateProfile'])->name('admin.profile.update');


        // Admin Settings
        Route::get('/settings',[AdminSettingsController::class,'index'])->name('admin.settings');
        Route::post('/settings-update',[AdminSettingsController::class,'update'])->name('update.admin.settings');


        // Languages
        Route::post('/save-language',[LanguagesController::class,'saveAjax'])->name('languages.save.ajax');

        // Import & Export
        Route::get('/import-export', [ImportExportController::class,'index'])->name('admin.import.export');
        Route::post('/import-data', [ImportExportController::class,'importData'])->name('admin.import.data');
        Route::post('/export-data', [ImportExportController::class,'exportData'])->name('admin.export.data');

        // Food Junctions
        Route::get('/food-junctions',[FoodJunctionController::class,'index'])->name('food.junctions');
        Route::get('/food-junctions-create',[FoodJunctionController::class,'create'])->name('food.junctions.create');
        Route::post('/food-junctions-store',[FoodJunctionController::class,'store'])->name('food.junctions.store');
        Route::post('/food-junctions-status',[FoodJunctionController::class,'changeStatus'])->name('food.junctions.status');
        Route::get('/food-junctions-edit/{id}',[FoodJunctionController::class,'edit'])->name('food.junctions.edit');
        Route::post('/food-junctions-update',[FoodJunctionController::class,'update'])->name('food.junctions.update');
        Route::get('/food-junctions-destroy/{id}',[FoodJunctionController::class,'destroy'])->name('food.junctions.destroy');

    });

});


Route::group(['prefix' => 'client'], function()
{
    // If Auth Login
    Route::group(['middleware' => ['auth','is_client']], function ()
    {
        // Client Dashboard
        Route::get('dashboard', [DashboardController::class,'clientDashboard'])->name('client.dashboard');

        // Categories
        Route::get('categories/{cat_id?}',[CategoryController::class,'index'])->name('categories');
        Route::post('store-categories',[CategoryController::class,'store'])->name('categories.store');
        Route::post('delete-categories',[CategoryController::class,'destroy'])->name('categories.delete');
        Route::post('edit-categories',[CategoryController::class,'edit'])->name('categories.edit');
        Route::post('update-categories',[CategoryController::class,'update'])->name('categories.update');
        Route::post('update-categories-by-lang',[CategoryController::class,'updateByLangCode'])->name('categories.update.by.lang');
        Route::post('status-categories',[CategoryController::class,'status'])->name('categories.status');
        Route::post('search-categories',[CategoryController::class,'searchCategories'])->name('categories.search');
        Route::post('sorting-categories',[CategoryController::class,'sorting'])->name('categories.sorting');
        Route::post('delete-categories-images',[CategoryController::class,'deleteCategoryImages'])->name('categories.delete.images');
        Route::get('delete-categories-image/{id}',[CategoryController::class,'deleteCategoryImage'])->name('categories.delete.image');

        // Items
        Route::get('items/{id?}',[ItemsController::class,'index'])->name('items');
        Route::post('store-items',[ItemsController::class,'store'])->name('items.store');
        Route::post('delete-items',[ItemsController::class,'destroy'])->name('items.delete');
        Route::post('status-items',[ItemsController::class,'status'])->name('items.status');
        Route::post('search-items',[ItemsController::class,'searchItems'])->name('items.search');
        Route::post('edit-items',[ItemsController::class,'edit'])->name('items.edit');
        Route::post('update-items',[ItemsController::class,'update'])->name('items.update');
        Route::post('update-items-by-lang',[ItemsController::class,'updateByLangCode'])->name('items.update.by.lang');
        Route::post('sorting-items',[ItemsController::class,'sorting'])->name('items.sorting');
        Route::post('delete-price-items',[ItemsController::class,'deleteItemPrice'])->name('items.delete.price');
        Route::get('delete-items-image/{id}',[ItemsController::class,'deleteItemImage'])->name('items.delete.image');

        // Options
        Route::get('options',[OptionController::class,'index'])->name('options');
        Route::post('store-options',[OptionController::class,'store'])->name('options.store');
        Route::post('delete-options',[OptionController::class,'destroy'])->name('options.delete');
        Route::post('edit-options',[OptionController::class,'edit'])->name('options.edit');
        Route::post('update-options-by-lang',[OptionController::class,'updateByLangCode'])->name('options.update-by-lang');
        Route::post('update-options',[OptionController::class,'update'])->name('options.update');
        Route::post('delete-price-options',[OptionController::class,'deleteOptionPrice'])->name('options.price.delete');

        // Designs
        Route::get('/design-logo', [DesignController::class,'logo'])->name('design.logo');
        Route::post('/design-logo-upload', [DesignController::class,'logoUpload'])->name('design.logo.upload');
        Route::get('/design-logo-delete', [DesignController::class,'deleteLogo'])->name('design.logo.delete');

        Route::post('/design-intro-status', [DesignController::class,'introStatus'])->name('design.intro.status');
        Route::post('/design-intro-icon', [DesignController::class,'introIconUpload'])->name('design.intro.icon');
        Route::post('/design-intro-duration', [DesignController::class,'introDuration'])->name('design.intro.duration');

        Route::get('/design-cover', [DesignController::class,'cover'])->name('design.cover');
        Route::get('/design-cover-delete', [DesignController::class,'deleteCover'])->name('design.cover.delete');

        Route::get('/banners', [ShopBannerController::class,'index'])->name('banners');
        Route::post('/banners-store', [ShopBannerController::class,'store'])->name('banners.store');
        Route::post('/banners-delete', [ShopBannerController::class,'destroy'])->name('banners.delete');
        Route::post('/banners-edit', [ShopBannerController::class,'edit'])->name('banners.edit');
        Route::post('/banners-update', [ShopBannerController::class,'update'])->name('banners.update');
        Route::post('/banners-image-delete', [ShopBannerController::class,'deleteBanner'])->name('banners.delete.image');
        Route::post('update-banners-by-lang',[ShopBannerController::class,'updateByLangCode'])->name('banners.update-by-lang');

        Route::get('/design-general-info', [DesignController::class,'generalInfo'])->name('design.general-info');
        Route::get('/design-mail-forms', [DesignController::class,'MailForms'])->name('design.mail.forms');
        Route::post('/design-generalInfoUpdate', [DesignController::class,'generalInfoUpdate'])->name('design.generalInfoUpdate');
        Route::post('/design-mailFormUpdate', [DesignController::class,'mailFormUpdate'])->name('design.mailFormUpdate');

        // Billing Infor
        Route::get('billing-info',[BillingInfoController::class, 'billingInfo'])->name('billing.info');
        Route::get('my-subscription',[BillingInfoController::class, 'clientSubscription'])->name('client.subscription');
        Route::get('edit-billing-info',[BillingInfoController::class, 'editBillingInfo'])->name('billing.info.edit');
        Route::post('billing-info-update',[BillingInfoController::class, 'updateBillingInfo'])->name('update.billing.info');

        // Languages
        Route::get('/languages', [LanguageController::class,'index'])->name('languages');
        Route::post('/language-set-primary', [LanguageController::class,'setPrimaryLanguage'])->name('language.set-primary');
        Route::post('/language-set-additional', [LanguageController::class,'setAdditionalLanguages'])->name('language.set-additional');
        Route::post('/language-delete-additional', [LanguageController::class,'deleteAdditionalLanguage'])->name('language.delete-additional');
        Route::post('/language-change-status', [LanguageController::class,'changeLanguageStatus'])->name('language.changeStatus');
        Route::post('/language-categorydetails', [LanguageController::class,'getCategoryDetails'])->name('language.categorydetails');
        Route::post('/language-update-catdetails', [LanguageController::class,'updateCategoryDetails'])->name('language.update-category-details');
        Route::post('/language-itemdetails', [LanguageController::class,'getItemDetails'])->name('language.itemdetails');
        Route::post('/language-update-itemdetails', [LanguageController::class,'updateItemDetails'])->name('language.update-item-details');
        Route::post('/language-google-translate', [LanguageController::class,'setGoogleTranslate'])->name('language.google.translate');


        // Shop QrCode
        Route::get('/qrcode', [ShopQrController::class,'index'])->name('qrcode');
        Route::post('/qrcode-settings', [ShopQrController::class,'QrCodeSettings'])->name('qrcode.settings');
        Route::post('/qrcode-update-settings', [ShopQrController::class,'QrCodeUpdateSettings'])->name('qrcode.update.settings');

        // ClientProfile
        Route::get('/my-profile/{id}',[UserController::class,'myProfile'])->name('client.profile.view');
        Route::get('/edit-profile/{id}',[UserController::class,'editProfile'])->name('client.profile.edit');
        Route::post('/update-profile',[UserController::class,'updateProfile'])->name('client.profile.update');
        Route::get('/delete-profile-picture',[UserController::class,'deleteProfilePicture'])->name('client.delete.profile.picture');

        // Delete Shop Logo
        Route::get('delete-shop-logo',[ShopController::class, 'deleteShopLogo'])->name('shop.delete.logo');

        // Tags
        Route::get('tags',[TagsController::class,'index'])->name('tags');
        Route::post('store-tags',[TagsController::class,'store'])->name('tags.store');
        Route::post('delete-tags',[TagsController::class,'destroy'])->name('tags.destroy');
        Route::post('edit-tags',[TagsController::class,'edit'])->name('tags.edit');
        Route::post('update-tags',[TagsController::class,'update'])->name('tags.update');
        Route::post('sorting-tags',[TagsController::class,'sorting'])->name('tags.sorting');
        Route::post('update-tags-by-lang',[TagsController::class,'updateByLangCode'])->name('tags.update-by-lang');

        // Preview
        Route::get('/preview',[PreviewController::class,'index'])->name('preview');

        // Tutorial
        Route::get('/tutorial',[TutorialController::class,'show'])->name('tutorial.show');


        // Statistic
        Route::get('/statistics/{key?}',[StatisticsController::class,'index'])->name('statistics');

        // contact us
        Route::get('/contact',[ContactController::class,'index'])->name('contact');
        Route::post('/contact-send',[ContactController::class,'send'])->name('contact.send');

        // Themes
        Route::get('/design-theme', [ThemeController::class,'index'])->name('design.theme');
        Route::get('/design-theme-preview/{id}', [ThemeController::class,'themePrview'])->name('design.theme-preview');
        Route::get('/design-create-theme', [ThemeController::class,'create'])->name('design.theme-create');
        Route::post('/design-store-theme', [ThemeController::class,'store'])->name('design.theme-store');
        Route::post('/design-update-theme', [ThemeController::class,'update'])->name('design.theme-update');
        Route::post('/change-theme', [ThemeController::class,'changeTheme'])->name('theme.change');
        Route::get('/delete-theme/{id}', [ThemeController::class,'destroy'])->name('theme.delete');
        Route::get('/clone-theme/{id}', [ThemeController::class,'cloneView'])->name('theme.clone');


        // Orders
        Route::get('/orders-settings',[OrderController::class,'OrderSettings'])->name('order.settings');
        Route::post('/orders-settings-update',[OrderController::class,'UpdateOrderSettings'])->name('update.order.settings');
        Route::get('/orders',[OrderController::class,'index'])->name('client.orders');
        Route::match(['get','post'],'orders-history',[OrderController::class,'ordersHistory'])->name('client.orders.history');
        Route::post('/orders-change-estimate',[OrderController::class,'changeOrderEstimate'])->name('change.order.estimate');
        Route::post('/accept-order',[OrderController::class,'acceptOrder'])->name('accept.order');
        Route::post('/reject-order',[OrderController::class,'rejectOrder'])->name('reject.order');
        Route::post('/finalized-order',[OrderController::class,'finalizedOrder'])->name('finalized.order');
        Route::get('/order-view/{id}',[OrderController::class,'viewOrder'])->name('view.order');
        Route::get('/clear-delivey-range',[OrderController::class,'clearDeliveryRangeSettings'])->name('remove.delivery.range');
        Route::post('/get-order-receipt',[OrderController::class,'getOrderReceipt'])->name('order.receipt');
        Route::post('/order-notification',[OrderController::class,'orderNotification'])->name('order.notification');
        Route::get('/new-orders',[OrderController::class,'getNewOrders'])->name('new.orders');
        Route::get('/jspm',[OrderController::class,'setPrinterLicense'])->name('jspm');

        // Special Icons
        Route::get('/special-icons',[IngredientController::class,'specialIcons'])->name('special.icons');
        Route::get('/new-special-icons',[IngredientController::class,'insertSpecialIcons'])->name('special.icons.add');
        Route::post('/store-special-icons',[IngredientController::class,'storeSpecialIcons'])->name('special.icons.store');
        Route::get('/delete-special-icons/{id}',[IngredientController::class,'destroySpecialIcons'])->name('special.icons.destroy');
        Route::get('/edit-special-icons/{id}',[IngredientController::class,'editSpecialIcons'])->name('special.icons.edit');
        Route::post('/update-special-icons',[IngredientController::class,'updateSpecialIcons'])->name('special.icons.update');
        Route::post('/status-special-icons',[IngredientController::class,'changeStatus'])->name('special.icons.status');

        // Payment
        Route::get('/payment-settings',[PaymentController::class,'paymentSettings'])->name('payment.settings');
        Route::post('/payment-settings-update',[PaymentController::class,'UpdatePaymentSettings'])->name('update.payment.settings');

        // Item Reviews
        Route::get('/items-reviews',[ItemsReviewsController::class,'index'])->name('items.reviews');
        Route::post('/items-reviews-destroy',[ItemsReviewsController::class,'destroy'])->name('items.reviews.destroy');

        Route::post('/verify/client/password',[UserController::class,'verifyClientPassword'])->name('verify.client.password');

        // Tables
        Route::get('/shop-tables',[ShopTablesController::class,'index'])->name('shop.tables');
        Route::get('/shop-tables/create',[ShopTablesController::class,'create'])->name('shop.tables.create');
        Route::post('/shop-tables/store',[ShopTablesController::class,'store'])->name('shop.tables.store');
        Route::post('/shop-tables/status',[ShopTablesController::class,'changeStatus'])->name('shop.tables.status');
        Route::post('/shop-tables/destroy',[ShopTablesController::class,'destroy'])->name('shop.tables.destroy');
        Route::get('/shop-tables/printqr',[ShopTablesController::class,'printTablesQR'])->name('shop.tables.printqr');

        // Rooms
        Route::get('/rooms',[RoomsController::class,'index'])->name('rooms');
        Route::get('/rooms/create',[RoomsController::class,'create'])->name('rooms.create');
        Route::post('/rooms/store',[RoomsController::class,'store'])->name('rooms.store');
        Route::post('/rooms/status',[RoomsController::class,'changeStatus'])->name('rooms.status');
        Route::post('/rooms/destroy',[RoomsController::class,'destroy'])->name('rooms.destroy');
        Route::get('/rooms/printqr',[RoomsController::class,'printRoomsQR'])->name('rooms.printqr');

        // CustomersVisit
        Route::match(['get','post'],'customer-visits',[CustomerVisits::class,'index'])->name('customers.visit');

    });
});

Route::get('/jspm',[OrderController::class,'setPrinterLicense'])->name('jspm');

// Get Total with currency
Route::post('total-with-currency',function(Request $request)
{
    try
    {
        $total = Currency::currency($request->currency)->format($request->total);
        return response()->json([
            'success' => 1,
            'total' => $total,
        ]);
    }
    catch (\Throwable $th)
    {
        return response()->json([
            'success' => 0,
            'message' => "Internal Server Error!",
        ]);
    }

})->name('total.with.currency');

// Shops
Route::get('junction/{junction_slug}',[ShopController::class,'foodJunction'])->name('junction');
Route::get('/{shop_slug}/table/{table_no}',[ShopController::class,'shopTable'])->name('shop-table');
Route::get('/{shop_slug}/room/{room_no}',[ShopController::class,'shopRoom'])->name('shop-room');
Route::get('/{shop_slug}/{catID?}',[ShopController::class,'index'])->name('restaurant')->where('shop_slug','[a-zA-Z-]+');
Route::get('{shop_slug}/items/{catID}',[ShopController::class,'itemPreview'])->name('items.preview')->where('shop_slug','[a-zA-Z-]+');
Route::post('shop-locale-change',[ShopController::class,'changeShopLocale'])->name('shop.locale.change');
Route::post('search-shop-categories',[ShopController::class,'searchCategories'])->name('shop.categories.search');
Route::post('search-shop-items',[ShopController::class,'searchItems'])->name('shop.items.search');
Route::post('details-items',[ShopController::class,'getDetails'])->name('items.get.details');
Route::post('do-check-in',[ShopController::class,'checkIn'])->name('do.check.in');
Route::post('shop-add-to-cart',[ShopController::class,'addToCart'])->name('shop.add.to.cart');
Route::post('shop-update-cart',[ShopController::class,'updateCart'])->name('shop.update.cart');
Route::post('shop-remove-cart-item',[ShopController::class,'removeCartItem'])->name('shop.remove.cart.item');
Route::get('{my_shop_slug}/my/cart/',[ShopController::class,'viewCart'])->name('shop.cart');
Route::get('{my_shop_slug}/my/cart/checkout/',[ShopController::class,'cartCheckout'])->name('shop.cart.checkout');
Route::post('{my_shop_slug}/my/cart/checkout/processing/',[ShopController::class,'checkoutProcessing'])->name('shop.cart.processing');
Route::get('{my_shop_slug}/checkout/success/{id}',[ShopController::class,'checkoutSuccess'])->name('shop.checkout.success');
Route::post('set-checkout-type',[ShopController::class,'setCheckoutType'])->name('set.checkout.type');
Route::post('check-order-status',[ShopController::class,'checkOrderStatus'])->name('check.order.status');
Route::post('send-item-review',[ShopController::class,'sendItemReview'])->name('send.item.review');
Route::post('/set-delivery-address',[OrderController::class,'setDeliveryAddress'])->name('set.delivery.address');
Route::post('/save-customer-details',[ShopController::class,'saveCustomerDetails'])->name('save.customer.details');

// Paypal Payment
Route::get('{my_shop_slug}/paypal/payment/',[PaypalController::class,'payWithpaypal'])->name('paypal.payment');
Route::get('{my_shop_slug}/paypal/payment/status',[PaypalController::class,'getPaymentStatus'])->name('paypal.payment.status');
Route::get('{my_shop_slug}/paypal/payment/cancel',[PaypalController::class,'paymentCancel'])->name('paypal.payment.cancel');

// EveryPay Payment
Route::post('{my_shop_slug}/everypay/payment/',[EveryPayController::class,'payWithEveryPay'])->name('everypay.payment');
Route::get('{my_shop_slug}/my/cart/checkout/processing/everypay',[EveryPayController::class,'gotoEveryPayCheckout'])->name('everypay.checkout.view');

// Change Backend Language
Route::post('/change-backend-language', [DashboardController::class, 'changeBackendLanguage'])->name('change.backend.language');
