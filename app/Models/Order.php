<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
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
    use Sushi;

    private array $data = [];

    protected static $invoiceDateFrom;

    protected $schema = [
        'id' => 'integer',
        'invoice_number' => 'string',
        'invoice_date' => 'timestamp',
        'full_name' => 'string',
        'email' => 'string',
        'product_name' => 'string',
        'amount_excluding_vat' => 'float',
        'archived_at' => 'timestamp',
    ];

    public function __construct(array $rows = [])
    {
        // Injects data before the contructor
        $this->setLoadedData($rows);

        parent::__construct([]);
    }

    // public static function setVar($invoiceDateFrom)
    // {
    //     self::$invoiceDateFrom = $invoiceDateFrom;

    //     return self::query();
    // }

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
            ->sinceInvoiceDate(Carbon::now()->subDays(7))
            ->untilInvoiceDate(Carbon::now())
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

        foreach ($orders as $order) {
            $fullName = $order->billing()->contact()->firstName() . $order->billing()->contact()->lastName();

            // Todo: Loop over items because an order can sometimes contain more than 1 item
            $this->data[] = [
                'id' => $order->id(),
                'invoice_number' => $order->invoiceNumber(),
                'invoice_date' => $order->createdAt(),
                'full_name' => $fullName,
                'email' => $order->billing()->contact()->email(),
                'product_name' => 'test',
                'amount_excluding_vat' => $order->amount(),
            ];
        }

        return $this->data;
    }
}
