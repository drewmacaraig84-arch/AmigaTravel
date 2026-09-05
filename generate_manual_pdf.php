<?php
/**
 * Amiga Gracia Travel Services - Master User Manual PDF Generator
 * Final Publication Edition (Strict 14-Page Perfection, 100% Accurate Table of Contents)
 */

require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');

echo "Generating Publication-Grade User Manual PDF (14-Page Accurate Edition)...\n";

function getBase64Image($relativePath) {
    $fullPath = __DIR__ . '/' . ltrim($relativePath, '/\\');
    if (file_exists($fullPath)) {
        $mime = mime_content_type($fullPath) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
    }
    return '';
}

$logoBase64 = getBase64Image('public/images/amiga-logo-transparent.png');
$appIconBase64 = getBase64Image('public/images/app-icon-original.png');
$twoGoLogo = getBase64Image('public/images/2GO-Logo.png');
$starliteLogo = getBase64Image('public/images/Starlite_Logo.png');
$cebuPacificLogo = getBase64Image('public/images/CebuPecific-Logo.png');
$airAsiaLogo = getBase64Image('public/images/AirAsia-Logo.png');

function buildHtml() {
    global $logoBase64, $appIconBase64, $twoGoLogo, $starliteLogo, $cebuPacificLogo, $airAsiaLogo;

    ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Amiga Gracia Travel Services - Complete System User Manual</title>
    <style>
        @page {
            margin: 15mm 14mm 15mm 14mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        .page-break {
            page-break-after: always;
        }

        /* Typography */
        h1, h2, h3, h4, h5 {
            color: #0f172a;
            margin-top: 0;
            font-weight: bold;
        }
        h1 {
            font-size: 18pt;
            line-height: 1.2;
            color: #216417;
            margin-bottom: 6px;
        }
        h2 {
            font-size: 12pt;
            color: #216417;
            border-bottom: 2px solid #216417;
            padding-bottom: 3px;
            margin-top: 14px;
            margin-bottom: 8px;
        }
        h3 {
            font-size: 10pt;
            color: #1e293b;
            margin-top: 10px;
            margin-bottom: 5px;
        }
        h4 {
            font-size: 9pt;
            color: #334155;
            margin-top: 8px;
            margin-bottom: 4px;
        }
        p {
            margin-top: 0;
            margin-bottom: 5px;
        }

        .text-green { color: #216417; }
        .text-pink { color: #ee018d; }
        .text-muted { color: #64748b; }
        .text-bold { font-weight: bold; }

        /* Cover Page */
        .cover-container {
            text-align: center;
            padding-top: 15mm;
            padding-bottom: 10mm;
        }
        .cover-logo {
            max-width: 230px;
            height: auto;
            margin-bottom: 16px;
        }
        .cover-badge {
            display: inline-block;
            background-color: #f0fdf4;
            border: 1px solid #86efac;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 8.5pt;
            font-weight: bold;
            color: #166534;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 14px;
        }
        .cover-title {
            font-size: 23pt;
            font-weight: bold;
            color: #216417;
            line-height: 1.2;
            margin-bottom: 8px;
        }
        .cover-subtitle {
            font-size: 10.5pt;
            color: #475569;
            max-width: 520px;
            margin: 0 auto 16px auto;
            line-height: 1.4;
        }
        .cover-divider {
            width: 70px;
            height: 3px;
            background-color: #ee018d;
            margin: 0 auto 18px auto;
            border-radius: 2px;
        }
        .cover-pillars-box {
            width: 100%;
            margin-top: 15px;
            margin-bottom: 18px;
        }
        .cover-pillar-card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 6px;
            text-align: center;
        }
        .cover-meta {
            margin-top: 20px;
            font-size: 8pt;
            color: #64748b;
            line-height: 1.5;
            border-top: 1px solid #e2e8f0;
            padding-top: 14px;
        }

        /* Callouts */
        .callout {
            border-radius: 5px;
            padding: 7px 10px;
            margin: 6px 0;
            font-size: 8pt;
            page-break-inside: avoid;
        }
        .callout-tip {
            background-color: #f0fdf4;
            border-left: 4px solid #16a34a;
            color: #166534;
        }
        .callout-info {
            background-color: #f0f9ff;
            border-left: 4px solid #0284c7;
            color: #0369a1;
        }
        .callout-warning {
            background-color: #fffbeb;
            border-left: 4px solid #d97706;
            color: #92400e;
        }
        .callout-danger {
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
            color: #991b1b;
        }
        .callout-title {
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.5px;
        }

        /* Step Cards */
        .step-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 6px 8px;
            margin-bottom: 6px;
            page-break-inside: avoid;
        }
        .step-badge {
            display: inline-block;
            background-color: #216417;
            color: #ffffff;
            font-weight: bold;
            font-size: 7.5pt;
            padding: 1px 6px;
            border-radius: 8px;
            margin-right: 4px;
        }
        .step-title {
            font-weight: bold;
            font-size: 8.5pt;
            color: #0f172a;
        }

        /* Tables */
        table.styled-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
            font-size: 7.5pt;
            page-break-inside: avoid;
        }
        table.styled-table th {
            background-color: #216417;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 4px 6px;
            border: 1px solid #1e5a15;
        }
        table.styled-table td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        table.styled-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-green { background-color: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .badge-amber { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-red { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-blue { background-color: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
        .badge-purple { background-color: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe; }

        .two-col {
            width: 100%;
            margin: 4px 0;
            page-break-inside: avoid;
        }
        .col-half {
            width: 49%;
            vertical-align: top;
        }

        /* Table of Contents Single Page Two-Column */
        .toc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.2pt;
        }
        .toc-table td {
            padding: 2.2px 0;
            border-bottom: 1px dotted #cbd5e1;
        }
        .toc-part {
            font-weight: bold;
            color: #216417;
            padding-top: 5px !important;
            border-bottom: 1.5px solid #216417 !important;
            font-size: 7.8pt;
        }
        .toc-page {
            text-align: right;
            font-weight: bold;
            color: #475569;
            width: 25px;
        }

        .checklist-item {
            margin-bottom: 4px;
            font-size: 8pt;
        }
        .check-box {
            display: inline-block;
            width: 8px;
            height: 8px;
            border: 1px solid #475569;
            margin-right: 4px;
            border-radius: 2px;
        }
        .logo-strip {
            margin: 6px 0;
            text-align: center;
            background-color: #f8fafc;
            padding: 4px;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
            page-break-inside: avoid;
        }
        .logo-img {
            max-height: 20px;
            width: auto;
            margin: 0 8px;
            vertical-align: middle;
        }
    </style>
</head>
<body>

    <!-- ========================================================================= -->
    <!-- COVER PAGE (Page 1) -->
    <!-- ========================================================================= -->
    <div class="cover-container page-break">
        <div class="cover-badge">Official Operating Documentation</div>
        <br>
        <?php if (!empty($logoBase64)): ?>
            <img src="<?php echo $logoBase64; ?>" class="cover-logo" alt="Amiga Gracia Travel Services" /><br>
        <?php endif; ?>

        <div class="cover-title">COMPLETE SYSTEM<br>USER MANUAL</div>
        <div class="cover-divider"></div>
        <div class="cover-subtitle">
            A Plain-Language, Step-by-Step Operations Handbook for the<br>
            <strong>Administrator Dashboard, Staff Portal, Customer Website, and Mobile App</strong>
        </div>

        <table class="cover-pillars-box">
            <tr>
                <td width="25%" style="padding: 3px;">
                    <div class="cover-pillar-card">
                        <strong class="text-green" style="font-size: 8.5pt;">PILLAR 1</strong><br>
                        <strong>Administrator</strong><br>
                        <span class="text-muted" style="font-size: 6.8pt;">Full Governance, Master Data &amp; Reports</span>
                    </div>
                </td>
                <td width="25%" style="padding: 3px;">
                    <div class="cover-pillar-card">
                        <strong class="text-green" style="font-size: 8.5pt;">PILLAR 2</strong><br>
                        <strong>Staff Portal</strong><br>
                        <span class="text-muted" style="font-size: 6.8pt;">Ticketing, Proof Verifications &amp; Service</span>
                    </div>
                </td>
                <td width="25%" style="padding: 3px;">
                    <div class="cover-pillar-card">
                        <strong class="text-green" style="font-size: 8.5pt;">PILLAR 3</strong><br>
                        <strong>Customer Web</strong><br>
                        <span class="text-muted" style="font-size: 6.8pt;">Online Booking Engine &amp; Self-Service</span>
                    </div>
                </td>
                <td width="25%" style="padding: 3px;">
                    <div class="cover-pillar-card">
                        <strong class="text-green" style="font-size: 8.5pt;">PILLAR 4</strong><br>
                        <strong>Mobile App</strong><br>
                        <span class="text-muted" style="font-size: 6.8pt;">Pocket Booking, E-Tickets &amp; Rewards</span>
                    </div>
                </td>
            </tr>
        </table>

        <div class="cover-meta">
            <strong>Architecture:</strong> Laravel 11 / Filament 3 &bull; Flutter Android/iOS &bull; Livewire 3<br>
            <strong>Version:</strong> v1.0.44+48 (Production Release) &bull; <strong>Published:</strong> September 2026<br>
            <strong>Target Audience:</strong> Business Owners, Administrators, Front-Desk Staff, Ticketing Clerks, and Everyday Travelers.
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TABLE OF CONTENTS (Strictly Page 2 - Two Column Layout) -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>Table of Contents</h2>
        <p class="text-muted" style="font-size: 7.5pt; margin-bottom: 6px;">This handbook is organized into modular sections so that any user can quickly find exact step-by-step instructions.</p>

        <table width="100%" style="border-collapse: collapse;">
            <tr>
                <td width="48%" style="vertical-align: top; padding-right: 10px;">
                    <table class="toc-table">
                        <tr>
                            <td colspan="2" class="toc-part">SECTION 1: SYSTEM OVERVIEW</td>
                        </tr>
                        <tr>
                            <td>1.1 What is Amiga Gracia? &bull; Architecture</td>
                            <td class="toc-page">3</td>
                        </tr>
                        <tr>
                            <td>1.2 The Complete Booking Lifecycle</td>
                            <td class="toc-page">3</td>
                        </tr>
                        <tr>
                            <td>1.3 Key Terms Explained in Plain Language</td>
                            <td class="toc-page">4</td>
                        </tr>

                        <tr>
                            <td colspan="2" class="toc-part">SECTION 2: ADMINISTRATOR'S MANUAL</td>
                        </tr>
                        <tr>
                            <td>2.1 Admin Dashboard &amp; Security Protocols</td>
                            <td class="toc-page">5</td>
                        </tr>
                        <tr>
                            <td>2.2 Staff Accounts, Roles &amp; Permissions</td>
                            <td class="toc-page">5</td>
                        </tr>
                        <tr>
                            <td>2.3 Master Bookings &amp; Passenger Manifests</td>
                            <td class="toc-page">5</td>
                        </tr>
                        <tr>
                            <td>2.4 Payment Proof Center &amp; Retention Rules</td>
                            <td class="toc-page">6</td>
                        </tr>
                        <tr>
                            <td>2.5 Ticket Issuance &amp; Airline PDF Uploads</td>
                            <td class="toc-page">6</td>
                        </tr>
                        <tr>
                            <td>2.6 Transport Master Data: Routes &amp; Vessels</td>
                            <td class="toc-page">6</td>
                        </tr>
                        <tr>
                            <td>2.7 Schedule Management &amp; Timetable Import</td>
                            <td class="toc-page">7</td>
                        </tr>
                        <tr>
                            <td>2.8 Rolling Cargo: Vehicle Brands &amp; Rates</td>
                            <td class="toc-page">7</td>
                        </tr>
                        <tr>
                            <td>2.9 Rebookings &amp; Fare Differences</td>
                            <td class="toc-page">7</td>
                        </tr>
                        <tr>
                            <td>2.10 Refund &amp; Disbursement Management</td>
                            <td class="toc-page">8</td>
                        </tr>
                        <tr>
                            <td>2.11 Disruptions &amp; Weather Cancellations</td>
                            <td class="toc-page">8</td>
                        </tr>
                        <tr>
                            <td>2.12 Vouchers, Gracia Coins &amp; Discounts</td>
                            <td class="toc-page">8</td>
                        </tr>
                        <tr>
                            <td>2.13 Website Customizer &amp; Payment Settings</td>
                            <td class="toc-page">8</td>
                        </tr>
                        <tr>
                            <td>2.14 Overall Reports &amp; Staff Performance</td>
                            <td class="toc-page">8</td>
                        </tr>
                    </table>
                </td>
                <td width="48%" style="vertical-align: top; padding-left: 10px;">
                    <table class="toc-table">
                        <tr>
                            <td colspan="2" class="toc-part">SECTION 3: STAFF MEMBER'S MANUAL</td>
                        </tr>
                        <tr>
                            <td>3.1 Daily Routine: Shift Checklist &amp; Balancing</td>
                            <td class="toc-page">8</td>
                        </tr>
                        <tr>
                            <td>3.2 Walk-in Customer Ticketing Guide</td>
                            <td class="toc-page">8</td>
                        </tr>
                        <tr>
                            <td>3.3 Payment Proof Verification Checklist</td>
                            <td class="toc-page">9</td>
                        </tr>
                        <tr>
                            <td>3.4 Rebooking &amp; Route Changes Step-by-Step</td>
                            <td class="toc-page">9</td>
                        </tr>
                        <tr>
                            <td>3.5 Handling Disruptions &amp; Stranded Guests</td>
                            <td class="toc-page">9</td>
                        </tr>
                        <tr>
                            <td>3.6 Managing Inquiries &amp; My Page Reports</td>
                            <td class="toc-page">9</td>
                        </tr>

                        <tr>
                            <td colspan="2" class="toc-part">SECTION 4: CUSTOMER WEBSITE MANUAL</td>
                        </tr>
                        <tr>
                            <td>4.1 Exploring Home, Schedules &amp; Tours</td>
                            <td class="toc-page">10</td>
                        </tr>
                        <tr>
                            <td>4.2 Complete 12-Step Online Booking Guide</td>
                            <td class="toc-page">10</td>
                        </tr>
                        <tr>
                            <td>4.3 Payment via Official Bank Transfer</td>
                            <td class="toc-page">10</td>
                        </tr>
                        <tr>
                            <td>4.4 Checking Status &amp; Downloading Tickets</td>
                            <td class="toc-page">11</td>
                        </tr>
                        <tr>
                            <td>4.5 Self-Service Rebooking &amp; Refunds Online</td>
                            <td class="toc-page">11</td>
                        </tr>

                        <tr>
                            <td colspan="2" class="toc-part">SECTION 5: MOBILE APP MANUAL</td>
                        </tr>
                        <tr>
                            <td>5.1 Installing APK &amp; Android Permissions</td>
                            <td class="toc-page">12</td>
                        </tr>
                        <tr>
                            <td>5.2 Account Registration, OTP &amp; Login</td>
                            <td class="toc-page">12</td>
                        </tr>
                        <tr>
                            <td>5.3 App Tabs: Home, Travel, Activity, Profile</td>
                            <td class="toc-page">12</td>
                        </tr>
                        <tr>
                            <td>5.4 Direct Camera Receipt Uploads</td>
                            <td class="toc-page">12</td>
                        </tr>
                        <tr>
                            <td>5.5 Digital Boarding Passes &amp; QR Codes</td>
                            <td class="toc-page">12</td>
                        </tr>
                        <tr>
                            <td>5.6 Gracia Coins Wallet, Vouchers &amp; Referrals</td>
                            <td class="toc-page">13</td>
                        </tr>
                        <tr>
                            <td>5.7 Real-Time Weather Disruption Alerts</td>
                            <td class="toc-page">13</td>
                        </tr>

                        <tr>
                            <td colspan="2" class="toc-part">SECTION 6: APPENDIX &amp; TROUBLESHOOTING</td>
                        </tr>
                        <tr>
                            <td>6.1 Complete Status Dictionary</td>
                            <td class="toc-page">14</td>
                        </tr>
                        <tr>
                            <td>6.2 Essential Traveler Checklist</td>
                            <td class="toc-page">14</td>
                        </tr>
                        <tr>
                            <td>6.3 Emergency Support Contacts Directory</td>
                            <td class="toc-page">14</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- ========================================================================= -->
    <!-- SECTION 1: SYSTEM OVERVIEW (Pages 3-4) -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>1. System Overview &amp; Core Architecture</h2>

        <h3>1.1 What is Amiga Gracia Travel Services?</h3>
        <p><strong>Amiga Gracia Travel Services</strong> is a complete, automated travel booking and operations management platform based in the Philippines. It unifies sea ferry voyages (such as <em>Starlite Ferries</em> and <em>2GO Travel</em>), commercial domestic and regional airline flights (such as <em>Cebu Pacific, Philippine Airlines, and AirAsia</em>), vehicle rolling cargo shipping (cars, motorcycles, trucks), hotel accommodations, and vacation tour packages into one modern ecosystem.</p>

        <div class="logo-strip">
            <?php if (!empty($starliteLogo)): ?><img src="<?php echo $starliteLogo; ?>" class="logo-img" alt="Starlite Ferries" /><?php endif; ?>
            <?php if (!empty($twoGoLogo)): ?><img src="<?php echo $twoGoLogo; ?>" class="logo-img" alt="2GO Travel" /><?php endif; ?>
            <?php if (!empty($cebuPacificLogo)): ?><img src="<?php echo $cebuPacificLogo; ?>" class="logo-img" alt="Cebu Pacific" /><?php endif; ?>
            <?php if (!empty($airAsiaLogo)): ?><img src="<?php echo $airAsiaLogo; ?>" class="logo-img" alt="AirAsia" /><?php endif; ?>
        </div>

        <p>The platform is organized into four purpose-built interfaces tailored to each stakeholder:</p>
        <table class="styled-table">
            <tr>
                <th width="18%">Portal</th>
                <th width="28%">Target User</th>
                <th width="54%">Core Functionality</th>
            </tr>
            <tr>
                <td><strong>Admin Panel</strong></td>
                <td>Business Owners, Managers, Operations Supervisors</td>
                <td>Executive control: master data (vessels, routes, schedules, rates), staff account management, financial auditing, bulk imports, and website settings.</td>
            </tr>
            <tr>
                <td><strong>Staff Portal</strong></td>
                <td>Ticketing Clerks, Front-Desk Agents, Support Staff</td>
                <td>Operational execution: reviewing passenger manifests, verifying GCash/Maya/Bank receipts, issuing official e-tickets, managing rebookings and refunds.</td>
            </tr>
            <tr>
                <td><strong>Website</strong></td>
                <td>Everyday travelers using a PC, tablet, or phone browser</td>
                <td>24/7 public booking engine, live ferry and flight timetables, transparent fare breakdowns, discount ID uploads, self-service tracking.</td>
            </tr>
            <tr>
                <td><strong>Mobile App</strong></td>
                <td>Travelers on the go (Android &amp; iOS)</td>
                <td>Personal pocket travel companion: instant mobile bookings, camera receipt upload, offline QR boarding passes, Gracia Rewards points, and storm alerts.</td>
            </tr>
        </table>

        <h3>1.2 The Complete Travel Booking Lifecycle</h3>
        <p>Every booking in the Amiga Gracia system follows a disciplined, automated 5-phase lifecycle:</p>

        <div class="step-card">
            <span class="step-badge">Phase 1</span> <span class="step-title">Trip Search &amp; Customization</span>
            <p style="margin: 2px 0 0 0; font-size: 7.8pt; color: #475569;">
                The customer selects transport mode (Ferry or Flight), route (Origin &amp; Destination), dates, and passengers on the <strong>Website</strong> or <strong>Mobile App</strong>. They can attach vehicles (RoRo) or check-in baggage, and upload Senior, PWD, or Student discount IDs.
            </p>
        </div>

        <div class="step-card">
            <span class="step-badge">Phase 2</span> <span class="step-title">Payment &amp; Proof Submission</span>
            <p style="margin: 2px 0 0 0; font-size: 7.8pt; color: #475569;">
                The customer transfers the total amount directly to the company's official <strong>Bank Account</strong>. They upload the payment transfer receipt / bank slip screenshot, enter the reference number, and receive an instant tracking ID (e.g. <code>AGT-2026-10048</code>). The status is <strong style="color: #d97706;">PENDING</strong>.
            </p>
        </div>

        <div class="step-card">
            <span class="step-badge">Phase 3</span> <span class="step-title">Staff Verification &amp; Ticket Issuance</span>
            <p style="margin: 2px 0 0 0; font-size: 7.8pt; color: #475569;">
                The transaction appears immediately in the staff verification queue. Staff confirm the amount and reference number in their merchant records. Upon approval, the status becomes <strong style="color: #16a34a;">CONFIRMED</strong>, and an official <strong>E-Ticket Itinerary PDF</strong> is issued and emailed.
            </p>
        </div>

        <div class="step-card">
            <span class="step-badge">Phase 4</span> <span class="step-title">Port / Airport Check-in &amp; Boarding</span>
            <p style="margin: 2px 0 0 0; font-size: 7.8pt; color: #475569;">
                The passenger must present their <strong>printed physical copy of the E-Ticket</strong> together with valid government IDs at the port terminal or airport counter to receive their boarding pass.
            </p>
        </div>

        <div class="step-card">
            <span class="step-badge">Phase 5</span> <span class="step-title">Gracia Rewards Credited</span>
            <p style="margin: 2px 0 0 0; font-size: 7.8pt; color: #475569;">
                Registered users receive automatic <strong>Gracia Points</strong> in their digital wallet based on the total fare paid. These points are convertible into direct cash discounts on future travels!
            </p>
        </div>

        <h3>1.3 Key Terms Explained in Everyday Plain Language</h3>
        <table class="styled-table">
            <tr>
                <th width="24%">Term</th>
                <th width="76%">Plain-Language Explanation</th>
            </tr>
            <tr>
                <td><strong>Transaction # (Reference ID)</strong></td>
                <td>A unique code (e.g., <code>AGT-100234</code>) generated for every booking. Customers and staff use this single number to look up tickets, track payments, or request date changes.</td>
            </tr>
            <tr>
                <td><strong>Proof of Payment</strong></td>
                <td>A screenshot or photograph of the payment confirmation screen from GCash, Maya, or online banking showing the reference number, date, and amount paid.</td>
            </tr>
            <tr>
                <td><strong>E-Ticket / Travel Itinerary</strong></td>
                <td>The official PDF document confirming the travel details. It lists passenger names, seat or cabin numbers, vessel/flight name, boarding terminal, and baggage allowance.</td>
            </tr>
            <tr>
                <td><strong>Rolling Cargo (RoRo)</strong></td>
                <td>Vehicles driven onto a ferry vessel (such as a motorcycle, sedan, SUV, van, or cargo truck). These require vehicle registration details and pay a dedicated cargo freight fee.</td>
            </tr>
            <tr>
                <td><strong>Rebooking</strong></td>
                <td>Changing the travel date, voyage time, or accommodation of an existing booking without cancelling the trip entirely.</td>
            </tr>
            <tr>
                <td><strong>Service Disruption / Gale Warning</strong></td>
                <td>When the Philippine Coast Guard (PCG) or Civil Aviation Authority suspends sea voyages or flights due to typhoons or bad weather. The system provides 100% free replacement trips.</td>
            </tr>
            <tr>
                <td><strong>Gracia Coins (Gracia Points)</strong></td>
                <td>The platform's official loyalty currency earned on completed bookings (e.g., 10 coins per ₱100 spend). 1 Gracia Coin = ₱1.00 direct cash discount during checkout. Coins never expire.</td>
            </tr>
            <tr>
                <td><strong>Vouchers &amp; Promo Codes</strong></td>
                <td>Promotional discount codes (e.g., <code>AMIGA2026</code>) entered during checkout for fixed peso or percentage discounts, subject to minimum booking spend and operator eligibility rules.</td>
            </tr>
            <tr>
                <td><strong>Statutory Discounts</strong></td>
                <td>Philippine legally mandated 20% discounts for Senior Citizens (with 12% VAT exemption), PWDs, and Students. Requires uploading valid government ID photos before ticket issuance.</td>
            </tr>
        </table>
    </div>

    <!-- ========================================================================= -->
    <!-- SECTION 2: THE ADMINISTRATOR'S MANUAL (Pages 5-7) -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>2. The Administrator's Manual (Full System Control)</h2>
        <p>The <strong>Administrator Panel</strong> is the central operations dashboard of Amiga Gracia Travel Services. Built on Laravel Filament 3, it provides complete executive control over bookings, financial accounts, transport master data, staff permissions, and website assets.</p>

        <h3>2.1 Accessing the Admin Dashboard &amp; Security</h3>
        <div class="step-card">
            <span class="step-badge">1</span> <strong>Navigate to the Admin URL:</strong> Open any modern web browser and go to <code>https://www.amigagracia.com/admin</code>.
        </div>
        <div class="step-card">
            <span class="step-badge">2</span> <strong>Enter Admin Credentials:</strong> Type your administrator email address and secure password. Click <strong>Sign in</strong>.
        </div>
        <div class="step-card">
            <span class="step-badge">3</span> <strong>Dashboard Intelligence:</strong> The dashboard displays live gross revenue, bookings count, pending payment proofs, and route sales distribution between Starlite, 2GO, Cebu Pacific, PAL, and AirAsia.
        </div>

        <div class="callout callout-warning">
            <div class="callout-title">[SECURITY BEST PRACTICE]</div>
            Always log out when leaving your desk. All admin actions (approving payments, deleting schedules, issuing refunds) are permanently recorded in system audit logs with user ID, IP address, and timestamp.
        </div>

        <h3>2.2 Managing Staff Accounts, Roles &amp; Granular Permissions</h3>
        <p>Administrators can create user accounts for branch staff, ticketing officers, and accountants under <strong>Administration &rarr; Staff Accounts</strong>:</p>

        <table class="styled-table">
            <tr>
                <th width="18%">Role</th>
                <th width="30%">Target Position</th>
                <th width="52%">Permissions &amp; Scope</th>
            </tr>
            <tr>
                <td><strong>Super Admin</strong></td>
                <td>Lead IT &amp; System Architect</td>
                <td>Unrestricted access: server diagnostics, raw database management, and all operational modules.</td>
            </tr>
            <tr>
                <td><strong>Admin</strong></td>
                <td>Business Owner, Branch Manager, Operations Head</td>
                <td>Full operational control: bookings, staff creation, payment settings, refund approvals, and website editing.</td>
            </tr>
            <tr>
                <td><strong>Staff</strong></td>
                <td>Ticketing Clerks, Counter Agents</td>
                <td>Controlled access: verifying payments, viewing bookings, and handling rebookings. Cannot edit website settings or delete other staff.</td>
            </tr>
            <tr>
                <td><strong>Finance</strong></td>
                <td>Accountants, Bookkeepers</td>
                <td>Financial oversight: Overall Reports, transaction audits, and refund disbursement verification.</td>
            </tr>
        </table>

        <h4>How to Add a Staff Member:</h4>
        <ol style="font-size: 7.8pt; margin: 3px 0 5px 15px; padding-left: 0;">
            <li>Go to <strong>Administration &rarr; Staff Accounts</strong> and click <strong>New Staff Account</strong>.</li>
            <li>Input the staff member's <strong>Full Name</strong>, <strong>Email Address</strong>, and initial <strong>Password</strong>.</li>
            <li>Select the Role as <strong>Staff</strong> and check the modular permission boxes: <em>Bookings</em>, <em>Proofs</em>, <em>Schedules</em>, and <em>Refunds</em>.</li>
            <li>Click <strong>Create Account</strong>. The employee can log in immediately.</li>
        </ol>

        <h3>2.3 Master Booking Management &amp; Passenger Manifests</h3>
        <p>Under <strong>Bookings &rarr; Bookings</strong>, administrators have real-time visibility over every passenger reservation:</p>
        <ul>
            <li><strong>Filter &amp; Search:</strong> Filter by Status (<em>Pending, Confirmed, Cancelled, Rebooked, Refunded</em>), Date, Operator, or search by Reference ID (e.g., <code>AGT-10048</code>) or Passenger Name.</li>
            <li><strong>Passenger &amp; Cargo Manifest:</strong> Click any booking to view passenger birthdates, assigned cabins, discount IDs, and vehicle plate numbers.</li>
            <li><strong>Official Document Downloads:</strong> Download <em>Payment Acknowledgement Receipts</em>, <em>E-Ticket Itineraries</em>, or <em>E-Refund Acknowledgements</em>.</li>
            <li><strong>Export Manifests:</strong> Download manifest lists in <strong>PDF</strong> (for Coast Guard port authorities), <strong>CSV / Excel</strong> (for accounting audits), or send directly to <strong>Print</strong>.</li>
        </ul>

        <h3>2.4 Payment Verification Center &amp; Automated Proof Retention</h3>
        <p>Located at <strong>Reports &rarr; Proof of Payment</strong>, this page displays all uploaded GCash, Maya, and bank transfer receipts.</p>
        <ul>
            <li><strong>Verify Reference &amp; Amount:</strong> Confirm the customer's reference number and total amount against your merchant bank account.</li>
            <li><strong>Approve or Reject:</strong> Click <em>Verify &amp; Confirm</em> to issue the ticket, or <em>Reject Payment</em> with an explanation if fraudulent or underpaid.</li>
            <li><strong>Automated Proof Retention:</strong> Set <em>Proof Retention Days</em> (e.g., <code>30</code> days) so old screenshots are automatically pruned to save server storage while keeping financial records intact.</li>
        </ul>

        <h3>2.5 Ticket Issuance &amp; Airline Confirmation PDF Uploads</h3>
        <p>For ferry bookings (Starlite and 2GO), the system automatically generates an official E-Ticket. For commercial flights (Cebu Pacific, PAL, AirAsia), administrators can upload the official airline PDF confirmation under <strong>Bookings &rarr; Receipts &amp; Tickets</strong>. When the traveler clicks "Download Ticket" on their phone or computer, they receive the official airline boarding document!</p>

        <h3>2.6 Transport Master Data: Routes, Vessels, Flights &amp; Accommodations</h3>
        <ul>
            <li><strong>Travel Routes (Transport Master Data &rarr; Routes):</strong> Manage Origin and Destination ports/airports (e.g. <em>Batangas &rarr; Calapan, Manila &rarr; Boracay, Cebu &rarr; Tagbilaran</em>).</li>
            <li><strong>Vehicles &amp; Vessels (Transport Master Data &rarr; Vehicles / Vessels):</strong> Register ferry ships (e.g., <em>Starlite Eagle, 2GO Masagana</em>) or aircraft types (<em>Airbus A320</em>) with capacities.</li>
            <li><strong>Accommodations &amp; Classes:</strong> Define cabin levels (<em>Economy Open-Air Cot, Tourist Air-Conditioned Bunk, Private Stateroom Cabin, Suite</em>).</li>
            <li><strong>Airline Baggage Rules:</strong> Set free carry-on (7kg) and check-in baggage rates (₱850 for 20kg, ₱1,400 for 32kg).</li>
        </ul>

        <h3>2.7 Schedule Management &amp; Excel/CSV Bulk Importer</h3>
        <p>Under <strong>Travel &amp; Tours &rarr; Import Schedules</strong>, administrators can batch-load hundreds of trip departures:</p>
        <table class="styled-table">
            <tr>
                <th width="32%">Import Method</th>
                <th width="68%">Procedure</th>
            </tr>
            <tr>
                <td><strong>Automated Timetable Ingestion (Starlite)</strong></td>
                <td>Select "Starlite", choose a date range (e.g. next 60 days), and click <em>Run Ingestion</em>. The system populates all active ferry sailings, vessels, and fares automatically in seconds!</td>
            </tr>
            <tr>
                <td><strong>Excel / CSV Importer (Airlines &amp; 2GO)</strong></td>
                <td>Download the standard spreadsheet template, input flight/voyage numbers, departure/arrival hours, and base fares. Upload the file to register the entire schedule into the live booking engine.</td>
            </tr>
        </table>

        <h3>2.8 Rolling Cargo: Vehicle Brands, Models &amp; Freight Rates</h3>
        <p>Under <strong>Transport Master Data &rarr; Vehicle Rates &amp; Brands</strong>, administrators manage RoRo ferry vehicle shipping:</p>
        <ul>
            <li><strong>Classifications:</strong> Class 1 (Motorcycles / Scooters), Class 2 (Sedans / Compact SUVs), Class 3 (Pickups / Large SUVs / Vans), Class 4 (Buses), Class 5 (Heavy Cargo Trucks).</li>
            <li><strong>Brand/Model Mapping:</strong> When a customer selects <em>Toyota Fortuner</em>, the system automatically assigns Class 2 SUV freight charges.</li>
        </ul>

        <h3>2.9 Rebookings, Rescheduling &amp; Fare Differences</h3>
        <p>Under <strong>Bookings &rarr; Rebookings</strong>, admins inspect passenger date change requests, calculate any fare difference or rebooking fee, and approve the new trip to issue an updated E-Ticket.</p>

        <h3>2.10 Refund &amp; Disbursement Management Center</h3>
        <p>Under <strong>Bookings &rarr; Refunds</strong>, ticket cancellations are processed with complete financial rigor:</p>
        <ol style="font-size: 7.8pt; margin: 3px 0 5px 15px; padding-left: 0;">
            <li>Review the passenger's reason for cancellation and original amount paid.</li>
            <li>The system computes the operator's cancellation penalty and displays the exact <strong>Net Refund Amount</strong>.</li>
            <li>Finance sends the refund via GCash/Maya/Bank, enters the reference number, and attaches a payout screenshot.</li>
            <li>Clicking <strong>Mark as Refunded</strong> automatically generates and emails an official <strong>E-Refund Acknowledgement PDF</strong>.</li>
        </ol>

        <h3>2.11 Service Disruptions &amp; Weather Cancellations</h3>
        <p>When PAGASA storm signals or Coast Guard gale warnings ground vessels, open <strong>Travel &amp; Tours &rarr; Service Cancellations</strong>:</p>
        <ul>
            <li>Select the grounded voyage and mark it as <em>Weather Cancellation (Force Majeure)</em>.</li>
            <li>Link designated future <strong>Replacement Schedules</strong>.</li>
            <li>Affected travelers receive immediate push notifications and can transfer to replacement trips on their phone with <strong>zero rebooking fees</strong>!</li>
        </ul>

        <h3>2.12 Vouchers, Gracia Coins, Statutory Discounts &amp; Tours</h3>
        <ul>
            <li><strong>Vouchers &amp; Coupon Codes (`VoucherResource`):</strong> Create promo codes with percentage or fixed ₱ deductions, spend thresholds, usage limits, and operator/route restrictions. Prevents stacking with super-promos.</li>
            <li><strong>Gracia Coins Loyalty Engine (`GraciaEarningRuleResource`):</strong> Configure member earning ratios (e.g., 10 Gracia Coins awarded per ₱100 spend). 1 Gracia Coin = ₱1.00 checkout discount. Full audit ledger tracks member balances.</li>
            <li><strong>Statutory Discount Administration (`DiscountResource`):</strong> Manage statutory 20% discounts for <strong>Senior Citizens</strong> (with 12% VAT exemption formula: <code>Gross &times; 0.80 / 1.12</code>), <strong>PWDs</strong>, and <strong>Students</strong>. Staff review uploaded ID documents.</li>
            <li><strong>Promotional Seat Sales &amp; Tour Packages (`PromotionalTicket`, `TourResource`):</strong> Publish seasonal route seat sale quotas and complete vacation tour packages with hotel accommodations.</li>
        </ul>

        <h3>2.13 Website Content Customizer &amp; Payment Settings</h3>
        <ul>
            <li><strong>Website Settings:</strong> Edit homepage hero slider photos, update booking cards, change contact phone numbers, and edit FAQ answers live without code.</li>
            <li><strong>Payment Settings:</strong> Upload new GCash QR and Maya QR images, and update bank account numbers shown to customers.</li>
            <li><strong>App Notifications:</strong> Send broadcast push notification announcements directly to all smartphone app users.</li>
        </ul>

        <h3>2.14 Overall Reports &amp; Staff Performance Leaderboard</h3>
        <div class="two-col">
            <table width="100%">
                <tr>
                    <td class="col-half">
                        <div class="step-card">
                            <strong class="text-green">Overall Financial Reports:</strong><br>
                            Found under <em>Reports &rarr; Overall Reports</em>. View gross sales, operator commissions, completed bookings, and refunds across custom dates. Export in PDF, CSV, or Print.
                        </div>
                    </td>
                    <td class="col-half">
                        <div class="step-card">
                            <strong class="text-green">Staff Performance Leaderboard:</strong><br>
                            Found under <em>Reports &rarr; Staff Performance</em>. Tracks employee productivity: total verified transactions, bookings handled, and sales volume per shift.
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SECTION 3: THE STAFF MEMBER'S MANUAL (Pages 8-9) -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>3. The Staff Member's Manual (Front-Desk &amp; Ticketing)</h2>
        <p>Welcome to the <strong>Amiga Gracia Front-Desk &amp; Ticketing Team</strong>! As a staff member, you represent the frontline of customer care and financial accuracy. This section guides you through your daily shift routine and operational responsibilities.</p>

        <h3>3.1 Daily Work Routine: Shift Checklist to End-of-Day Balancing</h3>
        <div class="step-card">
            <span class="step-badge">Step 1</span> <span class="step-title">Shift Handover &amp; System Login</span>
            <p style="margin: 2px 0 0 0; font-size: 7.8pt;">
                Log into <code>https://www.amigagracia.com/admin</code> with your personal staff account. Check the <strong>Notification Bell</strong> in the top header for overnight booking requests.
            </p>
        </div>

        <div class="step-card">
            <span class="step-badge">Step 2</span> <span class="step-title">Clear Pending Payment Proofs</span>
            <p style="margin: 2px 0 0 0; font-size: 7.8pt;">
                Navigate to <strong>Proof of Payment</strong>. Check reference numbers and approve genuine payments to dispatch e-tickets promptly.
            </p>
        </div>

        <div class="step-card">
            <span class="step-badge">Step 3</span> <span class="step-title">Walk-In Customer Ticketing &amp; Inquiries</span>
            <p style="margin: 2px 0 0 0; font-size: 7.8pt;">
                Assist walk-in passengers at the ticket counter. Search schedules, enter passenger details accurately, validate Senior/PWD discounts, and print boarding itineraries.
            </p>
        </div>

        <div class="step-card">
            <span class="step-badge">Step 4</span> <span class="step-title">Process Reschedules &amp; Inquiry Responses</span>
            <p style="margin: 2px 0 0 0; font-size: 7.8pt;">
                Answer customer inquiries in the <strong>Inquiries</strong> tab regarding baggage limits, departure terminals, and schedules.
            </p>
        </div>

        <div class="step-card">
            <span class="step-badge">Step 5</span> <span class="step-title">Daily Sales Balancing via "My Page"</span>
            <p style="margin: 2px 0 0 0; font-size: 7.8pt;">
                Open <strong>My Account &rarr; My Page &amp; Reports</strong>. Review total transactions and revenue processed under your name today. Reconcile with cash drawer logs before shift turnover.
            </p>
        </div>

        <h3>3.2 Assisting Customers: Booking Walk-in &amp; Phone Inquiries</h3>
        <div class="callout callout-info">
            <div class="callout-title">[CRITICAL: EXACT PASSENGER NAME RULE]</div>
            Always inspect the passenger's valid physical government ID (Passport, Driver's License, PhilID, SSS, Postal ID). The name entered into the system <strong>MUST match their official ID character-for-character</strong>. Philippine Coast Guard and airline security officers will deny boarding if the ticket name does not match the ID!
        </div>

        <h4>Step-by-Step Walk-in Booking Procedure:</h4>
        <ol style="font-size: 7.8pt; margin: 3px 0 5px 15px; padding-left: 0;">
            <li>Go to <strong>Bookings &rarr; Bookings</strong> and click <strong>New Booking</strong> (or open <code>/book/new</code>).</li>
            <li>Select Transport Mode: <strong>Ferry</strong> or <strong>Airline</strong>. Select <strong>One-Way</strong> or <strong>Round-Trip</strong>.</li>
            <li>Choose <strong>Origin</strong>, <strong>Destination</strong>, and <strong>Travel Date</strong>.</li>
            <li>Review available departures with the customer:
                <ul>
                    <li><em>Open Air Economy:</em> Budget-friendly cot bed on open deck.</li>
                    <li><em>Tourist Aircon:</em> Enclosed, air-conditioned room with bunk beds.</li>
                    <li><em>Cabin / Suite:</em> Private room with private bathroom for families or groups.</li>
                </ul>
            </li>
            <li>Type legal names, gender, and birthdates for all passengers.</li>
            <li>If the customer claims a <strong>Senior Citizen, PWD, or Student</strong> statutory discount, inspect their original physical ID card before applying. Apply any valid <strong>Voucher Promo Code</strong> or redeem the customer's available <strong>Gracia Coins</strong> (1 coin = ₱1.00) to discount the total fare.</li>
            <li>If shipping a vehicle on the ferry, inspect the vehicle OR/CR and input the correct <strong>Plate Number</strong>.</li>
            <li>Collect payment (Cash or Bank Transfer), confirm the booking, and print their official physical <strong>E-Ticket Itinerary</strong>!</li>
        </ol>

        <h3>3.3 Step-by-Step Payment Proof Verification Guide</h3>
        <p>Never approve a payment blindly. Follow this 5-point verification checklist:</p>
        <table class="styled-table">
            <tr>
                <th width="28%">Checkpoint</th>
                <th width="72%">Verification Standard</th>
            </tr>
            <tr>
                <td><strong>1. Receipt Authenticity</strong></td>
                <td>Ensure the screenshot is a genuine GCash / Maya / Bank app screen. Watch out for blurry, edited, or cropped images where the text looks altered.</td>
            </tr>
            <tr>
                <td><strong>2. Recipient Account</strong></td>
                <td>Confirm funds were sent to the official Amiga Gracia merchant number or company bank account.</td>
            </tr>
            <tr>
                <td><strong>3. Exact Amount Match</strong></td>
                <td>Confirm the amount matches the booking total down to the centavo (e.g. ₱2,450.00). Do not approve partial payments.</td>
            </tr>
            <tr>
                <td><strong>4. Recent Timestamp</strong></td>
                <td>Verify the payment timestamp is within the last few hours to prevent reuse of old receipts.</td>
            </tr>
            <tr>
                <td><strong>5. Approve / Reject</strong></td>
                <td>Click <em>Verify &amp; Confirm</em> to issue the ticket, or <em>Reject Payment</em> with clear notes if invalid.</td>
            </tr>
        </table>

        <h3>3.4 Rebooking, Rescheduling &amp; Route Changes Step-by-Step</h3>
        <ol style="font-size: 7.8pt; margin: 3px 0 5px 15px; padding-left: 0;">
            <li>Ask for the customer's <strong>Reference Number (e.g., AGT-10025)</strong> and pull up their booking.</li>
            <li>Check seat availability on their newly requested departure date under <strong>Rebookings</strong>.</li>
            <li>Explain any applicable <strong>Fare Difference</strong> (e.g. weekday to weekend rate) and rebooking fees.</li>
            <li>Collect payment for the difference, upload the receipt, and click <strong>Confirm Rebooking</strong>. Print the newly updated E-Ticket.</li>
        </ol>

        <h3>3.5 Handling Weather Disruptions &amp; Stranded Passengers</h3>
        <div class="callout callout-tip">
            <div class="callout-title">[DE-ESCALATION &amp; PASSENGER CARE PROTOCOL]</div>
            1. <strong>Remain Calm &amp; Reassuring:</strong> Explain that voyage cancellations are mandated by the <em>Philippine Coast Guard</em> for passenger safety due to severe waves.<br>
            2. <strong>Assure Fare Safety:</strong> Inform the passenger their ticket has not expired and their fare is 100% protected.<br>
            3. <strong>Offer Free Replacement Travel:</strong> Transfer them to the first sailing once the storm signal clears with <strong>zero penalty fees</strong>.<br>
            4. <strong>Process Full Refund if Needed:</strong> If the passenger cannot travel, submit a refund marked <em>"Weather Cancellation / Force Majeure"</em> for full refund processing.
        </div>

        <h3>3.6 Managing Inquiries &amp; Personal Accountability via "My Page"</h3>
        <ul>
            <li><strong>Inquiries Tab:</strong> Promptly answer passenger questions about terminal gate hours, baggage rules, and schedules.</li>
            <li><strong>My Page &amp; Reports:</strong> Displays live counts of transactions verified by you, completed bookings, and shift revenue. Use this screen during evening shift turnover to confirm your daily figures with your supervisor.</li>
        </ul>
    </div>

    <!-- ========================================================================= -->
    <!-- SECTION 4: THE CUSTOMER WEBSITE MANUAL (Pages 10-11) -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>4. The Customer Website Manual (Online Travel Portal)</h2>
        <p>The official website (<code>www.amigagracia.com</code>) enables ordinary travelers to book ferries, flights, rolling cargo, and tour packages from home on a computer or smartphone browser in minutes.</p>

        <h3>4.1 Exploring the Website</h3>
        <ul>
            <li><strong>Home:</strong> Search booking engine, popular island routes, promotional deals, and reviews.</li>
            <li><strong>Schedules:</strong> Live timetable showing all ferry sailings (Starlite, 2GO) and flights (Cebu Pacific, PAL, AirAsia) for the next 60 days.</li>
            <li><strong>Tour Packages:</strong> Curated vacation packages (Coron, Boracay, El Nido, Bohol, Batanes) with complete itineraries.</li>
            <li><strong>Services:</strong> Explanations of passenger travel, vehicle RoRo shipping, hotel reservations, and group charters.</li>
            <li><strong>Download App:</strong> Direct link to download the Amiga Travel Android Mobile App APK.</li>
            <li><strong>Booking Status:</strong> Track your reservation, download your e-ticket, or reschedule your trip.</li>
        </ul>

        <h3>4.2 Complete 12-Step Online Booking Guide</h3>
        <div class="step-card">
            <span class="step-badge">Step 1</span> <strong>Choose Transport Mode:</strong> Click on <strong>Ferry</strong> (sea travel) or <strong>Airline</strong> (air travel).
        </div>
        <div class="step-card">
            <span class="step-badge">Step 2</span> <strong>Choose Trip Type:</strong> Select <strong>One-Way</strong> (single trip) or <strong>Round-Trip</strong> (returning trip).
        </div>
        <div class="step-card">
            <span class="step-badge">Step 3</span> <strong>Select Origin &amp; Destination:</strong> Pick departure port/airport (e.g. <em>Batangas</em>) and arrival destination (e.g. <em>Calapan</em> or <em>Caticlan / Boracay</em>).
        </div>
        <div class="step-card">
            <span class="step-badge">Step 4</span> <strong>Pick Travel Dates &amp; Passenger Count:</strong> Select travel dates on the calendar and set the count of Adults, Children, and Infants.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 5</span> <strong>Select Your Schedule:</strong> Compare departure times, arrival times, operator logos (Starlite, 2GO, Cebu Pacific, PAL), vessel names, and base prices. Select your preferred trip.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 6</span> <strong>Choose Seat or Cabin Class:</strong> Pick your comfort level: <em>Economy Cot</em> (open-air bed), <em>Tourist Aircon</em> (air-conditioned bunk), or <em>Cabin / Suite</em> (private room).
        </div>
        <div class="step-card">
            <span class="step-badge">Step 7</span> <strong>Enter Passenger Details:</strong> Type legal full names, gender, and birthdates for every traveler. <em>Ensure names match valid IDs!</em>
        </div>
        <div class="step-card">
            <span class="step-badge">Step 8</span> <strong>Claim Senior, PWD, or Student Discounts:</strong> If any passenger qualifies, select the discount box and upload a photo of their valid ID card to receive the 20% statutory discount.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 9</span> <strong>Add Vehicle / Rolling Cargo (Ferry Only):</strong> Check <em>"I am bringing a vehicle"</em>. Select Vehicle Brand (e.g., <em>Toyota</em>), Model (<em>Fortuner</em>), and type the <strong>Plate Number</strong>. Freight charges update automatically.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 10</span> <strong>Add Airline Baggage (Flights Only):</strong> All flights include free 7kg hand-carry baggage. Select <strong>20kg Check-in</strong> or <strong>32kg Heavy Baggage</strong> if needed.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 11</span> <strong>Apply Vouchers, Redeem Gracia Coins &amp; Review Fare:</strong> Enter your <strong>Voucher Promo Code</strong> (e.g. <code>AMIGA2026</code>) and toggle <strong>Redeem Gracia Coins</strong> (1 coin = ₱1.00 discount) to reduce your total. Review the itemized fare breakdown.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 12</span> <strong>Payment &amp; Uploading Proof:</strong> Copy the displayed official <strong>Bank Transfer account details</strong>. Transfer the exact total via your bank app, upload the receipt screenshot into the proof box, and click <strong>Submit Booking</strong>!
        </div>

        <div class="callout callout-tip">
            <div class="callout-title">[INSTANT TRANSACTION CODE]</div>
            Upon submission, you will see your unique <strong>Transaction Number (e.g., AGT-2026-10048)</strong>. Take a screenshot or save this number! Once verified by staff (usually 5 to 15 minutes), your official <strong>E-Ticket PDF</strong> will be delivered to your email and accessible online.
        </div>

        <h3>4.3 Checking Booking Status &amp; Self-Service Tools</h3>
        <ol style="font-size: 7.8pt; margin: 3px 0 5px 15px; padding-left: 0;">
            <li>Go to <code>www.amigagracia.com/book/status</code> and enter your <strong>Transaction Number</strong>.</li>
            <li>Check status: <strong style="color: #d97706;">PENDING</strong> (staff reviewing), <strong style="color: #16a34a;">CONFIRMED</strong> (ready to download e-ticket), <strong style="color: #0284c7;">REBOOKED</strong> (updated schedule), <strong style="color: #9333ea;">REFUNDED</strong> (funds returned).</li>
            <li><strong>Reschedule Online:</strong> Click <em>Request Reschedule</em> to choose a new departure date and pay any fare difference online.</li>
            <li><strong>Refund Online:</strong> Click <em>Request Refund</em> to submit your cancellation reason and GCash/bank details for disbursement.</li>
        </ol>
    </div>

    <!-- ========================================================================= -->
    <!-- SECTION 5: THE MOBILE APP MANUAL (Pages 12-13) -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>5. The Mobile App Manual (Flutter Android &amp; iOS)</h2>
        <p>The <strong>Amiga Gracia Mobile App</strong> provides the ultimate on-the-go travel companion. Built on Flutter, it offers instant booking, camera receipt upload, offline QR boarding passes, and the exclusive <em>Gracia Rewards</em> loyalty program.</p>

        <div style="text-align: center; margin: 4px 0;">
            <?php if (!empty($appIconBase64)): ?>
                <img src="<?php echo $appIconBase64; ?>" style="height: 32px; width: auto;" alt="Amiga App Icon" />
            <?php endif; ?>
        </div>

        <h3>5.1 Installing the App (APK Download &amp; Android Permissions)</h3>
        <div class="step-card">
            <span class="step-badge">1</span> <strong>Download the APK:</strong> Visit <code>https://www.amigagracia.com/download</code> on your phone browser and tap <strong>Download Android APK</strong>.
        </div>
        <div class="step-card">
            <span class="step-badge">2</span> <strong>Allow Installation:</strong> Tap <em>"Download Anyway"</em> when Android displays its standard outside-PlayStore file notice.
        </div>
        <div class="step-card">
            <span class="step-badge">3</span> <strong>Grant Permissions:</strong> Tap on <code>app-release.apk</code> to install. Allow <strong>Camera / Photos</strong> (to snap receipt photos) and <strong>Notifications</strong> (to receive payment approval and typhoon alerts).
        </div>

        <h3>5.2 Account Creation, OTP Verification &amp; Login</h3>
        <ul>
            <li>Tap <strong>Profile &rarr; Register</strong>. Enter your Name, Mobile Number, Email, and Password.</li>
            <li>If a friend referred you, type their <strong>Referral Code</strong> to receive a welcome bonus!</li>
            <li>Type the <strong>6-digit OTP code</strong> sent to your email. Your account is active immediately.</li>
        </ul>

        <h3>5.3 The 4 Main App Tabs</h3>
        <table class="styled-table">
            <tr>
                <th width="18%">Tab</th>
                <th width="28%">Screen Name</th>
                <th width="54%">Features &amp; Tools</th>
            </tr>
            <tr>
                <td><strong>1st Tab</strong></td>
                <td><strong>Home</strong></td>
                <td>Hero video banner, quick trip search bar, featured island promos, direct buttons for Ferries, Flights, Tours, and Rolling Cargo.</td>
            </tr>
            <tr>
                <td><strong>2nd Tab</strong></td>
                <td><strong>Travel</strong></td>
                <td>Interactive booking wizard: select routes, travel dates, passenger counts, and accommodation classes.</td>
            </tr>
            <tr>
                <td><strong>3rd Tab</strong></td>
                <td><strong>Activity (My Trips)</strong></td>
                <td>Digital ticket wallet: lists <em>Upcoming, Completed, and Cancelled Trips</em>. View live digital tickets and boarding QR codes.</td>
            </tr>
            <tr>
                <td><strong>4th Tab</strong></td>
                <td><strong>Profile</strong></td>
                <td>Account settings, Gracia Points balance, Referral Code sharing, Push Notifications toggle, and FAQs.</td>
            </tr>
        </table>

        <h3>5.4 Mobile Booking: Direct Camera Receipt Upload</h3>
        <ol style="font-size: 7.8pt; margin: 3px 0 5px 15px; padding-left: 0;">
            <li>Select your trip, pick seats, and enter passenger names.</li>
            <li>Tap <strong>Save QR Code</strong> to save the GCash/Maya QR code to your gallery, or copy the account number.</li>
            <li>Open GCash/Maya and pay the exact amount.</li>
            <li>Return to the app and tap <strong>Upload Proof</strong>. Choose <em>Take Photo</em> (for physical bank slips) or <em>Choose from Gallery</em> (for GCash screenshots).</li>
            <li>Type the reference number and tap <strong>Submit Booking</strong>!</li>
        </ol>

        <h3>5.5 Digital Tickets, QR Codes &amp; Printed Itinerary</h3>
        <div class="callout callout-tip">
            <div class="callout-title">[BOARDING REQUIREMENTS]</div>
            Once confirmed, open the <strong>Activity</strong> tab. Tap on your trip to view your booking details and download the official <strong>E-Ticket PDF</strong>. Passengers must <strong>print out a physical copy of their E-Ticket</strong> to present at port terminal gates and airport check-in counters together with valid government IDs!
        </div>

        <h3>5.6 Gracia Coins Wallet, Vouchers &amp; Referral Bonuses</h3>
        <div class="two-col">
            <table width="100%">
                <tr>
                    <td class="col-half">
                        <div class="step-card">
                            <strong class="text-green">Gracia Coins Economy:</strong><br>
                            &bull; <strong>Earn Coins:</strong> Receive 10 Gracia Coins automatically for every ₱100 spent on completed voyages and flights.<br>
                            &bull; <strong>Direct Value:</strong> 1 Gracia Coin = ₱1.00 checkout discount. Coins never expire.<br>
                            &bull; <strong>Wallet:</strong> View live coin balance and ledger in the Profile tab.
                        </div>
                    </td>
                    <td class="col-half-last">
                        <div class="step-card">
                            <strong class="text-pink">Vouchers &amp; Referrals:</strong><br>
                            &bull; <strong>App Vouchers:</strong> Store and apply voucher codes (e.g. <code>AMIGA2026</code>) during checkout for fixed or percentage savings.<br>
                            &bull; <strong>Refer-a-Friend:</strong> Share your Referral Code with friends. When they register and book, <strong>both of you receive bonus Gracia Coins</strong>!
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <h3>5.7 Real-Time Weather Advisories &amp; Zero-Fee Replacement Trips</h3>
        <p>If sea travel is grounded by bad weather:</p>
        <ul>
            <li>You receive an instant <strong>Push Notification</strong> alert: <em>"Voyage Suspended: Starlite Ferry grounded due to Coast Guard gale warning."</em></li>
            <li>Open the notification to access the <strong>Disruption Center</strong>.</li>
            <li>The app displays designated <strong>Replacement Sailings</strong> scheduled once the storm passes.</li>
            <li>Tap <strong>Accept Replacement Trip</strong> to transfer your reservation with <strong>zero rebooking fees</strong>!</li>
        </ul>
    </div>

    <!-- ========================================================================= -->
    <!-- SECTION 6: APPENDIX & QUICK TROUBLESHOOTING (Page 14) -->
    <!-- ========================================================================= -->
    <div>
        <h2>6. Appendix &amp; Quick Troubleshooting</h2>

        <h3>6.1 Complete Status Dictionary</h3>
        <table class="styled-table">
            <tr>
                <th width="18%">Status</th>
                <th width="14%">Indicator</th>
                <th width="68%">Meaning &amp; Required Action</th>
            </tr>
            <tr>
                <td><strong>Pending</strong></td>
                <td><strong style="color: #d97706;">PENDING</strong></td>
                <td>Booking created, waiting for payment screenshot verification. Customer should ensure receipt was uploaded.</td>
            </tr>
            <tr>
                <td><strong>Confirmed</strong></td>
                <td><strong style="color: #16a34a;">CONFIRMED</strong></td>
                <td>Payment successfully verified! Seat/cabin is guaranteed. Passenger can download and use their official E-Ticket.</td>
            </tr>
            <tr>
                <td><strong>Cancelled</strong></td>
                <td><strong style="color: #dc2626;">CANCELLED</strong></td>
                <td>Trip cancelled by traveler or payment rejected as fraudulent/unpaid. Ticket is void.</td>
            </tr>
            <tr>
                <td><strong>Rebooked</strong></td>
                <td><strong style="color: #0284c7;">REBOOKED</strong></td>
                <td>Travel date, vessel, or seat class was updated. Use the newly issued updated itinerary.</td>
            </tr>
            <tr>
                <td><strong>Refunded</strong></td>
                <td><strong style="color: #9333ea;">REFUNDED</strong></td>
                <td>Booking cancelled and funds disbursed to customer's GCash or bank account. E-Refund PDF issued.</td>
            </tr>
            <tr>
                <td><strong>Disrupted</strong></td>
                <td><strong style="color: #dc2626;">DISRUPTED</strong></td>
                <td>Voyage/flight suspended by operator or Coast Guard due to weather. Passenger eligible for free replacement trip or full refund.</td>
            </tr>
        </table>

        <h3>6.2 Essential Traveler Checklist</h3>
        <div class="step-card">
            <div class="checklist-item"><span class="check-box"></span> <strong>Valid Government ID:</strong> Bring original physical ID matching the ticket name (Passport, Driver's License, PhilID, Postal ID, SSS, Student ID).</div>
            <div class="checklist-item"><span class="check-box"></span> <strong>Arrival Cut-off Times:</strong> Arrive at the Ferry Port at least <strong>2 hours before departure</strong>. Arrive at the Airport at least <strong>2 to 3 hours before departure</strong>. Gates close strictly.</div>
            <div class="checklist-item"><span class="check-box"></span> <strong>Port Terminal Fees:</strong> Municipal port entrance and environmental fees (₱30 to ₱150 depending on the port) are collected directly at terminal entrance gates by government port authorities.</div>
            <div class="checklist-item"><span class="check-box"></span> <strong>Baggage Regulations:</strong> Hand-carry baggage must not exceed 7kg. Flammable items, unauthorized weapons, and non-certified pets are strictly prohibited by Coast Guard rules.</div>
            <div class="checklist-item"><span class="check-box"></span> <strong>Vehicle OR/CR:</strong> If bringing a car or motorcycle, have original vehicle OR/CR and driver's license ready for port marshals during RoRo boarding.</div>
        </div>

        <h3>6.3 Customer Support &amp; Emergency Help Desk</h3>
        <table class="styled-table">
            <tr>
                <th width="30%">Support Channel</th>
                <th width="70%">Contact Details</th>
            </tr>
            <tr>
                <td><strong>Customer Support Hotline</strong></td>
                <td><strong>Mobile:</strong> 0930-928-4278 &bull; <strong>Landline:</strong> (043) 738-2989</td>
            </tr>
            <tr>
                <td><strong>Official Email</strong></td>
                <td>agtsreservation@amigagracia.com &bull; amigagracia.travelservices@gmail.com</td>
            </tr>
            <tr>
                <td><strong>Official Website</strong></td>
                <td>https://www.amigagracia.com</td>
            </tr>
            <tr>
                <td><strong>Main Office Address</strong></td>
                <td>Roxas Drive, Libis, Calapan City, Oriental Mindoro, 5200, Philippines</td>
            </tr>
            <tr>
                <td><strong>Facebook Page</strong></td>
                <td>facebook.com/profile.php?id=100072122019511 (Amiga Gracia Travel Services)</td>
            </tr>
        </table>

        <div style="margin-top: 10px; text-align: center; border-top: 2px solid #216417; padding-top: 6px;">
            <strong class="text-green" style="font-size: 9pt;">AMIGA GRACIA TRAVEL SERVICES</strong><br>
            <span class="text-muted" style="font-size: 7.2pt;">Your Trusted Journey Partner across the Philippine Islands &bull; Sea, Air, and Land</span>
        </div>
    </div>

</body>
</html>
<?php
    return ob_get_clean();
}

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml(buildHtml());
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$canvas = $dompdf->getCanvas();
$totalPages = $canvas->get_page_count();
echo "Render Complete. Total Pages: " . $totalPages . "\n";

// Dynamic header/footer via Canvas script (Appears on all pages EXCEPT page 1 cover)
$canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
    if ($pageNumber > 1) {
        $font = $fontMetrics->get_font("DejaVu Sans", "normal");
        $fontBold = $fontMetrics->get_font("DejaVu Sans", "bold");
        
        // Running Top Header
        $canvas->text(40, 22, "AMIGA GRACIA TRAVEL SERVICES • COMPLETE SYSTEM USER MANUAL", $fontBold, 7, [0.13, 0.39, 0.09]);
        $canvas->text(380, 22, "Admin • Staff • Website • Mobile App", $font, 7, [0.55, 0.6, 0.65]);
        $canvas->line(40, 32, 555, 32, [0.8, 0.83, 0.88], 0.75);
        
        // Running Bottom Footer
        $canvas->line(40, 804, 555, 804, [0.8, 0.83, 0.88], 0.75);
        $canvas->text(40, 810, "© " . date('Y') . " Amiga Gracia Travel Services. All rights reserved. • Confidential Operations", $font, 7, [0.4, 0.45, 0.5]);
        $canvas->text(498, 810, "Page " . $pageNumber . " of " . $pageCount, $font, 7, [0.4, 0.45, 0.5]);
    }
});

$outputPdfRoot = __DIR__ . '/Amiga_Travel_Complete_User_Manual.pdf';
$outputPdfPublic = __DIR__ . '/public/Amiga_Travel_Complete_User_Manual.pdf';

file_put_contents($outputPdfRoot, $dompdf->output());
file_put_contents($outputPdfPublic, $dompdf->output());

echo "SUCCESS: 14-Page Perfection Manual Generated!\n";
echo "Root File: " . $outputPdfRoot . " (" . number_format(filesize($outputPdfRoot) / 1024, 1) . " KB)\n";
echo "Public File: " . $outputPdfPublic . " (" . number_format(filesize($outputPdfPublic) / 1024, 1) . " KB)\n";
