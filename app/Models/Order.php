<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelArchivable\Archivable;
use PlugAndPay\Sdk\Enum\InvoiceStatus;
use PlugAndPay\Sdk\Enum\Mode;
use PlugAndPay\Sdk\Enum\OrderIncludes;
use PlugAndPay\Sdk\Enum\PaymentStatus;
use PlugAndPay\Sdk\Filters\OrderFilter;
use PlugAndPay\Sdk\Service\Client;
use PlugAndPay\Sdk\Service\OrderService;
use Sushi\Sushi;

class Order extends Model
{
    use HasFactory;
    use Archivable;
    use Sushi;

    private array $data = [];

    public function getRows(): array
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

        foreach($orders as $order) {
            $this->data[] = [
                'id' => $order->id(),
                'amount_excluding_vat' => $order->amount(),
                'archived_at' => null,
            ];
        }

        return $this->data;
    }

    protected $schema = [
        'id' => 'integer',
        'amount_excluding_vat' => 'float',
        'archived_at' => 'timestamp',
    ];
}
