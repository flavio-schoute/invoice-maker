<?php

namespace App\Jobs;

use App\Models\Customer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PlugAndPay\Sdk\Enum\InvoiceStatus;
use PlugAndPay\Sdk\Enum\Mode;
use PlugAndPay\Sdk\Enum\OrderIncludes;
use PlugAndPay\Sdk\Enum\PaymentStatus;
use PlugAndPay\Sdk\Filters\OrderFilter;
use PlugAndPay\Sdk\Service\Client;
use PlugAndPay\Sdk\Service\OrderPaymentService;
use PlugAndPay\Sdk\Service\OrderService;

class ProcessPaymentInformationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $client = new Client(
            secretToken: config('services.plug_and_pay.api_key')
        );

        $orderService = new OrderService($client);

        $orderFilter = (new OrderFilter())
            ->mode(Mode::LIVE)
            ->invoiceStatus(InvoiceStatus::FINAL)
            ->productGroup('educatie')
            ->paymentStatus(PaymentStatus::PAID);

        $orders  = $orderService
            ->include(
                OrderIncludes::BILLING,
                OrderIncludes::ITEMS,
                OrderIncludes::PAYMENT,
                OrderIncludes::TAXES,
                OrderIncludes::CUSTOM_FIELDS,
            )
            ->get($orderFilter);

        // dd($orders);

        // foreach($orders as $order) {
        //     // dd($order->billing()->contact()->firstName());

        //     Customer::query()->create([
        //         'full_name' => $order->billing()->contact()->firstName(),
        //         'address' => 'test',
        //         'email' => $order->billing()->contact()->email(),
        //         'user_id' => 1
        //     ]);
        // }
    }
}
