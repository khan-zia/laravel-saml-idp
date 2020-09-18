<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->string('namespace')->nullable()->default(null);
            $table->string('name');
            $table->string('entity_id');
            $table->string('acs_url');
            $table->boolean('want_assertions_signed')->default(1);
            $table->boolean('want_response_encrypted')->default(0);
            $table->text('x509')->nullable()->default(null);
            $table->enum('nameid_format', [
                'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
                'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
                'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
                'urn:oasis:names:tc:SAML:1.1:nameid-format:X509SubjectName',
                'urn:oasis:names:tc:SAML:1.1:nameid-format:WindowsDomainQualifiedName',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:kerberos',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:entity',
            ])->default('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent');
            $table->enum('binding', ['post', 'redirect'])->default('post');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_providers');
    }
}
