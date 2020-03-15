<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use App\User;
use Hash;

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
        $check_password = User::where(['admin'=>'1'])->firsh();
        if (Hash::check($current_password,$check_password->password)) {
            echo 'true';die;
        } else {
            echo 'false';die;
        }
    }

    public function logout() {
        Session::flush();
        return redirect('admin')->with('flash_message_success','Logged out Successfully');
    }
}
