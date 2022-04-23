<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="bumblebee">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Château de Villa - Woocommerce & TCPOS Sync | Tables</title>
    <link rel="icon" href="data:;base64,iVBORw0KGgo=">
    <meta name="robots" content="noindex, nofollow">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1/dist/tailwind.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@1.14.0/dist/full.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

</head>

<body class="">
    <div class="overflow-x-auto shadow-lg">
        <h2 class="ml-4 my-4 text-xl font-bold text-neutral">5 dernières commandes</h2>
        <table class="w-full rounded whitespace-no-wrap">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase">
                        Woo ID</th>
                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase">
                        Status</th>
                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase">
                    {{ config('cdv.wc_meta_tcpos_order_id') }}</th>
                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase">
                    Date</th>
                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase">
                        À jour ?</th>
                </tr>
            </thead>
            <tbody class="">
                @forelse($orders as $order)
                @php
                $tcposOrderId = AppHelper::getMetadataValueFromKey($order->meta_data, config('cdv.wc_meta_tcpos_order_id'));
                @endphp
                <tr class="font-sm leading-relaxed">
                    <td class="px-4"><a class="link link-accent" href="https://chateaudevilla.ch/wp-admin/post.php?post={{ $order->id }}&action=edit" target="_blank">{{ $order->id }}</a></td>
                    <td class="px-4">{{ $order->status }}</td>
                    <td class="px-4">{{ $tcposOrderId }}</td>
                    <td class="px-4">{{ \Carbon\Carbon::parse($order->date_created)->locale('fr_ch')->isoFormat('L LT') }}</td>
                    <td>
                        {!! $tcposOrderId ? '<i class="bi bi-check-square text-green-600"></i>' : '<i class="bi bi-exclamation-square text-red-600"></i>' !!}
                        @if ( empty($tcposOrderId) && $order->status != 'completed' && \Carbon\Carbon::parse($order->date_created) < \Carbon\Carbon::now()->addMinutes(60) )
                        <i class="bi bi-exclamation-square text-red-600"></i> problème de synchronisation détecté
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="100" class="">
                        <div class="my-6">
                            <div class="text-center">
                                <div class="text-gray-500 text-lg">
                                    No Order
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <h2 class="ml-4 my-4 text-xl font-bold text-neutral">Tous les produits</h2>
        <table class="w-full rounded whitespace-no-wrap">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase">
                        tcposId</th>
                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase">
                        tcposCode (UGS)</th>
                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase">
                        Categorie</th>
                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase">
                        Prix</th>
                    <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase">
                        Image</th>
                    <th class="py-3 font-medium text-left text-xs text-gray-600 uppercase">
                        Stock</th>
                    <th class="py-3 font-medium text-left text-xs text-gray-600 uppercase">
                        Règle de stock passé</th>
                    <th class="py-3 font-medium text-left text-xs text-gray-600 uppercase">
                        À jour ?</th>
                    <th class="py-3 font-medium text-left text-xs text-gray-600 uppercase">
                        Actions</th>
                </tr>
            </thead>
            <tbody class="">
                @forelse($products as $product)
                <tr class="font-sm leading-relaxed {{ $product->needToUpdate() ? 'bg-green-100' : '' }}">
                    <td class="px-4">{{ $product->_tcposId }}</td>
                    <td class="px-4"><a class="link link-accent" target="_blank" href="https://chateaudevilla.ch/wp-admin/edit.php?s={{ $product->_tcposCode }}&post_status=all&post_type=product">{{ $product->_tcposCode }}</a></td>
                    <td class="px-4">{{ $product->category }}</td>
                    <td class="px-4">@foreach($product->pricesRelations as $price)
                        {{ config('cdv.tcpos_price_level_id')[$price->pricelevelid]['name'] }} {!! $price->sync_action
                        == 'update' ? '<i class="bi bi-exclamation-square"></i>' : '<i class="bi bi-check-square"></i>'
                        !!} @endforeach</td>
                    <td class="px-4">@isset($product->imageRelation->hash)<a class="link link-accent" target="_blank"
                            href="{{ $product->imageUrl() }}">URL</a>@else No image @endif {!! data_get($product->imageRelation, 'sync_action') ==
                        'update' ? '<i class="bi bi-exclamation-square"></i>' : '<i class="bi bi-check-square"></i>' !!}
                    </td>
                    <td>{{ $product->stock() }} {!! data_get($product->stockRelation, 'sync_action')
                        == 'update' ? '<i class="bi bi-exclamation-square"></i>' : '<i class="bi bi-check-square"></i>'
                        !!}</td>
                    <td>@if($product->isStockManaged())@if($product->isStockRuleCorrect())<i class="bi bi-check-circle text-green-600"></i>@else<i
                            class="bi bi-x-circle text-red-600"></i>@endif @else not managed @endif</td>
                    <td>{!! $product->needToUpdate() ? '<i class="bi bi-exclamation-square"></i>' : '<i
                            class="bi bi-check-square"></i>' !!}</td>
                    <td>
                        <form method="POST" action="{{ route('tables.products.force.update', ['id' => $product->id]) }}">
                            <input type="hidden" name="id" value="{{ $product->id }}">
                            {!! csrf_field() !!}
                            <button type="submit" class="link-neutral" title="Forcer la mise à jour du produit"><i class="bi bi-arrow-repeat"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="100" class="">
                        <div class="my-6">
                            <div class="text-center">
                                <div class="text-gray-500 text-lg">
                                    No products
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>
