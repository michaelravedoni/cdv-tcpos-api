<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
</head>
<body style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;color:#0f172a;background:#f8fafc;margin:0;padding:24px;">
    <div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
        <h2 style="margin:0 0 12px;color:#7c2d12;font-size:18px;">Problème de synchronisation des commandes</h2>
        <p style="margin:0 0 12px;">Bonjour,</p>
        <p style="margin:0 0 16px;">
            Un problème de synchronisation a été détecté pour une ou plusieurs commandes entre WooCommerce et TCPOS sur
            <a href="{{ config('app.url') }}" style="color:#7c2d12;">{{ config('app.name') }}</a>
            (commandes en statut <em>processing</em> ou <em>on-hold</em> depuis plus de 60 minutes sans identifiant TCPOS).
        </p>

        @if (! empty($orders))
            @php
                $wooBaseUrl = rtrim((string) config('woocommerce.store_url', 'https://shop.chateaudevilla.ch'), '/');
                $appUrl = rtrim((string) config('app.url'), '/');
            @endphp
            <table style="width:100%;border-collapse:collapse;font-size:13px;margin:16px 0;">
                <thead>
                    <tr style="background:#f1f5f9;text-align:left;">
                        <th style="padding:8px 10px;border:1px solid #e2e8f0;">Commande</th>
                        <th style="padding:8px 10px;border:1px solid #e2e8f0;">Client</th>
                        <th style="padding:8px 10px;border:1px solid #e2e8f0;">Montant</th>
                        <th style="padding:8px 10px;border:1px solid #e2e8f0;">Statut</th>
                        <th style="padding:8px 10px;border:1px solid #e2e8f0;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td style="padding:8px 10px;border:1px solid #e2e8f0;">
                                <a href="{{ $wooBaseUrl }}/wp-admin/post.php?post={{ $order['id'] }}&amp;action=edit" style="color:#7c2d12;font-weight:600;">
                                    #{{ $order['id'] }}
                                </a>
                            </td>
                            <td style="padding:8px 10px;border:1px solid #e2e8f0;">
                                {{ $order['customer'] ?: 'Invité' }}
                                @if (! empty($order['email']))
                                    <br><span style="color:#64748b;font-size:12px;">{{ $order['email'] }}</span>
                                @endif
                            </td>
                            <td style="padding:8px 10px;border:1px solid #e2e8f0;">{{ $order['total'] }}</td>
                            <td style="padding:8px 10px;border:1px solid #e2e8f0;">{{ $order['status'] }}</td>
                            <td style="padding:8px 10px;border:1px solid #e2e8f0;">{{ $order['date'] }}</td>
                        </tr>
                        @if (! empty($order['error']))
                            <tr>
                                <td colspan="5" style="padding:6px 10px;border:1px solid #e2e8f0;background:#fef2f2;color:#b91c1c;font-size:12px;">
                                    <strong>Dernier log :</strong> {{ $order['error'] }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>

            <p style="margin:16px 0 8px;font-size:13px;">
                <strong>Liens rapides pour vérifier :</strong><br>
                • <a href="{{ $appUrl }}/tables?tab=orders" style="color:#7c2d12;">Voir les commandes dans le dashboard</a><br>
                • <a href="{{ $appUrl }}/jobs" style="color:#7c2d12;">Vérifier la file de jobs</a><br>
                • <a href="{{ $appUrl }}/api/sync/orders" style="color:#7c2d12;">Relancer manuellement la synchro des commandes</a>
            </p>
        @endif

        <p style="margin:16px 0 0;color:#64748b;font-size:12px;">Ceci est un email automatique envoyé toutes les 4 heures maximum tant que le problème persiste.</p>
    </div>
</body>
</html>
