<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
    public $timestamps = false;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        "pivot_aa_provider_fee"
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function attendantEvents()
    {
        return $this->belongsToMany('App\Models\Event', 'i_user_event', 'user_id', 'event_id');
    }

    public function notifications()
    {
        return $this->belongsToMany('App\Models\AlertMessage', 'aa_alert_message_recipient', 'user_id', 'premise_id');
    }

    public function sentAlerts()
    {
        return $this->belongsToMany('App\Models\AlertMessage', 'aa_alert_message_recipient', 'user_id', 'premise_id');
    }

    public function receivedAlerts()
    {
        return $this->belongsToMany('App\Models\AlertMessage', 'aa_alert_message_recipient', 'recipient_id', 'alert_message_id');
    }

    public function householdAlertRecipients()
    {
        return $this->belongsToMany('App\Models\User', 'aa_user_household_alert_recipient', 'created_by', 'recipient_id');
    }

    public function alertRecipients()
    {
        return $this->belongsToMany(
            'App\Models\User',
            'aa_user_alert_recipient',
            'user_id',
            'recipient_id'
        )->withPivot(['theft_alert', 'medical_alert', 'gbv_alert', 'sender_contact_name', 'recipient_contact_name', 'cyclone_alert',]);
    }

    public function alertSenders()
    {
        return $this->belongsToMany(
            'App\Models\User',
            'aa_user_alert_recipient',
            'recipient_id',
            'user_id'
        )->withPivot(['theft_alert', 'medical_alert', 'gbv_alert', 'sender_contact_name', 'recipient_contact_name', 'cyclone_alert']);
    }

    public function subscriptions() {
        return $this->belongsToMany(
            'App\Models\ServiceProviderFee', 'aa_user_subscriptions',
             'aa_user_id','aa_provider_fee')
            ->withPivot(["start_date", "end_date", "is_trial"]);
    }

    public static function sentRecipientRequest($user_id) {
        $sql = "
            SELECT urr.phone, urr.created_at request_date, urr.status
            FROM aa_user_recipients_requests urr
            WHERE requesting_user_id = {$user_id}";
        return DB::select($sql);
    }

    public static function receivedRecipientRequest($user_phone) {
        $sql = "
            SELECT u.name, u.phone, urr.created_at request_date, urr.status
            FROM aa_user_recipients_requests urr
            INNER JOIN users u ON u.id = urr.requesting_user_id
            WHERE urr.phone = {$user_id}";
        return DB::select($sql);
    }

    public function toArray()
    {
        $attributes = $this->attributesToArray();
        $attributes = array_merge($attributes, $this->relationsToArray());
//        foreach ($columns as $column) {
//            $attributes[$column] = $attributes['pivot'][$column];
//        }
        unset($attributes['pivot']);
//        unset($attributes['pivot']['created_at']);
        return $attributes;
    }
}


//select `aa_provider_fees`.*, `aa_user_subscriptions`.`aa_user_id` as `pivot_aa_user_id`, `aa_user_subscriptions`.`aa_provider_fee` as `pivot_aa_provider_fee`, `aa_user_subscriptions`.`start_date` as `pivot_start_date`, `aa_user_subscriptions`.`end_date` as `pivot_end_date` from `aa_provider_fees` inner join `aa_user_subscriptions` on `aa_provider_fees`.`id` = `aa_user_subscriptions`.`aa_provider_fee` where `aa_user_subscriptions`.`aa_user_id` = 74 and (`status` = paid)
