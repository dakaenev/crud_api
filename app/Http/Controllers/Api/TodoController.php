<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Resources\TodoResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;

class TodoController extends Controller
{
	private $states_accepted = ['Todo', 'Done'];
	
	// Checking hash_key in every case
	public function __construct(Request $request)
    {
        //b9895e103f84f710907f353dd6a34e2b24deba40
		$hash_now = sha1(date('Y-m-d'));
		if($request->hash_key != $hash_now)
		{
			echo json_encode(['status' => 'error', 'message' => 'Hash Key must be correct!']);
			die();
		}
    }
	
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $todos = Todo::all();
        return TodoResource::collection($todos);
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTodoRequest $request)
    {
		$validator = Validator::make($request->all(), [
            'description' => ['required', 'max:255'],
            'state'  => ['required'],
			'project'  => ['required'],
			'user'  => ['required']
        ]);
 
		$errors = [];
        if ($validator->fails()) {
			$errors = $validator->messages()->all();
        }
		
		if(isset($request->user) && !User::find($request->user))
		{
			$errors[] = 'User does not exist!!';
		}
		
		if(isset($request->project) && !Project::find($request->project))
		{
			$errors[] = 'Project does not exist!';
		}
	
		if(isset($request->state) && !in_array($request->state, $this->states_accepted))
		{
			$errors[] = 'State must be '.implode(' or ', $this->states_accepted).'!';
		}
		
		if(!empty($errors))
		{
			echo json_encode(['status' => 'error', 'messages' => $errors]);
			exit;
		}
		
        $todos = Todo::create($request->all());
        
        return new TodoResource($todos);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function show(Todo $todo)
    {
        $todo->view_count++;
        $todo->save();
        
        return new TodoResource($todo);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function edit(Todo $todo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Todo $todo)
    {
		$validator = Validator::make($request->all(), [
            'description' => ['max:255']
        ]);
 
        $errors = [];
        if ($validator->fails()) {
			$errors = $validator->messages()->all();
        }
		
		if(isset($request->user))
		{
			$user = User::find($request->user);
			if(is_null($user))
			{
				$errors[] = 'User does not exist!!';
			}
		}
		
		if(isset($request->project))
		{
			$project = Project::find($request->project);
			if(is_null($project))
			{
				$errors[] = 'Project does not exist!';
			}
		}
	
		if(isset($request->state) && !in_array($request->state, $this->states_accepted))
		{
			$errors[] = 'State must be '.implode(' or ', $this->states_accepted).'!';
		}
		
		if(!empty($errors))
		{
			echo json_encode(['status' => 'error', 'messages' => $errors]);
			exit;
		}
		
		//view_count cant be updated manualy
		$data = $request->except(['view_count']);
        $todo->update($data);
        
        return new TodoResource($todo);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Todo $todo)
    {
        $todo->delete();
        return response(null, 204);
    }
}
