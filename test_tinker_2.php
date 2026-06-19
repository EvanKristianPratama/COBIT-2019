<?php
echo "Total Inputs: " . App\Models\MstInfoflowInput::count() . "\n";
echo "Total Outputs: " . App\Models\MstInfoflowOutput::count() . "\n";
$firstInput = App\Models\MstInfoflowInput::first();
echo "Sample Input: " . json_encode($firstInput) . "\n";
$firstOutput = App\Models\MstInfoflowOutput::first();
echo "Sample Output: " . json_encode($firstOutput) . "\n";
