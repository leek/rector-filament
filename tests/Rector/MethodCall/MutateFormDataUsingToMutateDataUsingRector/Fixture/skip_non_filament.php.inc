<?php

use App\SomeClass;

SomeClass::make('create')->mutateFormDataUsing(fn (array $data) => $data);

?>
