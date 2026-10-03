<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin');
        });

        Schema::create('elections', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('institution');
            $table->string('organization')->nullable();
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->string('timezone')->default('Africa/Lubumbashi');
            $table->string('status')->default('draft');
            $table->string('voting_type')->default('candidates');
            $table->boolean('results_visible_before_close')->default(false);
            $table->boolean('is_public_default')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['election_id', 'name']);
            $table->index(['election_id', 'display_order']);
        });

        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('post_name')->nullable();
            $table->string('matricule')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('biography')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['election_id', 'position_id']);
            $table->index(['position_id', 'status']);
        });

        Schema::create('voters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->string('matricule');
            $table->string('last_name');
            $table->string('post_name')->nullable();
            $table->string('first_name');
            $table->string('sex', 16)->nullable();
            $table->string('faculty')->nullable();
            $table->string('promotion')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->default('eligible');
            $table->boolean('has_voted')->default(false);
            $table->timestamp('voted_at')->nullable();
            $table->timestamps();

            $table->unique(['election_id', 'matricule']);
            $table->index(['election_id', 'has_voted']);
        });

        Schema::create('participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->foreignId('voter_id')->constrained()->restrictOnDelete();
            $table->string('receipt_code')->unique();
            $table->timestamp('voted_at');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['election_id', 'voter_id']);
            $table->index('election_id');
        });

        Schema::create('ballots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->timestamp('cast_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index('election_id');
        });

        Schema::create('ballot_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ballot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained()->restrictOnDelete();
            $table->foreignId('candidate_id')->constrained()->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['ballot_id', 'position_id']);
            $table->index('candidate_id');
            $table->index('position_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ballot_choices');
        Schema::dropIfExists('ballots');
        Schema::dropIfExists('participations');
        Schema::dropIfExists('voters');
        Schema::dropIfExists('candidates');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('elections');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
