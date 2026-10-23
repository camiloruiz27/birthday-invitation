<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Express (demo de feria): salas, jugadores, contactos y la actividad de cada
 * ronda. Todo propio del modulo (prefijo express_), nada se cuelga de las
 * tablas del juego pago.
 *
 * Los eventos NO tienen tabla: viven en config/express.php; aqui solo se guarda
 * su slug.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Quien dejo un contacto y autorizo que lo usemos. El contacto va
        // cifrado; `contact_hash` (HMAC) solo sirve para no repetir a la misma
        // persona dentro de un evento sin tener que descifrar nada.
        Schema::create('express_leads', function (Blueprint $table) {
            $table->id();
            $table->string('event_slug', 60);
            $table->string('nickname', 24);
            $table->string('contact_type', 12);
            $table->text('contact');
            $table->char('contact_hash', 64);
            $table->timestamp('consent_at');
            $table->string('consent_version', 24);
            $table->json('attribution')->nullable();
            $table->timestamps();

            $table->unique(['event_slug', 'contact_hash']);
        });

        Schema::create('express_rooms', function (Blueprint $table) {
            $table->id();
            $table->char('code', 5)->unique();
            $table->string('event_slug', 60)->index();
            $table->string('case_slug', 80);
            // lobby -> running -> finished (o expired si nadie la termino).
            $table->string('status', 12)->default('lobby')->index();
            $table->unsignedBigInteger('gm_player_id')->nullable();
            // Milisegundos desde epoch: la demo se mide en segundos y la columna
            // timestamp de MySQL (sin fraccion) perderia hasta un segundo.
            $table->unsignedBigInteger('started_at_ms')->nullable();
            $table->timestamp('revealed_at')->nullable();
            $table->unsignedBigInteger('next_room_id')->nullable();
            // Sube con cada escritura. La instantanea en cache lo guarda para
            // que una reconstruccion lenta nunca pise a una mas nueva.
            $table->unsignedInteger('rev')->default(0);
            $table->timestamps();
        });

        Schema::create('express_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('express_rooms')->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('express_leads')->nullOnDelete();
            // El token de la URL ES la credencial del jugador (como /jugador/{token}).
            $table->char('token', 36)->unique();
            $table->string('nickname', 24);
            // A o B: decide que pistas privadas recibe. Alterna por orden de union.
            $table->char('audience', 1);
            $table->boolean('is_gm')->default(false);
            $table->unsignedTinyInteger('joined_order');
            $table->timestamps();

            $table->index('room_id');
        });

        // Cada pregunta del equipo. El presupuesto se cuenta aqui, y una fila
        // sin respuesta (`answer` null) es una pregunta EN CURSO: ya gasto su
        // lugar pero la IA todavia no contesta (la fila se guarda antes de la
        // llamada para que dos celulares no gasten la misma pregunta, y se
        // borra si la IA falla y no hay respuesta de autor que dar).
        // `topic_id` es el tema del caso al que se parecio la pregunta, si hubo
        // uno; `source` dice quien contesto: la IA o el texto del autor.
        Schema::create('express_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('express_rooms')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('express_players')->cascadeOnDelete();
            $table->string('suspect_id', 40);
            $table->string('topic_id', 40)->nullable();
            $table->string('question', 200);
            $table->text('answer')->nullable();
            $table->string('source', 10)->default('authored');
            $table->timestamps();

            $table->index(['room_id', 'suspect_id']);
        });

        // Pistas privadas que un jugador decidio mostrar al grupo.
        Schema::create('express_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('express_rooms')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('express_players')->cascadeOnDelete();
            $table->string('evidence_id', 8);
            $table->timestamps();

            $table->unique(['room_id', 'evidence_id']);
        });

        // Se guardan INDICES de las opciones del caso, no el texto: el cliente
        // recibe ids opacos y barajados, nunca el orden del archivo (donde la
        // correcta es siempre la primera).
        Schema::create('express_accusations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('express_rooms')->cascadeOnDelete();
            $table->foreignId('player_id')->unique()->constrained('express_players')->cascadeOnDelete();
            $table->string('who_id', 40);
            $table->unsignedTinyInteger('how_index');
            $table->unsignedTinyInteger('why_index');
            $table->unsignedBigInteger('submitted_ms');
            $table->unsignedSmallInteger('score_who')->nullable();
            $table->unsignedSmallInteger('score_how')->nullable();
            $table->unsignedSmallInteger('score_why')->nullable();
            $table->unsignedSmallInteger('score_total')->nullable();
            $table->timestamps();

            $table->index('room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('express_accusations');
        Schema::dropIfExists('express_shares');
        Schema::dropIfExists('express_questions');
        Schema::dropIfExists('express_players');
        Schema::dropIfExists('express_rooms');
        Schema::dropIfExists('express_leads');
    }
};
