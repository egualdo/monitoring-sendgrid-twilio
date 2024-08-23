<?php

use App\Http\Controllers\SendGridController;
use Illuminate\Support\Facades\Route;


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

Route::get('/', function () {
    return view('welcome');
});
// Route::get('/', [SendGridController::class, 'index'])->name('index');
Route::get('/posts/{id}', [SendGridController::class, 'show'])->name('view-post');
Route::post('/posts/receive-email-response', [SendGridController::class, 'receiveEmailResponse'])->name('receiveEmailResponse');
Route::get('/posts/send-email/{id?}', [SendGridController::class, 'sendMails'])->name('sendMails');
