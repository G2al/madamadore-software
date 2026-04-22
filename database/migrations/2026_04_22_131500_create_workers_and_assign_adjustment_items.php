<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('workers')) {
            Schema::create('workers', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('adjustments', function (Blueprint $table): void {
            if (! Schema::hasColumn('adjustments', 'primary_worker_id')) {
                $table->foreignId('primary_worker_id')
                    ->nullable()
                    ->after('referente')
                    ->constrained('workers')
                    ->nullOnDelete();
            }
        });

        Schema::table('company_adjustments', function (Blueprint $table): void {
            if (! Schema::hasColumn('company_adjustments', 'primary_worker_id')) {
                $table->foreignId('primary_worker_id')
                    ->nullable()
                    ->after('referente')
                    ->constrained('workers')
                    ->nullOnDelete();
            }
        });

        Schema::table('adjustment_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('adjustment_items', 'worker_id')) {
                $table->foreignId('worker_id')
                    ->nullable()
                    ->after('description')
                    ->constrained('workers')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('adjustment_items', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('worker_id');
            }
        });

        Schema::table('company_adjustment_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('company_adjustment_items', 'worker_id')) {
                $table->foreignId('worker_id')
                    ->nullable()
                    ->after('description')
                    ->constrained('workers')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('company_adjustment_items', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('worker_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_adjustment_items', function (Blueprint $table): void {
            if (Schema::hasColumn('company_adjustment_items', 'worker_id')) {
                $table->dropConstrainedForeignId('worker_id');
            }

            if (Schema::hasColumn('company_adjustment_items', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });

        Schema::table('adjustment_items', function (Blueprint $table): void {
            if (Schema::hasColumn('adjustment_items', 'worker_id')) {
                $table->dropConstrainedForeignId('worker_id');
            }

            if (Schema::hasColumn('adjustment_items', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });

        Schema::table('company_adjustments', function (Blueprint $table): void {
            if (Schema::hasColumn('company_adjustments', 'primary_worker_id')) {
                $table->dropConstrainedForeignId('primary_worker_id');
            }
        });

        Schema::table('adjustments', function (Blueprint $table): void {
            if (Schema::hasColumn('adjustments', 'primary_worker_id')) {
                $table->dropConstrainedForeignId('primary_worker_id');
            }
        });

        Schema::dropIfExists('workers');
    }
};
