<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_la_home_pubblica_presenta_l_applicazione_e_permette_di_accedere(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertViewIs('welcome')
            ->assertSee('Coordinamento Esami')
            ->assertSee('href="'.route('login').'"', false);
    }

    public function test_la_landing_non_sostituisce_l_accesso_alle_pagine_riservate(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/appelli')->assertRedirect(route('login'));
        $this->get('/user/profile')->assertRedirect(route('login'));
    }
}
