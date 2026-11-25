<?php

test('validation messages are in spanish', function () {
    app()->setLocale('es');

    $this->from(route('password.recovery.create'))
        ->post(route('password.recovery.store'), ['email' => ''])
        ->assertSessionHasErrors(['email' => 'El campo correo es obligatorio.']);
});
