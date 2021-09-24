<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="bumblebee">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Château de Villa - Woocommerce & TCPOS Sync | Tables</title>
    <link rel="icon" href="data:;base64,iVBORw0KGgo=">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1/dist/tailwind.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@1.14.0/dist/full.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

</head>

<body class="">

        <div class="overflow-x-auto shadow-lg">

            <table class="w-full rounded whitespace-no-wrap">
                <thead class="bg-gray-200">
                    <tr>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            tcposId</th>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            tcposCode (UGS)</th>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            Prix</th>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            Image</th>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            Stock</th>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            Règle de stock passé</th>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            À jour ?</th>
                    </tr>
                </thead>
                <tbody class="">
                    @forelse($products as $product)
                    @php
                    @endphp

                    <tr class="font-sm leading-relaxed {{ $product->needToUpdate() ? 'bg-green-100' : '' }}">
                        <td class="px-4">{{ $product->_tcposId }}</td>
                        <td class="px-4">{{ $product->_tcposCode }}</td>
                        <td class="px-4">@foreach($product->pricesRelations as $price) {{ $price->pricelevelid }}:{{ $price->sync_action }} @endforeach</td>
                        <td class="px-4">{{ data_get($product->imageRelation, 'imageUrl') }} {{ $product->imageUrl() }}</td>
                        <td>{{ $product->stock() }}</td>
                        <td>{!! $product->isStockRuleCorrect() ? '<i class="bi bi-check-circle text-green-600"></i>' : '<i class="bi bi-x-circle text-red-600"></i> or not managed' !!}</td>
                        <td>{!! $product->needToUpdate() ? '<i class="bi bi-exclamation-square"></i>' : '<i class="bi bi-check-square"></i>' !!}</td>
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