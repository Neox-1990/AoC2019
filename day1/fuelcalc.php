<?php

$input = '80891
109412
149508
114894
97527
59858
113548
110516
97454
84612
84578
87923
102675
114312
144158
147190
53051
115477
50870
122198
91019
114350
88592
119617
61012
67012
85425
62185
124628
98505
53320
123834
105862
113715
149328
72125
107301
110684
86037
102012
133227
66950
64761
141015
132134
87171
84142
80355
124967
87973
98062
79312
120108
97537
89584
55206
68135
83286
66726
101805
72996
113109
116248
132007
128378
52506
113628
62277
73720
101756
141675
107011
81118
60598
122703
129905
67786
50982
96193
70006
137087
136121
146902
74781
50569
102645
99426
97857
122801
55022
81433
60509
66906
142099
126652
103240
141014
55579
143169
125521';

$input_array = explode("\r\n", $input);
$input_array = array_map(function ($val) {
    return intval($val);
}, $input_array);

$fuel_a = array_map(function ($val) {
    $fuel = floor(floatval($val)/3)-2;
    return $fuel;
}, $input_array);

$fuel_b = array_map(function ($val) {
    $fuel = floor(floatval($val)/3)-2;
    $fuel_fuel = $fuel;
    while (floor(floatval($fuel_fuel)/3)-2 > 0) {
        $fuel_fuel = floor(floatval($fuel_fuel)/3)-2;
        $fuel += $fuel_fuel;
    }
    return $fuel;
}, $input_array);

$fuel_totala = array_sum($fuel_a);
$fuel_totalb = array_sum($fuel_b);

echo 'total fuel needed for modules only: '.$fuel_totala."\r\n";
echo 'total fuel needed for modules + fuel only: '.$fuel_totalb;
