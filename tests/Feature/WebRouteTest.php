<?php

use App\Models\User;

test('la page d\'accueil fonctionne', function () {
    $this->withoutExceptionHandling();
    $this->get(route('info'))
        ->assertStatus(200);
});

test('la page des tables fonctionne', function () {
    $this->get(route('tables'))
        ->assertStatus(200);
});

test('la route de santé (health) fonctionne', function () {
    $this->get('/health')
        ->assertStatus(200);
});

test('la route de redirection vers les logs fonctionne', function () {
    $this->get('/logs')
        ->assertStatus(200);
});

test('la route des jobs (queue monitor) fonctionne', function () {
    $this->get('/jobs')
        ->assertStatus(200);
});
