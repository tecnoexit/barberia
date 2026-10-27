<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request; use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    public function form() { return view('auth.login'); }
    public function login(Request $request) { $data=$request->validate(['email'=>'required|email','password'=>'required']); if (!Auth::attempt($data) || !Auth::user()->isStaff()) return back()->withErrors(['email'=>'Credenciales incorrectas.']); $request->session()->regenerate(); return redirect()->route('dashboard'); }
    public function logout(Request $request) { Auth::logout(); $request->session()->invalidate(); return redirect()->route('login'); }
}
