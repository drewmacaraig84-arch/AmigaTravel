<?php

namespace App\Services;

/**
 * Resolves location codes (IATA airport codes or ferry port shortcodes) to the
 * canonical location names used in the system.
 *
 * Codes are matched case-insensitively. If no match is found the original value
 * is returned unchanged, so full location names continue to work as-is.
 *
 * Airline codes follow the official IATA standard.
 * Ferry codes follow practical shorthands aligned with the 5 default operators:
 *   Airline : Philippine Airlines, Cebu Pacific, AirAsia
 *   Ferry   : 2GO, Starlite
 */
class LocationCodeResolver
{
    /**
     * Airline (IATA) code => canonical city/location name.
     * Names match exactly what the RouteScheduleSeeder and system use.
     */
    private const AIRLINE_CODES = [
        // Philippines domestic
        'MNL' => 'Manila',
        'CEB' => 'Cebu',
        'DVO' => 'Davao',
        'ILO' => 'Iloilo',
        'MPH' => 'Boracay (Caticlan)',
        'CRK' => 'Clark',
        'KLO' => 'Kalibo',
        'PPS' => 'Puerto Princesa',
        'LAO' => 'Laoag',
        'ZAM' => 'Zamboanga',
        'GES' => 'General Santos',
        'TAG' => 'Tagbilaran',
        'LGP' => 'Legazpi',
        'DRP' => 'Legazpi',
        'BCD' => 'Bacolod',
        'BXU' => 'Butuan',
        'CGY' => 'Cagayan de Oro',
        'DGT' => 'Dumaguete',
        'TAC' => 'Tacloban',
        'CBO' => 'Cotabato',
        'BSO' => 'Basco',
        'DPL' => 'Dipolog',
        'RXS' => 'Roxas',
        'SUG' => 'Surigao',
        'BAG' => 'Baguio',
        'CGM' => 'Camiguin',
        'SIR' => 'Siargao',
        'CYZ' => 'Cauayan',
        'EUQ' => 'San Jose de Buenavista',
        'AAV' => 'Allah Valley',
        'JOL' => 'Jolo',
        'OZC' => 'Ozamiz',

        // International — routes seeded for the 3 airline operators
        'NRT' => 'Tokyo (Narita)',
        'HND' => 'Tokyo (Haneda)',
        'ICN' => 'Seoul (Incheon)',
        'GMP' => 'Seoul (Gimpo)',
        'SIN' => 'Singapore',
        'HKG' => 'Hong Kong',
        'KUL' => 'Kuala Lumpur',
        'BKK' => 'Bangkok',
        'SYD' => 'Sydney',
        'MEL' => 'Melbourne',
        'LAX' => 'Los Angeles',
        'SFO' => 'San Francisco',
        'JFK' => 'New York',
        'DXB' => 'Dubai',
        'DOH' => 'Doha',
        'LHR' => 'London',
        'CDG' => 'Paris',
        'FRA' => 'Frankfurt',
        'AMS' => 'Amsterdam',
        'PVG' => 'Shanghai',
        'PEK' => 'Beijing',
        'CAN' => 'Guangzhou',
        'TPE' => 'Taipei',
        'GUM' => 'Guam',
        'HNL' => 'Honolulu',
    ];

    /**
     * Ferry port shortcode => canonical port/city name.
     * Names match exactly what the RouteScheduleSeeder uses (2GO, Starlite routes).
     */
    private const FERRY_CODES = [
        // --- Short codes ---
        'MNL' => 'Manila',
        'MAN' => 'Manila',
        'CEB' => 'Cebu',
        'CEL' => 'Cebu',
        'BTG' => 'Batangas',
        'BAT' => 'Batangas',
        'CAL' => 'Calapan',
        'CLP' => 'Calapan',
        'CTL' => 'Caticlan',
        'CAT' => 'Caticlan',
        'MPH' => 'Caticlan',
        'ROX' => 'Roxas',
        'RXS' => 'Roxas',
        'RXM' => 'Roxas Mindoro',
        'RXC' => 'Roxas Capiz',
        'ROM' => 'Romblon',
        'SIB' => 'Sibuyan (Magdiwang)',
        'MAG' => 'Sibuyan (Magdiwang)',
        'CAJ' => 'Cajidiocan',
        'ODI' => 'Odiongan',
        'BUR' => 'Buruanga',
        'DAN' => 'Danao',
        'DAP' => 'Dapitan',
        'ILO' => 'Iloilo',
        'CDO' => 'Cagayan de Oro',
        'CGY' => 'Cagayan de Oro',
        'BOH' => 'Tagbilaran',
        'TAG' => 'Tagbilaran',
        'TAB' => 'Tagbilaran',
        'DGT' => 'Dumaguete',
        'OZM' => 'Ozamiz',
        'ZAM' => 'Zamboanga',
        'GEN' => 'General Santos',
        'NAG' => 'Nasipit',
        'BUT' => 'Butuan',
        'SRG' => 'Surigao',
        'SUR' => 'Surigao',
        'DVO' => 'Davao',
        'ELP' => 'El Nido',
        'CON' => 'Coron',
        'PPS' => 'Puerto Princesa',

        // --- Full-name uppercase keys (for raw XLSX city names from Starlite timetable) ---
        'MANILA' => 'Manila',
        'CEBU' => 'Cebu',
        'BATANGAS' => 'Batangas',
        'CALAPAN' => 'Calapan',
        'CATICLAN' => 'Caticlan',
        'ROXAS MINDORO' => 'Roxas Mindoro',
        'ROXAS CAPIZ' => 'Roxas Capiz',
        'ROXAS, CAPIZ' => 'Roxas Capiz',
        'ROXAS CITY' => 'Roxas Capiz',
        'ROMBLON' => 'Romblon',
        'ROMBLOM' => 'Romblon', // Typo in Starlite XLSX
        'MAGDIWANG' => 'Sibuyan (Magdiwang)',
        'SIBUYAN (MAG)' => 'Sibuyan (Magdiwang)',
        'CAJIDIOCAN' => 'Cajidiocan',
        'ODIONGAN' => 'Odiongan',
        'BURUANGA' => 'Buruanga',
        'BURUANGGA' => 'Buruanga', // Typo in Starlite XLSX
        'DAPITAN' => 'Dapitan',
        'SURIGAO' => 'Surigao',
        'NASIPIT' => 'Nasipit',
        'DAVAO' => 'Davao',
        'ILOILO' => 'Iloilo',
        'ZAMBOANGA' => 'Zamboanga',

        // --- 2GO Excel typos ---
        'MANLA' => 'Manila',           // Typo in 2GO XLSX
        'MNAILA' => 'Manila',          // Alternate typo guard
        'ILO-ILO' => 'Iloilo',         // 2GO XLSX hyphenated spelling
        'ILO ILO' => 'Iloilo',
        'CAGAYAN' => 'Cagayan de Oro', // 2GO XLSX shorthand
        'CAGAYAN DE ORO' => 'Cagayan de Oro',
        'CAGAYAN DE ORO CITY' => 'Cagayan de Oro',
        'GENSAN' => 'General Santos',
        'GEN. SANTOS' => 'General Santos',
        'GENERAL SANTOS' => 'General Santos',
        'OZAMIS' => 'Ozamiz',          // Common alternate spelling
        'OZAMIZ' => 'Ozamiz',
        'DUMAGUETE' => 'Dumaguete',
        'TAGBILARAN' => 'Tagbilaran',
        'BUTUAN' => 'Butuan',
        'SIARGAO' => 'Siargao',
        'CORON' => 'Coron',
        'PUERTO PRINCESA' => 'Puerto Princesa',
        'DIPOLOG' => 'Dipolog',
        'BACOLOD' => 'Bacolod',
        'BATANGAS' => 'Batangas',
    ];

    /**
     * Resolve a location value to a canonical name.
     *
     * @param  string|null  $value  Raw value from CSV/XLSX (code or full name)
     * @param  string       $mode   'airline' or 'ferry'
     * @return string               Canonical name, or original value if no match
     */
    public function resolve(?string $value, string $mode = 'airline'): string
    {
        if (blank($value)) {
            return '';
        }

        $trimmed = trim($value);
        $key = strtoupper($trimmed);
        $map = $mode === 'ferry' ? self::FERRY_CODES : self::AIRLINE_CODES;

        if (isset($map[$key])) {
            return $map[$key];
        }

        // Passthrough: normalize ALL-CAPS full names to Title Case
        // so that "BATANGAS" from XLSX becomes "Batangas"
        if ($trimmed === strtoupper($trimmed) && strlen($trimmed) > 3) {
            return ucwords(strtolower($trimmed));
        }

        return $trimmed;
    }
}
