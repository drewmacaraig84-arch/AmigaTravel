<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TransportClass;
use App\Models\FerryRoute;
use App\Models\Schedule;
use App\Models\ScheduleTransportClass;
use App\Models\PaymentSetting;
use App\Filament\Resources\TransportClassResource\Pages\ViewTransportClass;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use Filament\Facades\Filament;

class ViewTransportClassPromoTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        PaymentSetting::firstOrCreate([], [
            'admin_fee_long_haul' => 50,
            'admin_fee_short_haul' => 25,
            'transaction_fee_long_haul' => 20,
            'transaction_fee_short_haul' => 10,
        ]);
    }

    public function test_batch_promo_modal_updates_schedules(): void
    {
        $route = FerryRoute::create([
            'origin' => 'Batangas',
            'destination' => 'Calapan',
            'mode' => 'ferry',
            'operator' => 'Starlite Ferries',
            'is_active' => true,
        ]);

        $tc = TransportClass::create([
            'name' => 'Economy Class',
            'price' => 500.00,
            'is_active' => true,
        ]);

        $schedules = collect(range(1, 3))->map(function ($i) use ($route, $tc) {
            $dep = now()->addDays($i)->setTime(8, 0);
            $schedule = Schedule::create([
                'ferry_route_id' => $route->id,
                'departure_date' => $dep->format('Y-m-d'),
                'departure_time' => $dep->format('Y-m-d H:i:s'),
                'arrival_time' => $dep->copy()->addHours(2)->format('Y-m-d H:i:s'),
                'price' => 800.00,
                'duration_minutes' => 120,
                'status' => 'scheduled',
                'is_active' => true,
            ]);

            $schedule->transportClasses()->attach($tc->id, [
                'additional_price' => 500.00,
                'tickets_available' => 50,
                'rate_type' => 'regular',
                'is_promo' => false,
                'is_active' => true,
            ]);

            return $schedule;
        });

        $scheduleIds = $schedules->pluck('id')->toArray();

        // Test Livewire component
        Livewire::actingAs($this->admin)->test(ViewTransportClass::class, ['record' => $tc->id])
            ->set('selectedSchedules', $scheduleIds)
            ->call('openPromoModal')
            ->assertSet('showPromoModal', true)
            ->set('modalRateType', 'promotional')
            ->set('modalPrice', '350.00')
            ->set('modalPromoType', 'temporary')
            ->set('modalDurationStart', now()->format('Y-m-d\TH:i'))
            ->set('modalDurationEnd', now()->addDays(7)->format('Y-m-d\TH:i'))
            ->call('applyPromoModal')
            ->assertSet('showPromoModal', false)
            ->assertSet('selectedSchedules', []);

        // Verify database records
        foreach ($scheduleIds as $id) {
            $stc = ScheduleTransportClass::where('schedule_id', $id)
                ->where('transport_class_id', $tc->id)
                ->first();

            $this->assertNotNull($stc);
            $this->assertEquals('promotional', $stc->rate_type);
            $this->assertTrue((bool)$stc->is_promo);
            $this->assertEquals(350.00, (float)$stc->additional_price);
            $this->assertEquals('temporary', $stc->promo_type);
            $this->assertNotNull($stc->promo_duration_start);
            $this->assertNotNull($stc->promo_duration_end);
        }
    }

    public function test_reverting_promo_to_regular_fare(): void
    {
        $route = FerryRoute::create([
            'origin' => 'Batangas',
            'destination' => 'Calapan',
            'mode' => 'ferry',
            'operator' => 'Starlite Ferries',
            'is_active' => true,
        ]);

        $tc = TransportClass::create([
            'name' => 'Tourist Class',
            'price' => 600.00,
            'is_active' => true,
        ]);

        $dep = now()->addDays(2)->setTime(10, 0);
        $schedule = Schedule::create([
            'ferry_route_id' => $route->id,
            'departure_date' => $dep->format('Y-m-d'),
            'departure_time' => $dep->format('Y-m-d H:i:s'),
            'arrival_time' => $dep->copy()->addHours(2)->format('Y-m-d H:i:s'),
            'price' => 800.00,
            'duration_minutes' => 120,
            'status' => 'scheduled',
            'is_active' => true,
        ]);

        $schedule->transportClasses()->attach($tc->id, [
            'additional_price' => 400.00,
            'tickets_available' => 50,
            'rate_type' => 'super_promotional',
            'is_promo' => true,
            'promo_type' => 'permanent',
            'promo_duration_start' => now()->subDay(),
            'promo_duration_end' => now()->addDays(5),
            'is_active' => true,
        ]);

        Livewire::actingAs($this->admin)->test(ViewTransportClass::class, ['record' => $tc->id])
            ->set('selectedSchedules', [$schedule->id])
            ->call('openPromoModal')
            ->set('modalRateType', 'regular')
            ->set('modalPrice', '600.00')
            ->call('applyPromoModal');

        $stc = ScheduleTransportClass::where('schedule_id', $schedule->id)
            ->where('transport_class_id', $tc->id)
            ->first();

        $this->assertEquals('regular', $stc->rate_type);
        $this->assertFalse((bool)$stc->is_promo);
        $this->assertNull($stc->promo_type);
        $this->assertNull($stc->promo_duration_start);
        $this->assertNull($stc->promo_duration_end);
        $this->assertEquals(600.00, (float)$stc->additional_price);
    }

    public function test_price_restores_to_original_regular_price_when_restored(): void
    {
        $route = FerryRoute::create([
            'origin' => 'Batangas',
            'destination' => 'Calapan',
            'mode' => 'ferry',
            'operator' => 'Starlite Ferries',
            'is_active' => true,
        ]);

        // Transport class base price is 0.00 (just like user's setup)
        $tc = TransportClass::create([
            'name' => 'Reclining Seat',
            'price' => 0.00,
            'is_active' => true,
        ]);

        $dep = now()->addDays(3)->setTime(8, 0);
        $schedule = Schedule::create([
            'ferry_route_id' => $route->id,
            'departure_date' => $dep->format('Y-m-d'),
            'departure_time' => $dep->format('Y-m-d H:i:s'),
            'arrival_time' => $dep->copy()->addHours(2)->format('Y-m-d H:i:s'),
            'price' => 0.00,
            'duration_minutes' => 120,
            'status' => 'scheduled',
            'is_active' => true,
        ]);

        // Schedule regular add-on is 680.00
        $schedule->transportClasses()->attach($tc->id, [
            'additional_price' => 680.00,
            'tickets_available' => 40,
            'rate_type' => 'regular',
            'is_promo' => false,
            'is_active' => true,
        ]);

        // 1. Turn it to promo with discounted price 500.00
        Livewire::actingAs($this->admin)->test(ViewTransportClass::class, ['record' => $tc->id])
            ->set('selectedSchedules', [$schedule->id])
            ->call('openPromoModal')
            ->set('modalRateType', 'promotional')
            ->set('modalPrice', '500.00')
            ->set('modalPromoType', 'temporary')
            ->set('modalDurationStart', now()->subDay()->format('Y-m-d\TH:i'))
            ->set('modalDurationEnd', now()->addDays(2)->format('Y-m-d\TH:i'))
            ->call('applyPromoModal');

        $stc = ScheduleTransportClass::where('schedule_id', $schedule->id)
            ->where('transport_class_id', $tc->id)
            ->first();

        $this->assertEquals(500.00, (float) $stc->additional_price);
        $this->assertEquals(680.00, (float) $stc->original_price);
        $this->assertEquals('promotional', $stc->rate_type);
        $this->assertEquals(500.00, $stc->getEffectivePrice());

        // 2. Click Restore Price
        Livewire::actingAs($this->admin)->test(ViewTransportClass::class, ['record' => $tc->id])
            ->call('restorePrice', $schedule->id);

        $stc->refresh();
        $this->assertEquals(680.00, (float) $stc->additional_price, 'Price should restore to 680.00, not 0.00');
        $this->assertNull($stc->original_price);
        $this->assertEquals('regular', $stc->rate_type);
        $this->assertFalse((bool) $stc->is_promo);
    }

    public function test_temporary_promo_expiry_automatically_returns_original_price(): void
    {
        $route = FerryRoute::create([
            'origin' => 'Batangas',
            'destination' => 'Calapan',
            'mode' => 'ferry',
            'operator' => 'Starlite Ferries',
            'is_active' => true,
        ]);

        $tc = TransportClass::create([
            'name' => 'Reclining Seat',
            'price' => 0.00,
            'is_active' => true,
        ]);

        $dep = now()->addDays(4)->setTime(12, 0);
        $schedule = Schedule::create([
            'ferry_route_id' => $route->id,
            'departure_date' => $dep->format('Y-m-d'),
            'departure_time' => $dep->format('Y-m-d H:i:s'),
            'arrival_time' => $dep->copy()->addHours(2)->format('Y-m-d H:i:s'),
            'price' => 0.00,
            'duration_minutes' => 120,
            'status' => 'scheduled',
            'is_active' => true,
        ]);

        // Attach with expired temporary promo: original was 680, promo was 500
        $schedule->transportClasses()->attach($tc->id, [
            'additional_price' => 500.00,
            'original_price' => 680.00,
            'tickets_available' => 40,
            'rate_type' => 'promotional',
            'is_promo' => true,
            'promo_type' => 'temporary',
            'promo_duration_start' => now()->subDays(5),
            'promo_duration_end' => now()->subDay(), // Expired!
            'is_active' => true,
        ]);

        $stc = ScheduleTransportClass::where('schedule_id', $schedule->id)
            ->where('transport_class_id', $tc->id)
            ->first();

        // Effective rate type reverts to regular
        $this->assertEquals('regular', $stc->getEffectiveRateType());
        // Effective price automatically falls back to original_price (680.00)
        $this->assertEquals(680.00, $stc->getEffectivePrice());
    }
}
