<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_vault_document_sequences', function (Blueprint $table) {
            $table->foreignId('project_id')->primary()->constrained('projects')->cascadeOnDelete();
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();
        });

        // Preserve any RH numbers already issued before this migration.
        // Older manually entered numbers remain untouched.
        $documents = DB::table('client_vault_documents')
            ->select('project_id', 'document_number')->get();
        $maxByProject = [];
        foreach ($documents as $document) {
            if (preg_match('/^RH-DOC-(\d+)$/', $document->document_number, $matches)) {
                $projectId = (int) $document->project_id;
                $maxByProject[$projectId] = max($maxByProject[$projectId] ?? 0, (int) $matches[1]);
            }
        }
        foreach ($maxByProject as $projectId => $lastNumber) {
            DB::table('client_vault_document_sequences')->insert([
                'project_id' => $projectId,
                'last_number' => $lastNumber,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_vault_document_sequences');
    }
};
