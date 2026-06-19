<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Update</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <tr>
                    <td align="center" style="padding-bottom: 5px;">
                        <h1 style="color: #006198; font-size: 22px; margin-bottom: 4px;">IIA Malawi</h1>
                        <p style="color: #888888; font-size: 13px; margin-top: 0;">Institute of Internal Auditors Malawi</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0 20px;">
                        <hr style="border: none; border-top: 1px solid #eeeeee;">
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 10px;">
                        <h2 style="color: #333333; font-size: 20px;">
                            @if($booker->booking_status === 'Declined')Booking Declined
                            @elseif($booker->booking_status === 'Cancelled')Booking Cancelled
                            @else Booking Update
                            @endif
                        </h2>
                        <p style="font-size: 15px; color: #555555; line-height: 1.6;">
                            Dear <strong>{{ $booker->name }}</strong>,
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 15px 0;">
                        <table width="100%" cellpadding="12" cellspacing="0" border="0" style="background-color: #f9f9f9; border-radius: 6px;">
                            <tr>
                                <td style="font-size: 14px; color: #333333;">
                                    <strong>Booking Reference:</strong> {{ $booker->booking_reference ?? $booker->bookingID }}
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: 14px; color: #333333;">
                                    <strong>Status:</strong>
                                    <span style="color:{{ $booker->booking_status === 'Declined' ? '#dc3545' : '#6c757d' }};">
                                        {{ $booker->booking_status }}
                                    </span>
                                </td>
                            </tr>
                            @if($booker->admin_note)
                            <tr>
                                <td style="font-size: 14px; color: #333333;">
                                    <strong>Reason:</strong><br>
                                    {{ $booker->admin_note }}
                                </td>
                            </tr>
                            @endif
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 14px; color: #555555; line-height: 1.6;">
                        <p>
                            @if($booker->booking_status === 'Declined')
                                Your booking has been declined. If you believe this is an error, please contact IIA Malawi.
                            @elseif($booker->booking_status === 'Cancelled')
                                Your booking has been cancelled. If you have any questions, please contact IIA Malawi.
                            @endif
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding: 20px 0;">
                        <a href="{{ url('/member-dashboard') }}"
                           style="display: inline-block; padding: 14px 36px; background-color: #006198; color: #ffffff; font-size: 16px; text-decoration: none; border-radius: 5px;">
                            View My Dashboard
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding-top: 20px;">
                        <hr style="border: none; border-top: 1px solid #eeeeee;">
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding-top: 15px; color: #aaaaaa; font-size: 12px;">
                        &copy; {{ date('Y') }} IIA Malawi. All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
