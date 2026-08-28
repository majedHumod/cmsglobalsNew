<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubdomainCheckController extends Controller
{
    private const RESERVED = ['www', 'admin', 'api', 'demo', 'test', 'pay', 'billing', 'support', 'app', 'mail', 'ftp'];

    public function __invoke(Request $request): JsonResponse
    {
        $slug = strtolower(trim((string) $request->query('subdomain', '')));
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug) ?? '';

        if ($slug === '') {
            return response()->json([
                'available' => false,
                'subdomain' => $slug,
                'message' => 'أدخل سب-دومين صالحاً.',
                'state' => 'empty',
            ]);
        }

        if (strlen($slug) < 3) {
            return response()->json([
                'available' => false,
                'subdomain' => $slug,
                'message' => 'يجب أن يكون السب-دومين 3 أحرف على الأقل.',
                'state' => 'invalid',
            ]);
        }

        if (strlen($slug) > 30) {
            return response()->json([
                'available' => false,
                'subdomain' => $slug,
                'message' => 'السب-دومين طويل جداً (30 حرفاً كحد أقصى).',
                'state' => 'invalid',
            ]);
        }

        if (! preg_match('/^[a-z0-9-]+$/', $slug) || str_starts_with($slug, '-') || str_ends_with($slug, '-')) {
            return response()->json([
                'available' => false,
                'subdomain' => $slug,
                'message' => 'استخدم أحرفاً صغيرة وأرقاماً وشرطة (-) فقط، دون أن يبدأ أو ينتهي بشرطة.',
                'state' => 'invalid',
            ]);
        }

        if (in_array($slug, self::RESERVED, true)) {
            return response()->json([
                'available' => false,
                'subdomain' => $slug,
                'message' => 'هذا السب-دومين محجوز ولا يمكن استخدامه.',
                'state' => 'reserved',
            ]);
        }

        $domain = config('app.domain', 'etoscoach.com');
        $taken = Tenant::on('system')->where(function ($query) use ($slug, $domain) {
            $query->where('subdomain', $slug)
                ->orWhere('domain', $slug.'.'.$domain);
        })->exists();

        if ($taken) {
            return response()->json([
                'available' => false,
                'subdomain' => $slug,
                'message' => 'هذا السب-دومين مستخدم بالفعل. جرّب اسماً آخر.',
                'state' => 'taken',
                'preview_url' => $slug.'.'.$domain,
            ]);
        }

        return response()->json([
            'available' => true,
            'subdomain' => $slug,
            'message' => 'متاح — سيكون رابطك: '.$slug.'.'.$domain,
            'state' => 'available',
            'preview_url' => $slug.'.'.$domain,
        ]);
    }
}
