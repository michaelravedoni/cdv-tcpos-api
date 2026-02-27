<?php

use App\Mail\OrderProblemCheck;
use Illuminate\Support\Facades\Mail;

test('le mailable OrderProblemCheck est correctement configuré', function () {
    $mailable = new OrderProblemCheck();

    $mailable->assertHasSubject('Commandes entre Woocommerce et TCPOS : Problème de synchronisation détecté');
    $mailable->assertSeeInHtml('Un problème de synchronisation a été détecté'); // Vérifie que le texte est présent
});

test('l\'envoi de mail fonctionne (simulé)', function () {
    Mail::fake();

    Mail::to('test@example.com')->send(new OrderProblemCheck());

    Mail::assertSent(OrderProblemCheck::class);
});
