<?php

namespace App\Http\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Spatie\Activitylog\Models\Activity;

class ActivityTable extends DataTableComponent
{
    public function columns(): array
    {
        return [
            Column::make('Groupe', 'properties.group')->sortable(),
            Column::make('Ressource', 'properties.resource')->sortable(),
            Column::make('Type', 'properties.level'),
            Column::make('Message', 'description')->searchable(),
            Column::make('Duration', 'properties.duration')->format(function ($value) {
                return $value ? number_format($value, 2).' secondes' : null;
            }),
            Column::make('Date', 'created_at')->sortable()->searchable()->format(function ($value) {
                return $value->locale('fr_CH')->timezone('Europe/Zurich')->isoFormat('L LT');
            }),
        ];
    }

    public function query(): Builder
    {
        return Activity::query();
    }

    public function setTableRowClass($row): ?string
    {
        $level = data_get($row->properties, 'level');

        $colorText = '';
        $colorBg = '';
        if ($level == 'start') {
            $colorBg = 'bg-success';
            $colorText = 'alert-success';
        } elseif ($level == 'end') {
            $colorBg = 'bg-success';
            $colorText = 'alert-success';
        } elseif ($level == 'error') {
            $colorBg = 'bg-error';
            $colorText = 'alert-error';
        } elseif ($level == 'warning') {
            $colorBg = 'bg-warning';
            $colorText = 'alert-warning';
        } elseif ($level == 'info') {
            $colorBg = 'text-base-content';
            $colorText = 'bg-base-content-100';
        } elseif ($level == 'job') {
            $colorBg = 'bg-info';
            $colorText = 'alert-info';
        }

        return $colorText.' '.$colorBg;
    }

    public array $perPageAccepted = [100, 500, 1000, 2000];

    public bool $perPageAll = true;
}
