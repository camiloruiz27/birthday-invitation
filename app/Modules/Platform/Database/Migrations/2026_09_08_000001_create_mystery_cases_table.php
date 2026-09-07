<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The sellable catalog. Narrative content stays in the case manifests
     * (versioned in git, diffable, no CMS needed); this table holds only what
     * has to be queried or joined — pricing, publication state and the
     * shopper-facing summary.
     *
     * `slug` is the join key back to the engine: a Game stores case_slug, not
     * a foreign key into this table, so the engine keeps running even if the
     * catalog row does not exist yet.
     */
    public function up(): void
    {
        Schema::create('mystery_cases', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            // Content-derived: overwritten on every sync from the manifest.
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('difficulty')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedTinyInteger('min_players')->nullable();
            $table->unsignedTinyInteger('max_players')->nullable();
            $table->json('mechanics')->nullable();
            $table->string('content_version')->default('1.0');

            // Commercial: seeded once, then owned by the platform so a price
            // change never needs a deploy (and a sync never clobbers it).
            $table->unsignedInteger('price_amount')->default(0);
            $table->char('currency', 3)->default('COP');
            $table->timestamp('published_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            // Catalog listing: published cases in display order.
            $table->index(['published_at', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mystery_cases');
    }
};
