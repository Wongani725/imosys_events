<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} — Privacy Policy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('cms/vendor/fonts/boxicons.css') }}">
    <style>
        body {
            background-color: #f4f5f7;
            font-family: 'Public Sans', sans-serif;
        }
        .privacy-card {
            max-width: 800px;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            background-color: #fff;
        }
        .brand-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .brand-logo img {
            height: 80px;
        }
        h4, h5 {
            color: #2d3e50;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="privacy-card">
        <div class="brand-logo">
            <img src="{{ asset('MEI_LOGO.png') }}" alt="MEI Logo">
        </div>

        <h4 class="text-center mb-4">MEI Conference App – Privacy Policy</h4>
        <p class="text-muted text-center mb-4"><strong>Effective Date:</strong> 21 July 2025</p>

        <h5>Introduction</h5>
        <p>Welcome to the Malawi Engineering Institute (MEI) Conference App,
            developed for members and non-members of MEI for Malawian residents and users based abroad.
            This Privacy Policy explains how we collect, use, store, and protect your personal information when you use our mobile application ("App").
            By using the MEI Conference App, you agree to the terms of this Privacy Policy.</p>

        <h5>Information We Collect</h5>
        <ul>
            <li><strong>Personal Information:</strong> Full name, member ID (for members), contact details (phone number, email address), organization details, and meal preferences.</li>
            <li><strong>Payment Proof:</strong> Uploaded proof of payment for conference and accommodation bookings (payment is made outside the App).</li>
            <li><strong>Geolocation Data:</strong> Collected at registration to verify venue attendance.</li>
            <li><strong>Clothing Size Information:</strong> For conference gear selection.</li>
            <li><strong>Other Information:</strong> Access to programs, speaker profiles, announcements, digital attendance tags, certificates, and evaluations.</li>
        </ul>

        <h5>How We Use Your Information</h5>
        <ul>
            <li>Facilitate conference and accommodation bookings.</li>
            <li>Process and verify registrations.</li>
            <li>Personalize and manage the conference experience (e.g., meals, gear, digital attendance).</li>
            <li>Communicate updates and announcements.</li>
            <li>Issue attendance certificates and manage post-event feedback.</li>
        </ul>

        <h5>Data Sharing and Disclosure</h5>
        <p>We do not sell, rent, or share your personal information except:</p>
        <ul>
            <li>With authorized MEI personnel and service providers.</li>
            <li>When required by law or legal requests.</li>
            <li>To protect the rights and safety of MEI, its members, and the public.</li>
        </ul>

        <h5>Data Security</h5>
        <p>We use technical and organizational measures to protect your data from unauthorized access, loss, misuse, or disclosure.
            While we strive to secure your data, no method of internet transmission or storage is 100% secure.</p>

        <h5>Your Rights</h5>
        <p>You have the right to:</p>
        <ul>
            <li>Access and update your personal data.</li>
            <li>Request deletion of your personal data (subject to legal/operational obligations).</li>
            <li>Withdraw consent for optional data collection (e.g., geolocation).</li>
        </ul>
        <p>To exercise these rights, contact us at <a href="mailto:mei@mei.org.mw">mei@mei.org.mw</a>.</p>

        <h5>Changes to This Privacy Policy</h5>
        <p>This policy may be updated occasionally. Significant changes will be posted on this page. Please review it periodically.</p>

        <h5>Contact Us</h5>
        <p>If you have questions or concerns, please contact:</p>
        <p>
            <strong>Malawi Engineering Institute (MEI)</strong><br>
            P.O. Box 30228, Lilongwe 3<br>
            Email: <a href="mailto:mei@mei.org.mw">mei@mei.org.mw</a><br>
            Phone: +265-1-789-114
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
