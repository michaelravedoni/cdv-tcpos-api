<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Château de Villa - tcpos Woocommerce sync</title>

    <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">

</head>

<body class="font-sans p-6 pb-64 bg-gray-100">

    <h1 class="text-5xl text-blue-900 font-bold">
        Dashboard
        <small class="text-xl">Château de Villa</small>
    </h1>
    <div class="mb-6">Woocommerce & TCPOS Sync</div>

    <div class="px-6 py-4 mb-6 pl-4 bg-white rounded-md shadow-md">

        <h2 class="mb-4 text-2xl font-bold text-blue-900">
            Informations
        </h2>
        <div class="flex flex-wrap">
            <div class="w-1/3">
                <h3 class="mb-1 font-bold text-blue-900">Produits</h3>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Produits dans la base de données</div>
                    <div class="w-1/3">{{ $products_count }}</div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Produits dont la quantité d'inventaire est < 6</div> <div class="w-1/3">
                            {{ $products_where_minimal_quantity_under_six }}</div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Produits dont la quantité d'inventaire est > 6</div>
                    <div class="w-1/3">{{ $products_where_minimal_quantity_below_equal_six }}</div>
                </div>
            </div>
            <div class="w-1/3">
                <div class="flex flex-wrap">
                    <div class="w-2/3">Vins</div>
                    <div class="w-1/3">{{ $count_wine }}</div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Spiritueux</div>
                    <div class="w-1/3">{{ $spirit }}</div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Cidres</div>
                    <div class="w-1/3">{{ $cider }}</div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Service du vin</div>
                    <div class="w-1/3">{{ $wineSet }}</div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Minéraux</div>
                    <div class="w-1/3">{{ $mineralDrink }}</div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Bières</div>
                    <div class="w-1/3">{{ $beer }}</div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Livres</div>
                    <div class="w-1/3">{{ $book }}</div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Sélections</div>
                    <div class="w-1/3">{{ $selection }}</div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Sans catégorie</div>
                    <div class="w-1/3">{{ $none }}</div>
                </div>
            </div>
            <div class="w-1/3">
                <h3 class="mb-1 font-bold text-blue-900">Synchronisation et importations</h3>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Dernière tâche d'arrière-fond</div>
                    <div class="w-1/3">{{ $lastJob->started_at->locale('fr_ch')->timezone('Europe/Zurich')->isoFormat('L LT') }}</div>
                </div>
                <!--
                <div class="flex flex-wrap">
                    <div class="w-2/3">Dernière synchronisation entre TCPOS et Woocommerce</div>
                    <div class="w-1/3">?</div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Dernière importation de TCPOS</div>
                    <div class="w-1/3">?</div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Dernière importation de Woocommerce</div>
                    <div class="w-1/3">?</div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Dernière remontée de commande</div>
                    <div class="w-1/3">?</div>
                </div>
                -->
                <div class="flex flex-wrap my-4">@if($jobsWorking)<a
                        class="bg-red-800 hover:bg-red-700 text-white py-1 px-3 rounded" href="/jobs">Tâches
                        d'arrière-fond en cours d'exécution</a>@endif</div>
                <div>
                    <div class="my-4">
                        <a class="bg-blue-800 hover:bg-blue-700 text-white py-1 px-3 rounded" href="/jobs">Voir les
                            Jobs</a>
                        <a class="bg-blue-800 hover:bg-blue-700 text-white py-1 px-3 rounded" href="/logs">Voir les
                            Logs</a>
                    </div>
                    <div class="my-4">
                        <a class="bg-purple-800 hover:bg-purple-700 text-white py-1 px-3 rounded"
                            href="/api/import/all">1. Tout importer de TCPOS</a>
                        <div class="my-4">
                        </div>
                        <a class="bg-purple-800 hover:bg-purple-700 text-white py-1 px-3 rounded"
                            href="/api/wc/import/all">2. Tout importer de Woocommerce</a>
                        <div class="my-4">
                        </div>
                        <a class="bg-purple-800 hover:bg-purple-700 text-white py-1 px-3 rounded"
                            href="/api/wc/sync/all">3. Synchroniser</a>
                    </div>
                </div>
                </div>
        </div>
    </div>

    <div class="px-6 py-4 mb-6 pl-4 bg-white rounded-md shadow-md">

        <h2 class="mb-4 text-2xl font-bold text-blue-900">
            Logs
        </h2>

        <div class="overflow-x-auto shadow-lg">

            <table class="w-full rounded whitespace-no-wrap">

                <thead class="bg-gray-200">

                    <tr>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            Message</th>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            Details</th>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            Duration</th>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            Started</th>
                    </tr>

                </thead>

                <tbody class="bg-white">

                    @forelse($activities as $activity)

                    <tr class="font-sm leading-relaxed">
                        <td class="px-4">{{ $activity->description }}</td>
                        <td>{{ $activity->getExtraProperty('customProperty') }}</td>
                        <td>{{ $activity->getExtraProperty('duration') ? number_format($activity->getExtraProperty('duration'), 2).' secondes' : null }}</td>
                        <td>{{ $activity->created_at->locale('fr_CH')->timezone('Europe/Zurich')->isoFormat('LL LT') }}</td>
                    </tr>

                    @empty

                    <tr>

                        <td colspan="100" class="">

                            <div class="my-6">

                                <div class="text-center">

                                    <div class="text-gray-500 text-lg">
                                        No Logs
                                    </div>

                                </div>

                            </div>

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    </div>

</body>

</html>