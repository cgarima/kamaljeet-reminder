<?php

namespace App\Http\Controllers\api;

use JWTAuth;
use App\Models\User;
use App\Models\UserRole;
use App\Mail\SendMailReset;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * @OA\Post(
     ** path="/api/v1/register",
     *   tags={"User"},
     *   summary="Register",
     *   operationId="Register",
     *
     *    @OA\Parameter(
     *      name="name",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Forbidden"
     *   )
     *)
     **/
     /**
     * register api
     *
     * @return \Illuminate\Http\Response
     */
    public function registerUser(Request $request)
    {
    	//Validate data
        $data = $request->only('name', 'email', 'password');
        $validator = Validator::make($data, [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',             // must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
                   ],
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        
        //Fetch User Role
        $user_role=UserRole::where('name', 'admin')->first();
        //Request is valid, create new user
        $user = User::create([
        	'name' => $request->name,
        	'email' => $request->email,
        	'password' => bcrypt($request->password),
            'user_role_id' => $user_role->id
        ]);

        //User created, return success response
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     ** path="/api/v1/login",
     *   tags={"User"},
     *   summary="Login",
     *   operationId="Login",
     *
     *   @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *       @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Forbidden"
     *   )
     *)
     **/
    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function authenticateUser(Request $request)
    {
        $credentials = $request->only('email', 'password');

        //validate credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'min:8',             // must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
                   ],
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is validated
        //Create token
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                	'success' => false,
                	'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return $credentials;
                return response()->json([
                        'success' => false,
                        'message' => 'Could not create token.',
                    ], 500);
            }
 	
 		//Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }

    /**
     * @OA\Post(
     ** path="/api/v1/passwordResetLink",
     *   tags={"User"},
     *   summary="sendResetPasswordLink",
     *   operationId="sendResetPasswordLink",
     *
     *   @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *       @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Forbidden"
     *   )
     *)
     **/
    /**
     * sendResetPasswordLink api
     *
     * @return \Illuminate\Http\Response
     */
    public function sendEmail(Request $request)
    {
        if (!$this->validateEmail($request->email)) {
            return $this->failedResponse();
        }
        $this->send($request->email);   //call function to send mail
        return $this->successResponse();
    }

    public function send($email)  //function to send mail
    {
        $token = $this->createToken($email);
        Mail::to($email)->send(new SendMailReset($token, $email));
    }

    public function createToken($email)
    {
        $oldToken = DB::table('password_resets')->where('email', $email)->first();

        if ($oldToken) {
            return $oldToken->token;
        }

        $token=Str::random(40);
        $this->saveToken($token, $email);
        return $token;
    }

    public function saveToken($token, $email)  //function saves new password
    {
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now() 
        ]);
    }

    public function validateEmail($email)  //function to get email from database
    {
        $user = User::where('email', $email)->first();
        return $user;
    }

    public function failedResponse()
    {
        return response()->json([
            'error' => 'Email not found on your database'
        ], Response::HTTP_NOT_FOUND);
    }

    public function successResponse()
    {
        return response()->json([
            'data' => 'Reset email is send successfully, please check your inbox.'
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     ** path="/api/v1/resetPassword",
     *   tags={"User"},
     *   summary="resetPassword",
     *   operationId="resetPassword",
     *
     *    @OA\Parameter(
     *      name="resetToken",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="password_confirmation",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *       @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Forbidden"
     *    )
     *)
     **/
    /**
     * password reset api
     *
     * @return \Illuminate\Http\Response
     */
    public function passwordReset(UpdatePasswordRequest $request)
    {
        return $this->updateUserPassword($request)->count() > 0 ? $this->resetPassword($request) : $this->tokenNotFoundError();
    }

    //Verify if token is valid
    private function updateUserPassword($request)
    {
        return DB::table('password_resets')->where([
            'email' => $request->email,
            'token' => $request->resetToken
        ]);
    }

    //Token Not Found Response
    private function tokenNotFoundError()
    {
        return response()->json([
            'error' => 'Either your email or token is wrong.'
        ],Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    //Reset Password
    private function resetPassword($request)
    {
        $userData = User::where('email', $request->email)->first();  //find email
        //update password
        $userData->update([
            'password' => bcrypt($request->password)
        ]);
        //remove verification data from db
        $this->updateUserPassword($request)->delete();
        //reset password response
        return response()->json([
            'data' => 'Password has been updated.'
        ],Response::HTTP_CREATED);
    }

}
