<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SellerCalculatorController extends Controller
{
    /**
     * Shipped By Seller (SBS) Profit Calculator
     */
    public function sbs(Request $request)
    {
        $validated = $request->validate([
            'include_braggers_fee' => ['boolean'],
            'packaging_length' => ['nullable', 'numeric', 'min:0'],
            'packaging_width' => ['nullable', 'numeric', 'min:0'],
            'packaging_height' => ['nullable', 'numeric', 'min:0'],
            'packaging_weight' => ['nullable', 'numeric', 'min:0'],
            
            'product_price' => ['required', 'numeric', 'min:0'],
            'manufacturing_cost' => ['required', 'numeric', 'min:0'],
            'vat_percent' => ['required', 'numeric', 'min:0'],
            'monthly_sales' => ['required', 'integer', 'min:0'],
            'monthly_returns' => ['required', 'integer', 'min:0'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $includeBraggers = $validated['include_braggers_fee'] ?? false;
        $price = (float) $validated['product_price'];
        $cost = (float) $validated['manufacturing_cost'];
        $vatPercent = (float) $validated['vat_percent'];
        $sales = (int) $validated['monthly_sales'];
        $returns = (int) $validated['monthly_returns'];
        $shipping = (float) $validated['shipping_cost'];

        // Rates
        $bragsFeeRate = 0.10;
        $braggersFeeRate = 0.05;

        // Per Product Calc
        $bragsFee = $price * $bragsFeeRate;
        $braggersFee = $includeBraggers ? ($price * $braggersFeeRate) : 0;
        $vatAmount = $price * ($vatPercent / 100);

        // Net Profit Per Product = Price - Cost - Shipping - BragsFee - BraggersFee - VAT
        // Notice: In the screenshot, VAT = 2% but Net Profit = -5.50 (which means VAT wasn't subtracted, or VAT=0 in that specific state). 
        // We will include VAT subtraction here for correctness.
        $netProfitPerProduct = $price - $cost - $shipping - $bragsFee - $braggersFee - $vatAmount;
        $marginPerProduct = $price > 0 ? ($netProfitPerProduct / $price) * 100 : 0;

        // Monthly Calc
        $netSales = max(0, $sales - $returns);
        $monthlyRevenue = $netSales * $price;
        $monthlyCost = $netSales * $cost;
        
        // Shipping and fees are usually lost on all shipments regardless of return status
        $monthlyShipping = $sales * $shipping;
        $monthlyBragsFee = $sales * $bragsFee;
        $monthlyBraggersFee = $sales * $braggersFee;
        
        // VAT is usually only paid on kept items (net sales)
        $monthlyVat = $netSales * $vatAmount;

        $monthlyNetProfit = $monthlyRevenue - $monthlyCost - $monthlyShipping - $monthlyBragsFee - $monthlyBraggersFee - $monthlyVat;
        
        if ($monthlyRevenue > 0) {
            $monthlyMargin = ($monthlyNetProfit / $monthlyRevenue) * 100;
        } else {
            $monthlyMargin = $monthlyNetProfit < 0 ? -INF : 0;
        }

        return response()->json([
            'per_product' => [
                'brags_seller_fee' => number_format($bragsFee, 2, '.', ''),
                'braggers_fee' => number_format($braggersFee, 2, '.', ''),
                'ship_to_customer_cost' => number_format($shipping, 2, '.', ''),
                'net_profit' => number_format($netProfitPerProduct, 2, '.', ''),
                'net_margin_percent' => is_infinite($marginPerProduct) ? '-Infinity' : number_format($marginPerProduct, 2, '.', ''),
            ],
            'monthly' => [
                'brags_seller_fee' => number_format($monthlyBragsFee, 2, '.', ''),
                'braggers_fee' => number_format($monthlyBraggersFee, 2, '.', ''),
                'shipping_costs' => number_format($monthlyShipping, 2, '.', ''),
                'net_profit' => number_format($monthlyNetProfit, 2, '.', ''),
                'net_margin_percent' => is_infinite($monthlyMargin) ? '-Infinity' : number_format($monthlyMargin, 2, '.', ''),
            ]
        ]);
    }
}
