<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ConversationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'second_user_id' => 'required',
            'body' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $conversation = new Conversation();
        $conversation->second_user_id = $request->second_user_id;
        $conversation->first_user_id = Auth::id();
        $conversation->save();

        $message = new Message();
        $message->body = $request->body;
        $message->conversation_id = $conversation->id;
        $message->user_id = Auth::id();
        $message->save();

        return response()->json([
            'message' => 'conversation created successfully.',
            'data' => new ConversationResource($conversation),
        ], Response::HTTP_OK);
    }

    public function makeConversationReaded(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $messages = Conversation::findOrFail($request->conversation_id)->messages;

        $messages->each(function ($message) {
            $message->update([$message->read = true]);
        });

        return response()->json([
            'message' => 'messages readed successfully ',
            'data' => null,
        ], Response::HTTP_OK);
    }

    public function show(Conversation $conversation)
    {
        return response()->json([
            'message' => 'Messages in this conversation.',
            'data' => MessageResource::collection($conversation->messages),
        ], Response::HTTP_OK);
    }

    public function index()
    {
        $conversations = Conversation::where('first_user_id', Auth::id())
            ->orWhere('second_user_id', Auth::id())
            ->get();

        return response()->json([
            'message' => 'Conversations to this user.',
            'data' => ConversationResource::collection($conversations),
        ], Response::HTTP_OK);
    }
}
