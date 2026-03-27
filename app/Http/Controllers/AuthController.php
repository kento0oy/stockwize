<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /* ─── Show login page ─── */
    public function showLogin()
    {
        if (session('manager_logged_in')) {
            return redirect()->route('dashboard');
        }
        return view('login');
    }

    /* ─── Show dashboard ─── */
    public function showDashboard()
    {
        if (!session('manager_logged_in')) {
            return redirect('/')->with('error', 'Please log in to continue.');
        }
        return view('dashboard');
    }

    /* ─── Login ─── */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $manager = DB::table('managers')
            ->where('email', $request->email)
            ->first();

        if (!$manager || !Hash::check($request->password, $manager->password)) {
            return back()->with('error', 'Invalid email or password.')->onlyInput('email');
        }

        session([
            'manager_logged_in' => true,
            'manager_id'        => $manager->id,
            'manager_name'      => $manager->first_name . ' ' . $manager->last_name,
            'manager_email'     => $manager->email,
        ]);

        return redirect()->route('dashboard');
    }

    /* ─── Logout ─── */
    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect('/');
    }

    /* ─── Show Forgot Password page ─── */
    public function showForgotPassword()
    {
        if (session('manager_logged_in')) {
            return redirect()->route('dashboard');
        }
        return view('forgot_password');
    }

    /* ─── Send Reset Link via Email ─── */
    public function sendResetLink(Request $request)
    {
        
        $request->validate(['email' => 'required|email']);

        $manager = DB::table('managers')->where('email', $request->email)->first();

        if (!$manager) {
            return back()->with('success', 'If that email exists, a reset link has been sent.');
        }

        $token = Str::random(64);

        DB::table('password_resets')->where('email', $request->email)->delete();

        DB::table('password_resets')->insert([
            'email'      => $request->email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($request->email));

        // Always log the reset URL so it can be found in storage/logs/laravel.log
        \Log::info('STOCKWIZE Reset URL: ' . $resetUrl);

        try {
            Mail::send([], [], function ($message) use ($manager, $resetUrl) {
                $message->to($manager->email)
                        ->subject('STOCKWIZE — Password Reset Request')
                        ->html("
                            <div style='font-family:Arial,sans-serif;max-width:520px;margin:0 auto;background:#0d0f11;color:#f0f2f5;padding:40px;border-radius:12px;'>
                                <div style='margin-bottom:32px;'>
                                    <h1 style='font-size:22px;font-weight:800;color:#e8ff47;letter-spacing:-0.5px;margin:0;'>STOCKWIZE</h1>
                                    <p style='font-size:10px;letter-spacing:3px;text-transform:uppercase;color:#5a6070;margin:4px 0 0;'>Inventory Management System</p>
                                </div>
                                <h2 style='font-size:20px;font-weight:700;margin-bottom:12px;'>Password Reset Request</h2>
                                <p style='color:#8a909e;font-size:14px;line-height:1.6;margin-bottom:28px;'>
                                    Hi {$manager->first_name}, we received a request to reset your STOCKWIZE password.
                                    Click the button below to set a new password.
                                    This link expires in <strong style='color:#f0f2f5;'>60 minutes</strong>.
                                </p>
                                <a href='{$resetUrl}'
                                   style='display:inline-block;background:#e8ff47;color:#0d0f11;font-weight:700;font-size:14px;padding:14px 28px;border-radius:6px;text-decoration:none;margin-bottom:28px;'>
                                   Reset My Password
                                </a>
                                <p style='color:#5a6070;font-size:12px;line-height:1.6;'>
                                    If you didn't request this, you can safely ignore this email.
                                </p>
                                <hr style='border:none;border-top:1px solid #2a2d33;margin:28px 0;'/>
                                <p style='color:#5a6070;font-size:11px;'>© 2026 STOCKWIZE — All rights reserved.</p>
                            </div>
                        ");
            });
        } catch (\Exception $e) {
            \Log::error('STOCKWIZE Mail error: ' . $e->getMessage());
        }

        return back()->with('success', 'A password reset link has been sent to your email address.');
    }

    /* ─── Show Reset Password page ─── */
    public function showResetPassword($token, Request $request)
    {
        if (session('manager_logged_in')) {
            return redirect()->route('dashboard');
        }

        $reset = DB::table('password_resets')
            ->where('email', $request->query('email'))
            ->first();

        if (!$reset || !Hash::check($token, $reset->token)) {
            return redirect('/forgot-password')->with('error', 'This reset link is invalid or has already been used.');
        }

        if (now()->diffInMinutes($reset->created_at) > 60) {
            DB::table('password_resets')->where('email', $request->query('email'))->delete();
            return redirect('/forgot-password')->with('error', 'This reset link has expired. Please request a new one.');
        }

        return view('reset_password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /* ─── Handle Password Reset ─── */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                     => 'required|email',
            'token'                     => 'required',
            'new_password'              => 'required|min:8',
            'new_password_confirmation' => 'required|same:new_password',
        ]);

        $reset = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return back()->with('error', 'Invalid or expired reset link.');
        }

        if (now()->diffInMinutes($reset->created_at) > 60) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return redirect('/forgot-password')->with('error', 'Reset link expired. Please request a new one.');
        }

        DB::table('managers')
            ->where('email', $request->email)
            ->update(['password' => Hash::make($request->new_password)]);

        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect('/')->with('success', 'Password reset successfully! You can now log in.');
    }

    /* ─── Change Password (from dashboard) ─── */
    public function changePassword(Request $request)
    {
        if (!session('manager_logged_in')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $manager = DB::table('managers')->where('id', session('manager_id'))->first();

        if (!$manager || !Hash::check($request->current_password, $manager->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect.']);
        }

        if (strlen($request->new_password) < 8) {
            return response()->json(['success' => false, 'message' => 'Password must be at least 8 characters.']);
        }

        DB::table('managers')
            ->where('id', session('manager_id'))
            ->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['success' => true]);
    }

    /* ─── GET all products ─── */
    public function getProducts()
    {
        if (!session('manager_logged_in')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $products = DB::table('products')->orderBy('created_at', 'desc')->get();
        return response()->json($products);
    }

    /* ─── ADD product ─── */
    public function addProduct(Request $request)
    {
        if (!session('manager_logged_in')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'name'     => 'required',
            'sku'      => 'required|unique:products,sku',
            'category' => 'required',
            'quantity' => 'required|integer|min:0',
            'price'    => 'required|numeric|min:0',
            'reorder'  => 'required|integer|min:0',
            'supplier' => 'required',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $id = DB::table('products')->insertGetId([
            'name'     => $request->name,
            'sku'      => $request->sku,
            'category' => $request->category,
            'quantity' => $request->quantity,
            'price'    => $request->price,
            'reorder'  => $request->reorder,
            'supplier' => $request->supplier,
            'notes'    => $request->notes,
            'image'    => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'id'      => $id,
            'image'   => $imagePath ? asset('storage/' . $imagePath) : null,
        ]);
    }

    /* ─── UPDATE product ─── */
    public function updateProduct(Request $request, $id)
    {
        if (!session('manager_logged_in')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = [
            'name'     => $request->name,
            'sku'      => $request->sku,
            'category' => $request->category,
            'quantity' => $request->quantity,
            'price'    => $request->price,
            'reorder'  => $request->reorder,
            'supplier' => $request->supplier,
            'notes'    => $request->notes,
        ];

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);
            $old = DB::table('products')->where('id', $id)->value('image');
            if ($old) Storage::disk('public')->delete($old);
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        DB::table('products')->where('id', $id)->update($data);

        $updatedImage = DB::table('products')->where('id', $id)->value('image');
        return response()->json([
            'success' => true,
            'image'   => $updatedImage ? asset('storage/' . $updatedImage) : null,
        ]);
    }

    /* ─── DELETE product ─── */
    public function deleteProduct($id)
    {
        if (!session('manager_logged_in')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $image = DB::table('products')->where('id', $id)->value('image');
        if ($image) Storage::disk('public')->delete($image);

        DB::table('products')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }
}