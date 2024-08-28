<?php

namespace App\Exports;

use App\Models\md_maps;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class MapsbyidExport implements FromView
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $mapsData;

    public function __construct($mapsData)
    {
        $this->mapsData = $mapsData;
    }

    public function view(): View
    {
        return view('export.tablebyid', [
            'mapsData' => $this->mapsData
        ]);
    }
}
