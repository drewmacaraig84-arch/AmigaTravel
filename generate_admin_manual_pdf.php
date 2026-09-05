<?php
/**
 * Amiga Gracia Travel Services - Master Administrator Operations Manual PDF Generator
 * Comprehensive, feature-by-feature operational guide exclusively for System Administrators,
 * Business Owners, Branch Managers, Operations Supervisors, and IT Staff.
 * 
 * Strictly structured: 16 Pages Total, exactly 1 Page per Chapter (Ch 1 - Ch 14),
 * with Table of Contents on Page 2, and Executive Cover on Page 1.
 */

require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');

echo "Generating Dedicated Admin Manual PDF (16-Page Master Edition)...\n";

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
    <title>Amiga Gracia Travel Services - Administrator Operations Manual</title>
    <style>
        @page {
            margin: 14mm 13mm 14mm 13mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 8.2pt;
            line-height: 1.32;
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
            font-size: 17pt;
            line-height: 1.2;
            color: #216417;
            margin-bottom: 5px;
        }
        h2 {
            font-size: 11pt;
            color: #216417;
            border-bottom: 2px solid #216417;
            padding-bottom: 2px;
            margin-top: 0;
            margin-bottom: 6px;
        }
        h3 {
            font-size: 9.2pt;
            color: #1e293b;
            margin-top: 6px;
            margin-bottom: 3px;
        }
        h4 {
            font-size: 8.4pt;
            color: #334155;
            margin-top: 5px;
            margin-bottom: 2px;
        }
        p {
            margin-top: 0;
            margin-bottom: 4px;
        }

        .text-green { color: #216417; }
        .text-pink { color: #ee018d; }
        .text-muted { color: #64748b; }
        .text-bold { font-weight: bold; }

        /* Cover Page */
        .cover-container {
            text-align: center;
            padding-top: 14mm;
            padding-bottom: 10mm;
        }
        .cover-logo {
            max-width: 230px;
            height: auto;
            margin-bottom: 15px;
        }
        .cover-badge {
            display: inline-block;
            background-color: #f0fdf4;
            border: 1px solid #86efac;
            padding: 3px 12px;
            border-radius: 14px;
            font-size: 8.2pt;
            font-weight: bold;
            color: #166534;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 14px;
        }
        .cover-title {
            font-size: 22pt;
            font-weight: bold;
            color: #216417;
            line-height: 1.2;
            margin-bottom: 8px;
        }
        .cover-subtitle {
            font-size: 10.2pt;
            color: #475569;
            max-width: 520px;
            margin: 0 auto 15px auto;
            line-height: 1.4;
        }
        .cover-divider {
            width: 65px;
            height: 3px;
            background-color: #ee018d;
            margin: 0 auto 16px auto;
            border-radius: 2px;
        }
        .cover-pillars-box {
            width: 100%;
            margin-top: 14px;
            margin-bottom: 16px;
        }
        .cover-pillar-card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 6px;
            text-align: center;
        }
        .cover-meta {
            margin-top: 18px;
            font-size: 7.8pt;
            color: #64748b;
            line-height: 1.5;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
        }

        /* Callouts */
        .callout {
            border-radius: 5px;
            padding: 5px 8px;
            margin: 4px 0;
            font-size: 7.8pt;
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
            font-size: 7.2pt;
            letter-spacing: 0.5px;
        }

        /* Step Cards */
        .step-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 4px 7px;
            margin-bottom: 4px;
            page-break-inside: avoid;
        }
        .step-badge {
            display: inline-block;
            background-color: #216417;
            color: #ffffff;
            font-weight: bold;
            font-size: 7pt;
            padding: 1px 5px;
            border-radius: 5px;
            margin-right: 4px;
        }
        .step-title {
            font-weight: bold;
            font-size: 8.2pt;
            color: #0f172a;
        }

        /* Tables */
        table.styled-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 5px;
            font-size: 7.8pt;
        }
        table.styled-table th {
            background-color: #216417;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 3.5px 6px;
            border: 1px solid #1c5513;
        }
        table.styled-table td {
            padding: 3.5px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        table.styled-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* Two-Column Grid */
        .two-col {
            width: 100%;
            margin-bottom: 3px;
        }
        .col-half {
            width: 50%;
            vertical-align: top;
            padding-right: 5px;
        }
        .col-half-last {
            width: 50%;
            vertical-align: top;
            padding-left: 5px;
        }

        /* Lists */
        ul {
            margin-top: 2px;
            margin-bottom: 4px;
            padding-left: 16px;
        }
        li {
            margin-bottom: 2px;
        }
        code {
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 1px 3px;
            border-radius: 3px;
            font-size: 7.6pt;
        }

        /* Table of Contents Single Page Two-Column */
        .toc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.8pt;
            line-height: 1.2;
        }
        .toc-table td {
            padding: 1.5px 0;
            border-bottom: 1px dotted #cbd5e1;
        }
        .toc-part {
            font-weight: bold;
            color: #216417;
            padding-top: 3.5px !important;
            border-bottom: 1.5px solid #216417 !important;
            font-size: 7.2pt;
        }
        .toc-page {
            text-align: right;
            font-weight: bold;
            color: #475569;
            width: 25px;
        }
    </style>
</head>
<body>

    <!-- ========================================================================= -->
    <!-- PAGE 1: COVER PAGE -->
    <!-- ========================================================================= -->
    <div class="page-break cover-container">
        <div class="cover-badge">Executive Administration Edition</div><br>
        <?php if (!empty($logoBase64)): ?>
            <img src="<?php echo $logoBase64; ?>" class="cover-logo" alt="Amiga Gracia Logo"><br>
        <?php endif; ?>

        <div class="cover-title">ADMINISTRATOR<br>OPERATIONS MANUAL</div>
        <div class="cover-divider"></div>
        <div class="cover-subtitle">
            The Complete Master Operations, Modals &amp; System Governance Guide for<br>
            <strong>Business Owners, Branch Managers, Operations Supervisors, and IT Administrators</strong>
        </div>

        <table class="cover-pillars-box" cellspacing="6">
            <tr>
                <td width="25%" class="cover-pillar-card">
                    <strong class="text-green" style="font-size: 8.5pt;">GOVERNANCE</strong><br>
                    <span style="font-size: 7.8pt; font-weight: bold; color: #1e293b;">Staff &amp; Roles</span><br>
                    <span style="font-size: 7.2pt; color: #64748b;">Accounts, Permissions &amp; Security Audits</span>
                </td>
                <td width="25%" class="cover-pillar-card">
                    <strong class="text-green" style="font-size: 8.5pt;">OPERATIONS</strong><br>
                    <span style="font-size: 7.8pt; font-weight: bold; color: #1e293b;">Bookings &amp; Manifests</span><br>
                    <span style="font-size: 7.2pt; color: #64748b;">Manifests, Proofs, Tickets &amp; RoRo</span>
                </td>
                <td width="25%" class="cover-pillar-card">
                    <strong class="text-green" style="font-size: 8.5pt;">LOGISTICS</strong><br>
                    <span style="font-size: 7.8pt; font-weight: bold; color: #1e293b;">Schedules &amp; Fleet</span><br>
                    <span style="font-size: 7.2pt; color: #64748b;">Routes, Timetables, Importers &amp; Disruptions</span>
                </td>
                <td width="25%" class="cover-pillar-card">
                    <strong class="text-green" style="font-size: 8.5pt;">FINANCE &amp; MODALS</strong><br>
                    <span style="font-size: 7.8pt; font-weight: bold; color: #1e293b;">Dialogs &amp; Reports</span><br>
                    <span style="font-size: 7.2pt; color: #64748b;">Refunds, Sales, Staff Audits &amp; Modal Guides</span>
                </td>
            </tr>
        </table>

        <div class="cover-meta">
            <strong>Platform:</strong> Laravel 11 / Filament 3 &bull; MySQL &bull; Flutter API &bull; Livewire 3<br>
            <strong>Version:</strong> v1.0.44+48 (Production Edition) &bull; <strong>Scope:</strong> Complete Admin Panel &amp; Interactive Modals Guide<br>
            <strong>Ownership &amp; Authority:</strong> Business Owners, Branch Managers, and Operations Directors.
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- PAGE 2: TABLE OF CONTENTS -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>Table of Contents</h2>
        <p class="text-muted" style="font-size: 7.2pt; margin-bottom: 5px;">This operations manual covers every administrative feature, interactive modal dialog, setting, and resource in the Amiga Gracia system.</p>

        <table width="100%" style="border-collapse: collapse;">
            <tr>
                <td width="48%" style="vertical-align: top; padding-right: 10px;">
                    <table class="toc-table">
                        <tr>
                            <td colspan="2" class="toc-part">CHAPTER 1: ARCHITECTURE &amp; SECURITY</td>
                        </tr>
                        <tr>
                            <td>1.1 Admin Platform &amp; Architecture Overview</td>
                            <td class="toc-page">3</td>
                        </tr>
                        <tr>
                            <td>1.2 Accessing the Dashboard &amp; Security Protocols</td>
                            <td class="toc-page">3</td>
                        </tr>
                        <tr>
                            <td>1.3 Notification Center &amp; Live System Alerts</td>
                            <td class="toc-page">3</td>
                        </tr>

                        <tr>
                            <td colspan="2" class="toc-part">CHAPTER 2: USER &amp; STAFF GOVERNANCE</td>
                        </tr>
                        <tr>
                            <td>2.1 Role Hierarchy: Admin (Owner) vs Super Admin</td>
                            <td class="toc-page">4</td>
                        </tr>
                        <tr>
                            <td>2.2 Staff Account Creation &amp; Granular Permissions</td>
                            <td class="toc-page">4</td>
                        </tr>
                        <tr>
                            <td>2.3 Staff Login Histories &amp; Audit Trail</td>
                            <td class="toc-page">4</td>
                        </tr>
                        <tr>
                            <td>2.4 Mobile APK Users Management</td>
                            <td class="toc-page">4</td>
                        </tr>

                        <tr>
                            <td colspan="2" class="toc-part">CHAPTER 3: BOOKINGS &amp; MANIFESTS</td>
                        </tr>
                        <tr>
                            <td>3.1 Master Bookings Register &amp; Live Filtering</td>
                            <td class="toc-page">5</td>
                        </tr>
                        <tr>
                            <td>3.2 Passenger Manifests &amp; Discount ID Review</td>
                            <td class="toc-page">5</td>
                        </tr>
                        <tr>
                            <td>3.3 Rolling Cargo Manifests (Vehicle RoRo)</td>
                            <td class="toc-page">5</td>
                        </tr>
                        <tr>
                            <td>3.4 Exporting Manifests (Coast Guard PDF/CSV)</td>
                            <td class="toc-page">5</td>
                        </tr>

                        <tr>
                            <td colspan="2" class="toc-part">CHAPTER 4: PAYMENT VERIFICATION &amp; PROOFS</td>
                        </tr>
                        <tr>
                            <td>4.1 Proof of Payment Center (ManageProofs)</td>
                            <td class="toc-page">6</td>
                        </tr>
                        <tr>
                            <td>4.2 5-Point Proof Verification Protocol</td>
                            <td class="toc-page">6</td>
                        </tr>
                        <tr>
                            <td>4.3 Approving &amp; Rejecting Transactions</td>
                            <td class="toc-page">6</td>
                        </tr>
                        <tr>
                            <td>4.4 Automated 30-Day Storage Pruning Rules</td>
                            <td class="toc-page">6</td>
                        </tr>

                        <tr>
                            <td colspan="2" class="toc-part">CHAPTER 5: TICKETS &amp; RECEIPTS</td>
                        </tr>
                        <tr>
                            <td>5.1 Automated Ferry E-Ticket Generation</td>
                            <td class="toc-page">7</td>
                        </tr>
                        <tr>
                            <td>5.2 Airline Confirmation PDF Uploads</td>
                            <td class="toc-page">7</td>
                        </tr>
                        <tr>
                            <td>5.3 Single-Passenger Boarding Passes</td>
                            <td class="toc-page">7</td>
                        </tr>
                        <tr>
                            <td>5.4 Automated E-Ticket Delivery &amp; Printing</td>
                            <td class="toc-page">7</td>
                        </tr>

                        <tr>
                            <td colspan="2" class="toc-part">CHAPTER 6: TRANSPORT MASTER DATA</td>
                        </tr>
                        <tr>
                            <td>6.1 Route Management (Ferry &amp; Flight Paths)</td>
                            <td class="toc-page">8</td>
                        </tr>
                        <tr>
                            <td>6.2 Vessel &amp; Aircraft Fleet Registry</td>
                            <td class="toc-page">8</td>
                        </tr>
                        <tr>
                            <td>6.3 Accommodation Classes (Economy to Suite)</td>
                            <td class="toc-page">8</td>
                        </tr>
                        <tr>
                            <td>6.4 Airline Baggage Rules &amp; Rolling Cargo Rates</td>
                            <td class="toc-page">8</td>
                        </tr>

                        <tr>
                            <td colspan="2" class="toc-part">CHAPTER 7: SCHEDULE MANAGEMENT</td>
                        </tr>
                        <tr>
                            <td>7.1 Managing Schedules &amp; Departures</td>
                            <td class="toc-page">9</td>
                        </tr>
                        <tr>
                            <td>7.2 The Schedule Import Center</td>
                            <td class="toc-page">9</td>
                        </tr>
                        <tr>
                            <td>7.3 Step-by-Step Automated Ingestion</td>
                            <td class="toc-page">9</td>
                        </tr>
                        <tr>
                            <td>7.4 Modifying &amp; Cancelling Existing Schedules</td>
                            <td class="toc-page">9</td>
                        </tr>
                    </table>
                </td>
                <td width="48%" style="vertical-align: top; padding-left: 10px;">
                    <table class="toc-table">
                        <tr>
                            <td colspan="2" class="toc-part">CHAPTER 8: REBOOKINGS &amp; REFUNDS</td>
                        </tr>
                        <tr>
                            <td>8.1 Rebooking Center &amp; Fare Differences</td>
                            <td class="toc-page">10</td>
                        </tr>
                        <tr>
                            <td>8.2 Refund &amp; Disbursement Center</td>
                            <td class="toc-page">10</td>
                        </tr>
                        <tr>
                            <td>8.3 Emergency Weather Disruptions (Coast Guard)</td>
                            <td class="toc-page">10</td>
                        </tr>

                        <tr>
                            <td colspan="2" class="toc-part">CHAPTER 9: VOUCHERS, COINS &amp; DISCOUNTS</td>
                        </tr>
                        <tr>
                            <td>9.1 The Voucher &amp; Promo Codes Engine</td>
                            <td class="toc-page">11</td>
                        </tr>
                        <tr>
                            <td>9.2 Gracia Coins Loyalty Rewards System</td>
                            <td class="toc-page">11</td>
                        </tr>
                        <tr>
                            <td>9.3 Statutory Discounts (Senior, PWD &amp; Student)</td>
                            <td class="toc-page">11</td>
                        </tr>
                        <tr>
                            <td>9.4 Tour Packages &amp; Partner Accommodations</td>
                            <td class="toc-page">11</td>
                        </tr>

                        <tr>
                            <td colspan="2" class="toc-part">CHAPTER 10: WEBSITE CMS &amp; SETTINGS</td>
                        </tr>
                        <tr>
                            <td>10.1 The Live Website Customizer (CMS)</td>
                            <td class="toc-page">12</td>
                        </tr>
                        <tr>
                            <td>10.2 Payment Gateway Settings (Bank Accounts)</td>
                            <td class="toc-page">12</td>
                        </tr>
                        <tr>
                            <td>10.3 Broadcast App Push Notifications</td>
                            <td class="toc-page">12</td>
                        </tr>
                        <tr>
                            <td>10.4 Customer Inquiry Resolution Center</td>
                            <td class="toc-page">12</td>
                        </tr>

                        <tr>
                            <td colspan="2" class="toc-part">CHAPTER 11: FINANCIAL AUDITS &amp; REPORTS</td>
                        </tr>
                        <tr>
                            <td>11.1 Overall Business &amp; Financial Reports</td>
                            <td class="toc-page">13</td>
                        </tr>
                        <tr>
                            <td>11.2 Staff Performance Analytics Leaderboard</td>
                            <td class="toc-page">13</td>
                        </tr>
                        <tr>
                            <td>11.3 My Page &amp; Personal Shift Reconciliation</td>
                            <td class="toc-page">13</td>
                        </tr>
                        <tr>
                            <td>11.4 End-of-Day Balancing Protocol</td>
                            <td class="toc-page">13</td>
                        </tr>

                        <tr>
                            <td colspan="2" class="toc-part">CHAPTER 12: TROUBLESHOOTING &amp; OPS</td>
                        </tr>
                        <tr>
                            <td>12.1 System Diagnostic Endpoints</td>
                            <td class="toc-page">14</td>
                        </tr>
                        <tr>
                            <td>12.2 Storage Symlinks &amp; File Permissions</td>
                            <td class="toc-page">14</td>
                        </tr>
                        <tr>
                            <td>12.3 Complete Status Master Dictionary</td>
                            <td class="toc-page">14</td>
                        </tr>

                        <tr>
                            <td colspan="2" class="toc-part">CHAPTER 13: OPERATIONS &amp; VERIFICATION MODALS</td>
                        </tr>
                        <tr>
                            <td>13.1 Payment Proof Verification &amp; Rejection Modals</td>
                            <td class="toc-page">15</td>
                        </tr>
                        <tr>
                            <td>13.2 Booking Manifest &amp; Discount ID Review Slide-Over</td>
                            <td class="toc-page">15</td>
                        </tr>
                        <tr>
                            <td>13.3 Airline E-Ticket Attachment Dialog</td>
                            <td class="toc-page">15</td>
                        </tr>
                        <tr>
                            <td>13.4 Rebooking Review &amp; Approval Modal</td>
                            <td class="toc-page">15</td>
                        </tr>

                        <tr>
                            <td colspan="2" class="toc-part">CHAPTER 14: FINANCE, DISRUPTIONS &amp; CMS MODALS</td>
                        </tr>
                        <tr>
                            <td>14.1 Refund Disbursement &amp; Exclusive Review Lock</td>
                            <td class="toc-page">16</td>
                        </tr>
                        <tr>
                            <td>14.2 Service Cancellation &amp; Resumption Modals</td>
                            <td class="toc-page">16</td>
                        </tr>
                        <tr>
                            <td>14.3 Schedule Ingestion &amp; Timetable Modals</td>
                            <td class="toc-page">16</td>
                        </tr>
                        <tr>
                            <td>14.4 Vouchers, Push Alerts &amp; Staff Account Modals</td>
                            <td class="toc-page">16</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- ========================================================================= -->
    <!-- PAGE 3: CHAPTER 1 - ARCHITECTURE & SECURITY -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>Chapter 1: Admin System Architecture &amp; Security Governance</h2>

        <h3>1.1 Platform Architecture Overview</h3>
        <p>
            The Amiga Gracia Travel Services administrator portal operates on Laravel 11 and Filament 3. It serves as the unified operational core connecting our customer website, customer Android APK, and ticketing counters into one centralized MySQL database.
        </p>

        <div class="step-card" style="text-align: center; padding: 5px;">
            <?php if (!empty($starliteLogo)): ?><img src="<?php echo $starliteLogo; ?>" height="15" style="margin: 0 8px;" alt="Starlite"><?php endif; ?>
            <?php if (!empty($twoGoLogo)): ?><img src="<?php echo $twoGoLogo; ?>" height="15" style="margin: 0 8px;" alt="2GO"><?php endif; ?>
            <?php if (!empty($cebuPacificLogo)): ?><img src="<?php echo $cebuPacificLogo; ?>" height="12" style="margin: 0 8px;" alt="Cebu Pacific"><?php endif; ?>
            <?php if (!empty($airAsiaLogo)): ?><img src="<?php echo $airAsiaLogo; ?>" height="13" style="margin: 0 8px;" alt="AirAsia"><?php endif; ?>
        </div>

        <h3>1.2 Accessing the Dashboard &amp; Operational Security</h3>
        <div class="step-card">
            <span class="step-badge">Step 1</span> <span class="step-title">Admin Portal Login:</span>
            Open a web browser and navigate to <code>https://www.amigagracia.com/admin</code>. Enter your authorized administrator email and password, then click <strong>Sign in</strong>.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 2</span> <span class="step-title">The Executive Dashboard:</span>
            Upon authentication, you enter the <strong>System Dashboard</strong> featuring four real-time KPI monitors:
            <ul>
                <li><strong>Gross Sales Today &amp; Month-to-Date:</strong> Real-time peso revenue from confirmed tickets across sea, air, and tour packages.</li>
                <li><strong>Active Passenger Count:</strong> Total travelers booked on upcoming voyages and flights.</li>
                <li><strong>Pending Payment Proofs Counter:</strong> Immediate counter showing unverified customer payment receipts awaiting review.</li>
                <li><strong>Operator Volume Breakdown:</strong> Interactive charts comparing passenger bookings across Starlite Ferries, 2GO Travel, Cebu Pacific, PAL, and AirAsia.</li>
            </ul>
        </div>

        <div class="callout callout-warning">
            <div class="callout-title">[ADMINISTRATOR SECURITY MANDATE]</div>
            Always log out before leaving your workstation. The admin portal automatically logs session IDs, user credentials, IP addresses, and exact timestamps for every sensitive action (including payment approvals, schedule deletions, and refund disbursements).
        </div>

        <h3>1.3 Notification Center &amp; Real-Time Alerts</h3>
        <p>Located in the top-right header, the <strong>Notification Bell</strong> provides instant operational alerts:</p>
        <ul>
            <li><strong>New Booking Alerts:</strong> Triggers immediately when a customer submits a booking online or via the mobile app.</li>
            <li><strong>Payment Proof Submissions:</strong> Displays uploaded GCash, Maya, or bank transfer receipts needing approval.</li>
            <li><strong>Rebooking &amp; Cancellation Notices:</strong> Alerts staff to customer schedule change requests or refund filings.</li>
            <li><strong>Action Tools:</strong> Click on any alert to jump directly to the relevant record, or use <em>Mark All as Read</em>.</li>
        </ul>
    </div>

    <!-- ========================================================================= -->
    <!-- PAGE 4: CHAPTER 2 - USER & STAFF GOVERNANCE -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>Chapter 2: User Administration &amp; Granular Permissions</h2>

        <h3>2.1 The User Role Hierarchy</h3>
        <p>Amiga Gracia implements a role-based access control (RBAC) model to ensure strict operational segregation of duties:</p>

        <table class="styled-table">
            <tr>
                <th width="18%">Role</th>
                <th width="30%">Target Position</th>
                <th width="52%">Operational Scope &amp; Responsibilities</th>
            </tr>
            <tr>
                <td><strong>Super Admin</strong></td>
                <td>Lead IT &amp; System Architect</td>
                <td>Technical administration: server diagnostics, raw database backups, system error tracing, and infrastructure health checks.</td>
            </tr>
            <tr>
                <td><strong>Admin</strong></td>
                <td>Business Owner, Branch Manager, Operations Head</td>
                <td>Full executive authority: managing staff accounts, setting prices, configuring payment accounts, approving refunds, and publishing tour packages.</td>
            </tr>
            <tr>
                <td><strong>Staff</strong></td>
                <td>Ticketing Clerks, Front-Desk Agents, Customer Support</td>
                <td>Operational execution: verifying payments, viewing manifests, handling walk-in bookings, and processing rebookings according to granted permissions.</td>
            </tr>
            <tr>
                <td><strong>Finance</strong></td>
                <td>Accountants, Bookkeepers</td>
                <td>Financial auditing: Overall Reports, transaction reconciliations, payment audit logs, and refund disbursement verification.</td>
            </tr>
        </table>

        <h3>2.2 Creating Staff Accounts &amp; Granular Permissions</h3>
        <p>Located at <strong>Administration &rarr; Staff Accounts</strong>, administrators create, update, or deactivate employee logins:</p>

        <div class="step-card">
            <span class="step-badge">Step 1</span> Click the <strong>New Staff Account</strong> button in the upper-right corner.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 2</span> Enter the employee's <strong>Full Legal Name</strong>, corporate <strong>Email Address</strong>, and initial <strong>Password</strong>.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 3</span> Select the Role as <strong>Staff</strong>. The <em>Granular Permissions Matrix</em> will display 8 functional groups:
            <ul style="margin: 2px 0 0 15px;">
                <li><strong>Bookings:</strong> Allows viewing booking records and transaction manifests.</li>
                <li><strong>Proofs:</strong> Grants access to the Proof of Payment verification screen.</li>
                <li><strong>Schedules &amp; Routes:</strong> Permits viewing timetables and vessel departures.</li>
                <li><strong>Refunds:</strong> Enables reviewing and processing passenger ticket refund requests.</li>
                <li><strong>Inquiries:</strong> Allows reading and answering customer support contact tickets.</li>
            </ul>
        </div>
        <div class="step-card">
            <span class="step-badge">Step 4</span> Save the account. The staff member can now log in immediately with their credentials.
        </div>

        <h3>2.3 Staff Login Histories &amp; Audit Trail</h3>
        <p>Inside every staff member's profile, administrators can inspect the <strong>Login Histories</strong> tab. This displays a complete chronological log of every successful login, recorded IP address, browser user-agent, and session duration for complete accountability.</p>

        <h3>2.4 Mobile APK Users Management (`ApkUserResource`)</h3>
        <p>Located at <strong>Administration &rarr; Mobile APK Users</strong>, this screen manages customers who have registered an account via the Android mobile app:</p>
        <ul>
            <li><strong>Customer Data:</strong> View traveler names, verified email addresses, mobile numbers, and date registered.</li>
            <li><strong>Loyalty Balance:</strong> View the customer's current <strong>Gracia Points</strong> wallet balance and redemption history.</li>
            <li><strong>Referral Tracking:</strong> Inspect the customer's unique Referral Code and see which new travelers they invited.</li>
            <li><strong>Account Status:</strong> Verify whether the customer has completed email OTP verification.</li>
        </ul>
    </div>

    <!-- ========================================================================= -->
    <!-- PAGE 5: CHAPTER 3 - MASTER BOOKINGS & MANIFESTS -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>Chapter 3: Master Booking &amp; Passenger Manifest Operations</h2>

        <h3>3.1 The Master Bookings Register</h3>
        <p>Located at <strong>Bookings &rarr; Bookings</strong>, this is the operational command center for all passenger reservations made across Web, Mobile App, and Front-Desk channels.</p>

        <h4>Powerful Real-Time Search &amp; Filter Tools:</h4>
        <ul>
            <li><strong>Transaction Number Search:</strong> Instantly locate any booking using its unique code (e.g. <code>AGT-2026-10048</code>).</li>
            <li><strong>Client Search:</strong> Search by passenger full name, primary booker email address, or contact phone number.</li>
            <li><strong>Status Filtering:</strong> Filter live views by status:
                <strong style="color: #d97706;">PENDING</strong>,
                <strong style="color: #16a34a;">CONFIRMED</strong>,
                <strong style="color: #dc2626;">CANCELLED</strong>,
                <strong style="color: #ef4444;">REJECTED</strong>,
                <strong style="color: #0284c7;">REBOOKED</strong>,
                <strong style="color: #9333ea;">REFUNDED</strong>, or
                <strong style="color: #dc2626;">DISRUPTED</strong>.
            </li>
            <li><strong>Operator &amp; Route Filter:</strong> View bookings specifically for <em>Starlite Ferries, 2GO Travel, Cebu Pacific, PAL, or AirAsia</em>.</li>
        </ul>

        <h3>3.2 Passenger Manifest &amp; Discount ID Review</h3>
        <p>Clicking <strong>View</strong> on any booking opens the comprehensive <strong>Passenger Manifest</strong>:</p>
        <div class="step-card">
            <span class="step-title">Passenger Details Breakdown:</span>
            <p style="margin: 2px 0 0 0; font-size: 7.4pt;">
                Lists every traveler under the booking, including legal full name, gender, date of birth, age classification (Adult, Child, Infant), and assigned seat or cabin number.
            </p>
        </div>
        <div class="step-card">
            <span class="step-title">Special Statutory Discounts:</span>
            <p style="margin: 2px 0 0 0; font-size: 7.4pt;">
                If a 20% discount was claimed for a <strong>Senior Citizen, PWD, or Student</strong>, administrators can view the uploaded photo of their government ID card to confirm legal discount qualification before issuing the final ticket.
            </p>
        </div>
        <div class="step-card">
            <span class="step-title">Airline Baggage Allowance:</span>
            <p style="margin: 2px 0 0 0; font-size: 7.4pt;">
                Displays the baggage weight purchased for each passenger (e.g., Free 7kg Carry-on, 20kg Standard Check-in, or 32kg Heavy Baggage).
            </p>
        </div>

        <h3>3.3 Rolling Cargo Manifests (Vehicle RoRo Freight)</h3>
        <p>When travelers take their vehicles on sea ferries, vehicle details are tied directly to the booking:</p>
        <ul>
            <li><strong>Vehicle Specifications:</strong> Displays Vehicle Brand (e.g., <em>Toyota</em>), Model (<em>Fortuner</em>), and official <strong>Plate Number</strong>.</li>
            <li><strong>Freight Classification:</strong> Shows the assigned freight class (e.g. Class 2 SUV) and the vehicle shipping fee collected.</li>
            <li><strong>Driver Identification:</strong> Confirms which passenger on the manifest is designated as the certified driver.</li>
        </ul>

        <h3>3.4 Exporting Manifests (Coast Guard &amp; Port Authorities)</h3>
        <p>Philippine maritime regulations require submitting official passenger and cargo manifests to the <strong>Philippine Coast Guard (PCG)</strong> and port terminal operators prior to vessel departure:</p>
        <table class="styled-table">
            <tr>
                <th width="26%">Export Option</th>
                <th width="74%">Use Case &amp; Recipient</th>
            </tr>
            <tr>
                <td><strong>Export Manifest PDF</strong></td>
                <td>Generates a clean, printable official passenger manifest formatted to maritime inspection standards for the Coast Guard and vessel captain.</td>
            </tr>
            <tr>
                <td><strong>Export Manifest CSV / Excel</strong></td>
                <td>Downloads spreadsheet data containing all booking financial totals, passenger demographic details, and operator commissions for accounting and tax audits.</td>
            </tr>
            <tr>
                <td><strong>Direct Print</strong></td>
                <td>Sends the formatted manifest directly to terminal ticket office printers for immediate physical stamping and filing.</td>
            </tr>
        </table>
    </div>

    <!-- ========================================================================= -->
    <!-- PAGE 6: CHAPTER 4 - PAYMENT VERIFICATION -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>Chapter 4: Payment Verification &amp; Proof Management</h2>

        <h3>4.1 The Payment Proof Center (`ManageProofs`)</h3>
        <p>Located at <strong>Reports &rarr; Proof of Payment</strong>, this critical module handles customer payment receipt verification for GCash, Maya, and manual bank transfers.</p>

        <h3>4.2 The 5-Point Payment Verification Protocol</h3>
        <p>To protect the business against forged screenshots, edited transaction numbers, or duplicate receipts, all staff and administrators must enforce this strict 5-point protocol:</p>

        <div class="step-card">
            <span class="step-badge">Point 1</span> <span class="step-title">Screenshot Authenticity Inspection</span>
            <p style="margin: 2px 0 0 0; font-size: 7.4pt;">
                Inspect the uploaded image closely. Verify it is a genuine, uncropped transaction receipt from GCash, Maya, or an authorized online banking app. Beware of blurry fonts or cut-off reference numbers.
            </p>
        </div>

        <div class="step-card">
            <span class="step-badge">Point 2</span> <span class="step-title">Recipient Account Confirmation</span>
            <p style="margin: 2px 0 0 0; font-size: 7.4pt;">
                Verify that the payment was transferred to the official Amiga Gracia Travel Services corporate GCash number, Maya merchant QR, or registered company bank account.
            </p>
        </div>

        <div class="step-card">
            <span class="step-badge">Point 3</span> <span class="step-title">Exact Amount Reconciliation</span>
            <p style="margin: 2px 0 0 0; font-size: 7.4pt;">
                The receipt total must match the required booking price down to the exact centavo. Never approve partial payments or short amounts.
            </p>
        </div>

        <div class="step-card">
            <span class="step-badge">Point 4</span> <span class="step-title">Payment Timestamp Verification</span>
            <p style="margin: 2px 0 0 0; font-size: 7.4pt;">
                Check the date and time on the payment receipt. It must coincide with the booking time window. This prevents customers from submitting receipts from old trips.
            </p>
        </div>

        <div class="step-card">
            <span class="step-badge">Point 5</span> <span class="step-title">Merchant SMS / App Inbox Cross-Check</span>
            <p style="margin: 2px 0 0 0; font-size: 7.4pt;">
                When handling high-value bookings or unfamiliar customers, cross-reference the transaction reference number with your branch's official merchant SMS inbox or online banking portal.
            </p>
        </div>

        <h3>4.3 Approving &amp; Rejecting Transactions</h3>
        <ul>
            <li><strong>Approving a Transaction:</strong> Click the green <strong>Verify &amp; Confirm</strong> button. The system immediately updates the booking status to <strong style="color: #16a34a;">CONFIRMED</strong>, updates seat inventories, credits the customer's Gracia Points, and triggers automated email delivery of their official E-Ticket PDF.</li>
            <li><strong>Rejecting an Invalid Receipt:</strong> Click <strong>Reject Payment</strong>. Enter an internal explanation (e.g., <em>"Reference number not found in bank logs"</em> or <em>"Short payment"</em>). The booking status transitions to <strong style="color: #ef4444;">REJECTED</strong>, the customer is notified with the reason, and the rejection is logged in the branch Rejection Rate KPI.</li>
        </ul>

        <h3>4.4 Server Storage Protection: Proof Retention Rules</h3>
        <p>High-resolution payment screenshots can rapidly consume server storage over time. On the <strong>Manage Proofs</strong> page, administrators can configure the <strong>Proof Retention Days</strong> parameter (default: <code>30</code> days). When enabled, proof image files older than 30 days are automatically pruned from the server disk, while all financial database records and transaction numbers remain permanently archived.</p>
    </div>

    <!-- ========================================================================= -->
    <!-- PAGE 7: CHAPTER 5 - TICKETS & AIRLINE INTEGRATION -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>Chapter 5: Ticket Issuance &amp; Airline Confirmation Integration</h2>

        <h3>5.1 Automated Ferry E-Ticket Generation</h3>
        <p>For ferry voyages operated by <strong>Starlite Ferries</strong> and <strong>2GO Travel</strong>, the Amiga Gracia system automatically generates an official, standardized <strong>E-Ticket &amp; Travel Itinerary PDF</strong>. It features the official corporate logo, voyage schedule, cabin assignment, passenger manifest, baggage rules, and an scannable QR boarding pass.</p>

        <h3>5.2 Custom Commercial Airline Confirmation PDF Uploads</h3>
        <p>Commercial airlines (such as <em>Cebu Pacific, Philippine Airlines, and AirAsia</em>) operate on centralized global distribution systems (GDS) and issue official airline booking confirmations featuring proprietary Passenger Name Record (PNR) barcodes.</p>

        <h4>How to Attach Official Airline PDFs:</h4>
        <div class="step-card">
            <span class="step-badge">Step 1</span> Locate the confirmed airline transaction under <strong>Bookings &rarr; Receipts &amp; Tickets</strong>.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 2</span> In the <strong>Custom Airline Confirmation PDF</strong> field, click upload and attach the official e-ticket document downloaded from the airline partner portal.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 3</span> Alternatively, if the airline provides an online check-in link, enter it into the <strong>Official Airline Confirmation URL</strong> field.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 4</span> Save the record. When the customer taps <em>Download Ticket</em> on their website account or mobile app, the system will seamlessly deliver this official airline document directly to their phone!
        </div>

        <h3>5.3 Single-Passenger Boarding Passes</h3>
        <p>For group bookings (e.g. 5 passengers under 1 transaction), the system allows generating individual single-passenger boarding pass PDFs via <code>/ticket/passenger/{passenger_id}</code>. This allows family members or corporate travelers to hold their own personalized boarding pass displaying their individual seat number.</p>

        <h3>5.4 Automated E-Ticket Customer Delivery</h3>
        <p>Upon payment verification, the system executes an automated multi-channel delivery sequence:</p>
        <ul>
            <li><strong>Automated Transactional Email:</strong> Dispatches the official itinerary PDF as an email attachment via SendGrid or SMTP.</li>
            <li><strong>Web Customer Portal:</strong> The ticket becomes immediately downloadable in the customer's <em>My Bookings</em> dashboard.</li>
            <li><strong>Mobile Flutter App:</strong> The Android app refreshes automatically, displaying the trip under <em>Active Trips</em> with offline PDF caching.</li>
        </ul>

        <div class="callout callout-tip">
            <div class="callout-title">TICKET PRINTING REQUIREMENT</div>
            Passengers traveling on sea voyages or commercial flights must bring a <strong>printed physical copy of their E-Ticket itinerary</strong> to present at the port terminal or airport check-in counter along with valid government IDs.
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- PAGE 8: CHAPTER 6 - TRANSPORT MASTER DATA -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>Chapter 6: Transport Master Data Management</h2>

        <h3>6.1 Travel Route Management (`FerryRouteResource`)</h3>
        <p>Located at <strong>Transport Master Data &rarr; Routes</strong>, administrators define the Philippine travel network:</p>
        <ul>
            <li><strong>Origin &amp; Destination Ports:</strong> Set departure and arrival terminals (e.g. <em>Batangas Pier &rarr; Calapan Port; Manila NAIA &rarr; Caticlan Boracay</em>).</li>
            <li><strong>Transport Mode:</strong> Categorize routes as <strong>Ferry</strong> or <strong>Airline</strong>.</li>
            <li><strong>Distance &amp; Duration:</strong> Set estimated voyage or flight hours to provide accurate arrival estimates on passenger itineraries.</li>
            <li><strong>Active Toggle:</strong> Temporarily deactivate seasonal routes with a single click without deleting historical records.</li>
        </ul>

        <h3>6.2 Vessel &amp; Aircraft Fleet Registry (`VehicleResource`)</h3>
        <p>Located at <strong>Transport Master Data &rarr; Vehicles / Vessels</strong>, this module registers the physical fleet:</p>
        <ul>
            <li><strong>Ship / Plane Name:</strong> Register vessel names (e.g. <em>MV Starlite Eagle, MV 2GO Maligaya</em>) or aircraft (<em>Airbus A320-200, ATR 72-600</em>).</li>
            <li><strong>Operator Mapping:</strong> Assign each vehicle to its licensed operator (Starlite, 2GO, PAL, Cebu Pacific, AirAsia).</li>
            <li><strong>Capacity Limits:</strong> Set passenger capacity and cargo tonnage limits to prevent overbooking.</li>
        </ul>

        <h3>6.3 Accommodations &amp; Cabin Classes (`TransportClassResource`)</h3>
        <p>Located under <strong>Transport Master Data &rarr; Accommodations / Classes</strong>, administrators define comfort tiers:</p>

        <table class="styled-table">
            <tr>
                <th width="24%">Accommodation</th>
                <th width="26%">Vessel / Flight Type</th>
                <th width="50%">Description &amp; Amenities</th>
            </tr>
            <tr>
                <td><strong>Economy Open-Air Cot</strong></td>
                <td>RoPax Ferry Vessels</td>
                <td>Bunk beds located on open-air sheltered decks with fresh sea breeze. Most affordable budget option.</td>
            </tr>
            <tr>
                <td><strong>Tourist Aircon Cabin</strong></td>
                <td>Fastcraft &amp; Ferries</td>
                <td>Enclosed, fully air-conditioned dormitory cabins with bunk beds, charging outlets, and clean bedding.</td>
            </tr>
            <tr>
                <td><strong>Cabin / Stateroom</strong></td>
                <td>Cruise Ferries (2GO / Starlite)</td>
                <td>Private 2-person or 4-person room with private bathroom, television, and linens. Ideal for families.</td>
            </tr>
            <tr>
                <td><strong>Suite Class</strong></td>
                <td>Premium Vessels</td>
                <td>Luxury accommodations featuring queen bed, private living area, mini-fridge, and priority boarding.</td>
            </tr>
            <tr>
                <td><strong>Airline Economy / Seat</strong></td>
                <td>Commercial Aircraft</td>
                <td>Standard domestic flight seating with complimentary 7kg carry-on baggage allowance.</td>
            </tr>
        </table>

        <h3>6.4 Airline Baggage Rules &amp; Rolling Cargo Freight (RoRo)</h3>
        <ul>
            <li><strong>Baggage Settings (`AirlineBaggageRuleResource`):</strong> Configure standard check-in tier (₱850 for 20kg), heavy tier (₱1,400 for 32kg), and excess weight per-kg surcharges.</li>
            <li><strong>Vehicle Rates &amp; Brands (`VehicleRateResource`, `VehicleBrandResource`):</strong> Manages RoRo freight across 5 classes: Class 1 (Motorcycles), Class 2 (Sedans/Compact SUVs), Class 3 (Pickups/Large SUVs/Vans), Class 4 (Buses/Light Trucks), Class 5 (Heavy 10-Wheelers).</li>
        </ul>
    </div>

    <!-- ========================================================================= -->
    <!-- PAGE 9: CHAPTER 7 - SCHEDULE MANAGEMENT & IMPORTERS -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>Chapter 7: Schedule Management &amp; Bulk Timetable Importer</h2>

        <h3>7.1 Managing Schedules &amp; Departures</h3>
        <p>Located under <strong>Travel &amp; Tours &rarr; Schedules</strong>, administrators create individual sailings and flight departures, defining departure time, arrival time, vessel assignment, available seats per accommodation tier, and base fares.</p>

        <h3>7.2 The Schedule Import Center (`ImportSchedules`)</h3>
        <p>Entering hundreds of recurring trips manually is time-consuming. The <strong>Schedule Import Center</strong> offers two automated batch solutions:</p>

        <table class="styled-table">
            <tr>
                <th width="32%">Import Method</th>
                <th width="68%">Procedure &amp; Operational Benefits</th>
            </tr>
            <tr>
                <td><strong>Automated Timetable Ingestion (Starlite)</strong></td>
                <td>Select "Starlite" from the operator list, pick a date range (e.g. Next 60 Days), and click <strong>Run Automated Ingestion</strong>. The system populates all active ferry sailings, vessel assignments, departure/arrival hours, and pricing tiers in seconds!</td>
            </tr>
            <tr>
                <td><strong>Excel / CSV Bulk Upload (Airlines &amp; 2GO)</strong></td>
                <td>Download the standard spreadsheet template, input flight/voyage numbers, departure/arrival hours, and base fares. Upload the file to register the entire schedule into the live booking engine.</td>
            </tr>
        </table>

        <h3>7.3 Step-by-Step Guide: Executing Automated Ingestion</h3>
        <div class="step-card">
            <span class="step-badge">Step 1</span> Open <strong>Travel &amp; Tours &rarr; Import Schedules</strong> in the sidebar.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 2</span> Choose <strong>Starlite Ferries</strong> from the Operator dropdown menu.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 3</span> Specify the <strong>Date Range</strong> (e.g., from <code>2026-09-01</code> to <code>2026-10-31</code>).
        </div>
        <div class="step-card">
            <span class="step-badge">Step 4</span> Click <strong>Start Automated Ingestion</strong>. The system connects to the timetable repository, verifies vessel availability, and writes all scheduled sailings into the database without creating duplicates.
        </div>

        <h3>7.4 Modifying &amp; Cancelling Existing Schedules</h3>
        <p>When sea conditions or airline operations change:</p>
        <ul>
            <li><strong>Delaying a Departure:</strong> Open the schedule record, edit the departure and arrival times, and save. The website and mobile app will update instantly.</li>
            <li><strong>Seat Inventory Adjustments:</strong> Modify the remaining seat quotas per accommodation class if an operator reserves seats for group charters.</li>
            <li><strong>Schedule Deactivation:</strong> Toggle the <em>Is Active</em> switch to hide the voyage from online customer searches while preserving past bookings.</li>
        </ul>
    </div>

    <!-- ========================================================================= -->
    <!-- PAGE 10: CHAPTER 8 - REBOOKINGS, REFUNDS & DISRUPTIONS -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>Chapter 8: Rebookings, Refunds &amp; Emergency Disruptions</h2>

        <h3>8.1 The Rebooking Management Center (`ManageRebookings`)</h3>
        <p>When travelers request date or voyage changes, their files flow into <strong>Bookings &rarr; Rebookings</strong>:</p>
        <ul>
            <li><strong>Inspect Original vs. New Schedule:</strong> The admin sees the passenger's current departure schedule side-by-side with their newly requested schedule.</li>
            <li><strong>Automated Fare Difference Computation:</strong> If the new schedule has a higher base fare or peak season surcharge, the system computes the exact difference plus any rebooking fee.</li>
            <li><strong>Confirming Rebooking:</strong> Once the customer pays the difference, clicking <em>Confirm Rebooking</em> updates the passenger manifest, recalculates seat inventories, and generates a newly updated <strong style="color: #0284c7;">REBOOKED</strong> E-Ticket.</li>
        </ul>

        <h3>8.2 The Refund &amp; Disbursement Center (`ManageRefunds`)</h3>
        <p>Located at <strong>Bookings &rarr; Refunds</strong>, ticket cancellations are processed with complete financial rigor:</p>

        <div class="step-card">
            <span class="step-badge">Step 1</span> <strong>Review Cancellation Request:</strong> Inspect the passenger's reason for cancellation (e.g. personal emergency, change of plans) and the original amount paid.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 2</span> <strong>Automatic Cancellation Penalty Deduction:</strong> The system applies the operator's cancellation penalty (e.g. 20% cancellation fee) and displays the exact <strong>Net Refund Amount</strong> to be returned to the traveler.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 3</span> <strong>Disbursing the Funds:</strong> The finance officer transfers the refund to the customer's nominated GCash, Maya, or bank account.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 4</span> <strong>Attaching Disbursement Proof:</strong> Enter the bank transaction number and upload a screenshot of the payout receipt.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 5</span> <strong>Completion &amp; Official E-Refund PDF:</strong> Clicking <em>Mark as Refunded</em> automatically generates and emails an official <strong>E-Refund Acknowledgement PDF</strong> with status updated to <strong style="color: #9333ea;">REFUNDED</strong>.
        </div>

        <h3>8.3 Emergency Service Disruptions &amp; Weather Cancellations</h3>
        <p>During severe weather (typhoon signal warnings from PAGASA or Coast Guard gale warnings), ferry trips and flights are grounded. The <strong>Travel &amp; Tours &rarr; Service Cancellations</strong> module manages these crises smoothly:</p>

        <div class="callout callout-danger" style="margin-bottom: 2px;">
            <div class="callout-title">[FORCE MAJEURE / SERVICE CANCELLATION PROTOCOL]</div>
            <strong>1. Declare Cancelled Voyage:</strong> Select route, carrier, and date under <em>Travel &amp; Tours &rarr; Service Cancellations</em>. System marks bookings as <strong style="color: #dc2626;">DISRUPTED</strong>.<br>
            <strong>2. Resumption &amp; 14-Day Replacements:</strong> When travel resumes, administrators declare the <em>Resume Date</em>, auto-seeding eligible replacement sailings across the next 14 days.<br>
            <strong>3. Zero-Fee Rebooking (₱0.00 Penalty):</strong> All rebooking surcharges and revalidation fees are 100% waived. Same-tier replacement is <strong>₱0.00</strong>. If upgrading to a higher class, travelers only pay the net fare difference (<code>price_diff</code>). Downgrades are disabled self-service to protect customer value.<br>
            <strong>4. 100% Full Refund Guarantee:</strong> Travelers who decline replacement trips receive a <strong>100% full refund</strong> with zero penalty deductions (exempt from standard 20% fees and 24-hr cutoffs).
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- PAGE 11: CHAPTER 9 - MARKETING, VOUCHERS & TOURS -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>Chapter 9: Vouchers, Gracia Coins, Discounts &amp; Tour Packages</h2>

        <h3>9.1 The Voucher &amp; Promo Codes Engine (`VoucherResource`)</h3>
        <p>Located at <strong>Travel &rarr; Vouchers</strong>, administrators create, schedule, and govern marketing coupon promotions across all booking channels:</p>
        <ul>
            <li><strong>Discount Types &amp; Ceiling Caps:</strong> Choose between <strong>Percentage</strong> (e.g. 10% or 20% off) or <strong>Fixed Amount</strong> (e.g. ₱200 or ₱500 off). Set an optional <strong>Maximum Discount</strong> cap (<code>max_discount</code>) to protect profits on large bookings.</li>
            <li><strong>Eligibility Thresholds &amp; Quotas:</strong> Define <strong>Minimum Booking Amount</strong> (e.g., min ₱1,500 spend required) and global <strong>Total Usage Limit</strong> (e.g., first 200 redemptions only).</li>
            <li><strong>Fraud Prevention (One-Use Rule):</strong> Toggle <strong>One Use per Customer</strong> (<code>one_use_per_customer</code>) to prevent the same user account from repeatedly redeeming the code.</li>
            <li><strong>Targeted Operator &amp; Route Scope:</strong> Restrict vouchers to specific operators (e.g., Starlite Ferries or 2GO only) or designated origin/destination ports.</li>
            <li><strong>Hidden Vouchers &amp; Anti-Stacking Policy:</strong> Mark vouchers as <code>is_hidden</code> for targeted VIP email campaigns. The system strictly forbids stacking vouchers with Super Promotional tickets (`VoucherService`).</li>
        </ul>

        <h3>9.2 The Gracia Coins Loyalty Rewards System (`GraciaEarningRuleResource`)</h3>
        <p>Amiga Gracia features a closed-loop loyalty currency—<strong>Gracia Coins (Gracia Points)</strong>—governed under <strong>Settings &rarr; Gracia Rules</strong>:</p>
        <ul>
            <li><strong>Coin Economy:</strong> <strong>1 Gracia Coin = ₱1.00 direct checkout discount</strong>. Coins never expire and cannot be redeemed for physical cash.</li>
            <li><strong>Configuring Earning Rules:</strong> Administrators set spend thresholds (e.g. award <strong>10 Gracia Coins</strong> for every <strong>₱100 spent</strong>). Upon booking confirmation, the engine automatically credits coins to the traveler's digital balance.</li>
            <li><strong>Ledger Audit Trail (`GraciaPointLedger`):</strong> Every coin earned, redeemed, or adjusted is permanently recorded with member ID, booking reference, entry type, qualifying spend, and idempotency key.</li>
            <li><strong>Referral Program Bonuses:</strong> Mobile app users can share their unique Referral Code. When a referred traveler registers and books, <strong>both users earn bonus Gracia Coins</strong>!</li>
        </ul>

        <h3>9.3 Philippine Statutory Discounts Administration (`DiscountResource`)</h3>
        <p>Located at <strong>Travel &rarr; Discounts</strong>, administrators maintain legal compliance with mandatory Philippine discount legislation:</p>

        <table class="styled-table">
            <tr>
                <th width="26%">Beneficiary</th>
                <th width="24%">Governing Law</th>
                <th width="50%">Mandated Computation Formula &amp; ID Requirement</th>
            </tr>
            <tr>
                <td><strong>Senior Citizens</strong></td>
                <td>RA 9994 (Senior Citizens Act)</td>
                <td><strong>20% discount on gross fare + 12% VAT exemption:</strong><br>
                <code>Net Fare = (Gross &times; 0.80) / 1.12</code>. Requires valid OSCA ID.</td>
            </tr>
            <tr>
                <td><strong>Persons with Disabilities</strong></td>
                <td>RA 10754 (PWD Act)</td>
                <td><strong>20% statutory fare discount:</strong> Deducted from regular passenger ticket base fare. Requires official government PWD ID.</td>
            </tr>
            <tr>
                <td><strong>Students</strong></td>
                <td>RA 11314 (Student Fare Act)</td>
                <td><strong>20% domestic travel discount:</strong> Applies to active basic and higher education students. Requires validated School ID.</td>
            </tr>
        </table>

        <h3>9.4 Tour Packages &amp; Partner Accommodations (`TourResource`, `HotelResource`)</h3>
        <p>Publish curated Philippine island vacation packages (Coron, Boracay, El Nido, Bohol) under <strong>Travel &amp; Tours &rarr; Tour Packages</strong>, bundling ferry/flight departures with partner hotel room inventories and island hopping tours.</p>
    </div>

    <!-- ========================================================================= -->
    <!-- PAGE 12: CHAPTER 10 - WEBSITE CMS & SETTINGS -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>Chapter 10: Website Content Management (CMS) &amp; System Settings</h2>

        <h3>10.1 The Live Website Customizer (`ManageWebsiteSettings`)</h3>
        <p>The <strong>Manage Website Settings</strong> page gives administrators complete CMS control over public website pages without writing a single line of code:</p>
        <ul>
            <li><strong>Header &amp; Navigation:</strong> Update the company logo, emergency hotlines, email contacts, and navigation menu links.</li>
            <li><strong>Homepage Hero Slider:</strong> Upload high-definition banner images for seasonal travel promotions and set custom headline banners.</li>
            <li><strong>Booking Cards:</strong> Customize the featured destination cards on the homepage (e.g., Coron, El Nido, Boracay, Calapan).</li>
            <li><strong>About Us &amp; Services:</strong> Update company background history, mission statement, and service descriptions.</li>
            <li><strong>FAQs Management:</strong> Add, edit, or reorder frequently asked questions and answers.</li>
            <li><strong>Footer &amp; Social Media:</strong> Update office address, copyright text, Facebook page URL, and customer service email.</li>
        </ul>

        <h3>10.2 Payment Gateway Settings (`ManagePaymentSettings`)</h3>
        <p>Located at <strong>Settings &rarr; Payment Settings</strong>, administrators configure payment accounts displayed to customers during checkout:</p>
        <div class="two-col">
            <table width="100%">
                <tr>
                    <td class="col-half">
                        <div class="step-card">
                            <strong class="text-green">E-Wallet Channels:</strong><br>
                            &bull; <strong>GCash QR Code:</strong> Upload official GCash merchant QR.<br>
                            &bull; <strong>Maya QR Code:</strong> Upload company Maya merchant QR.<br>
                            &bull; <strong>Merchant Mobile Numbers:</strong> Set corporate transfer numbers.
                        </div>
                    </td>
                    <td class="col-half-last">
                        <div class="step-card">
                            <strong class="text-green">Bank Accounts:</strong><br>
                            &bull; <strong>Bank Name:</strong> BDO, BPI, Landbank, Metrobank.<br>
                            &bull; <strong>Account Name:</strong> Amiga Gracia Travel Services.<br>
                            &bull; <strong>Account Number:</strong> Official corporate checking number.
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <h3>10.3 Broadcast App Push Notifications (`AppNotificationResource`)</h3>
        <p>Under <strong>Settings &rarr; App Notifications</strong>, administrators compose and broadcast instant push notifications directly to travelers' smartphones (e.g. <em>"Super Seat Sale: 50% Off Boracay Flights This Weekend!"</em>) via Firebase Cloud Messaging (FCM).</p>

        <h3>10.4 Customer Inquiry Resolution Center (`InquiryResource`)</h3>
        <p>Under <strong>Administration &rarr; Inquiries</strong>, administrators review and respond to incoming customer messages submitted through the website contact form or mobile app, tracking tickets until fully resolved.</p>
    </div>

    <!-- ========================================================================= -->
    <!-- PAGE 13: CHAPTER 11 - REPORTS & AUDITS -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>Chapter 11: Financial Audits, Overall Reports &amp; Staff Performance</h2>

        <h3>11.1 Overall Business &amp; Financial Reports (`OverallReports`)</h3>
        <p>Found under <strong>Reports &rarr; Overall Reports</strong>, this provides executive business intelligence:</p>
        <table class="styled-table">
            <tr>
                <th width="25%">Report Metric</th>
                <th width="75%">Operational Meaning</th>
            </tr>
            <tr>
                <td><strong>Gross Ticket Sales</strong></td>
                <td>Total revenue collected across all travel modes (Ferries, Flights, Tour Packages, and Cargo).</td>
            </tr>
            <tr>
                <td><strong>Operator Commissions</strong></td>
                <td>Itemized breakdown of ticketing commissions due from Starlite, 2GO, Cebu Pacific, PAL, and AirAsia.</td>
            </tr>
            <tr>
                <td><strong>Net Payouts &amp; Refunds</strong></td>
                <td>Total funds disbursed for ticket cancellations and passenger refunds.</td>
            </tr>
            <tr>
                <td><strong>Rejection Rate &amp; Stats</strong></td>
                <td>Tracks rejected bookings, rejected rebookings, and percentage rejection rate to identify fraud trends.</td>
            </tr>
            <tr>
                <td><strong>Export Tools</strong></td>
                <td>Download complete audited reports in <strong>PDF</strong> (for executive reviews), <strong>CSV / Excel</strong> (for accounting spreadsheets), or send to <strong>Print</strong>.</td>
            </tr>
        </table>

        <h3>11.2 Staff Performance Analytics Leaderboard (`StaffPerformance`)</h3>
        <p>Found under <strong>Reports &rarr; Staff Performance</strong>, this module tracks employee productivity across custom date ranges:</p>
        <div class="two-col">
            <table width="100%">
                <tr>
                    <td class="col-half">
                        <div class="step-card">
                            <strong class="text-green">Productivity Metrics:</strong><br>
                            &bull; Total payment proofs verified per employee.<br>
                            &bull; Total confirmed, rebooked, and rejected bookings.<br>
                            &bull; Average verification turnaround time per shift.
                        </div>
                    </td>
                    <td class="col-half-last">
                        <div class="step-card">
                            <strong class="text-green">Sales Leaderboard:</strong><br>
                            &bull; Gross sales revenue generated per employee.<br>
                            &bull; Leaderboard ranking top-performing ticketing agents.<br>
                            &bull; Invaluable tool for monthly performance bonuses.
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <h3>11.3 My Page &amp; Personal Shift Reconciliation (`MyPage`)</h3>
        <p>Every administrator and staff member has a personalized dashboard at <strong>My Account &rarr; My Page &amp; Reports</strong>. It lists every transaction personally handled during your shift, enabling seamless evening cash drawer reconciliation.</p>

        <h3>11.4 End-of-Day Balancing Protocol</h3>
        <div class="step-card">
            <span class="step-badge">Step 1</span> Open <strong>My Account &rarr; My Page &amp; Reports</strong>. Filter transactions for "Today".
        </div>
        <div class="step-card">
            <span class="step-badge">Step 2</span> Tally cash on hand against total payments recorded as "Cash" in the shift manifest.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 3</span> Cross-reference GCash, Maya, and Bank transfer totals against bank merchant receipts.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 4</span> Export the shift summary report to PDF, sign off, and file with the Branch Manager.
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- PAGE 14: CHAPTER 12 - TROUBLESHOOTING & STATUS MASTER -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>Chapter 12: Admin Troubleshooting &amp; System Maintenance</h2>

        <h3>12.1 System Diagnostic Endpoints</h3>
        <p>Super Admins and IT staff can monitor server health using built-in diagnostic routes:</p>
        <ul>
            <li><code>/health-check</code>: Verifies PHP version, database connectivity, cache driver, SendGrid mailer, and Firebase credentials.</li>
            <li><code>/queue-status</code>: Inspects background asynchronous queue jobs (email sending and PDF generation).</li>
            <li><code>/db-test</code>: Tests raw database latency, active table counts, and connection pools.</li>
        </ul>

        <h3>12.2 Storage Symlinks &amp; File Permissions</h3>
        <p>If uploaded receipts or e-ticket PDFs fail to display, ensure the public storage symlink is active by running:</p>
        <pre style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 4px; font-size: 7.5pt; border-radius: 4px; margin: 3px 0;">php artisan storage:link</pre>

        <h3>12.3 Complete Status Master Dictionary</h3>
        <p>All operational statuses across bookings, rebookings, refunds, and tickets are strictly defined below:</p>
        <table class="styled-table">
            <tr>
                <th width="16%">Status</th>
                <th width="14%">Indicator</th>
                <th width="70%">Meaning &amp; Required Operational Action</th>
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
                <td>Trip cancelled voluntarily by traveler or operator. Ticket is void.</td>
            </tr>
            <tr>
                <td><strong>Rejected</strong></td>
                <td><strong style="color: #ef4444;">REJECTED</strong></td>
                <td>Payment proof rejected as fraudulent/invalid, or rebooking denied. Recorded in branch Rejection Rate KPI.</td>
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
    </div>

    <!-- ========================================================================= -->
    <!-- PAGE 15: CHAPTER 13 - INTERACTIVE MODALS GUIDE (PART 1) -->
    <!-- ========================================================================= -->
    <div class="page-break">
        <h2>Chapter 13: Master Modal Operations Guide (Operations, Proofs &amp; Rebookings)</h2>
        <p class="text-muted" style="font-size: 7.2pt; margin-bottom: 5px;">A step-by-step operational standard for every interactive dialog window and modal in the ticketing workflow.</p>

        <h3>13.1 Payment Proof Verification Modal (`ManageProofs`)</h3>
        <p>Opened by clicking the green <strong>Verify</strong> button on any pending customer payment screenshot:</p>
        <div class="step-card">
            <span class="step-badge">Visual Inspection</span> <strong>Full-Resolution Proof Lightbox:</strong> Inspect the uploaded GCash, Maya, or bank transfer receipt. Ensure reference number, payment timestamp, recipient merchant name, and total amount match down to the exact centavo.
        </div>
        <div class="step-card">
            <span class="step-badge">Data Entry</span> <strong>Confirmed Reference No.:</strong> Type or confirm the bank transaction reference number into the input field.
        </div>
        <div class="step-card">
            <span class="step-badge">Action Button</span> <strong>Click [Confirm &amp; Issue Tickets]:</strong>
            <p style="margin: 2px 0 0 0; font-size: 7.3pt;">
                <strong>Automated System Triggers:</strong> (1) Status updates to <strong style="color: #16a34a;">CONFIRMED</strong>; (2) Allocates seat inventories; (3) Automatically generates the standardized E-Ticket PDF with QR boarding pass; (4) Dispatches the confirmation email with PDF attachment; (5) Credits customer's Gracia Points loyalty balance.
            </p>
        </div>

        <h3>13.2 Payment Rejection Modal (`ManageProofs`)</h3>
        <p>Opened by clicking the red <strong>Reject Payment</strong> button on invalid, short, or unreadable receipts:</p>
        <div class="step-card">
            <span class="step-badge">Required Input</span> <strong>Rejection Reason Textarea:</strong> Enter clear, actionable feedback for the client (e.g., <em>"Reference number not found in BDO records"</em>, <em>"Short payment: ₱400 balance remaining"</em>, or <em>"Screenshot is cut off / blurry"</em>).
        </div>
        <div class="step-card">
            <span class="step-badge">Action Button</span> <strong>Click [Confirm Rejection]:</strong>
            <p style="margin: 2px 0 0 0; font-size: 7.3pt;">
                <strong>Automated System Triggers:</strong> (1) Booking status transitions to <strong style="color: #ef4444;">REJECTED</strong>; (2) Sends immediate email alert displaying the rejection reason and a secure link for the customer to re-upload proof; (3) Logs the rejecting staff member's ID in the branch <strong>Rejection Rate KPI</strong>.
            </p>
        </div>

        <h3>13.3 View Booking &amp; Manifest Slide-Over Modal (`BookingResource`)</h3>
        <p>Opened by clicking the <strong>View</strong> (eye icon) button or clicking any transaction number:</p>
        <ul>
            <li><strong>Passenger Manifest Review:</strong> Review full legal names, gender, birth dates, age classifications, and assigned seat/cabin numbers.</li>
            <li><strong>Statutory Discount Inspection:</strong> Click the preview badge for <strong>Senior Citizen (OSCA ID)</strong>, <strong>PWD ID</strong>, or <strong>Student ID</strong> to view the uploaded government card photo. Verify photo and birth date before honoring the 20% discount.</li>
            <li><strong>Rolling Cargo (RoRo) Freight:</strong> Inspect vehicle plate number, brand, model, freight classification, and certified driver.</li>
            <li><strong>Ticket Downloads:</strong> Click <em>Download Ticket PDF</em> or generate individual single-passenger boarding passes.</li>
        </ul>

        <h3>13.4 Commercial Airline Confirmation Attachment Modal (`Receipts &amp; Tickets`)</h3>
        <p>Opened by clicking <strong>Attach Airline PDF</strong> on commercial flight bookings (Cebu Pacific, PAL, AirAsia):</p>
        <div class="step-card">
            <span class="step-badge">Uploader</span> <strong>Custom Airline Confirmation PDF:</strong> Attach the official PDF e-ticket with PNR barcode downloaded from the airline portal. Optional: paste the airline web check-in URL into <code>ticket_url</code>.
        </div>
        <p style="font-size: 7.3pt; margin-top: 2px;">
            <strong>Result:</strong> When travelers click <em>Download Ticket</em> on the mobile app or website, the system delivers the authentic airline PNR document.
        </p>

        <h3>13.5 Rebooking Review &amp; Approval Modal (`ManageRebookings`)</h3>
        <p>Opened by clicking <strong>Review Rebooking</strong> on requests marked <code>reschedule_requested</code>:</p>
        <ul>
            <li><strong>Side-by-Side Comparison:</strong> Compares original departure date, route, and vessel with the newly selected replacement voyage.</li>
            <li><strong>Automated Fare Difference:</strong> Calculates new fare vs original fare, displaying the net rate difference (<code>price_diff</code>).</li>
            <li><strong>Payment Proof:</strong> Inspects receipt screenshot if the traveler upgraded to a higher cabin tier.</li>
            <li><strong>Approval Action:</strong> Click <strong>Approve Rebooking</strong> to update status to <strong style="color: #0284c7;">REBOOKED</strong> and re-issue the updated itinerary, or <strong>Decline Rebooking</strong> with explanatory notes.</li>
        </ul>
    </div>

    <!-- ========================================================================= -->
    <!-- PAGE 16: CHAPTER 14 - INTERACTIVE MODALS GUIDE (PART 2) -->
    <!-- ========================================================================= -->
    <div>
        <h2>Chapter 14: Master Modal Operations Guide (Refunds, Disruptions &amp; Settings)</h2>
        <p class="text-muted" style="font-size: 7.2pt; margin-bottom: 5px;">Operational reference for finance disbursements, weather emergencies, schedule imports, and system settings.</p>

        <h3>14.1 Refund Disbursement &amp; Exclusive Review Lock (`ManageRefunds`)</h3>
        <div class="step-card">
            <span class="step-badge">Step 1</span> <strong>Claim Review Lock:</strong> Click <em>Process Refund</em>. The modal activates an exclusive <strong>10-minute timer lock</strong>. Other staff see <em>"Locked by [Your Name]"</em> to prevent duplicate payouts.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 2</span> <strong>Inspect Financials &amp; Destination:</strong> Review Original Total, Fee Deduction (e.g. 20% penalty, or ₱0 for weather cancellations), and <strong>Net Refund Payable</strong>. Review recipient account: GCash number, Maya wallet, or Bank Name + Account No. + Holder Name.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 3</span> <strong>Disburse &amp; Record Proof:</strong> Transfer funds via corporate banking. Type the <strong>Disbursement Reference Number</strong> and upload the transfer receipt screenshot into <strong>Disbursement Proof</strong>.
        </div>
        <div class="step-card">
            <span class="step-badge">Step 4</span> <strong>Click [Complete Disbursement]:</strong> Updates booking and passenger status to <strong style="color: #9333ea;">REFUNDED</strong>, generates and emails the official <strong>E-Refund Acknowledgement PDF</strong>, and clears the review lock.
        </div>

        <h3>14.2 Service Disruption &amp; Resumption Modals (`ServiceCancellationResource`)</h3>
        <ul>
            <li><strong>New Cancellation Modal:</strong> Select Service Type (*Ferry* or *Airline*), Scope (*Specific Schedule*, *Carrier Date*, or *Date Range*), Reason (*PAGASA Storm Signal*, *Coast Guard Gale Warning*, *Safety*), and Customer Message. Click <strong>Finalize Cancellation</strong> to mark bookings as <strong style="color: #dc2626;">DISRUPTED</strong> and dispatch reschedule alerts.</li>
            <li><strong>Declare Resume Date Modal:</strong> When maritime clearance is granted, click <strong>Declare Resume Date</strong> and select the date. The system auto-seeds eligible replacement sailings across the next <strong>14 days</strong> and alerts travelers that free rebooking is active.</li>
        </ul>

        <h3>14.3 Automated Schedule Ingestion Modal (`ImportSchedules`)</h3>
        <p>Open <em>Travel &amp; Tours &rarr; Import Schedules</em>. Select <strong>Starlite Ferries</strong> from the Operator dropdown, specify the Date Range (e.g. Next 60 Days), and click <strong>Start Automated Ingestion</strong>. The engine queries the timetable repository, verifies fleet assignments, and populates all scheduled sailings, accommodations, and pricing tiers without duplicates.</p>

        <h3>14.4 Marketing Voucher Creation Modal (`VoucherResource`)</h3>
        <p>Open <em>Travel &rarr; Vouchers</em> and click <strong>+ New Voucher</strong>:</p>
        <ul>
            <li><strong>Code &amp; Deduction:</strong> Enter promo code; select <em>Percentage</em> (e.g. 10%) or <em>Fixed Amount</em> (₱200).</li>
            <li><strong>Financial Guardrails:</strong> Set <strong>Max Discount Cap</strong> (<code>max_discount</code>), <strong>Min Spend</strong>, and <strong>Total Usage Limit</strong>.</li>
            <li><strong>Fraud Prevention:</strong> Enable <strong>One Use Per Customer</strong>. Anti-stacking rule prevents use on super-promos.</li>
        </ul>

        <h3>14.5 Broadcast Push Notification &amp; Payment Gateway Modals</h3>
        <div class="two-col">
            <table width="100%">
                <tr>
                    <td class="col-half">
                        <div class="step-card">
                            <strong class="text-green">Broadcast Push Alert (`AppNotificationResource`):</strong><br>
                            Click <em>+ New Notification</em>. Enter title, body text, and select target audience. Click <strong>Send Broadcast</strong> to deliver instant alerts via Firebase Cloud Messaging (FCM).
                        </div>
                    </td>
                    <td class="col-half-last">
                        <div class="step-card">
                            <strong class="text-green">Payment Settings (`ManagePaymentSettings`):</strong><br>
                            Upload official merchant <strong>GCash QR</strong> and <strong>Maya QR</strong> images. Update official corporate BDO, BPI, or Landbank account numbers displayed at checkout.
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <h3>14.6 Staff Account Creation &amp; 8-Point Permissions Modal (`UserResource`)</h3>
        <p>Open <em>Administration &rarr; Users</em> and click <strong>+ New User</strong>. Enter staff member's legal name, email, and password. Select Role (<em>Admin</em> or <em>Staff</em>). Check authorized modules: <code>Bookings</code>, <code>Manifests</code>, <code>Proofs</code>, <code>Schedules</code>, <code>Vouchers</code>, <code>Discounts</code>, <code>Refunds</code>, and <code>Reports</code>. Click <strong>Create Staff Account</strong>.</p>

        <div style="margin-top: 6px; text-align: center; border-top: 2px solid #216417; padding-top: 4px;">
            <strong class="text-green" style="font-size: 8.8pt;">AMIGA GRACIA TRAVEL SERVICES &bull; ADMINISTRATOR OPERATIONS MANUAL</strong><br>
            <span class="text-muted" style="font-size: 7.2pt;">Official Governance Documentation &bull; For Internal Administrative &amp; Executive Use Only</span>
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

// Canvas dynamic running header and footer on all pages except page 1
$canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
    if ($pageNumber > 1) {
        $font = $fontMetrics->get_font("DejaVu Sans", "normal");
        $fontBold = $fontMetrics->get_font("DejaVu Sans", "bold");
        
        // Running Top Header
        $canvas->text(38, 20, "AMIGA GRACIA TRAVEL SERVICES • ADMINISTRATOR OPERATIONS MANUAL", $fontBold, 6.8, [0.13, 0.39, 0.09]);
        $canvas->text(385, 20, "Executive Governance & Master Operations", $font, 6.8, [0.55, 0.6, 0.65]);
        $canvas->line(38, 29, 557, 29, [0.8, 0.83, 0.88], 0.75);
        
        // Running Bottom Footer
        $canvas->line(38, 808, 557, 808, [0.8, 0.83, 0.88], 0.75);
        $canvas->text(38, 814, "© " . date('Y') . " Amiga Gracia Travel Services. Confidential Executive Operations.", $font, 6.8, [0.4, 0.45, 0.5]);
        $canvas->text(502, 814, "Page " . $pageNumber . " of " . $pageCount, $font, 6.8, [0.4, 0.45, 0.5]);
    }
});

$outputPdfRoot = __DIR__ . '/Amiga_Travel_Admin_Manual.pdf';
$outputPdfPublic = __DIR__ . '/public/Amiga_Travel_Admin_Manual.pdf';

file_put_contents($outputPdfRoot, $dompdf->output());
file_put_contents($outputPdfPublic, $dompdf->output());

echo "SUCCESS: Dedicated Admin Manual Generated!\n";
echo "Root File: " . $outputPdfRoot . " (" . number_format(filesize($outputPdfRoot) / 1024, 1) . " KB)\n";
echo "Public File: " . $outputPdfPublic . " (" . number_format(filesize($outputPdfPublic) / 1024, 1) . " KB)\n";
