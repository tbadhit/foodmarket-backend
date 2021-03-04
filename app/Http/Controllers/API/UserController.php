<?php

namespace App\Http\Controllers\API;

use App\Actions\Fortify\PasswordValidationRules;
use App\Helpers\ResponseFormatter;
use Exception;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    use PasswordValidationRules;
    // API Login :
    public function login(Request $request) {
        // Bloc try catch untuk menghandle errornya :
        try{

            // Membuat validasi input :
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            // Mengecek credentials login :
            $credentials = request(['email', 'password']);
            if(!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], 'Authentication Failed', 500);
            }

            // Jika hash tidak sesuai maka :
            $user = User::where('email', $request->email)->first();
            if(!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }

            // Jika berhasil maka loginkan
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');

        } catch(Exception $error) {
            // Kalo autentikasi gagal :
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authenticated Failed', 500);
        }
    }

    // API Register :
    public function register(Request $request) {

        // Bloc try catch untuk menghandle error :
        try {
            // Membuat validasi input :
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'unique:users'],
                'password' => $this->passwordRules()
            ]);

            // Membuat user :
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'houseNumber' => $request->houseNumber,
                'phoneNumber' => $request->phoneNumber,
                'city' => $request->city,
                'password' => Hash::make($request->password)
            ]); 

            // Membuat variable yang menyimpan data untuk memanggil user :
            $user = User::where('email', $request->email)->first();

            // Ambil tokennya juga karna pengen login :
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            // kalo regist berhasil kemblaikan token dan usernya :
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ]);
            
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Somthing went wrong',
                'error' => $error,
            ], 'Authentication Failed', 500);  
        }
    }

    // API Logout :
    public function logout(Request $request) {
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success($token, 'Token Revoked');
    }

    // API Fetch (ambil data user) :
    public function fetch(Request $request) {
        return ResponseFormatter::success(
            $request->user(), 'Data profile user berhasil di ambil'
        );
    }

    // API Update Profile :
    public function updateProfile(Request $request) {

        $data = $request->all();
        // Membuat variable user yang sedang login
        $user = Auth::user();
        $user->update($user);

        return ResponseFormatter::success($user, 'Profile Updated');
    }

    public function updatePhoto(Request $request) {
        // Validasinya (Yang dimana kita membutuhkan max 2mb gambar yang di upload & dibutuhkan saat proses upload) :
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|max:2048'
        ]);

        // kode di bawah ini kalo semisal validatornya gagal :
        if ($validator->fails()) {
            return ResponseFormatter::error(
                ['error' => $validator->errors()],
                'Upload Photo Fails',
                401
            );
        }

        // kode dibawah ini kalo semisal validator berhasil maka simpan fotonya ke database :
        if ($request->file('file')) {
            $file = $request->file->store('assets/user', 'public');

            // Simpan foto kedatabase (urlnya)
            $user = Auth::user();
            $user->profile_photo_path = $file;
            // ubah field databasenya dengan file yang sudah di upload
            $user->update();

            return ResponseFormatter::success([$file], 'File Successfully uploaded');
        }   
    }

    
    
    
    
}
 