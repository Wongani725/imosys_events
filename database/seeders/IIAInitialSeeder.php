<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Models\Hotel;
use App\Models\EventPrices;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class IIAInitialSeeder extends Seeder
{
    public function run()
    {
        // Create simplified roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $financeRole = Role::firstOrCreate(['name' => 'Finance', 'guard_name' => 'web']);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'wongani087@gmail.com'],
            [
                'name' => 'Wongani Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'status' => 'active',
                'total_web_logins' => 0,
                'total_mobile_app_logins' => 0,
            ]
        );
        $admin->assignRole($superAdminRole);

        // Create events
        $governanceEvent = Event::firstOrCreate(
            ['event_id' => 'IIA-GF-2026'],
            [
                'event_name' => '2026 Governance Forum',
                'theme' => 'The Currency of Trust: Governance as Strategy, Assurance as Proof',
                'start_date' => '2026-09-07',
                'end_date' => '2026-09-10',
                'event_venue' => 'Sunbird Nkopola',
                'venue' => 'Mangochi',
                'event_type' => 'governance',
                'event_status' => 'active',
                'event_gps_coordinates' => '-14.0500,35.1500',
                'total_sessions' => 10,
            ]
        );

        $mainEvent = Event::firstOrCreate(
            ['event_id' => 'IIA-AC-2026'],
            [
                'event_name' => '2026 Annual Conference',
                'theme' => 'The Currency of Trust: Governance as Strategy, Assurance as Proof',
                'start_date' => '2026-09-10',
                'end_date' => '2026-09-13',
                'event_venue' => 'Sun N Sand Holiday Resort',
                'venue' => 'Mangochi',
                'event_type' => 'main',
                'event_status' => 'active',
                'event_gps_coordinates' => '-14.0600,35.1600',
                'total_sessions' => 12,
            ]
        );

        // Create hotels with quantity
        $nkopolaHotel = Hotel::firstOrCreate(
            ['name' => 'Sunbird Nkopola'],
            [
                'event_id' => $governanceEvent->event_id,
                'venue_type' => 'governance',
                'quantity' => 70,
                'available_count' => 70,
                'booked_count' => 0,
                'gps_coordinates' => '-14.0500,35.1500',
                'extra_price' => 0,
            ]
        );

        $sunNSandHotel = Hotel::firstOrCreate(
            ['name' => 'Sun N Sand Holiday Resort'],
            [
                'event_id' => $mainEvent->event_id,
                'venue_type' => 'both',
                'quantity' => 110,
                'available_count' => 110,
                'booked_count' => 0,
                'gps_coordinates' => '-14.0600,35.1600',
                'extra_price' => 0,
            ]
        );

        // Create pricing tiers
        $this->createPrices($governanceEvent->event_id, 'governance');
        $this->createPrices($mainEvent->event_id, 'main');
    }

    private function createPrices($eventId, $eventType)
    {
        $prices = [];

        if ($eventType == 'governance') {
            $prices = [
                ['member_type' => 'Member', 'accommodation' => false, 'hotel' => null, 'spouse_included' => false, 'price' => 1200000, 'status' => 'Conference Only Members (no accommodation)'],
                ['member_type' => 'Member', 'accommodation' => true, 'hotel' => 'nkopola', 'spouse_included' => false, 'price' => 2250000, 'status' => 'Nkopola Members'],
                ['member_type' => 'Member', 'accommodation' => true, 'hotel' => 'nkopola', 'spouse_included' => true, 'price' => 2700000, 'status' => 'Nkopola Members with Spouse'],
                ['member_type' => 'Member', 'accommodation' => true, 'hotel' => 'sun_n_sand', 'spouse_included' => false, 'price' => 2200000, 'status' => 'Sun N Sand Members'],
                ['member_type' => 'Member', 'accommodation' => true, 'hotel' => 'sun_n_sand', 'spouse_included' => true, 'price' => 2800000, 'status' => 'Sun N Sand Members with Spouse'],
                ['member_type' => 'Non-Member', 'accommodation' => false, 'hotel' => null, 'spouse_included' => false, 'price' => 1350000, 'status' => 'Conference Only Non-Members (no accommodation)'],
                ['member_type' => 'Non-Member', 'accommodation' => true, 'hotel' => 'nkopola', 'spouse_included' => false, 'price' => 2500000, 'status' => 'Nkopola Non-Members'],
                ['member_type' => 'Non-Member', 'accommodation' => true, 'hotel' => 'nkopola', 'spouse_included' => true, 'price' => 3150000, 'status' => 'Nkopola Non-Members with Spouse'],
                ['member_type' => 'Non-Member', 'accommodation' => true, 'hotel' => 'sun_n_sand', 'spouse_included' => false, 'price' => 2350000, 'status' => 'Sun N Sand Non-Members'],
                ['member_type' => 'Non-Member', 'accommodation' => true, 'hotel' => 'sun_n_sand', 'spouse_included' => true, 'price' => 2950000, 'status' => 'Sun N Sand Non-Members with Spouse'],
            ];
        } else {
            $prices = [
                ['member_type' => 'Member', 'accommodation' => false, 'hotel' => null, 'spouse_included' => false, 'price' => 980000, 'status' => 'Conference Only Members (no accommodation)'],
                ['member_type' => 'Member', 'accommodation' => true, 'hotel' => 'nkopola', 'spouse_included' => false, 'price' => 2200000, 'status' => 'Nkopola Members'],
                ['member_type' => 'Member', 'accommodation' => true, 'hotel' => 'nkopola', 'spouse_included' => true, 'price' => 2800000, 'status' => 'Nkopola Members with Spouse'],
                ['member_type' => 'Member', 'accommodation' => true, 'hotel' => 'sun_n_sand', 'spouse_included' => false, 'price' => 1755000, 'status' => 'Sun N Sand Members'],
                ['member_type' => 'Member', 'accommodation' => true, 'hotel' => 'sun_n_sand', 'spouse_included' => true, 'price' => 2350000, 'status' => 'Sun N Sand Members with Spouse'],
                ['member_type' => 'Non-Member', 'accommodation' => false, 'hotel' => null, 'spouse_included' => false, 'price' => 1150000, 'status' => 'Conference Only Non-Members (no accommodation)'],
                ['member_type' => 'Non-Member', 'accommodation' => true, 'hotel' => 'nkopola', 'spouse_included' => false, 'price' => 2500000, 'status' => 'Nkopola Non-Members'],
                ['member_type' => 'Non-Member', 'accommodation' => true, 'hotel' => 'nkopola', 'spouse_included' => true, 'price' => 3100000, 'status' => 'Nkopola Non-Members with Spouse'],
                ['member_type' => 'Non-Member', 'accommodation' => true, 'hotel' => 'sun_n_sand', 'spouse_included' => false, 'price' => 2150000, 'status' => 'Sun N Sand Non-Members'],
                ['member_type' => 'Non-Member', 'accommodation' => true, 'hotel' => 'sun_n_sand', 'spouse_included' => true, 'price' => 2750000, 'status' => 'Sun N Sand Non-Members with Spouse'],
            ];
        }

        foreach ($prices as $price) {
            EventPrices::firstOrCreate([
                'event_id' => $eventId,
                'member_type' => $price['member_type'],
                'accommodation' => $price['accommodation'],
                'hotel' => $price['hotel'],
                'spouse_included' => $price['spouse_included'],
                'event_type' => $eventType,
            ], [
                'status' => $price['status'],
                'price' => $price['price'],
                'extra_person_price' => 600000.00,
            ]);
        }
    }
}
