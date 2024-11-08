<?php

use App\Http\Controllers\api\Admin\StatisticsController;
use App\Http\Controllers\api\Admin\CommitController;
use App\Http\Controllers\api\Admin\TraitsController;
use App\Http\Controllers\api\Admin\UserController as AdminUserController;
use App\Http\Controllers\api\user\HeartConsumeControll;
use App\Http\Controllers\api\user\ProfileController;
use App\Http\Controllers\api\user\RatingController;
use App\Http\Controllers\api\user\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\Admin\ExPartnerController;
use App\Http\Controllers\api\user\ExPartnerController;
use App\Http\Controllers\api\Admin\ExPartnerController as adminExPartnerController;
use App\Http\Controllers\api\AuthController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });




Route::group(['prefix' => 'auth'], function () {

    Route::post('register', [AuthController::class, 'register']);

    Route::post('login', [AuthController::class, 'login']);
    Route::post('sociallogin', [AuthController::class, 'socialLogin']);
    // Route::post('logout', [AuthController::class, 'login']);

    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');


    Route::post('send-otp', [AuthController::class, 'sendOtp']);

    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);

    Route::post('forgot-password', [AuthController::class, 'forgotPassword']); // Forgot Password route
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

});

Route::group(['prefix' => 'user'], function () {

    Route::group(['middleware' => 'auth:api'], function(){

                    Route::controller(UserController::class)->group(function(){
                        Route::get('get-user-profile/{id}', 'userProfile');
                        Route::post('update-user-profile/{id}', 'UpdateUserProfile');
                        Route::post('count-referred-users/{id}', 'countReferredUsers');

                    });

                    Route::controller(ExPartnerController::class)->group(function () {
                        Route::post('ex-partners', 'store');         // Create a new ex-partner record
                        Route::get('ex-partners/{id}', 'show');      // Show a specific ex-partner record
                        Route::post('ex-partners-update/{id}', 'update');    // Update a specific ex-partner record
                        Route::delete('ex-partners/{id}', 'destroy');// Delete a specific ex-partner record
                        Route::get('ex-partners', 'index');          // List all ex-partners for a specific user

                        Route::post('/profiles/search','searchProfiles');


                    });

                    Route::controller(HeartConsumeControll::class)->group(function(){
                        // Route::get('get-day-consume-hearts', 'consumeHeart');

                        Route::post('consume-heart', 'consumeHeart'); // Route to consume a heart and check for streaks
                        // Route::post('earn-heart/referral', 'earnHeartByReferral'); // Route to earn heart by referral
                        Route::post('earn-heart/facebook', 'earnHeartByConnectingFacebook'); // Route to earn heart by connecting Facebook
                        Route::post('earn-heart/profile', 'earnHeartByCompletingProfile');

                    });

                    Route::controller(RatingController::class)->group(function(){
                        Route::post('ratings',  'store'); // Create a new rating
                        // Route::get('ex-partners/{id}/ratings',  'show');
                        // Route::get('get-all-rating-ex-partners',  'getAllRatings');
                        Route::get('get-rating-ex-partners/{id}',  'show');
                    });

                    Route::controller(ProfileController::class)->group(function(){
                        Route::get('/profiles/own-ratings', 'viewOwnRatings');
                        Route::get('/profiles/search', 'searchProfiles');
                        Route::post('/profiles/unlock/{id}', 'unlockProfile');
                        Route::get('/profiles/view/{id}', 'viewProfile');

                    });

                });


});

Route::group(['prefix' => 'admin'], function () {

Route::group(['middleware' => 'auth:api'], function(){


    Route::get('/user-count', [StatisticsController::class, 'getUserCount']);
    Route::get('/ex-partner-count', [StatisticsController::class, 'getExPartnerCount']);
    Route::get('/users/monthly-count', [StatisticsController::class, 'getMonthlyUserCounts']);
    Route::get('/ex-partners/monthly-count', [StatisticsController::class, 'getMonthlyExPartnerCounts']);



    Route::controller(TraitsController::class)->group(function(){
        Route::get('/get-traits', 'index'); // Get all users
        Route::post('/add-traits', 'store'); // Create a new user
        Route::post('/update-traits', 'update'); // Create a new user
        Route::get('/delet-traits/{id}', 'destroy');

    });

    Route::controller(CommitController::class)->group(function(){
        Route::get('/get-all-commits', 'AllCommits')->name('commits.index'); // List commits for an ex-partner
        Route::get('/get-commits/{id}', 'index')->name('commits.index'); // List commits for an ex-partner
        Route::post('/add-commits/{id}', 'store')->name('commits.store'); // Add a new commit to an ex-partner
        Route::put('/update-commit/{id}',  'update')->name('commits.update'); // Update a specific commit
        Route::get('/approve-commit/{id}',  'updateUserStatus'); // Update a specific commit
        Route::delete('/delete-commit/{id}', 'destroy')->name('commits.destroy');

    });

        Route::controller(AdminUserController::class)->group(function () {
        Route::get('/users', 'index'); // Get all users
        Route::get('/users/{id}', 'show'); // Get a specific user
        Route::post('/users', 'store'); // Create a new user
        Route::put('/users/{id}', 'update'); // Update a user
        Route::delete('/users/{id}', 'destroy'); // Delete a user

        Route::get('/approve-user/{id}',  'updateUserStatus');

    });



    Route::controller(adminExPartnerController::class)->group(function () {
        Route::post('ex-partners', 'store');         // Create a new ex-partner record
        Route::get('ex-partners/{id}', 'show');      // Show a specific ex-partner record
        Route::post('ex-partners-update/{id}', 'update');    // Update a specific ex-partner record
        Route::delete('ex-partners/{id}', 'destroy');// Delete a specific ex-partner record
        Route::get('ex-partners', 'index');
        Route::get('/approve-expartner/{id}',  'updatePartnerStatus');        // List all ex-partners for a specific user
    });

    Route::controller(RatingController::class)->group(function(){
        Route::post('ratings',  'store'); // Create a new rating
        // Route::get('ex-partners/{id}/ratings',  'show');
        Route::get('get-rsting-ex-partners/{id}',  'show');
    });

    Route::controller(ProfileController::class)->group(function(){
        Route::get('/profiles/own-ratings', 'viewOwnRatings');
        Route::get('/profiles/search', 'searchProfiles');
        Route::post('/profiles/unlock/{id}', 'unlockProfile');
        Route::get('/profiles/view/{id}', 'viewProfile');

    });


});


});


// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('ex-partners', [ExPartnerController::class, 'store']);
//     Route::get('ex-partners', [ExPartnerController::class, 'index']);
//     Route::put('ex-partners/{id}', [ExPartnerController::class, 'update']);
//     Route::delete('ex-partners/{id}', [ExPartnerController::class, 'destroy']);
// });

