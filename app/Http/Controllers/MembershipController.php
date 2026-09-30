<?php

namespace App\Http\Controllers;

use App\Services\MyadsApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MembershipController extends Controller
{
    /**
     * Inisiasi join membership — panggil API backend, kembalikan JSON ke frontend.
     */
    public function join(Request $request, MyadsApiClient $client): JsonResponse
    {
        $validated = $request->validate([
            'customer_phone' => ['required', 'string', 'max:32'],
            'customer_email' => ['nullable', 'email', 'max:255'],
        ]);

        $accessToken = (string) $request->session()->get('myads.access_token', '');
        if ($accessToken === '') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Resolusi email & name dari session
        $user = $request->user();
        $sessionUser = $request->session()->get('myads.user', []);
        $email = (string) ($validated['customer_email'] ?? '');
        if (trim($email) === '') {
            $email = is_object($user) ? (string) ($user->email ?? '') : '';
        }
        if (trim($email) === '' && is_array($sessionUser)) {
            $email = (string) ($sessionUser['email'] ?? '');
        }
        if (trim($email) === '') {
            $email = 'myads@telkomsel.com';
        }

        $name = is_object($user) ? (string) ($user->name ?? '') : '';
        if (trim($name) === '' && is_array($sessionUser)) {
            $name = (string) ($sessionUser['name'] ?? '');
        }

        // Resolusi phone
        $rawPhone = (string) ($validated['customer_phone'] ?? '');
        if (trim($rawPhone) === '' && is_array($sessionUser)) {
            $rawPhone = (string) ($sessionUser['msisdn'] ?? $sessionUser['phone'] ?? $sessionUser['mobile'] ?? '');
        }
        // Normalize phone
        $phone = $this->normalizeMsisdn($rawPhone);
        if ($phone === null) {
            throw ValidationException::withMessages([
                'customer_phone' => 'Masukkan nomor telepon Indonesia yang valid, misalnya 081234567890.',
            ]);
        }

        try {
            $result = $client->joinMembership($accessToken, [
                'customer_phone'   => $phone,
                'customer_email'   => $email,
                'customer_name'    => trim($name) !== '' ? $name : $email,
                'product_category' => 'MYADS',
                'product_type'     => 'MYADS_MEMBERSHIP',
                'product_detail'   => 'ShopAds Membership - 30 Hari (Unlimited SMS, Maks. 5.000 Penerima/Campaign)',
                'email_invoice'    => $email,
                'send_invoice'     => true,
            ]);

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Cek status membership user saat ini.
     */
    public function status(Request $request, MyadsApiClient $client): JsonResponse
    {
        $accessToken = (string) $request->session()->get('myads.access_token', '');
        if ($accessToken === '') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $result = $client->getMembershipStatus($accessToken);
            if (($result['success'] ?? false) === true) {
                $request->session()->put('myads.membership', (array) ($result['data'] ?? []));
            }
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Normalize MSISDN ke format 628xxx.
     */
    private function normalizeMsisdn(string $phone): ?string
    {
        $phone = preg_replace('/[\s()\-]/', '', trim($phone));
        if (! preg_match('/^(?:\+?62|0)?8[0-9]{8,11}$/', $phone)) {
            return null;
        }
        $phone = ltrim($phone, '+');
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }
        return $phone;
    }
}
