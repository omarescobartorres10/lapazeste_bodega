<?php

namespace Tests\Feature;

use App\Http\Middleware\PreventDuplicateWrites;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SystemCorrectionsTest extends TestCase
{
    public function test_login_does_not_query_inventory_and_cannot_be_cached(): void
    {
        $this->get('/login')->assertOk()->assertHeader('Pragma', 'no-cache');
    }

    public function test_requester_expires_after_thirty_minutes(): void
    {
        $this->withSession(['solicitante_id' => 1, 'last_activity' => time() - 1800])
            ->get('/solicitud/tipo')->assertRedirect(route('solicitud.login'));
        $this->assertFalse(session()->has('solicitante_id'));
    }

    public function test_active_requester_keeps_access(): void
    {
        $this->withSession(['solicitante_id' => 1, 'last_activity' => time() - 1790])
            ->get('/solicitud/tipo')->assertOk();
    }

    public function test_logout_invalidates_requester_and_admin_session_data(): void
    {
        $this->withSession(['solicitante_id' => 1, 'tipo_movimiento' => 'SALIDA'])
            ->post('/logout')->assertRedirect('/login');
        $this->assertFalse(session()->has('solicitante_id'));
        $this->get('/panel')->assertRedirect('/login');
        $this->get('/solicitud/tipo')->assertRedirect(route('solicitud.login'));
    }

    public function test_pdf_is_served_without_public_storage_link(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('solicitudes/solicitud-123.pdf', '%PDF-1.4 test');
        $url = URL::temporarySignedRoute('solicitud.pdf', now()->addMinutes(30), ['id' => 123]);
        $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->get('/solicitud/pdf/123')->assertForbidden();
        $this->get(str_replace('/123?', '/124?', $url))->assertForbidden();
    }

    public function test_expired_pdf_link_is_rejected(): void
    {
        $url = URL::temporarySignedRoute('solicitud.pdf', now()->subMinute(), ['id' => 123]);
        $this->get($url)->assertForbidden();
    }

    public function test_unauthenticated_submission_does_not_create_request(): void
    {
        $this->postJson('/solicitud/enviar')->assertUnauthorized();
    }

    public function test_repeated_write_executes_only_once(): void
    {
        $calls = 0;
        $session = app('session.store');
        $session->start();
        $middleware = new PreventDuplicateWrites;
        $request = \Illuminate\Http\Request::create('/test-write', 'POST', ['name' => uniqid()]);
        $request->setLaravelSession($session);
        $next = function () use (&$calls) {
            $calls++;
            return response()->json(['success' => true]);
        };
        $middleware->handle($request, $next);
        $middleware->handle($request, $next);
        $this->assertSame(1, $calls);
    }

    public function test_failed_write_can_be_retried(): void
    {
        $calls = 0;
        $session = app('session.store');
        $session->start();
        $request = \Illuminate\Http\Request::create('/test-write', 'POST', ['name' => uniqid()]);
        $request->setLaravelSession($session);
        $middleware = new PreventDuplicateWrites;
        $next = function () use (&$calls) {
            $calls++;
            return response()->json(['message' => 'Invalid'], 422);
        };
        $middleware->handle($request, $next);
        $middleware->handle($request, $next);
        $this->assertSame(2, $calls);
    }
}
