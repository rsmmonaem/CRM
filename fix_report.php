<?php
$file = '/Users/mm/Desktop/Rakib/rakib/Hand OVER/crm/app/Http/Controllers/ReportController.php';
$content = file_get_contents($file);

// Find the start of the crDashboardReport method
$startPos = strpos($content, 'public function crDashboardReport(Request $request)');
if ($startPos === false) die("Method not found");

// Find the end of the method by counting braces
$len = strlen($content);
$braces = 0;
$started = false;
$endPos = -1;

for ($i = $startPos; $i < $len; $i++) {
    if ($content[$i] == '{') {
        $braces++;
        $started = true;
    } elseif ($content[$i] == '}') {
        $braces--;
        if ($started && $braces == 0) {
            $endPos = $i;
            break;
        }
    }
}

if ($endPos == -1) die("Method end not found");

$methodBody = substr($content, $startPos, $endPos - $startPos + 1);

// Now duplicate it and modify it
$newMethod = str_replace('public function crDashboardReport(Request $request)', "public function crDashboardReportPrint(Request \$request)", $methodBody);
$newMethod = str_replace("Inertia::render('Reports/CRDashboardReport'", "Inertia::render('Reports/Print/CRDashboardReportPrint'", $newMethod);

// Also pass printTime
$newMethod = str_replace("'user' => \$user", "'user' => \$user,\n            'printTime' => now()->format('Y-m-d h:i A')", $newMethod);

// Insert the new method right after the old one
$newContent = substr($content, 0, $endPos + 1) . "\n\n    " . $newMethod . substr($content, $endPos + 1);

file_put_contents($file, $newContent);
echo "Success\n";
