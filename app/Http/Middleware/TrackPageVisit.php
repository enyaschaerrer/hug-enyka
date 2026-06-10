<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->cookie('hug_analytics_consent') !== '1') {
            return $response;
        }

        DB::table('page_visits')->insert([
            'ip_hash'    => hash('sha256', $request->ip()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $response;
    }
}
