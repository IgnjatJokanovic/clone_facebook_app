<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\Friends;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $fields = request()->all();

        $validator = Validator::make($fields, [
            'firstName' => 'required|string|max:255',
            'lastName' =>  'required|string|max:255',
            'birthday' =>  'required|date',
            'email' => 'required|email|unique:users',
            'password' => 'required|alpha_num',
        ]);

        if($validator->fails())
        {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $birthday = Carbon::parse($fields['birthday']);
        $password = bcrypt($fields['password']);

        $fields['birthday'] = $birthday;
        $fields['password'] = $password;


        User::create($fields);

        return response()->json("Thank you for registering, activation link has been sent to your email", 201);


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::with('acceptedFriends')
                    ->where('id', $id)
                    ->first();

        if($user === null){
            return response()->json('User not found', 404);
        }

        $userId = null;
        try{
            $payload = JWTAuth::parseToken()->getPayload();
            $userId = $payload->get('id');
        }catch(Exception $e){
        }

        $user->isFriends = null;

        if((int)$userId !== (int)$id && $userId !== null){
            // Load is friend relationship
            Log::debug("usoi, $userId, $id");
            $user->isFriends = Friend::where(function ($q) use ($id, $userId){
                                    $q->where('to', $id)
                                    ->orWhere('from', $userId);
                                })
                                ->orWhere(function($q) use ($id, $userId){
                                    $q->where('to', $userId)
                                    ->orWhere('from', $id);
                                })->first();

        }

        return response()->json($user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
