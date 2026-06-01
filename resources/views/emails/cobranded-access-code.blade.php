<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Votre accès à la collecte</title>
</head>
<body style="margin:0;background:#f5f4f4;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    @php
        $company = $collection->company;
        $primary = $company->primaryColor ?: '#575656';
        $secondary = $company->secondaryColor ?: '#f1f7f7';
        $hex = ltrim($primary, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $buttonText = (($r * 299 + $g * 587 + $b * 114) / 1000) > 150 ? '#111827' : '#ffffff';
    @endphp

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f4f4;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e6e5e5;">
                    <tr>
                        <td style="padding:22px 24px;border-top:6px solid {{ $primary }};">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="left" style="vertical-align:middle;">
                                        <img src="{{ url('/img/logo_HUG.png') }}" alt="HUG" style="max-height:42px;max-width:140px;display:block;">
                                    </td>
                                    <td align="right" style="vertical-align:middle;">
                                        @if ($company->logo)
                                            <img src="{{ url($company->logo) }}" alt="{{ $company->name }}" style="max-height:42px;max-width:150px;display:block;">
                                        @else
                                            <span style="font-size:16px;font-weight:700;color:{{ $primary }};">{{ $company->name }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 24px 28px;">
                            <h1 style="margin:0 0 14px;font-size:24px;line-height:1.2;color:#111827;">Votre accès à la collecte {{ $company->name }}</h1>
                            <p style="margin:0 0 18px;font-size:15px;line-height:1.55;color:#404040;">
                                Voici votre mot de passe personnel pour accéder à la page de collecte de votre entreprise.
                            </p>

                            <div style="margin:0 0 22px;padding:16px;border-radius:10px;background:{{ $secondary }};">
                                <p style="margin:0 0 6px;font-size:13px;color:#575656;">Email</p>
                                <p style="margin:0 0 14px;font-size:16px;font-weight:700;color:#111827;">{{ $email }}</p>
                                <p style="margin:0 0 6px;font-size:13px;color:#575656;">Mot de passe</p>
                                <p style="margin:0;font-size:24px;letter-spacing:2px;font-weight:700;color:#111827;">{{ $password }}</p>
                            </div>

                            <p style="margin:0 0 22px;">
                                <a href="{{ $accessUrl }}" style="display:inline-block;background:{{ $primary }};color:{{ $buttonText }};text-decoration:none;font-size:15px;font-weight:700;padding:12px 18px;border-radius:8px;">
                                    Accéder à la collecte
                                </a>
                            </p>

                            <p style="margin:0;font-size:13px;line-height:1.45;color:#737171;">
                                Ne partagez pas ce mot de passe. Si vous redemandez un accès, un nouveau mot de passe remplacera celui-ci.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
