<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSamlClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // disable foreign key constraints to allow table to be created
        Schema::disableForeignKeyConstraints();

        Schema::create('user_saml_clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('service_provider_id');
            $table->longText('subject_metadata')->nullable()->default(null);
            $table->string('entity_id')->nullable()->default(null);
            $table->string('acs_url')->nullable()->default(null);
            $table->string('relay_state')->nullable()->default(null);
            $table->timestamp('last_logged_in')->nullable()->default(null);
            $table->boolean('revoked')->default(0);
            $table->timestamps();
        });

        // create indexes and foreign key constraints
        Schema::table('user_saml_clients', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('service_provider_id')->references('id')->on('service_providers')->onDelete('cascade');
            $table->index('user_id');
            $table->index('service_provider_id');
        });

        // enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // disable foreign key constraints to allow table to be created
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('user_saml_clients');

        // enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }
}
