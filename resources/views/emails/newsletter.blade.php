<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $subjectLine ?? 'Newsletter' }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4; font-family: Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4; padding:20px 0;">
    <tr>
        <td align="center">

            <!-- Main Container -->
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:10px; overflow:hidden;">

                <!-- 🔹 HEADER -->
                <tr>
                    <td style="background:#0f766e; padding:20px; text-align:center;">
                        <img src="{{ $settings['logo'] ?? url('/default-logo.png') }}" 
                             alt="Logo" 
                             style="max-height:50px; margin-bottom:10px;">
                        <h2 style="color:#ffffff; margin:0;">
                            {{ $settings['site_name'] ?? 'The Nature One' }}
                        </h2>
                    </td>
                </tr>

                <!-- 🔹 HERO IMAGE -->
                @if(!empty($settings['newsletter_banner']))
                <tr>
                    <td>
                        <img src="{{ $settings['newsletter_banner'] }}" 
                             alt="Banner" 
                             width="100%" 
                             style="display:block;">
                    </td>
                </tr>
                @endif

                <!-- 🔹 CONTENT -->
                <tr>
                    <td style="padding:30px; color:#333333; font-size:14px; line-height:1.6;">
                        {!! $content !!}
                    </td>
                </tr>

                <!-- 🔹 CTA BUTTON -->
                <tr>
                    <td align="center" style="padding-bottom:30px;">
                        <a href="{{ $settings['website_url'] ?? url('/') }}" 
                           style="background:#0f766e; color:#ffffff; padding:12px 25px; text-decoration:none; border-radius:5px; display:inline-block;">
                            Visit Website
                        </a>
                    </td>
                </tr>

                <!-- 🔹 FOOTER -->
                <tr>
                    <td style="background:#f1f1f1; padding:20px; text-align:center; font-size:12px; color:#777;">
                        
                        <!-- Social Icons -->
                        <div style="margin-bottom:10px;">
                            @if(!empty($settings['facebook']))
                                <a href="{{ $settings['facebook'] }}" style="margin:0 5px;">
                                    <img src="https://cdn-icons-png.flaticon.com/24/733/733547.png" />
                                </a>
                            @endif

                            @if(!empty($settings['instagram']))
                                <a href="{{ $settings['instagram'] }}" style="margin:0 5px;">
                                    <img src="https://cdn-icons-png.flaticon.com/24/733/733558.png" />
                                </a>
                            @endif

                            @if(!empty($settings['twitter']))
                                <a href="{{ $settings['twitter'] }}" style="margin:0 5px;">
                                    <img src="https://cdn-icons-png.flaticon.com/24/733/733579.png" />
                                </a>
                            @endif
                        </div>

                        <!-- Copyright -->
                        <p style="margin:5px 0;">
                            © {{ date('Y') }} {{ $settings['site_name'] ?? 'The Nature One' }}. All rights reserved.
                        </p>

                        <!-- Address -->
                        <p style="margin:5px 0;">
                            {{ $settings['address'] ?? 'India' }}
                        </p>

                        <!-- Unsubscribe -->
                        <p style="margin-top:10px;">
                            <a href="{{ url('/api/unsubscribe?email=' . urlencode($email)) }}" style="color:#0f766e;">
                                Unsubscribe
                            </a>
                        </p>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>