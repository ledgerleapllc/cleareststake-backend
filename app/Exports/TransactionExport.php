<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionExport implements FromArray, WithHeadings
{
    public function __construct($data)
    {
        $this->data = $data;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        $transaction =  $this->data;
        return $transaction;
    }

    public function headings(): array
    {
        return [
            'Date',
			'Fund',
			'Transaction Type',
            'Amount of Transaction',
            'USD',
			'User'
			];
    }
}
