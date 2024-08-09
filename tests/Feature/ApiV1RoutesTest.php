<?php

test('API v1 user route is accessible', function () {
    $response = $this->getJson('/api/v1/users');
    $response->assertStatus(200);

});