<?php

test('the application returns a successful response for login page', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});
