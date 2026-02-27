<?php

use Illuminate\Support\Facades\Route;

test('aucune route principale ne renvoie d\'erreur 500', function () {
    $routes = [
        '/',
        '/tables',
        '/health',
        '/jobs',
        '/log-viewer',
        '/api/info'
    ];

    foreach ($routes as $url) {
        $response = $this->get($url);
        
        expect($response->status())
            ->toBeLessThan(500, "La route $url a renvoyé une erreur serveur 500.");
    }
});

test('les assets Vite sont présents dans le HTML', function () {
    $this->get('/')
        ->assertSee('<script', false)
        ->assertSee('<link', false);
});
