<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\DummyVoucherReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request, DummyVoucherReport $demo): RedirectResponse
    {
        $request->merge([
            'code' => is_string($request->input('code'))
                ? strtoupper(trim($request->input('code')))
                : $request->input('code'),
        ]);

        $validated = $request->validate([
            'code' => ['required', 'string', 'regex:/\A[A-Z0-9]{6,8}\z/'],
        ], [
            'code.required' => 'Kode voucher wajib diisi.',
            'code.string' => 'Kode voucher harus berupa 6-8 karakter huruf atau angka.',
            'code.regex' => 'Kode voucher harus berupa 6-8 karakter huruf atau angka.',
        ]);

        $package = config('vouchers.'.$validated['code']);
        if (! is_array($package)) {
            throw ValidationException::withMessages(['code' => 'Kode voucher tidak ditemukan. Periksa kembali kode Anda.']);
        }

        $request->session()->regenerate();
        $request->session()->forget('myads');
        $request->session()->forget(['voucher_campaign', 'voucher_report']);
        $request->session()->put('voucher', array_merge($package, ['code' => $validated['code']]));

        if ($package['report_demo'] ?? false) {
            $data = $demo->create($validated['code'], $package);
            $request->session()->put('voucher_campaign', $data['campaign']);
            $request->session()->put('voucher_report', $data['report']);
        }

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
