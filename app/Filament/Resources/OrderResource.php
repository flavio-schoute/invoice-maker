<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PlugAndPay\Sdk\Enum\InvoiceStatus;
use PlugAndPay\Sdk\Enum\Mode;
use PlugAndPay\Sdk\Enum\OrderIncludes;
use PlugAndPay\Sdk\Enum\PaymentStatus;
use PlugAndPay\Sdk\Filters\OrderFilter;
use PlugAndPay\Sdk\Service\Client;
use PlugAndPay\Sdk\Service\OrderService;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        // Sushi data ophalen -> Display -> Claim -> Insert database
        return $table
            ->query(Order::query())
            ->columns([
                IconColumn::make('is_claimed_by_sales_rep')
                    ->label('Claimed by closer')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('invoice_number')
                    ->label('Factuurnummer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('invoice_date')
                    ->label('Bestel datum')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('full_name')
                    ->label('Volledige naam')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount_excluding_vat')
                    ->label('Bedrag')
                    ->money('EUR')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('test'),

                // Select column for closer en setter
            ])
            ->filters([
                Filter::make('Periode')
                    ->form([
                        DatePicker::make('invoice_date_from')
                            ->label('Factuurdatum van'),

                        DatePicker::make('invoice_date_until')
                            ->label('Factuurdatum tot')
                            ->default(Carbon::now()),
                    ])
                    ->query(function (Builder $builder, array $data) {
                        $result = [];

                        $client = new Client(
                            secretToken: config('services.plug_and_pay.api_key')
                        );

                        $orderService = new OrderService($client);

                        $invoiceDate = isset($data['invoice_date_from'])
                            ? Carbon::createFromFormat('Y-m-d', $data['invoice_date_from'])
                            : Carbon::now()->subDays(7);

                        $orderFilter = (new OrderFilter())
                            ->mode(Mode::LIVE)
                            ->invoiceStatus(InvoiceStatus::FINAL)
                            ->productGroup('educatie')
                            ->sinceInvoiceDate($invoiceDate)
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

                            $result[] = [
                                'id' => $order->id(),
                                'invoice_number' => $order->invoiceNumber(),
                                'invoice_date' => $order->createdAt(),
                                'full_name' => $fullName,
                                'email' => $order->billing()->contact()->email(),
                                'product_name' => 'test',
                                'amount_excluding_vat' => $order->amount(),
                            ];
                        }

                        var_dump($result);

                        return Order::loadData($result);
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['invoice_date_from'] ?? null) {
                            $indicators[] = Indicator::make('Created from ' . Carbon::parse($data['invoice_date_from'])
                                ->toFormattedDateString())
                                ->removeField('invoice_date_from');
                        }

                        if ($data['invoice_date_until'] ?? null) {
                            $indicators[] = Indicator::make('Created until ' . Carbon::parse($data['invoice_date_until'])
                                ->toFormattedDateString())
                                ->removeField('invoice_date_until');
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
