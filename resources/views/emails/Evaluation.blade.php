<!DOCTYPE html>
<html>
<head>
    <title>Evaluation Submitted</title>
</head>
<body style="font-family:Arial,sans-serif;padding:20px;">
    <h2 style="color:#006198;">IIA Malawi</h2>
    <hr>

    <h3>Hello {{ $data['name'] }},</h3>
    <p>Thank you for submitting your evaluation for <strong>{{ $data['event_name'] }}</strong>.</p>
    <p>Your feedback is valuable and helps us improve future events.</p>

    <p>Your certificate is ready for download:</p>
    <p>
        <a href="{{ $data['certUrl'] }}" style="display:inline-block;padding:10px 20px;background-color:#006198;color:#fff;text-decoration:none;border-radius:6px;">
            Download Certificate
        </a>
    </p>

    <hr>
    <p style="color:#999;font-size:12px;">For any inquiries, contact IIA Malawi.</p>
</body>
</html>
