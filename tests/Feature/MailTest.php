<?php

use App\Mail\OrderProblemCheck;
use Illuminate\Support\Facades\Mail;

test('le mailable OrderProblemCheck est correctement configuré', function () {
    $mailable = new OrderProblemCheck();

    $mailable->assertHasSubject('Commandes entre Woocommerce et TCPOS : Problème de synchronisation détecté');
    $mailable->assertSeeInHtml('Un problème de synchronisation a été détecté'); // Vérifie que le texte est présent
});

test('le mailable OrderProblemCheck affiche les details des commandes bloquees', function () {
    $mailable = new OrderProblemCheck([
        [
            'id' => 9999,
            'customer' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'total' => '45.00 CHF',
            'status' => 'processing',
            'date' => '18.11.2026 12:00',
            'error' => 'TCPOS timeout',
        ],
    ]);

    $mailable->assertSeeInHtml('#9999');
    $mailable->assertSeeInHtml('Jean Dupont');
    $mailable->assertSeeInHtml('TCPOS timeout');
});

test('l\'envoi de mail fonctionne (simulé)', function () {
    Mail::fake();

    Mail::to('test@example.com')->send(new OrderProblemCheck());

    Mail::assertSent(OrderProblemCheck::class);
});
