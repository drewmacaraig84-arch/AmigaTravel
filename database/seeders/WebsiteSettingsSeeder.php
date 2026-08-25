<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WebsiteSetting;

class WebsiteSettingsSeeder extends Seeder
{
    public function run()
    {
        $setting = WebsiteSetting::getOrCreateByPage('services');

        $content = [
            'badge' => 'Services',
            'title' => 'Our Travel Services',
            'description' => 'Explore a full range of reliable travel, transit ticketing, and customizable packages designed to make your journey completely hassle-free.',
            'service_cta' => [
                'badge' => 'New Booking System',
                'title' => 'Book Ferry Tickets Directly Online',
                'description' => 'Quickly check available schedules, fares, and cabins for 2GO and Starlite Complete your passenger credentials and print tickets instantly.',
                'button_text' => 'Start Direct Booking',
            ],
            'travel_service_cards' => [
                [
                    'title' => '2GO Booking',
                    'description' => 'Book premier overnight ship accommodation and fast cargo transits with 2GO. Ideal for family retreats, business logistics, and leisure trips.',
                    'note' => 'Available Online',
                    'image' => 'images/2GO-Logo.png',
                    'button_text' => 'Book Now',
                    'link' => '/book/new?operator=' . urlencode('2GO') . '&trip_type=one_way&mode=ferry',
                    'color' => 'text-pink-600',
                ],
                [
                    'title' => 'Starlite',
                    'description' => 'Affordable regional transits between Batangas, Calapan, and Roxas. We manage standard ferry bookings and roll-on/roll-off (RoRo) cargo slots.',
                    'note' => 'Available Online',
                    'image' => 'images/Starlite_Logo.png',
                    'button_text' => 'Book Now',
                    'link' => '/book/new?operator=' . urlencode('Starlite') . '&trip_type=one_way&mode=ferry',
                    'color' => 'text-emerald-700',
                ],
                [
                    'title' => 'Cebu Pacific',
                    'description' => 'Domestic and international flights powered by leading carriers including AirAsia, Cebu Pacific, and Philippine Airline (PAL). Hassle-free check-ins and seat bookings.',
                    'note' => 'PAL, CebuPac, AirAsia',
                    'image' => 'images/CebuPecific-Logo.png',
                    'button_text' => 'Book Now',
                    'link' => '/book/new?operator=' . urlencode('Cebu Pacific') . '&trip_type=one_way&mode=airline',
                    'color' => 'text-blue-600',
                ],
                [
                    'title' => 'Philippine Airlines',
                    'description' => 'Philippine Airlines flights with premium support and flexible fare options.',
                    'note' => 'PAL & International',
                    'image' => 'images/Pal-Logo.jfif',
                    'button_text' => 'Book Now',
                    'link' => '/book/new?operator=' . urlencode('Philippine Airlines') . '&trip_type=one_way&mode=airline',
                    'color' => 'text-purple-600',
                ],
                [
                    'title' => 'AirAsia',
                    'description' => 'Find low-cost airline tickets and convenient domestic connections.',
                    'note' => 'Low Fare Flights',
                    'image' => 'images/AirAsia-Logo.png',
                    'button_text' => 'Book Now',
                    'link' => '/book/new?operator=' . urlencode('AirAsia') . '&trip_type=one_way&mode=airline',
                    'color' => 'text-orange-600',
                ],
                [
                    'title' => 'Custom Travel Arrangements',
                    'description' => 'Tailored travel packages for corporate retreats, family reunions, and large groups. We handle flight connections, hotel accommodation blocks, and group transport.',
                    'note' => 'Tailored For Groups',
                    'button_text' => 'Learn more',
                    'link' => '/contact-us',
                    'color' => 'text-teal-700',
                ],
            ],
            'service_cards' => [
                [
                    'icon' => 'M13 5l7 7-7 7M5 5l7 7-7 7',
                    'title' => '2GO Onboard Training',
                    'description' => 'Comprehensive onboarding and orientation programs for individuals joining 2GO operations, covering safety, customer service, and onboard protocols.',
                    'note' => 'For New Hires & Trainees',
                    'link' => url('/contact-us'),
                    'color' => 'text-pink-600',
                ],
                [
                    'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
                    'title' => 'Educational Tour',
                    'description' => 'Educational tours for students and academic groups, featuring visits to travel facilities, ports, and cultural sites for immersive learning experiences.',
                    'note' => 'For Schools & Groups',
                    'link' => url('/contact-us'),
                    'color' => 'text-emerald-700',
                ],
                [
                    'icon' => 'M12 14l9-5-9-5-9 5 9 5z',
                    'title' => 'Stay and Learn',
                    'description' => 'Combined accommodation and learning packages, perfect for workshops, seminars, and training sessions with comfortable stays included.',
                    'note' => 'Workshops & Seminars',
                    'link' => url('/contact-us'),
                    'color' => 'text-blue-600',
                ],
                [
                    'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
                    'title' => 'Marine Related Trainings',
                    'description' => 'Specialized training programs for maritime professionals, including safety, navigation, and vessel operations in partnership with marine institutions.',
                    'note' => 'For Mariners & Seafarers',
                    'link' => url('/contact-us'),
                    'color' => 'text-purple-600',
                ],
                [
                    'icon' => 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8',
                    'title' => 'Transport',
                    'description' => 'Reliable transport solutions including ferry, airline, and land transfers for individuals, groups, and corporate travel needs.',
                    'note' => 'Multi-Modal Transport',
                    'link' => url('/contact-us'),
                    'color' => 'text-orange-600',
                ],
                [
                    'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
                    'title' => 'Visa & Passport Assistance',
                    'description' => 'Complete assistance with visa applications and passport processing, helping you prepare required documents and navigate application procedures.',
                    'note' => 'Document Processing',
                    'link' => url('/contact-us'),
                    'color' => 'text-teal-700',
                ],
            ],
        ];

        $setting->content = $content;
        $setting->save();
    }
}
