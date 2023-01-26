<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller {

    /**
     * New user Create.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    function create( Request $request ) {

        $valid = Validator::make( $request->all(), [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'photo'    => 'nullable|image|mimes:jpg,png,jpeg|max:300',
        ] );

        if ( $valid->fails() ) {
            $response = [
                'error'   => true,
                'message' => $valid->errors(),
            ];
            return response()->json( $response, 401 );
        }

        $photo = $request->file( 'photo' );

        if ( $photo ) {

            $_photo_name = Str::ulid() . '.' . $photo->extension();
            Storage::putFileAs( 'profile', $photo, $_photo_name );

        } else {
            $_photo_name = null;
        }

        $user = User::create( [
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make( $request->password ),
            'role'     => 'author',
            'photo'    => $_photo_name,
        ] );

        $token = $user->createToken( 'apptoken' )->plainTextToken;

        event( new Registered( $user ) );

        $respons = [
            'success' => true,
            'user'    => $user,
            'token'   => $token,
            'message' => "Registation Successfull. Please, Check Your Email and Verify Your Account!",
        ];

        return response()->json( $respons, 201 );
    }

    /**
     * Api User Login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login( Request $request ) {

        $request->validate( [
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ] );

        if ( Auth::attempt( ['email' => $request->email, 'password' => $request->password] ) ) {
            $user  = Auth::user();
            $token = $user->createToken( 'apptoken' )->plainTextToken;

            $respons = [
                'success' => true,
                'token'   => $token,
                'user'    => $user,
                'message' => 'Login Successfull!',
            ];

            return response()->json( $respons, 201 );
        } else {
            $respons = [
                'success' => false,
                'message' => 'Login Failed!',
            ];

            return response()->json( $respons, 401 );
        }

    }

    /**
     * Api User Logout.
     *
     */
    public function logout( Request $request ) {

        $user = auth()->user();

        return response()->join( $user );
        // $accessToken = $request->bearerToken();

        // if ( $accessToken ) {
        //     $token = PersonalAccessToken::findToken( $accessToken );

        //     $token->delete();
        //     return [
        //         'message' => 'Logout Successfull!',
        //     ];
        // } else {
        //     return [
        //         'message' => 'Logout failed!',
        //     ];
        // }

    }
}
