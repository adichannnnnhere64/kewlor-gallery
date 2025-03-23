<?php

test('basic test', function (): void {
    $this->get('/')->assertSuccessful();
});
