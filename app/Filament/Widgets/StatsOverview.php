<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Calculate total revenue from paid orders
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total');

        // Count orders from this month
        $ordersThisMonth = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Count products currently in stock
        $productsInStock = Product::where('in_stock', true)
            ->where('stock_quantity', '>', 0)
            ->count();

        // Count active users (customers and admin users)
        $activeUsers = User::count();

        return [
            Stat::make('Total Revenue', 'KES ' . number_format($totalRevenue, 2))
                ->description('From all paid orders')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]), // Sample chart data

            Stat::make('Orders This Month', $ordersThisMonth)
                ->description('Orders in ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info')
                ->chart([3, 5, 4, 6, 7, 8, 6, 5]),

            Stat::make('Products In Stock', $productsInStock)
                ->description('Available products')
                ->descriptionIcon('heroicon-m-cube')
                ->color('warning')
                ->chart([10, 12, 11, 13, 12, 14, 15, 14]),

            Stat::make('Total Users', $activeUsers)
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->chart([5, 6, 7, 8, 9, 10, 12, 11]),
        ];
    }
}
