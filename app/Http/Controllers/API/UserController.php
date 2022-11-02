<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'email|required',
                'password'  => 'required'
            ]);

            if($validator->fails()){
                return response()->json($validator->errors(), 400);
            }

            $credentials = request(['email', 'password']);
            if(!Auth::attempt($credentials)){
                return ResponseFormatter::error([
                    'message'   => 'Unauthorized',
                ], 'Authentication Failed', 500);
            }

            $user = User::where('email', $request->email)->first();

            if(!Hash::check($request->password, $user->password, [])){
                throw new \Exception('Invalid Credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user'  => $user
            ], 'Authenticated');

        } catch (\Exception $error) {
            return ResponseFormatter::error([
                'message'   => 'Something went wrong',
                'error' => $error,
            ], 'Authentication Failed', 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),[
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users',
                'handphone' => 'required|numeric|digits_between:9,14',
                'alamat' => 'string',
                'photo' => 'image|mimes:jpg,png,jpeg|max:2048',
                'password' => 'required|string|min:8',
            ]);

            // dd($validator);

            if($validator->fails()){
                return response()->json($validator->errors(), 400);
            }

            if($request->photo == ''){
                $image_path = '';
            } else {
                $file_ext = $request->file('photo')->getClientOriginalExtension();
                $file_name = 'avatar' . "-" . rand(1111, 9999) . "-" . Carbon::now()->format('dmY') . "." . $file_ext;
                $image_path = $request->file('photo')->storeAs('images/photo', $file_name);
            }



            User::create([
                'name'  => $request->name,
                'email' => $request->email,
                'handphone' => $request->handphone,
                'alamat' => $request->alamat,
                'photo' => $image_path,
                'password'  => Hash::make($request->password),
            ]);

            
            $user = User::where('email', $request->email)->first();
            
            $tokenResult = $user->createToken('authToken')->plainTextToken;

           
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type'   => 'Bearer',
                'user'  => $user,
            ], 'User Terdaftar');


        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Authentication Failed', 500);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success($token, 'Token Revoked');
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'handphone' => 'required|numeric|digits_between:9,14',
            'alamat' => 'string',
            'photo' => 'image|mimes:jpg,png,jpeg|max:2048',
            'password' => 'required|string|min:8',
        ]);

        // dd($validator);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        if($request->photo == ''){
            $image_path = '';
        } else {
            $file_ext = $request->file('photo')->getClientOriginalExtension();
            $file_name = 'avatar' . "-" . rand(1111, 9999) . "-" . Carbon::now()->format('dmY') . "." . $file_ext;
            $image_path = $request->file('photo')->storeAs('images/photo', $file_name);
        }

        $user = Auth::user();

        $user->update([
                'name'  => $request->name,
                'email' => $request->email,
                'handphone' => $request->handphone,
                'alamat' => $request->alamat,
                'photo' => $image_path,
                'password'  => Hash::make($request->password),
        ]);

        return ResponseFormatter::success($user, 'Profile Berhasil diupdate');
    }

    public function jmluser()
    {
        $jumlahuser = User::count();
        return ResponseFormatter::success('jumlah User = ' . $jumlahuser, 'Jumlah user berhasil didapatkan');
    }

    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $nama_user = $request->input('name');

        if($id)
        {
            $users = User::find($id);

            if($users)
            {
                return ResponseFormatter::success(
                    $users,
                    'Data User berhasil diambil'
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Data User tidak ada',
                    404
                );
            }
        }

        $toko = User::query();

        if($nama_user)
        {
            $toko->where('name','like','%' . $nama_user . '%');
        }

        return ResponseFormatter::success(
            $toko->paginate($limit),
            'Data List User berhasil diambil',
        );
    }
}
