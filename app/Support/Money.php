<?php

namespace App\Support;

class Money
{
    // Shows an amount as BDT: "৳ 45,000.00".
    public static function bdt(float|int|string|null $amount): string
    {
        return '৳ '.number_format((float) $amount, 2, '.', ',');
    }
}
