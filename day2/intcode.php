<?php

//Part 1
$code_a = '1,12,2,3,1,1,2,3,1,3,4,3,1,5,0,3,2,13,1,19,1,19,10,23,2,10,23,27,1,27,6,31,1,13,31,35,1,13,35,39,1,39,10,43,2,43,13,47,1,47,9,51,2,51,13,55,1,5,55,59,2,59,9,63,1,13,63,67,2,13,67,71,1,71,5,75,2,75,13,79,1,79,6,83,1,83,5,87,2,87,6,91,1,5,91,95,1,95,13,99,2,99,6,103,1,5,103,107,1,107,9,111,2,6,111,115,1,5,115,119,1,119,2,123,1,6,123,0,99,2,14,0,0';
$code_a = array_map('intVal', explode(',', $code_a));
$output_a = runIntcode($code_a);
echo "Output code a: ".$output_a."\r\n";

//Part 2
$code_b = '1,0,0,3,1,1,2,3,1,3,4,3,1,5,0,3,2,13,1,19,1,19,10,23,2,10,23,27,1,27,6,31,1,13,31,35,1,13,35,39,1,39,10,43,2,43,13,47,1,47,9,51,2,51,13,55,1,5,55,59,2,59,9,63,1,13,63,67,2,13,67,71,1,71,5,75,2,75,13,79,1,79,6,83,1,83,5,87,2,87,6,91,1,5,91,95,1,95,13,99,2,99,6,103,1,5,103,107,1,107,9,111,2,6,111,115,1,5,115,119,1,119,2,123,1,6,123,0,99,2,14,0,0';
$code_b = array_map('intVal', explode(',', $code_b));
$found = false;
$output_expected = 19690720;
$param_a;
$param_b;
for ($param_a = 0; $param_a<100; $param_a++) {
    for ($param_b = 0; $param_b<100; $param_b++) {
        $active_code = $code_b;
        $active_code[1] = $param_a;
        $active_code[2] = $param_b;

        if (runIntcode($active_code) == $output_expected) {
            $found = true;
            break;
        }
    }
    if ($found) {
        break;
    }
}
 if ($found) {
     echo "The parameters ".str_pad($param_a, 2, '0', STR_PAD_LEFT).str_pad($param_b, 2, '0', STR_PAD_LEFT)." produce the desired output."."\r\n";
 } else {
     echo "Suiting parameters couldnt be found :("."\r\n";
 }

function runIntcode(array $code_band) : int
{
    $op_pointer = 0;
    $valid = true;
    while ($code_band[$op_pointer] != 99) {
        //Wrong code
        $op_code = $code_band[$op_pointer];
        if (!in_array($op_code, [1,2])) {
            $valid = false;
            break;
        }

        $operantA_pointer = $code_band[$op_pointer+1];
        $operantB_pointer = $code_band[$op_pointer+2];
        $result_pointer = $code_band[$op_pointer+3];

        $operantA = $code_band[$operantA_pointer] ?? null;
        $operantB = $code_band[$operantB_pointer] ?? null;

        if (is_null($operantA) || is_null($operantB)) {
            $valid = false;
            break;
        }

        if ($op_code == 1) {
            //do the add code
            $result = $operantA + $operantB;
        } else {
            //do the multiply code
            $result = $operantA * $operantB;
        }
        $code_band[$result_pointer] = $result;

        $op_pointer += 4;
    }

    if ($valid) {
        return $code_band[0];
    } else {
        return null;
    }
}
