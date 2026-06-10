<?php
$file = '/Users/mm/Desktop/Rakib/rakib/Hand OVER/crm/app/Http/Controllers/ReportController.php';
$content = file_get_contents($file);

// Replace user_id with assigned_user_id in filters array
$content = str_replace("'user_id' => \$filterUserId,", "'assigned_user_id' => \$filterUserId,", $content);

file_put_contents($file, $content);
echo "Success\n";
