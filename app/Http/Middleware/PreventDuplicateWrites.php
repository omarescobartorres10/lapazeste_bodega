<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PreventDuplicateWrites
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethodSafe() || $request->is('login', 'logout', 'solicitud/login', 'solicitud/logout')) {
            return $next($request);
        }

        // File locks work without requiring a cache table in the application database.
        $cache = Cache::store('file');
        $payload = \Illuminate\Support\Arr::except($request->input(), ['_token']);
        foreach (\Illuminate\Support\Arr::dot($request->allFiles()) as $name => $file) {
            $payload['_files'][$name] = $file->isValid() ? hash_file('sha256', $file->getRealPath()) : 'invalid';
        }
        $actor = $request->user() ? 'user:'.$request->user()->getAuthIdentifier()
            : ($request->session()->has('solicitante_id') ? 'solicitante:'.$request->session()->get('solicitante_id') : 'session:'.$request->session()->getId());
        $key = 'write:'.hash('sha256', $actor.'|'.$request->method().'|'.$request->path().'|'.serialize($payload));
        $lock = $cache->lock($key.':lock', 120);
        if (!$lock->get()) {
            return response()->json(['message' => 'La operación ya se está procesando.', 'error' => 'La operación ya se está procesando.'], 409);
        }
        try {
            if ($saved = $cache->get($key)) {
                return response($saved['body'], $saved['status'], $saved['headers']);
            }
            $response = $next($request);
            $data = $response instanceof \Illuminate\Http\JsonResponse ? $response->getData(true) : [];
            $failed = ($data['success'] ?? true) === false || ($data['ok'] ?? true) === false;
            if (!$failed && ($response->isSuccessful() || ($response->isRedirection() && !$request->session()->has('errors') && !$request->session()->has('error')))) {
                $cache->put($key, ['body' => $response->getContent(), 'status' => $response->getStatusCode(), 'headers' => $response->headers->all()], 5);
            }
            return $response;
        } finally {
            $lock->release();
        }
    }
}
