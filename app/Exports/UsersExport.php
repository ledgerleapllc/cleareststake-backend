<?php

namespace App\Exports;

use App\Http\Helper;
use App\Transaction;
use App\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromArray
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
        $user =  $this->data;
        return $user;
    }

    public function headings(): array
    {
        return [
			'Name',
			'Fund',
			'Total Tokens',
			'% of Total',
			'Withdraw Sum',
			'Last Withdraw',
			'Total inflation'
			];
    }
}
