<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="bumblebee">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Château de Villa - Woocommerce & TCPOS Sync</title>
    <link rel="icon" href="data:;base64,iVBORw0KGgo=">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1/dist/tailwind.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@1.14.0/dist/full.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

</head>

<body class="font-sans p-6 bg-base-200">

    <button type="button" class="absolute top-0 right-0 m-4 btn btn-primary" title="Rafraîchissement automatique" onclick="location.reload();" id="reloadbtn"><i class="bi bi-arrow-repeat mr-3"></i>60</button>

    <h1 class="text-5xl text-neutral font-bold">
        Dashboard
        <small class="text-xl">Château de Villa</small>
    </h1>
    <div class="mb-6">Woocommerce & TCPOS Sync</div>

    <div class="px-6 py-4 mb-6 pl-4 rounded-md shadow-lg bg-base-100 text-base-content card">

        <h2 class="mb-4 text-2xl font-bold text-neutral">
            Informations
        </h2>
        <div class="flex flex-wrap">
            <div class="w-full lg:w-1/3">
                <h3 class="mb-1 font-bold text-neutral">Produits</h3>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Produits dans la base de données</div>
                    <div class="w-1/3">{{ $products_count }}</div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3">Produits avec une quantité ⊖ à 6</div>
                    <div class="w-1/3">
                        {{ $products_where_minimal_quantity_under_six }}</div>
                </div>
                <div class="flex flex-wrap">
                    <div tabindex="0" class="collapse w-full">
                        <div class="text-base font-normal p-0 m-0">
                            Produits par type <i class="bi bi-caret-down-fill"></i>
                        </div>
                        <div class="collapse-content">
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
                    </div>
                </div>
            </div>
            <div class="w-full lg:w-1/3">
                <h3 class="mb-1 font-bold text-neutral">Synchronisation et importations</h3>
                <div tabindex="0" class="collapse w-full mb-2">
                    <div class="text-base font-normal">
                        Prochains événements programmés <i class="bi bi-caret-down-fill"></i>
                    </div>
                    <div class="collapse-content">
                        <div class="flex flex-wrap">
                            <div class="w-1/2"><span class="tooltip tooltip-right"
                                    data-tip="Prochaine importation Woocommerce"><i class="bi bi-arrow-right-square"></i> <i class="bi bi-download"></i> Woocommerce</span></div>
                            <div class="w-1/2">
                                {{ $scheduledWoo->locale('fr_ch')->timezone('Europe/Zurich')->isoFormat('L LT') }}</div>
                        </div>
                        <div class="flex flex-wrap">
                            <div class="w-1/2"><span class="tooltip tooltip-right"
                                    data-tip="Prochaine importation TCPOS"><i class="bi bi-arrow-right-square"></i> <i class="bi bi-download"></i> TCPOS</span></div>
                            <div class="w-1/2">
                                {{ $scheduledTcpos->locale('fr_ch')->timezone('Europe/Zurich')->isoFormat('L LT') }}
                            </div>
                        </div>
                        <div class="flex flex-wrap">
                            <div class="w-1/2"><span class="tooltip tooltip-right"
                                    data-tip="Prochaine synchronisation"><i class="bi bi-arrow-right-square"></i> <i class="bi bi-arrow-repeat"></i> Synchro</span></div>
                            <div class="w-1/2">
                                {{ $scheduledSync->locale('fr_ch')->timezone('Europe/Zurich')->isoFormat('L LT') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3"><span class="tooltip tooltip-right"
                            data-tip="Dernière mise à jour dans la base TCPOS"><i class="bi bi-arrow-left-square"></i> <i class="bi bi-upload"></i> TCPOS</span></div>
                    <div class="w-1/3">
                        <span class="tooltip" data-tip="{{ AppHelper::getLastTcposUpdate()->locale('fr_ch')->isoFormat('L LT') }}">
                            {{ now()->locale('fr_ch')->longRelativeToNowDiffForHumans(AppHelper::getLastTcposUpdate()) }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3"><span class="tooltip tooltip-right"
                            data-tip="Besoin d'importer la base de données TCPOS ?"><i class="bi bi-question-circle"></i> <i class="bi bi-download"></i> TCPOS</span></div>
                    <div class="w-1/3">
                        <span class="tooltip" data-tip="{{ $lastTcposUpdate }}">
                            {!! $needImportFromTcpos ? '<i class="bi bi-exclamation-circle"></i> Oui' : '<i class="bi bi-check-circle"></i>' !!}
                        </span>
                    </div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3"><span class="tooltip tooltip-right" data-tip="Date de la dernière tâche d'arrière-fond exécutée (job)"><i class="bi bi-arrow-left-square"></i>
                    <i class="bi bi-card-checklist"></i></span></div>
                    <div class="w-1/3">
                        <span class="tooltip" data-tip="{{ $lastJobDatetime ? $lastJobDatetime->isoFormat('L LT') : null }}">
                            {{ $lastJobDatetime ? $lastJobDatetime->longRelativeToNowDiffForHumans() : null }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-wrap">
                    <div class="w-2/3"><span class="tooltip tooltip-right" data-tip="Nombre de zâches restantes à exécuter (jobs)"><i class="bi bi-hash"></i> <i class="bi bi-card-checklist"></i></div>
                    <div class="w-1/3">{{ $remainingJobs }}</span></div>
                </div>
                <div class="flex flex-wrap my-4">
                    @if($jobsWorking)
                    <a class="bg-red-800 hover:bg-red-700 text-white py-1 px-3 rounded" href="/jobs">Tâches
                        d'arrière-fond en cours d'exécution</a>
                    @endif
                </div>
            </div>
            <div class="w-full lg:w-1/3">
                <div>
                    <div class="mb-4">
                        <a class="btn btn-neutral btn-sm" href="/jobs">Voir les
                            Jobs</a>
                        <a class="btn btn-neutral btn-sm" href="/logs">Voir les
                            Logs</a>
                    </div>
                    <div class="mb-4">
                        <ul>
                            <li><a class="link link-accent" href="/api/wc/import/all?force=1">1. Forcer l'importation depuis Woocommerce  | ~2 min.</a></li>
                            <li><a class="link link-accent" href="/api/import/all?force=1">2. Forcer l'importation depuis TCPOS | ~15 min.</a></li>
                            <li><a class="link link-accent" href="/api/sync/all?force=1">3. Forcer la synchronisation | ~25 min.</a></li>
                        </ul>
                    </div>
                    <div class="mb-4">
                        <a class="btn btn-outline btn-neutral btn-sm block" href="/api/sync/orders">Synchroniser les commandes manuellement (~1
                            min.)</a>
                        <a class="btn btn-outline btn-neutral btn-sm block mt-2" href="/api/import/products/images">Importer les images depuis TCPOS (~10 min.)</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="px-6 py-4 mb-6 pl-4 bg-base-100 rounded-md shadow-md">
        <h2 class="mb-4 text-2xl font-bold text-neutral">
            Logs
        </h2>
        
        <select class="select select-bordered select-sm w-full max-w-xs my-2" onchange="window.location.href = '?limit='+this.value">
            <option value="500" @if($activitiesLimit == 500) selected @endif>500</option>
            <option value="1000" @if($activitiesLimit == 1000) selected @endif>1000</option>
            <option value="2000" @if($activitiesLimit == 2000) selected @endif>2000</option>
            <option value="5000" @if($activitiesLimit == 5000) selected @endif>5000</option>
        </select>

        <div class="overflow-x-auto shadow-lg">

            <table class="w-full rounded whitespace-no-wrap">
                <thead class="bg-gray-200">
                    <tr>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            Groupe</th>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            Ressource</th>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            Type</th>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            Message</th>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            Durée</th>
                        <th
                            class="px-4 py-3 font-medium text-left text-xs text-gray-600 uppercase border-b border-gray-200">
                            Date</th>
                    </tr>
                </thead>
                <tbody class="">
                    @forelse($activities as $activity)
                    @php
                    $level = $activity->getExtraProperty('level');
                    $colorText = '';
                    $colorBg = '';
                    if ($level == 'start') {
                    $colorBg = 'bg-success';
                    $colorText = 'alert-success';
                    } elseif ($level == 'end') {
                    $colorBg = 'bg-success';
                    $colorText = 'alert-success';
                    } elseif ($level == 'error') {
                    $colorBg = 'bg-error';
                    $colorText = 'alert-error';
                    } elseif ($level == 'warning') {
                    $colorBg = 'bg-warning';
                    $colorText = 'alert-warning';
                    } elseif ($level == 'info') {
                    $colorBg = 'text-base-content';
                    $colorText = 'bg-base-content-100';
                    } elseif ($level == 'job') {
                    $colorBg = 'bg-info';
                    $colorText = 'alert-info';
                    }
                    @endphp

                    <tr class="font-sm leading-relaxed {{ $colorText }} {{ $colorBg }}">
                        <td class="px-4">{{ $activity->getExtraProperty('group') }}</td>
                        <td class="px-4">{{ $activity->getExtraProperty('resource') }}</td>
                        <td class="px-4">{{ $level }}</td>
                        <td class="px-4">{{ $activity->description }}</td>
                        <td>{{ $activity->getExtraProperty('duration') ? number_format($activity->getExtraProperty('duration'), 2).' secondes' : null }}
                        </td>
                        <td>{{ $activity->created_at->locale('fr_CH')->timezone('Europe/Zurich')->isoFormat('L LT') }}
                        </td>
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
<script>
    var reload = 60;
    var rld = setInterval(function(){
        if(reload===0) {
            document.querySelector('#reloadbtn').innerHTML = '<i class="bi bi-arrow-repeat mr-3"></i>…';
            location.reload();
            clearInterval(rld);
        } else {
            document.querySelector('#reloadbtn').innerHTML = '<i class="bi bi-arrow-repeat mr-3"></i>' + --reload;
        }
    },1000);
</script>
</html>