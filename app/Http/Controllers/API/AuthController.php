<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;

class AuthController extends BaseController
{
    public function __construct() {

    }
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['name'] =  $user->name;

        return $this->sendResponse($success, 'User register successfully.');
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')-> accessToken;
            $success['name'] =  $user->name;
            $user->generate2FACode();
            return $this->sendResponse($success, 'User login successfully.');
        }
        else{
            return $this->sendError('Unauthorised.', ['error'=>'unauthenticated']);
        }
    }

    public function check2FA(Request $request) {
        $request->validate([
            '2fa_code' => 'integer|required',
        ]);

        $user = auth()->user();
        if($this->hash2FaToken($request->input('2fa_code')) == $user->last_access_session->token)
        {
            if(optional(optional($user->last_access_session->created_at)->addMinutes(10))->lt(now())) {
                return response(['The two factor code has expired. Please login again.'],503);
            }
            $user->setActivated2Fa();
            return response(['success' => 'login with 2fa success']);
        }

        return response(['error' =>
                'The two factor code you have entered does not match']);
    }

    public function resend2Fa() {
        return auth()->user()->generate2FACode();
    }

    private function hash2FaToken($num) {
        return hash('sha256', $num);
    }

    public function logout(Request $request) {
        auth()->user()->reset2Fa();
        $request->user()->token()->revoke();
        return response(['sucess' => 'logout success']);
    }
}
