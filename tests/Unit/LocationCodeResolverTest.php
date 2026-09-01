<?php

namespace Tests\Unit;

use App\Services\LocationCodeResolver;
use PHPUnit\Framework\TestCase;

class LocationCodeResolverTest extends TestCase
{
    private LocationCodeResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new LocationCodeResolver();
    }

    // --- Airline (IATA) resolution ---

    public function test_resolves_mnl_to_manila(): void
    {
        $this->assertEquals('Manila', $this->resolver->resolve('MNL', 'airline'));
    }

    public function test_resolves_ceb_to_cebu(): void
    {
        $this->assertEquals('Cebu', $this->resolver->resolve('CEB', 'airline'));
    }

    public function test_resolves_dvo_to_davao(): void
    {
        $this->assertEquals('Davao', $this->resolver->resolve('DVO', 'airline'));
    }

    public function test_resolves_mph_to_boracay(): void
    {
        $this->assertEquals('Boracay (Caticlan)', $this->resolver->resolve('MPH', 'airline'));
    }

    public function test_resolves_nrt_to_tokyo(): void
    {
        $this->assertEquals('Tokyo (Narita)', $this->resolver->resolve('NRT', 'airline'));
    }

    public function test_resolves_icn_to_seoul(): void
    {
        $this->assertEquals('Seoul (Incheon)', $this->resolver->resolve('ICN', 'airline'));
    }

    public function test_resolves_sin_to_singapore(): void
    {
        $this->assertEquals('Singapore', $this->resolver->resolve('SIN', 'airline'));
    }

    public function test_resolves_hkg_to_hong_kong(): void
    {
        $this->assertEquals('Hong Kong', $this->resolver->resolve('HKG', 'airline'));
    }

    // --- Ferry resolution ---

    public function test_resolves_btg_to_batangas(): void
    {
        $this->assertEquals('Batangas', $this->resolver->resolve('BTG', 'ferry'));
    }

    public function test_resolves_cal_to_calapan(): void
    {
        $this->assertEquals('Calapan', $this->resolver->resolve('CAL', 'ferry'));
    }

    public function test_resolves_ctl_to_caticlan(): void
    {
        $this->assertEquals('Caticlan', $this->resolver->resolve('CTL', 'ferry'));
    }

    public function test_resolves_mnh_to_manila_ferry(): void
    {
        $this->assertEquals('Manila', $this->resolver->resolve('MNL', 'ferry'));
    }

    public function test_resolves_rox_to_roxas(): void
    {
        $this->assertEquals('Roxas', $this->resolver->resolve('ROX', 'ferry'));
    }

    public function test_resolves_starlite_specific_ferry_codes(): void
    {
        $this->assertEquals('Romblon', $this->resolver->resolve('ROM', 'ferry'));
        $this->assertEquals('Sibuyan (Magdiwang)', $this->resolver->resolve('SIB', 'ferry'));
        $this->assertEquals('Sibuyan (Magdiwang)', $this->resolver->resolve('MAG', 'ferry'));
        $this->assertEquals('Cajidiocan', $this->resolver->resolve('CAJ', 'ferry'));
        $this->assertEquals('Odiongan', $this->resolver->resolve('ODI', 'ferry'));
        $this->assertEquals('Roxas Mindoro', $this->resolver->resolve('RXM', 'ferry'));
        $this->assertEquals('Roxas Capiz', $this->resolver->resolve('RXC', 'ferry'));
        $this->assertEquals('Buruanga', $this->resolver->resolve('BUR', 'ferry'));
        $this->assertEquals('Dapitan', $this->resolver->resolve('DAP', 'ferry'));
        $this->assertEquals('Surigao', $this->resolver->resolve('SUR', 'ferry'));
        $this->assertEquals('Nasipit', $this->resolver->resolve('NAG', 'ferry'));
    }

    // --- Case insensitivity ---

    public function test_codes_are_case_insensitive(): void
    {
        $this->assertEquals('Manila', $this->resolver->resolve('mnl', 'airline'));
        $this->assertEquals('Cebu', $this->resolver->resolve('ceb', 'airline'));
        $this->assertEquals('Batangas', $this->resolver->resolve('btg', 'ferry'));
    }

    // --- Passthrough for full names ---

    public function test_full_name_passes_through_unchanged(): void
    {
        $this->assertEquals('Manila', $this->resolver->resolve('Manila', 'airline'));
        $this->assertEquals('Batangas', $this->resolver->resolve('Batangas', 'ferry'));
        $this->assertEquals('Boracay (Caticlan)', $this->resolver->resolve('Boracay (Caticlan)', 'airline'));
    }

    public function test_unknown_code_passes_through_unchanged(): void
    {
        $this->assertEquals('XYZ', $this->resolver->resolve('XYZ', 'airline'));
        $this->assertEquals('Unknown Port', $this->resolver->resolve('Unknown Port', 'ferry'));
    }

    // --- Null / blank ---

    public function test_null_returns_empty_string(): void
    {
        $this->assertEquals('', $this->resolver->resolve(null, 'airline'));
        $this->assertEquals('', $this->resolver->resolve('', 'ferry'));
    }
}
