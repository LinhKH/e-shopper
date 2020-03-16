<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Session;
use App\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function login(Request $request) {
        if ($request->isMethod('post')) {
            $data = $request->input();
            if (Auth::attempt([ 'email'=>$data['email'], 'password'=> $data['password'], 'admin' =>'1' ])) {
                Session::put('adminSession',$data['email']);
                return redirect('/admin/dashboard');
            } else {
                return redirect('/admin')->with('flash_message_error','Invalid Username or Password');
            }
        }
        return view('admin.admin_login');
    }

    public function dashboard() {
        // if (Session::has('adminSession')) {
        //     # Perform all dashboard tasks / Thực hiện tất cả các nhiệm vụ bảng điều khiển
        // } else {
        //     return redirect('/admin')->with('flash_message_error','Please login to access');
        // }
        return view('admin.dashboard');
    }

    public function settings() {
        return view('admin.settings');
    }

    public function checkpwd(Request $request) {
        $data = $request->all();
        $current_password = $data['cpwd'];
        $check_password = User::where(['admin'=>'1'])->first();
        if (Hash::check($current_password,$check_password->password)) {
            echo 'true';die;
        } else {
            echo 'false';die;
        }
    }

    public function updatePassword(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $check_password = User::where(['email'=>Auth::user()->email])->first();
            $current_password = $data['cpwd'];

            if (Hash::check($current_password, $check_password->password)) {
                // here you know data is valid
                $password = bcrypt($data['npwd']);
                User::where('id','1')->update(['password'=>$password]);
                return redirect('/admin/settings')->with('flash_message_success', 'Password updated successfully.');
            }else{
                return redirect('/admin/settings')->with('flash_message_error', 'Current Password entered is incorrect.');
            }

            
        }
    }

    public function logout() {
        Session::flush();
        return redirect('admin')->with('flash_message_success','Logged out Successfully');
    }
}
