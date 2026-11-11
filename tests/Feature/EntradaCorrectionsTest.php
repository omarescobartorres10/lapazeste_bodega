<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EntradaCorrectionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (['departamentos', 'categorias', 'bodegas'] as $table) {
            Schema::create($table, fn (Blueprint $t) => $t->id());
            DB::table($table)->insert(['id' => 1]);
        }
        Schema::create('productos', function (Blueprint $t) {
            $t->id();
            $t->integer('stock')->default(0);
            $t->timestamps();
        });
        DB::table('productos')->insert(['id' => 1, 'stock' => 0]);
        Schema::create('entradas', function (Blueprint $t) {
            $t->id();
            foreach (['departamento_id', 'categoria_id', 'producto_id', 'bodega_id', 'cantidad', 'estado'] as $field) {
                $t->integer($field);
            }
            foreach (['recibido_por', 'entregado_por', 'observacion', 'codigo_operacion', 'numero_factura', 'numero_orden_compra', 'factura_pdf'] as $field) {
                $t->string($field)->nullable();
            }
            $t->timestamps();
        });
        $user = new User;
        $user->id = 1;
        $user->bodega_id = 1;
        $this->actingAs($user);
    }

    public function test_entry_preserves_free_text_without_pdf_and_does_not_duplicate_stock(): void
    {
        $payload = [
            'recibido_por' => 'Receptor', 'entregado_por' => 'Proveedor',
            'numero_factura' => 'Fac-aB/2026 #&+ñ', 'numero_orden_compra' => 'OC-x.1/@2026',
            'productos' => [['departamento_id' => 1, 'categoria_id' => 1, 'producto_id' => 1, 'cantidad' => 4]],
        ];
        $this->post('/entradas', $payload)->assertRedirect(route('entradas'))->assertSessionHasNoErrors();
        $this->post('/entradas', $payload)->assertRedirect(route('entradas'));
        $this->assertSame(1, DB::table('entradas')->count());
        $this->assertSame(4, DB::table('productos')->value('stock'));
        $this->assertDatabaseHas('entradas', ['numero_factura' => $payload['numero_factura'], 'numero_orden_compra' => $payload['numero_orden_compra'], 'factura_pdf' => null]);
    }

    public function test_cancelling_twice_does_not_decrement_stock_twice(): void
    {
        DB::table('productos')->where('id', 1)->update(['stock' => 10]);
        DB::table('entradas')->insert(['departamento_id' => 1, 'categoria_id' => 1, 'producto_id' => 1, 'bodega_id' => 1, 'cantidad' => 4, 'estado' => 1, 'codigo_operacion' => 'ENT-TEST']);
        $controller = app(\App\Http\Controllers\EntradaController::class);
        $controller->destroy('ENT-TEST');
        $controller->destroy('ENT-TEST');
        $this->assertSame(6, DB::table('productos')->value('stock'));
        $this->assertSame(0, DB::table('entradas')->value('estado'));
    }

    public function test_cancellation_cannot_make_stock_negative(): void
    {
        DB::table('entradas')->insert(['departamento_id' => 1, 'categoria_id' => 1, 'producto_id' => 1, 'bodega_id' => 1, 'cantidad' => 4, 'estado' => 1, 'codigo_operacion' => 'ENT-TEST']);
        $result = app(\App\Http\Controllers\EntradaController::class)->destroy('ENT-TEST');
        $this->assertFalse($result->getData(true)['success']);
        $this->assertSame(0, DB::table('productos')->value('stock'));
        $this->assertSame(1, DB::table('entradas')->value('estado'));
    }
}
