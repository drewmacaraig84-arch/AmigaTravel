<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FerryRoute;
use App\Models\Vehicle;
use App\Models\Operator;

class FerryRouteOperatorFixSeeder extends Seeder
{
    public function run(): void
    {
        $operators = Operator::all();

        foreach (FerryRoute::all() as $route) {
            $matchedOperator = $this->matchOperator($route->operator, $operators);
            
            if ($matchedOperator && $route->operator_id !== $matchedOperator->id) {
                $route->operator_id = $matchedOperator->id;
                $route->saveQuietly();
            }
        }

        foreach (Vehicle::all() as $vehicle) {
            $matchedOperator = $this->matchOperator($vehicle->operator, $operators);
            
            if ($matchedOperator && $vehicle->operator_id !== $matchedOperator->id) {
                $vehicle->operator_id = $matchedOperator->id;
                $vehicle->saveQuietly();
            }
        }
    }

    private function matchOperator(?string $operatorName, $operators): ?Operator
    {
        if (empty($operatorName)) {
            return null;
        }

        // Exact match
        $exactMatch = $operators->firstWhere('name', $operatorName);
        if ($exactMatch) return $exactMatch;

        // Substring match
        $operatorName = strtolower($operatorName);
        
        if (str_contains($operatorName, 'pal') || str_contains($operatorName, 'philippine airline')) {
            return $operators->first(fn($op) => stripos($op->name, 'Philippine Airline') !== false);
        }

        if (str_contains($operatorName, 'cebu') || str_contains($operatorName, 'cebupecific')) {
            return $operators->first(fn($op) => stripos($op->name, 'Cebu') !== false);
        }

        if (str_contains($operatorName, 'airasia')) {
            return $operators->first(fn($op) => stripos($op->name, 'AirAsia') !== false);
        }

        if (str_contains($operatorName, '2go')) {
            return $operators->first(fn($op) => stripos($op->name, '2GO') !== false);
        }

        if (str_contains($operatorName, 'starlite')) {
            return $operators->first(fn($op) => stripos($op->name, 'Starlite') !== false);
        }

        return null;
    }
}
