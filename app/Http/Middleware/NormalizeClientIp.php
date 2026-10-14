<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Normalisasi IP klien di balik proxy hosting (Railway).
 *
 * Kenapa perlu: Railway tidak menerbitkan rentang IP proxy yang stabil, jadi
 * `trustProxies(at: '*')` tetap dipakai agar deteksi HTTPS (X-Forwarded-Proto)
 * dan pembentukan URL benar. Efek sampingnya, Laravel membaca IP klien dari
 * nilai paling kiri `X-Forwarded-For` — nilai yang bisa dipalsukan klien untuk
 * mendapat "bucket" rate limit baru dan melewati `throttle` (mis. booking kamar).
 *
 * Railway menjamin edge HTTP publiknya menimpa `X-Real-Ip` dengan IP klien asli
 * (menimpa juga `X-Forwarded-For`, tetapi `X-Forwarded-For` mentah tetap tidak
 * boleh dipercaya begitu saja). Middleware ini menjadikan `X-Real-Ip` sebagai
 * satu-satunya sumber IP klien, sehingga pemalsuan `X-Forwarded-For` tidak lagi
 * memengaruhi `$request->ip()` yang dipakai throttle.
 */
class NormalizeClientIp
{
    public function handle(Request $request, Closure $next): Response
    {
        $realIp = $request->header('X-Real-Ip');

        if (is_string($realIp)) {
            $realIp = trim($realIp);

            // Hanya percaya bila benar-benar format IP yang valid dan bukan
            // rentang privat/CGNAT (proxy internal), agar nilai sampah atau
            // alamat internal tidak menjadi IP klien.
            if (
                filter_var($realIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false
            ) {
                // Samakan rantai X-Forwarded-For dengan IP tepercaya dari edge
                // supaya Laravel tidak membaca nilai yang bisa dipalsukan klien.
                $request->headers->set('X-Forwarded-For', $realIp);
            }
        }

        return $next($request);
    }
}
