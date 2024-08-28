<?php

namespace App\Exports;

use App\Models\md_maps;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class MapsExport implements FromView
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function view(): View
    {
        return view('export.table', [
            'mapsData' => md_maps::all()
        ]);
    }
}
