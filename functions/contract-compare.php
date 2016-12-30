function contract_compare($payment1, $payment2, $timespan, $output = true) {
    if (is_array($payment1)) {
        $one_time1 = $payment1[0];
        $monthly1 = $payment1[1];
    } else {
        $one_time1 = 0;
        $monthly1 = $payment1;
    }
    if (is_array($payment2)) {
        $one_time2 = $payment2[0];
        $monthly2 = $payment2[1];
    } else {
        $one_time2 = 0;
        $monthly2 = $payment2;
    }

    $costs1 = $one_time1;
    $costs2 = $one_time2;
    for ($i = 0; $i < $timespan; $i++) {
        $costs1 += $monthly1;
        $costs2 += $monthly2;
    }
    $costs_cheap = min($costs1, $costs2);

    if ($output) {
        if ($costs1 < $costs2) $contract_nr = 1;
        elseif ($costs2 < $costs1) $contract_nr = 2;
        else $contract_nr = false;

        //setlocale(LC_MONETARY, 'de_DE.UTF-8');
        //$costs_cheap = money_format('%.2n', $costs_cheap);
        $costs_cheap = strtr(round($costs_cheap, 2), ".", ",") . " €";

        if ($contract_nr) {
            echo "Contract $contract_nr costs $costs_cheap.";
        }
    }
    else return $costs_cheap;
}

//echo contract_compare(array(235.66, 11.99), array(29.99, 29.99), 12);