<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KeyResource\Pages;
use App\Filament\Resources\KeyResource\RelationManagers;
use App\Models\Key;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class KeyResource extends Resource
{
    protected static ?string $model = Key::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Token mező automatikus generálással és újragenerálással
                TextInput::make('token')
                    ->required()
                    ->maxLength(255)
                    ->default(Str::random(40)) // Alapértelmezett token generálása
                    ->suffixAction(
                        Action::make('generateToken')
                            ->icon('heroicon-o-arrow-path')
                            ->action(function ($state, $set) {
                                $set('token', Str::random(40)); // Token újragenerálása
                                Notification::make()
                                    ->title('Token újragenerálva!')
                                    ->success()
                                    ->send();
                            })
                    ),
                Select::make('type')
                    ->required()
                    ->options([
                        '0' => 'Moderátor kulcs',
                        '1' => 'Tulajdonosi kulcs',
                        '2' => 'Fejlesztő kulcs',
                    ])
                    ->live(),
                Select::make('site_id')
                    ->relationship('site', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                    ->requiredIf('type', '0')
                    ->hidden(fn ($get) => $get('type') !== '0'),
                DatePicker::make('expiration_time')
                    ->label('Lejárati dátum')
                    ->requiredIf('type', '!=', '0')
                    ->hidden(fn ($get) => $get('type') === '0' || $get('type') === '2')
                    ->displayFormat('Y-m-d H:i:s')
                    ->timezone('UTC'),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('token')
                    ->searchable()
                    ->limit(25),
                TextColumn::make('site.name')
                    ->label('Oldal név')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Felhasználó neve')
                    ->searchable()
                    ->sortable(),
                SelectColumn::make('type')
                    ->label('Típus')
                    ->options([
                        '0' => 'Moderátor kulcs',
                        '1' => 'Tulajdonosi kulcs',
                        '2' => 'Fejlesztő kulcs',
                    ])
                    ->sortable(),
                TextColumn::make('expiration_time')
                    ->label('Lejárati dátum')
                    ->date(),
            ])
            ->filters([
                SelectFilter::make('site_id')
                    ->relationship('site', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->multiple()
                    ->options([
                        0 => 'Moderátor', 1 => 'Tulajdonos', 2 => 'Superadmin'
                    ])
                    ->label('Kulcs típusa'),
                TernaryFilter::make('activated')
                    ->trueLabel('Aktíválva')
                    ->falseLabel('Nincs aktiválva')
                    ->nullable()
                    ->label('Státusz'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKeys::route('/'),
            'create' => Pages\CreateKey::route('/create'),
            'edit' => Pages\EditKey::route('/{record}/edit'),
        ];
    }
}
