<?php

namespace App\Filament\Pages;

use App\Models\PaymentSetting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;

class PaymentMode extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    protected static ?string $navigationLabel = 'Payment Mode';
    protected static ?string $title = 'Razorpay payment mode';
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 10;
    protected string $view = 'filament.pages.payment-mode';

    public string $mode = 'test';
    public string $password = '';

    public function mount(): void { $this->mode = PaymentSetting::mode(); }

    public function save(): void
    {
        $this->validate(['mode' => ['required', 'in:test,live'], 'password' => ['required', 'string']]);
        if (! Hash::check($this->password, auth()->user()->password)) {
            $this->addError('password', 'The administrator password is incorrect.');
            return;
        }
        PaymentSetting::query()->firstOrCreate([], ['razorpay_mode' => 'test'])->update(['razorpay_mode' => $this->mode, 'updated_by' => auth()->id()]);
        $this->password = '';
        Notification::make()->title('Razorpay mode changed to ' . strtoupper($this->mode))->success()->send();
    }
}
