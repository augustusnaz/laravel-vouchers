<?php

namespace MOIREI\Vouchers\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use MOIREI\Vouchers\Helpers;
use MOIREI\Vouchers\VoucherScheme;

/**
 * @property string $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $code
 * @property int $quantity
 * @property Object $quantity_used
 * @property VoucherScheme $limit_scheme
 * @property string[] $can_redeem
 * @property string[] $cannot_redeem
 * @property \Carbon\Carbon $active_date
 * @property \Carbon\Carbon $expires_at
 * @property double $value
 * @property bool $active
 * @property bool $expired
 * @property \Illuminate\Support\Collection $data
 * @property \Illuminate\Support\Collection $users
 * @property \Illuminate\Support\Collection $products
 * @property \Illuminate\Support\Collection $relatedRedeemers
 * @property \Illuminate\Support\Collection $items
 * @property \Illuminate\Support\Collection $redeemers
 * @property \Illuminate\Support\Collection $voucher_items
 * @property \Illuminate\Support\Collection $voucherItems
 * @property \Illuminate\Support\Collection $allowd_users
 * @property \Illuminate\Support\Collection $allowdUsers
 * @property \Illuminate\Support\Collection $disallowd_users
 * @property \Illuminate\Support\Collection $disallowdUsers
 *
 * @method static self create()
 * @method static \Illuminate\Database\Eloquent\Builder active()
 * @method static \Illuminate\Database\Eloquent\Builder code(string $code)
 * @method static \Illuminate\Database\Eloquent\Builder disposable()
 * @method static \Illuminate\Database\Eloquent\Builder expired(\Illuminate\Support\Carbon|int|null $age = null)
 * @method static \Illuminate\Database\Eloquent\Builder ofScheme(VoucherScheme|string $scheme)
 * @method static \Illuminate\Database\Eloquent\Builder instanceScheme()
 * @method static \Illuminate\Database\Eloquent\Builder itemScheme()
 * @method static \Illuminate\Database\Eloquent\Builder redeemerScheme()
 */
class Voucher extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'code', 'active_date', 'expires_at',
        'quantity', 'quantity_used', 'limit_scheme', 'value',
        'can_redeem', 'cannot_redeem', 'allow_models', 'deny_models',
        'data',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'active_date' => 'datetime',
        'expires_at' => 'datetime',
        'can_redeem' => 'array',
        'cannot_redeem' => 'array',
        'quantity' => 'integer',
        'quantity_used' => 'json',
        'value' => 'decimal:2',
        'data' => AsCollection::class,
        'limit_scheme' => VoucherScheme::class,
    ];

    /**
     * Used to track items to be associated to the
     * voucher if the voucher model has not been created.
     *
     * @var array
     */
    private $queuedItems = [];

    /**
     * Similar to items but specific to products relationship.
     * Used to track products to be associated to the
     * voucher if the voucher model has not been created.
     *
     * @var array
     */
    private $queuedProducts = [];

    /**
     * Construct a voucher instance
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct(array_merge(['limit_scheme' => config('vouchers.default_limit_scheme', VoucherScheme::INSTANCE)], $attributes));
        $this->table = config('vouchers.tables.vouchers', 'vouchers');
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
     * Query builder; get by active state.
     *
     * @param $query
     * @param string $code
     * @return mixed
     */
    public function scopeActive($query)
    {
        $now = now();
        return $query->where(function ($query) use ($now) {
            $query->whereNull('active_date')
                ->orWhere('active_date', '<=', $now);
        })
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $now);
            })
            ->whereNotNull('price_rule_id');
    }

    /**
     * Query builder; get by code.
     *
     * @param $query
     * @param string $code
     * @return mixed
     */
    public function scopeCode($query, string $code)
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
     * @param \Illuminate\Support\Carbon|int|null $age
     * @return mixed
     */
    public function scopeExpired($query, $age = null)
    {
        if (!$age) $age = Carbon::now();
        elseif (is_int($age)) {
            $age = Carbon::now()->subDays($age);
        }

        return $query->where(function ($query) use ($age) {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '<=', $age);
        });
    }

    /**
     * Query builder; scope by scheme.
     *
     * @param $query
     * @param VoucherScheme|string $scheme
     * @return mixed
     */
    public function scopeOfScheme($query, VoucherScheme|string $scheme)
    {
        return $query->where('limit_scheme', (is_string($scheme) ? VoucherScheme::from($scheme) : $scheme)->value);
    }

    /**
     * Query builder; scope by instance scheme.
     *
     * @param $query
     * @return mixed
     */
    public function scopeInstanceScheme($query)
    {
        return $query->ofScheme(VoucherScheme::INSTANCE);
    }

    /**
     * Query builder; scope by item scheme.
     *
     * @param $query
     * @return mixed
     */
    public function scopeItemScheme($query)
    {
        return $query->ofScheme(VoucherScheme::ITEM);
    }

    /**
     * Query builder; scope by redeemer scheme.
     *
     * @param $query
     * @return mixed
     */
    public function scopeRedeemerScheme($query)
    {
        return $query->ofScheme(VoucherScheme::REDEEMER);
    }

    /**
     * Whether the voucher code is active
     *
     * @return bool
     */
    public function getActiveAttribute()
    {
        return !$this->expired && (!$this->active_date ||
            ($this->active_date && !$this->active_date->isFuture())
        );
    }

    /**
     * Whether the voucher code is expired
     *
     * @return bool
     */
    public function getExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Models that have redeemed this voucher
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRedeemersAttribute()
    {
        return $this->relatedRedeemers()->get()->map(function ($entry) {
            return $entry->redeemer;
        });
    }

    /**
     * Items associated with this voucher
     *
     * @return \Illuminate\Support\Collection
     */
    public function getVoucherItemsAttribute()
    {
        return $this->items()->get()->map(function ($entry) {
            return $entry->item;
        });
    }

    /**
     * Models that are allowed to redeem this voucher
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllowdUsersAttribute()
    {
        $array = [];
        if (!is_array($can_redeem = $this->can_redeem)) {
            /** @var string $can_redeem */
            $can_redeem = json_decode($can_redeem, true) ?? [];
        }
        foreach ($can_redeem as $user) {
            if (is_string($user)) {
                $user = json_decode($user, true);
            }
            array_push($array, $user);
        }

        return collect($array)->map(function ($model) {
            $class = '\\' . $model['redeemer_type'];
            if (!class_exists($class)) {
                $class = Relation::getMorphedModel($model['redeemer_type']);
            }
            return $class::find($model['redeemer_id']);
        });
    }

    /**
     * Models that are NOT allowed to redeem this voucher
     *
     * @return \Illuminate\Support\Collection
     */
    public function getDisallowdUsersAttribute()
    {
        $array = [];
        if (!is_array($cannot_redeem = $this->cannot_redeem)) {
            /** @var string $cannot_redeem */
            $cannot_redeem = json_decode($cannot_redeem, true) ?? [];
        }
        foreach ($cannot_redeem as $user) {
            if (is_string($user)) {
                $user = json_decode($user, true);
            }
            array_push($array, $user);
        }

        return collect($array)->map(function ($model) {
            $class = '\\' . $model['redeemer_type'];
            if (!class_exists($class)) {
                $class = Relation::getMorphedModel($model['redeemer_type']);
            }
            return $class::find($model['redeemer_id']);
        });
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
            'redeemer',
            config('vouchers.tables.redeemer_pivot_table', 'redeemer_voucher'),
            'voucher_id',
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
            config('vouchers.tables.item_pivot_table', 'item_voucher'),
            'voucher_id',
        );
    }

    /**
     * Models that have redeemed this voucher
     *
     * @return HasMany
     */
    public function relatedRedeemers()
    {
        return $this->hasMany(UserVoucherable::class, 'voucher_id');
    }

    /**
     * Items associated with this voucher
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(ItemVoucherable::class, 'voucher_id');
    }

    /**
     * Increment the amount used
     *
     * @param int $count
     * @param Model|null $model redeemer or related item model
     * @return self
     */
    public function incrementUse(int $count = 1, Model $model = null)
    {
        $this->setQuantityUsed(
            $this->getQuantityUsed($model) + $count,
            $model,
        );
        $this->save();

        return $this;
    }

    /**
     * Increment the amount used by model
     *
     * @param Model $model redeemer or related item model
     * @param int $count
     * @return self
     */
    public function incrementModelUse(Model $model, int $count = 1)
    {
        return $this->incrementUse($count, $model);
    }

    /**
     * Limit use by
     *
     * @param VoucherScheme|string $per
     * @param int|null $quantity
     * @return self
     */
    public function limitUsePer(VoucherScheme|string $scheme, int $quantity = null)
    {
        $this->limit_scheme = is_string($scheme) ? VoucherScheme::from($scheme) : $scheme;
        if (!is_null($quantity)) {
            $this->quantity = $quantity;
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
        return $this->limitUsePer(VoucherScheme::INSTANCE, $quantity);
    }

    /**
     * Limit use per product
     *
     * @param int|null $quantity
     * @return self
     */
    public function limitUsePerItem(int|null $quantity = null)
    {
        return $this->limitUsePer(VoucherScheme::ITEM, $quantity);
    }

    /**
     * Limit use per redeemer
     *
     * @param int|null $quantity
     * @return self
     */
    public function limitUsePerRedeemer(int|null $quantity = null)
    {
        return $this->limitUsePer(VoucherScheme::REDEEMER, $quantity);
    }

    /**
     * Get the quantity used (based on limit scheme)
     *
     * @param int $quantity
     * @param Model|null $model redeemer or related item model
     * @return self
     */
    protected function setQuantityUsed(int $quantity, Model $model = null): self
    {
        $quantity_used = $this->quantity_used;

        if ($this->limit_scheme->is(VoucherScheme::INSTANCE)) {
            Arr::set($quantity_used, 'instance', $quantity);
        } elseif ($this->limit_scheme->is(VoucherScheme::ITEM) && $model) {
            if ($this->isItem($model)) {
                Arr::set($quantity_used, "item.$model->id", $quantity);
            }
        } elseif ($this->limit_scheme->is(VoucherScheme::REDEEMER) && $model) {
            if ($this->isAllowed($model)) {
                Arr::set($quantity_used, Helpers::getModelKey($model, "redeemer."), $quantity);
            }
        } else {
            return $this;
        }

        $this->quantity_used = $quantity_used;

        return $this;
    }

    /**
     * Get the quantity used (based on limit scheme)
     *
     * @param Model|null $model redeemer or related item model
     * @return int
     */
    public function getQuantityUsed(Model $model = null): int
    {
        $quantity_used = $this->quantity_used;

        if ($this->limit_scheme->is(VoucherScheme::INSTANCE)) {
            $used = Arr::get($quantity_used, 'instance', 0);
        } elseif ($this->limit_scheme->is(VoucherScheme::ITEM)) {
            if ($model) {
                $used = Arr::get($quantity_used, "item.$model->id", 0);
            } else {
                // if no specific item given, the least quantity exhausts usage
                $used = collect(Arr::get($quantity_used, 'item', []))->values()->flatten()->min() ?: 0;
            }
        } elseif ($this->limit_scheme->is(VoucherScheme::REDEEMER)) {
            if ($model) {
                $used = Arr::get($quantity_used, Helpers::getModelKey($model, "redeemer."), 0);
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
     * @return self
     */
    public function setItems($items): self
    {
        $this->items()->delete();
        $items = is_array($items) ? $items : func_get_args();
        $this->addItems($items);

        return $this;
    }

    /**
     * Add to the related items.
     *
     * @param Model|string|array $items
     * @return bool
     */
    public function addItems($items): self
    {
        $items = is_array($items) ? $items : func_get_args();

        if (!$this->exists) {
            $this->queuedItems = array_merge($this->queuedItems, $items);
        } else {
            $default_item_class = config('vouchers.models.products');
            $items = array_map(fn ($item) => ($item instanceof Model) ? $item : $default_item_class::find($item), $items);
            $existing_item_keys = $this->items()->get()->map(fn ($item) => Helpers::getModelKey($item->item))->toArray();
            $changed = false;

            /** @var Model $item */
            foreach ($items as $item) {
                if (!in_array(Helpers::getModelKey($item), $existing_item_keys)) {
                    $this->morphedByMany(
                        Helpers::getMorphedModel($item),
                        'item',
                        config('vouchers.tables.item_pivot_table', 'item_voucher'),
                        'voucher_id',
                    )->save($item);
                    $changed = true;
                }
            }

            if ($changed) {
                $this->clearItemsCache();
            }
        }

        return $this;
    }

    /**
     * Remove voucher items.
     *
     * @param Model|string|array $items
     */
    public function removeItems($items): self
    {
        $items = is_array($items) ? $items : func_get_args();

        if ($this->exists) {
            $default_item_class = config('vouchers.models.products');
            $changed = false;

            /** @var Model $item */
            foreach ($items as $item) {
                if (!($item instanceof Model)) {
                    $item = $default_item_class::find($item);
                }
                DB::table(config('vouchers.tables.item_pivot_table', 'item_voucher'))
                    ->where([
                        'item_id' => $item->getKey(),
                        'item_type' => $item->getMorphClass(),
                    ])
                    ->delete();
                $changed = true;
            }

            if ($changed) {
                $this->clearItemsCache();
            }
        }

        return $this;
    }

    /**
     * Set the related products.
     *
     * @param Model|string|array $items
     * @return self
     */
    public function setProducts($products): self
    {
        $products = is_array($products) ? $products : func_get_args();
        if (!$this->exists) {
            $this->queuedItems = $products;
        } else {
            $products = array_map(fn ($product) => ($product instanceof Model) ? $product->getKey() : $product, $products);
            $this->products()->sync($products);
            $this->clearItemsCache();
        }

        return $this;
    }

    /**
     * Add to the related products.
     *
     * @param Model|string|array $products
     * @return self
     */
    public function addProducts($products): self
    {
        $products = is_array($products) ? $products : func_get_args();
        if (!$this->exists) {
            $this->queuedItems = array_merge($this->queuedItems, $products);
        } else {
            $products = array_map(fn ($product) => ($product instanceof Model) ? $product->getKey() : $product, $products);
            $this->products()->syncWithoutDetaching($products);
            $this->clearItemsCache();
        }

        return $this;
    }

    /**
     * Remove voucher products.
     *
     * @param Model|string|array $products
     * @return self
     */
    public function removeProducts($products): self
    {
        $products = is_array($products) ? $products : func_get_args();
        $products = array_map(fn ($product) => ($product instanceof Model) ? $product->getKey() : $product, $products);
        $this->products()->detach($products);
        $this->clearItemsCache();

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
     * @param $allowed
     * @return self
     */
    public function allow($allowed)
    {
        $models = $this->can_redeem ?: [];
        $allowed = is_array($allowed) ? $allowed : func_get_args();

        foreach ($allowed as $model) {
            if ($model instanceof Model) {
                array_push($models, [
                    'redeemer_type' => $model->getMorphClass(),
                    'redeemer_id' => $model->getKey(),
                ]);
                $this->removeModelFromDenied($model);
            }
        }

        $this->can_redeem = $models;
        $this->clearAllowedDisallowedUsersCache();

        return $this;
    }

    /**
     * Models that are NOT allowed to redeem this voucher
     *
     * @param $denied
     * @return self
     */
    public function deny($denied)
    {
        $models = $this->cannot_redeem ?: [];
        $denied = is_array($denied) ? $denied : func_get_args();

        foreach ($denied as $model) {
            if ($model instanceof Model) {
                array_push($models, [
                    'redeemer_type' => $model->getMorphClass(),
                    'redeemer_id' => $model->getKey(),
                ]);
                $this->removeModelFromAllowed($model);
            }
        }

        $this->cannot_redeem = $models;
        $this->clearAllowedDisallowedUsersCache();

        return $this;
    }

    /**
     * Models that are allowed to redeem this voucher and remove existing
     *
     * @param $allowed
     * @return self
     */
    public function setAllowed($allowed)
    {
        $this->can_redeem = [];
        $allowed = is_array($allowed) ? $allowed : func_get_args();
        return $this->allow($allowed);
    }

    /**
     * Models that are NOT allowed to redeem this voucher and remove existing
     *
     * @param $denied
     * @return self
     */
    public function setDenied($denied)
    {
        $this->can_redeem = [];
        $denied = is_array($denied) ? $denied : func_get_args();
        return $this->deny($denied);
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
        return $this->expired;
    }

    /**
     * Check if code is redeemed.
     *
     * @param Model|null $model redeemer or related item model
     * @return bool
     */
    public function isRedeemed(Model $model = null): bool
    {
        return $this->redeemers->count() && $this->isExhausted($model);
    }

    /**
     * Check if the voucher redeems has been exhausted.
     *
     * @param Model|null $model redeemer or related item model
     * @return bool
     */
    public function isExhausted(Model $model = null): bool
    {
        if (!$model && ($this->limit_scheme->is(VoucherScheme::ITEM) ||
            $this->limit_scheme->is(VoucherScheme::REDEEMER)
        )) {
            return false;
        }

        return $this->getQuantityUsed($model) >= $this->quantity;
    }

    /**
     * Check if the given model(s) are allowed to use voucher.
     *
     * @param $models
     * @return bool
     */
    public function isAllowed($models): bool
    {
        if (is_null($this->getRawOriginal('can_redeem'))) {
            return !$this->isDisallowed($models);
        }

        $models = is_array($models) ? $models : func_get_args();
        $allowed = Helpers::in($models, $this->allowdUsers);

        return Helpers::isAll($allowed, true);
    }

    /**
     * Check the given model(s) are disallowed to use voucher.
     *
     * @param $models
     * @return bool
     */
    public function isDisallowed($models): bool
    {
        $models = is_array($models) ? $models : func_get_args();
        $disallowed = Helpers::in($models, $this->disallowdUsers);

        return Helpers::isAll($disallowed, true);
    }

    /**
     * Check if ANY of the given model(s) are allowed to use voucher.
     *
     * @param $models
     * @return bool
     */
    public function isAnyAllowed($models): bool
    {
        if (is_null($this->getRawOriginal('can_redeem'))) {
            return !$this->isDisallowed($models);
        }

        $models = is_array($models) ? $models : func_get_args();

        return Helpers::anyIn($models, $this->allowdUsers);
    }

    /**
     * Check if ANY of the given model(s) are disallowed to use voucher.
     *
     * @param $models
     * @return bool
     */
    public function isAnyDisallowed($models): bool
    {
        $models = is_array($models) ? $models : func_get_args();
        return Helpers::anyIn($models, $this->disallowdUsers);
    }

    /**
     * Check if the given item is a related product.
     *
     * @param Model|string|int $item
     * @return bool
     */
    public function isItem(Model|string|int $item): bool
    {
        return $this->isAnyItem($item);
    }

    /**
     * Check if any of the given items is a related product.
     *
     * @param $items
     * @return bool
     */
    public function isAnyItem($items): bool
    {
        $items = is_array($items) ? $items : func_get_args();
        $query = $this->products();
        $count = 0;

        foreach ($items as $item) {
            if (!($item instanceof Model)) {
                $default_item_class = config('vouchers.models.products');
                /** @var Model */
                $item = $default_item_class::find($item);
            }
            $where = [
                'item_id' => $item->getKey(),
                'item_type' => $item->getMorphClass(),
            ];
            if ($count++ == 0) {
                $query->where($where);
            } else {
                $query->orWhere(function($query) use($where){
                    $query->where($where);
                });
            }
        }

        return !!$query->count();
    }

    /**
     * Persist any pending queued items and products.
     */
    public function flushQueuedItems()
    {
        if (!empty($this->queuedItems)) {
            $this->setItems($this->queuedItems);
            $this->queuedItems = [];
        }
        if (!empty($this->queuedProducts)) {
            $this->setProducts($this->queuedProducts);
            $this->queuedProducts = [];
        }
        $this->save();
    }

    /**
     * Prune the expired vouchers.
     *
     * @param \Illuminate\Support\Carbon|int $age
     * @return void
     */
    static public function pruneExpired(Carbon | int $age = 1)
    {
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

    protected function clearAllowedDisallowedUsersCache()
    {
        unset($this->attributeCastCache['allowdUsers']);
        unset($this->attributeCastCache['allowd_users']);
        unset($this->attributeCastCache['disallowdUsers']);
        unset($this->attributeCastCache['disallowd_users']);
    }

    protected function clearItemsCache()
    {
        unset($this->attributeCastCache['voucherItems']);
        unset($this->attributeCastCache['items']);
        unset($this->attributeCastCache['products']);
        $this->load('items');
        $this->load('products');
    }

    protected function removeModelFromDenied(Model $model)
    {
        $models = $this->cannot_redeem ?: [];
        $this->cannot_redeem = array_filter($models, function ($entry) use ($model) {
            return !(
                ($entry['redeemer_type'] == $model->getMorphClass()) &&
                ($entry['redeemer_id'] == $model->getKey())
            );
        });
    }

    protected function removeModelFromAllowed(Model $model)
    {
        $models = $this->can_redeem ?: [];
        $this->can_redeem = array_filter($models, function ($entry) use ($model) {
            return !(
                ($entry['redeemer_type'] == $model->getMorphClass()) &&
                ($entry['redeemer_id'] == $model->getKey())
            );
        });
    }
}
