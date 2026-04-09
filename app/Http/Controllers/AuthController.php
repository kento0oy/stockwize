<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            $firstName = $manager->first_name;
            $htmlBody  = <<<HTML
                <div style="font-family:Arial,sans-serif;max-width:520px;margin:0 auto;background:#0d0f11;color:#f0f2f5;padding:40px;border-radius:12px;">
                    <div style="margin-bottom:32px;">
                        <h1 style="font-size:22px;font-weight:800;color:#e8ff47;letter-spacing:-0.5px;margin:0;">STOCKWIZE</h1>
                        <p style="font-size:10px;letter-spacing:3px;text-transform:uppercase;color:#5a6070;margin:4px 0 0;">Inventory Management System</p>
                    </div>
                    <h2 style="font-size:20px;font-weight:700;margin-bottom:12px;">Password Reset Request</h2>
                    <p style="color:#8a909e;font-size:14px;line-height:1.6;margin-bottom:28px;">
                        Hi {$firstName}, we received a request to reset your STOCKWIZE password.
                        Click the button below to set a new password.
                        This link expires in <strong style="color:#f0f2f5;">15 minutes</strong>.
                    </p>
                    <a href="{$resetUrl}"
                       style="display:inline-block;background:#e8ff47;color:#0d0f11;font-weight:700;font-size:14px;padding:14px 28px;border-radius:6px;text-decoration:none;margin-bottom:28px;">
                       Reset My Password
                    </a>
                    <p style="color:#5a6070;font-size:12px;line-height:1.6;">
                        If you did not request this, you can safely ignore this email.
                    </p>
                    <hr style="border:none;border-top:1px solid #2a2d33;margin:28px 0;"/>
                    <p style="color:#5a6070;font-size:11px;">&copy; 2026 STOCKWIZE &mdash; All rights reserved.</p>
                </div>
HTML;

            Mail::html($htmlBody, function ($message) use ($manager) {
                $message->to($manager->email)
                        ->subject('STOCKWIZE — Password Reset Request');
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

        if (now()->diffInMinutes($reset->created_at) > 15) {
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

        if (now()->diffInMinutes($reset->created_at) > 15) {
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
/* ─── GET all categories ─── */
public function getCategories()
{
    if (!session('manager_logged_in')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    $categories = DB::table('categories')->orderBy('name')->get();
    return response()->json($categories);
}

/* ─── ADD category ─── */
public function addCategory(Request $request)
{
    if (!session('manager_logged_in')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $request->validate([
        'name'  => 'required|unique:categories,name|max:100',
        'color' => 'required|max:7',
        'icon'  => 'nullable|max:10',
    ]);

    $id = DB::table('categories')->insertGetId([
        'name'  => $request->name,
        'color' => $request->color,
        'icon'  => $request->icon ?? '📦',
    ]);

    return response()->json(['success' => true, 'id' => $id]);
}

/* ─── DELETE category ─── */
public function deleteCategory($id)
{
    if (!session('manager_logged_in')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    DB::table('categories')->where('id', $id)->delete();
    return response()->json(['success' => true]);
}
}

class ReportsController extends Controller
{
    /**
     * GET /reports
     * Main reports page — loads all products for the Inventory Overview table.
     */
    public function index()
    {
        $products = Product::with(['category', 'supplier'])
            ->orderBy('name')
            ->get();
 
        return view('reports', compact('products'));
    }
 
    /* ══════════════════════════════════════════════
       JSON ENDPOINTS  (called via fetch from reports.js)
    ══════════════════════════════════════════════ */
 
    /**
     * GET /reports/summary
     * Returns all products with totals.
     */
    public function summary()
    {
        $products = Product::with(['category', 'supplier'])
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'name'          => $p->name,
                'sku'           => $p->sku,
                'category_name' => $p->category?->name,
                'supplier_name' => $p->supplier?->name,
                'quantity'      => $p->quantity,
                'price'         => $p->price,
                'reorder_level' => $p->reorder_level,
            ]);
 
        return response()->json([
            'products' => $products,
            'totals'   => [
                'count'       => $products->count(),
                'total_qty'   => $products->sum('quantity'),
                'total_value' => $products->sum(fn($p) => $p['quantity'] * $p['price']),
            ],
        ]);
    }
 
    /**
     * GET /reports/lowstock
     * Returns items at or below reorder_level.
     */
    public function lowstock()
    {
        $items = Product::with(['category', 'supplier'])
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity')
            ->get()
            ->map(fn($p) => [
                'name'          => $p->name,
                'sku'           => $p->sku,
                'category_name' => $p->category?->name,
                'supplier_name' => $p->supplier?->name,
                'quantity'      => $p->quantity,
                'reorder_level' => $p->reorder_level,
            ]);
 
        $outOfStock = $items->where('quantity', 0)->count();
 
        return response()->json([
            'items'  => $items,
            'counts' => [
                'total'       => $items->count(),
                'out_of_stock'=> $outOfStock,
                'low_stock'   => $items->count() - $outOfStock,
            ],
        ]);
    }
 
    /**
     * GET /reports/valuation
     * Returns inventory worth grouped by category and supplier.
     */
    public function valuation()
    {
        $allProducts = Product::with(['category', 'supplier'])->get();
 
        $grandTotal = $allProducts->sum(fn($p) => $p->quantity * $p->price);
 
        // Group by category
        $categories = $allProducts
            ->groupBy(fn($p) => $p->category?->name ?? 'Uncategorised')
            ->map(fn($group, $name) => [
                'name'  => $name,
                'value' => $group->sum(fn($p) => $p->quantity * $p->price),
            ])
            ->sortByDesc('value')
            ->values();
 
        // Group by supplier
        $suppliers = $allProducts
            ->groupBy(fn($p) => $p->supplier?->name ?? 'Unknown')
            ->map(fn($group, $name) => [
                'name'  => $name,
                'value' => $group->sum(fn($p) => $p->quantity * $p->price),
                'pct'   => $grandTotal > 0
                    ? round($group->sum(fn($p) => $p->quantity * $p->price) / $grandTotal * 100, 1)
                    : 0,
            ])
            ->sortByDesc('value')
            ->values();
 
        return response()->json([
            'grand_total' => $grandTotal,
            'categories'  => $categories,
            'suppliers'   => $suppliers,
        ]);
    }
 
    /**
     * GET /reports/movement
     * Returns recent stock activity log.
     *
     * NOTE: This assumes you have an `activity_logs` table.
     * If you use spatie/laravel-activitylog, adjust the query below.
     * If you don't have activity logging yet, see the fallback below.
     */
    public function movement()
    {
        // ── Option A: custom activity_logs table ──
        // Uncomment if you have this table:
        /*
        $logs = DB::table('activity_logs')
            ->join('products', 'activity_logs.product_id', '=', 'products.id')
            ->select(
                'activity_logs.type',
                'products.name',
                'activity_logs.detail',
                DB::raw("DATE_FORMAT(activity_logs.created_at, '%b %d %H:%i') as time")
            )
            ->orderByDesc('activity_logs.created_at')
            ->limit(50)
            ->get();
        */
 
        // ── Option B: spatie/laravel-activitylog ──
        // Uncomment if you use this package:
        /*
        use Spatie\Activitylog\Models\Activity;
        $logs = Activity::with('subject')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn($a) => [
                'type'   => $a->event,   // 'created' | 'updated' | 'deleted'
                'name'   => $a->subject?->name ?? $a->properties->get('old.name', '—'),
                'detail' => $a->description,
                'time'   => $a->created_at->diffForHumans(),
            ]);
        */
 
        // ── Option C: fallback — show recently updated products ──
        // Remove this block once you implement activity logging above.
        $recent = Product::with('category')
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get()
            ->map(fn($p) => [
                'type'   => 'edit',
                'name'   => $p->name,
                'detail' => "Last updated · Qty: {$p->quantity}",
                'time'   => $p->updated_at->format('M d H:i'),
            ]);
 
        $added   = $recent->where('type', 'add')->count();
        $updated = $recent->where('type', 'edit')->count();
        $removed = $recent->where('type', 'del')->count();
 
        return response()->json([
            'activities' => $recent->values(),
            'counts'     => [
                'added'   => $added,
                'updated' => $updated,
                'removed' => $removed,
            ],
        ]);
    }
 
    /**
     * GET /reports/category
     * Returns item count and value breakdown per category.
     */
    public function category()
    {
        $allProducts = Product::with('category')->get();
 
        $rows = $allProducts
            ->groupBy(fn($p) => $p->category?->name ?? 'Uncategorised')
            ->map(fn($group, $name) => [
                'name'      => $name,
                'count'     => $group->count(),
                'qty'       => $group->sum('quantity'),
                'value'     => $group->sum(fn($p) => $p->quantity * $p->price),
                'low_stock' => $group->filter(fn($p) => $p->quantity <= $p->reorder_level)->count(),
            ])
            ->sortByDesc('value')
            ->values();
 
        return response()->json([
            'totals' => [
                'categories'    => $rows->count(),
                'skus'          => $allProducts->count(),
                'combined_value'=> $allProducts->sum(fn($p) => $p->quantity * $p->price),
            ],
            'rows' => $rows,
        ]);
    }
 
    /* ══════════════════════════════════════════════
       CSV EXPORTS
    ══════════════════════════════════════════════ */
 
    /**
     * GET /reports/export          — full inventory CSV
     * GET /reports/export/{type}   — per-report CSV from modal Download button
     */
    public function export(string $type = 'full'): StreamedResponse
    {
        return match ($type) {
            'summary'   => $this->csvSummary(),
            'lowstock'  => $this->csvLowstock(),
            'valuation' => $this->csvValuation(),
            'movement'  => $this->csvMovement(),
            'category'  => $this->csvCategory(),
            default     => $this->csvFull(),
        };
    }
 
    private function csvFull(): StreamedResponse
    {
        $products = Product::with(['category', 'supplier'])->orderBy('name')->get();
        $date     = now()->format('Y-m-d');
 
        return response()->streamDownload(function () use ($products) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['Product', 'SKU', 'Category', 'Qty', 'Unit Price', 'Total Value', 'Status', 'Supplier', 'Last Updated']);
            foreach ($products as $p) {
                $status = $p->quantity === 0 ? 'Out of Stock'
                        : ($p->quantity <= $p->reorder_level ? 'Low Stock' : 'OK');
                fputcsv($f, [
                    $p->name,
                    $p->sku,
                    $p->category?->name ?? '—',
                    $p->quantity,
                    number_format($p->price, 2),
                    number_format($p->quantity * $p->price, 2),
                    $status,
                    $p->supplier?->name ?? '—',
                    $p->updated_at->format('Y-m-d'),
                ]);
            }
            fclose($f);
        }, "stockwize_inventory_{$date}.csv", ['Content-Type' => 'text/csv']);
    }
 
    private function csvSummary(): StreamedResponse
    {
        $products = Product::with(['category'])->orderBy('name')->get();
        $date     = now()->format('Y-m-d');
 
        return response()->streamDownload(function () use ($products) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['Product', 'SKU', 'Category', 'Qty', 'Unit Price', 'Total Value', 'Status']);
            foreach ($products as $p) {
                $status = $p->quantity === 0 ? 'Out of Stock'
                        : ($p->quantity <= $p->reorder_level ? 'Low Stock' : 'OK');
                fputcsv($f, [
                    $p->name, $p->sku, $p->category?->name ?? '—',
                    $p->quantity, number_format($p->price, 2),
                    number_format($p->quantity * $p->price, 2), $status,
                ]);
            }
            fclose($f);
        }, "stockwize_summary_{$date}.csv", ['Content-Type' => 'text/csv']);
    }
 
    private function csvLowstock(): StreamedResponse
    {
        $items = Product::with(['category', 'supplier'])
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity')
            ->get();
        $date = now()->format('Y-m-d');
 
        return response()->streamDownload(function () use ($items) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['Product', 'Category', 'Current Qty', 'Reorder Level', 'Supplier', 'Status']);
            foreach ($items as $p) {
                fputcsv($f, [
                    $p->name, $p->category?->name ?? '—',
                    $p->quantity, $p->reorder_level,
                    $p->supplier?->name ?? '—',
                    $p->quantity === 0 ? 'Out of Stock' : 'Low Stock',
                ]);
            }
            fclose($f);
        }, "stockwize_lowstock_{$date}.csv", ['Content-Type' => 'text/csv']);
    }
 
    private function csvValuation(): StreamedResponse
    {
        $products = Product::with(['category', 'supplier'])->get();
        $date     = now()->format('Y-m-d');
        $grand    = $products->sum(fn($p) => $p->quantity * $p->price);
 
        return response()->streamDownload(function () use ($products, $grand) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['Category', 'Total Value']);
            $cats = $products->groupBy(fn($p) => $p->category?->name ?? 'Uncategorised');
            foreach ($cats as $name => $group) {
                fputcsv($f, [$name, number_format($group->sum(fn($p) => $p->quantity * $p->price), 2)]);
            }
            fputcsv($f, []);
            fputcsv($f, ['Supplier', 'Total Value', '% of Inventory']);
            $sups = $products->groupBy(fn($p) => $p->supplier?->name ?? 'Unknown');
            foreach ($sups as $name => $group) {
                $val = $group->sum(fn($p) => $p->quantity * $p->price);
                fputcsv($f, [$name, number_format($val, 2), round($val / $grand * 100, 1) . '%']);
            }
            fputcsv($f, []);
            fputcsv($f, ['Grand Total', number_format($grand, 2)]);
            fclose($f);
        }, "stockwize_valuation_{$date}.csv", ['Content-Type' => 'text/csv']);
    }
 
    private function csvMovement(): StreamedResponse
    {
        // Adjust to your actual activity log source
        $recent = Product::orderByDesc('updated_at')->limit(50)->get();
        $date   = now()->format('Y-m-d');
 
        return response()->streamDownload(function () use ($recent) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['Type', 'Product', 'Detail', 'Time']);
            foreach ($recent as $p) {
                fputcsv($f, ['edit', $p->name, "Qty: {$p->quantity}", $p->updated_at->format('Y-m-d H:i')]);
            }
            fclose($f);
        }, "stockwize_movement_{$date}.csv", ['Content-Type' => 'text/csv']);
    }
 
    private function csvCategory(): StreamedResponse
    {
        $products = Product::with('category')->get();
        $date     = now()->format('Y-m-d');
 
        return response()->streamDownload(function () use ($products) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['Category', 'SKUs', 'Total Units', 'Total Value', 'Low Stock']);
            $cats = $products->groupBy(fn($p) => $p->category?->name ?? 'Uncategorised');
            foreach ($cats as $name => $group) {
                fputcsv($f, [
                    $name,
                    $group->count(),
                    $group->sum('quantity'),
                    number_format($group->sum(fn($p) => $p->quantity * $p->price), 2),
                    $group->filter(fn($p) => $p->quantity <= $p->reorder_level)->count(),
                ]);
            }
            fclose($f);
        }, "stockwize_category_{$date}.csv", ['Content-Type' => 'text/csv']);
    }
}