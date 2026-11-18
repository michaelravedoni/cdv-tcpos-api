@extends('layouts.app')

@section('title', 'Tables & Recherche — Château de Villa Sync')

@section('topbar_actions')
    <a href="{{ request()->fullUrlWithQuery(['refresh' => 1]) }}" class="btn btn-primary btn-sm">
        Actualiser
    </a>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Tables &amp; Recherche</h1>
            <p class="page-subtitle">Inspection des commandes WooCommerce, du catalogue produits et des membres avec carte</p>
        </div>
    </div>

    <div class="tabs">
        <a href="{{ route('tables', ['tab' => 'orders']) }}" class="tab {{ $tab === 'orders' ? 'active' : '' }}">
            Commandes WooCommerce
        </a>
        <a href="{{ route('tables', ['tab' => 'products']) }}" class="tab {{ $tab === 'products' ? 'active' : '' }}">
            Produits ({{ $productsCount }})
        </a>
        <a href="{{ route('tables', ['tab' => 'customers']) }}" class="tab {{ $tab === 'customers' ? 'active' : '' }}">
            Clients avec carte ({{ $customersCount }})
        </a>
    </div>

    {{-- TAB: ORDERS --}}
    @if ($tab === 'orders')
        @if ($ordersError)
            <div class="alert alert-danger">
                <strong>Erreur WooCommerce :</strong> {{ $ordersError }}
            </div>
        @endif

        <div class="card card-flush">
            <div class="card-header">
                <h2 class="card-title">Commandes WooCommerce (cache 10s)</h2>
                <form method="GET" action="{{ route('tables') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                    <input type="hidden" name="tab" value="orders">
                    <input type="text" name="order_q" value="{{ $orderQuery }}" class="input" placeholder="N° commande, nom, email..." style="min-width:240px;">
                    <button type="submit" class="btn btn-primary btn-sm">Rechercher</button>
                    @if ($orderQuery !== '')
                        <a href="{{ route('tables', ['tab' => 'orders']) }}" class="btn btn-sm">Réinitialiser</a>
                    @endif
                </form>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Woo ID</th>
                            <th>Client</th>
                            <th>Total</th>
                            <th>Statut Woo</th>
                            <th>TCPOS Order ID</th>
                            <th>Date</th>
                            <th>Synchro</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            @php
                                $tcposOrderId = App\Utilities\AppHelper::getMetadataValueFromKey($order->meta_data ?? [], config('cdv.wc_meta_tcpos_order_id'));
                                $customerName = trim(data_get($order, 'billing.first_name') . ' ' . data_get($order, 'billing.last_name'));
                                $isProblem = empty($tcposOrderId) && in_array($order->status, ['processing', 'on-hold']) && \Carbon\Carbon::parse($order->date_created) < \Carbon\Carbon::now()->addMinutes(60);
                            @endphp
                            <tr>
                                <td class="mono">
                                    <a href="{{ $wooStoreUrl }}/wp-admin/post.php?post={{ $order->id }}&amp;action=edit" target="_blank" rel="noopener">
                                        #{{ $order->id }}
                                    </a>
                                </td>
                                <td>
                                    <div><strong>{{ $customerName !== '' ? $customerName : 'Invité' }}</strong></div>
                                    <div style="font-size:12px;color:var(--text-muted);">{{ data_get($order, 'billing.email') }}</div>
                                </td>
                                <td class="mono">{{ number_format((float) ($order->total ?? 0), 2) }} {{ $order->currency ?? 'CHF' }}</td>
                                <td><span class="badge badge-neutral">{{ $order->status }}</span></td>
                                <td class="mono">{{ $tcposOrderId ?: '—' }}</td>
                                <td class="mono" style="color:var(--text-muted);white-space:nowrap;">
                                    {{ \Carbon\Carbon::parse($order->date_created)->locale('fr_ch')->isoFormat('L LT') }}
                                </td>
                                <td>
                                    @if ($tcposOrderId)
                                        <span class="badge badge-success">Synchronisée</span>
                                    @elseif ($isProblem)
                                        <span class="badge badge-danger">Problème synchro</span>
                                    @else
                                        <span class="badge badge-warning">Non transmise</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center;padding:32px;color:var(--text-muted);">
                                    Aucune commande trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                <span>Page {{ $orderPage }} ({{ $orders->count() }} affichées)</span>
                <div class="pagination-links">
                    @if ($orderPage > 1)
                        <a href="{{ route('tables', ['tab' => 'orders', 'order_q' => $orderQuery, 'order_page' => $orderPage - 1]) }}" class="btn btn-sm">← Précédent</a>
                    @endif
                    @if ($orders->count() >= $orderPerPage)
                        <a href="{{ route('tables', ['tab' => 'orders', 'order_q' => $orderQuery, 'order_page' => $orderPage + 1]) }}" class="btn btn-sm">Suivant →</a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- TAB: PRODUCTS --}}
    @if ($tab === 'products' && $products)
        <div class="card card-flush">
            <div class="card-header">
                <h2 class="card-title">Catalogue Produits ({{ $products->total() }} résultats)</h2>
                <form method="GET" action="{{ route('tables') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                    <input type="hidden" name="tab" value="products">
                    <input type="text" name="q" value="{{ $productQuery }}" class="input" placeholder="Nom, SKU, ID TCPOS..." style="min-width:220px;">
                    <select name="category" class="select">
                        <option value="all" {{ $productCategory === 'all' ? 'selected' : '' }}>Toutes catégories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" {{ $productCategory === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                        <option value="none" {{ $productCategory === 'none' ? 'selected' : '' }}>none</option>
                    </select>
                    <select name="sync_action" class="select">
                        <option value="all" {{ $productSync === 'all' ? 'selected' : '' }}>Tous états</option>
                        <option value="update" {{ $productSync === 'update' ? 'selected' : '' }}>À mettre à jour</option>
                        <option value="none" {{ $productSync === 'none' ? 'selected' : '' }}>Synchronisé</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
                    @if ($productQuery !== '' || $productCategory !== 'all' || $productSync !== 'all')
                        <a href="{{ route('tables', ['tab' => 'products']) }}" class="btn btn-sm">Réinitialiser</a>
                    @endif
                </form>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Code (SKU)</th>
                            <th>ID TCPOS</th>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th>Stock</th>
                            <th>État synchro</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td class="mono"><strong>{{ $product->_tcposCode }}</strong></td>
                                <td class="mono" style="color:var(--text-muted);">{{ $product->_tcposId }}</td>
                                <td>{{ $product->name }}</td>
                                <td><span class="badge badge-neutral">{{ $product->category }}</span></td>
                                <td class="mono">{{ $product->stock() ?? '—' }}</td>
                                <td>
                                    @if ($product->sync_action === 'update')
                                        <span class="badge badge-warning">update</span>
                                    @else
                                        <span class="badge badge-success">{{ $product->sync_action ?? 'none' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('tables.products.force.update', $product->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm">Forcer MAJ</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center;padding:32px;color:var(--text-muted);">
                                    Aucun produit trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                <span>Affichage {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} sur {{ $products->total() }}</span>
                <div class="pagination-links">
                    @if (! $products->onFirstPage())
                        <a href="{{ $products->previousPageUrl() }}" class="btn btn-sm">← Précédent</a>
                    @endif
                    <span>Page {{ $products->currentPage() }} / {{ $products->lastPage() }}</span>
                    @if ($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" class="btn btn-sm">Suivant →</a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- TAB: CUSTOMERS --}}
    @if ($tab === 'customers' && $customers)
        <div class="card card-flush">
            <div class="card-header">
                <h2 class="card-title">Clients WooCommerce avec carte ({{ $customers->total() }} résultats)</h2>
                <form method="GET" action="{{ route('tables') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                    <input type="hidden" name="tab" value="customers">
                    <input type="text" name="customer_q" value="{{ $customerQuery }}" class="input" placeholder="Nom, email, n° carte, Woo ID..." style="min-width:240px;">
                    <button type="submit" class="btn btn-primary btn-sm">Rechercher</button>
                    @if ($customerQuery !== '')
                        <a href="{{ route('tables', ['tab' => 'customers']) }}" class="btn btn-sm">Réinitialiser</a>
                    @endif
                </form>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Woo ID</th>
                            <th>Client</th>
                            <th>Email</th>
                            <th>N° Carte</th>
                            <th>Type</th>
                            <th>Solde (account_funds)</th>
                            <th>Dernier import</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customerRow)
                            @php
                                $c = $customerRow->data;
                                $name = trim(data_get($c, 'first_name') . ' ' . data_get($c, 'last_name'));
                                $accountType = data_get(collect(data_get($c, 'meta_data', []))->firstWhere('key', config('cdv.wc_meta_customer_account_type')), 'value', '—');
                            @endphp
                            <tr>
                                <td class="mono">
                                    <a href="{{ $wooStoreUrl }}/wp-admin/user-edit.php?user_id={{ $customerRow->_wooId }}" target="_blank" rel="noopener">
                                        #{{ $customerRow->_wooId }}
                                    </a>
                                </td>
                                <td><strong>{{ $name !== '' ? $name : data_get($c, 'username', '—') }}</strong></td>
                                <td>{{ data_get($c, 'email', '—') }}</td>
                                <td class="mono"><strong>{{ data_get($c, 'card_number', '—') }}</strong></td>
                                <td>
                                    <span class="badge {{ $accountType === 'chatelin' ? 'badge-info' : 'badge-neutral' }}">
                                        {{ $accountType }}
                                    </span>
                                </td>
                                <td class="mono"><strong>{{ number_format((float) data_get($c, 'account_funds', 0), 2) }} CHF</strong></td>
                                <td class="mono" style="color:var(--text-muted);white-space:nowrap;">
                                    {{ $customerRow->updated_at?->timezone('Europe/Zurich')->format('d.m.Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center;padding:32px;color:var(--text-muted);">
                                    Aucun client trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                <span>Affichage {{ $customers->firstItem() ?? 0 }}–{{ $customers->lastItem() ?? 0 }} sur {{ $customers->total() }}</span>
                <div class="pagination-links">
                    @if (! $customers->onFirstPage())
                        <a href="{{ $customers->previousPageUrl() }}" class="btn btn-sm">← Précédent</a>
                    @endif
                    <span>Page {{ $customers->currentPage() }} / {{ $customers->lastPage() }}</span>
                    @if ($customers->hasMorePages())
                        <a href="{{ $customers->nextPageUrl() }}" class="btn btn-sm">Suivant →</a>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endsection
