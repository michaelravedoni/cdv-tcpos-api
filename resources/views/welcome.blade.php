@extends('layouts.app')

@section('title', 'Dashboard — Château de Villa Sync')

@section('topbar_actions')
    <button type="button" id="toggle-autoreload" class="btn btn-sm" title="Mettre en pause / reprendre le rechargement automatique">
        <span id="autoreload-dot" class="dot" style="color:var(--success);"></span>
        <span id="autoreload-label">Auto: 60s</span>
    </button>
    <a href="{{ request()->fullUrlWithQuery(['refresh' => 1]) }}" class="btn btn-primary btn-sm">
        Actualiser
    </a>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Tableau de bord</h1>
            <p class="page-subtitle">Supervision de la synchronisation TCPOS ↔ WooCommerce</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('tables', ['tab' => 'orders']) }}" class="btn btn-sm">Voir les commandes</a>
            <a href="{{ route('tables', ['tab' => 'customers']) }}" class="btn btn-sm">Voir les clients ({{ $customersWithCardCount }})</a>
            <a href="{{ route('tables', ['tab' => 'products']) }}" class="btn btn-sm">Voir les produits ({{ $products_count }})</a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-grid">
        <div class="card">
            <div class="kpi-label">TCPOS WOND</div>
            <div class="kpi-value" style="font-size:20px;">
                @if (! $tcposReachable)
                    <span class="badge badge-danger"><span class="dot"></span> Injoignable</span>
                @elseif ($needImportFromTcpos)
                    <span class="badge badge-warning"><span class="dot"></span> Import requis</span>
                @else
                    <span class="badge badge-success"><span class="dot"></span> À jour</span>
                @endif
            </div>
            <div class="kpi-meta">
                <span>MAJ TCPOS : <strong>{{ $lastTcposUpdate }}</strong></span>
            </div>
        </div>

        <div class="card">
            <div class="kpi-label">File de Jobs</div>
            <div class="kpi-value">
                {{ $remainingJobs }}
                <span style="font-size:13px;font-weight:500;color:var(--text-muted);">en attente</span>
            </div>
            <div class="kpi-meta">
                @if ($failedJobs > 0)
                    <span class="badge badge-danger">{{ $failedJobs }} échoué(s)</span>
                @else
                    <span class="badge badge-success">0 échoué</span>
                @endif
                @if ($jobsWorking)
                    <span class="badge badge-info">Worker actif</span>
                @endif
                <span>Dernier : {{ $lastJobDatetime ? $lastJobDatetime->diffForHumans() : '—' }}</span>
            </div>
        </div>

        <div class="card">
            <div class="kpi-label">Produits</div>
            <div class="kpi-value">
                {{ $products_count }}
                <span style="font-size:13px;font-weight:500;color:var(--text-muted);">TCPOS / {{ $wooProductsCount }} Woo</span>
            </div>
            <div class="kpi-meta">
                @if ($productsPendingUpdate > 0)
                    <span class="badge badge-warning">{{ $productsPendingUpdate }} à synchroniser</span>
                @else
                    <span class="badge badge-success">Synchronisés</span>
                @endif
                <span>Stock &lt; 6 : <strong>{{ $products_where_minimal_quantity_under_six }}</strong></span>
            </div>
        </div>

        <div class="card">
            <div class="kpi-label">Clients (avec carte)</div>
            <div class="kpi-value">{{ $customersWithCardCount }}</div>
            <div class="kpi-meta">
                <span>Filtrés sans bots</span>
                <span>·</span>
                <a href="{{ route('tables', ['tab' => 'customers']) }}">Inspecter →</a>
            </div>
        </div>
    </div>

    {{-- Details Row --}}
    <div class="grid-2">
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <h2 class="card-title">Prochaines tâches planifiées</h2>
                <span class="badge badge-neutral">Europe/Zurich</span>
            </div>
            <div class="kv-list">
                <div class="kv-row">
                    <span class="kv-key">Import WooCommerce (<span class="mono">import:woo</span>)</span>
                    <span class="kv-val">{{ $scheduledWoo ? $scheduledWoo->format('d.m H:i') . ' (' . $scheduledWoo->diffForHumans() . ')' : '—' }}</span>
                </div>
                <div class="kv-row">
                    <span class="kv-key">Import TCPOS (<span class="mono">import:tcpos</span>)</span>
                    <span class="kv-val">{{ $scheduledTcpos ? $scheduledTcpos->format('d.m H:i') . ' (' . $scheduledTcpos->diffForHumans() . ')' : '—' }}</span>
                </div>
                <div class="kv-row">
                    <span class="kv-key">Synchro TCPOS → Woo (<span class="mono">sync:tcpos_woo</span>)</span>
                    <span class="kv-val">{{ $scheduledSync ? $scheduledSync->format('d.m H:i') . ' (' . $scheduledSync->diffForHumans() . ')' : '—' }}</span>
                </div>
                <div class="kv-row">
                    <span class="kv-key">Synchro Commandes (<span class="mono">sync:tcpos_woo_order</span>)</span>
                    <span class="kv-val">{{ $scheduledOrders ? $scheduledOrders->format('d.m H:i') . ' (' . $scheduledOrders->diffForHumans() . ')' : '—' }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <h2 class="card-title">Répartition du catalogue</h2>
                <span class="badge badge-neutral">{{ $products_count }} articles</span>
            </div>
            <div class="kv-list" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 24px;">
                <div class="kv-row"><span class="kv-key">Vins</span><span class="kv-val">{{ $count_wine }}</span></div>
                <div class="kv-row"><span class="kv-key">Spiritueux</span><span class="kv-val">{{ $spirit }}</span></div>
                <div class="kv-row"><span class="kv-key">Bières</span><span class="kv-val">{{ $beer }}</span></div>
                <div class="kv-row"><span class="kv-key">Cidres</span><span class="kv-val">{{ $cider }}</span></div>
                <div class="kv-row"><span class="kv-key">Coffrets</span><span class="kv-val">{{ $wineSet }}</span></div>
                <div class="kv-row"><span class="kv-key">Sélections</span><span class="kv-val">{{ $selection }}</span></div>
                <div class="kv-row"><span class="kv-key">Produits régionaux</span><span class="kv-val">{{ $regionalProduct }}</span></div>
                <div class="kv-row"><span class="kv-key">Boissons minérales</span><span class="kv-val">{{ $mineralDrink }}</span></div>
                <div class="kv-row"><span class="kv-key">Livres</span><span class="kv-val">{{ $book }}</span></div>
                <div class="kv-row"><span class="kv-key">Non classés</span><span class="kv-val">{{ $none }}</span></div>
            </div>
        </div>
    </div>

    {{-- Manual Actions --}}
    <div class="card" style="margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
            <h2 class="card-title">Actions manuelles</h2>
            <span style="font-size:12.5px;color:var(--text-muted);">Déclenchement direct des imports et synchronisations</span>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="/api/wc/import/all?force=1" target="_blank" class="btn btn-sm">1. Importer WooCommerce (~2 min)</a>
            <a href="/api/import/all?force=1" target="_blank" class="btn btn-sm">2. Importer TCPOS (~15 min)</a>
            <a href="/api/sync/all?force=1" target="_blank" class="btn btn-sm">3. Synchroniser tout</a>
            <a href="/api/sync/customers" target="_blank" class="btn btn-sm">Synchroniser les clients</a>
            <a href="/api/sync/orders" target="_blank" class="btn btn-sm">Synchroniser les commandes</a>
            <a href="/api/check/woo" target="_blank" class="btn btn-sm">Contrôler WooCommerce</a>
        </div>
    </div>

    {{-- Activity Log --}}
    <div class="card card-flush">
        <div class="card-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <h2 class="card-title">Journal d'activité</h2>
                <span class="badge badge-neutral">{{ $activities->count() }} affichées</span>
            </div>
            <form method="GET" action="{{ route('dashboard') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <select name="resource" class="select" onchange="this.form.submit()">
                    <option value="all" {{ $resourceFilter === 'all' ? 'selected' : '' }}>Toutes ressources</option>
                    <option value="customers" {{ $resourceFilter === 'customers' ? 'selected' : '' }}>Clients</option>
                    <option value="products" {{ $resourceFilter === 'products' ? 'selected' : '' }}>Produits</option>
                    <option value="orders" {{ $resourceFilter === 'orders' ? 'selected' : '' }}>Commandes</option>
                    <option value="attributes" {{ $resourceFilter === 'attributes' ? 'selected' : '' }}>Attributs</option>
                </select>
                <select name="level" class="select" onchange="this.form.submit()">
                    <option value="default" {{ $levelFilter === 'default' ? 'selected' : '' }}>Sans niveau info</option>
                    <option value="errors" {{ $levelFilter === 'errors' ? 'selected' : '' }}>Erreurs &amp; Warnings uniquement</option>
                    <option value="info" {{ $levelFilter === 'info' ? 'selected' : '' }}>Tout (avec info)</option>
                </select>
                <select name="limit" class="select" onchange="this.form.submit()">
                    <option value="50" {{ $activitiesLimit === 50 ? 'selected' : '' }}>50 lignes</option>
                    <option value="100" {{ $activitiesLimit === 100 ? 'selected' : '' }}>100 lignes</option>
                    <option value="250" {{ $activitiesLimit === 250 ? 'selected' : '' }}>250 lignes</option>
                </select>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Groupe</th>
                        <th>Ressource</th>
                        <th>Niveau</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        @php
                            $level = data_get($activity->properties, 'level', 'info');
                            $badgeClass = match ($level) {
                                'error' => 'badge-danger',
                                'warning' => 'badge-warning',
                                'start', 'end', 'job' => 'badge-info',
                                default => 'badge-neutral',
                            };
                        @endphp
                        <tr>
                            <td class="mono" style="white-space:nowrap;color:var(--text-muted);">
                                {{ $activity->created_at->timezone('Europe/Zurich')->format('d.m.Y H:i:s') }}
                            </td>
                            <td>{{ data_get($activity->properties, 'group', '—') }}</td>
                            <td>{{ data_get($activity->properties, 'resource', '—') }}</td>
                            <td><span class="badge {{ $badgeClass }}">{{ $level }}</span></td>
                            <td>{{ $activity->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center;padding:32px;color:var(--text-muted);">
                                Aucune activité pour ces filtres.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
<script>
(function () {
    const STORAGE_KEY = 'cdv_dashboard_autoreload_paused';
    const label = document.getElementById('autoreload-label');
    const dot = document.getElementById('autoreload-dot');
    const btn = document.getElementById('toggle-autoreload');

    let paused = localStorage.getItem(STORAGE_KEY) === '1';
    let remaining = 60;

    function render() {
        if (paused) {
            label.textContent = 'Auto: Pause';
            dot.style.color = 'var(--text-muted)';
        } else {
            label.textContent = 'Auto: ' + remaining + 's';
            dot.style.color = 'var(--success)';
        }
    }

    btn.addEventListener('click', function () {
        paused = !paused;
        localStorage.setItem(STORAGE_KEY, paused ? '1' : '0');
        if (!paused) remaining = 60;
        render();
    });

    render();
    setInterval(function () {
        if (paused) return;
        remaining -= 1;
        if (remaining <= 0) {
            const url = new URL(window.location.href);
            url.searchParams.delete('refresh');
            window.location.href = url.toString();
            return;
        }
        render();
    }, 1000);
})();
</script>
@endsection
