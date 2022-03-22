<?php

namespace App\Http\Livewire;

use App\Cart\Contracts\CartInterface;
use App\Mail\OrderCreated;
use App\Models\Order;
use App\Models\ShippingAddress;
use App\Models\ShippingType;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class Checkout extends Component
{
    public $shippingTypes;

    public $shippingTypeId;

    public $shippingAddress;

    public $userShippingAddressId;

    public $accountForm = [
        'email' => ''
    ];

    public $shippingForm = [
        'address' => '',
        'city' => '',
        'postcode' => '',
    ];

    protected $validationAttributes = [
        'accountForm.email' => 'email address',
        'shippingForm.address' => 'shipping address',
        'shippingForm.city' => 'shipping city',
        'shippingForm.postcode' => 'shipping postal code',
    ];

    protected $messages = [
        'accountForm.email.unique' => 'Seems you already have an account. Please sign in to place an order.',
        'shippingForm.address.required' => 'Your :attribute is required'
    ];

    public function rules()
    {
        return [
            'accountForm.email' => 'required|email|max:255|unique:users,email' . (auth()->user() ? ',' . auth()->user()->id : ''),
            'shippingForm.address' => 'required|max:255',
            'shippingForm.city' => 'required|max:255',
            'shippingForm.postcode' => 'required|max:255',
            'shippingTypeId' => 'required|exists:shipping_types,id'
        ];
    }

    public function updatedUserShippingAddressId($id)
    {
        if (!$id) {
            return;
        }

        $this->shippingForm = $this->userShippingAddresses->find($id)
            ->only('address', 'city', 'postcode');
    }

    public function getUserShippingAddressesProperty()
    {
        return auth()->user()?->shippingAddresses;
    }

    public function checkout(CartInterface $cart)
    {
        $this->validate();

        $this->shippingAddress = ShippingAddress::query();

        if (auth()->user()) {
            $this->shippingAddress = $this->shippingAddress->whereBelongsTo(auth()->user());
        }

        ($this->shippingAddress = $this->shippingAddress->firstOrcreate($this->shippingForm))
            ?->user()
            ->associate(auth()->user())
            ->save();

        $order = Order::make(array_merge($this->accountForm, [
            'subtotal' => $cart->subtotal()
        ]));

        $order->user()->associate(auth()->user());

        $order->shippingType()->associate($this->shippingType);

        $order->shippingAddress()->associate($this->shippingAddress);

        $order->save();

        $order->variations()->attach(
            $cart->contents()->mapWithKeys(function ($variation) {
                return [
                    $variation->id => [
                        'quantity' => $variation->pivot->quantity
                    ]
                ];
            })
        );

        $cart->contents()->each(function ($variation) {
            $variation->stocks()->create([
                'amount' => 0 - $variation->pivot->quantity
            ]);
        });

        $cart->removeAll();

        Mail::to($order->user)->send(new OrderCreated($order));

        $cart->destroy();

        if (!auth()->user()) {
            return redirect()->route('order.confirmation', $order->uuid);
        }

        return redirect()->route('orders');
    }

    public function mount()
    {
        $this->shippingTypes = ShippingType::orderBy('price', 'asc')->get();
        $this->shippingTypeId = $this->shippingTypes->first()->id;

        if ($user = auth()->user()) {
            $this->accountForm['email'] = $user->email;
        }
    }

    public function getShippingTypeProperty()
    {
        return $this->shippingTypes->find($this->shippingTypeId);
    }

    public function getTotalProperty(CartInterface $cart)
    {
        return $cart->subtotal() + $this->shippingType->price;
    }

    public function render(CartInterface $cart)
    {
        return view('livewire.checkout', [
            'cart' => $cart,
        ]);
    }
}
