<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Faker\Core\File;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\DateTimeColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                    TextInput::make('name')
                        ->label('Teljes név')
                        ->required(),
                    TextInput::make('email')
                        ->label('E-mail cím')
                        ->required(),
                    TextInput::make('password')
                        ->label('Jelszó')
                        ->required(),
                    Select::make('status')
                        ->label('Státusz')
                        ->options([
                            0 => 'Blokkolt',
                            1 => 'Inaktív',
                            2 => 'Aktív',
                        ])
                        ->default(2)
                        ->required(),
                    FileUpload::make('image')
                        ->label('Profilkép')
                        ->disk('public')
                        ->avatar(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                ImageColumn::make('image')
                    ->label('Profilkép'),
                TextColumn::make('name')
                    ->label('Teljes név')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('keys.site.name')
                    ->label('Kapcsolódó oldalak')
                    ->searchable(),
                SelectColumn::make('status')
                    ->label('Státusz')
                    ->options([
                        0 => 'Blokkolt',
                        1 => 'Inaktív',
                        2 => 'Aktív',
                    ])
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('keys.site.name')
                    ->label('Oldal')
                    ->relationship('keys.site', 'name')
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Státusz')
                    ->options([
                        0 => 'Blokkolt',
                        1 => 'Inaktív',
                        2 => 'Aktív',
                    ])
                    ->multiple(),
                TernaryFilter::make('email_verified_at')
                    ->label('E-mail státusz')
                    ->trueLabel('E-mail ellenőrizve')
                    ->falseLabel('E-mail nincs ellenőrizve')
                    ->nullable(),
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


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
