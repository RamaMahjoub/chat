<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'body' => 'required',
            'conversation_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $message = new Message();
        $message->body = $request->body;
        $message->conversation_id = $request->conversation_id;
        $message->user_id = Auth::id();
        $message->save();

        return response()->json([
            'message' => 'message created successfully.',
            'data' => new MessageResource(Message::findOrFail($message->id)),
        ], Response::HTTP_OK);
    }

    public function update(Request $request, Message $msg)
    {
        $validator = Validator::make($request->all(), [
            'body' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson(),
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!(Auth::id() == $msg->user_id)) {
            return response()->json([
                'error' => 'unauthorized to edit.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $msg->update([
            $msg->body = $request->body
        ]);

        return response()->json([
            'message' => 'message updated successfully.',
            'data' => new MessageResource($msg),
        ], Response::HTTP_OK);
    }

    public function delete(Message $msg)
    {
        if (!(Auth::id() == $msg->user_id)) {
            return response()->json([
                'error' => 'unauthorized to delete.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $msg->delete();

        return response()->json([
            'message' => 'message deleted successfully.',
            'data' => null,
        ], Response::HTTP_OK);
    }
}
