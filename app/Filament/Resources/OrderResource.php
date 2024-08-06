<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use BladeUI\Icons\Components\Icon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Okeonline\FilamentArchivable\Tables\Actions\ArchiveAction;
use Okeonline\FilamentArchivable\Tables\Actions\UnArchiveAction;
use Okeonline\FilamentArchivable\Tables\Filters\ArchivedFilter;

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
        /**
         * Factuurnummber
         * Datum
         * Naam
         * Email
         * Product
         * Bedrag
         *
         * Claim knop
         */

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

                // Select column for closer en setter
            ])
            ->filters([
                ArchivedFilter::make(),

                Filter::make('Periode')
                    ->form([
                        DatePicker::make('filter_from'),

                        DatePicker::make('filter_unti')
                            ->default(Carbon::now())
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('Created from ' . Carbon::parse($data['from'])->toFormattedDateString())
                                ->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Created until ' . Carbon::parse($data['until'])->toFormattedDateString())
                                ->removeField('until');
                        }

                        return $indicators;
                    })
            ])
            ->actions([
                BulkActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ])->label('Acties'),

                ArchiveAction::make(),
                UnArchiveAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->archivedRecordClasses(['opacity-25']);
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
