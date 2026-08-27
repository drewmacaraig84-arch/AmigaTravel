<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MyTransactionsExport implements WithMultipleSheets
{
    use Exportable;

    protected array $groupedBookings;

    public function __construct(array $groupedBookings)
    {
        $this->groupedBookings = $groupedBookings;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->groupedBookings as $title => $bookings) {
            $sheets[] = new MyTransactionsSheet($title, $bookings);
        }

        return $sheets;
    }
}
