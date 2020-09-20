<?php

namespace ZiaKhan\SamlIdp\Modals;

use Illuminate\Database\Eloquent\Model;

class UserSamlClient extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_saml_clients';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Date attributes that should be parsed to a Carbon instance
     *
     * @var array
     */
    protected $dates = ['last_logged_in'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'subject_metadata',
    ];

    /**
     * Get the user that the saml client belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get the service provider that the saml client belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function serviceProvider()
    {
        return $this->belongsTo('ZiaKhan\SamlIdp\Modals\ServiceProvider');
    }
}
