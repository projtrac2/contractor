
<?php

$amount = 10000000;
$percentage = [30, 40, 30];
$increases = [0, 2000000, 1000000];

for ($i = 0; $i < count($percentage); $i++) {
    $increase = $increases[$i];
    $percent = $percentage[$i];
    if ($increase > 0) {
        $paid_amount = $percent / 100 * $amount;
        $payment[] = $paid_amount + $increase;
    } else {
        $paid_amount = $percent / 100 * $amount;
        $payment[] = $paid_amount;
    }
}
var_dump($payment);

