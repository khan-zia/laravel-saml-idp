<?php

namespace ZiaKhan\SamlIdp\Modals;

use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'service_providers';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];
}
