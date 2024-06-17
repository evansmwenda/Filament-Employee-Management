<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\City;
use App\Models\Country;
use App\Models\Employee;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                ->schema([
                    Select::make('country_id')
                        ->label('Country')
                        ->options(Country::all()->pluck('name','id')->toArray())
                        ->reactive()
                        ->afterStateUpdated(fn(callable $set) => $set('state_id',null)),


                        Select::make('state_id')
                        ->label('State')
                        ->options(function (callable $get){
                            $country = Country::find($get('country_id'));
                            if(!$country){
                                return State::all()->pluck('name','id');
                            }
                            return $country->states->pluck('name','id')->toArray();
                        })
                        ->reactive()
                        ->afterStateUpdated(fn(callable $set) => $set('city_id',null)),

                        Select::make('city_id')
                        ->label('City')
                        ->options(function (callable $get){
                            $state = State::find($get('state_id'));
                            if(!$state){
                                return City::all()->pluck('name','id');
                            }
                            return $state->cities->pluck('name','id')->toArray();
                        }),


                        Select::make('department_id')
                        ->relationship('department', 'name')->required(),
                    TextInput::make('first_name')->required(),
                    TextInput::make('last_name')->required(),
                    TextInput::make('address')->required(),
                    TextInput::make('zip_code')->required(),
                    DatePicker::make('birth_date')
                    ->minDate(now()->subYears(150))
                    ->maxDate(now())->required(),
                    DatePicker::make('date_hired')
                    ->minDate(now()->subYears(50))
                    ->maxDate(now())->required()
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('first_name')->sortable()->searchable(),
                TextColumn::make('last_name')->sortable()->searchable(),
                // TextColumn::make('country.name')->sortable()->searchable(),
                // TextColumn::make('state.name')->sortable()->searchable(),
                // TextColumn::make('city.name')->sortable()->searchable(),
                TextColumn::make('department.name')->sortable()->searchable(),
                // TextColumn::make('address')->sortable()->searchable(),
                // TextColumn::make('zip_code')->sortable()->searchable(),
                // TextColumn::make('birth_date')->sortable()->searchable(),
                TextColumn::make('date_hired')->sortable()->searchable(),
                TextColumn::make('created_at'),
            ])
            ->filters([
                SelectFilter::make('department')
                 ->relationship('department', 'name')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
