<?php
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function () {
    return response()->json(['message' => 'Hello World']);
});

Route::get('/test-db-connection', function () {
     try {
          DB::connection()->getPdo();
            return "Database connection successful!";
        } catch (\Exception $e) {
            return "Could not connect to the database. Please check your configuration. Error: " . $e->getMessage();
        }
    });

Route::post('/login',[UserController::class, 'login']);
Route::post('/register',[UserController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
