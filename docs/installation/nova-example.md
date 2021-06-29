# Nova Resource Example


```php
<?php

namespace App\Nova;

...
use OptimistDigital\MultiselectField\Multiselect;
use MOIREI\Vouchers\Models\Voucher;

class Vouchers extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'MOIREI\Vouchers\Models\Voucher';

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {

        $users = [];
        foreach(\App\Models\User::all() as $user){
            $users[json_encode([
                'voucherable_type' => $user->getMorphClass(),
                'voucherable_id' => $user->getKey(),
            ])] = "$user->name (User)";
            // $users[$user->id] = $user->name;
        }
        foreach(\App\Models\General\Reseller::all() as $user){
            $users[json_encode([
                'voucherable_type' => $user->getMorphClass(),
                'voucherable_id' => $user->getKey(),
            ])] = "$user->name (Reseller)";
        }
        foreach(\App\Models\Guest::all() as $user){
            $users[json_encode([
                'voucherable_type' => $user->getMorphClass(),
                'voucherable_id' => $user->getKey(),
            ])] = "$user->name (Guest)";
        }

        return [
            ID::make()->sortable(),

            MorphTo::make('Product', 'model')->readOnly(function(){
                return !is_null($this->model);
            })->types([
                Product::class,
            ]),

            ...

            MorphOne::make('Data', 'modeldata', ModelData::class), // you'll have to create a ModelData resource

            Multiselect::make('Only users', 'can_redeem')->options($users),

            Multiselect::make('Except users', 'cannot_redeem')->options($users),
        ];

    }

}

```

