<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_bento_cards', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('title', 160);
            $table->string('image_url', 2000);
            $table->string('alt', 160);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('landing_bento_cards')->insert([
            [
                'key' => 'architecture',
                'title' => 'Arquitetura Escalável',
                'image_url' => 'https://images.unsplash.com/photo-2JJ3wBHu4_0?q=80&w=1200&auto=format&fit=crop',
                'alt' => 'Arquitetura escalável',
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'speed',
                'title' => 'Velocidade Real',
                'image_url' => 'https://images.unsplash.com/photo-Ib2e4-Qy9mQ?q=80&w=1200&auto=format&fit=crop',
                'alt' => 'Velocidade e performance',
                'sort_order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'ai',
                'title' => 'IA Nativa',
                'image_url' => 'https://images.unsplash.com/photo-zips8ILZd04?q=80&w=1200&auto=format&fit=crop',
                'alt' => 'Inteligência artificial',
                'sort_order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'design',
                'title' => 'Design System',
                'image_url' => 'https://images.unsplash.com/photo-qaedPly-Uro?q=80&w=1200&auto=format&fit=crop',
                'alt' => 'Design system',
                'sort_order' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'mobile',
                'title' => 'Mobile-First Real',
                'image_url' => 'https://images.unsplash.com/photo-BlWbfrQrI5k?q=80&w=1200&auto=format&fit=crop',
                'alt' => 'Mobile-first',
                'sort_order' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'security',
                'title' => 'Segurança Enterprise',
                'image_url' => 'https://images.unsplash.com/photo-RMIsZlv8qv4?q=80&w=1200&auto=format&fit=crop',
                'alt' => 'Segurança e autenticação',
                'sort_order' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_bento_cards');
    }
};

