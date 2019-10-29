<?php

namespace MOIREI\Vouchers\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use MOIREI\ModelData\HasData;
use MOIREI\Vouchers\Facades\Vouchers;

class Voucher extends Model
{
    use HasData;

    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_MONETARY = 'monetary';
    const TYPE_OTHER = 'other';

    protected $fillable = [
        'model_id',
        'model_type',
        'code',
        'expires_at',
        'reward_type', 'reward', 'currency_code',
        'quantity', 'is_disposable',
        'can_redeem', 'cannot_redeem', 'allow', 'deny',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expires_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'can_redeem' => 'array',
        'cannot_redeem' => 'array',
        'quantity' => 'integer',
        'is_disposable' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('vouchers.table', 'vouchers');
    }

    public function __toString()
    {
        switch($this->reward_type){
            case self::TYPE_PERCENTAGE: return "$this->reward%";
            case self::TYPE_MONETARY: return "$this->currency_code $this->reward";
        }
        return $this->reward;
    }

    /**
     * Query builder; get by code.
     *
     * @param $query
     * @return mixed
     */
    public function scopeCode($query, $code){
        return $query->where('code', $code);
    }

    /**
     * Query builder; get disposable codes.
     *
     * @param $query
     * @return mixed
     */
    public function scopeDisposable($query)
    {
        return $query->where('is_disposable', true);
    }

    /**
     * Query builder to get non-disposable codes.
     *
     * @param $query
     * @return mixed
     */
    public function scopeNotDisposable($query)
    {
        return $query->where('is_disposable', false);
    }

    /**
     * Query builder; get expired promotion codes.
     *
     * @param $query
     * @return mixed
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')->whereDate('expires_at', '<=', Carbon::now());
    }

    /**
     * Get the users who redeemed this voucher.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphedByMany
     */
    public function users()
    {
        return $this->morphedByMany(config('vouchers.user_model'), 'voucherable', config('vouchers.pivot_table', 'user_voucher'));
    }

    /**
     * Get others who redeemed this voucher. Ideally a guest model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphedByMany
     */
    public function others()
    {
        return $this->morphedByMany(config('vouchers.custom_model'), 'voucherable', config('vouchers.pivot_table', 'user_voucher'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Vourables of models that have redeemed this voucher
     *
     * @return Collection
     */
    public function related_redeemers()
    {
        return $this->hasMany(Voucherable::class);
    }

    /**
     * Check if code is disposable.
     *
     * @return bool
     */
    public function isDisposable()
    {
        return $this->is_disposable;
    }

    /**
     * Check if code is expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at ? Carbon::now()->gte($this->expires_at) : false;
    }

    /**
     * Check if code is redeemed.
     *
     * @return bool
     */
    public function isRedeemed()
    {
        $attached = $this->users()->exists() || $this->others()->exists();

        if($this->is_disposable && $attached) return true;

        if(!$this->is_disposable && $this->quantity < 1) return true;

        return false;
    }

    /**
     * Add expiry days
     *
     * @param days $reuse
     * @return self
     */
    public function days(integer $days)
    {
        $this->expires_at = today()->addDays($days);

        return $this;
    }

    /**
     * Number of times this voucher may be reused
     *
     * @param integer $reuse
     * @return self
     */
    public function reuse($reuse = 1)
    {
        $this->is_disposable = false;
        $this->quantity = $reuse+1;

        return $this;
    }

    /**
     * The currency code
     *
     * @param string $currency_code
     * @return self
     */
    public function currency($currency_code)
    {
        $this->currency_code = $currency_code;

        return $this;
    }

    /**
     * The reward type
     *
     * @param string $reward_type
     * @return self
     */
    public function rewardType($reward_type)
    {
        $this->reward_type = $reward_type;

        return $this;
    }

    /**
     * The reward
     *
     * @param double $reward
     * @return self
     */
    public function rewardValue($reward)
    {
        $this->reward = $reward;

        return $this;
    }

    /**
     * Models that are allowed to redeem this voucher
     *
     * @param array $allowed
     * @return self
     */
    public function allow(array $allowed)
    {
        $models = array();
        foreach($allowed as $allowed_model){
            if($allowed_model instanceof Model){
                array_push($models, [
                    'voucherable_type' => $allowed_model->getMorphClass(),
                    'voucherable_id' => $allowed_model->getKey(),
                ]);
            }
        }
        $this->can_redeem = $models;

        return $this;
    }

    /**
     * Models that are NOT allowed to redeem this voucher
     *
     * @param array $denied
     * @return self
     */
    public function deny(array $denied)
    {
        $models = array();
        foreach($denied as $denied_model){
            if($denied_model instanceof Model){
                array_push($models, [
                    'voucherable_type' => $denied_model->getMorphClass(),
                    'voucherable_id' => $denied_model->getKey(),
                ]);
            }
        }
        $this->cannot_redeem = $models;

        return $this;
    }

    /**
     * Models that have redeemed this voucher
     *
     * @return Collection
     */
    public function getRedeemersAttribute()
    {
        // return $this->with('related_redeemers.voucherables');

        /**
         * There's probably a more Eloquent way of retrieving the related models
         */
        return $this->related_redeemers->map(function($voucherable){
            $class = '\\'.$voucherable->voucherable_type;
            return $class::find( $voucherable->voucherable_id );
        });
    }

    /**
     * Users and Others that have redeemed this voucher
     *
     * @return Collection
     */
    public function getUsersAndOthersAttribute()
    {
        return $this->users->union($this->others)->all();
    }

    /**
     * Models that are allowed to redeem this voucher
     *
     * @return array
     */
    public function getAllowedUsersArrayAttribute(){
        $array = [];
        if(!is_array($can_redeem = $this->can_redeem)){
            $can_redeem = json_decode($can_redeem, true);
        }
        foreach($can_redeem as $user){
            if(is_string($user)){
                $user = json_decode($user, true);
            }
            array_push($array, $user);
        }
        return $array;
    }

    /**
     * Models that are NOT allowed to redeem this voucher
     *
     * @return array
     */
    public function getDisallowedUsersArrayAttribute(){
        $array = [];
        if(!is_array($cannot_redeem = $this->cannot_redeem)){
            $cannot_redeem = json_decode($cannot_redeem, true);
        }
        foreach($cannot_redeem as $user){
            if(is_string($user)){
                $user = json_decode($user, true);
            }
            array_push($array, $user);
        }
        return $array;
    }

    /**
     * Get the allows models that may redeem this voucher
     *
     * @return array
     */
    public function getAllowedModels()
    {
        $models = [];
        foreach($this->allowed_users_array as $user){
            $class = '\\'.$user['voucherable_type'];
            array_push($models,
                $class::find( $user['voucherable_id'] )
            );
        }
        return $models;
    }

    /**
     * Get the allows models that may redeem this voucher
     *
     * @return array
     */
    public function getDisallowedModels()
    {
        $models = [];
        foreach($this->disallowed_users_array as $user){
            $class = '\\'.$user['voucherable_type'];
            array_push($models,
                $class::find( $user['voucherable_id'] )
            );
        }
        return $models;
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot() {
        parent::boot();

        // when a voucher is being created
        static::creating(function(Voucher $voucher){

            if(empty($voucher->code) || is_null($voucher->code)){
                $voucher->code = Vouchers::generate(1)[0];
            }

            if(isset($voucher->allow) && is_array($voucher->allow)){
                $voucher->allow( $voucher->allow );
            }
            unset($voucher->allow);

            if(isset($voucher->deny) && is_array($voucher->deny)){
                $voucher->deny( $voucher->deny );
            }
            unset($voucher->deny);

        });

        // when a voucher is being updated
        static::updating(function(Voucher $voucher){

            if(isset($voucher->allow) && is_array($voucher->allow)){
                $voucher->allow( $voucher->allow );
            }
            unset($voucher->allow);

            if(isset($voucher->deny) && is_array($voucher->deny)){
                $voucher->deny( $voucher->deny );
            }
            unset($voucher->deny);

        });

    }

}