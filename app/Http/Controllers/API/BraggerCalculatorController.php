<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BraggerCalculatorController extends Controller
{
    /**
     * Calculate estimated bragger income based on product price and sales
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'product_price' => ['required', 'numeric', 'min:0'],
            'monthly_sales' => ['required', 'integer', 'min:0'],
            'monthly_returns' => ['required', 'integer', 'min:0'],
        ]);

        // Constant commission rate is 5% based on the UI example 
        // (£500 price -> £25 commission per item = 5%)
        $commissionRate = 0.05; 
        
        $price = (float) $validated['product_price'];
        $sales = (int) $validated['monthly_sales'];
        $returns = (int) $validated['monthly_returns'];

        $commissionPerItem = $price * $commissionRate;
        $totalMonthlyIncome = $commissionPerItem * $sales;
        $incomeAfterReturns = $commissionPerItem * max(0, ($sales - $returns));

        return response()->json([
            'inputs' => [
                'product_price' => number_format($price, 2, '.', ''),
                'monthly_sales' => $sales,
                'monthly_returns' => $returns,
                'commission_rate' => $commissionRate,
            ],
            'results' => [
                'commission_per_item' => number_format($commissionPerItem, 2, '.', ''),
                'total_monthly_income' => number_format($totalMonthlyIncome, 2, '.', ''),
                'income_after_returns' => number_format($incomeAfterReturns, 2, '.', ''),
            ]
        ]);
    }
}
