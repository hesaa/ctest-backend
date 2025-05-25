<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $env = App::environment();
        if ($env == 'local') {
            return $next($request);
        }
        
        if ($request->hasHeader('X-Signature')) {
            $signature_request = $request->header('X-Signature');
        }else{
            $signature_request = null;
        }
        $method = $request->method();
        $uri = $request->getRequestUri();
        $data = json_encode($request->all());
        $salt = '';
        $signature = password_hash(md5($method.$uri.$data.$salt), PASSWORD_DEFAULT);
        if (password_verify($signature_request, $signature)) {
            return $next($request);
        }

        $status = 403;
        abort(response()->json([
            'status' => $status,
            'errors' => ['You do not have permission to access for this service.']
        ], $status));
    }
}
