<?php
$rootDir = __DIR__;

$excludeDirs = ['node_modules', 'vendor', '.git', '.gemini', 'DATABASE FILE', 'assets', 'dist'];
$entryPoints = [
    'index.php', 'login.php', 'admin/dashboard.php', 'landlord/dashboard.php', 
    'client/dashboard.php', 'caretaker/dashboard.php', 'admin/index.php',
    'landlord/index.php', 'client/index.php', 'caretaker/index.php'
];
$tempScripts = [
    'test-gemini.php', 'test-tooltip.php', 'test_out.html', 'db_cleanup.php', 
    'discover_schema.php', 'fix_schema.php', 'schema_probe.php', 
    'show_bookings_schema.php', 'migrate_daycare.php', 'run-migration.php',
    'list_tables.php', 'verify_logic.php'
];

$allFiles = [];
$referenceCounts = [];

$dir = new RecursiveDirectoryIterator($rootDir);
$iterator = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($iterator, '/^.+\.(php|js|css|html)$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($files as $file) {
    $path = $file[0];
    
    // Skip excluded directories
    $skip = false;
    foreach ($excludeDirs as $exc) {
        if (strpos($path, DIRECTORY_SEPARATOR . $exc . DIRECTORY_SEPARATOR) !== false || 
            strpos($path, '/' . $exc . '/') !== false ||
            strpos($path, '\\' . $exc . '\\') !== false) {
            $skip = true;
            break;
        }
    }
    
    if (strpos($path, 'find_unused_files.php') !== false) {
        $skip = true;
    }
    
    if ($skip) {
        continue;
    }
    
    $relativePath = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $path);
    $relativePath = str_replace('\\', '/', $relativePath); // normalize
    
    $allFiles[] = $relativePath;
    $referenceCounts[$relativePath] = 0;
}

// Now search inside all files for the filenames
foreach ($allFiles as $searchInFile) {
    $content = file_get_contents($rootDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $searchInFile));
    
    foreach ($allFiles as $targetFile) {
        if ($searchInFile === $targetFile) continue; // don't count self references
        
        $basename = basename($targetFile);
        
        // If the basename or the relative path appears in the content
        if (strpos($content, $basename) !== false || strpos($content, $targetFile) !== false) {
            $referenceCounts[$targetFile]++;
        }
    }
}

echo "Potentially Unused Support/Application Files (0 references):\n";
echo "===========================================================\n";
foreach ($referenceCounts as $file => $count) {
    if ($count === 0 && !in_array($file, $entryPoints) && !in_array($file, $tempScripts)) {
        // Double check typical entry points
        if (strpos($file, 'index.php') !== false) continue;
        
        echo "- " . $file . "\n";
    }
}

echo "\nKnown Temporary/Helper Scripts:\n";
echo "===============================\n";
foreach ($allFiles as $file) {
    if (in_array(basename($file), $tempScripts) || in_array($file, $tempScripts)) {
        echo "- " . $file . "\n";
    }
}
?>
