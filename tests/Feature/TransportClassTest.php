<?php

namespace Tests\Feature;

use App\Models\TransportClass;
use Tests\TestCase;

class TransportClassTest extends TestCase
{
    public function test_transport_class_accepts_airline_and_ferry_modes(): void
    {
        $airline = TransportClass::make([
            'name' => 'Economy',
            'mode' => 'airline',
            'operator' => 'Philippine Airlines',
            'price' => 1500,
            'is_active' => true,
        ]);

        $ferry = TransportClass::make([
            'name' => 'Tourist',
            'mode' => 'ferry',
            'operator' => '2GO',
            'price' => 800,
            'is_active' => true,
        ]);

        $this->assertSame('airline', $airline->mode);
        $this->assertSame('Philippine Airlines', $airline->operator);
        $this->assertSame('ferry', $ferry->mode);
        $this->assertSame('2GO', $ferry->operator);
    }
}
