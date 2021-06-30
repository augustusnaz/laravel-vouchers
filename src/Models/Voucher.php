<?php

namespace MOIREI\Vouchers\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use MOIREI\ModelData\HasData;
use MOIREI\Vouchers\Facades\Vouchers;

class Voucher extends Model
{
    use HasData;

    const LIMIT_INSTANCE = 'limit-per-instance';
    const LIMIT_ITEM = 'limit-per-item';
    const LIMIT_REDEEMER = 'limit-per-redeemer';

    protected $fillable = [
        'code', 'expires_at',
        'quantity', 'quantity_used', 'limit_scheme', 'value',
        'can_redeem', 'cannot_redeem', 'allow_models', 'deny_models',
        'data',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expires_at' => 'datetime',
        'can_redeem' => 'array',
        'cannot_redeem' => 'array',
        'quantity' => 'integer',
        'quantity_used' => 'json',
        'value' => 'decimal:2',
    ];

    /**
     * ModelData: use model's column
     *
     * @var array|string|false
     */
    protected $model_data = 'data';

    /**
     * Used to track items to be associated to the voucher if the instances has not been saved
     *
     * @var array
     */
    private $queuedItems = [];

    /**
     * Construct a voucher instance
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('vouchers.table', 'vouchers');
    }

    /**
     * Make a new voucher instance
     *
     * @param array $attributes
     * @return \MOIREI\Vouchers\Models\Voucher
     */
    public static function make(array $attributes = [])
    {
        return new static($attributes);
    }

    /**
     * Query builder; get by code.
     *
     * @param $query
     * @return mixed
     */
    public function scopeCode($query, $code)
    {
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
        return $query->where('quantity', 1);
    }

    /**
     * Query builder to get non-disposable codes.
     *
     * @param $query
     * @return mixed
     */
    public function scopeNotDisposable($query)
    {
        return $query->where('quantity', '>', 1);
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
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            config('vouchers.models.users'),
            'voucherable',
            config('vouchers.tables.redeemer_pivot_table', 'redeemer_voucher')
        );
    }

    /**
     * Products related this voucher
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function products(): MorphToMany
    {
        return $this->morphedByMany(
            config('vouchers.models.products'),
            'item',
            config('vouchers.tables.item_pivot_table', 'item_voucher')
        );
    }

    /**
     * Models that have redeemed this voucher
     *
     * @return HasMany
     */
    public function related_redeemers()
    {
        return $this->hasMany(UserVoucherable::class);
    }

    /**
     * Items associated with this voucher
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(ItemVoucherable::class);
    }

    /**
     * Increment the amount used
     *
     * @param int $count
     * @param Model|null $redeemer
     * @param Model|null $item
     * @return self
     */
    public function incrementUse(int $count = 1, Model $redeemer = null, Model $item = null)
    {
        $this->setQuantityUsed(
            $this->getQuantityUsed($redeemer, $item) + $count,
            $redeemer,
            $item,
        );

        return $this;
    }

    /**
     * Limit use by
     *
     * @param string $per
     * @param int|null $quantity
     * @return self
     */
    public function limitUsePer(string $per, int|null $quantity = null)
    {
        $this->attributes['limit_scheme'] = $per;
        if (!is_null($quantity)) {
            $this->attributes['quantity'] = $quantity;
        }
        return $this;
    }

    /**
     * Limit use per instance
     *
     * @param int|null $quantity
     * @return self
     */
    public function limitUsePerInstance(int|null $quantity = null)
    {
        return $this->limitUsePer(self::LIMIT_INSTANCE, $quantity);
    }

    /**
     * Limit use per product
     *
     * @param int|null $quantity
     * @return self
     */
    public function limitUsePerItem(int|null $quantity = null)
    {
        return $this->limitUsePer(self::LIMIT_ITEM, $quantity);
    }

    /**
     * Limit use per redeemer
     *
     * @param int|null $quantity
     * @return self
     */
    public function limitUsePerRedeemer(int|null $quantity = null)
    {
        return $this->limitUsePer(self::LIMIT_REDEEMER, $quantity);
    }

    /**
     * Get the quantity used (based on limit scheme)
     *
     * @param int $quantity
     * @param Model|null $redeemer
     * @param Model|null $item
     * @return self
     */
    protected function setQuantityUsed(int $quantity, Model $redeemer = null, Model $item = null): self
    {
        $quantity_used = $this->quantity_used;

        if ($this->limit_scheme === self::LIMIT_INSTANCE) {
            Arr::set($quantity_used, 'instance', $quantity);
        } elseif ($this->limit_scheme === self::LIMIT_ITEM && $item) {
            Arr::set($quantity_used, "item.$item->id", $quantity);
        } elseif ($this->limit_scheme === self::LIMIT_REDEEMER && $redeemer) {
            $redeemer_type = $redeemer->getMorphClass();
            $redeemer_id = $redeemer->getKey();
            Arr::set($quantity_used, "redeemer.$redeemer_type:$redeemer_id", $quantity);
        } else {
            return $this;
        }

        $this->quantity_used = $quantity_used;

        return $this;
    }

    /**
     * Get the quantity used (based on limit scheme)
     *
     * @param Model|null $redeemer
     * @param Model|null $item
     * @return int
     */
    public function getQuantityUsed(Model $redeemer = null, Model $item = null): int
    {
        $quantity_used = $this->quantity_used;

        if ($this->limit_scheme === self::LIMIT_INSTANCE) {
            $used = Arr::get($quantity_used, 'instance', 0);
        } elseif ($this->limit_scheme === self::LIMIT_ITEM) {
            if ($item) {
                $used = Arr::get($quantity_used, "item.$item->id", 0);
            } else {
                // if no specific item given, the least quantity exhausts usage
                $used = collect(Arr::get($quantity_used, 'item', []))->values()->flatten()->min() ?: 0;
            }
        } elseif ($this->limit_scheme === self::LIMIT_REDEEMER) {
            if ($redeemer) {
                $redeemer_type = $redeemer->getMorphClass();
                $redeemer_id = $redeemer->getKey();
                $used = Arr::get($quantity_used, "redeemer.$redeemer_type:$redeemer_id", 0);
            } else {
                // if no specific redeemer given, the least quantity exhausts usage
                $used = collect(Arr::get($quantity_used, 'redeemer', []))->values()->flatten()->min() ?: 0;
            }
        } else {
            $used = 0;
        }

        return $used;
    }

    /**
     * Set the related items.
     *
     * @param Model|string|array $items
     * @return bool
     */
    public function setItems(Model|string|array $items): self
    {
        if (!is_array($items)) {
            $items = [$items];
        }
        if (!$this->exists) {
            $this->queuedItems = $items;
        } else {
            $default_item_class = config('vouchers.models.products');
            $items = array_map(fn ($item) => ($item instanceof Model) ? $item : $default_item_class::find($item), $items);
            $this->items()->get()->each->delete();
            foreach ($items as $item) {
                $this->morphedByMany(
                    $item->getMorphClass(),
                    'item',
                    config('vouchers.tables.item_pivot_table', 'item_voucher')
                )->save($item);
            }
        }

        return $this;
    }

    /**
     * Add to the related items.
     *
     * @param Model|string|array $items
     * @return bool
     */
    public function addItems(Model|string|array $items): self
    {
        if (!is_array($items)) {
            $items = [$items];
        }
        if (!$this->exists) {
            $this->queuedItems = array_merge($this->queuedItems, $items);
        } else {
            function getItemKey($item)
            {
                return $item->getMorphClass() . ':' . $item->getKey();
            }

            $default_item_class = config('vouchers.models.products');
            $items = array_map(fn ($item) => ($item instanceof Model) ? $item : $default_item_class::find($item), $items);
            $existing_item_keys = $this->items()->get()->map(fn ($item) => getItemKey($item->item))->toArray();

            foreach ($items as $item) {
                if (!in_array(getItemKey($item), $existing_item_keys)) {
                    $this->morphedByMany(
                        $item->getMorphClass(),
                        'item',
                        config('vouchers.tables.item_pivot_table', 'item_voucher')
                    )->save($item);
                }
            }
        }

        return $this;
    }

    /**
     * Set voucher value
     *
     * @param $value
     * @return self
     */
    public function value($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Add expiry days
     *
     * @param $reuse
     * @return self
     */
    public function days($days)
    {
        $this->expires_at = today()->addDays($days);

        return $this;
    }

    /**
     * Number of times this voucher may be reused
     *
     * @param int $reuse
     * @return self
     */
    public function reuse(int $reuse = 1)
    {
        $this->quantity = $reuse + 1;

        return $this;
    }

    /**
     * Expire the voucher
     *
     * @param Carbon|null $date
     * @return self
     */
    public function expire(Carbon|Null $date = null): self
    {
        $this->expires_at = is_null($date) ? Carbon::now() : $date;

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
        foreach ($allowed as $allowed_model) {
            if ($allowed_model instanceof Model) {
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
        foreach ($denied as $denied_model) {
            if ($denied_model instanceof Model) {
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
        return $this->related_redeemers()->get()->map(function ($entry) {
            return $entry->voucherable;
        });
    }

    /**
     * Items associated with this voucher
     *
     * @return Collection
     */
    public function getItemsAttribute()
    {
        return $this->items()->get()->map(function ($entry) {
            return $entry->item;
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
    public function getAllowedUsersArrayAttribute()
    {
        $array = [];
        if (!is_array($can_redeem = $this->can_redeem)) {
            $can_redeem = json_decode($can_redeem, true) ?? [];
        }
        foreach ($can_redeem as $user) {
            if (is_string($user)) {
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
    public function getDisallowedUsersArrayAttribute()
    {
        $array = [];
        if (!is_array($cannot_redeem = $this->cannot_redeem)) {
            $cannot_redeem = json_decode($cannot_redeem, true) ?? [];
        }
        foreach ($cannot_redeem as $user) {
            if (is_string($user)) {
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
        foreach ($this->allowed_users_array as $user) {
            $class = '\\' . $user['voucherable_type'];
            array_push(
                $models,
                $class::find($user['voucherable_id'])
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
        foreach ($this->disallowed_users_array as $user) {
            $class = '\\' . $user['voucherable_type'];
            array_push(
                $models,
                $class::find($user['voucherable_id'])
            );
        }
        return $models;
    }

    /**
     * Check if code is disposable.
     *
     * @return bool
     */
    public function isDisposable()
    {
        return $this->quantity == 1;
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
     * @param Model|null $redeemer
     * @param Model|null $item
     * @return bool
     */
    public function isRedeemed(Model $redeemer = null, Model $item = null): bool
    {
        return $this->redeemers->count() && $this->isExhausted($redeemer, $item);
    }

    /**
     * Check if the voucher redeems has been exhausted.
     *
     * @param Model|null $redeemer
     * @param Model|null $item
     * @return bool
     */
    public function isExhausted(Model $redeemer = null, Model $item = null): bool
    {
        return $this->getQuantityUsed($redeemer, $item) >= $this->quantity;
    }

    /**
     * Check if the given item is related.
     *
     * @param Model|string|int $product
     * @return bool
     */
    public function isItem(Model|string|int $item): bool
    {
        function getItemKey($item)
        {
            return $item->getMorphClass() . ':' . $item->getKey();
        }

        $default_item_class = config('vouchers.models.products');
        $item = ($item instanceof Model) ? $item : $default_item_class::find($item);
        $existing_item_keys = $this->items()->get()->map(fn ($item) => getItemKey($item->item))->toArray();

        return in_array(getItemKey($item), $existing_item_keys);
    }

    /**
     * Prune the expired vouchers.
     *
     * @param \Illuminate\Support\Carbon|int $age
     * @return void
     */
    static public function pruneExpired(Carbon | int $age = 1)
    {
        if (is_int($age)) {
            $age = Carbon::now()->subDays($age);
        }

        $query = static::expired($age)->orderBy('id', 'desc');

        $query->chunk(100, function ($vouchers) {
            $vouchers->each->delete();
        });
    }

    /**
     * Cast voucher to string by returning its code
     *
     * @return string
     */
    public function __toString()
    {
        return $this->code;
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // when a voucher is being created
        static::creating(function (Voucher $voucher) {

            if (empty($voucher->code)) {
                $voucher->code = Vouchers::generate(1)[0];
            }

            if (isset($voucher->allow_models)) {
                if (is_array($voucher->allow_models)) {
                    $voucher->allow($voucher->allow_models);
                }
                unset($voucher->allow_models);
            }

            if (isset($voucher->deny_models)) {
                if (is_array($voucher->deny_models)) {
                    $voucher->deny($voucher->deny_models);
                }
                unset($voucher->deny_models);
            }
        });

        static::created(function (Voucher $voucher) {
            if ($voucher->queuedItems) {
                $voucher->setItems($voucher->queuedItems);
                $voucher->queuedItems = [];
                $voucher->save();
            }
        });

        // when a voucher is being updated
        static::updating(function (Voucher $voucher) {

            if (isset($voucher->allow_models)) {
                if (is_array($voucher->allow_models)) {
                    $voucher->allow($voucher->allow_models);
                }
                unset($voucher->allow_models);
            }

            if (isset($voucher->deny_models)) {
                if (is_array($voucher->deny_models)) {
                    $voucher->deny($voucher->deny_models);
                }
                unset($voucher->deny_models);
            }
        });
    }
}
