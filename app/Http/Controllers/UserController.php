<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:32',
                'last_name' => 'required|string|max:32',
                'birthdate' => 'required|date',
                'phone' => 'required|unique:users,phone',
                'email' => 'required|unique:users,email',
                'address' => 'nullable|string',
                'password' => 'required|min:6',
                'user_type' => 'required|in:personal,professional',
                'broker_idno' => 'nullable|string|unique:users,broker_idno',
                'fyda_fan' => 'nullable|string|unique:users,fyda_fan',
            ]);

            $user = User::create($validated);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([ 
                'message' => 'User registerd successfully',
                'user' => $user,
                'token' => $token
            ],201);

         } catch (Exception $e) {
            Log::error('User Creation Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to Create the User'],500);
         }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return User::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try{
            $user = User::findOrFail($id);
            if(!$user){
               Log::error($user);
               return response()->json(['message'=>'User Not Found'],404);
            }
            if($request->user()->id !== $user->id){
                return response()->json(['message' => 'Unauthorized'],403);
            }
            $validated = $request->validate([
                'first_name' => 'string|max:32|nullable',
                'last_name' => 'string|max:32|nullable',
                'phone' => 'string|unique:users,phone|nullable',
                'address' => 'nullable|string',
                'broker_idno' => 'nullable|string|unique:users,broker_idno',
            ]);

            $user->update($validated);
            return response()->json(['message'=> 'Profile Updated', 'user' => $user],200);
        }catch (Exception $e){
            Log::error('Failed to Update the User: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to Update the User'],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try{
            $user = User::findOrFail($id);
                if(!$user){
                    return response()->json(['message' => 'User Not Found'],404);
                }elseif($request->user()->id !== $user->id){
                    return response()->json(['message' => 'You are Unauthorized'], 403);
                }else{
                    $deleted = $user->delete();
                }
            if($deleted) {
                return response()->json(['message'=>'User Deleted Successfully'],200);
            }
        }catch(Exception $e){
            Log::error('Unable to delete User: ' .$e->getMessage());
            return response()->json(['message' => 'Unable to delete User'],500);
        }
    }

    // Custom Auth Methods

    public function login(Request $request)
    {
        try{
            $request ->validate([
                'phone' => 'required',
                'password' => 'required',
            ]);

            if(!Auth::attempt($request->only('phone', 'password'))) {
                throw validationException::withMessages([
                    'phone' => ['Invalid phone or password.']
                ]);
            }

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login Successful',
                'user' => $user,
                'token' => $token
            ],200);

      }catch(Exception $e){
        Log::error('Unable to Authenticate'. $e->getMessage());
        return response()->json(['message'=>'User Authentication Failed'],500);
      }
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Logged out']);
    }



}
