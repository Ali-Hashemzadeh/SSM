<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP Code</title>
    <style>
        /* Basic email resets */
        body, table, td, div, p, h1 {
            font-family: Arial, sans-serif;
            font-size: 16px;
            color: #333;
        }
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }
        /* Main styles */
        .container {
            padding: 20px;
            background-color: #f4f4f7; /* Light grey background */
        }
        .content-card {
            width: 90%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff; /* White card */
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }
        .header {
            padding: 30px 40px;
            text-align: center;
            /* Using a professional blue color. You can change this. */
            background-color: #004a99;
        }
        .header h1 {
            color: #ffffff; /* White title text */
            font-size: 24px;
            margin: 0;
        }
        .body-content {
            padding: 40px;
            line-height: 1.6;
            text-align: left;
        }
        .otp-panel {
            /* This is the panel for the code, meeting supervisor's Rule 6 */
            background-color: #f4f4f7;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .otp-code {
            /* This makes the code itself stand out */
            font-size: 32px;
            font-weight: bold;
            color: #004a99; /* Same as header color */
            letter-spacing: 4px;
        }
        .footer {
            padding: 30px 40px;
            text-align: center;
            font-size: 12px;
            color: #888;
            background-color: #fafafa;
        }
    </style>
</head>
<body style="margin: 0; padding: 0;">
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td class="container" style="padding: 20px; background-color: #f4f4f7;">
            <!-- Main Content Card -->
            <table role="presentation" class="content-card" align="center" border="0" cellpadding="0" cellspacing="0" style="width: 90%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0;">

                <!-- Header -->
                <tr>
                    <td class="header" style="padding: 30px 40px; text-align: center; background-color: #004a99;">
                        <h1 style="font-family: Arial, sans-serif; color: #ffffff; font-size: 24px; margin: 0;">
                            Your One-Time Password
                        </h1>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td class="body-content" style="padding: 40px; line-height: 1.6; text-align: left;">
                        <p style="font-family: Arial, sans-serif; font-size: 16px; color: #333;">Hello,</p>
                        <p style="font-family: Arial, sans-serif; font-size: 16px; color: #333;">
                            Here is your one-time password (OTP) to log in. This code is valid for 10 minutes.
                        </p>

                        <!-- OTP Panel -->
                        <div class="otp-panel" style="background-color: #f4f4f7; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0;">
                            <div class="otp-code" style="font-family: Arial, sans-serif; font-size: 32px; font-weight: bold; color: #004a99; letter-spacing: 4px;">
                                {{ $otpCode }}
                            </div>
                        </div>

                        <p style="font-family: Arial, sans-serif; font-size: 16px; color: #333;">
                            Please do not share this code with anyone.
                        </p>
                        <p style="font-family: Arial, sans-serif; font-size: 16px; color: #333;">
                            If you did not request this, you can safely ignore this email.
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td class="footer" style="padding: 30px 40px; text-align: center; font-size: 12px; color: #888; background-color: #fafafa;">
                        <p style="font-family: Arial, sans-serif; font-size: 12px; color: #888; margin: 0;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

