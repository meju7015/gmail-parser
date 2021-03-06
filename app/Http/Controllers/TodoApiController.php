<?php

namespace App\Http\Controllers;

use App\Http\Requests\TodoStoreRequest;
use App\Http\Requests\TodoUpdateRequest;
use App\Models\Todo;
use Illuminate\Http\Request;

class TodoApiController extends Controller
{
    private $todos;

    public function __construct(Todo $todos)
    {
        $this->todos = $todos;
    }

    public function list($userId): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->todos->where('user_id', $userId)->get()->toArray()
        ]);
    }

    public function store($userId, TodoStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        $todo = new Todo();
        $todo->user_id = $userId;
        $todo->text = $request->post('text');
        $result = $todo->save();

        return response()->json([
            'success' => (bool)$result,
            'message' => $result ? '성공' : '실패'
        ]);
    }

    public function update($id, TodoUpdateRequest $request)
    {
        $result = $this->todos->find($id);

        if ($result) {
            $result->fill($request->all())->save();
        }

        return response([
            'success' => (bool)$result,
            'message' => $result ? '성공' : '실패'
        ]);
    }

    public function delete($id)
    {
        $result = $this->todos->find($id);

        if ($result) {
            $result->delete();
        }

        return response()->json([
           'success' => (bool)$result,
           'message' => $result ? '성공' : '실패'
        ]);
    }
}
