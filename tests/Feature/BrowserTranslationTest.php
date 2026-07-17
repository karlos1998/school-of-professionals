<?php

test('it prevents browsers from automatically translating the application', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('<html lang="pl" class="notranslate" translate="no">', false)
        ->assertSee('<meta name="google" content="notranslate">', false);
});
