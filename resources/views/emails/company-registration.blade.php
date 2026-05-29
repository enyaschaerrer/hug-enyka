<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #FAF8F2; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center" style="padding: 40px 16px;">
                
                <table role="presentation" width="100%" style="max-width: 600px; width: 100%;" cellspacing="0" cellpadding="0" border="0">
                    
                    <tr>
                        <td style="padding-bottom: 24px; text-align: center;">
                            <span style="color: #1c1917; font-size: 22px; font-weight: 900; letter-spacing: -0.05em;">Coeur d'Honneur</span>
                        </td>
                    </tr>

                    <tr style="background-color: #ffffff; border-radius: 24px;">
                        <td style="padding: 40px; border-radius: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                            
                            <h2 style="margin: 0 0 12px 0; color: #1c1917; font-size: 24px; font-weight: 800; line-height: 1.2;">
                                Merci pour votre {{ isset($form->address) ? 'demande' : 'candidature' }}, {{ $form->name }} !
                            </h2>
                            
                            <p style="margin: 0 0 32px 0; color: #78716c; font-size: 16px; line-height: 1.6;">
                                Nous avons bien reçu vos informations. Notre équipe va examiner votre dossier avec attention et nous reviendrons vers vous dans les plus brefs délais.
                            </p>

                            <div style="padding-top: 20px; border-top: 1px solid #f5f5f4;">
                                <h3 style="margin-top: 0; margin-bottom: 20px; color: #1c1917; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Récapitulatif de votre envoi
                                </h3>

                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                    <tr>
                                        <td style="padding-bottom: 16px;">
                                            <label style="display: block; font-size: 11px; font-weight: 700; color: #a8a29e; text-transform: uppercase;">Votre e-mail de contact</label>
                                            <div style="font-size: 15px; color: #1c1917;">{{ $form->email }}</div>
                                        </td>
                                    </tr>

                                    @if(!empty($form->phone))
                                    <tr>
                                        <td style="padding-bottom: 16px;">
                                            <label style="display: block; font-size: 11px; font-weight: 700; color: #a8a29e; text-transform: uppercase;">Téléphone</label>
                                            <div style="font-size: 15px; color: #1c1917;">{{ $form->phone }}</div>
                                        </td>
                                    </tr>
                                    @endif

                                    @if(!empty($form->address))
                                    <tr>
                                        <td style="padding-bottom: 16px;">
                                            <label style="display: block; font-size: 11px; font-weight: 700; color: #a8a29e; text-transform: uppercase;">Adresse</label>
                                            <div style="font-size: 15px; color: #1c1917;">{{ $form->address }}</div>
                                        </td>
                                    </tr>
                                    @endif

                                    @if(isset($form->trophy))
                                    <tr>
                                        <td style="padding-bottom: 16px;">
                                            <label style="display: block; font-size: 11px; font-weight: 700; color: #a8a29e; text-transform: uppercase;">Prix du Cœur</label>
                                            <div style="font-size: 14px; color: #f43f5e; font-weight: 600;">
                                                {{ $form->trophy ? 'Inscrit' : 'Collecte simple uniquement' }}
                                            </div>
                                        </td>
                                    </tr>
                                    @endif

                                    @if(!empty($form->message))
                                    <tr>
                                        <td style="padding-top: 8px;">
                                            <label style="display: block; margin-bottom: 8px; font-size: 11px; font-weight: 700; color: #a8a29e; text-transform: uppercase;">Votre message</label>
                                            <div style="background-color: #fafaf9; border: 1px solid #e7e5e4; border-radius: 12px; padding: 16px; font-size: 14px; line-height: 1.6; color: #44403c; font-style: italic;">
                                                "{{ $form->message }}"
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>

                            <div style="margin-top: 40px; text-align: center;">
                                <a href="https://coeurdhonneur.ch" style="background-color: #f43f5e; color: #ffffff; padding: 14px 28px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 14px; display: inline-block;">
                                    Retourner sur le site
                                </a>
                            </div>

                        </td>
                    </tr>

                    <tr>
                        <td style="padding-top: 32px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #a8a29e; line-height: 1.5;">
                                Vous recevez cet e-mail car vous avez rempli un formulaire sur notre plateforme.<br>
                                <strong>Cœur d'Honneur</strong>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>