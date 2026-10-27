<?php

namespace Tests\Unit;

use App\Services\PayrollCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PayrollCalculatorTest extends TestCase
{
    private const RULES = [
        'days_in_month' => 30,
        'late_days_per_absent' => 3,
        'half_day_fraction' => 0.5,
    ];

    public static function cases(): array
    {
        return [
            'only present and on leave: no deduction' => [
                45000.0,
                ['present' => 20, 'leave' => 2],
                self::RULES,
                0.0,
                45000.0,
            ],
            'three late days make one absent day' => [
                45000.0,
                ['present' => 19, 'late' => 3],
                self::RULES,
                1500.0,
                43500.0,
            ],
            'two late days make no deduction' => [
                45000.0,
                ['late' => 2],
                self::RULES,
                0.0,
                45000.0,
            ],
            'mixed month: absent, late, half day and leave' => [
                45000.0,
                ['present' => 17, 'late' => 4, 'absent' => 2, 'half_day' => 1, 'leave' => 1],
                self::RULES,
                5250.0, // (2 absent + 1 from 4 lates + 0.5 half day) x 1500
                39750.0,
            ],
            'half day only, odd salary' => [
                35000.0,
                ['present' => 20, 'half_day' => 1],
                self::RULES,
                583.33,  // 0.5 x 1166.67 (rounded to 2 decimals)
                34416.67,
            ],
            'one absent and one half day, odd salary' => [
                35000.0,
                ['absent' => 1, 'half_day' => 1],
                self::RULES,
                1750.0, // 1.5 x 1166.67 = 1750.005, rounded to 1750.00
                33250.0,
            ],
            'net never below zero' => [
                45000.0,
                ['absent' => 31],
                self::RULES,
                46500.0,
                0.0,
            ],
            'custom rules: 26 days and 2 lates per absent' => [
                45000.0,
                ['absent' => 1, 'late' => 4],
                ['days_in_month' => 26, 'late_days_per_absent' => 2, 'half_day_fraction' => 0.5],
                5192.31, // 3 days (1 absent + 2 from 4 lates) x 1730.77
                39807.69,
            ],
        ];
    }

    #[DataProvider('cases')]
    public function test_calculates_deduction_and_net_pay(float $salary, array $counts, array $rules, float $deduction, float $net): void
    {
        $result = (new PayrollCalculator)->calculate($salary, $counts, $rules);

        $this->assertEqualsWithDelta($deduction, $result['deduction'], 0.001);
        $this->assertEqualsWithDelta($net, $result['net_pay'], 0.001);
    }

    public function test_late_equivalent_is_floor_of_late_over_rule(): void
    {
        $calculator = new PayrollCalculator;

        $this->assertSame(0, $calculator->calculate(30000, ['late' => 2], self::RULES)['late_equivalent_days']);
        $this->assertSame(1, $calculator->calculate(30000, ['late' => 3], self::RULES)['late_equivalent_days']);
        $this->assertSame(1, $calculator->calculate(30000, ['late' => 5], self::RULES)['late_equivalent_days']);
        $this->assertSame(2, $calculator->calculate(30000, ['late' => 6], self::RULES)['late_equivalent_days']);
    }
}
