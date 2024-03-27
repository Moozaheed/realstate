<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function AdminDashboard(){
        return view('admin/index');
    }

    public function AdminLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }

    public function AdminLogin()
    {
        return view('admin.admin_login');
    }

    public function AdminProfile()
    {
        $id=Auth::user()->id;
        $profileData=User::find($id);
        return view('admin.admin_profile_view',compact('profileData'));
    }

    public function AdminProfileStore(Request $request)
    {
        $id=Auth::user()->id;
        $profileData=User::find($id);
        $profileData->name=$request->name;
        $profileData->username=$request->username;
        $profileData->email=$request->email;
        $profileData->phone=$request->phone;
        $profileData->address=$request->address;
        if($request->file('photo')){
            $image=$request->file('photo');
            $image_name=time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('upload/admin_images'),$image_name);
            $profileData->photo=$image_name;
        }
        $profileData->save();

        $notification=array(
            'message'=>'Profile Updated Successfully',
            'alert-type'=>'success'
        );
        return redirect()->back()->with($notification);
    }

    public function AdminChangePassword()
    {
        $id=Auth::user()->id;
        $profileData=User::find($id);
        return view('admin.admin_change_password',compact('profileData'));
    }
    public function AdminUpdatePassword(Request $request)
    {
        // dd($request->all());
        //validation
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed',
        ]);

        //matching old password
        if(!Hash::check($request->old_password, auth::user()->password)){
            $notification=array(
                'message'=>'Old Password Does Not Matched',
                'alert-type'=>'error'
            );
            return back()->with($notification);
        }

        //Update the new password
        User::whereId(auth()->user()->id)->update([
            'password'=>Hash::make($request->new_password)
        ]);

        $notification=array(
            'message'=>'Password Has Beed Updated',
            'alert-type'=>'success'
        );
        return back()->with($notification);


    }
}
