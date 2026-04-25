<?php

it('renders exam flow pages', function () {
    $this->seed();

    $this->get('/')
        ->assertStatus(200)
        ->assertSee('WelcomePage');

    $this->get('/egzaminy/wit')
        ->assertStatus(200)
        ->assertSee('AuthorityTestsPage');

    $this->get('/egzaminy/wit/maszyny-drogowe')
        ->assertStatus(200)
        ->assertSee('ExamSessionPage');

    $this->get('/egzaminy/udt/dzwigi-budowlane/i')
        ->assertStatus(200)
        ->assertSee('ExamSessionPage');
});
