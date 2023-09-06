<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tutorial;
use App\Http\Requests\TutorialRequest;




class TutorialController extends Controller
{
    //
    public function index()
    {
        $tutorial =  Tutorial::get();
        
        return view('admin.tutorial.tutorial', compact('tutorial'));
    }

    public function insert()
    {
        return view('admin.tutorial.new_tutorial');
    }

    public function store(TutorialRequest $request)
    {
        // Store tutorial
        $tutorial = new Tutorial();
        $tutorial->text = $request->text;
        $tutorial->title = $request->title;

        if($request->hasFile('file'))
        {
            $filename = "tutorial_".time().".". $request->file('file')->getClientOriginalExtension();
            $request->file('file')->move(public_path('client_uploads/tutorial/'), $filename);
            $tutorial->video = $filename;
        }
        $tutorial->save();
        return redirect()->route('tutorial')->with('success','Tutorial has been Inserted SuccessFully....');

    }

    public function destroy($id)
    {
        // Delete tutorial
        Tutorial::where('id',$id)->delete();

        return redirect()->route('tutorial')->with('success','Tutorial has been Removed SuccessFully..');
    }

    public function edit($id)
    {
        try 
        {
            $data = Tutorial::where('id',$id)->first();
            if($data)
            {
                
                return view('admin.tutorial.edit_tutorial',compact('data'));
            }
            return redirect()->route('tutorial')->with('error', 'Something went wrong!');
        } 
        catch (\Throwable $th) 
        {
            return redirect()->route('tutorial')->with('error', 'Something went wrong!');
        }
    }

    
    public function update(TutorialRequest $request)
    {
        // Update tutorial
        
        $tutorial = Tutorial::find($request->tutorial_id);
        $tutorial->text = $request->text;
        $tutorial->title = $request->title;
        if($request->hasFile('file'))
        {
            $filename = "tutorial_".time().".". $request->file('file')->getClientOriginalExtension();
            $request->file('file')->move(public_path('client_uploads/tutorial/'), $filename);
            $tutorial->video = $filename;
        }
        $tutorial->update();

        return redirect()->route('tutorial')->with('success','tutorial has been Updated SuccessFully....');
    }

    public function show()
    {
        $tutorial =  Tutorial::get();

        return view('client.tutorial.tutorial',compact('tutorial'));
    }

}
