<?php

namespace App\Http\Controllers\api;

use App\Models\Reminder;
use Carbon\Carbon;
use JWTAuth;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ReminderController extends Controller
{

    protected $user;
 
    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }
    /**
     * @OA\Get(
     ** path="/api/v1/indexReminder",
     *   tags={"Reminder"},
     *   summary="show all Reminders",
     *   operationId="show all Reminders",
     *   security={{ "api_auth": {} }},
     *
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
     *      )
     *)
     **/
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexReminder()
    {
        $reminder = Reminder::all();

        //Return success response
        return response()->json([
            'success' => true,
            'message' => 'Here are the Reminders.....',
            'data' => $reminder
        ], Response::HTTP_OK);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }
    /**
     * @OA\Post(
     ** path="/api/v1/createNewReminder",
     *   tags={"Reminder"},
     *   summary="createNewReminder",
     *   operationId="createNewReminder",
     *   security={{ "api_auth": {} }},
     *
     *    @OA\Parameter(
     *      name="description",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="date_of_origin",
     *      in="query",
     *      required=true,
     *      description="mm/dd/YYYY",
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeReminder(Request $request)
    {
        //Validate data
        $data = $request->only('description', 'date_of_origin');
        $validator = Validator::make($data, [
            'description' => 'required|string',
            'date_of_origin' => 'date_format:m/d/Y'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        
        //Set status of reminder according to current date
        if (((Carbon::now()->format('d') >= Carbon::parse($request->date_of_origin)->format('d')) && (Carbon::now()->format('m') >= Carbon::parse($request->date_of_origin)->format('m'))) || (Carbon::now()->format('m') > Carbon::parse($request->date_of_origin)->format('m')) ) {
            $status = "completed";
        }
        else {
            $status = "opened";
        }

        //Create Reminder
        $reminder = Reminder::create([
            'description' => $request->description,
            'date_of_origin' => $request->date_of_origin,
            'status' => $status 
        ]);

        //Reminder created, return success response
        return response()->json([
            'success' => true,
            'message' => 'Reminder created successfully',
            'data' => $reminder
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     ** path="/api/v1/getUpComingReminders",
     *   tags={"Reminder"},
     *   summary="show upcoming Reminders",
     *   operationId="show upcoming Reminders",
     *   security={{ "api_auth": {} }},
     *
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
     * Show the Upcoming Reminders.
     *
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function getUpComingReminders()
    {
        $reminder = Reminder::whereDay('date_of_origin', '>=', Carbon::now())->whereMonth('date_of_origin', '>=', Carbon::now())->get();

        //Return success response
        return response()->json([
            'success' => true,
            'message' => 'Here are the upcoming Reminders.....',
            'data' => $reminder
        ], Response::HTTP_OK);        
    }

    /**
     * @OA\Get(
     ** path="/api/v1/getReminderForDate",
     *   tags={"Reminder"},
     *   summary="Show Reminders for particular date",
     *   operationId="Show Reminders for particular date",
     *   security={{ "api_auth": {} }},
     *
     *    @OA\Parameter(
     *      name="date_of_origin",
     *      in="query",
     *      required=true,
     *      description="Get reminder for entered date. Date should be in the given format: mm/dd/YYYY ",
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function getReminderForDate(Request $request)
    {
        //Validate data
        $data = $request->only('date_of_origin');
        $validator = Validator::make($data, [
            'date_of_origin' => 'date_format:m/d/Y'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $reminder = Reminder::whereDay('date_of_origin', '=', Carbon::parse($request->date_of_origin))->whereMonth('date_of_origin', '=', Carbon::parse($request->date_of_origin))->get();

        //Return success response
        return response()->json([
            'success' => true,
            'message' => 'Here are the Reminders for entered date.....',
            'data' => $reminder
        ], Response::HTTP_OK);        
    }

    /**
     * @OA\Get(
     ** path="/api/v1/getCompleteReminders",
     *   tags={"Reminder"},
     *   summary="Show all closed Reminders",
     *   operationId="Show all closed Reminders",
     *   security={{ "api_auth": {} }},
     *
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
     *      )
     *)
     **/
    /**
     * Show all closed reminders.
     *
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function getCompleteReminders()
    {
        //Get reminders that are closed
        $reminder = Reminder::where('status', 'completed')->get();

        //Return success response
        return response()->json([
            'success' => true,
            'message' => 'Here are the Reminders that are closed.....',
            'data' => $reminder
        ], Response::HTTP_OK);        
    }

    /**
     * @OA\Get(
     ** path="/api/v1/getOpenReminders",
     *   tags={"Reminder"},
     *   summary="Show all open Reminders",
     *   operationId="Show all open Reminders",
     *   security={{ "api_auth": {} }},
     *
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
     * Show all open reminders.
     *
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function getOpenReminders()
    {
        //Get reminders that are closed
        $reminder = Reminder::where('status', 'opened')->get();

        //Return success response
        return response()->json([
            'success' => true,
            'message' => 'Here are the Reminders that are opened.....',
            'data' => $reminder
        ], Response::HTTP_OK);        
    }

    /**
     * @OA\Get(
     ** path="/api/v1/getReminder/{id}",
     *   tags={"Reminder"},
     *   summary="Get Reminder",
     *   operationId="Get Reminder",
     *   security={{ "api_auth": {} }},
     *
     *    @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      description="ID of reminder that needs to be fetched",
     *      @OA\Schema(
     *           type="integer"
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
     * Get reminder based on id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function getReminder($id)
    {
        //Get reminder of the entered id
        $reminder = Reminder::where('id', $id)->first();

        //Return success message
        return response()->json([
            'success' => true,
            'message' => 'Here are the reminder for entered id',
            'data' => $reminder
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     ** path="/api/v1/updateReminder/{id}",
     *   tags={"Reminder"},
     *   summary="updateReminder",
     *   operationId="updateReminder",
     *   security={{ "api_auth": {} }},
     *
     *    @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      description="ID of reminder that needs to be updated",
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *    @OA\Parameter(
     *      name="description",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="date_of_origin",
     *      in="query",
     *      required=true,
     *      description="mm/dd/YYYY",
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function updateReminder(Request $request, $id)
    {
        //Validate data
        $data = $request->only('description', 'date_of_origin');
        $validator = Validator::make($data, [
            'description' => 'required|string',
            'date_of_origin' => 'date_format:m/d/Y'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        
        //Set status of reminder according to current date
        if (((Carbon::now()->format('d') >= Carbon::parse($request->date_of_origin)->format('d')) && (Carbon::now()->format('m') >= Carbon::parse($request->date_of_origin)->format('m'))) || (Carbon::now()->format('m') > Carbon::parse($request->date_of_origin)->format('m')) ) {
            $statusOfReminder = "completed";
        }
        else {
            $statusOfReminder = "opened";
        }

        //Get reminder whose value you want to update
        $reminder = Reminder::where('id', $id)->first();

        //Request is valid, update reminder
        $reminder->description = $request->description;
        $reminder->date_of_origin = $request->date_of_origin;
        $reminder->status = $statusOfReminder;
        $reminder->save();

        return response()->json([
            'success' => true,
            'message' => 'Reminder updated successfully',
            'data' => $reminder
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     ** path="/api/v1/updateReminderStatus/{id}",
     *   tags={"Reminder"},
     *   summary="Update Reminder Status",
     *   operationId="Update Reminder Status",
     *   security={{ "api_auth": {} }},
     *
     *    @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      description="ID of reminder whose status needs to be updated",
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="status",
     *      in="query",
     *      required=true,
     *      description="Enter 'completed' or 'opened' ",
     *      @OA\Schema(
     *           type="string"
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function updateReminderStatus(Request $request, $id)
    {
        //Validate data
        $data = $request->only('status');
        $validator = Validator::make($data, [
            'status' => 'required|in:completed,opened'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        
        //Get reminder whose value you want to update
        $reminder = Reminder::where('id', $id)->first();

        //Request is valid, update reminder
        $reminder->status = $request->status;
        $reminder->save();

        return response()->json([
            'success' => true,
            'message' => 'Reminder Status Updated Successfully',
            'data' => $reminder
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     ** path="/api/v1/deleteReminderById/{id}",
     *   tags={"Reminder"},
     *   summary="Delete Reminder",
     *   operationId="Delete Reminder",
     *   security={{ "api_auth": {} }},
     *
     *    @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      description="ID of reminder that needs to be deleted",
     *      @OA\Schema(
     *           type="integer"
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
     * Delete reminder based on id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function deleteReminderById($id)
    {
        //Delete reminder of the entered id
        Reminder::where('id', $id)->delete();

        //Return success message
        return response()->json([
            'success' => true,
            'message' => 'Reminder Deleted Successfully...'
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     ** path="/api/v1/deleteReminderForDate",
     *   tags={"Reminder"},
     *   summary="Delete Reminders for particular date",
     *   operationId="Delete Reminders for particular date",
     *   security={{ "api_auth": {} }},
     *
     *    @OA\Parameter(
     *      name="date_of_origin",
     *      in="query",
     *      required=true,
     *      description="Delete reminder for entered date. Date should be in the given format: mm/dd/YYYY ",
     *      @OA\Schema(
     *           type="string"
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
     * Delete reminders for specific date.
     *
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function deleteReminderForDate(Request $request)
    {
        //Validate data
        $data = $request->only('date_of_origin');
        $validator = Validator::make($data, [
            'date_of_origin' => 'date_format:m/d/Y'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        Reminder::whereDay('date_of_origin', '=', Carbon::parse($request->date_of_origin))->whereMonth('date_of_origin', '=', Carbon::parse($request->date_of_origin))->delete();

        //Return success response
        return response()->json([
            'success' => true,
            'message' => 'Your reminder for entered date is deleted successfully.....'
        ], Response::HTTP_OK);        
    }

    /**
     * @OA\Delete(
     ** path="/api/v1/deleteCompleteReminders",
     *   tags={"Reminder"},
     *   summary="Delete all closed Reminders",
     *   operationId="Delete all closed Reminders",
     *   security={{ "api_auth": {} }},
     *
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
     * Delete all closed reminders.
     *
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function deleteCompleteReminders()
    {
        //Delete reminders that are closed
        Reminder::where('status', 'completed')->delete();

        //Return success response
        return response()->json([
            'success' => true,
            'message' => 'All closed reminders are deleted successfully.....'
        ], Response::HTTP_OK);        
    }
    
}
