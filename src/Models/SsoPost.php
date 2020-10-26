<?php

namespace ZiaKhan\SamlIdp\Models;

use Illuminate\Database\Eloquent\Model;

class SsoPost extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sso_posts';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];
}
